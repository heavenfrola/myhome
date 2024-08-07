<?PHP

	require_once $engine_dir.'/_engine/include/file.lib.php';
	require_once $engine_dir.'/_engine/card.nicepay/autobill/lib/NicepayLite.php';

	if(empty($price) == true) {
		$price = $card['wm_price'];
	}

	// 취소 요청
	$nicepay = new NicepayLite;
	$nicepay->m_LicenseKey   = $cfg['card_auto_nicepay_key'];
	$nicepay->m_TID          = $card['tno'];
	$nicepay->m_NicepayHome  = $root_dir.'/_data/nicepay_log';
	$nicepay->m_ssl          = "true";
	$nicepay->m_ActionType   = "CL0";
	$nicepay->m_debug        = "DEBUG";
	$nicepay->m_MID          = $cfg['card_auto_nicepay_mid'];
	$nicepay->m_Moid         = $card['wm_ono'];
	$nicepay->m_MallIP       = $_SERVER['SERVER_ADDR'];
	$nicepay->m_PayMethod    = "BILL";
	$nicepay->m_BillKey      = $data['billing_key'];
	$nicepay->m_NetCancelPW  = $cfg['card_auto_nicepay_pwd'];
	$nicepay->m_NetCancelAmt = $price;
	$nicepay->m_charSet      = "UTF8";

	$nicepay->startAction();

	// 결과 처리
	$resultCode = $nicepay->m_ResultData['ResultCode'];
	$resultCode = $nicepay->m_ResultData['ResultCode'];
	/*
Array
(
    [PG] => NICE
    [ResultCode] => PL40
    [ResultMsg] => ���� ���� ����
    [CancelAmt] => 4000
    [CancelDate] => 20191125
    [CancelTime] => 141724
    [CancelNum] => 30066894
    [PayMethod] => CARD
    [MID] => nictest04m
    [TID] => nictest04m01161911251324086012
    [ErrorCD] => 0000
    [ErrorMsg] => 정상취소
    [EncMode] => null
)
*/


	$stat = 1;
	if($resultCode == '2001' || $resultCode == '2211') {
		$stat = 2;
		$cstat = ($rev_amount == 0 || $price == 0) ? 3 : 2;
		$pdo->query("update {$tbl['card']} set stat='$cstat', wm_price='$rev_amount' where no='$cno'");
	}

	$pdo->query("
		insert into {$tbl['card_cc_log']} (cno, stat, ono, price, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values ('$cno', '$stat', '{$card['wm_ono']}', '$rev_amount', '{$card['tno']}', '$resultCode', '$resultMsg', '{$admin['admin_id']}', '{$admin['no']}', '{$_SERVER['REMOTE_ADDR']}', '$now')
	");

	msg(php2java($resultMsg), 'reload', 'parent');

	return array(
		'result' => ($nicepay->m_ResultData['ResultCode'] == '3001') ? true : false,
		'tid' => $nicepay->m_ResultData['TID'],
		'card_cd' => $nicepay->m_ResultData['CardCode'],
		'card_name' => $nicepay->m_ResultData['CardName'],
		'app_no' => $nicepay->m_ResultData['AuthCode'],
		'rec_cd' => $nicepay->m_ResultData['ResultCode'],
		'res_msg' => $nicepay->m_ResultData['ResultMsg'],
		'quota' => $nicepay->m_ResultData['CardQuota'],
		'amount' => $nicepay->m_ResultData['Amt'],
	);

?>