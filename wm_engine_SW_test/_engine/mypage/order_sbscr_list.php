<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 정기주문내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

	memberOnly(1,"");
	$rURL=urlencode($this_url);

	common_header();

	$_tmp_file_name = '/mypage/order_sbscr_list.php';

?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>
