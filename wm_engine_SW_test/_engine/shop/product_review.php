<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$pno = addslashes($_GET['pno']);
	$rno = numberOnly($_GET['rno']);

	if(!$pno && !$rno) msg("",$root_url."/shop/product_review_list.php");

	if($pno) {
		$prd = checkPrd($pno, 1);
		$prd['name'] = stripslashes($prd['name']);
		$prd['milage'] = number_format($prd['milage']);
		$prd['sell_prc_str'] = number_format($prd['sell_prc']); // 판매가
		$prd['normal_prc'] = number_format($prd['normal_prc']); // 시중가
		if($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) {
			$prd['milage'] = $prd['sell_prc'] * ($cfg['milage_type_per']/100);
		}
	}

	if(!$listURL) {
		$listURL = $root_url."/shop/product_review_list.php";
	}

	// 상품평 권한
	$ra = reviewAuth('product_review_auth');
	$all_rev = 1;
	common_header();

	if(!$cfg['product_review_con_strlen']) $cfg['product_review_con_strlen'] = 0;

    $ono = (isset($_GET['ono']) == true) ? addslashes($_GET['ono']) : null;

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/ajax.js"></script>
<script type="text/javascript">
<!--
var ra='<?=$ra?>';
var review_strlen=<?=$cfg['product_review_strlen']?>;
var review_con_strlen=<?=$cfg['product_review_con_strlen']?>;
var hid_now='<?=$now?>';
var ono = '<?=$ono?>';

<?if($_GET['startup'] == 'true') {?>
$(document).ready(function() {
	writeReview();
});
<?}?>
//-->
</script>
<?
	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>