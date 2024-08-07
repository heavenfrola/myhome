<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 배송지 변경
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$sno = addslashes($_GET['sno']);

    $order_type = 'sbscr';
    $sdata = $pdo->assoc("select * from {$tbl['sbscr']} where sbono=?", array($sno));
    if (!$sdata) {
        $order_type = 'order';
        $sdata = $pdo->assoc("select * from {$tbl['order']} where ono=?", array($sno));
    }
    if(!$sdata) msg("존재하지 않는 주문입니다.", "close");

    /*
    if ($sdata['stat'] > 2) {
        msg(__lang_common_error_modifyperm__, 'close');
    }
    */

	printAjaxHeader();

	$_tmp_file_name = '/mypage/sbscr_dlv_edit.php';
	$striplayout = $_GET['striplayout'] = 1;

	common_header();

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<script type="text/javascript">
$(function() {
	selfResize();
});
</script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>