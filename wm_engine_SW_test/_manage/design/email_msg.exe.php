<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  자동메일내용 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	$mail_case = numberOnly($_POST['mail_case']);
	$content2 = addslashes($_POST['content2']);
	$exec = addslashes($_POST['exec']);
	checkBlank($mail_case,"메일정보를 입력해주세요.");

	include_once $engine_dir."/_engine/include/mail.lib.php";
	if(!$cfg['mail_lang']) $cfg['mail_lang'] = $_POST['mail_lang'];

	$_mtitle=$_mail_menu_arr[$mail_case];
	if(!$_mtitle) msg("잘못된 코드입니다");
	$code="mail_case_msg".$mail_case;
	if(!$cfg['mail_lang']) $cfg['mail_lang'] = 'kor';
	if($cfg['mail_lang']) $code .= "_".$cfg['mail_lang'];

	if(empty($_POST['email_title_'.$mail_case]) == false) {
		$title_code = 'email_title_'.$cfg['mail_lang'].'_'.$mail_case;
		$title_value = addslashes($_POST['email_title_'.$mail_case]);
		$title_chk = $pdo->row("select code from {$tbl['default']} where code='$title_code'");
		if($title_chk) {
			$pdo->query("update {$tbl['default']} set value='$title_value' where code='$title_chk'");
		}else {
			$pdo->query("insert into {$tbl['default']} (code, value, ext) values ('$title_code', '$title_value', '$now')");
		}
	}
	if($exec == "delete"){
		$pdo->query("delete from {$tbl['default']} where `code`='".$code."'");
		msg("초기화되었습니다","reload","parent");
	}

	checkBlank($content2,"메일내용을 입력해주세요.");
	$mcontent=genMailContent($mail_case,1);
	if($mcontent != stripslashes($content2)){
		updateWMCode($code,$content2,$now);
	}

	msg("저장되었습니다.","reload","parent");

?>