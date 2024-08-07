<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  아이디/암호찾기(AUTO COMMIT TEST)
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member['level']!=10) msg("","/","parent");
	checkBasic();

	// 아이핀 체크 플러스 사용 시 휴대폰 인증으로 강제 전환
	if ($cfg['ipin_checkplus_use'] == 'Y') {
		$cfg['member_confirm_sms'] = 'Y';
	}

	$name = addslashes(trim($_POST['name']));
	$email = addslashes(trim($_POST['email']));
	$cell = $_POST['cell'];
	$member_id = $_POST['member_id'];
	$ftype = numberOnly($_POST['ftype']);
	$cfg['join_jumin_use'] = 'N'; // 주민번호 사용하지 않음으로 고정

	checkBlank(stripslashes($name), __lang_member_input_name__);

	$jumin=$jumin1."-".$jumin2;
	$tmp_jumin2=substr($jumin,0,10);

	if($ftype==1) {
		$find_id_type = 1;
		if($cfg['join_jumin_use'] != "Y") $find_id_type = 2;
		if($_POST['find_id_type']) $find_id_type = numberOnly($_POST['find_id_type']);

		switch($find_id_type) {
			case '1' :
				// 구 주민번호 처리 부분
				break;
			case '2' :
				$id_msg=__lang_findpw_email_and;//"이메일과";
				$asql  = " and `email`='$email' ";
				$asql2 = " and (reg_email='Y' or (reg_email='N' and reg_sms='N'))";
				break;
			case '3' :
				$cell = addslashes(implode('-', $cell));
				$cell_n = str_replace('-', '', $cell);
				$id_msg=__lang_findpw_cell_and;//"휴대폰번호가";
				$asql  = " and (cell='$cell' or cell='$cell_n') ";
				$asql2 = " and reg_sms='Y'";
				break;
		}
		$data=findMember($asql, $asql2);
		if(!$data['no']) msg(sprintf(__lang_findpw_fail_infor_custom, $id_msg));
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/common.js"></script>
<script type="text/javascript">
window.alert(parent._lang_pack.member_info_findid.format('<?=$data['member_id']?>'));
f=parent.findFrm2;
f.name.value='<?=$name?>';
f.member_id.value='<?=$data['member_id']?>';
f.name.closest('div.fld').classList.add('active');
f.member_id.closest('div.fld').classList.add('active');
</script>
<?PHP
	}
	else { // 비밀번호찾기
		$find_pw_type = 1;
		if($cfg['join_jumin_use'] != "Y") $find_pw_type = 2;
		if($_POST['find_pw_type']) $find_pw_type = numberOnly($_POST['find_pw_type']);

		$err=0;
		checkBlank($member_id,__lang_member_input_memberid__);
		$asql=" and `name`='$name' and `member_id`='$member_id'";

		if($ftype == 2 && $find_pw_type == 1) {
			$find_pw_type = 2;
		}

		/*
		if($cfg['member_confirm_email'] != 'Y' && $cfg['member_confirm_sms'] != 'Y' && $cfg['join_jumin_use'] != 'Y') {
			$find_pw_type = 4;
		}
		*/

		switch($find_pw_type) {
			case '1' :
				// 구 주민번호 처리 부분
			break;
			case '2' :
				$asql  = " and `email`='$email' ";
				$asql2 = " and (reg_email='Y' or (reg_email='N' and reg_sms='N'))";
	 			$ftype = 2;
				break;
			case '3' :
				$cell = addslashes(implode('-', $cell));
				$cell_n = str_replace('-', '', $cell);
				$asql  = " and (cell='$cell' or cell='$cell_n') ";
				$asql2 = " and reg_sms='Y'";
	 			$ftype = 1;
			break;
		}

		$data=findMember($asql, $asql2);
		if($data['no']) {
			// 이메일
			if($ftype==2) {
				if($data['reg_email'] == 'Y') {
					checkBlank($email,__lang_member_input_email__);
					if($data['email']!=$email) $err++;
				}
			}
			// SMS
			else {
				checkBlank($cell,__lang_member_input_cell__);
				if(numberOnly($cell) != numberOnly($data['cell'])) $err++;
				$sms_find=1;
			}
		}
		else {
			$err++;
		}

		if($err>0) {
			msg(__lang_findpw_fail_infor);
		}

        $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='key' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['pwd_log']}'");
        if ($data_type != 'varchar(100)') {
            modifyField($tbl['pwd_log'], 'key', 'VARCHAR(100)');
        }
		if($sms_find){
			if($_POST['auth_key']){
				$key=$_POST['auth_key'];
                $repstr = smsCertificateNumCheckstr();
                if ($repstr) $key = str_replace($repstr,'',$key);
                $key_enc = aes128_encode($key, 'pwd_log');
				$data=$pdo->assoc("select * from `$tbl[pwd_log]` where `key`='$key_enc' limit 1");
				if(!$data['no']) msg(__lang_findpw_no_match_key);
				if($data['stat'] != "1") msg(__lang_findpw_overtime_key);
?>
<script language="JavaScript">
<!--
alert(parent._lang_pack.member_info_certcomplete);
parent.location.href='<?=$root_url?>/main/exec.php?exec_file=member/modify_pwd.php&key=<?=$key?>';
//-->
</script>
<?php
				exit;
			}

			// sms 보내기
			$key = smsCertificateNum();
			$sms_replace['name']=$data['name'];
			$sms_replace['pwd']=$key;
			include $engine_dir."/_engine/sms/sms_module.php";
			SMS_send_case(22,$data['cell']);

            $key = numberOnly($key);
            $pdo->query("update `$tbl[pwd_log]` set `stat`=2 where `member_id`='$data[member_id]'");
            $key_enc = aes128_encode($key, 'pwd_log');
            $log_sql="insert into `$tbl[pwd_log]` (`stat`, `member_no`, `member_id`, `member_name`, `email`, `key`, `ip`, `reg_date`) values('1', '$data[no]', '$data[member_id]', '$data[name]', '$data[email]', '$key_enc', '$_SERVER[REMOTE_ADDR]', '$now')";
            $pdo->query($log_sql);

			msg(__lang_findpw_send_cell_key);
		}else{

			$key=md5($member_id.$now);
            $key_enc = aes128_encode($key, 'pwd_log');
			$log_sql="insert into `$tbl[pwd_log]` (`stat`, `member_no`, `member_id`, `member_name`, `email`, `key`, `ip`, `reg_date`) values('1', '$data[no]', '$data[member_id]', '$data[name]', '$data[email]', '$key_enc', '$_SERVER[REMOTE_ADDR]', '$now')";
			$pdo->query($log_sql);

			$mail_case = 16;
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$email=$data['email'];
			$r = sendMailContent($mail_case, $data['name'], $to_mail);

			if(!$r){
				$mail_case=6;
				$mail_title[6]="[{쇼핑몰이름}] {회원이름}님, 비밀번호 변경 URL 을 알려드립니다.";
				$content1="고객님의 비밀번호 변경 URL 을 알려드립니다. 아래의 주소를 클릭하여 비밀번호를 변경해주시기 바랍니다.<br>
				단 해당 URL 은 일회성이며 변경에 실패하신 후에는 모든 과정을 처음부터 다시 해주셔야 합니다.<br><br>
				<a href=\"".$root_url."/main/exec.php?exec_file=member/modify_pwd.php&key=".$key."\" target=\"_blank\">비밀번호 변경하러 가기</a>";
				$r=sendMailContent($mail_case,$data[name],$to_mail);
			}

			if(!$r) msg(__lang_findpw_send_fail_email);

			msg(__lang_findpw_send_email,$root_url,"parent");
		}
	}

	function findMember($asql1, $asql2 = null) {
		global $tbl,$name,$jumin,$tmp_jumin2,$cfg, $pdo;
		$data=$pdo->assoc("select * from `$tbl[member]` where `name`='$name' $asql1 $asql2");
		if($data == false) {
			$data = $pdo->assoc("select * from {$tbl['member_deleted']} where 1 $asql1");
			if($asql2) { // 가입 인증 조건 체크
				$data2 = $pdo->assoc("select * from {$tbl['member']} where no='{$data['no']}' $asql2");
				if($data2 == false) $data = false;
			}
		}
		if(!$data['no'] && strlen($jumin) == 40){
			$_tmp=$pdo->assoc("select * from `$tbl[member]` where `name`='$name' and `jumin`='".left($jumin, 14)."' limit 1");
			if($_tmp['no']){
				$pdo->query("update `$tbl[member]` set `jumin`='$jumin' where `no`='$_tmp[no]' limit 1");
				$data=$_tmp;
			}
		}

		if(numberOnly($jumin) && $cfg['join_jumin_use'] == "Y") {
			if(left($data['jumin'], 10) == left($jumin, 10)){
				if($data['no'] && right($data['jumin'], 4) == "XXXX") $pdo->query("update `$tbl[member]` set `jumin`='$jumin' where `no`='$data[no]'");
			}
		}

		return $data;
	}
?>