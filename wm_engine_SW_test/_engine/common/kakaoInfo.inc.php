<?PHP
/* +----------------------------------------------------------------------------------------------+
' |  [매장지도] 상세내용
' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/file.lib.php';
include_once $engine_dir.'/_engine/include/design.lib.php';


$reciveData = json_decode(file_get_contents('php://input'), true);
if($reciveData) $_POST = $reciveData;
$exec = $_POST['exec'];

if(!$exec) msg("잘못 된 접근 입니다.");

printAjaxHeader();
$_skin = getSkinCfg();

//[미사용중]
if($exec == 'layer') {
	$no = numberOnly($_POST['no']);
	if (!$no) msg("잘못 된 접근 입니다.");

	$sl = $pdo->assoc("select * from {$tbl['store_location']} where no=:no", array(':no' => $no));

	$line = getModuleContent('store_location_info');

	$_replace_code['common']['store_location_info'] = '';
	$_replace_hangul['common']['store_location_info'] = '매장상세보기';
	$_replace_datavals['common']['store_location_info'] = '매장명:title;전화번호:phone;주소1:addr1;주소2:addr2;기타내용:content;아이콘:store_location_info_icons;시설안내리스트:facility_list;영업시간:operate_list;';

	$_replace_code['common']['store_location_info_facility_list'] = '';
	$_replace_hangul['common']['store_location_info_facility_list'] = '시설안내리스트';
	$_code_comment['common']['store_location_info_facility_list'] = '시설안내 리스트 출력';
	$_replace_datavals['common']['store_location_info_facility_list'] = '순서:sort;이름:name;시설이미지:fimg;';

	$_replace_code['common']['store_location_operate_list'] = '';
	$_replace_hangul['common']['store_location_operate_list'] = '영업시간리스트';
	$_auto_replace['common']['store_location_operate_list'] = 'Y';
	$_replace_datavals['common']['store_location_operate_list'] = '시작여부:use;요일:week;종일:atime;시작시간:shour;마감시간:ehour;브레이크시작여부:break_use;브레이크시작시간:break_shour;브레이크마감시간:break_ehour;';


	for ($i = 1; $i <= 3; $i++) {
		$_replace_datavals['common']['store_location_info'] .= "매장이미지".$i.":img".$i.";";
		$sl['img' . $i] = $sl['upfile' . $i] ? "<img src=" . getFileDir($sl['updir']) . "/" . $sl['updir'] . "/" . $sl['upfile' . $i] . ">" : "<img src=" . $root_url . "/".$cfg['store_noimg'].">";
	}

	$tmp = $_tmp = '';
	$_line = getModuleContent('store_location_operate_list');
	$ores = $pdo->iterator("select * from {$tbl['store_operate']} where sno=:sno", array(':sno'=>$sl['no']));

	foreach($ores as $so) {
		$so['use'] = ($so['ouse'] == 'Y') ? 'Y':'';
		$so['break_use'] = ($so['break_use'] == 'Y') ? 'Y':'';
		$so['week'] = $_schedul_week_config[$so['week']].'요일';

		$so['shour'] = ($so['all_time'] == '') ? 'AM '.$so['shour']:'';
		$so['ehour'] =  ($so['all_time'] == '') ? 'PM '.$so['ehour']:'';
		$so['atime'] =  ($so['all_time'] == 'Y') ? '24시간':'';

		$so['break_shour'] = 'AM '.$so['break_shour'];
		$so['break_ehour'] = 'PM '.$so['break_ehour'];

		$_tmp .= lineValues("store_location_operate_list", $_line, $so, 'common');
	}

	$tmp = listContentSetting($_tmp, $_line);
	//$sl['operate_list'] = $tmp;
	unset($tmp,$ores,$so,$_line, $tmp, $_tmp);

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
	$sl['store_location_info_icons'] = $icon_img;

	//시설 안내 리스트
	$tmp = $_tmp = '';
	$_line = getModuleContent('store_location_info_facility_list');

	$fsql = "select sort, name, updir, upfile1 from {$tbl['store_facility_set']} where 1 order by sort asc";
	$fres = $pdo->iterator($fsql);
	foreach($fres as $fd) {
		$fd_img = ($fd['upfile1']&&$cfg['store_facility_icon_config'] == 'A') ? $root_url."/".$fd['updir']."/".$fd['upfile1'] : "";
		$fd['fimg'] = $fd_img;

		$_tmp .= lineValues("store_location_info_facility_list", $_line,$fd, 'common', '1');
	}

	$tmp = listContentSetting($_tmp, $_line);
	$sl['facility_list'] = $tmp;

	$_tmp = lineValues('store_location_info', $line, $sl, 'common');
	echo json_encode(contentReset(listContentSetting($_tmp, $line), 'store_location_info'));
	unset($_tmp, $line, $tmp, $sl);
} else if($exec == 'kakaoList') {

	$_skin = getSkinCfg();

	$search_key = addslashes(trim(urldecode($_POST['search_key'])));
	$search_str = addslashes(trim(urldecode($_POST['search_str'])));
	$facility = addslashes(trim(urldecode($_POST['facility'])));
	$gps_lat = urldecode($_POST['gps_lat']);
	$gps_lng = urldecode($_POST['gps_lng']);
	$no = numberOnly($_POST['no']);
	$type = addslashes(trim(urldecode($_POST['type'])));
	//필터
	$where="";
	$_qry_arr = array();

	//검색조건
	if($search_str) {
		$_qry_arr[':search_key'] = "%".$search_str."%";
		$where .= " and (l.title like :search_key or l.addr1 like :search_key or l.addr2 like :search_key)";
	}

	//시설 안내
	if($facility) {
		$facility = explode(',',$facility);
		$where .= "and ";
		foreach($facility as $k => $v) {
			$_qry_arr[':facility'.$v] = "%@".$v."%";
			$fwhere[] =  "(l.facility like :facility{$v})";
		}
		$where .= implode(' or ', $fwhere);
	}

	if($no) {
		$_qry_arr[':no'] = $no;
		$where .= " and l.no=:no";
	}

	//입점사 구분
	if($cfg['use_store_partner_yn'] == 'Y') {
		$where .= " and l.partner_no>0";
	}

	$_replace_code['common']['store_location_list'] = '';
	$_replace_hangul['common']['store_location_list'] = '매장리스트';
	$_code_comment['common']['store_location_list'] = '매장 리스트 출력';
	$_replace_datavals['common']['store_location_list'] = '번호:no;매장명:title;전화번호:cell;주소1:addr1;주소2:addr2;기타내용:content;위도:lat;경도:lng;순번:seq;아이콘:icon_img;길찾기:kakao_load_link;';
	$_replace_datavals['common']['store_location_list'] .= "영업상태:store_stat;영업상태표시:css_stat;전화번호아이콘:phone_icon;운영시간아이콘:operate_icon;거리표시:location_distance;";

	$_replace_code['common']['store_location_overlay_list'] = '';
	$_replace_hangul['common']['store_location_overlay_list'] = '오버레이리스트';
	$_code_comment['common']['store_location_overlay_list'] = '오버레이 리스트 출력';
	$_replace_datavals['common']['store_location_overlay_list'] = '번호:no;상호명:title;전화번호:cell;주소1:addr1;주소2:addr2;위도:lat;경도:lng;순번:seq;썸네일이미지링크:store_img1_link;길찾기:kakao_load_link;';
	$_replace_datavals['common']['store_location_overlay_list'] .= '운영시간:operate_hour;브레이크타임:break_hour;전화번호아이콘:phone_icon;운영시간아이콘:operate_icon;거리표시:location_distance;담기여부:wish_on;';
		// 아이콘파일 경로
	$conck = fsConFolder($dir['upload'].'/'.$dir['icon']);
	$file_dir = getFileDir($dir['upload'].'/'.$dir['icon']);
	$_preload_prdicons['url'] = $file_dir.'/'.$dir['upload'].'/'.$dir['icon'];

	// 아이콘파일
	$res = $pdo->iterator("select no, upfile, itype from {$tbl['product_icon']} where itype=:itype order by sort", array(':itype'=>9));
	foreach ($res as $data) {
		$_preload_prdicons[$data['no']] = $data['upfile'];
	}

	$_store_data = array();
	$_line = getModuleContent('store_location_list');
	$_overlay_line = getModuleContent('store_location_overlay_list');

	$gps_field = ", (6371 * ACOS(COS( RADIANS( {$gps_lat} )) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS( {$gps_lng} )) + SIN(RADIANS( {$gps_lat} )) * SIN(RADIANS(lat)))) AS distance";
	$_ow = "order by distance asc";

	if($type == 'wishList') {
		$_wjoin = " inner join {$tbl['store_wish']} w ON w.sno=l.no";
		$_qry_arr[':ws_no'] = $member['no'];
		$where .= " and w.member_no=:ws_no";
	}
	$sql = "select * $gps_field from {$tbl['store_location']} l $_wjoin where 1 $where $_ow";

	$res = $pdo->iterator($sql, $_qry_arr);
	$totalCount = $pdo->rowCount($sql, $_qry_arr);

	$seq=0;
	foreach($res as $data) {

		$data['content'] = stripslashes($data['content']);
		$data['seq'] = $seq;
		$operate = operateWeekList($data['no'], 'today');
		$operate = $operate[date('N')];

		$_css_stat = ($operate['css_stat']) ? $operate['css_stat']:2;
		$data['store_stat'] =  $_operate_stat_config[$_css_stat];
		$data['css_stat'] = ($_css_stat == 1) ? 1:2;
		$data['location_distance'] = number_format($data['distance'],1);
		$data['operate_hour'] = $operate['operate_hour'];
		$data['break_hour'] = $operate['break_hour'][0]['hour'];

		$data['phone_icon'] = ($data['phone']) ? '<img src="'.$engine_url.'/_manage/image/store/store_icon_phone.png">':"";
		$data['operate_icon'] = '<img src="'.$engine_url.'/_manage/image/store/store_icon_clock.png">';
		$data['phone'] = pregNumber($data['phone']);
		$data['cell'] = pregNumber($data['cell']);
		$icons = array();
		$icon_img = '';
		if($data['icons']) {
			$icons = explode('@', preg_replace('/^@|@$/','',$data['icons']));
			
			foreach($icons as $key => $val) {
				$icon_img .="<img src='".$_preload_prdicons['url']."/".$_preload_prdicons[$val]."' align='absmiddle'>";
			}
			$data['icon_img'] = $icon_img;
		}

		for ($i = 1; $i <= 3; $i++) {
			$data['store_img'.$i] = $data['upfile'.$i] ? "<img src=" . getFileDir($data['updir']) . "/" . $data['updir'] . "/" . $data['upfile' . $i] . ">" : "<img src=" . $root_url.$cfg['store_noimg'].">";
			$data['store_img'.$i.'_link'] = $data['upfile'.$i] ? getFileDir($data['updir']) . "/" . $data['updir'] . "/" . $data['upfile' . $i]: $root_url.$cfg['store_noimg'];
		}
		$_wish = $pdo->row("select no from {$tbl['store_wish']} where sno=:sno and member_no=:member_no", array(':sno'=>$data['no'], ':member_no'=>$member['no']));
		$data['wish_on'] = ($_wish) ? ' on':'';

		//단독 모듈일 경우 single_module 사용해야 if 처리 됨
		$_GET['single_module'] = 1;

		//커스텀 오버레이 표기
		$_overlay_tmp = $overlay_tmp = '';
		$_overlay_tmp .= lineValues("store_location_overlay_list", $_overlay_line,$data, 'common');
		$overlay_tmp = contentReset(listContentSetting($_overlay_tmp, $_overlay_line));

		$data['overlay'] = $overlay_tmp;

		$seq++;
		$_tmp .= lineValues("store_location_list", $_line,$data, 'common');
		$_store_data[] = $data;
	}
	$tmp = listContentSetting($_tmp, $_line);

	$_json_arr = array(
		'store_data'=>$_store_data,
		'store_list'=>$tmp,
		'store_total_count'=>($totalCount) ? $totalCount:'0'
	);

	exit(json_encode($_json_arr));
}
?>
