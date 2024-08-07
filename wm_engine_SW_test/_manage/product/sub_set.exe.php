<?PHP

	checkBasic();

	if($_POST['exec'] == 'remove') {
		$no = numberOnly($_POST['no']);

		$pdo->query("delete from {$tbl['sbscr_set_product']} where setno='$no'");
		$pdo->query("delete from {$tbl['sbscr_set']} where no='$no'");

		exit('OK');
	}

	$sbscr_set_no = numberOnly($_POST['sbscr_set_no']);
	$popup = ($_POST['popup']=='Y') ? 'Y':'N';

	$name = addslashes(del_html($_POST['name']));
	$set_default = ($_POST['set_default'] == 'Y') ? 'Y' : 'N';
	$dlv_period = $_POST['sbscr_dlv_period'];
	$dlv_week = $_POST['sbscr_dlv_week'];
	$dlv_type = ($_POST['sbscr_dlv_type'] == 'Y') ? 'Y' : 'N';
	$dlv_ea = $_POST['sbscr_dlv_ea'];
	$dlv_end = numberOnly($_POST['sbscr_dlv_end']);
	$sale_use = ($_POST['sbscr_sale_use'] == 'Y') ? 'Y' : 'N';
	$sale_ea = numberOnly($_POST['sale_ea']);
	$sale_percent = numberOnly($_POST['sale_percent']);

	if($popup=='Y') checkBlank($name,"세트명을 입력해주세요.");
	checkBlank($dlv_period,"배송주기를 선택해주세요.");
	checkBlank($dlv_week,"배송요일을 선택해주세요.");

	if($sale_use=='Y' && (!$sale_ea||!$sale_percent)) msg("할인 사용시 회차 or %를 입력해주세요");

	if(is_array($dlv_period)) $dlv_period = implode("|", $dlv_period);
	if(is_array($dlv_week)) $dlv_week = implode("|", $dlv_week);
	if($dlv_type=='Y') {
		checkBlank($dlv_end,"특정기간을 선택해주세요.");
	}

	if($popup!='Y') {
        unset($popup);
		$_POST['sbscr_dlv_period'] = $dlv_period;
		$_POST['sbscr_dlv_week'] = $dlv_week;
		$_POST['sbscr_dlv_type'] = $dlv_type;
		$_POST['sbscr_dlv_ea'] = $dlv_ea;
		$_POST['sbscr_sale_use'] = $sale_use;
		$_POST['sbscr_sale_ea'] = $sale_ea;
		$_POST['sbscr_sale_percent'] = $sale_percent;
		$cfg_msg="정기배송 설정이 완료되었습니다.";
		include $engine_dir.'/_manage/config/config.exe.php';
		exit;
	}

	if($set_default=='Y') $pdo->query("update $tbl[sbscr_set] from `default`='N'");
	if($sbscr_set_no) {
		$sql="update `".$tbl['sbscr_set']."` set `name`='$name', `dlv_period`='$dlv_period', `dlv_week`='$dlv_week', `dlv_type`='$dlv_type', `dlv_ea`='$dlv_ea', `dlv_end`='$dlv_end', `sale_use`='$sale_use', `sale_ea`='$sale_ea', `sale_percent`='$sale_percent', `default`='$set_default' where `no`='$sbscr_set_no'";
		$pdo->query($sql);

		$msg = '세트가 수정되었습니다.';
	}
	else {
		$sql="INSERT INTO `".$tbl['sbscr_set']."` (`name`, `dlv_period`, `dlv_week`, `dlv_type`, `dlv_ea`, `dlv_end`, `sale_use`, `sale_ea`, `sale_percent`, `default`, `admin_id`, `reg_date`) VALUES ('$name', '$dlv_period', '$dlv_week', '$dlv_type', '$dlv_ea', '$dlv_end', '$sale_use', '$sale_ea', '$sale_percent', '$set_default', '$admin[admin_id]', '$now')";
		$pdo->query($sql);

		$msg = '세트가 등록되었습니다';
	}

	msg($msg , 'popup');

?>