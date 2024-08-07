<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  NEW 아이디/비번찾기 인증
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	$phone_length = $pdo->row("select length(phone) from `$tbl[join_sms]`");
	if($phone_length!=100) {
		$pdo->query("alter table `$tbl[join_sms]` modify `phone` varchar(100) NOT NULL");
	}

    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
    if ($data_type != 'varchar(100)') {
        modifyField($tbl['join_sms'], 'reg_code', 'VARCHAR(100)');
    }

	$exec = addslashes($_POST['exec']);
	$ftype = numberOnly($_POST['ftype']);
	$find_id_type = numberOnly($_POST['find_id_type']);
	$search_member_no = numberOnly($_POST['search_member_no']);
	$search_val = addslashes($_POST['search_val']);
	$search_name = addslashes($_POST['search_name']);

	if($exec=='regcomplete') { //인증번호 발송
		if($find_id_type==2) {//이메일
            $check = $pdo->row("select no from {$tbl['member']} where no=? and email=?", array($search_member_no, $search_val));
            if ($check != $search_member_no) {
                exit(__lang_member_info_faildAuth__);
            }

			$reg_code = mt_rand(123456,987654);
			$mail_case = 19;
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$r = sendMailContent($mail_case, $search_name, $search_val);
		}else { //sms 보내기
            $check = $pdo->row("select no from {$tbl['member']} where no=? and cell=?", array($search_member_no, $search_val));
            if ($check != $search_member_no) {
                exit(__lang_member_info_faildAuth__);
            }

            $reg_code = smsCertificateNum(); // 인증번호 생성

			$sms_replace['name'] = $search_name;
			$sms_replace['pwd'] = $reg_code;
			include $engine_dir."/_engine/sms/sms_module.php";
			$r = SMS_send_case(22, $search_val);

            $reg_code = numberOnly($reg_code); // 숫자만 저장되도록
		}
		//인증번호저장
        $reg_code_enc = aes128_encode($reg_code, 'join');
		$pdo->query("delete from `$tbl[join_sms]` where phone='$search_val'");
		$pdo->query("insert into `$tbl[join_sms]` (phone, reg_code, reg_date) values ('$search_val', '$reg_code_enc', '$now')");
		exit('OK');
	}else { //인증번호 확인
		$reg_code = $_POST['reg_code'];
        $repstr = smsCertificateNumCheckstr();
        if ($repstr) $reg_code = str_replace($repstr,'',$reg_code);
        $reg_code_enc = aes128_encode($reg_code, 'join');
		$data = $pdo->assoc("select * from `$tbl[join_sms]` where phone='$search_val'");
		if($reg_code_enc != $data['reg_code']) {

    	common_header();
?>
<script type='text/javascript'>
	var f = parent.document.idpwdsearchFrm;
	f.reg_code.value = "";
	alert(_lang_pack.member_idpwd_reg_code);
</script>
<?php
		}else {
			$pdo->query("delete from `$tbl[join_sms]` where phone='$search_val'");

			if($ftype=='2') {
?>
<script type='text/javascript'>
	var search_member_no = '<?=$search_member_no?>';
	parent.$.post('/main/exec.php', {'exec_file':'member/new_find_id.exe.php', 'exec':'pwd_log', 'search_member_no':search_member_no}, function(key) {
		if(key) {
			parent.location.href='/main/exec.php?exec_file=member/modify_pwd.php&key='+key;
		}
	});
</script>
<?php
			}else {
?>
<script type='text/javascript'>
	var find_id_type = '<?=$find_id_type?>';
	var confirm_reg_code = '<?=$reg_code?>';
	var search_val = '<?=$search_val?>';
	var browser_type = '<?=$_SESSION[browser_type]?>';
	var search_name = '<?=$search_name?>';

	if(browser_type == 'mobile') {
		parent.$.post('<?=$root_url?>/main/exec.php?exec_file=member/search_id_pwd.php', {'search_name':search_name, 'search_val':search_val, "reg_code":confirm_reg_code, 'find_id_type':find_id_type}, function(r) {
			parent.$(window).scrollTop(window.oriScroll);
			parent.$('#idpwd_layer').html(r);
		});
	}else {
		parent.$.post('<?=$root_url?>/main/exec.php?exec_file=member/search_id_pwd.php&striplayout=1&stripheader=true', {'search_name':search_name, 'search_val':search_val, "reg_code":confirm_reg_code, 'find_id_type':find_id_type}, function(r) {
			parent.$('#search_id_pwd').remove();
			parent.$('body').append(r);
		});
	}
</script>
<?php
			}
		}
	}
?>