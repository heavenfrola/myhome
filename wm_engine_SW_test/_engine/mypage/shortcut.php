<?php

/**
 * 마이페이지 주문상세 바로가기
 **/

require_once $engine_dir.'/_engine/include/common.lib.php';

$hash = $_GET['hash'];
$ord = $pdo->assoc("select ono, buyer_cell from {$tbl['order']} where hash=?", array($hash));

if ($ord == false) {
    header("Location: {$root_url}/mypage/order_list.php");
    exit;
}

$_SESSION['my_order'] = $_SESSION['od_ono'] = $ord['ono'];

$_REQUEST['ono'] = $ord['ono'];
$_REQUEST['phone'] = $ord['buyer_cell'];
$_tmp_file_name = 'mypage_order_detail.php';
require_once 'order_detail.php';