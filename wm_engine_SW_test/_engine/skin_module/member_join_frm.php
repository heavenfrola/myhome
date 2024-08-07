<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입/수정폼
	' +----------------------------------------------------------------------------------------------+*/


	$_replace_code[$_file_name]['form_start']="<form name=\"joinFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkRegMember(this);\" autocomplete=\"do-not-show-ac\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/join.exe.php\">
<input type=\"hidden\" name=\"member_id_checked\" value=\"0\">
<input type=\"hidden\" name=\"nick_checked\" value=\"0\">
<input type=\"hidden\" name=\"namecheck_num\" value=\"".$namecheck_num."\">
<input type=\"hidden\" name=\"ipin_num\" value=\"$ipin_num\">
<input type=\"hidden\" name=\"member_type\" value=\"$member_type\">
<input type=\"hidden\" name=\"reg_data\" value=\"$reg_data\">
";

	if($member['no']){
		$_replace_code[$_file_name]['join_login_name']=getModuleContent("join_login_name");
		$_replace_code[$_file_name]['join_login_id'] = ($member['join_ref'] == 'mng') ? getModuleContent('join_logout_id') : getModuleContent('join_login_id');
		$_replace_code[$_file_name]['join_phone']=$member['phone'];
		$_replace_code[$_file_name]['join_phone1']=$_phone[0];
		$_replace_code[$_file_name]['join_phone2']=$_phone[1];
		$_replace_code[$_file_name]['join_phone3']=$_phone[2];
		$_replace_code[$_file_name]['join_cell']=$member['cell'];
		$_replace_code[$_file_name]['join_cell1']=$_cell[0];
		$_replace_code[$_file_name]['join_cell2']=$_cell[1];
		$_replace_code[$_file_name]['join_cell3']=$_cell[2];
		$_replace_code[$_file_name]['join_zip']=$member['zip'];
		$_replace_code[$_file_name]['join_addr1']=$member['addr1'];
		$_replace_code[$_file_name]['join_addr2']=$member['addr2'];
		$_replace_code[$_file_name]['join_email1']=$_email[0];
		$_replace_code[$_file_name]['join_email2']=$_email[1];
		if($cfg['milage_use'] == "1" && ($cfg['milage_recom1'] || $cfg['milage_recom2'])){
			$_line=getModuleContent("join_login_recom");
			$_replace_code[$_file_name]['join_login_recom']=lineValues("join_login_recom", $_line, $member);
		}
	}else{
		$_replace_code[$_file_name]['join_logout_name']=getModuleContent("join_logout_name");
		$_line=getModuleContent("join_logout_id");
		$_jid['id_dbl_ck']="javascript:checkDupl(document.joinFrm.member_id);";
		if($cfg['member_join_id_email'] != 'Y') $_replace_code[$_file_name]['join_login_id']=lineValues("join_logout_id", $_line, $_jid);
		$_replace_code[$_file_name]['join_logout_recom']=($cfg['milage_use'] == "1" && ($cfg['milage_recom1'] || $cfg['milage_recom2'])) ? getModuleContent("join_logout_recom") : "";

		if($_POST['reg_data']) {
			$_replace_code[$_file_name]['join_cell']=$_cell;
			$_replace_code[$_file_name]['join_email1']=$_email[0];
			$_replace_code[$_file_name]['join_email2']=$_email[1];
		}
	}

	if(!$_POST['reg_data'] && ($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y')) {
		if($member['reg_sms'] == 'Y') $checkeds = 'checked';
		else $checkede = 'checked';

		$_tmp = array();
		if($cfg['member_confirm_sms'] == 'Y') $_tmp['reg_type_radio'] .= "<input type='radio' id='cert_unique2' name='unique' value='2' onclick='openCertFrm()' $checkeds> <label for='cert_unique2'>".__lang_member_info_atype1__."</label>";
		if($cfg['member_confirm_email'] == 'Y') $_tmp['reg_type_radio'] .= "<input type='radio' id='cert_unique1' name='unique' value='1' $checkede> <label for='cert_unique1'>".__lang_member_info_atype2__."</label>";

		$_line=getModuleContent("join_reg_type");
		$_replace_code[$_file_name]['join_reg_type']=lineValues("join_reg_type", $_line, $_tmp);
		unset($_tmp);
	}

	if($cfg['member_join_nickname'] != 'N') {
		$_line=getModuleContent('join_nick_chk');
		$_jnick['nick_dbl_ck'] = 'javascript:checkDuplNick(document.joinFrm.nick);';
		$_jnick['nick'] = $member['nick'];
		$_replace_code[$_file_name]['join_nick_chk'] = lineValues('join_nick_chk', $_line, $_jnick);
	}

	if($cfg['join_jumin_use'] == "Y"){
		$_replace_code[$_file_name]['join_login_jumin']=($member[no]) ? getModuleContent("join_login_jumin") : "";
		$_replace_code[$_file_name]['join_logout_jumin']=($member[no]) ? "" : getModuleContent("join_logout_jumin");
	}else{
		if($cfg['join_birth_use'] == "Y"){
			if(!$_SESSION['ipin_res']['birth']) {
				$_line=getModuleContent("join_birth");
				$_jbirth['birth1_select']=$birth1_select;
				$_jbirth['birth2_select']=$birth2_select;
				$_jbirth['birth3_select']=$birth3_select;
				$_jbirth['birth_type_ck1']=$birth_type_ck1;
				$_jbirth['birth_type_ck2']=$birth_type_ck2;
				$_replace_code[$_file_name]['join_birth']=lineValues("join_birth", $_line, $_jbirth);
				unset($_jbirth);
			}
		}
		if($cfg['join_sex_use'] == "Y"){
			if(!$_SESSION['ipin_res']['gender']) {
				$_line=getModuleContent("join_sex");
				$_jsex['sex_ck1']=$sex_ck1;
				$_jsex['sex_ck2']=$sex_ck2;
				$_replace_code[$_file_name]['join_sex']=lineValues("join_sex", $_line, $_jsex);
				unset($_jsex);
			}
		}
		if($cfg['use_whole_mem'] == "Y"){
			$_line=getModuleContent("join_whole_mem");
			$_jwhole['whole_y']=$whole_y;
			$_jwhole['whole_n']=$whole_n;
			$_replace_code[$_file_name]['join_whole_mem']=lineValues("join_whole_mem", $_line, $_jwhole);
			unset($_jwhole);
		}
	}

	if($cfg['join_addr_use'] != "N"){
		$_line=getModuleContent("join_addr");
		$_jaddr['zip']=$member['zip'];
		$_jaddr['addr1']=$member['addr1'];
		$_jaddr['addr2']=$member['addr2'];
        $isMb = ($mobile_browser == 'mobile') ? 'Y' : '';
		$_jaddr['zip_url']="javascript:zipSearch('joinFrm','zip','addr1','addr2','','$isMb');";
		$_jaddr['street_zip_url']="javascript:zipSearch('joinFrm','zip','addr1','addr2',2,'$isMb');";
		$_replace_code[$_file_name]['join_addr']=lineValues("join_addr", $_line, $_jaddr);
		unset($_jaddr);
	}

	$_replace_code[$_file_name]['join_sms_checked']=$sms_check;
	$_replace_code[$_file_name]['find_zip_url']="javascript:zipSearch('joinFrm','zip','addr1','addr2');";
	$_replace_code[$_file_name]['find_street_zip_url']="javascript:zipSearch('joinFrm','zip','addr1','addr2',2);";
	$_replace_code[$_file_name]['join_email_checked']=$mailing_check;

	if(@is_array($_mbr_add_info)){
		$_tmp="";
		$_line=getModuleContent("join_addfd_list");
		foreach($_mbr_add_info as $key=>$val){//ADDINFO_DONE
			$_jaddfd['name']=$val['name'];
			$_jaddfd['value']=memberAddFrm($key);
			$_jaddfd['cate']=$val['cate'];
			$_jaddfd['add_img']=$val['upfile1'] ? "<img src=".$root."/".$val['updir']."/".$val['upfile1'].">" : $val['name'];
			$_jaddfd['is_required'] = ($val['ncs'] == 'Y') ? 'required' : '';
			$_tmp .= lineValues("join_addfd_list", $_line, $_jaddfd);
		}
		unset($_jaddfd);
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['join_addfd_list']=$_tmp."<!-- ADDINFO_DONE -->";
	}

	$_replace_code[$_file_name]['form_end']="</form><form id='ssl_tmp' method='post'><input type='hidden' name='exec_file'><input type='hidden' name='setOpenSSL' value='Y'><input type='hidden' name='data'></form>";

	// 기업회원 처리
	if($cfg['use_biz_member'] == 'Y') {
		$_tmp = '';
		$biz = $pdo->assoc("select * from $tbl[biz_member] where ref='$member[no]'");
		if($biz['ref'] > 0 || $_POST['member_type'] == 2) {
			$_tmp = getModuleContent('biz_frm');
			if($member['no'] > 0) {
				list($biz['biz_num1'], $biz['biz_num2'], $biz['biz_num3']) = explode('-', $biz['biz_num']);
				$biz['biz_type1'] = stripslashes($biz['biz_type1']);
				$biz['biz_type2'] = stripslashes($biz['biz_type2']);

				$_tmp = lineValues('biz_frm', $_tmp, $biz);
			}
		}

		$_replace_code[$_file_name]['biz_frm'] = $_tmp;
		unset($_tmp);
	}

    // SNS 계정 통합
    if ($member['no']) {
		$_tmp = '';
        $_line = getModuleContent('join_sns_integrate_list');
        foreach ($_sns_type as $_code => $_type) {
            $_name_en = $_sns_type_info[$_code]['name_en'];
            $sns_use = $cfg[$_name_en.'_login_use'];
            if (empty($sns_use) == true || $sns_use == 'N') {
                continue;
            }
            if ($_SESSION['sns_type'] == $_code) {
                continue;
            }

            $_status = $pdo->row("select reg_date from {$tbl['sns_join']} where member_no='{$member['no']}' and type='$_type'");
            if ($_status > 0) {
                $_status = date('Y-m-d', $_status);
            }

            $_tmp .= lineValues('join_sns_integrate_list', $_line, array(
                'name' => $_sns_type_info[$_code]['name'],
                'code' => $_code,
                'status' => $_status,
                'disconnected' => ($_status > 0) ? '' : 'Y',
                'link' => "snsIntegrate('$_name_en');",
                'link2' => "snsDisconnect('$_type');",
            ));
        }
        $_replace_code[$_file_name]['join_sns_integrate_list'] = listContentSetting($_tmp, $_line);
    }

?>