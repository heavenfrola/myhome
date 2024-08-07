<?php

/**
 * ������� ��� �Ϸ� ó��
 **/

use Wing\API\Naver\NaverSimplePay;

include_once $engine_dir."/_engine/include/common.lib.php";

// ���� â���� ���� ��� ����
$resultCode = $_GET['resultCode'];
$resultMessage = $_GET['resultMessage'];
$reserveId = $_GET['reserveId'];
$tempReceiptId = $_GET['tempReceiptId'];
$ono = $sno = $_GET['ono'];

startOrderLog($ono, 'subscription.exe.php');

// ���� ����
$pay = new NaverSimplePay($scfg, 'Y');

$redir_url = $root_url.'/shop/order.php?sbscr=Y';
if ($_SESSION['nsp_cart_selected']) {
    $redir_url .= '&cart_selected='.$_SESSION['nsp_cart_selected'];
    unset($_SESSION['nsp_cart_selected']);
}

if ($resultCode != 'Success') {
   msg($pay->resultMessage($resultMessage), $redir_url);
}
$ret = $pay->recurrentRegist($reserveId, $tempReceiptId);
$code = $ret->code;
$message = $ret->message;

if ($ret->code != 'Success') {
   msg($ret->message, $redir_url);
}

// Ű ����
$pdo->query("
    insert into {$tbl['subscription_key']}
    (pg, ono, reserveId, tempReceiptId, recurrentId, reg_date)
    values ('nsp', ?, ?, ?, ?, now())
", array(
    $ono, $ret->body->reserveId, $ret->body->tempReceiptId, $ret->body->recurrentId
));

// ���� �α� ����
$pdo->query("
    update {$tbl['card']} set
        stat='2', res_cd='0000', res_msg=?, ordr_idxx=?, tno=?
    where wm_ono=?
", array(
    $ret->code, $ono, $ret->body->recurrentId, $sno
));

$pdo->query("update {$tbl['sbscr']} set billing_key='{$ret->body->recurrentId}' where sbono='$sno'");

// ���� �������� �̵�
$sbscr = 'Y';
require __ENGINE_DIR__.'/_engine/order/order2.exe.php';