<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  [매장지도]
	' +----------------------------------------------------------------------------------------------+*/

//해당 폼 생성
$_replace_code[$_file_name]['store_location_form_start'] = "<form name=\"locationFrm\" onKeydown=\"javascript:if(event.keyCode == 13) gpsKakaoMap({'type':'list'});\" onsubmit=\"return false;\">";
$_replace_code[$_file_name]['store_location_form_end'] = "</form>";

//[검색 조건]
$_tmp = '';
$_line = getModuleContent('store_search_list');
$_arr_search_key = array('title'=>'상호명', 'addr1'=>'주소');
foreach($_arr_search_key as $skey => $sval ) {
	$data['key'] = $skey;
	$data['name'] = $sval;
	$_tmp .= lineValues("store_search_list", $_line, $data);
}
$tmp = listContentSetting($_tmp, $_line);
$_replace_code[$_file_name]['store_search_list'] = $tmp;

//[지역 리스트]
$_tmp = '';
$_line = getModuleContent('store_sido_list');
foreach($_arr_sido as $location) {
	$data['name'] = $location;
	$data['selected'] = ($location == $sido) ? 'selected':'';
	$_tmp .= lineValues("store_sido_list", $_line,$data);
}
$tmp = listContentSetting($_tmp, $_line);
$_replace_code[$_file_name]['store_sido_list'] = $tmp;

//[시설안내 리스트]
$_tmp = '';
$_line = getModuleContent('store_location_facility_list');
$fsql = "select no, sort, name from {$tbl['store_facility_set']} where 1 order by sort asc";
$fres = $pdo->iterator($fsql);
foreach($fres as $fd) {
	$_tmp .= lineValues("store_location_facility_list", $_line,$fd);
}
$tmp = listContentSetting($_tmp, $_line);
$_replace_code[$_file_name]['store_location_facility_list'] = $tmp;
unset($_tmp,$fsql,$fres,$tmp,$_line,$fd,$data);

//[검색 값]
$_replace_code[$_file_name]['store_location_search_str'] = $search_str;
if($cfg['use_kakao_location'] == 'Y') {
		$_replace_code[$_file_name]['store_location_api_start'] = "
	<div id=\"shop_detail\"></div>
	<script type=\"text/javascript\">
		//시청 기준
		let lat = 126.9779451;
		let lng = 37.5662952;
	
		//중심 좌표 설정 값
		if(gps_center_lat && gps_center_lng ) {
			lat = gps_center_lat;
			lng = gps_center_lng;
		}

		const mapContainer = document.getElementById('map');
		const mapOption = {
			center: new kakao.maps.LatLng(lat, lng),
			level: 9
		};
		const map = new kakao.maps.Map(mapContainer, mapOption);
	</script>
	<script type=\"text/javascript\" src=\"" . $engine_url . "/_engine/common/kakao_location.js?" . date('Ymdhis') . "\"></script>";
		$_replace_code[$_file_name]['store_location_api_start'] .= "<script type=\"text/javascript\">
		//navigator.geolocation.getCurrentPosition(GpsSuccess, GpsFail);
		gpsKakaoMap({'type':'list'});
	</script>";
}

//$_line = getModuleContent('store_location_type');
//$_replace_code[$_file_name]['store_location_type'] = $tmp = listContentSetting(lineValues("store_location_type", $_line,""), $_line);

//[네이버 추후 개발]
//if($cfg['use_naver_location'] == 'Y') {
//	$_replace_code[$_file_name]['store_naver_location_api_start'] = "
//	<div id=\"shop_detail\"></div>
//	<script type=\"text/javascript\">
//		const mapContainer = document.getElementById('map');
//		const mapOption = {
//			center: new naver.maps.LatLng(37.541, 126.986),
//			zoom: 12
//		};
//
//		const map = new naver.maps.Map(mapContainer,mapOption);
//	</script>
//	<script type=\"text/javascript\" src=\"" . $engine_url . "/_engine/common/naver_location.js?" . date('Ymdhis') . "\"></script>
//	<script type=\"text/javascript\">naverMapList();</script>";
//}
?>