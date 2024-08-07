<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 주문내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	memberOnly(1,"");
	$rURL=urlencode($this_url);

	$sbscr = ($_GET['sbscr']=='Y')? 'Y':'N';
	if($sbscr=='Y') {
		include_once $engine_dir."/_engine/mypage/order_sbscr_list.php";
		return;
	}

	common_header();

?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.datepicker').datepicker(date_picker_default);
});
</script>
<?

	if($cfg['card_pg'] == 'inicis' && $cfg['pg_version'] == 'INILite') include_once $GLOBALS['engine_dir']."/_engine/card.inicis/INILite/confirm_frm.php";
	if($cfg['card_pg'] == 'allat') include_once $engine_dir.'/_engine/card.allat/confirm_frm.php';

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>
