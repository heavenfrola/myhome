<?php

/**
 * 작성자 표기 설정 마이그레이션
 **/

addField('mari_config', 'protect_id', 'enum("N", "Y") not null default "N"');
addField('mari_config', 'protect_id_strlen', 'int(10) not null default 0');
addField('mari_config', 'protect_id_suffix', 'varchar(20) not null default ""');

foreach (array('review', 'qna') as $val) {
    $val2 = '';
    switch($cfg['product_'.$val.'_name']) {
        case '1' : $val2 = 'name'; break;
        case '2' : $val2 = 'id'; break;
        case '3' : $val2 = 'name_id'; break;
        case '4' : $val2 = 'nickname'; break;
    }
    if ($val2) {
        $scfg->import(array('product_'.$val.'_name' => $val2));
    }
}

$tmp = $pdo->iterator("select no, protect_name, protect_name_strlen, protect_name_suffix from mari_config");
foreach ($tmp as $val) {
    $pdo->query("update mari_config set protect_id=?, protect_id_strlen=?, protect_id_suffix=? where no=?", array(
        $val['protect_name'],
        $val['protect_name_strlen'],
        $val['protect_name_suffix'],
        $val['no']
    ));
}

$scfg->import(array(
    'use_global_protect_name' => 'Y',
));

?>