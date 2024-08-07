<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 주문내역 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$ono = addslashes(trim($_REQUEST['ono']));
	$phone = addslashes(trim($_REQUEST['phone']));

	if(preg_match('/SS/', $ono)) {
		$_REQUEST['sbono'] = $ono;
		$_GET['ono'] = $ono;
		$_GET['phone'] = $phone;
	}
	$sbono = addslashes(trim($_REQUEST['sbono']));
	if($sbono) {
		include_once $engine_dir."/_engine/mypage/order_sbscr_detail.php";
		return;
	}

	if(!$ono && !$phone && $_SESSION['od_ono'] && $_SESSION['od_phone']) {
		$_POST['ono'] = $ono = $_SESSION['od_ono'];
		$_POST['phone'] = $phone = $_SESSION['od_phone'];
	}

	if(!$ono) msg(__lang_mypage_input_ono__, 'back');
	$ord = get_info($tbl['order'], 'ono', $ono);
	if(!$ord['no']) msg(__lang_mypage_error_onoNotExist__, 'back');

    if ($ord['stat'] == '1' && $ord['pay_type'] != '2') {
        $ord['stat2'] = '@1@';
    }

    if (empty($_SESSION['my_order']) == true || strcmp($_SESSION['my_order'], $ord['ono']) !== 0) {
        if($ord['member_no']) {
            if($ord['member_no'] != $member['no']) msg(__lang_mypage_error_notOwnOrd__, '/');
            if(!$rURL) $rURL=$root_url."/mypage/order_list.php";

        }
        else {
            checkBlank($phone, "전화번호를 입력해주세요.");
            if(numberOnly($phone) != numberOnly($ord['buyer_phone']) && numberOnly($phone) != numberOnly($ord['buyer_cell'])) msg(__lang_mypage_error_wrongPhoneNum__, 'back');
        }
    }

	if($ord['pay_type'] == '1' || $ord['pay_type'] == '5' || $ord['pay_type'] == '12') {
		$card = get_info($tbl['card'], "wm_ono", $ono);
		if($card['quota'] == "00" || empty($card['quota']) == true) $card['quota_str'] = __lang_mypage_info_paystyle1__;
		else $card['quota_str'] = sprintf(__lang_mypage_info_paystyle2__, $card['quota']);
	}
	elseif($ord['pay_type'] == 4) {
		$card = get_info($tbl['vbank'], "wm_ono", $ono);
		$ord['bank'] = trim($card['bankname'].' '.$card['account'].' '.$card['depositor']);
		$esc = 1;
	}


	function orderStatOnOff($stat) {
		global $ord, $cfg, $_skin;
		$res = ($stat == $ord['stat'] && $stat < 10) ? "on" : "off";
		$_src = ($cfg['design_version'] == "V3") ? $_skin['url']."/img/" : "/_image/";
		$res = "<img src=\"".$_src."mypage/order_stat_".$stat."_".$res.".gif\">";
		return $res;
	}

	$ord['prd_prc'] = parsePrice($ord['prd_prc']-$pdo->row("select sum(repay_prc) from {$tbl['order_product']} where ono='$ono'"), true);
	$ord['dlv_prc'] = parsePrice($ord['dlv_prc'], true);
	$ord['total_prc'] = parsePrice($ord['total_prc'], true);
	$ord['pay_prc'] = parsePrice($ord['pay_prc'], true);
	$ord['total_milage'] = parsePrice($ord['total_milage'], true);
	$ord['milage_prc_c'] = parsePrice($ord['milage_prc'], true);
	$ord['emoney_prc_c'] = parsePrice($ord['emoney_prc'], true);
	$ord['total_sale_prc'] = parsePrice($ord['sale2']+$ord['sale4']+$ord['sale5']+$ord['sale6']);
	$ord['sale1'] = parsePrice($ord['sale1'], true);
	$ord['sale2'] = parsePrice($ord['sale2'], true);
	$ord['sale3'] = parsePrice($ord['sale3'], true);
	$ord['sale4'] = parsePrice($ord['sale4'], true);
	$ord['sale5'] = parsePrice($ord['sale5'], true);
	$ord['sale6'] = parsePrice($ord['sale6'], true);
	$ord['stat_str'] = $_order_stat[$ord['stat']];
	$ord['date1'] = date("Y/m/d", $ord['date1']);
	$ord['date2'] = ($ord['date2']) ? date("Y/m/d", $ord['date2']) : __lang_mypage_info_paystyle3__;
	$ord['pay_type_str'] = (defined('__lang_order_info_paytype'.$ord['pay_type'].'__')) ? constant('__lang_order_info_paytype'.$ord['pay_type'].'__') : $_pay_type[$ord['pay_type']];

	if($ord['stat'] == 20) $ord['date2'] = __lang_mypage_info_paystyle4__;

	$dlv = getDlvUrl($ord);

	$counsel_list_include = 1;
	include $engine_dir."/_engine/mypage/counsel_list.php";

	$stat2 = explode('@', trim($ord['stat2'], '@'));
	$calcelable = (in_array('1', $stat2) == true || in_array('2', $stat2) == true || in_array('3', $stat2) == true) ? 'true' : '';
	$returnable = (in_array('4', $stat2) == true || in_array('5', $stat2) == true) ? 'true' : '';
	$directcancel = ($ord['stat'] == 1 && $cfg['order_cancel_type_1'] == 'Y' && $cfg['stat1_direct_cancel'] == 'N') ? 'true' : '';

	// PG모듈 로딩
	$stat2 = explode('@', trim($ord['stat2'], '@'));
	foreach($stat2 as $key => $val) if($val > 10) unset($stat2[$key]);
	$stat2 = array_unique($stat2);
	$mypage_pay_able = false;
	if($cfg['use_paytype_change'] == 'Y' && $cfg['change_pay_type'] && ($member['no'] > 0 || strcmp($_SESSION['my_order'], $ord['ono']) === 0) && $ord['stat'] == 1 && count($stat2) == 1 && $ord['pay_type'] != '3' && $ord['pay_type'] != '6' && $ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && $ord['talkstore'] != 'Y') {
		if($cfg['pg_version']) $pg_version = $cfg['pg_version'].'/';
		if($cfg['pg_mobile_version']) $pg_mobile_version = $cfg['pg_mobile_version'].'/';
		if($cfg['card_pg'] != 'dacom' && $cfg['card_pg'] != 'inicis') $pg_version = '';
		if(($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') || ($cfg['card_pg'] == 'inicis' && ($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') && $cfg['card_inicis_mobile_id'])) {
			include_once $engine_dir.'/_engine/card.'.$cfg['card_mobile_pg'].'/'.$pg_mobile_version.'card_frm.php';
		} else {
			include_once $engine_dir.'/_engine/card.'.$cfg['card_pg'].'/'.$pg_version.'card_frm.php';
		}
		include_once $engine_dir.'/_engine/order/pay_cancel.php';
		$mypage_pay_able = true;
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/order.js"></script>
<script type="text/javascript">
var cancelable = '<?=$calcelable?>';
var returnable = '<?=$returnable?>';
var directcancel = '<?=$directcancel?>';
var ono = '<?=$_GET['ono']?>';
var sbono = '';
var ord_delivery_type = '<?= ($ord['nations']?"O":"");?>';
var use_order_phone='<?=$cfg['use_order_phone']?>';
var nec_buyer_email='<?=$_use['nec_buyer_email']?>';
var nec_buyer_phone='<?=$_use['nec_buyer_phone']?>';
var nec_addressee_phone='<?=$_use['nec_addressee_phone']?>';
</script>
<form name="orderCustFrm" method="get" action="<?=$root_url?>/mypage/counsel_step1.php" style="margin:0px">
<input type="hidden" name="ono" value="<?=$ord['ono']?>">
<input type="hidden" name="stat" value="<?=$ord['stat']?>">
<input type="hidden" name="phone" value="<?=$phone?>">
<input type="hidden" name="cate1" value="">
<input type="hidden" name="cate2" value="">
</form>
<div id="order1"></div><div id="order2"></div>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>