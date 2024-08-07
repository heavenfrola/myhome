<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문완료 페이지
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	loadPlugIn('order_finish_start');

	if($_POST['sno']) {
		$sbscr = 'Y';

		$ord = $pdo->assoc("select * from $tbl[sbscr] where sbono='$_POST[sno]'");

		$ord['ono'] = $_POST['sno'];
		$ord['pay_prc'] = parsePrice($ord['s_pay_prc']);
		$ord['prd_prc'] = parsePrice($ord['s_prd_prc']);
		$ord['total_prc'] = parsePrice($ord['s_total_prc']);
		$ord['dlv_prc'] = parsePrice($ord['s_dlv_prc']);
	}else {
		// 기본 체크
		if($_GET['ono']) $_SESSION['last_order'] = addslashes($_GET['ono']);
		checkBlank($_SESSION['last_order'], __lang_mypage_input_ono__);
		$ord=get_info($tbl['order'],'ono',$_SESSION['last_order']);
	}

	if($_GET['LGD_OID']) {
		$_SESSION['last_order'] = $_GET['LGD_OID'];
	}

	$ord['pay_prc'] = parsePrice($ord['pay_prc']);
	$ord['prd_prc'] = parsePrice($ord['prd_prc']);
	$ord['total_prc'] = parsePrice($ord['total_prc']);
	$ord['addressee_name'] = stripslashes($ord['addressee_name']);
	$ord['addressee_addr1'] = stripslashes($ord['addressee_addr1']);
	$ord['addressee_addr2'] = stripslashes($ord['addressee_addr2']);
	$ord['addressee_addr3'] = stripslashes($ord['addressee_addr3']);
	$ord['addressee_addr4'] = stripslashes($ord['addressee_addr4']);

	if($ord['stat'] == 31) {
		$card_tbl = $ord['pay_type'] == 4 ? $tbl['vbank'] : $tbl['card'];
		$res_msg = addslashes($pdo->row("select res_msg from $card_tbl where wm_ono='$ord[ono]'"));
		msg(__lang_order_error_cardFailed__."\\n".$res_msg, $root_url);
	}

	// 주문 존재 여부
	if(!$ord['no']) msg(__lang_mypage_error_orderNotExist__, $root_url);

	// 회원의 주문 정보
    if (isset($_SESSION['my_order']) == false || strcmp($_SESSION['my_order'], $ord['ono']) !== 0) {
        if ($ord['member_no'] && !$member['no']) {
            //주문정보내 회원번호가 존재하지만 현재 로그인된 회원정보가 없는 경우 (로그인 세션이 소실된 경우)
            $member = $pdo->assoc("SELECT * FROM ".$tbl['member']." WHERE `no` = ? ", array(
                $ord['member_no']
            )); //회원정보를 재생성
            if ($ord['member_no'] != $member['no']) {
                //일치하지 않는다면 에러발생
                msg(__lang_mypage_error_notOwnOrd__, $root_url);
            } else {
                //일치한다면 세션정보에도 추가
                $_SESSION['member_no'] = $member['no'];
            }
        }
    }
	if($ord['pay_type'] == 4) $ord['bank'] = $pdo->row("select concat(`bankname`,' ',`account`) from `$tbl[vbank]` where `wm_ono` = '$ord[ono]'");

	$total_gift_res = 0;
	if($cfg['order_gift_timing'] == '') {
		include 'order_gift.inc.php';
	}

	$total_buy_ea = $pdo->row("select sum(buy_ea) from `$tbl[order_product]` where `ono`='$ord[ono]'");

	common_header();

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/order.js"></script>
<?php if ($ord['sbono']) { ?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<?php } ?>
<script type="text/javascript" >
	var total_gift=<?=$total_gift_res?>;
	var gift_multi='<?=$cfg['order_gift_multi']?>';
    var ono = '<?= $ord['ono'];?>';
    var sbono = '<?=$ord['sbono']?>';
    var ord_delivery_type = '<?= ($ord['nations']?"O":"");?>';
    var use_order_phone='<?=$cfg['use_order_phone']?>';
    var nec_buyer_phone='<?=$_use['nec_buyer_phone']?>';
</script>

<?php if ($_COOKIE['nv_pchs'] && $cfg['roi_use'] == 'Y') { ?>
<!-- 네이버 지식쇼핑 ROI Tracker -->
<div id="nv_price" style="display:none" value="<?=$total_buy_ea?>,<?=numberOnly($ord['prd_prc'])?>"></div>
<script type='text/javascript' src="http://shopping.naver.com/CPC/purchase_analysis.js"></script>
<?php } ?>

<?php
	if($nvcpa) {
		$cpa_order = '';
		$res = $pdo->iterator("select * from $tbl[order_product] where ono='$ord[ono]'");
        foreach ($res as $data) {
			if($cpa_order) $cpa_order .= ",";
			$data['name'] = addslashes($data['name']);
			$data['hash'] = strtoupper(md5($data['pno']));
			$cpa_order .= "{\"oid\":\"$data[ono]\", \"poid\":\"$data[no]\", \"pid\":\"$data[hash]\", \"parpid\":null, \"name\":\"$data[name]\", \"cnt\":\"$data[buy_ea]\", \"price\":\"$data[total_prc]\"}";
		}
?>
	<!-- 네이버 CPA 스크립트 -->
	<script type='text/javascript'>
	var cpa = {};
	cpa['chn'] = 'AD';
	cpa['order'] = [<?=$cpa_order?>];

	if(wcs.isCPA) {
		wcs.CPAOrder(cpa)
	}
	</script>
<?php } ?>

<?php

// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
include_once $engine_dir."/_engine/common/skin_index.php";

?>