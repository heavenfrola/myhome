<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 변경
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$cno = numberOnly($_GET['cno']);
	$prd = $pdo->assoc("
		select
			p.*,
			c.no, c.pno, c.option, c.buy_ea
			from $tbl[cart] c inner join $tbl[product] p on c.pno=p.no
		where c.no='$cno' ".mwhere('c.')
	);
	$prd = shortCut($prd);
	if(!$prd['no']) {
		msg(__lang_common_error_nodata__, 'close');
	}

	$prd['name'] = stripslashes($prd['name']);
	$prd['option'] = stripslashes(str_replace('<split_small>', ' : ', str_replace('<split_big>', ' / ', $prd['option'])));
	$prd['img'] = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile3'];

	if(!$_GET['from_ajax']) {
		common_header();
	}

	$_GET['striplayout'] = 1;
	$_tmp_file_name = 'shop_cart_chgOption.php';

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript">
var is_option_change = true;
</script>
<?php
	include_once $engine_dir.'/_engine/common/skin_index.php';

?>