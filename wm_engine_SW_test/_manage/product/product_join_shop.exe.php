<?PHP

	include_once $engine_dir.'/_engine/include/file.lib.php';


	// 첨부파일 다운로드
	if($_REQUEST['exec'] == 'download') {
		$no = numberOnly($_GET['no']);
		$target = numberOnly($_GET['target']);
		$data = $pdo->assoc("select updir, upfile$target from $tbl[partner_shop] where no='$no'");
		if(!$data['upfile'.$target]) msg('첨부파일이 없습니다.');

		if(fsConFolder($data['updir'])) {
			fsFileDown($data['updir'], $data['upfile'.$target], $root_dir.'/'.$dir['upload'].'/temp');
			$path = $root_dir.'/'.$dir['upload'].'/temp/'.$data['upfile'.$target];
			$delete = 'Y';
		} else {
			$path = $root_dir.'/'.$data['updir'].'/'.$data['upfile'.$target];
		}
		$filesize = filesize($path);

		Header("Content-Type: file/unknown");
		Header("Content-Disposition: attachment; filename=".$data['upfile'.$target]);
		Header("Content-Length: ".$filesize);
		header("Content-Transfer-Encoding: binary ");
		Header("Pragma: no-cache");
		Header("Expires: 0");
		flush();

		if($fp = fopen($path, "r")) {
			echo fread($fp, $filesize);
		}
		fclose($fp);
		if($delete == 'Y') unlink($path);

		exit;
	}

	// 상품등록시 실시간 수수료율 수집
	if($_GET['exec'] == 'getRate') {
		$no = numberOnly($_GET['no']);
		$rates = $pdo->row("select partner_rate from $tbl[partner_shop] where no='$no'");
		$tmp = explode(',', $rates);
		$_partner_rate = array();
		foreach($tmp as $val) {
			$val = numberOnly($val, true);
			if(strlen($val) > 0) $_partner_rate[] = $val;
		}

		exit(json_encode($_partner_rate));
	}

	// 담당자 리스트 출력, 추가
	if($_POST['exec'] == 'dam' || $exec =='dam') {
		if(!$partner_no) $partner_no = numberOnly($_POST['partner_no']);

		$new_mng_no = numberOnly($_POST['new_mng_no']);
		if($new_mng_no > 0) {
			$pdo->query("update $tbl[mng] set partner_no='$partner_no' where no='$new_mng_no'");
		}

		$del_mng_no = numberOnly($_POST['del_mng_no']);
		if($del_mng_no > 0) {
			$pdo->query("update $tbl[mng] set partner_no='' where no='$del_mng_no'");
		}

		$res = $pdo->iterator("select no, admin_id, name from $tbl[mng] where partner_no='$partner_no'");
        foreach ($res as $mdata) {
			$mdata = array_map('stripslashes', $mdata);
			echo "<li>$mdata[admin_id] ($mdata[name]) <a href='#' onclick='removeDam($mdata[no]); return false;'>[삭제]</a></ii>";
		}
		return;
	}

	// 파트너 관리자 로그인
	if($_POST['exec'] == 'connect') {
		$_SESSION['partner_login_no'] = numberOnly($_POST['no']);
		exit;
	}

	// 파트너 삭제
	if($_POST['exec'] == 'remove') {
		$partner_no = numberOnly($_POST['partner_no']);
		$cnt = $pdo->row("select count(*) from {$tbl['product']} where partner_no='$partner_no'");

		header('Content-type:application/json; charset='._BASE_CHARSET_);
		if($cnt > 0) {
			exit(json_encode(array('status'=>'error', 'message'=>"등록된 상품이 있는 경우 해당 입점사를 삭제할 수 없습니다.\n\n입점사 삭제를 진행하기 위해서는 상품 휴지통을 포함하여\n모든 상품을 삭제해주세요.")));
		} else {
			$pdo->query("update {$tbl['partner_shop']} set stat=5 where no='$partner_no'");
			exit(json_encode(array('status'=>'success', 'message'=>'입점사가 삭제되었습니다.')));
		}
	}

	// 파트너 저장
	$no = numberOnly($_POST['no']);
	$corporate_name = addslashes(trim($_POST['corporate_name']));
	$biz_num = addslashes(trim($_POST['biz_num']));
	$com_num = addslashes(trim($_POST['com_num']));
	$service_type1 = addslashes(trim($_POST['service_type1']));
	$service_type2 = addslashes(trim($_POST['service_type2']));
	$ceo = addslashes(trim($_POST['ceo']));
	$zipcode = addslashes(trim($_POST['zipcode']));
	$addr1 = addslashes(trim($_POST['addr1']));
	$addr2 = addslashes(trim($_POST['addr2']));
	$email = addslashes(trim($_POST['email']));
	$cell = addslashes(trim($_POST['cell']));
	$siteurl = addslashes(trim($_POST['siteurl']));
	$title = addslashes(trim($_POST['title']));
	$content = addslashes(trim($_POST['content']));
	$bank_name = addslashes(trim($_POST['bank_name']));
	$bank = addslashes(trim($_POST['bank']));
	$bank_account = addslashes(trim($_POST['bank_account']));
	$partner_rate = addslashes(trim($_POST['partner_rate']));
	$stat = addslashes(trim($_POST['stat']));
	$dates = ($_POST['dates']) ? strtotime($_POST['dates']) : 0;
	$datee = ($_POST['datee']) ? strtotime($_POST['datee'])+86399 : 0;
	$account_dates = addslashes(trim($_POST['account_dates']));
	$partner_sms_use = ($_POST['partner_sms_use'] == "Y") ? "Y" : "N";

	if(!$corporate_name) msg('협력사명을 입력해주세요.');

	if($_FILES['upfile1']) {
		if($no) {
			$data = $pdo->assoc("select updir, upfile1 from $tbl[partner_shop] where no='$no'");
			$updir = $data['updir'];
		}

		$file = $_FILES['upfile1'];
		if($file['size'] > 0) {
			if($data['upfile1']) {
				deletePrdImage($data, 1, 1);
			}

			if(!$updir) {
				$updir = $dir['upload'].'/intra_board/partner/';
				makeFullDir($updir);
				$asql .= " , `updir`='$updir'";
			}

			$up_filename = md5($upfile['name'].$now);
			$up_info = uploadFile($file, $up_filename, $updir);
			$upfile1 = $up_info[0];
			$asql = ", updir='$updir', upfile1='$upfile1'";
		}
	}

	if($no) {
		$pdo->query("
			update $tbl[partner_shop] set
				corporate_name='$corporate_name', biz_num='$biz_num', com_num='$com_num', service_type1='$service_type1', service_type2='$service_type2', ceo='$ceo',
				zipcode='$zipcode', addr1='$addr1', addr2='$addr2',
				email='$email', cell='$cell', siteurl='$siteurl',
				title='$title', content='$content',
				bank_name='$bank_name', bank='$bank', bank_account='$bank_account', partner_rate='$partner_rate',
				stat='$stat', dates='$dates', datee='$datee', account_dates='$account_dates' , partner_sms_use='$partner_sms_use' $asql
			where no='$no'
		");
	} else {
		$pdo->query("
			insert into $tbl[partner_shop] (
				corporate_name, biz_num, com_num, service_type1, service_type2, ceo,
				zipcode, addr1, addr2,
				email, cell, siteurl,
				title, content,
				bank_name, bank, bank_account, partner_rate,
				stat, dates, datee, account_dates, updir, upfile1, partner_sms_use
			) values (
				'$corporate_name', '$biz_num', '$com_num', '$service_type1', '$service_type2', '$ceo',
				'$zipcode', '$addr1', '$addr2',
				'$email', '$cell', '$siteurl',
				'$title', '$content',
				'$bank_name', '$bank', '$bank_account', '$partner_rate',
				'$stat', '$dates', '$datee', '$account_dates', '$updir', '$upfile1', '$partner_sms_use'
			)
		");
	}

	if($pdo->getError()) {
		msg('입점사 정보 저장  중 오류가 발생하였습니다.');
		exit;
	}

	$listURL = ($_SESSION['list_url']) ? $_SESSION['list_url'] : '?body=product@product_join_shop';
	msg('', $listURL, 'parent');

?>