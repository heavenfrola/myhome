<?php

/**
 * 구글 판매자센터 피드 갱신
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

$starttime = microtime(true);

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/wingPos.lib.php';
include_once $engine_dir.'/_engine/include/file.lib.php';

if ($_REQUEST['site_key'] != $_we['wm_key_code']) {
    exit('Site keys do not match.');
}

// 피드 파일
define('__FEED_PATH__', '_data/compare/google/merchants_feed.txt');
makeFullDir(dirname(__FEED_PATH__));

// 브랜드명 캐시
if ($cfg['ge_brand'] == 'xbig' || $cfg['ge_brand'] == 'ybig') {
    $ctype = ($cfg['ge_brand'] == 'xbig') ? 4 : 5;
    $brandcache = getCategoriesCache($ctype);
}

// feed 구조
$feed_struct = array(
    'id', 'title', 'description', 'price', 'sale_price', 'condition', 'link', 'image_link', 'availability'
);
$currency_type = $cfg['currency_type'];
if (!$currency_type || $currency_type == '원') $currency_type = 'KRW';

if($cfg['ge_brand']) {
    $feed_struct[] = 'brand';
}

// header
$fp = fopen($root_dir.'/'.__FEED_PATH__, 'w');
fputcsv($fp, $feed_struct, "\t");

// body
$add_field  = '';
$add_field .= ", upfile{$cfg['ge_image_no']} as upfile";

$w = '';
if ($scfg->comp('compare_explain', 'Y') == true) {
    $w .= " and p.no_ep!='Y'";
}

$res = $pdo->iterator("
    select no, hash, name, updir, sell_prc, milage, free_delivery as free_dlv, xbig, ybig, content1 $add_field
	from $tbl[product] p
    where prd_type='1' and stat=2 and wm_sc=0 and upfile{$cfg['ge_image_no']}!='' $w
");
foreach ($res as $data) {
    $prdCart = new OrderCart();
    $prdCart->addCart($data);
    $prdCart->complete();

    $feed['id'] = $data['no'];
    $feed['title'] = stripslashes(strip_tags($data['name']));
    $feed['description'] = trim($data['content1']) ? trim($data['content1']) : $feed['title'];
    $feed['description'] = preg_replace("/\t|\r|\n/", '', stripslashes(strip_tags($feed['description'])));
    $feed['price'] = ($data['normal_prc']) ? parsePrice($data['normal_prc']) : parsePrice($data['sell_prc']);
    $feed['sell_price'] = parsePrice($data['sell_prc']);
    $feed['condition'] = 'new';
    $feed['link'] = $root_url.'/shop/detail.php?pno='.$data['hash'];
    $feed['image_link'] = getListImgURL($data['updir'], $data['upfile']);
    $feed['availability'] = (isWingposStock($data['no']) > 0) ? 'in_stock' : 'out_of_stock';

    $feed['price'] .= ' '.$currency_type;
    $feed['sell_price'] .= ' '.$currency_type;

    if($cfg['ge_brand']) {
        switch($cfg['ge_brand']) {
            case 'xbig' : $feed['brand'] = $brandcache[$data['xbig']]; break;
            case 'ybig' : $feed['brand'] = $brandcache[$data['ybig']]; break;
            default :
                list($dummy, $fno) = explode('@', $cfg['ge_brand']);
                $feed['brand'] = $pdo->row("select value from {$tbl['product_filed']} where fno='$fno' and pno='{$data['no']}'");
                $feed['brand'] = stripslashes($feed['brand']);
            break;
        }
    }
    fputcsv($fp, $feed, "\t");
}
fclose($fp);

echo(json_encode(array(
    'status' => 'completed', 'elapsed' => round(microtime(true)-$starttime, 3), 'count' => $res->rowCount()
)));