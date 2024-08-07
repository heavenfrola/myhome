<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$pno = addslashes($_GET['pno']);
	$rno = numberOnly($_GET['rno']);
	$cate = numberOnly($_GET['cate']);

	if(!$pno && !$rno) msg("",$root_url."/shop/product_qna_list.php");

	if($pno) {
		$prd=checkPrd($pno,1);
		$prd[name]=stripslashes($prd[name]);
		$prd['milage']=number_format($prd['milage']);
		$prd['sell_prc_str']=number_format($prd['sell_prc']); // 판매가
		$prd['normal_prc']=number_format($prd['normal_prc']); // 시중가
		if($prd[stat]==2) $prd[stack_ok]=1; // 상품 품절 2007-02-05
		if($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) {
			$prd['milage'] = $prd['sell_prc'] * ($cfg['milage_type_per']/100);
		}
	}

	// 권한
	$qa=reviewAuth('product_qna_auth');
	$all_qna=1;
	common_header();

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/ajax.js"></script>
<script type='text/javascript'>
<!--
var qa='<?=$qa?>';
var qna_strlen='<?=$cfg[product_qna_strlen]?>';
var hid_now='<?=$now?>';
//-->
</script>
<?
	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>