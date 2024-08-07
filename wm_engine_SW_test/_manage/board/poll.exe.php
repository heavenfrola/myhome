<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
	$item_no = numberOnly($_POST['item_no']);

	if($no) {
		$data=get_info($tbl[poll_config],"no",$no);
		if(!$data[no]) {
			msg("존재하지 않는 자료입니다");
		}
	}
	else {
		$no=$pdo->row("select max(`no`) from `$tbl[poll_config]`");
		$no++;
	}

	if($exec=="delete") {
		$ii=0;
		$check_pno = numberOnly($_POST['check_pno']);
		foreach($check_pno as $key=>$val) {
			$ii++;
			$pdo->query("delete from `$tbl[poll_config]` where `no`='$val'");
			$pdo->query("delete from `$tbl[poll_item]` where `ref`='$val'");
			$pdo->query("delete from `$tbl[poll_comment]` where `ref`='$val'");
		}
		msg($ii."개의 설문을 삭제하였습니다","reload","parent");
	}

	$title=$_POST['title'];
	checkBlank($title,"제목을 입력해주세요.");
	$title=addslashes($title);
	$content = addslashes(trim($_POST['content']));
	$item = $_POST['item'];
	$dupl = numberOnly($_POST['dupl']);
	$auth = numberOnly($_POST['auth']);
	$sdate = addslashes(trim($_POST['sdate']));
	$fdate = addslashes(trim($_POST['fdate']));
	$milage = numberOnly($_POST['milage']);

	$total_item=0;
	for($ii=0; $ii<10; $ii++) {
		$item[$ii] = addslashes(trim($item[$ii]));
		if(!$item[$ii]) {
			if($item_no[$ii]) {
				$pdo->query("DELETE FROM `$tbl[poll_item]` where `no`='".$item_no[$ii]."'");
			}
			continue;
		}

		$total_item++;

		if($item_no[$ii]) {
			$pdo->query("update `$tbl[poll_item]` set `title`='".addslashes($item[$ii])."' , `sort`='$total_item' where `no`='".$item_no[$ii]."'");
		}
		else {
			$pdo->query("INSERT INTO `$tbl[poll_item]` ( `ref` , `title` , `total` , `sort` ) VALUES ('$no', '".addslashes($item[$ii])."', '0', '$total_item')");
		}

	}
	if($total_item<1) {
		msg("하나 이상의 문항을 입력하세요");
	}

	$ii=1;
	$updir="_data/poll_imgs";
	makeFullDir($updir);
	$data[updir]=$updir;
	$up_filename=$data['upfile'.$ii];
	if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
		deletePrdImage($data,1,1);
		$up_filename="";
	}
	if($_FILES['upfile'.$ii][tmp_name]) {
		$up_filename=md5($ii+time()); // 새파일명
		$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
		$up_filename=$up_info[0];
	}

	if($data[no]) {
		$pdo->query("UPDATE `$tbl[poll_config]` set `title`='$title' , `dupl`='$dupl' , `auth`='$auth' , `sdate`='$sdate' , `fdate`='$fdate' , `milage`='$milage', `total_item`='$total_item', `content`='$content', `upfile1`='$up_filename' where `no`='$no'");
		$ems="수정되었습니다";
	}
	else {
		$pdo->query("INSERT INTO `$tbl[poll_config]` ( `no` , `title` , `dupl` , `auth` , `sdate` , `fdate` , `stat`, `milage`, `total_vote` , `total_item` , `reg_date` , `voted`, `content`, `upfile1`) VALUES ('$no', '$title', '$dupl', '$auth', '$sdate', '$fdate', '$stat', '$milage', '$total_vote', '$total_item', '$now', '@', '$content', '$up_filename')");
		$ems="등록되었습니다";
	}


	msg($ems,"./?body=board@poll_list","parent");

?>