<?PHP
/**
 * 쿼리 저장
 */
function qryResult($arr = array(), $warr = array())
{
	//[insert]
	$_q = $_v = $_u  = $_varr = array();
	foreach ($arr as $key => $val) {
		$val = (isset ($val) == false) ? '' : $val;


		$_q[] = $key;
		$_v[] = ':'.$key;
		$_u[] = $key . '=:' . $key; //[update]
		$_varr[':' . $key] = $val;
	}

	$return['i'] = implode(',', $_q);
	$return['v'] = implode(',', $_v);

	//[where]
	if (count($warr) > 0) {
		foreach ($warr as $key => $val) {
			$_w[] = $key . '=:' . $key;
			$_varr[':' . $key] = $val;
		}
		$return['w'] = implode(' and ', $_w);

	}

	$return['u'] = implode(',', $_u);
	$return['s'] = implode(' and ', $_u);
	$return['a'] = $_varr;

	return $return;
}

/* +----------------------------------------------------------------------------------------------+
' |  string radioArray(array 배열데이터, string 객체이름, int 사용값종류,  string 옵션이름 , string 제목옵션, string 선택된값, string onchange스크립트)
' +----------------------------------------------------------------------------------------------+*/
function radioNewArray($array,$name,$valtype=1,$blank="", $select="",$onClick="") {
	if(is_array($array) == false) return;
	$str="";
	if($blank) {
		$str.="<label><input type=\"radio\" name=\"$name\" value=\"\" checked>$blank</label>";
	}
	foreach($array as $key=>$val) {
		if($val) {
			if($valtype==1) $value=$val;
			else $value=$key;
			if($select!="") $sel=($value==$select)?'checked':'';
			$str.="<label><input type=\"radio\" name=\"$name\" value=\"$value\" onclick=\"$onClick\" $sel>$val</label>\n";
		}
	}

	return $str;
}

/* +----------------------------------------------------------------------------------------------+
' |  운영시간 설정 / [오전, 오후]
' +----------------------------------------------------------------------------------------------+*/
function getTime_Format($date_str) {
	if(!$date_str) return;
	$hour = date("H", strtotime($date_str));
	$min  = date("i", strtotime($date_str));

	if ($hour > 12) {
		$hour = $hour - 12;
		$result = "PM " . $hour. ":". $min;
	} else {
		$result = "AM " . $hour. ":". $min;
	}

	return $result;
}

/* +----------------------------------------------------------------------------------------------+
' |  영업 상태 내용 표시
  |  today : 당일 표시, list : 전체 표시
' +----------------------------------------------------------------------------------------------+*/
function operateWeekList($no, $type='list') {
	global $tbl, $pdo, $_schedul_week_config;

	$res = $pdo->iterator("select l.stat, t.* from {$tbl['store_operate']} o left join {$tbl['store_operate_time']} t ON o.no=t.sono left join {$tbl['store_location']} l ON l.no=o.sno where o.sno=:sno",
		array(':sno'=>$no)
	);

	$_arr_week = array();
	foreach($res as $so) {
		$_week_list = explode(",",$so['week']);
		$_now = strtotime(date('y-m-d H:i'));

		$css_stat = 2;
		foreach($_week_list as $k =>$w) {
			if($type == 'today' && $w != date('N')) continue;

			$st = strtotime(date('y-m-d ' . $so['shour']));
			$et = strtotime(date('y-m-d ' . $so['ehour']));

			if ($st < $_now && $_now < $et && $w == date('N')) $css_stat = 1;
			else $css_stat = 2;

			if($so['all_time'] == 'Y') $css_stat = 1;
			if($so['stat']>2) $css_stat = $so['stat'];
			$_arr_week[$w]['css_stat'] = $css_stat;

			$_arr_week[$w]['operate_week'] =$_schedul_week_config[$w].'요일';

			if ($so['all_time'] == 'Y') {
				$_arr_week[$w]['operate_hour'] = '24시간';
			} else {
				$_arr_week[$w]['operate_hour'] = $so['shour'] . ' ~ ' . $so['ehour'];
			}

			$bres = $pdo->iterator("select * from {$tbl['store_operate_break']} where stno=:stno order by shour asc ", array(':stno' => $so['no']));
			foreach ($bres as $bd) {
				$_arr_week[$w]['break_hour'][]['hour'] = $bd['shour'] . ' ~ ' . $bd['ehour'];
			}
		}
	}

	ksort($_arr_week);

	return $_arr_week;
}

/* +----------------------------------------------------------------------------------------------+
' |  string selectArray(array 배열데이터, string 객체이름, int 사용값종류, string 제목옵션, string 선택된값, string onchange스크립트)
' +----------------------------------------------------------------------------------------------+*/
function selectNewArray($array,$name,$valtype=1,$blank="",$select="",$onChange="", $selattr="") {
	if(is_array($array) == false) return;
	$str="<select name=\"$name\" onChange=\"$onChange\" $selattr>\n";
	if($blank) {
		$str.="<option value=\"\">$blank</option>\n";
	}
	foreach($array as $key=>$val) {
		if($val) {
			if($valtype==1) $value=$val;
			else $value=$key;
			if($select!=="") $sel=checked($value,$select,1);
			$str.="<option value=\"$value\" $sel>$val</option>\n";
		}
	}
	$str.="</select>\n";
	return $str;
}

/* +----------------------------------------------------------------------------------------------+
' |  string checkArray(array 배열데이터, string 객체이름, int 사용값종류, string 제목옵션, string 선택된값, string onchange스크립트)
' +----------------------------------------------------------------------------------------------+*/
function checkNewArray($array,$name,$arrayVal=array(),$onClick="") {
	if(is_array($array) == false) return;
	$str="";

	foreach($array as $key=>$val) {
		if($val) {
			if(is_array($arrayVal)) $sel=(in_array($key, $arrayVal)) ? "checked":"";
			$str.="<label><input type=\"checkbox\" name=\"$name\" value=\"$key\" onclick=\"$onClick\" $sel>$val</label>\n";
		}
	}

	return $str;
}

/* +----------------------------------------------------------------------------------------------+
' |  전화번호 치환
' +----------------------------------------------------------------------------------------------+*/
function pregNumber($num) {
	if(!$num) return;

	if(strlen($num) >8 ) {
		$num = preg_replace('/^(02.{0}|^01.{1}|[0-9]{3})(\d{3,4})(\d{4})$/', '$1-$2-$3', $num);
	} else {
		$num = preg_replace('/^(\d{3,4})(\d{4})$/', '$1-$2', $num);
	}
	return $num;
}

/* +----------------------------------------------------------------------------------------------+
' |  시간표시 치환
' +----------------------------------------------------------------------------------------------+*/
function otimeDefault() {
	for($i=6; $i<=23; $i++) {
		$_p=($i>12) ? "오후":"오전";
		$i = sprintf('%02d',$i);
		$_time[$i.':00'] = $i.':00';
		$_time[$i.':30'] = $i.':30';
	}

	return $_time;
}

/*
	===============================================================
	- 사업자 등록번호 API 조회 (https://www.data.go.kr/index.do)
	skey : 사이트 키
	num : 사업자등록번호
	===============================================================
	*/
function businessNumApi($bnum, $type='') {
	global $cfg;

	$msg = array();
	$header = array();

	$service_key = $cfg['use_biz_api_skey']; // 서비스 키 API
	$api_url = "https://api.odcloud.kr/api/nts-businessman/v1/status?serviceKey=";

	//JSON 형식 전송
	$header[]   = 'accept: application/json';
	$header[]   = 'Content-type: application/json';

	//사업자 번호
	$post_args['b_no'] = array($bnum);

	//XML 방식 return / 기본 JSON
	if($type == 'XML' ) $return_url = "&returnType=XML";

	$returnApi = comm($api_url.$service_key,json_encode($post_args), '', $header );
	$returnApi = json_decode($returnApi, true);

	$msg = array(
		'status_code'=>$returnApi['status_code'],
		'status_yn'=>$returnApi['data'][0]['utcc_yn'],
		'result_msg'=>$returnApi['data'][0]['tax_type'],
		'match_cnt'=>$returnApi['match_cnt']
	);

	//API 에러
	if($msg['status_code'] != 'OK')
	{
		$msg['result_msg'] = (!$msg['result_msg']) ? "API error : ".$msg['status_code']:$msg['result_msg'];
	}

	if($returnApi['code'] != '' )
	{
		$msg['status_yn'] = 'Y';
		$msg['result_msg'] = 'error ['.$returnApi['code'].'] : (사업자번호) '.$returnApi['msg'];
	}

	return $msg;
}
?>