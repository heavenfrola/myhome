<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	checkBasic();

	$exec = addslashes($_POST['exec']);
	$cdno = numberOnly($_POST['cdno']);
	$no = numberOnly($_POST['no']);

	function couponLogw($cno, $cname, $stat, $type="", $content="", $code="") {
		global $tbl, $admin, $pdo;

		return $pdo->query("
            insert into {$tbl['sccoupon_log']}
            (type, stat, scno, code, name, admin_id, admin_no, content, ip, reg_date)
            values (
                '$type', '$stat', '$cno', '$code', '$cname',
                '{$admin['admin_id']}', '{$admin['no']}', '$content', '{$_SERVER['REMOTE_ADDR']}', unix_timestamp(now())
            )
        ");
	}

	function makeCode($no){
		global $_auth_code;
		$rand=mt_rand();
		$tmp=md5($rand);
		$tmp_code=substr($tmp,0,10);
		$tmp_code=$no."-".strtoupper($tmp_code);
		if(strchr($_auth_code, $tmp_code)) return "";
		else return $tmp_code;
	}

	if(empty($cfg['sccoupon'])) msg('쿠폰 기능이 정상적으로 셋팅되지 않았습니다');

	if($no) {
		$data=get_info($tbl['sccoupon'], "no", $no);
		if(!$data[no]) msg("존재하지 않는 자료입니다");
	}

	$_logcontent="";
	foreach($_POST as $key => $val){
		$_logcontent.= "$key:$val<wisa>";
	}

	if($exec == 'delete') {

		$sql="delete from `$tbl[sccoupon]` where `no`='$no'";
		$res=$pdo->query($sql);

		$sql="delete from `$tbl[sccoupon_code]` where `scno`='$no'";
		$res=$pdo->query($sql);

		couponLogw($no, $data['name'], 3, $data['is_type'], $_logcontent);

		msg('삭제되었습니다.', 'reload', 'parent');
	}

	if($exec == 'delete_code') {

		$code=$pdo->row("select `code` from `$tbl[sccoupon_code]` where `no`='$cdno'");

		$sql="delete from `$tbl[sccoupon_code]` where `no`='$cdno'";
		$res=$pdo->query($sql);

		couponLogw($no, $data['name'].' '.$code, 3, $data['is_type'], $_logcontent);

		msg('삭제되었습니다.', 'reload', 'parent');
	}

	$date_type = addslashes($_POST['date_type']);
	$is_type = addslashes($_POST['is_type']);
	$name = addslashes($_POST['name']);
	$start_date = addslashes($_POST['start_date']);
	$finish_date = addslashes($_POST['finish_date']);
	$memo = addslashes($_POST['memo']);
	$issue_type = numberOnly($_POST['issue_type']);
	$milage_prc=numberOnly($_POST['milage_prc']);
	$cno=numberOnly($_POST['cno']);
	$cpn_ea=numberOnly($_POST['cpn_ea']);

	if($_POST['download_cnt'] == "Y") {
		$sql="update `$tbl[sccoupon]` set `date_type`='$date_type', `start_date`='$start_date', `finish_date`='$finish_date', `memo`='$memo' where `no`='$no'";
		$r=$pdo->query($sql);
		msg("수정되었습니다","?body=promotion@sccoupon", "parent");
		couponLogw($no, $data['name'], 2, $data['is_type'], $_logcontent);
	}

	checkBlank($name,'쿠폰명을 입력해주세요.');

	if($is_type == 1) {
		checkBlank($milage_prc, '쿠폰 적립액을 입력해주세요.');
	} else {
		checkBlank($cno, '교환 쿠폰을 입력해주세요.');
	}

	if(empty($data['no'])) {
		if($issue_type == 1) {
			checkBlank($cpn_ea, '생성 쿠폰갯수를 입력해주세요.');
		} else {
			checkBlank($_FILES['cpn_file'], '생성할 csv파일을 입력해주세요.');
		}
	}

	if($date_type == 2) {
		checkBlank($start_date, '교환시작일을 입력해주세요.');
		checkBlank($finish_date, '교환종료일을 입력해주세요.');
	}

	if($data['no']) {

		$sql="update `$tbl[sccoupon]` set `is_type`='$is_type', `name`='$name', `milage_prc`='$milage_prc', `cno`='$cno', `date_type`='$date_type', `start_date`='$start_date', `finish_date`='$finish_date', `memo`='$memo' where `no`='$data[no]'";
		$r=$pdo->query($sql);

		$mode='edit';

		couponLogw($data['no'], $name, 2, $is_type, $_logcontent);
	}
	else {
		$sql="INSERT INTO `$tbl[sccoupon]` (`is_type`, `name` , `milage_prc`, `cno`, `date_type`, `start_date`, `finish_date`, `reg_date`, `memo`) values ('$is_type', '$name', '$milage_prc', '$cno', '$date_type', '$start_date', '$finish_date', '$now', '$memo')";
		$pdo->query($sql);

		$cno = $pdo->lastInsertId();

		$mode='new';

		couponLogw($cno, $name, 1, $is_type, $_logcontent);
	}

	if(!$data['no']) $data['no']=$cno;
	if($issue_type == 1) {
		if($cpn_ea) {
			for($ii=0; $ii < $cpn_ea; $ii++) {
				while(!$tmp_code) {
					$tmp_code=makeCode($data['no']);
				}
				$_auth_code.=$tmp_code."@";
				$pdo->query("insert into `$tbl[sccoupon_code]` (`scno`, `code`, `reg_date`) values ('$data[no]', '$tmp_code', '$now')");
				$tmp_code="";
			}
		}
	} else { // csv 생성시

		if($_FILES['cpn_file']['tmp_name'] && $issue_type == 2) {
			$fp=fopen($_FILES['cpn_file']['tmp_name'], 'r');
			while($csv=fgetcsv($fp, 1000, ",")) {
				$tmp_code=trim($csv[0]);
				$pdo->query("insert into `$tbl[sccoupon_code]` (`scno`, `code`, `reg_date`) values ('$data[no]', '$tmp_code', '$now')");
			}
		}
	}

	if($mode == 'edit') msg("수정되었습니다","?body=promotion@sccoupon", "parent");
	else msg("쿠폰이 생성되었습니다","?body=promotion@sccoupon","parent");

?>