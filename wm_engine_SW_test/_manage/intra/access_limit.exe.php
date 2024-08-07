<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  계정잠금 해제 인증 처리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

    /*필드사이즈 확인*/
    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='phone' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
    if ($data_type != 'varchar(100)') {
        modifyField($tbl['join_sms'], 'phone', 'VARCHAR(100)');
    }
    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
    if ($data_type != 'varchar(100)') {
        modifyField($tbl['join_sms'], 'reg_code', 'VARCHAR(100)');
    }

	$exec = addslashes($_POST['exec']);
    $cert_type = addslashes($_POST['cert_type']);
	$find_type = numberOnly($_POST['find_type']);
    $admin_no = numberOnly($_SESSION['access_admin_no']);

	if($_POST['cell']) {
		$cell = $_POST['cell'];
		if(preg_match("/-/", $cell)) {
			$cell_n = $cell;
			$cell = str_replace('-', '', $cell);
		}else {
			$_cell1 = substr(0, 3, $cell);
			$_cell2 = substr(3, 4, $cell);
			$_cell3 = substr(7, 4, $cell);
			$cell_n = $_cell1."-".$_cell2."-".$_cell3;
		}
	}

	$search_val = ($find_type==2) ? addslashes($_POST['email']):$cell;
	$where = ($find_type==2) ? " and email='$search_val'": " and (cell='$search_val' or cell='$cell_n')";
    $mng_data = $pdo->assoc("select `no`, name from $tbl[mng] where no='$admin_no' $where");

    if(empty($mng_data['no'])){
        echo '잘못된 인증정보 입니다.';
        exit;
    }

	if($exec=='confirm') { //인증번호 발송
		if($find_type==2) {//이메일
			$reg_code = mt_rand(123456,987654);
			$mail_case = 19;
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$r = sendMailContent($mail_case, $mng_data['name'], $search_val);
		}else { //sms 보내기
			$reg_code = mt_rand(123456,987654);
			$sms_replace['name'] = $mng_data['name'];
			$sms_replace['pwd'] = $reg_code;
			include $engine_dir."/_engine/sms/sms_module.php";
			$r = SMS_send_case(22, $search_val);
		}
		//인증번호저장
		$pdo->query("delete from `$tbl[join_sms]` where phone='$search_val'");
        $reg_code_enc = aes128_encode($reg_code, 'admin_sms');
		$pdo->query("insert into `$tbl[join_sms]` (phone, reg_code, reg_date) values ('$search_val', '$reg_code_enc', '$now')");
		echo "OK";
		exit;
	}else { //인증번호 확인
		$reg_code = numberOnly($_POST['reg_code']);
        $reg_code_enc = aes128_encode($reg_code, 'admin_sms');
		$data = $pdo->assoc("select * from `$tbl[join_sms]` where phone='$search_val'");
		if($reg_code_enc != $data['reg_code']) {
?>
<script type='text/javascript'>
	var f = parent.document.unlockFrm;
	f.reg_code.value = "";
	alert('정확하지 않은 인증번호입니다.');
</script>
<?
		}else {
            if ($where) {
                $pdo->query("update $tbl[mng] set access_lock='N', access_count='0' where no='$admin_no' $where");
            }

            // 비밀번호 유효기간 체크
            $mng = $pdo->assoc("select expire_pwd from {$tbl['mng']} where no=?", array($admin_no));
            if ($scfg->comp('mng_pass_expire') == true && strtotime($mng['expire_pwd']) < $now) {
                $redir = $root_url.'/_manage/?body=intra@password_expire.frm';
            } else {
                $_SESSION['admin_no'] = $admin_no;
                ?>
                <html>
                <body>
                    <form name="makeCookieFrm" method="post" action="/main/exec.php" target="_parent">
                        <input type="hidden" name="exec_file" value="common/makeCookie.php" />
                        <input type="hidden" name="session_id" value="<?=session_id()?>" />
                        <input type="hidden" name="urlfix" value="Y" />
                    </form>
                    <script type="text/javascript">
                        document.makeCookieFrm.submit();
                    </script>
                </body>
                </html>
                <?
                exit;
            }

			$pdo->query("delete from `$tbl[join_sms]` where phone='$search_val'");
            if ($where) {
                $pdo->query("update $tbl[mng] set access_lock='N', access_count='0' where no='$admin_no' $where");
            }
            if ($cert_type == 'factor2') {
                msg('인증이 완료되었습니다.', $redir, 'parent');
            } else {
                msg('계정 잠금이 해제되었습니다.', './?body=main@main', 'parent');
            }
		}
	}
?>
