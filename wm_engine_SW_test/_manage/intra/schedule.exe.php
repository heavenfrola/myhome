<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  일정 등록/관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	adminCheck(2);

	$exec = $_POST['exec'];
	$no = numberOnly($_POST['no']);
	$wdate = addslashes($_POST['wdate']);
	$content = addslashes($_POST['content']);
	$alarm = addslashes($_POST['alarm']);
	$font_color = addslashes($_POST['font_color']);

	if($exec == "delete"){
		$pdo->query("delete from `$tbl[intra_schedule]` where `no`='$no'");
		msg("","reload","parent");
	}

	checkBlank($wdate, "일정 날짜를 입력해주세요.");
	checkBlank($content, "일정 내용을 입력해주세요.");


	if($no){
		$sql="update `$tbl[intra_schedule]` set `date`='$wdate', `alarm`='$alarm', `font_color`='$font_color', `content`='$content' where `no`='$no'";
	}else{
		$ck=$pdo->row("select `no` from `$tbl[intra_schedule]` where `date`='$wdate'");
		if($ck) msg("해당 날짜에 등록된 일정이 이미 존재합니다");
		$sql="insert into `$tbl[intra_schedule]`(`date`, `alarm`, `content`, `font_color`, `reg_date`) values('$wdate', '$alarm', '$content', '$font_color', '$now')";
	}

	$r=$pdo->query($sql);
	if(!$r) msg("일정등록에 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

	msg("","reload","parent");

?>