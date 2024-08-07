<?php

/**
 * crema 일일 크론
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

$starttime = microtime(true);

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/rest_api.class.php';
include_once $engine_dir.'/_engine/api/class/crema.api.php';

$crema = new cremaAPI();

$date1 = strtotime(date('Ymd', strtotime("-1 days")));
$date2 = $date1+86399;
$p = $o = array();

// 상품 정보 전송
$rs = $pdo->iterator("
    select no from {$tbl['product']}
    where
        prd_type=1 and wm_sc=0
        and (edt_date2 between $date1 and $date2 or reg_date between $date1 and $date2 or edt_date between $date1 and $date2)
");
foreach ($rs as $row) {
    $crema->createProduct($row['no']);
    $p[] = $row['no'];
}

// 배송 및 반품 주문 정보 전송
$asql = '';
if ($scfg->comp('crema_non_member', 'Y') == false) {
    $asql .= " and o.member_no > 0";
}
$rs = $pdo->iterator("
select op.no, op.ono, op.stat
    from
        {$tbl['order_product']} op inner join {$tbl['order_product_log']} l on op.no=l.opno
        inner join {$tbl['order']} o on o.ono=op.ono
    where
    	(op.stat=4 and l.reg_date between $date1 AND $date2 and l.stat=4) or
    	(op.stat=5 and l.reg_date between $date1 AND $date2 and l.stat=5) or
    	(op.stat=17 and l.reg_date between $date1 AND $date2 and l.stat=17)
        $asql
    group by o.ono
");
echo $pdo->geterror();
foreach ($rs as $row) {
    $crema->createOrder($row['ono']);
    $rs2 = $pdo->iterator("select no from {$tbl['order_product']} WHERE ono='{$row['ono']}' and stat between 4 and 10");
    foreach ($rs2 as $row2) {
        $crema->createOrderProduct($row2['no']);
    }
    $o[] = $row['ono'];
}

exit(json_encode(array(
    'elapsed' => microtime(true)-$starttime,
    'result' => array(
        'products' => $p,
        'orders' => $o,
    )
)));