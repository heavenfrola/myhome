<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  메일 발송 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	checkBasic();

	$title = trim($_POST['title']);
	$email = trim($_POST['email']);
	$content1 = trim($_POST['content1']);

	checkBlank($title,'메일 제목을 입력해주세요.');
	checkBlank($email,'받는 이의 이메일을 입력해주세요.');
	checkBlank($content1,'메일 내용을 입력해주세요.');

	// 메일
	$mail_case=6;
	include_once $engine_dir."/_engine/include/mail.lib.php";
	$mail_title[6]=$title;
	$r=sendMailContent($mail_case,"",$email);

	msg("메일 발송 완료","reload","parent");

?>