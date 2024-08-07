<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  단체메일 발송 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();
	set_time_limit(0);
	include_once $engine_dir."/_manage/member/group_mail.lib.php";

	// 가능 건수 체크
	$mail_ck = mailLimitCk();
	$total_mail = $_POST['total_mail'];

	// 발송건수가 허용 건수를 초과할 경우
	$over_mail = number_format($total_mail - $mail_ck[3]);
	if($total_mail > $mail_ck[3]) msg("현재 최대 발송 가능 건수를 ".$over_mail." 건 초과하여 전송이 불가능합니다.");

	// 첨부이미지
	$temp = $_POST['temp'];
	$updir1="/".$dir['upload']."/".$dir['mail']."/";
	if($_POST['bizmail'] == 'Y') $old_dir = $updir1.'real_'.$temp;
	else $old_dir=$updir1."temp_".$temp;

	$exec = $_POST['exec'];
	if($exec=="upload") {
		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		$fcnt = 0;
		$dir = opendir($root_dir.$old_dir);
		while($dc = readdir($dir)) {
			if(is_file($root_dir.$old_dir.'/'.$dc)) $fcnt++;
		}
		wingUploadRule($_FILES, 'email', $fcnt);

		makeFullDir($old_dir);
		if(!is_dir($root_dir.$old_dir)) {
			msg("$old_dir 업로드 디렉토리가 생성되지 않았습니다");
		}

		if(!$_FILES["upfile"]["name"]) {
			msg("업로드할 파일을 입력하세요");
		}
		$up_filename=md5(time()); // 새파일명
		$up_info=uploadFile($_FILES["upfile"],$up_filename,$old_dir,"jpg|jpeg|gif|png|bmp");

		msg("","reload","parent");
	}
	if($exec == 'delete') {
		$img = str_replace("\/", '', $_POST['img']);
		if(is_file($root_dir.$old_dir.'/'.$img)) {
			unlink($root_dir.$old_dir.'/'.$img);
		}
		msg('', 'reload', 'parent');
	}

	// 발송
	$content2 = addslashes(trim($_POST['content2']));

	$content="<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=euc-kr\"><link rel=\"stylesheet\" type=\"text/css\" href=\"".$root_url."/_template/mail/style.css\"></head><body LEFTMARGIN=\"0\" TOPMARGIN=\"0\" MARGINWIDTH=\"0\" MARGINHEIGHT=\"0\">".$content2."</body></html>";

	$msplit=":";
	$send_mail=0;

	$to_name = str_replace('&', '', $_POST['to_name']);
	$to_mail = str_replace('&', '', $_POST['to_mail']);
	$_to_name=explode($msplit, $_POST['to_name']);
	$_to_mail=explode($msplit, $_POST['to_mail']);
	$from_name = trim($_POST['from_name']);
	$from_mail = trim($_POST['from_mail']);
	$title = trim($_POST['title']);

	if($total_mail!=count($_to_name) || count($_to_name)!=count($_to_mail)) {
		msg("수신자 정보가 정확하지 않습니다");
	}
	if(!$total_mail) {
		msg("수신자가 없습니다");
	}

	$reg_date=$now;
	$start_date=$finish_date="";
	$stat=1;

	$new_dir=$updir1.$reg_date;
	if(is_dir($root_dir.$old_dir)) {
		@rename($root_dir.$old_dir,$root_dir.$new_dir);
	}
	$content=str_replace($root_url.$old_dir,$root_url.$new_dir,$content);

	$to_name = mb_convert_encoding($to_name, 'euckr', _BASE_CHARSET_);
	$from_name = mb_convert_encoding($from_name, 'euckr', _BASE_CHARSET_);
	$title = mb_convert_encoding($title, 'euckr', _BASE_CHARSET_);
	$content = urlencode(mb_convert_encoding(urldecode($content), 'euckr', _BASE_CHARSET_));
	$we_mail->call('insert', array('args1'=>$we_mail->config['account_idx'], 'args2'=>$from_name, 'args3'=>$from_mail, 'args4'=>$to_name, 'args5'=>$to_mail, 'args6'=>$title, 'args7'=>$content, 'args8'=>$root_url));
	if($we_mail->result != 'OK') msg(mb_convert_encoding($we_mail->result, _BASE_CHARSET_, 'euckr'));

?>
<script type='text/javascript'>
	if (confirm('메일 발송 예약이 완료되었습니다.\n\n메일 발송 확인 페이지로 이동하시겠습니까?\n')) {
		parent.opener.location.href='./?body=wing@service@email_list';
		parent.window.close();
	} else {
		parent.window.close();
	}
</script>