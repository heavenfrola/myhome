<?php
/* +----------------------------------------------------------------------------------------------+
' |  [매장지도]
' +----------------------------------------------------------------------------------------------+*/
include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";


$cno1 = numberOnly($_GET['cno1']);
$cno2 = numberOnly($_GET['cno2']);
$sort = numberOnly($_GET['sort']);
$sido = addslashes(trim($_GET['sido']));
$search_str = addslashes(trim($_GET['search_str']));

//지역 리스트
$_arr_sido = $_kakao_store_handler->getStoreAddr('sido');

// 페이징 설정
include_once $engine_dir."/_engine/include/paging.php";

common_header();

?>

<?php if($cfg['use_kakao_location'] == 'Y') {?>
	<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?php echo $cfg['use_kakao_location_key'];?>&libraries=services,clusterer,drawing"></script>
<?php } ?>

<?php //if($cfg['use_naver_location'] == 'Y') {?>
<!--	<script type="text/javascript" src="https://openapi.map.naver.com/openapi/v3/maps.js?ncpClientId=ukpyms8pk7"></script>-->
<?php //} ?>

<?php
// 디자인 버전 점검 & 페이지 출력
include_once $engine_dir."/_engine/common/skin_index.php";
?>