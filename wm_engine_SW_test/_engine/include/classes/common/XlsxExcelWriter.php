<?php

namespace Wing\common;

use XLSXWriter;

class XlsxExcelWriter extends ExcelWriter
{
    public $writer; //XLSWriter 인스턴스
    public $sheet_name; //작업시트이름
    public $file_name; //엑셀파일명

    public $duplicate_chk; //필드의 중복체크 확인 여부
    public $duplicate_cnt; //중복필드별 카운트
    public $duplicate_stack; //필드별 현재 카운트 누적

    //라이브러리 인스턴스 생성
    public function __construct()
    {
        $this->writer = new XLSXWriter();
    } //construct
    //엑셀파일명 정의
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    } // abstract method
    //작업시트명 정의
    public function setSheetName($sheet_name)
    {
        $this->sheet_name = $sheet_name;
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
        global $cfg;
        foreach ( $header as $key => $type ) {
            if ( $type === 'price' ) {
                if ($cfg['currency_decimal']>0) {
                    $type = '#,##0.'.str_repeat('0', $cfg['currency_decimal']);
                } else {
                    $type = '#,##0';
                }
                $header[$key] = $type;
            }
        }
        $header_style = $this->setDefaultStyle($header_style);
        $this->writer->writeSheetHeader($this->sheet_name, $header, $header_style);
    } // abstract method
    //기본스타일값 정의
    public function setDefaultStyle($style)
    {
        /**
        특정 속성들은 기본 스타일을 지정해준다.
        **/
        //행전체 적용
        if (empty($style['valign'])) {
            //세로 정렬
            $style['valign'] = 'center';
        }
        if (empty($style['halign'])) {
            //가로정렬
            $style['halign'] = 'center';
        }
        if (empty($style['border'])) {
            //테두리
            $style['border'] = 'left,right,top,bottom';
            $style['border-style'] = 'thin';
        }
        if (!is_bool($style['wrap_text'])) {
            //텍스트속성(자동줄바꿈)
            $style['wrap_text'] = true;
        }
        if (empty($style['height'])) {
            //행 높이
            $style['height'] = 30;
        }
        foreach ($style as $key => $val)
        {
            //특정 셀에 지정된 스타일은 int => array() 형태이다. (재귀호출)
            if (is_array($val) && is_int(($key))) {
                $style[$key] = $this->setDefaultStyle($val);
            }
        }
        return $style;
    }
    //행별 데이터 적용
    public function writeSheetRow($row_data, $row_style = array())
    {
        $row_style = $this->setDefaultStyle($row_style);
        foreach ($row_data as $k => $data) {
            $data = stripslashes($data);
            if (mb_strlen($data, 'UTF-8') > 32767) {
                //셀내 데이터가 32,767바이트 초과시 유효성 에러 발생
                $data = mb_strcut($data, 0, 32767, 'UTF-8');
            }
            $row_data[$k] = $data;
        }
        $this->writer->writeSheetRow($this->sheet_name, $row_data, $row_style);
    } // abstract method
    //셀병합
    public function merge($start_row, $start_col, $end_row, $end_col)
    {
        $this->writer->markMergedCell($this->sheet_name, $start_row, $start_col, $end_row, $end_col);
    } // abstract method
    //엑셀파일작성
    public function writeFile($filename = null)
    {
        if ($filename) {
            $full_filename = $filename.'_'.date('Ymd', time()).'.xlsx';
            $this->writer->writeToFile($full_filename);

            return $full_filename;
        }

        header('Content-disposition: attachment; filename='.$this->file_name.'_'.date('Ymd', time()).'.xlsx');
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $this->writer->writeToStdOut();
    } // abstract method
}