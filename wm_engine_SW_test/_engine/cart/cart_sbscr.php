<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 장바구니 리스트 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$cart_sum_price = 0;

	common_header();

	$_tmp_file_name = '/shop/cart_sbscr.php';

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js?20180523"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<script type="text/javascript">
$(function() {
	if($('form[name=cartSbscrFrm]').find(":checkbox").length>0) {
		$('form[name=cartSbscrFrm]').find(":checkbox").attrprop('checked', true);
	}
	$('form[name=cartSbscrFrm]').find(":checkbox").change(function() {
		cartLiveCalc(this.form);
	});
});
</script>
<?php include_once $engine_dir."/_engine/common/skin_index.php"; ?>