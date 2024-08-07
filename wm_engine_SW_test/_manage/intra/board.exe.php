<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷게시판 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	include $engine_dir."/board/include/lib.php";

	$db = addslashes(trim($_POST['db']));
	$no = numberOnly($_POST['no']);
	$cno = numberOnly($_POST['cno']);
	$exec = $_POST['exec'];
	$QueryString = $_POST['QueryString'];
	$_bconfig = $pdo->assoc("select * from `$tbl[intra_board_config]` where `db`='$db' limit 1");
	if(!$_bconfig[no]) msg("DB 정보가 없습니다");

	$_tbl=$tbl[intra_board];

	if($no){
		$data = $pdo->assoc("select * from `$_tbl` where `no`='$no' limit 1");
		$no=$data[no];
	}

	function setComTotalNum(){
		global $tbl, $data, $db, $pdo;
		if(!$data[no] || !$db) return;
		$total_num=$pdo->row("select count(*) from `$tbl[intra_comment]` where `ref`='$data[no]' and `db`='$db'");
		$pdo->query("update `$tbl[intra_board]` set `total_comment`='$total_num' where `no`='$data[no]' limit 1");
	}
	function setBoardTotalNum(){
		global $tbl, $db, $pdo;
		if(!$db) return;
		$total_num=$pdo->row("select count(*) from `$tbl[intra_board]` where `db`='$db'");
		$pdo->query("update `$tbl[intra_board_config]` set `total_content`='$total_num' where `db`='$db' limit 1");
	}

	if($exec == "delete"){

		if($data[updir]) {
			deletePrdImage($data,1,2);
		}

		if($admin['partner_no'] > 0 && $data['member_id'] != $admin['admin_id']) {
			msg('글 삭제 권한이 없습니다.');
		}

		$sql="delete from `$tbl[intra_comment]` where `ref`='$no'";
		$pdo->query($sql);
		$sql="delete from `$_tbl` where `no`='$no'";
		$pdo->query($sql);
		setBoardTotalNum();

		msg("삭제되었습니다","./?body=intra@board".$QueryString,"parent");

	}elseif($exec == "comment"){

		if($no){
			$ccontent = addslashes(trim($_POST['ccontent']));

			checkBlank($ccontent, "내용을 입력해주세요.");

			$cdata = $pdo->assoc("select * from `$tbl[intra_comment]` where `no`='$cno' limit 1");
			$cno=$cdata[no];
			if($cno){
				$sql="update `$tbl[intra_comment]` set `content`='$ccontent' where `no`='$cno' limit 1";
			}else{
				if(!$admin[name]) $admin[name]=$admin[admin_id];
				$sql="insert into `$tbl[intra_comment]`(`db`, `ref`, `name`, `member_id`, `member_no`, `content`, `ip`, `reg_date`) values('$db', '$no', '$admin[name]', '$admin[admin_id]', '$admin[no]', '$ccontent', '$_SERVER[REMOTE_ADDR]', '$now')";
			}
			$pdo->query($sql);
			setComTotalNum();
		}

		msg("", "reload", "parent");

	} elseif($exec == "comment_delete") {
		$sql="delete from `$tbl[intra_comment]` where `no`='$cno' limit 1";
		$pdo->query($sql);
		setComTotalNum();

		msg("", "reload", "parent");

	}

	$title = addslashes(trim($_POST['title']));
	$content = addslashes(trim($_POST['content']));

	checkBlank($title, "제목을 입력해주세요.");
	checkBlank($content, "내용을 입력해주세요.");

	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	wingUploadRule($_FILES, 'intraCommu');

	$deny_ext = array('php', 'asp', 'wisa', 'html', 'txt', 'inc', 'jsp', 'js', 'htm', 'xml', 'phps', 'php3');
	$updir=$data['updir'];
	for($ii=1; $ii<=2; $ii++) {
		$chg_file="";
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
			deletePrdImage($data,$ii,$ii);
			$up_filename=$ori_filename="";
			$chg_file=1;
		}
		if($_FILES['upfile'.$ii][tmp_name] && $_FILES['upfile'.$ii][size]) {
			if(!$updir) {
				$updir=$dir['upload']."/intra_board/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$ext = getExt($_FILES['upfile'.$ii]['name']);
			if(in_array($den_ext, $ext)) msg("업로드할수 없는 형식의 파일입니다.\t");

			$up_filename=md5($ii+time());
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir);
			$ori_filename=$up_info[1];
			chmod($up_info[2],0777);

			$up_filename=$up_info[0];
			$chg_file=1;
		}

		if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."', `ori_upfile".$ii."`='".$ori_filename."'";
	}

	if($admin['partner_no']>0) {
		if($no > 0) { // 수정 시 기존 데이터
			$tmp_str = $data['view_member'];
		} else { // 신규 등록시 나에게만
			$tmp_str = '@'.$admin['partner_no'].'@';
		}
	} else {
		$tmp_str = '';
		if(is_array($_POST['view_member'])) {
			$tmp_str = '@'.implode('@', numberOnly($_POST['view_member'])).'@';
		}
	}

	if($no){
		$sql="update `$_tbl` set `title`='$title', `content`='$content', `view_member`='$tmp_str' $asql where `no`='$no' limit 1";
		if($admin['partner_no']>0) {
			$gURL="./?mode=view&body=board@board".$QueryString;
		}else {
			$gURL="./?body=intra@board&mode=view".$QueryString;
		}
	}else{
		if(!$admin[name]) $admin[name]=$admin[admin_id];
		$sql="insert into `$_tbl` set `db`='$db', `member_id`='$admin[admin_id]', `name`='$admin[name]', `member_no`='$admin[no]', `member_level`='$admin[level]', `ip`='$_SERVER[REMOTE_ADDR]', `title`='$title', `content`='$content', `view_member`='$tmp_str', `partner_no`='$admin[partner_no]', `reg_date`='$now' $asql";
		if($admin['partner_no']>0) {
			$gURL="./?body=board@board&db=".$db;
		}else {
			$gURL="./?body=intra@board&db=".$db;
		}
	}

	$pdo->query($sql);

	setBoardTotalNum();

	msg("", $gURL, "parent");

?>