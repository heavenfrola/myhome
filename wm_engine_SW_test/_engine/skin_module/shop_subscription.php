<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 정기배송 레이어
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
    include_once $engine_dir.'/_plugin/subScription/set.common.php';

	printAjaxHeader();

	// 상품
	$hash = addslashes($_POST['hash']);
	$spdata = checkPrd($hash);

	$img = prdImg(3, $spdata, 100, 100);
	$_replace_code[$_file_name]['detail_sbscr_prd_img'] = "<img id='mainImg' src='$img[0]' $img[1]>";
	$_replace_code[$_file_name]['detail_sbscr_prd_name'] = $spdata['name'];

	// 세트설정
	$sbscr_data = getsbscrCfg($spdata['no']);

	$spdata['buy_ea'] = $_POST['sbscr_buy_ea'];

	if($sbscr_data['sale_use']=='Y') {
		$spdata['sale_use'] = $sbscr_data['sale_use'];
		$spdata['sale_ea'] = $sbscr_data['sale_ea'];
		$spdata['sale_percent'] = $sbscr_data['sale_percent'];
	}

	$spdata['tmp_sell_prc'] = 0;
	if($_POST['sbscr_option_val']) {
		$_option_val = explode("|", $_POST['sbscr_option_val']);
		foreach($_option_val as $key=>$val) {
			$_val = explode("::", $val);
			$spdata['tmp_sell_prc'] += ($spdata['sell_prc']+$_val[1]);
		}
	}else {
		$spdata['tmp_sell_prc'] = $spdata['sell_prc'] * $spdata['buy_ea'];
	}

	$spdata['sell_prc'] = $spdata['tmp_sell_prc'];
	$spdata['free_dlv'] = $spdata['free_delivery'];

	$prdCart = new OrderCart();
	$prdCart->addCart($spdata);
	$prdCart->complete();
	$prdCart->pay_prc -= $prdCart->dlv_prc;
	$sbscr_data['dlv_prc'] = $prdCart->dlv_prc;
	$sbscr_data['sale8'] = $prdCart->getData('sale8', true);
	$sbscr_data['total_sell_prc'] = $prdCart->getData('sum_sell_prc');

	$_replace_code[$_file_name]['detail_sbscr_buy_ea'] = $sbscr_data['sbscr_buy_ea'] = $_POST['sbscr_buy_ea'];
	$sbscr_data['sbscr_option_val'] = $_POST['sbscr_option_val'];

	$_replace_code[$_file_name]['detail_sbscr_end_date_yn'] = $sbscr_data['sbscr_end_yn'];

	$detail_delivery_date = date("Y-m-d", strtotime("+".$cfg['sbscr_first_date']." DAYS", $now));
	$sbscr_data['detail_sbscr_start_date'] = strtotime($detail_delivery_date);
	$_replace_code[$_file_name]['detail_sbscr_first_start_date'] = $detail_delivery_date;

	if($sbscr_data['sbscr_dlv_end']) {
		$_detail_sbscr_end_date2 = date("Y-m-d", strtotime("+".$sbscr_data['sbscr_dlv_end']." MONTH", $now));
		$_detail_sbscr_end_date = date("Y-m-d", strtotime("+1 MONTH", $now));
		$sbscr_data['detail_sbscr_end_date'] = strtotime($_detail_sbscr_end_date);
		$_replace_code[$_file_name]['detail_sbscr_default_end'] = $_detail_sbscr_end_date2;
	}else {
		$sbscr_data['detail_sbscr_end_date'] = 0;
	}

	// 금액계산
	$caldata = getsbscrCal($sbscr_data);

	$_replace_code[$_file_name]['detail_sbscr_start_date'] = date('Y-m-d', $caldata['start_date']);
	$start_dlv_date = strtotime($_replace_code[$_file_name]['detail_sbscr_start_date']);

	if($sbscr_data['sbscr_dlv_end']) {
		$_replace_code[$_file_name]['detail_sbscr_end_date'] = date('Y-m-d', $caldata['end_date']);
		$end_dlv_date = strtotime($_replace_code[$_file_name]['detail_sbscr_end_date']);
	}
	$yoil = array("일","월","화","수","목","금","토");
	$_replace_code[$_file_name]['detail_sbscr_start_yoil'] = $yoil[date('w', $start_dlv_date)];
	$_replace_code[$_file_name]['detail_sbscr_end_yoil'] = $yoil[date('w', $end_dlv_date)];

	$_replace_code[$_file_name]['detail_sbscr_option_text'] = $caldata['option_text'];
	$_replace_code[$_file_name]['detail_sbscr_sell_prc'] = parsePrice($caldata['total_sell_prc'], true);
	$_replace_code[$_file_name]['detail_sbscr_dlv_prc'] = parsePrice($caldata['total_dlv_prc'], true);
	$_replace_code[$_file_name]['detail_sbscr_ea_prc'] = parsePrice($caldata['total_ea_pay_prc'], true);
	$_replace_code[$_file_name]['detail_sbscr_pay_prc'] = parsePrice($caldata['total_pay_prc'], true);
	$_replace_code[$_file_name]['detail_sbscr_dlv_cnt'] = ($sbscr_data['sbscr_end_yn'] == 'Y') ? number_format($caldata['total_dlv_cnt']) : 0;
	$_replace_code[$_file_name]['detail_sbscr_dlv_list'] = implode("|", $caldata['date_list']);

	for ($i = 1; $i <= 5; $i++) {
		$_replace_code[$_file_name]['detail_sbscr_period_'.$i] = (in_array($i, $sbscr_data['sbscr_dlv_period'])) ? "" : "disabled";
		$_replace_code[$_file_name]['detail_sbscr_min_period_'.$i] = ($i==$sbscr_data['sbscr_min_period']) ? "checked" : "";
	}

	$yoil_num = array();
	for ($i = 1; $i <= 7; $i++) {
		if(in_array($i, $sbscr_data['sbscr_dlv_week'])) {
			if($i==7) $yoil_num[] = 0;
			else $yoil_num[] = $i;
		}
		$_replace_code[$_file_name]['detail_sbscr_date_yoil_'.$i] = (in_array($i, $sbscr_data['sbscr_dlv_week'])) ? "" : "disabled";
		$_replace_code[$_file_name]['detail_sbscr_date_yoil_class_'.$i] = (in_array($i, $sbscr_data['sbscr_dlv_week'])) ? "active" : "";
		$_replace_code[$_file_name]['detail_sbscr_min_date_yoil_'.$i] = ($i==$sbscr_data['sbscr_min_dlv_week']) ? "checked" : "";
	}
	$_replace_code[$_file_name]['detail_sbscr_yoil_push'] = $sbscr_data['sbscr_min_dlv_week'];

	$_replace_code[$_file_name]['detail_sbscr_form_start'] = "<form name=\"sbscrFrm\" method=\"post\" style=\"margin:0px\" accept-charset=\""._BASE_CHARSET_."\">
<input type=\"hidden\" name=\"sbscr_pno\" value=\"".$hash."\">
<input type=\"hidden\" name=\"sbscr_option\" value=\"".$sbscr_data['sbscr_option_val']."\">
<input type=\"hidden\" name=\"sbscr_ea\" value=\"".$sbscr_data['sbscr_buy_ea']."\">
<input type=\"hidden\" name=\"sbscr_sell_prc\" value=\"".$sbscr_data['sell_prc']."\">
<span class=\"sbscr_dlv_cnt\" style=\"display:none;\"></span>
";

	$_replace_code[$_file_name]['detail_sbscr_form_end'] = "
	<input type='hidden' name='sbscr_sale_yn' value='".$spdata['sale_use']."'>
	<input type='hidden' name='sbscr_sale_ea' value='".$spdata['sale_ea']."'>
	<input type='hidden' name='sbscr_sale_percent' value='".$spdata['sale_percent']."'>
	</form>";

?>
