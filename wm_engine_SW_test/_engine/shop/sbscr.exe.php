<?php

	/* +----------------------------------------------------------------------------------------------+
	' | 정기배송 계산
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

	printAjaxHeader();

	$result = array();

    // 휴일 정보
    $_holidays = array();
    $todaystamp = strtotime(date('Y-m-d 00:00:00'));
    $res = $pdo->iterator("select timestamp from {$tbl['sbscr_holiday']} where timestamp>'$todaystamp' and is_holiday='Y'");
    foreach ($res as $_tmp) {
        $_holidays[] = $_tmp['timestamp'];
    }

    // 상품 정보
	$data['pno'] = $_POST['pno'];
	$data = $pdo->assoc("select * from $tbl[product] where hash=?", array($data['pno']));
	$data = shortCut($data);
    $config = getsbscrCfg($data['parent']);
	$data['sbscr_min_period'] = $_POST['sbscr_period'];
	$data['sbscr_dlv_week'] = $config['sbscr_dlv_week'];
	$data['detail_sbscr_start_date'] = strtotime($_POST['start_date']);
	$data['detail_sbscr_end_date'] = ($_POST['end_date']) ?  strtotime($_POST['end_date']) : 0;
	$data['sbscr_buy_ea'] = $data['buy_ea'] = (int) $_POST['buy_ea'];
	//$data['sell_prc'] = $_POST['sell_prc'];
	$data['sbscr_option_val'] = $_POST['option_val'];
	$data['sale_use'] = $_POST['sale_use'];
	$data['sale_percent'] = $_POST['sale_percent'];
	$data['week_all'] = 'Y';
	$spdata['tmp_sell_prc'] = 0;
	if($data['sbscr_option_val']) {
		$_option_val = explode("|", $data['sbscr_option_val']);
		foreach($_option_val as $key=>$val) {
			$_val = explode("::", $val);
			$spdata['tmp_sell_prc'] += ($data['sell_prc']+$_val[1]);
		}
        $data['sell_prc'] = $spdata['tmp_sell_prc'];
	}

	$data['free_dlv'] = $data['free_delivery'];

    $caldata = getsbscrCal($data);
    $data['date_list'] = implode('|', $caldata['date_list']);
    $data['dlv_cnt'] = $caldata['total_dlv_cnt'];
    $data['sale_use'] = $config['sale_use'];
    $data['sale_ea'] = $config['sale_ea'];
    $data['sale_percent'] = $config['sale_percent'];

    if ($data['dlv_cnt'] > 0) {
        $prdCart = new OrderCart();
        $prdCart->addCart($data);
        $prdCart->complete();
        $data['dlv_prc'] = $prdCart->dlv_prc;
        $data['total_sell_prc'] = ($prdCart->getData('pay_prc')-$prdCart->getData('dlv_prc'))/$data['dlv_cnt'];
        $data['dlv_prc'] = $prdCart->getData('dlv_prc')/$data['dlv_cnt'];
    }

    // ISO-8601 형식의 요일 코드를 일요일이 0인 요일 코드로 변환
    $week_js = $data['sbscr_dlv_week'];
    $sunday = array_search('7', $week_js);
    if ($sunday !== false) {
        $week_js[$sunday] = '0';
    }

    $caldata = getsbscrCal($data);
	$result['detail_sbscr_option_text'] = $caldata['option_text'];
	$result['detail_sbscr_sell_prc'] = parsePrice($caldata['total_sell_prc'], true);
	$result['detail_sbscr_dlv_prc'] = parsePrice($caldata['total_dlv_prc'], true);
	$result['detail_sbscr_ea_prc'] = parsePrice($caldata['total_ea_pay_prc'], true);
	$result['detail_sbscr_pay_prc'] = parsePrice($caldata['total_pay_prc'], true);
	$result['detail_sbscr_dlv_cnt'] = parsePrice($caldata['total_dlv_cnt'], true);
	$result['date_list'] = @implode("|", $caldata['date_list']);
	$result['detail_start_date'] = date("Y-m-d", $caldata['start_date']);
	$result['detail_end_date'] = ($caldata['end_date']) ? date("Y-m-d", $caldata['end_date']) : 0;
	$result['detail_sbscr_start_yoil'] = @constant('__lang_common_week_'.strtolower(date('D', $caldata['start_date'])).'__');
	$result['detail_sbscr_end_yoil'] = @constant('__lang_common_week_'.strtolower(date('D', $caldata['end_date'])).'__');
    $result['detail_interval_str'] = $_sbscr_periods[$_POST['sbscr_period']];
    $result['detail_date_str'] = $result['detail_start_date'].' ~ ';
    if ($result['detail_end_date'] != 0) {
        $result['detail_date_str'] .= date('Y-m-d', end($caldata['date_list']));
    }
    $result['weekSelectable'] = $week_js;
    $result['holidays'] = $_holidays;
    $result['sbscr_dlv_end'] = date('Y-m-d', strtotime('+'.$config['sbscr_dlv_end'].'months', $caldata['start_date']-1));

	$json = json_encode($result);
	exit($json);

?>