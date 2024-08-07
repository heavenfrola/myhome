<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업 등록/수정/삭제 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	checkBasic();

	addField($tbl['popup'], 'device', 'varchar(6)');
	addField($tbl['popup'], 'name', 'varchar(100)');
	addField($tbl['popup'], 'page', 'varchar(20) not null');
	addField($tbl['popup'], 'page_detail', 'varchar(500) not null');

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];

	if($no) {
		$data = get_info($tbl['popup'], 'no', $no);
		if(!$data['no']) msg("존재하지 않는 데이터입니다");
	}

	if($exec=="delete") {
		deletePrdImage($data,1,3);
		$pdo->query("delete from {$tbl['popup']} where `no`='$no'");

        $editor_file = new EditorFile();
        $editor_file->removeId('popup', $no);

		msg("삭제되었습니다","./?body=design@design_popup","parent");
	}

	if($exec == 'toggle') {
		$no = numberOnly($_POST['no']);
		$cnt_qry = base64_decode($_POST['cnt_qry']);
		$use_popup = $pdo->row("select `use` from {$tbl['popup']} where no='$no'");
		$use_popup = ($use_popup == 'Y') ? 'N' : 'Y';
		$pdo->query("update {$tbl['popup']} set `use`='$use_popup' where no='$no'");

		$cntres = $pdo->iterator($cnt_qry);
        foreach ($cntres as $tmp) {
			$cnt[$tmp['use']] = $tmp['cnt'];
		}

		header('Content-type:application/json;');
		exit(json_encode(array(
			'changed' => $use_popup,
			'Y' => number_format($cnt['Y']),
			'N' => number_format($cnt['N'])
		)));
	}

	$name = addslashes(trim($_POST['name']));
	$content = addslashes(trim($_POST['content']));
	$device = addslashes($_POST['device']);
	$frame = addslashes($_POST['frame']);
	$start_date = addslashes($_POST['start_date']);
	$finish_date = addslashes($_POST['finish_date']);
	$use = ($_POST['use'] == 'Y') ? 'Y' : 'N';
	$html = numberOnly($_POST['html']);
	$posx = numberOnly($_POST['posx']);
	$posy = numberOnly($_POST['posy']);
	$w = numberOnly($_POST['w']);
	$h = numberOnly($_POST['h']);
	$layer = ($_POST['layer'] == 'Y') ? 'Y' : 'N';
	$page = '@'.implode('@', $_POST['page']).'@'.$pages;
	$page_detail = $_POST['page_detail'];
	$unique = addslashes($_POST['unique']);

	if($page == '' || $page == '@@') msg('팝업 노출 페이지는 반드시 한 가지 이상 선택하셔야 합니다.');

	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	wingUploadRule($_FILES, 'popup');

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
				if($cfg['use_icb_storage'] == 'Y') {
					$dir['upload'] = $cfg['current_icb_updir'];

					$asql1 .= ", upurl";
					$asql2 .= ", '{$cfg['current_icb_upurl']}'";
				}
				$updir=$dir['upload']."/".$dir['popup']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename=md5($ii+time()); // 새파일명
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
			list($width, $height)=getimagesize($up_info[2]);

			$up_filename=$upfile[$ii]=$up_info[0];
			$chg_file=1;
		}

		if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."'";
	}

	if(!$html) $html="2";

	if($no) {
		$sql="update {$tbl['popup']} set name='$name', `use`='$use' , `device`='$device', `start_date`='$start_date' , `finish_date`='$finish_date' , `frame`='$frame' , `content`='$content' , `html`='$html', `w`='$w' , `h`='$h' , `x`='$posx' , `y`='$posy', `layer`='$layer', page='$page', page_detail='$page_detail' $asql where `no`='$no'";
		$pdo->query($sql);
		$msg = '수정되었습니다.';
	}
	else {
		$sql="INSERT INTO {$tbl['popup']} (name, `use` , `device`, `start_date` , `finish_date` , `frame` , `content` , `html` , `upfile1` , `upfile2` , `upfile3` , `updir` , `hit` , `reg_date` , `w` , `h` , `x` , `y` , `layer`, page, page_detail $asql1) VALUES ('$name', '$use', '$device', '$start_date', '$finish_date', '$frame', '$content', '$html', '$upfile[1]', '$upfile[2]', '$upfile[3]', '$updir', '$hit', '$now', '$w', '$h', '$posx', '$posy' ,'$layer', '$page', '$page_detail' $asql2)";
		$pdo->query($sql);
		$no = $pdo->lastInsertId();
		$msg = '등록되었습니다.';
	}

    $editor_file = new EditorFile();
    $editor_file->lock('popup', $no, $_POST['editor_code']);

    if ($pdo->geterror()) {
        msg('데이터베이스 오류');
    }

	msg($msg, './?body=design@design_popup', 'parent');

?>