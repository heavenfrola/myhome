<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  [매장지도] 오프라인 매장 상세
	' +----------------------------------------------------------------------------------------------+*/

$_replace_code[$_file_name]['store_location_info_title'] = $sl['title'];
$_replace_code[$_file_name]['store_location_info_zipcode'] = $sl['zipcode'];
$_replace_code[$_file_name]['store_location_info_addr1'] = $sl['addr1'];
$_replace_code[$_file_name]['store_location_info_addr2'] = $sl['addr2'];
$_replace_code[$_file_name]['store_location_info_phone'] = pregNumber($sl['phone']);
$_replace_code[$_file_name]['store_location_info_cell'] = pregNumber($sl['cell']);
$_replace_code[$_file_name]['store_location_info_kakao_load'] = "https://map.kakao.com/link/to/".$sl['title'].",".$sl['lat'].",".$sl['lng'];
$_replace_code[$_file_name]['store_location_info_content'] = nl2br($sl['content']);

for ($i = 1; $i <= 3; $i++) {
	$_replace_code[$_file_name]['store_location_info_img'.$i] =  $sl['upfile' . $i] ? "<img src=" . getFileDir($sl['updir']) . "/" . $sl['updir'] . "/" . $sl['upfile' . $i] . ">" : "<img src=" . $root_url . "/".$cfg['store_noimg'].">";
}

//영업 시간 설정
$_operate_list = operateWeekList($sl['no'], 'list');
$od = array();
$tmp = $_tmp = '';
$_line = getModuleContent('store_location_operate_list');
$_bline = getModuleContent('store_location_operate_break_list');
foreach($_operate_list as $k => $_week) {
	$od['today'] = $_week['operate_week'];
	$od['hour'] = $_week['operate_hour'];
	$od['week_today'] = (date('N') == $k) ? "today":"";

	//브레이크 타임
	$btmp = $_btmp = '';
	foreach($_week['break_hour'] as $b => $bd) {
		$_btmp .= lineValues("store_location_operate_break_list", $_bline, $bd);
	}
	$btmp = listContentSetting($_btmp, $_bline);
	$od['break_time'] = $btmp;
	$_tmp .= lineValues("store_location_operate_list", $_line, $od);
}
$tmp = listContentSetting($_tmp, $_line);
$_replace_code[$_file_name]['store_location_operate_list'] = $tmp;
unset($_bline,$_line,$tmp,$_tmp,$_btmp,$btmp,$od, $ws);

//시설 안내 리스트
$tmp = $_tmp = '';
$_line = getModuleContent('store_location_info_facility_list');

if($sl['facility']) {
	$w = '';
	$facility = preg_replace('/^@|@$/', '', $sl['facility']);
	$w .= " and no in (".preg_replace('/@/',',', $facility).")";

	$fsql = "select sort, name, updir, upfile1 from {$tbl['store_facility_set']} where 1 $w order by sort asc";
	$fres = $pdo->iterator($fsql);
	foreach($fres as $fd) {

		$fd_img = $root_url."/".$fd['updir']."/".$fd['upfile1'];
		$fd['name'] = ($cfg['store_facility_icon_config'] == 'A')  ? $fd['name'] : "";
		$fd['fimg'] = $fd_img;

		$_tmp .= lineValues("store_location_info_facility_list", $_line,$fd);
	}

	$tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['store_location_info_facility_list'] = $tmp;
	unset($_tmp,$fsql,$fres,$tmp,$_line,$fd,$data);
}

// 아이콘파일 경로
$conck = fsConFolder($dir['upload'].'/'.$dir['icon']);
$file_dir = getFileDir($dir['upload'].'/'.$dir['icon']);
$_preload_prdicons['url'] = $file_dir.'/'.$dir['upload'].'/'.$dir['icon'];

// 아이콘파일
$res = $pdo->iterator("select no, upfile, itype from {$tbl['product_icon']} where itype=:itype order by sort", array(':itype'=>9));
foreach ($res as $data) {
	$_preload_prdicons[$data['no']] = $data['upfile'];
}
$icons = array();
$icon_img = '';
if($sl['icons']) {
	$icons = explode('@', preg_replace('/^@|@$/','',$sl['icons']));

	foreach($icons as $key => $val) {
		$icon_img .="<img src='".$_preload_prdicons['url']."/".$_preload_prdicons[$val]."' align='absmiddle'> ";
	}
}
$_replace_code[$_file_name]['store_location_info_icons'] = $icon_img;

$_replace_code[$_file_name]['store_location_info_kakao_map'] = "
	<script type=\"text/javascript\">
		const mapContainer = document.getElementById('map');
		const mapOption = {
			center: new kakao.maps.LatLng(37.5662952, 126.9779451),
			level: 9
		};
		const mlat = \"{$sl['lat']}\";
		const mlng = \"{$sl['lng']}\";
		const mno = \"{$sl['no']}\";

		const map = new kakao.maps.Map(mapContainer, mapOption);
	</script>
	<script type=\"text/javascript\" src=\"" . $engine_url . "/_engine/common/kakao_location.js?" . date('Ymdhis') . "\"></script>
	<script type=\"text/javascript\">kakaoMapView({'lat':mlat, 'lng':mlng, 'no':mno});</script>";

?>