<?php

	/*
	 * 정기배송 주문처리 페이지
	 */

	set_time_limit(0);
	ini_set('memory_limit', -1);

	chdir(dirname(__FILE__));

	$urlfix = 'Y';
	//$no_qcheck = true;

	include $root_dir.'/_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

	$exec = $_POST['exec'];

	if($exec=='json') printAjaxHeader();

	$order_make_day = $cfg['sbscr_order_create'];

	$make_sdate = mktime(0,0,0,date("n"),date("j"),date("Y"));
	$make_edate = $now + ($order_make_day*86400);

	for($j=$make_sdate;$j<$make_edate;$j+=86400) {
		if(in_array(date('w',$j),array(0,6))) $make_edate += 86400;
		if($pdo->row("select count(*) from $tbl[sbscr_holiday] where timestamp='$make_edate' and is_holiday='Y'") > 0) {
			if($cfg['sbscr_holiday_after']=='Y') {
				$make_edate = strtotime('+'.$cfg['sbscr_holiday_create'].' days', $make_edate);
				continue;
			} else {
				$make_edate = strtotime('-'.$cfg['sbscr_holiday_create'].' days', $make_edate);
				continue;
			}
		}
	}
	$make_sdate = date('Y-m-d', $make_sdate);
	$make_edate = date('Y-m-d', $make_edate);

	// 스케줄 처리
	include_once $engine_dir.'/_engine/cron/auto_sbscr_schedule.exe.php';

	// 결제처리
	include_once $engine_dir.'/_engine/cron/auto_sbscr_pay.exe.php';

	addField($tbl['order_product'], 'sale8', 'double(8,2) not null default 0.00 after sale7');
	addField($tbl['order'], 'sale8', 'double(8,2) not null default 0.00 after sale7');

	autoSbscrCreate();

	if($exec=='json') {
		exit("주문서 생성이 완료되었습니다.");
	}

	echo "OK";
	exit;

?>