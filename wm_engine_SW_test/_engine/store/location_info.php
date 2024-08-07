<?php
/* +----------------------------------------------------------------------------------------------+
' |  [매장지도] 오프라인 매장 상세
' +----------------------------------------------------------------------------------------------+*/
include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";

$no = numberOnly($_GET['no']);
if (!$no) msg("잘못 된 접근 입니다.");

$sl = $pdo->assoc("select * from {$tbl['store_location']} where no=:no",
    array(':no' => $no)
);

//이미지 출력
$_img_arr = array(
	'1'=>'썸네일',
	'2'=>'커버',
	'3'=>'매장'
);

common_header();

?>

<?php if($cfg['use_kakao_location'] == 'Y') {?>

	<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?php echo $cfg['use_kakao_location_key'];?>&libraries=services,clusterer,drawing"></script>
	<!--    <script type="text/javascript" src="--><?php //echo $engine_url;?><!--/_engine/common/kakao_location.js?--><?php //echo $now;?><!--"></script>-->
<?php } ?>

<?php if($cfg['use_naver_location'] == 'Y') {?>
	<script type="text/javascript" src="https://openapi.map.naver.com/openapi/v3/maps.js?ncpClientId=ukpyms8pk7"></script>
<?php } ?>

<?php
// 디자인 버전 점검 & 페이지 출력
include_once $engine_dir."/_engine/common/skin_index.php";
?>