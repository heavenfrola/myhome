<?php

/**
 * 네이버쇼핑 도서 EP
 **/

include_once __ENGINE_DIR__.'/_engine/include/cart.class.php';

if (defined('JSON_UNESCAPED_UNICODE') == false) { // PHP 5.4 미만
    define('JSON_UNESCAPED_UNICODE', 0);
}

// 예외 카테고리
$exists_cno = $pdo->row("select group_concat(no) from {$tbl['category']} where private='Y' or hidden='Y'");
if ($exists_cno) {
    $add_qry .= " and (";
    $add_qry .= " p.big not in ($exists_cno) and p.mid not in ($exists_cno) and p.small not in ($exists_cno)";
    if($cfg['max_cate_depth'] >= 4) {
        $add_qry .= " and p.depth4 not in ($exists_cno)";
    }
    $add_qry .= ")";
}
if($feedtype == '4') {
    $add_qry .= " and edt_date > '".strtotime(date('Y-m-d 00:00:00'))."'";
    $search_stat = '2, 3';
} else {
    $search_stat = '2';
}

// 카테고리명 캐시
$ccache = array();
$res = $pdo->iterator("select no, name from {$tbl['category']} where ctype=1");
foreach ($res as $data) {
    $ccache[$data['no']] = stripslashes($data['name']);
}

// 조회 필드
$fd  = 'p.no, p.hash, p.is_book, p.content1, p.normal_prc, p.sell_prc, p.big, p.mid, p.small, p.updir, p.upfile1, p.upfile2, p.upfile3, p.upfile'.$cfg['compare_image_no'].', p.edt_date';
$fd .= ', b.isbn, b.is_used, b.title, b.number, b.version, b.original_title, b.subtitle, b.author, b.publisher';
if($cfg['max_cate_depth'] >= 4) {
    $fd .= ', p.depth4';
}

$res = $pdo->iterator("
    select
        $fd
    from {$tbl['product']} p inner join {$tbl['product_book']} b using(no)
    where p.stat in ($search_stat) and p.is_book!='N' $add_qry
    order by no asc
");
foreach ($res as $data) {
    $data['normal_prc'] = parsePrice($data['normal_prc']);
    $data['sell_prc'] = parsePrice($data['sell_prc']);
    $data['point'] = ($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) ? ($data['sell_prc']/100)*$cfg['milage_type_per'] : $data['milage'];

    if(!$data['upfile'.$cfg['compare_image_no']]) {
        for($i = 1; $i <= 3; $i++) {
            if($data['upfile'.$i]) {
                $data['upfile'.$cfg['compare_image_no']] = $data['upfile'.$i];
                break;
            }
        }
    }

    $prdCart = new OrderCart();
    $prdCart->addCart($data);
    $prdCart->complete();

    $line = array(
        'id' => $data['hash'],
        'goods_type' => $data['is_book'],
        'used' => $data['is_used'],
        'isbn' => $data['isbn'],
        'title' => $data['title'].'^'.$data['number'].'^'.$data['version'],
        'subtitle' => $data['subtitle'],
        'original_title' => $data['original_title'],
        'description' => $data['content1'],
        'normal_price' => $data['normal_price'],
        'price_pc' => $data['sell_prc'],
        'point' => $cfg['milage_name'].'^'.$data['point'].'^'.$cfg['milage_type_per'],
        'link' => $root_url.'/shop/detail.php?pno='.$data['hash'],
        'mobile_link' => $m_root_url.'/shop/detail.php?pno='.$data['hash'],
        'category_name1' => $ccache[$data['big']],
        'category_name2' => $ccache[$data['mid']],
        'category_name3' => $ccache[$data['small']],
        'category_name4' => $ccache[$data['depth4']],
        'image_link' => getListImgURL($data['updir'], $data['upfile3']),
        'author' => $data['author'],
        'publisher' => $data['publisher'],
        'description' => $data['description'],
        'publish_day' => $data['publish_day'],
        'shipping' => ($prdCart->cod_prc > 0 && $prdCart->dlv_prc == 0) ? -1 : $prdCart->dlv_prc,
    );
    if ($feedtype == '4') {
        $line['update_time'] = date('Y-m-d H:i:s', $data['edt_date']);
        $line['class'] = (date('Ymd', $data['edt_date']) == date('Ymd')) ? 'I' : 'U';
    }

    echo json_encode($line, JSON_UNESCAPED_UNICODE);
    echo "\n";
}