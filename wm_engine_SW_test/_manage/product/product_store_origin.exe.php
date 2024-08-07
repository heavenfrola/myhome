<?php

/**
 * 스마트스토어 원산지 ajax
 */

use Wing\API\Naver\CommerceAPI;

$level = intval($_GET['level']);
$cno = $_GET['cno'];

// json structure
$json = [
    'next' => $_cate_colname[1][($level + 1)],
    'data' => array()
];

// get sub categories
$commerceAPI = new CommerceAPI();
$ret = $commerceAPI->subOriginAreas($cno);
if (is_array($ret->subOriginAreaCodeNames)) {
    foreach ($ret->subOriginAreaCodeNames as $v) {
        array_push($json['data'], [
            'id' => $v->code,
            'name' => preg_replace('/.*(:|>)/', '', $v->name)
        ]);
    }
}

// output
Header('Content-type: application/json');
echo json_encode_pretty($json);