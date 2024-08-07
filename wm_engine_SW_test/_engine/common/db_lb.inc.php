<?php

/**
 * 읽기 데이터베이스 로드 밸런싱
 **/

if (isset($exec_file) == false) {
    if (file_exists($root_dir.'/_data/config/db_read.json')) {
        $tmp = file_get_contents($root_dir.'/_data/config/db_read.json');
        $tmp = json_decode($tmp);
        if (is_object($tmp) == false) return;
        if (count($tmp) < 1) return;

        $tmp_ip = array();
        $tmp_rate = array();
        foreach ($tmp as $key => $val) {
            $tmp_ip[] = $val[0];
            $tmp_rate[] = $val[1];
        }
        $tmp = rand(1, array_sum($tmp_rate));
        for($i = 0; $i < count($tmp_rate); $i++) {
            $tmp -= $tmp_rate[$i];
            if($tmp < 1) {
                $con_info[1] = $tmp_ip[$i];
                break;
            }
        }
    }
}