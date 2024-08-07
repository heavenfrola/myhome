<?php

use Wing\common\ExcelWriter;

class XmlExcelWriter extends ExcelWriter
{
    public $writer; //ExcelWriterXML() 인스턴스
    public $sheet_name; //작업시트이름
    public $file_name; //엑셀파일명
    public $sheet; //작업시트객체
    public $col_format; //열 데이터 속성
    public $style; //스타일정의 참조 변수

    public $duplicate_chk; //필드의 중복체크 확인 여부
    public $duplicate_cnt; //중복필드별 카운트
    public $duplicate_stack; //필드별 현재 카운트 누적

    private $row; //현재 데이터 쓰기 행번호 (임의 조정시 에러발생위험. private 적용)

    //엑셀파일명 정의 (생성자 역할)
    public function setFileName($file_name)
    {
        $this->file_name = $file_name.'_'.date('Ymd', time()).'.xls';
        $this->writer = new ExcelWriterXML($this->file_name);
        $this->writer->docAuthor('Wisa SmartWing');
        $this->col_format = array();
    } // abstract method
    //작업시트명 정의
    public function setSheetName($sheet_name)
    {
        $this->row = 1; //1행 으로 초기화
        $this->sheet_name = $sheet_name;
        $this->sheet = $this->writer->addSheet($this->sheet_name);
    } // abstract method
    //중복 항목명 체크
    public function duplicateField($field_list, $val, $invert = false) {
        //invert 수행 = field_list배열의 형태가 키=>텍스트 형태인경우 false, 텍스트=>키 형태인경우 true
        if (!$this->duplicate_chk) {
            if ($invert) {
                $field_list = array_flip($field_list);
            }
            $this->duplicate_cnt = array_count_values($field_list);
            $this->duplicate_chk = true;
        }
        if ((empty($this->duplicate_stack[$val]))) {
            $this->duplicate_stack[$val] = 1;
        } else {
            $this->duplicate_stack[$val]++;
        }
        $field_name = ($this->duplicate_stack[$val]>1) ? '_'.$this->duplicate_stack[$val] : '';

        return $field_name;
    }
    //헤더데이터 적용
    public function writeSheetHeader($header, $header_style = array())
    {
        $col = 0;
        foreach ($header as $text => $datatype) {
            $width = (!empty($header_style['widths'][$col])) ? $header_style['widths'][$col] : 20;
            $this->sheet->cellWidth($this->row, ($col+1), ($width * 5.32)); //xlsx대비 * 5.32사이즈
            $this->getColFormat($col, $datatype); //열의 데이터 타입 정의
            if(!$header_style['suppress_row']){
                //헤더 노출인 경우
                $style_code = $this->convertStyleCode($header_style);
                $this->setStyle($style_code, $header_style); //헤더영역 스타일 정의
                if (!empty($header_style[$col])) {
                    //셀별 스타일이 정의되어 있다면,
                    $style_code = $this->convertStyleCode($header_style[$col]);
                    $this->setStyle($style_code, $header_style[$col]); //셀별 스타일까지 선언한다.
                }
                $this->sheet->writeString($this->row, ($col+1), stripslashes($text), $style_code);
                $height = (!empty($header_style['heights'][$col])) ? $header_style['heights'][$col] : 20;
                $this->sheet->cellHeight($this->row, ($col + 1), $height); //셀 높이 적용
            }
            $col++;
        }
        if(!$header_style['suppress_row']) {
            $this->row++; //헤더행 작성 후 1행 증가
        }
    } // abstract method
    //열의 데이터 타입 적용
    public function setColFormat($col) {
        global $cfg;
        $type = $this->col_format[$col];
        if ($type === 'price') {
            if ($cfg['currency_decimal']>0) {
                $this->style->numberFormat('#,##0.'.str_repeat('0',$cfg['currency_decimal']));
            } else {
                $this->style->numberFormat('#,##0');
            }
        }

    }
    //열의 데이터 타입 정의
    public function getColFormat($col, $type) {
        $this->col_format[$col] = $type;
    }
    //행별 데이터 적용
    public function writeSheetRow($row_data, $row_style = array())
    {
        foreach ($this->col_format as $key => $type) {
            $row_style['col_style'][$key]['type'] = $type;
        }
        foreach ($row_data as $col => $data) {
            $style_code = $this->convertStyleCode($row_style);
            $this->setStyle($style_code, $row_style);
            if ($data !== 'emptyCell') {
                //merge되는 경우, 비어있는 셀이 존재해야한다. (emptyCell = 병합되는 셀)
                $cell_height = (!empty($row_style['height'])) ? $row_style['height'] : 20; //행 높이 기본값 20
                if (!empty($row_style[$col])) {
                    //행의 별도 스타일을 사용하는 경우
                    $style_code = $this->convertStyleCode($row_style[$col]);
                    $this->setStyle($style_code, $row_style[$col]); //행별 스타일 코드 적용
                    if (!empty($row_style[$col]['height'])) {
                        //셀 높이의 처리는 시트적용 내용이다.(setStyle x)
                        //행내 셀들의 높이가 다른경우 가장 작은 높이로 적용된다.
                        $cell_height = $row_style[$col]['height'];
                    }
                }
                //해당열의 데이터 타입 정의
                $this->sheet->cellHeight($this->row, ($col + 1), $cell_height); //셀 높이 적용
                $data = stripslashes($data);
                if (mb_strlen($data, 'UTF-8') > 32767) {
                    //셀내 데이터가 32,767바이트 초과시 유효성 에러 발생
                    $data = mb_strcut($data, 0, 32767, _BASE_CHARSET_);
                }
                $this->setColFormat($col);
                if ($this->col_format[$col] === 'price') {
                    $this->sheet->writeNumber($this->row, ($col + 1), $data, $style_code);
                } else {
                    $this->sheet->writeString($this->row, ($col + 1), $data, $style_code);
                }
            }
        }
        $this->row++; //한 행씩 증가
    } // abstract method
    //셀병합
    public function merge($start_row, $start_col, $end_row, $end_col)
    {
        /* XML Writer는 셀좌표의 시작을 1부터 정의한다.(XLSX writer는 0부터)
        integer  	$width  	Number of cells to the right to merge with
        integer  	$height  	Number of cells down to merge with */
        $start_row++;
        $start_col++;
        $end_row++;
        $end_col++;
        $width = $end_col - $start_col;
        $height = $end_row - $start_row;
        $this->sheet->cellMerge($start_row, $start_col, $width, $height);
    } // abstract method
    //스타일 정의
    public function setStyle($style_code, $style_arr)
    {
        //스타일 코드명이 겹치지 않아야 한다. (중복스타일은 파일에 기술하지 않음)
        if ($this->writer->checkStyleID($style_code)) {
            //스타일코드를 선언하며, xml내에 기술한다.
            $this->style = $this->writer->addStyle($style_code);
            //배경컬러
            (!empty($style_arr['fill'])) ? $this->style->bgColor($style_arr['fill']) : $this->style->bgColor('#ffffff');
            //폰트컬러
            (!empty($style_arr['color'])) ? $this->style->fontColor($style_arr['color']) : $this->style->fontColor('#000000');
            //폰트 스타일 (볼드, 이탤릭)
            if (!empty($style_arr['font-style'])) {
                //폰트 볼드처리
                switch ($style_arr['font-style']) {
                    case 'bold':
                        $this->style->fontBold();
                        break;
                    case 'italic':
                        $this->style->fontItalic();
                        break;
                    case 'underline':
                        $this->style->fontUnderline();
                        break;
                }
            }
            //세로 정렬 , 기본값 : 중앙
            (!empty($style_arr['valign'])) ? $this->style->alignVertical(ucfirst(strtolower($style_arr['valign']))) : $this->style->alignVertical('Center');
            //가로 정렬 , 기본값 : 중앙
            (!empty($style_arr['halign'])) ? $this->style->alignHorizontal(ucfirst(strtolower($style_arr['halign']))) : $this->style->alignHorizontal('Center');
            //기본 테두리(4면) , 기본값 : 전체
            (!empty($style_arr['border'])) ? $this->style->border($style_arr['border']) : $this->style->border('All');
            //셀내 자동줄바꿈 처리
            if ( is_bool($style_arr['wrap_text']) && $style_arr['wrap_text'] == true ) {
                $this->style->alignWraptext();
            }
        }
    }
    //스타일내용을 스트링으로 변환하여 고유 코드로 사용
    public function convertStyleCode($style_code)
    {
        $ret = 'style_';
        $temp_arr = array();
        foreach ($style_code as $val) {
            if (!is_array($val)) {
                $temp_arr[] = $val;//하위 배열의 속성은 제외
            }
        }
        $ret .= str_replace('"', '', json_encode($temp_arr));
        return $ret;
    }
    //엑셀파일작성
    public function writeFile($filename = null)
    {
        if ($filename) {
            $full_filename = $filename.'_'.date('Ymd', time()).'.xls';
            $this->writer->writeData($full_filename);

            return $full_filename;
        }

        $this->writer->sendHeaders();
        $this->writer->writeData();
    } // abstract method
}
