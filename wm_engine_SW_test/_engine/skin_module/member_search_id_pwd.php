<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  NEW 아이디/비번찾기
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$ftype = numberOnly($_POST['ftype']);
	$find_id_type = numberOnly($_POST['find_id_type']);
	$name = ($_POST['name']) ? addslashes(trim($_POST['name'])): addslashes(trim($_POST['search_name']));

	if($ftype=='2') {
		$member_id = addslashes(trim($_POST['member_id']));
		$search_member_no = $pdo->row("select no from `$tbl[member]` where member_id='$member_id'");
		$_replace_code[$_file_name]['search_id_pwd_type'] = __lang_member_info_pwd_search__;
	}else {
		$_replace_code[$_file_name]['search_id_pwd_type'] = __lang_member_info_id_search__;
	}
	if($find_id_type==2) {
		$email = addslashes($_POST['email']);
		$find_id_pwd_type = __lang_member_info_atype2b__;
		$find_id_pwd_confirm = $email;
	}else {
		$find_id_pwd_confirm = $cell = numberOnly($_POST['cell']);
		$find_id_pwd_type = __lang_member_info_atype1b__;
	}

	if($_POST['reg_code']) {
		$search_val = addslashes($_POST['search_val']);
		$search_name = addslashes($_POST['search_name']);
		$sw = '';
		$_tmp = '';
		$_line = getModuleContent('search_id_list');
		if($find_id_type==2) {
			$sw = " and `email`='$search_val'";
		}else {
			$search_val = str_replace('-', '', $search_val);
			$sw = " and replace(cell, '-', '')='$search_val'";
		}
		if($sw){
			$sres = $pdo->iterator("select * from `$tbl[member]` where `withdraw` in ('N', 'D1') and `name`='$search_name' $sw");
            foreach ($sres as $sfdata) {
				$sfdata['reg_date'] = date("Y.m.d", $sfdata['reg_date']);
				$sfdata['member_no'] = $sfdata['no'];
				$_tmp .= lineValues("search_id_list", $_line, $sfdata, "", 2);
			}
			$sres = $pdo->iterator("select * from {$tbl['member_deleted']} where `name`='$search_name' $sw");
            foreach ($sres as $sfdata) {
				$org_reg_date = $pdo->query("select reg_date from $tbl[member] where `no`='$sfdata[no]'");
				$sfdata['reg_date'] = date("Y.m.d", $org_reg_date);
				$sfdata['member_no'] = $sfdata['no'];
				$_tmp .= lineValues("search_id_list", $_line, $sfdata, "", 2);
			}

			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]['search_id_list'] = $_tmp;
		}
		$find_id_pwd_confirm = $search_val;
	}

	$_replace_code[$_file_name]['search_form_start']="<form name=\"idpwdsearchFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return chkSearchmember(this)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/search_id_pwd_sms.exe.php\">
<input type=\"hidden\" name=\"find_id_type\" value=\"".$find_id_type."\">
<input type=\"hidden\" name=\"exec\" value=\"regcomplete2\">
<input type=\"hidden\" name=\"ftype\" value=\"".$ftype."\">
<input type=\"hidden\" name=\"search_member_no\" value=\"".$search_member_no."\">
<input type=\"hidden\" name=\"search_name\" value=\"".$name."\">
<input type=\"hidden\" id=\"minsec\" name=\"minsec\" value=\"\">
";
	$_replace_code[$_file_name]['search_form_end'] = "</form>";
	$_replace_code[$_file_name]['search_type'] = $find_id_pwd_type;
	$_replace_code[$_file_name]['search_confirm'] = $find_id_pwd_confirm;
	$_replace_code[$_file_name]['search_old_type'] = $find_id_type;
	$_replace_code[$_file_name]['search_name'] = $name;
	$_replace_code[$_file_name]['search_reg_code'] = $_POST['reg_code'];

?>
