<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  카카오페이 결제 취소
' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";

$wm_paydata = comm('https://pghub.wisa.co.kr/kakaopay.api.php?wm_paykey='.$_GET['wm_paykey']);
$wm_paydata = json_decode($wm_paydata);
$ono = $wm_paydata->ono;

$card_tbl = $tbl['card'];
$card = $pdo->assoc("select * from `$card_tbl` where wm_ono='$ono'");

//사용자가 취소시
$adminkey  = $cfg['kaka_admin_key']; // admin 키
$cid       = $cfg['kakao_cid']; // cid
$tid       = $card['tno']; // tid

$req_auth = 'Authorization: KakaoAK '.$adminkey;
$req_cont = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

$kakao_header = array($req_auth, $req_cont);

$kakao_params = array(
	'cid'  => $cid,                             // cid
	'tid'  => $tid,						     // tid
);

$Result = comm('https://kapi.kakao.com/v1/payment/order', http_build_query($kakao_params), '', $kakao_header);

$result_json = json_decode($Result, true);

if($result_json['status']=='QUIT_PAYMENT') {
	$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
	$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
	$pdo->query("update $card_tbl set stat='3' where wm_ono='$ono'");

	if($_SESSION['browser_type'] == 'mobile') {
		msg('결제가 취소되었습니다.', '/shop/order.php');
	}else {
?>
	<script type='text/javascript'>
		parent.$("#kakaopay_layer").css("display","none");
		window.alert('결제가 취소되었습니다.');
        parent.removeDimmed();
        parent.layTgl3('order1', 'Y');
        parent.layTgl3('order2', 'N');
        parent.layTgl3('order3', 'Y');
	</script>
<?
	}
	exit;
}
?>