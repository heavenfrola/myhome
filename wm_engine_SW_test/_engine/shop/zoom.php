<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품이미지 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	// 기본 사항 체크
	$prd=checkPrd(addslashes($_GET['pno']));
	$prd['name']=stripslashes($prd['name']);


	common_header();

?>
<script type='text/javascript' src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>