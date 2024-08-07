<?php

/**
 * 스마트스토어 모델 ajax
 */

use Wing\API\Naver\CommerceAPI;

$name = $_GET['name'];
$categoryId = intval($_GET['categoryId']);
$page = 1;
$json = [
    'totalPages' => 0,
    'data' => array()
];

// get sub categories
$commerceAPI = new CommerceAPI();
while (1) {
    $models = $commerceAPI->productModels($name, $page);
    foreach ($models->contents as $model) {
        if ($model->categoryId != $categoryId) continue;

        array_push($json['data'], [
            'id' => $model->id,
            'name' => $model->name
        ]);
    }
    $page++;

    $json['totalPages'] = $models->totalPages;
    if ($page >= $models->totalPages || $page >= 5) {
        break;
    }
}

// output
Header('Content-type: application/json');
echo json_encode_pretty($json);