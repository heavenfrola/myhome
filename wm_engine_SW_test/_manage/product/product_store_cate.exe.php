<?php

/**
 * 스마트스토어 카테고리 ajax
 */

use Wing\API\Naver\CommerceAPI;

$level = intval($_GET['level']);
$cno = intval($_GET['cno']);

// json structure
$json = [
    'next' => $_cate_colname[1][($level + 1)],
    'data' => array(),
    'info' => null
];

// get sub categories
$commerceAPI = new CommerceAPI();
$ret = $commerceAPI->categoriesSubCategory($cno);
if (is_array($ret)) {
    foreach ($ret as $v) {
        array_push($json['data'], [
            'id' => $v->id,
            'name' => $v->name
        ]);
    }
}

// get category info
$json['info'] = $commerceAPI->categories($cno);


// output
Header('Content-type: application/json');
echo json_encode_pretty($json);