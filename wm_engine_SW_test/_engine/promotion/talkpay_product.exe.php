<?php

/**
 * 카카오페이 구매 전체 상품 목록 API
 **/

ini_set('memory_limit', -1);

use Wing\API\Kakao\KakaoTalkPay;

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/wingPos.lib.php';
include_once $engine_dir.'/_config/set.talkStore.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
$talkpay->compareShopKey($_GET['shopKey']);

// 상품 상세
$result = array(
    'shopKey' => $scfg->get('talkpay_ShopKey'),
    'products' => array()
);

// 상품 정보 로딩
$productIds = implode(',', array_map(function($hash) {
    $hash = addslashes(trim($hash));
    return "'$hash'";
}, explode(',', $_GET['productIds'])));

if (empty($productIds) == true) {
    $productIds = '';
}

$list = array();
$res = $pdo->iterator("select * from {$tbl['product']} where hash in ($productIds) and stat in (2, 3)");
foreach ($res as $data) {

    $data['free_dlv'] = $data['free_delivery'];

  	$prdCart = new OrderCart();
	$prdCart->addCart($data);
	$prdCart->complete();
    $cart = $prdCart->loopCart(); // 상품별 무료

    $product = array(
        'id' => $data['hash'],
        'basePrice' => $cart->getData('sum_sell_prc') / (int) $cart->getData('buy_ea'),
    );

    // combination 조합 수량
    $comb_cnt = 0;
    $comb = $pdo->iterator("select count(*) as cnt from {$tbl['product_option_item']} where pno='{$data['no']}' group by opno");
    foreach ($comb as $combdata) {
        if (!$comb_cnt) $comb_cnt = (int) $combdata['cnt'];
        else $comb_cnt *= (int) $combdata['cnt'];
    }
    if ($comb_cnt > 2000) {
        continue;
    }

    // 상세 정보
    if ($mode == 'detail') {
        $product['name'] = strip_tags(stripslashes($data['name']));

        // 카테고리
        if ($data['big'] > 0) $product['categoryName1'] = getCateName($data['big']);
        if ($data['mid'] > 0) $product['categoryName2'] = getCateName($data['mid']);

        // 할인 전 상품 가격
        if (!$data['normal_prc']) $data['normal_prc'] = $data['sell_prc'];
        if ($product['basePrice'] < $data['normal_prc']) {
            $product['originPrice'] = parsePrice($data['normal_prc']);
        }

        $product['description'] = trim(strip_tags($data['content2']));
        if (!$product['description']) {
            unset($product['description']);
        }
        $product['taxType'] = ($data['tax_free'] == 'Y') ? 'TAX_FREE' : 'TAX';
        $product['informationUrl'] = $root_url.'/shop/detail.php?pno='.$data['hash'];
        $product['descriptionUrl'] = $root_url.'/_data/compare/kakao/talkpay_detail.php?hash='.$data['hash'];
        $product['mainImageUrl'] = getListImgURL($data['updir'], $data['upfile2']);
        $product['registeredDateTime'] = $talkpay->convertDateFormat($data['reg_date']);

        // 부가이미지
        $imgres = $pdo->iterator("
            select updir, filename
            from {$tbl['product_image']}
            where pno='{$data['no']}' and filetype=2
            order by sort asc limit 10
        ");
        foreach ($imgres as $key => $imgdata) {
            if (is_array($product['imageUrls']) == false) {
                $product['imageUrls'] = array();
            }
            $product['imageUrls'][] = getListImgURL($imgdata['updir'], $imgdata['filename']);
        }

        // 배송 정책
        $pconf = ($data['partner_no'] > 0) ?
            $pdo->assoc("select * from {$tbl['partner_delivery']} where partner_no='{$data['partner_no']}'") :
            $cfg;
        if (!$data['partner_no']) $data['partner_no'] = 0;
        $dlv_feeType = 'CHARGE';
        if ($pconf['delivery_type'] == 1) {
            $dlv_feeType = 'FREE';
        }
        else if ($pconf['delivery_type'] == 3 && $pconf['delivery_free_limit'] > 0) {
            $dlv_feeType = 'CONDITIONAL_FREE';
        }
        $dlv_feePayType = 'PREPAID'; // 선불
        if ($cart->getData('is_freedlv') == 'Y') {
            $dlv_feeType = 'FREE';
        }
        if ($dlv_feeType == 'FREE') $dlv_feePayType = 'FREE'; // 무료
        $shipping_base_price = ($dlv_feeType == 'FREE') ? 0 : $pconf['delivery_fee'];

        $shippingPolicies = array(
            'id' => $data['partner_no'],
            'mainPolicy' => ($data['partner_no'] == 0) ? 'true' : 'false',
            'method' => 'LOGISTICS',
            'feeType' => $dlv_feeType,
            'feePayType' => $dlv_feePayType,
            'baseFee' => parsePrice($shipping_base_price),
        );
        if ($shipping_base_price > 0) {
            $shippingPolicies['conditionalFree'] = array(
                'basePrice' => parsePrice($pconf['delivery_free_limit'])
            );
        }
        $product['shippingPolicies'][] = $shippingPolicies;
    }

    // 상품 상태
    if ($data['use_talkpay'] != 'Y') {
        $product['status'] = 'NO_CONNECT';
    } else if ($data['ea_type'] != '1') {
        $product['status'] = 'NO_CONNECT';
    } else {
        switch($data['stat']) {
            case '2' :
                $product['status'] = 'ON_SALE';
                break;
            case '3' :
                $product['status'] = 'SOLD_OUT';
                break;
            default :
                $product['status'] = 'NO_CONNECT';
        }
    }

    // 옵션 및 재고
    if ($data['ea_type'] != '1') {
        $product['stockQuantity'] = 0;
        $product['optionSupport'] = 'false';
    } else {
        $option_no = $pdo->row("select count(*) from {$tbl['product_option_set']} where pno='{$data['no']}' and necessary in ('Y', 'N', 'C')");
        if ($option_no == 0) { // 옵션 없는 상품
            $product['stockQuantity'] = $pdo->row("select if (force_soldout='N', 9999, qty) from erp_complex_option where pno='{$data['no']}' and opts='' and del_yn='N'");
            if ($product['stockQuantity'] == false || $product['stockQuantity'] < 0) {
                $product['stockQuantity'] = 0;
            }
            $product['optionSupport'] = 'false';
        } else { // 옵션 있는 상품
            $ocache = $ness_n = array();
            $product['optionSupport'] = 'true';
            $product['option'] = array();
            $ores = $pdo->iterator("
                select no, otype, name, necessary from {$tbl['product_option_set']} where pno='{$data['no']}' and necessary!='P' order by sort asc
            ");
            foreach ($ores as $optdata) {
                $temp = array(
                    'type' => ($optdata['otype'] == '4B') ? 'INPUT' : 'SELECT',
                    'name' => stripslashes($optdata['name']),
                );
                if($optdata['otype'] == '4B') {
                    $optitem = $pdo->assoc("select max_val from {$tbl['product_option_item']} where opno='{$optdata['no']}'");
                    if ($optitem['max_val'] > 0) {
                        $temp['maxLangth'] = $optitem['max_val'];
                    }
                } else {
                    if ($optdata['necessary'] == 'N') { // 선택 옵션 '선택 안함' 생성
                        $temp['values'][] = array(
                            'id' => 'exclude_product_option',
                            'text' => '선택안함',
                            'status' => 'true'
                        );

                        $ocache['exclude_product_option'] = $ness_n[$optdata['no']][$optdata['no'].'x'] = array(
                            'name' => stripslashes($optdata['name']),
                            'value' => '선택안함',
                            'add_price' => 0
                        );
                    }
                    $ores2 = $pdo->iterator("
                        select no, iname, add_price, hidden from {$tbl['product_option_item']}
                        where pno='{$data['no']}' and opno='{$optdata['no']}'
                        order by sort asc
                    ");
                    foreach ($ores2 as $optitem) {
                        $temp['values'][] = array(
                            'id' => $optitem['no'],
                            'text' => stripslashes($optitem['iname']),
                            'status' => ($optitem['hidden'] == 'Y') ? 'false' : 'true',
                        );

                        $ocache[$optitem['no']] = array(
                            'name' => stripslashes($optdata['name']),
                            'value' => stripslashes($optitem['iname']),
                            'add_price' => $optitem['add_price']
                        );

                        if ($optdata['necessary'] == 'N') {
                            $ness_n[$optdata['no']][$optitem['no']] = $ocache[$optitem['no']];
                        }
                    }
                }
                $product['option']['optionItems'][] = $temp;
            }
            // 선택옵션 조합
            foreach ($ness_n as $oval) {
                $_tmp = $n_opts;
                foreach ($oval as $ino => $ival) {
                    if (count($n_opts) == 0) {
                        $_tmp[$ino] = $ino;
                    } else {
                        foreach ($n_opts as $key => $val) {
                            $_tmp[$ino.'_'.$key] = $ino.'_'.$key;
                            unset($_tmp[$key]);
                        }
                    }
                }
                $n_opts = $_tmp;
            }
            if (count($n_opts) == 0) $n_opts[''] = '';

            // complex_option
            $cres = $pdo->iterator("select complex_no, if (force_soldout='N', 9999, qty) as qty, opts from erp_complex_option where pno='{$data['no']}' and del_yn='N'");
            foreach ($cres as $cdata) {
                $_opts = explode('_', trim($cdata['opts'], '_'));
                foreach ($n_opts as $optional) {
                    $options_tmp = array();
                    $opts = array_merge($_opts, explode('_', $optional));
                    if (count($opts) == 0) {
                        continue;
                    }

                    $add_price = 0;
                    $manage_code_n = array();
                    foreach ($opts as $key => $ino) {
                        if (!$ocache[$ino]) {
                            continue;
                        }
                        $options_tmp[] = array(
                            'name' => $ocache[$ino]['name'],
                            'id' => $ino,
                        );
                        $add_price += (int) $ocache[$ino]['add_price'];
                        $manage_code_n[] = $ino;
                    }
                    sort($manage_code_n);
                    $manage_code_n = implode('x', $manage_code_n);
                    $manageCode = $cdata['complex_no'].'x'.$manage_code_n;

                    if ($add_price > 0 && $data['sell_prc'] > 0) {
                        $opt_per = $add_price/$data['sell_prc'];
                        $option_prc = floor($product['basePrice']*$opt_per);
                        $add_price = $option_prc;
                    }

                    if ($cdata['qty'] < 0) $cdata['qty'] = 0;
                    $product['option']['combinations'][] = array(
                        'manageCode' => $manageCode,
                        'price' => parsePrice($add_price),
                        'stockQuantity' => $cdata['qty'],
                        'status' => 'true',
                        'options' => $options_tmp
                    );
                }
            }

            unset($ocache);
        }
    }

    // 상품 고시 정보
    $announcement = $talkpay->getAnnoucement($data['no']);
    if (is_array($announcement) == true) {
        $product['noticeInfo'] = array(
            'noticeCategory' => $announcement['name'],
            'values' => $announcement['items'],
        );
    }

    $list[] = $product;
}

header('Content-type: application/json');
exit(json_encode(
    array(
        'shopKey' => $scfg->get('talkpay_ShopKey'),
        'products' => $list,
    ),
    (defined('JSON_PRETTY_PRINT') == true) ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES : null
));