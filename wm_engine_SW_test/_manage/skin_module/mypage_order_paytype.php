<?PHP

	$_replace_code[$_file_name]['pay_type_str'] = $_pay_type[$ord['pay_type']];
	$_replace_hangul[$_file_name]['pay_type_str'] = '결제방식';
	$_code_comment[$_file_name]['pay_type_str'] = '현재 주문서의 결제방식을 출력합니다.';
	$_auto_replace[$_file_name]['pay_type_str'] = 'Y';

	$_replace_code[$_file_name]['pay_prc'] = parsePrice($ord['pay_prc'], true);
	$_replace_hangul[$_file_name]['pay_prc'] = '결제금액';
	$_code_comment[$_file_name]['pay_prc'] = '현재 주문서의 결제금액 출력합니다.';
	$_auto_replace[$_file_name]['pay_prc'] = 'Y';

	$_replace_code[$_file_name]['mypage_paytype_cpn'] = '';
	$_replace_hangul[$_file_name]['mypage_paytype_cpn'] = '현금전용쿠폰명';
	$_code_comment[$_file_name]['mypage_paytype_cpn'] = '현금전용쿠폰 사용중일 경우 쿠폰명을 출력합니다.';
	$_auto_replace[$_file_name]['mypage_paytype_cpn'] = 'Y';

	$_replace_code[$_file_name]['mypage_paytype_chg_list'] = '';
	$_replace_hangul[$_file_name]['mypage_paytype_chg_list'] = "결제수단선택리스트";
	$_replace_datavals[$_file_name]['mypage_paytype_chg_list'] = '결제수단선택:radio;이름:name;';
	$_code_comment[$_file_name]['mypage_paytype_chg_list'] = '결제수단 변경 기능 사용 시 변경 가능한 결제수단을 출력합니다.';

	$_replace_code[$_file_name]['mypage_paytype_form_start'] = '<form id="chgMypagePayTypeFrm" method="post" action="/main/exec.php" onsubmit="return chgMypagePayType(this);"><input type="hidden" name="exec_file" value="mypage/order_paytype.exe.php"><input type="hidden" name="ono" value="'.$ord['ono'].'">';
	$_replace_hangul[$_file_name]['mypage_paytype_form_start'] = "결제수단변경폼시작";
	$_auto_replace[$_file_name]['mypage_paytype_form_start'] = 'Y';
	$_code_comment[$_file_name]['mypage_paytype_form_start'] = '결제수단 변경 기능 사용 시 시작폼을 출력합니다.';

	$_replace_code[$_file_name]['mypage_paytype_form_end'] = '</form><script>mypageChgPayType(); var change_pay_recalc="'.$cfg['change_pay_recalc'].'";</script>';
	$_replace_hangul[$_file_name]['mypage_paytype_form_end'] = "결제수단변경폼끝";
	$_auto_replace[$_file_name]['mypage_paytype_form_end'] = 'Y';
	$_code_comment[$_file_name]['mypage_paytype_form_end'] = '결제수단 변경 기능 사용 시 종료폼을 출력합니다.';

?>