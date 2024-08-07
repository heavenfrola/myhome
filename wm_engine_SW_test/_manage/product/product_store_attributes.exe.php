<?php

/**
 * 스마트스토어 상품 속성 필드 구성
 */

use Wing\API\Naver\CommerceAPI;

// 상품 정보
$pno = (int) $_GET['pno'];
$extra_datas = (object) array();
if ($pno) {
    $product = $pdo->assoc("select extra_datas from {$tbl['product_nstore']} where pno=?", array(
        $pno
    ));
    if ($product) {
        $extra_datas = json_decode($product['extra_datas']);
    }
}

// 속성 필드 정보
$commerceAPI = new CommerceAPI();
$attributes = $commerceAPI->productAttribute($_GET['categoryId']);
$values = $commerceAPI->productAttributeValues($_GET['categoryId']);
$units = $commerceAPI->productAttributeUnits($_GET['categoryId']);

$_attributes = array(); // 속성값
$optional = 0; // 비 중요속성의 갯수
if (is_array($attributes)) {
    foreach ($attributes as $attr) {
        $type = $attr->attributeClassificationType;
        $attr->values = array();

        // 부가 옵션일 경우 사용 안함 추가
        if (($type == 'SINGLE_SELECT' || $type == 'RANGE')) {
            array_push($attr->values, array(
                'attributeSeq' => $attr->attributeSeq,
                'attributeValueSeq' => 0,
                'minAttributeValue' => '사용 안함',
                'exposureOrder' => 0,
                'checked' => true
            ));
        }

        // 항목 추가
        foreach ($values as $val) {
            if ($attr->attributeSeq == $val->attributeSeq) {
                // 선택 여부
                if (count($extra_datas->attr->{$attr->attributeSeq}) > 0) {
                    $val->checked = in_array($val->attributeValueSeq, $extra_datas->attr->{$attr->attributeSeq});
                }
                array_push($attr->values, $val);
            }
        }

        // 범위 데이터 실제 값
        if ($extra_datas->attr_v->{$attr->attributeSeq}) {
            $attr->attributeRealValue = $extra_datas->attr_v->{$attr->attributeSeq};
        }

        // 단위 코드
        foreach ($units as $unit) {
            if ($attr->representativeUnitCode == $unit->id) {
                $attr->unit = $unit;
            }
        }
        $_attributes[$attr->attributeSeq] = $attr;

        // 비 중요항목 카운트
        if ($attr->attributeType == 'OPTIONAL') {
            $optional++;
        }
    }
}

// 정렬
ksort($_attributes);
$_attributes = array_values($_attributes);

// output
Header('Content-type: application/json');
echo json_encode_pretty(array(
    'attributes' => $_attributes,
    'total_rows' => count($_attributes),
    'optional' => $optional
));