<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";

	checkBasic();

	$no = numberOnly($_POST['no']);
	$poll = numberOnly($_POST['poll']);

	if($exec == "com_insert"){
		if($member[no]) $name=$member[name];
		$sql="insert into `$tbl[poll_comment]`(`ref`, `name`, `member_id`, `member_no`, `pwd`, `content`, `ip`, `reg_date`) values('$ref', '$name', '$member[member_id]', '$member[no]', '$pwd', '$content', '$REMOTE_ADDR', '$now')";
		$pdo->query($sql);
		msg("", "reload", "parent");
	}else if($exec == "com_delete"){
		$data=get_info($tbl[poll_comment],"no",$no);
		if(!$data[no]) msg(__lang_common_error_nodata__);
		if(($data[pwd] && $data[pwd] == $pwd) || ($data[member_no] && $data[member_no] == $member[no]) || $member[level] == 1 || $admin[no]){
			$pdo->query("delete from `$tbl[poll_comment]` where `no`='$no'");
			msg(__lang_common_error_deleted__, $rUrl, 'parent');
		}
		msg(__lang_member_error_cpwd__);
	}

	$data=get_info($tbl[poll_config],"no",$no);
	if(!$data[no]) msg(__lang_shop_error_nopoll__);
	if(!$_POST[poll]) msg(__lang_shop_select_pollitem__);
	$now_ymd=date("Y-m-d",$now);
	if($data[sdate]>$now_ymd || $data[fdate]<$now_ymd) {
		msg(	__lang_shop_error_polldate__);
	}

	if($data[auth]==2 && !$member[no]) {
		msg(__lang_common_error_memberOnly__);
	}

	if($data[dupl]=="2") {
		if($member[no]) {
			$voteck = explode("@", $data[voted]);
			if ( in_array ($member[no], $voteck)) $avoted = 1;
			$voted=$data[voted].$member[no]."@";
			$asql=", `voted`='$voted'";
		}
		else {
			if($_COOKIE['poll_'.$no]) $avoted=1;
			$cookie_time=$now+31536000;  //60*60*24*365
			setcookie('poll_'.$no, "Y", $cookie_time, "/");
		}

		if($avoted) msg(__lang_shop_error_checked__);

		if ($member[no] && $data[milage]) ctrlMilage("+",7,$data[milage],$member,$data[title]);
	}

	$sql="update `$tbl[poll_config]` set `total_vote`=`total_vote`+1 $asql where `no`='$no'";
	$pdo->query($sql);

	$sql="update `$tbl[poll_item]` set `total`=`total`+1 where `no`='$poll'";
	$pdo->query($sql);

	if(!$gurl) $gurl=$root_url."/shop/poll_list.php?no=$data[no]";

	if($gurl) msg(__lang_shop_info_pollOK__, $gurl, 'parent');
	else msg(__lang_shop_info_pollOK__);

?>