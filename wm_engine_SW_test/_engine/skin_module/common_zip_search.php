<?

	/* +----------------------------------------------------------------------------------------------+
	' |  우편번호 조회
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['form_start'] = "<form name=\"zsFrm\" method=\"get\" action=\"zip_search.php\" style=\"min-width:300px;\">
	<input type=\"hidden\" name=\"form_nm\" value=\"".$form_nm."\">
	<input type=\"hidden\" name=\"zip_nm\" value=\"".$zip_nm."\">
	<input type=\"hidden\" name=\"addr1_nm\" value=\"".$addr1_nm."\">
	<input type=\"hidden\" name=\"addr2_nm\" value=\"".$addr2_nm."\">
	<input type=\"hidden\" name=\"zip_mode\" value=\"1\">
	<input type=\"hidden\" name=\"urlfix\" value='Y'>
	<input type=\"hidden\" name=\"cart_selected\" value='{$_GET['cart_selected']}'>
	<input type=\"hidden\" name=\"sbscr\" value='{$_GET['sbscr']}'>";

	if($search){
        if ($_GET['form_nm'] == 'ordFrm' || $_GET['form_nm'] == 'nAddrFrm') {
            $cartres = checkDeliveryRangeList();
        }

		$_tmp = "";
		$_line = getModuleContent("common_zipcode_list");
		//행자부 주소API 사용
		if($cfg['juso_api_use'] == "Y") {
			if($res && is_array($res)){
				foreach ($res as $data) {
					$data['zipcode'] = $data['zipNo'];
					$data['address'] = str_replace($search,"<b><FONT COLOR='#0033FF'>".$search."</font></b>",$data['jibunAddr']);
					$data['address2'] = $data['jibunAddr'];
					$data['select_url'] = "javascript:putZip('".$data['zipcode']."','".addslashes($data['address2'])."');";

                    // 배송 불가지역 체크
                    if ($_GET['form_nm'] == 'ordFrm' || $_GET['form_nm'] == 'nAddrFrm') {
                        foreach ($cartres as $cart) {
                            $dlv_possible = checkDeliveryRange($data, $cart['partner_no']);
                            if ($dlv_possible[0] == false) {
                                $data['impossible_reason'] = $dlv_possible[1];
                                $_message = addslashes(str_replace('"', '&quot', strip_tags($data['impossible_reason'])));
                                $data['select_url'] = "javascript:window.alert('$_message')";
                            }
                        }
                    }

					$_tmp .= lineValues("common_zipcode_list", $_line, $data);
				}
			}
		} else {
			while($data = zipList($res)){
				$data['select_url'] = "javascript:putZip('".$data['zipcode']."','".addslashes($data['address2'])."');";
				$_tmp .= lineValues("common_zipcode_list", $_line, $data);
			}
		}

		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['common_zipcode_list'] = $_tmp;
	}

	$_replace_code[$_file_name]['search_word'] = inputText($search);
	$_replace_code[$_file_name]['form_end'] = "</form>";
	$_replace_code[$_file_name]['find_zip_url'] = "javascript:location.href = '?form_nm={$form_nm}&zip_nm={$zip_nm}&addr1_nm={$addr1_nm}&addr2_nm={$addr2_nm}&zip_mode=1&search={$search}&urlfix=Y';";
	$_replace_code[$_file_name]['find_street_zip_url'] = "javascript:location.href = '?form_nm={$form_nm}&zip_nm={$zip_nm}&addr1_nm={$addr1_nm}&addr2_nm={$addr2_nm}&zip_mode=2&search={$search}&urlfix=Y';";

?>