<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  NEW 아이디/비번찾기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member[level]!=10) msg("","/","parent");
	checkBasic();

	$exec = addslashes($_POST['exec']);
	$name = addslashes($_POST['name']);
	$member_id = addslashes($_POST['member_id']);
	$ftype = numberOnly($_POST['ftype']); //아이디찾기, 비번찾기 구분
	$find_id_type = numberOnly($_POST['find_id_type']); //휴대폰, 이메일 구분
	if(!$_POST['find_id_type'] && $_POST['find_pw_type']) $find_id_type = numberOnly($_POST['find_pw_type']); //휴대폰, 이메일 구분
    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='key' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['pwd_log']}'");
    if ($data_type != 'varchar(100)') {
        modifyField($tbl['pwd_log'], 'key', 'VARCHAR(100)');
    }
	if($exec=='pwd_log') {
		$search_member_no = numberOnly($_POST['search_member_no']);
		$data = $pdo->assoc("select * from `$tbl[member]` where no='$search_member_no'");

		if($data['no'] != $search_member_no){
			echo '0';
			exit;
		}

		$key = mt_rand(123456,987654);
		$pdo->query("update `$tbl[pwd_log]` set `stat`=2 where `member_id`='$data[member_id]'");
        $key_enc = aes128_encode($key, 'pwd_log');
		$log_sql = "insert into `$tbl[pwd_log]` (`stat`, `member_no`, `member_id`, `member_name`, `email`, `key`, `ip`, `reg_date`) values('1', '$data[no]', '$data[member_id]', '$data[name]', '$data[email]', '$key_enc', '$_SERVER[REMOTE_ADDR]', '$now')";
		$r = $pdo->query($log_sql);
		if($r) {
			echo $key;
			exit;
		}
	}

	checkBlank(stripslashes($name), __lang_member_input_name__);

	if($ftype==1) { //아이디찾기
		$asql = " and `withdraw` in ('N', 'D1')";
		$asql2 = " and `name`='$name'";
		switch($find_id_type) {
			case '2' :
				$email = addslashes($_POST['email']);
				$asql2 .= " and `email`='$email'";
				$name_text = "이메일 주소";
				$find_text = $email;
				break;
			case '3' :
				$cell = $_POST['cell'];
				if(is_array($cell)) {
					$cell = $cell[0].$cell[1].$cell[2];
				}else {
					if(preg_match("/-/", $cell)) {
						$cell = str_replace('-', '', $cell);
					}
				}
				$asql2 .= " and replace(cell, '-', '')='$cell'";
				$name_text = "휴대전화 번호";
				$find_text = $cell;
				break;
		}
		$data = $pdo->assoc("select * from `$tbl[member]` where 1 $asql $asql2");
		if($data == false) {
			$data = $pdo->assoc("select * from {$tbl['member_deleted']} where 1 $asql2");
		}
		if(!$data[no]) msg(__lang_member_idpwd_nomember__);
	}else { //비밀번호찾기
		$find_pw_type = numberOnly($_POST['find_pw_type']);

		$err = 0;
		checkBlank($member_id,__lang_member_input_memberid__);
		$asql = " and `withdraw` in ('N', 'D1')";
		$asql2 = " and `name`='$name' and `member_id`='$member_id'";
		$email = addslashes($_POST['email']);

		switch($find_pw_type) {
			case '1' :
				// 구 주민번호 처리 부분
			break;
			case '2' :
				$asql2 .= " and `email`='$email'";
				break;
			case '3' :
				$cell = $_POST['cell'];
				if(is_array($cell)) {
					$cell = $cell[0].$cell[1].$cell[2];
				}else {
					if(preg_match("/-/", $cell)) {
						$cell = str_replace('-', '', $cell);
					}
				}
				$asql2 .= " and replace(cell, '-', '')='$cell'";
			break;
		}

		$data = $pdo->assoc("select * from `$tbl[member]` where 1 $asql $asql2");
		if($data == false) {
			$data = $pdo->assoc("select * from {$tbl['member_deleted']} where 1 $asql2");
		}
		if($data[no]) {
			if($find_id_type==2) {//이메일
				checkBlank($email,__lang_member_input_email__);
				if($data[email]!=$email) $err++;
			}else { //SMS
				checkBlank($cell,__lang_member_input_cell__);
				if(numberOnly($cell) != numberOnly($data['cell'])) $err++;
			}
		}else {
			$err++;
		}

		if($err>0) {
			msg(__lang_member_idpwd_nomember__);
		}
	}
?>
<script type="text/javascript">
	var ftype = '<?=$ftype?>';
	var member_id = '<?=$member_id?>';
	var browser_type = '<?=$_SESSION[browser_type]?>';

	if(ftype==1) { //아이디 찾기
		var f = parent.document.findFrm1;
		var cell = '<?=$cell?>';
		var find_id_type = parent.$(f.find_id_type).filter(':checked').val();
	}else { //비밀번호 찾기
		var f = parent.document.findFrm2;
		var cell = '<?=$cell?>';
		var find_id_type = parent.$(f.find_pw_type).filter(':checked').val();
	}

	if(browser_type == 'mobile') {
		if(!parent.$('#idpwd_layer').length) {
			parent.$('body').append("<div id='idpwd_layer' style='display:none; overflow-y:scroll; position:fixed; z-index:10; width:100%; height:100%; background-color:#fff;left:0px;top:0px;'></div>");
		}
		parent.$.post('<?=$root_url?>/main/exec.php?exec_file=member/search_id_pwd.php&striplayout=1&stripheader=true', {'ftype':ftype, 'find_id_type':find_id_type, 'name':f.name.value, 'email':f.email.value,'cell':cell, 'member_id':member_id}, function(data) {
			parent.$('#idpwd_layer').show();
			parent.$('#idpwd_layer').html(data);
			window.oriScroll = parent.$(window).scrollTop();
		});
	}else {
		parent.setDimmed();
		parent.$.post('<?=$root_url?>/main/exec.php?exec_file=member/search_id_pwd.php&striplayout=1&stripheader=true', {'ftype':ftype, 'find_id_type':find_id_type, 'name':f.name.value, 'email':f.email.value,'cell':cell, 'member_id':member_id}, function(data) {
			if(parent.$('#search_id_pwd').length>0) {
				parent.$('#search_id_pwd').remove();
			}
			parent.$('body').append(data);
		});
	}
</script>