<?PHP

/**
 * [매장지도] 오프라인 매장 엑셀 일괄 업로드
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

use Wing\common\WorkLog;

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/classes/SpreadsheetExcelReader.class.php';
include_once $engine_dir.'/_engine/include/file.lib.php';


$use_file_server = fsConFolder('_data/product');

if($_FILES['csv']['size'] < 1) msg('정상적인 파일을 업로드 해주세요.');

if($cfg['product_upload_debug'] == 'Y') {
	if(getExt($_FILES['csv']['name']) != 'xls') {
		msg('파일 확장자명이 xls 가 아닙니다.');
	}

	makeFullDir('_data/productUploadExcel');
	copy($_FILES['csv']['tmp_name'], $root_dir.'/_data/productUploadExcel/'.date('Ymd_his').'.txt');
}

$excel = new Spreadsheet_Excel_Reader();
$excel->setUTFEncoder('mb');
$excel->setOutputEncoding(_BASE_CHARSET_);
$excel->read($_FILES['csv']['tmp_name']);
if($excel->sheets[0]['numRows'] < 2) msg('처리 가능한 데이터가 없습니다.');

for($i = 2; $i <= $excel->sheets[0]['numRows']; $i++) {
	$data = $excel->sheets[0]['cells'][$i];

	if(!trim($data[3])) continue;

	$pdo->query('START TRANSACTION');

	$qryset = $_where = array();
	$qryset['no'] = $no = numberOnly($data[1]);
	$qryset['partner_no'] = numberOnly($data[2]);
	$qryset['title'] = addslashes($data[3]);
	$qryset['owner'] = addslashes(trim($data[4]));
	$qryset['phone'] = addslashes(trim($data[5]));
	$qryset['cell'] = addslashes(trim($data[6]));
	$qryset['email'] = addslashes($data[7]);
	$qryset['zipcode'] = addslashes($data[8]);
	$qryset['addr1'] = addslashes($data[9]);
	$qryset['addr2'] = addslashes($data[10]);
	$qryset['stat'] = $_store_config_reverse_stat[addslashes($data[11])];
	$qryset['hidden'] = addslashes($data[12]);
	$qryset['content'] = addslashes(del_html($data[13]));
	$qryset['updir'] = addslashes($data[14]);
	$qryset['upfile1'] = addslashes($data[15]);
	$qryset['upfile2'] = addslashes($data[16]);
	$qryset['facility'] = addslashes($data[17]);

	//카카오 주소 좌표 호출
	$local = $_kakao_store_handler->kakaoRestApi('address', ['query' => $qryset['addr1'] . ' ' . $qryset['addr2']], 'json');
	$local = $local['documents'][0]['road_address'];
	if ((!$local['y'] || !$local['x'])) msg('정확한 주소값을 입력해 주세요.');

	$qryset['lat'] =$local['x'];
	$qryset['lng'] =  $local['y'];
	$qryset['phone'] = str_replace('-','', $qryset['phone']);
	$qryset['cell'] = str_replace('-','', $qryset['cell']);

	//유효성 체크
	checkBlank($qryset['title'],"상호명을 입력해주세요.");
	checkBlank($qryset['owner'],"대표자명 입력해주세요.");
	if ($qryset['cell'] != '' && !ctype_digit($qryset['cell'])) {
		msg('휴대전화의 경우 숫자만 입력 가능 합니다.');
	}

	if ($qryset['phone'] != '' && !ctype_digit($qryset['phone'])) {
		msg('전화번호의 경우 숫자만 입력 가능 합니다.');
	}
	// 이메일 형식 체크
	if (empty($qryset['email']) == false) {
		if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $qryset['email']) == false)  {
			msg('이메일 형식을 확인해주세요.');
		}
	}

	$_facility = explode("@", $qryset['facility']);
	foreach($_facility as $k => $_fno) {
		$_fcheck = $pdo->row("select no from {$tbl['store_facility_set']} where no=:no", array(':no'=>$_fno));
		if(!$_fcheck) msg('존재하지 않는 시설안내 입니다.');
	}

	$qryset['facility'] = "@".$qryset['facility']."@";

	if ($no) {
		$qryset['edit_date'] = $now;
		$_where['no'] = $no;
	} else {
		$qryset['ip'] = $_SERVER['REMOTE_ADDR'];
		$qryset['reg_date'] = $now;
	}

	//쿼리 병합
	$_mqry = qryResult($qryset, $_where);

	//수정 시
	if ($no) {
		$msql = "update {$tbl['store_location']} set " . $_mqry['u'] . " where " . $_mqry['w'];
	} else {
        // 추가
		$msql = "INSERT INTO {$tbl['store_location']} (" . $_mqry['i'] . ") VALUES (" . $_mqry['v'] . ")";
	}
	$r = $pdo->query($msql, $_mqry['a']);
	$pdo->query('COMMIT');

	$total++;
}

msg($total.' 건의 오프라인 매장 일괄 등록이 완료되었습니다.', $rURL, $target);

?>