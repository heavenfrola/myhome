<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | order 페이지 재입고요청
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type:application/json; charset='._BASE_CHARSET_);
	define('_LOAD_AJAX_PAGE_', true);

	function notify_restock_prdOptionList($prd, $option_list_asql) {
		global $tbl, $cfg, $opt_cnt, $pdo;
		$_opt_list = array();

		$sql = "select * from `$tbl[product_option_set]` where `stat`='2' and `pno`='$prd[parent]' $option_list_asql order by `sort`";
		$result = $pdo->iterator($sql);

		// 품절 옵션키 ======================================================================================================
		// 품절방식 설정값에 따른 품절옵션만 나오게
		if(!$cfg['notify_restock_type_l']) $cfg['notify_restock_type_l'] = "Y";
		if(!$cfg['notify_restock_type_f']) $cfg['notify_restock_type_f'] = "Y";
		$soldout_where = "";
		if($cfg['notify_restock_type_l'] == "Y") {
			$soldout_where .= " (is_soldout='Y' OR (force_soldout='L' AND qty<1)) AND force_soldout!='Y' ";
		}
		if($cfg['notify_restock_type_f'] == "Y") {
			if($soldout_where != "") $soldout_where .= " OR ";
			$soldout_where .= " force_soldout='Y' ";
		}
		$soldout_keys = array();
		$soldout_sql = "
						SELECT
							opts
						FROM
							erp_complex_option
						WHERE
							`pno`='$prd[parent]'
							AND del_yn='N'
							AND (
								$soldout_where
							) ";
		$soldout_result = $pdo->iterator($soldout_sql);
        foreach ($soldout_result as $soldout_row) {
			$_opts = explode("_", $soldout_row['opts']);
			$soldout_keys = array_merge($soldout_keys, $_opts);
		}
		// 배열 빈값제거
		$soldout_keys = array_values(array_filter(array_map('trim', $soldout_keys))); // 품절 옵션키
		$soldout_keys_string = join(",", $soldout_keys);

		$opt_no = 1;
        foreach ($result as $row) {
			if($row['otype'] == "4A") continue;

			$opt = array();
			$opt_str = "";
			$objName = "notify_restock_option".$opt_no;

			$inner_sql = "SELECT * FROM $tbl[product_option_item] WHERE opno='$row[no]' AND `no` IN ($soldout_keys_string) AND hidden != 'Y' ORDER BY sort ASC";
			$inner_result = $pdo->iterator($inner_sql);

			// *모든옵션 셀렉트박스(콤보박스)로 노출
			$opt_str .= "<select name=\"$objName\" id=\"$objName\" onChange=\"notify_restock_optionSelect('$opt_no', this.value)\" data-necessary=\"$row[necessary]\" class='is_notify_restock_option wing_multi_option necessary_$row[necessary]' opt_index='{$opt_no}' >";
			$opt_str .= "<option value=\"\">::".inputText($row['name'])."::</option>";
            foreach ($inner_result as $inner_row) {
				$inner_row['add_price'] = parsePrice($inner_row['add_price']);
				if($inner_row['add_price'] > 0 && $row['deco_use'] == 'Y') {
					$prc_str = $row['deco1'].number_format($inner_row['add_price']).$row['deco2'];
				} else {
					$prc_str = '';
				}

				$inner_row['iname'] = preg_replace('/\"|\'/', '', stripslashes($inner_row['iname']));
				$opt_str .= "<option value=\"$inner_row[no]\">$inner_row[iname]$prc_str</option>";
			}
			$opt_str .= "</select>";

			$opt['name'] = $row['name'];
			$opt['option_str'] = $opt_str;
			$opt['hidden_str'] = "<input type=\"hidden\" name=\"notify_restock_option_necessary".$opt_no."\" value=\"".$row['necessary']."\">\n";
			$opt['hidden_str'] .= "<input type=\"hidden\" name=\"notify_restock_option_type".$opt_no."\" value=\"".substr($row['otype'],0,1)."\">\n";
			$opt['hidden_str'] .= "<input type=\"hidden\" name=\"notify_restock_option_no".$opt_no."\" value=\"\">\n";

			$opt_no++;
			$_opt_list[] = $opt;
		}
		$opt_cnt = $opt_no-1;
		return $_opt_list;
	}

	// 재입고요청
	$_hash = get_info($tbl['product'], 'no', $_REQUEST['pno']);
	$prd = checkPrd($_hash['hash'], false);
	unset($_hash);
	$option_list_asql = " AND necessary='Y' "; // 필수옵션만 노출되도록
	$_notify_restock_opt_list = notify_restock_prdOptionList($prd, $option_list_asql);

	$_tmp = "";
	$_line = getModuleContent('detail_notify_restock_opt_list');
	foreach($_notify_restock_opt_list as $opt) {
		$_tmp .= $opt['hidden_str'];
		$_tmp .= lineValues('detail_notify_restock_opt_list', $_line, $opt);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['detail_notify_restock_opt_list'] = $_tmp;
	$_replace_code[$_file_name]['notify_restock_prd_name'] = $prd['name'];

	// 재입고 알림 폼
	$nowtime = (numberOnly($_GET['now'])) ? numberOnly($_GET['now']) : $now;
	$_replace_code[$_file_name]['notify_restock_form_start'] = '<form name="notify_restock_form" method="POST" action="/main/exec.php" target="hidden'.$nowtime.'">';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= '<input type="hidden" name="exec_file" value="shop/notify_restock.exe.php" />';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= '<input type="hidden" name="exec" value="insert" />';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= '<input type="hidden" name="sell_prc_consultation" value="" />';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= '<input type="hidden" name="pno" value="'.$prd['hash'].'" />';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= '<input type="hidden" name="ea_type" value="'.$prd['ea_type'].'">';
	$_replace_code[$_file_name]['notify_restock_form_start'] .= "<input type=\"hidden\" name=\"notify_restock_opt_count\" value=\"".$opt_cnt."\">";

	$_replace_code[$_file_name]['notify_restock_form_end'] = '</form>';

	$_replace_code[$_file_name]['notify_restock_sell_prc'] = parsePrice($prdCart->getData('pay_prc'), true);

?>