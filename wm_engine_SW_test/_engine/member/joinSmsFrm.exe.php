<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	switch($_POST['exec']) {
		case 'generate' :
			$type = numberOnly($_POST['type']);
			$source = addslashes($_POST['source']);
			$reg_code = rand(100000, 999999);
            $reg_code_enc = aes128_encode($reg_code, 'join');
			$session_id = session_id();

			$check_qry = $type == 1 ? " replace(cell, '-', '')='$source'" : " and email='$source'";
			$check_name = $type == 1 ? __lang_member_error_existsCell__ : __lang_member_error_existsEmail__;

			if($pdo->row("select count(*) from $tbl[member] where 1 $check_qry") > 0) {
				exit('{"rcode":"0001", "rmsg":"'.$check_name.'"}');
			} else {
				$limit = time()-3600;

				if(!istable($tbl['join_sms_new'])) {
					include_once $engine_dir.'/_config/tbl_schema.php';
					$pdo->query($tbl_schema['join_sms_new']);
				} else {
                    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms_new']}'");
                    if ($data_type != 'varchar(100)') {
                        modifyField($tbl['join_sms_new'], 'reg_code', 'VARCHAR(100)');
                    }
                }

				$pdo->query("delete from `$tbl[join_sms_new]` where (phone='$source' or session_id='$session_id') or reg_date < $limit");
				$pdo->query("insert into `$tbl[join_sms_new]` (type, phone, reg_code, session_id, reg_date) values ('$type', '$source', '$reg_code_enc', '$session_id', '$now')");

				if($type == 1) {
					$msg = "[$cfg[company_mall_name]] ".sprintf(__lang_member_input_joinCode__, $reg_code);

					$we_mms = new WeagleEyeClient($GLOBALS[_we], 'mms');
					$we_mms->queue("mms_send", "wing", $wec->config['account_idx'], $phone, 1, $cfg['company_phone'], cutStr($msg, 20), $msg, '', '', '', 7);
					$we_mms->send_clean();

					if($we_mms->result != 'OK') {
						exit('{"rcode":"0002", "rmsg":"'.$we_mms->result.'"}');
					}
				} else {
					$mail_case = 6;
					$mail_title[6] = "[{쇼핑몰이름}] 이메일 인증코드를 입력해 주세요.";
					$content1 = "
						본 메일에 포함된 인증코드를 입력하여 가입 절차를 계속 진행해주세요.<br />
						인증코드는 발급 이후 최대 한시간동안 유효합니다.<br /><br />
						[ $reg_code ]
					";

					include_once $engine_dir."/_engine/include/mail.lib.php";
					$r = sendMailContent($mail_case, '비회원', $source);
				}
			}

			exit('{"rcode":"0000", "rmsg": "'.sprintf(__lang_member_input_authcode__, $source).'"}');

			break;
		case 'confirm' :
            $reg_code = numberOnly($_POST['reg_code']);
            $reg_code_enc = aes128_encode($reg_code, 'join');
			$session_id = session_id();

			$data = $pdo->assoc("select * from $tbl[join_sms_new] where session_id='$session_id'");
			if($data['reg_code'] != $reg_code_enc) {
				exit('{"rcode":"0001", "rmsg":"'.__lang_member_error_deffAuthcode__.'"}');
			}

			$_SESSION['join_cert_type'] = $data['type'];
			$_SESSION['join_cert_type'] = $data['phone'];

			exit('{"rcode":"0000", "rmsg":"'.__lang_member_info_successAuth__.'", "rdata":"'.$data['no'].'"}');
			break;
	}

	exit;

	// 인증완료 처리
	if($_POST['reg_code']) {
		$phone = addslashes($_POST['phone']);
		$reg_code = numberOnly($_POST['reg_code']);
		$data = $pdo->assoc("select * from wm_join_sms_new where phone='$phone'");

		if($reg_code != $data['reg_code']) {
			alert(__lang_member_error_wrongAuthcode__);
		} else {
			javac("regComplete('$reg_code', '$phone')");
			exit;
		}
	}
?>