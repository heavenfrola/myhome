<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	$phone = addslashes($_POST['cell1'].'-'.$_POST['cell2'].'-'.$_POST['cell3']);
	$phone1 = addslashes($_POST['cell1'].$_POST['cell2'].$_POST['cell3']);
	$exec = addslashes($_POST['exec']);
	$reg_code = numberOnly($_POST['reg_code']);
    $reg_code_enc = aes128_encode($reg_code, 'join');
	$check_no = $_POST['check_no'];

	switch($exec) {
		case 'getreg' :
			$sql = $pdo->row("select `no` from `$tbl[mng]` where `level` = '2' and (`cell` = '$phone1' or `cell` = '$phone')");
			if($sql) {
				$reg_code = rand(100000,999999);
                $reg_code_enc = aes128_encode($reg_code, 'join');
				$msg = "[$cfg[company_mall_name]] ".sprintf("인증번호 %s를 입력해 주세요.", $reg_code);
				if(file_exists($engine_dir.'/_engine/include/account/wec.inc.php')) {
					$msg = iconv(_BASE_CHARSET_, "EUC-KR", $msg);
				}

				$limit = time()-3600;

				if(!istable($tbl['join_sms'])) {
					include_once $engine_dir.'/_config/tbl_schema.php';
					$pdo->query($tbl_schema['join_sms']);
                } else {
                    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
                    if ($data_type != 'varchar(100)') {
                        modifyField($tbl['join_sms'], 'reg_code', 'VARCHAR(100)');
                    }
                }


				$pdo->query("delete from `$tbl[join_sms]` where `phone`='$phone' || `reg_date` < '$limit'");
				$pdo->query("insert into `$tbl[join_sms]` (`phone`, `reg_code`, `reg_date`) values ('$phone', '$reg_code_enc', '$now')");

				include_once $engine_dir."/_engine/sms/sms_module.php";
				$mms_callback = MMS_callback();
				$_mms_callback = explode('@', $mms_callback);
				$_mms_callback = array_filter($_mms_callback);

				$we_mms = new WeagleEyeClient($GLOBALS[_we], 'mms');
				$we_mms -> queue("mms_send", "wing", $wec -> config['account_idx'], $phone, 1, $_mms_callback[0], cutStr($msg, 20), $msg, '', '', '', 7);
				$we_mms -> send_clean();
				if($we_mms -> result != 'OK') {
					msg("문자발송중 오류가 발생했습니다.", 'back');
				}
				msg("인증번호가 발송되었습니다.");
			} else {
				msg("최고관리자로 등록된 휴대폰 번호가 아닙니다.");
			}
			break;

		case 'receivereg' :
			$data = $pdo->assoc("select * from `$tbl[join_sms]` where `phone`='$phone'");
			if(($now-$data['reg_date']) > 300) {
				alert('입력 시간이 초과되었습니다. 인증번호를 다시 받아주세요');
			} else if($reg_code_enc != $data['reg_code']) {
				alert('잘못된 인증번호 입니다.\n인증번호를 확인한 다음 다시 입력해 주세요.');
			} else {
				foreach($check_no as $val) {
					$val = numberOnly($val);
					$res = $pdo->query("update `$tbl[mng]` set `cfg_receive` = 'Y' , `cfg_receive_regdate` = '$now' where `no`='$val'");
					if($res) $ii++;
				}
				msg("$ii 명의 관리자를 등록하였습니다.", 'reload', 'parent');
				exit;
			}
		break;

		case 'confirmreg' :
			$data = $pdo->assoc("select * from `$tbl[join_sms]` where `phone`='$phone'");
			if(($now-$data['reg_date']) > 300) {
				alert('입력 시간이 초과되었습니다. 인증번호를 다시 받아주세요');
			} else if($reg_code_enc != $data['reg_code']) {
				alert('잘못된 인증번호 입니다.\n인증번호를 확인한 다음 다시 입력해 주세요.');
			} else {
				foreach($check_no as $val) {
					$val = numberOnly($val);
					$res = $pdo->query("update `$tbl[mng]` set `cfg_confirm` = 'Y' , `cfg_confirm_regdate` = '$now' where `no`='$val'");
					if($res) $ii++;
				}
				msg("$ii 명의 관리자를 등록하였습니다.", 'reload', 'parent');
				exit;
			}
		break;
	}
	exit;
?>
<script type='text/javascript'>
	function regComplete(reg_code, phone) {
	var f = parent.document.getElementsByName('popupContent')[0];
	if(f.reg_code) {
		f.reg_code.value = reg_code;
	} else {
		$(f).append('<input type=\"hidden\" name=\"reg_code\" value=\"'+reg_code+'\">');
	}

	var temp = phone.split('-');
	var cell = ($(f).find('input[name="cell"]').length > 0) ? $(f).find('input[name="cell"]') : $(f).find("input[name^='cell[']");

	f.cell[0].value = temp[0];
	f.cell[1].value = temp[1];
	f.cell[2].value = temp[2];

	window.alert(_lang_pack.common_info_certcomplete);
	parent.removeCertFrm();
}
</script>
<?
	// 인증완료 처리
	if($reg_code) {
		$data = $pdo->assoc("select * from wm_join_sms_new where phone='$phone2'");

		if($reg_code_enc != $data['reg_code']) {
			alert(__lang_member_error_wrongAuthcode__);
		} else {
			javac("regComplete('$reg_code', '$phone2')");
			exit;
		}
	}

?>
