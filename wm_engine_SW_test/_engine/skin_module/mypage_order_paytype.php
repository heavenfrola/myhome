<?PHP

	$mypage_pay_able = false;
	$stat2 = array_unique(explode('@', trim($ord['stat2'], '@')));
	foreach($stat2 as $key => $val) {
		if($val > 10) unset($stat2[$key]);
	}
	if($ord['stat'] == 1 && count($stat2) == 1 && empty($ord['x_order_id']) == true && $cfg['pay_type'] != '3' && $cfg['pay_type'] != '6') {
		$mypage_pay_able = true;
	}

	$cpn = $pdo->assoc("select name, pay_type from {$tbl['coupon_download']} where ono='$ono'");
	if($cpn['pay_type'] == 2) {
		$_replace_code[$_file_name]['mypage_paytype_cpn'] = stripslashes($cpn['name']);
	}

	// 결제방법 변경
	if($mypage_pay_able == true) {
		if(empty($cfg['change_pay_type']) == false && $cfg['use_paytype_change'] == 'Y') {
			$_tmp = '';
			$change_pay_type = explode('@', $cfg['change_pay_type']);
			$_line = getModuleContent('mypage_paytype_chg_list');

			foreach($change_pay_type as $key) {
				$checked = (empty($tmp) == true) ? 'checked' : '';
				$_tmp .= lineValues("mypage_paytype_chg_list", $_line, array(
					'radio' => "<input type='radio' name='pay_type' value='$key' $checked onclick='mypageChgPayType();'>",
					'name' => $_pay_type[$key]
				));
			}
			$_replace_code[$_file_name]['mypage_paytype_chg_list'] = listContentSetting($_tmp, $_line);
			unset($_tmp);
		}
	}

?>