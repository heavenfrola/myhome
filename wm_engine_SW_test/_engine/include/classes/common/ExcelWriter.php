<?php

namespace Wing\common;

Abstract class ExcelWriter
{
    //작업파일명 정의
    abstract public function setFileName($file_name);
    //작업시트명 정의
    abstract public function setSheetName($sheet_name);
    //헤더데이터 적용
    abstract public function writeSheetHeader($header, $header_style = array());
    //행별 데이터 적용
    abstract public function writeSheetRow($row, $row_style = array());
    //셀병합
    abstract public function merge($start_row, $start_col, $end_row, $end_col);
    //엑셀파일작성
    abstract public function writeFile($filename = null);
}