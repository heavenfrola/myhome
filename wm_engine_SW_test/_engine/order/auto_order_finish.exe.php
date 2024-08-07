<?PHP

	define('__CRON_SCRIPT__', true);

	set_time_limit(0);
	ini_set('memory_limit', -1);
	$no_qcheck = true;

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/milage.lib.php';
	include_once $engine_dir.'/_engine/include/shop.lib.php';
	include_once $engine_dir.'/_manage/manage.lib.php';

	$wec_acc = new weagleEyeClient($_we, 'mall');
	$result = $wec_acc->call('getAutoDlvFinish');

	$day = $result[0]->day[0];
	$last_date = $result[0]->last_date[0];

	if($day < 1) exit;
	if(date('Ymd', $last_date) == date('Ymd', $now)) exit;

	$checktime = strtotime(date('Y-m-d 23:59:59', strtotime('-'.$day.'days', $now)));
	$ext = 5;
	$ocnt = 0;
	$auto_finish = 'Y';
	$res = $pdo->iterator("select * from $tbl[order] where stat=4 and (stat2='' || stat2 like '%@4@%') and date4 <= $checktime and checkout!='Y' and stat2 not like '%@18@%' and stat2 not like '%@16@%' and stat2 not like '%@14@%' and stat2 not like '%@12@%'");
    foreach ($res as $data) {
        if ($data['smartstore'] == 'Y') continue;
        if ($data['talkstore'] == 'Y') continue;
        if ($data['external_order'] == 'talkpay') continue;

		$ono = $data['ono'];
		include $engine_dir.'/_manage/order/order_stat.php';
		$ocnt++;
	}

	exit("$ocnt");

?>