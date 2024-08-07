<?PHP

	$no = numberOnly($_POST['no']);

	if($_POST['exec'] == 'toggle') {
		$ts = $pdo->assoc("select ts_use, ts_dates, ts_datee from {$tbl['product_timesale_set']} where no='$no'");

		$ts_use = ($ts['ts_use'] == 'Y') ? 'N' : 'Y';
		$ts_dates = strtotime($ts['ts_dates']);
		$ts_datee = strtotime($ts['ts_datee']);
		$ts_ing = ($ts_use == 'Y' && $ts_dates <= $now && $ts_datee >= $now) ? 'Y' : 'N';
		$pdo->query("update {$tbl['product']} set ts_ing='$ts_ing' where ts_set='$no' and ts_ing='{$ts['ts_use']}'");
		$r = $pdo->query("update {$tbl['product_timesale_set']} set ts_use='$ts_use' where no='$no'");

		header('Content-type:application/json;');
		exit(json_encode(array(
			'result' => ($r == true) ? 'success' : 'faild',
			'changed' => $ts_use
		)));
	}

	if($_POST['exec'] == 'getInfo') {
		$ts = $pdo->assoc("select * from {$tbl['product_timesale_set']} where no='$no'");
		$ts['desc'] = parsePrice($ts['ts_saleprc'], true)
			.($ts['ts_saletype'] == 'percent' ? '%' : $cfg['currency_type']).' '
			.($ts['ts_event_type'] == '1' ? '할인' : '적립');
		$ts['ts_dates'] = date('Y-m-d H:i', strtotime($ts['ts_dates']));
		$ts['ts_datee'] = ($ts['ts_datee'] > 0) ? date('Y-m-d H:i', strtotime($ts['ts_datee'])) : '무제한';
		$ts['date'] = $ts['ts_dates'].' ~ '.$ts['ts_datee'];
		$ts['state'] = $_prd_stat[$ts['ts_state']];

		header('Content-type:application/json;');
		exit(json_encode(array(
			'result' => ($ts['no']) ? 'success' : 'faild',
			'data' => $ts
		)));
	}

	if($_POST['exec'] == "delete") {
		$pdo->query("delete from {$tbl['product_timesale_set']} where no='$no'");
		$r = $pdo->query("update {$tbl['product']} set ts_use='N', ts_dates='', ts_datee='', ts_names='', ts_namee='', ts_saleprc='0', ts_saletype='0', ts_state='0', ts_ing='N', ts_set=0 where ts_set='$no'");

		header('Content-type:application/json;');
		exit(json_encode(array(
			'result' => ($r == true) ? 'success' : 'faild',
			'changed' => "Y"
		)));
	}

	$ts_use = ($_POST['ts_use'] == 'Y') ? 'Y' : 'N';
	$name = addslashes(trim($_POST['name']));
	$ts_dates_date = $_POST['ts_dates_date'];
	$ts_datee_date = $_POST['ts_datee_date'];
	$ts_dates = sprintf('%s %s:%s:00', $_POST['ts_dates'][0], $_POST['ts_dates'][1], $_POST['ts_dates'][2]);
	$ts_datee = sprintf('%s %s:%s:59', $_POST['ts_datee'][0], $_POST['ts_datee'][1], $_POST['ts_datee'][2]);
	$ts_event_type = ($_POST['ts_event_type'] == '1') ? '1' : '2';
	$ts_saleprc = numberOnly($_POST['ts_saleprc']);
	$ts_saletype = ($_POST['ts_saletype'] == 'percent') ? 'percent' : 'price';
	$ts_cut = numberOnly($_POST['ts_cut']);
	$ts_state = numberOnly($_POST['ts_state']);

	checkBlank($name, '타임세일 세트명을 입력해주세요.');
	checkBlank($_POST['ts_dates'][0], '시작일을 입력해주세요.');
    if (isset($_POST['ts_unlimited']) == false) {
    	checkBlank($_POST['ts_datee'][0], '종료일을 입력해주세요.');
    } else {
        $ts_datee = 0;
    }
	checkBlank($ts_saleprc, '할인금액(율)을 입력해주세요.');

	if($no > 0) {
		$pdo->query("
			update {$tbl['product_timesale_set']} set
				ts_use='$ts_use', name='$name', ts_dates='$ts_dates', ts_datee='$ts_datee',
				ts_event_type='$ts_event_type', ts_saleprc='$ts_saleprc', ts_saletype='$ts_saletype', ts_cut='$ts_cut', ts_state='$ts_state'
			where no='$no'
		");

		$ts_dates = strtotime($ts_dates);
		$ts_datee = strtotime($ts_datee);
		$ts_ing = ($ts_use == 'Y' && $ts_dates <= $now && $ts_datee >= $now) ? 'Y' : 'N';
		$pdo->query("update {$tbl['product']} set ts_ing='$ts_ing', ts_dates='$ts_dates', ts_datee='$ts_datee', ts_state='$ts_state', ts_saleprc='$ts_saleprc' where ts_set='$no'");
	} else {
		$pdo->query("
			insert into {$tbl['product_timesale_set']}
				(ts_use, name, ts_dates, ts_datee, ts_event_type, ts_saleprc, ts_saletype, ts_cut, ts_state, reg_date)
				values
				('$ts_use', '$name', '$ts_dates', '$ts_datee', '$ts_event_type', '$ts_saleprc', '$ts_saletype', '$ts_cut', '$ts_state', '$now')
		");
	}

	msg('', getListURL('timesale_regist'), 'parent');

?>