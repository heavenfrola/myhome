<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 정기주문내역 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

	$phone = addslashes(trim($_REQUEST['phone']));

	if(!$sbono) msg(__lang_mypage_input_ono__, 'back');
	$ord = get_info($tbl['sbscr'], 'sbono', $sbono);
	if(!$ord['no']) msg(__lang_mypage_error_onoNotExist__, 'back');

	if($ord['member_no']) {
		if($ord['member_no'] != $member['no']) msg(__lang_mypage_error_notOwnOrd__, '/');
		if(!$rURL) $rURL=$root_url."/mypage/order_list.php";
	}else {
		checkBlank($phone, "전화번호를");
		if(numberOnly($phone) != numberOnly($ord['buyer_phone']) && numberOnly($phone) != numberOnly($ord['buyer_cell'])) msg(__lang_mypage_error_wrongPhoneNum__, 'back');
	}

	if($ord['pay_type'] == 1) {
		$card = get_info($tbl['card'], "wm_ono", $ord['sbono']);
		if($card['quota'] == "00") $card['quota_str'] = __lang_mypage_info_paystyle1__;
		else $card['quota_str'] = sprintf(__lang_mypage_info_paystyle2__, $card['quota']);
	}
	elseif($ord['pay_type'] == 4) {
		$card = get_info($tbl['vbank'], "wm_ono", $ord['sbono']);
		$ord['bank'] = "$card[bankname] $card[account] $card[depositor]";
		$ord['pay_type'] = 2;
		$esc = 1;
	}

	function sbscrStatOnOff($stat) {
		global $ord, $cfg, $_skin;
		$res = ($stat == $ord['stat'] && $stat < 10) ? "on" : "off";
		$_src = ($cfg['design_version'] == "V3") ? $_skin['url']."/img/" : "/_image/";
		$res = "<img src=\"".$_src."mypage/order_stat_".$stat."_".$res.".gif\">";
		return $res;
	}

	$ord['prd_prc'] = parsePrice($ord['s_prd_prc'], true);
	$ord['dlv_prc'] = parsePrice($ord['s_dlv_prc'], true);
	$ord['total_prc'] = parsePrice($ord['s_total_prc'], true);
	$ord['pay_prc'] = parsePrice($ord['s_pay_prc'], true);
	$ord['total_milage'] = parsePrice($ord['s_total_milage'], true);
	$ord['total_sale_prc'] = parsePrice($ord['s_sale_prc']);
	$ord['stat_str'] = $_order_stat[$ord['stat']];
	$ord['date1'] = date("Y/m/d", $ord['date1']);
	$ord['date2'] = ($ord['date2']) ? date("Y/m/d", $ord['date2']) : __lang_mypage_info_paystyle3__;
	$ord['pay_type_str'] = (defined('__lang_order_info_paytype'.$ord['pay_type'].'__')) ? constant('__lang_order_info_paytype'.$ord['pay_type'].'__') : $_pay_type[$ord['pay_type']];

	if($ord['stat'] == 20) $ord['date2'] = __lang_mypage_info_paystyle4__;

    $_SESSION['my_order'] = $sbono;

	common_header();

	// 정기배송
	if ($ord['pay_type'] == '23') {
		if(isset($cfg['autobill_pg']) == false || empty($cfg['autobill_pg']) == true) {
			$cfg['autobill_pg'] = 'dacom';
		}
		switch($cfg['autobill_pg']) {
			case 'dacom' : $pg_version = 'XpayAutoBilling/'; break;
			case 'nicepay' : $pg_version = 'autobill/'; break;
		}
		include_once $engine_dir."/_engine/card.{$cfg['autobill_pg']}/{$pg_version}card_frm.php";
	} elseif ($ord['pay_type'] == '27') {
    	include_once __ENGINE_DIR__.'/_engine/card.naverSimplePay/card_frm.php';
    }

	$counsel_list_include = 1;
	include $engine_dir."/_engine/mypage/counsel_list.php";
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/order.js"></script>
<script type="text/javascript">
var ono = '';
var sbono = '<?=$_GET['sbono']?>';
var ord_delivery_type = '<?= ($ord['nations']?"O":"");?>';
</script>
<form name="orderCustFrm" method="get" action="<?=$root_url?>/mypage/counsel_step1.php" style="margin:0px">
<input type="hidden" name="ono" value="<?=$ord['sbono']?>">
<input type="hidden" name="stat" value="<?=$ord['stat']?>">
<input type="hidden" name="phone" value="<?=$phone?>">
<input type="hidden" name="cate1" value="">
<input type="hidden" name="cate2" value="">
<input type="hidden" name="sbscr" value="Y">
</form>
<?
	$_tmp_file_name = '/mypage/order_sbscr_detail.php';

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>