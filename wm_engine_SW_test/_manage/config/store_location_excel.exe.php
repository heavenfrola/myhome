<?PHP
/**
 * [매장지도] 엑셀 업로드 프로세스
 */

include_once 'store_location.php';

$row_style = array();
$headerType = array();
$headerStyle = array(
	'fill' => '#f2f2f2',
	'font-style' => 'bold',
	'halign' => 'center',
	'widths' => array()
);

//리스트 항목
$list = array(
	'no' => '고유번호',
	'partner_no' => '입점사 번호',
    'title' => '상호명',
	'owner' => '대표자명',
    'phone' => '전화번호',
	'cell' => '휴대폰',
	'email' => '이메일',
    'zipcode' => '우편번호',
	'addr1' => '주소',
    'addr2' => '상세주소',
	'stat' => '상태',
	'hidden' => '숨김여부',
    'content' => '내용',
	'updir' => '이미지경로',
	'upfile1' => '썸네일 이미지',
	'upfile2' => '커버 이미지',
	'facility' => '시설안내'
);

$exceptionColType = array(
	'prc_limit' => 'price',
	'sale_limit' => 'price'
);
foreach ($list as $key => $val) {
	$headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
	$headerStyle['widths'][] = 20;
}
$file_name = '매장주소리스트';
$ExcelWriter = setExcelWriter();
$ExcelWriter->setFileName($file_name);
$ExcelWriter->setSheetName($file_name);
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);
$res = $pdo->iterator($sql,$_qry_arr);
$cnt = 0;

foreach ($res as $data) {
	$cnt++;
	$data['cnt'] = $cnt;
	$data = array_map('stripslashes', $data);
	$data['stat'] = $_store_config_stat[$data['stat']];
	$data['facility'] = @preg_replace( '/^@|@$/', '',$data['facility']);
	$value = array();

	foreach($list as $key => $val) {
		if ($key === 'sale_prc' && $data['sale_type'] === 'm') {
			$value[] = parsePrice($data['sale_prc'], true);
		} else {
			$value[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
		}
	}
	$ExcelWriter->writeSheetRow($value);
	unset($value);
}
$ExcelWriter->writeFile();
