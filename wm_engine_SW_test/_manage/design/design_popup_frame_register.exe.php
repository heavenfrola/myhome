<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업스킨 편집 처리
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
	checkBasic();
	if($no) {
		$data=get_info($tbl['popup_frame'], 'no', $no);
		if(!$data['no']) msg("존재하지 않는 데이터입니다");
	}

	if($exec=="delete") {
		deletePrdImage($data,1,3);
		$pdo->query("delete from {$tbl['popup_frame']} where `no`='$no'");
		msg("삭제되었습니다","./?body=design@design_popup_frame","parent");
	}

	$title=addslashes($_POST['title']);
	$content=addslashes($_POST['content']);
	$content=str_replace($root_url."/_manage/{창닫기}", "{창닫기}", $content);
	$content=str_replace($root_url."/_manage/{하루창}", "{하루창}", $content);

	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	wingUploadRule($_FILES, 'popupSkin');

	$updir=$data['updir'];
	for($ii=1; $ii<=3; $ii++) {
		$chg_file="";
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii]['tmp_name'])) {
			deletePrdImage($data,$ii,$ii);
			$up_filename=$width=$height="";
			$chg_file=1;
		}
		if($_FILES['upfile'.$ii]['tmp_name']) {
			if(!$updir) {
				$updir=$dir['upload']."/".$dir['popup']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename=md5($ii+time());
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
			list($width, $height)=getimagesize($up_info[2]);

			$up_filename=$upfile[$ii]=$up_info[0];
			$chg_file=1;
		}

		if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."'";
	}

	if(!$html) $html="2";

	if($no) {
		$sql="update {$tbl['popup_frame']} set `title`='$title' , `content`='$content' , `html`='$html' $asql where `no`='$no'";
		$pdo->query($sql);
		msg("수정되었습니다","./?body=design@design_popup_frame","parent");
	}
	else {
		$sql="INSERT INTO {$tbl['popup_frame']} ( `title` , `content` , `html` , `reg_date` , `upfile1` , `upfile2` , `upfile3` , `updir` ) VALUES ('$title', '$content', '$html', '$now', '$upfile[1]', '$upfile[2]', '$upfile[3]', '$updir')";
		$pdo->query($sql);
		msg("등록되었습니다","./?body=design@design_popup_frame","parent");
	}

?>