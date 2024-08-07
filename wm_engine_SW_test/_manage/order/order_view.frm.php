<?PHP

	use Wing\API\Kakao\KakaoTalkPay;

    javac("printLoading()");
    ob_end_flush();

	if(!fieldExist($tbl['order'], 'parent')) {
		addField($tbl['order'], "parent", "VARCHAR(16) NOT NULL AFTER `ono`");
		$pdo->query("alter table `wm_order` add index ( `parent` )");
		addField($tbl['order_stat_log'], "spt", "VARCHAR( 15 ) NOT NULL AFTER `ono`");
	}

	$ono = addslashes(trim($_GET['ono']));
	checkBlank($ono, '주문번호를 입력해주세요.');
	$data = get_info($tbl['order'], 'ono', $ono);
	if(!$data['no']) {
        $data = $pdo->row("select count(*) from {$tbl['sbscr']} where sbono=?", array($ono));
        if ($data) {
            msg('', '?body=order@sbscr_view.frm&sbono='.$ono);
        }
		msg('주문이 삭제되었거나 존재하지 않습니다.', 'close');
	}
	if($data['cart_where'] == 'Z') {
		include $engine_dir.'/_manage/order/ext_view.php';
		return;
	}

	include_once $engine_dir.'/_engine/include/shop.lib.php';

	$add_info_file = $root_dir.'/_config/order.php';
	if(is_file($add_info_file)) {
		include_once $add_info_file;
	}

	$pay_type = $_pay_type[$data['pay_type']];

	if($data['pay_type'] == 2 || $data['pay_type'] == 4) {
		if($data['pay_type'] == 4) {
			$card = get_info($tbl['vbank'], 'wm_ono', $ono);
			$pay_type .= " (거래 번호 : $card[tno])";
			$data['bank'] = "$card[bankname] $card[account] $card[depositor]";
		}
		$pay_type .= " ($data[bank])";
		if($data['bank_name']) {
			$pay_type .= " <u>입금자명 : ".stripslashes($data['bank_name'])."</u>";
		}
	} else {
		$card = get_info($tbl['card'], 'wm_ono', $ono);
		if($card['pg_charge'] != '') {
			list($data['pg_charge'], $data['pg_charge_fee']) = explode(':', $card['pg_charge']);
		}
	}

	if($data['pay_type'] == 6) {
		$pay_type .= ' ( <b>'.parsePrice($data['emoney_prc'], true).' '.$cfg['currency_type'].'</b> )';
	}
	elseif($data['pay_type'] == 3) {
		$pay_type .= ' ( <b>'.parsePrice($data['milage_prc'], true).' '.$cfg['currency_type'].'</b> )';
	}
	else {
		$pay_type .= ' ( <b>'.parsePrice($data['pay_prc'], true).' '.$cfg['currency_type'].'</b> )';
	}

	if($data['checkout'] == 'Y' || $data['smartstore'] == 'Y') {
		if($data['point_use'] > 0) $pay_type.=" + <span style=\"color:#d75f06\">NPAY포인트 ( <b>".parsePrice($data['point_use'], true)." $cfg[currency_type]</b> )</span>";
	} else {
		if($data['milage_prc'] && $data['pay_type']!=3) $pay_type.=" + <span style=\"color:#d75f06\">적립금 ( <b>".parsePrice($data['milage_prc'], true)." $cfg[currency_type]</b> )</span>";
		if($data['emoney_prc'] && $data['pay_type']!=6) $pay_type.=" + <span style=\"color:#ff00ff\">예치금 ( <b>".parsePrice($data['emoney_prc'], true)." $cfg[currency_type]</b> )</span>";
	}
	if(!$data['member_id']) $data['member_id']="비회원";

	$mil = $pdo->assoc("select sum(total_milage) as mp, sum(member_milage) as mmp from $tbl[order_product] where ono='$ono' and stat<10");
	$data['total_milage'] = $mil['mp'];
	$data['member_milage'] = $mil['mmp'];

	if(!$data['member_no']) {
		$milage_down="비회원";
	}
	elseif($data['total_milage']<=0) {
		$milage_down="적립금 없음";
	}
	elseif($data['milage_down']=="Y"){
		if($data['milage_down_date']) {
			$milage_down="지급일시 : ".date("y/m/d g:i A",$data['milage_down_date']);
		}
		else {
			$milage_down="지급";
		}
	}
	else {
		$milage_down="미지급";
	}

	if($data['sms'] != 'Y') $data['sms'] = 'N';

    $is_free_dlv_cpn = false;
	if($data['sale5'] > 0 || $data['sale7'] > 0) {
		$mcouponRes = $pdo->iterator("select * from $tbl[coupon_download] where ono='$ono'");
		$cpn_cnt = $mcouponRes->rowCount();

        $cpn = $pdo->assoc("select stype from {$tbl['coupon_download']} where ono='$ono' and stype!='5'");
        if ($cpn['stype'] == '3') $is_free_dlv_cpn = true;
	}

	if($cfg['repay_part'] == "Y"){
		$_repay_part_stat=array("0" => "일반", 12=>'취소요청', 13 => "취소", 14=>'환불요청', 15 => "환불", 16=>'반품요청', 17 => "반품", 18=>'교환요청', 19=> "교환");
		if($data['checkout'] == 'Y' || @$data['smartstore'] == 'Y') {
			unset($_repay_part_stat[12], $_repay_part_stat[14], $_repay_part_stat[15], $_repay_part_stat[16], $_repay_part_stat[18], $_repay_part_stat[19]);
		} elseif ($data['talkstore'] == 'Y') {
			unset($_repay_part_stat[12], $_repay_part_stat[13], $_repay_part_stat[14], $_repay_part_stat[16], $_repay_part_stat[18], $_repay_part_stat[19]);
		} else if ($data['external_order'] == 'talkpay') {
			unset($_repay_part_stat[12], $_repay_part_stat[13], $_repay_part_stat[14], $_repay_part_stat[16]);
        }
	}else $_repay_part_stat=array("" => 0);

	if($cfg['use_partner_shop'] == 'Y') {
		$prd_orderby = "partner_no2='{$admin['partner_no']}' desc, partner_no2 asc, ";
		$pasql = ", partner_no, if(dlv_type=1, 0, partner_no) as partner_no2";
	}

    if ($data['external_order'] == 'talkpay') {
        $pasql .= ", external_id";
    }

	$prd_sql = $pdo->iterator("select `stat`,`no` $pasql from `$tbl[order_product]` where `ono`='$ono' order by $prd_orderby stat, no");
	$prdTotal = $prd_sql->rowCount();

    $order_products = array();
    $partner_no = 0;
    foreach ($prd_sql as $prd) {
		if($cfg['repay_part'] == "Y"){
			if($prd['stat'] == 13 || $prd['stat'] == 15 || in_array($prd['stat'], array(17,22,23)) || in_array($prd['stat'], array(19,24))){
				$nstat = $prd['stat'];
				if(in_array($nstat, array(17, 22, 23)) == true) $nstat = 17; // npay
				if(in_array($nstat, array(24, 25, 26)) == true) $nstat = ''; // npay

				$sn="prdStat".$nstat;
				${$sn}[]=$prd['no'];
			} else {
                $prdStat[]=$prd['no'];
                $order_products[] = $prd;
            }
		}else $prdStat[]=$prd['no'];
        $partner_no = $prd['partner_no'];
	}
	unset($prd_orderby, $pasql, $prd_sql, $prd);

    // 카카오 톡바이 주문 상품 API 일괄 실행
    $external_data = array();
    if ($data['external_order'] == 'talkpay') {
        $talkpay = new KakaoTalkPay($scfg);

        $_tmp = array_chunk($order_products, 20); // 한번에 20개씩 조회 가능
        foreach ($_tmp as $val) {
            $ids = '';
            foreach ($val as $key => $val) {
                $ids .= ','.$val['external_id'];
            }

            $ret = $talkpay->getOrderProduct(trim($ids, ','));
            foreach ($ret->orderProducts as $val) {
                $external_data[$val->id] = $val;
            }
        }
    }

	$amember = $pdo->assoc("select * from `$tbl[member]` where `no`='$data[member_no]'");
	$mno = $amember['no'];

	if($amember['no'] > 0) {
		$mchecker = array();
		if(isTable($tbl['member_checker'])) {
			$mcres = $pdo->iterator("select no, name from `$tbl[member_checker]` order by name asc");
            foreach ($mcres as $mcdata) {
				$mchecker[$mcdata['no']] = stripslashes($mcdata['name']);
			}
		}
		$member_checker = getMemberChecker($amember);
	}

	$_omode = array("한개의 창만 사용","여러개의 새창 사용");
	if(!$omode) {
		$def_omode=$_COOKIE['def_omode'];
		if(!$def_omode) $def_omode=0;
		$omode=$def_omode;
	}

	// EMS 배송정보
	if($data['nations'] && $data['cart_weight']) {
		$data['nations'] = stripslashes($data['nations']);
		$data['cart_weight'] = number_format($data['cart_weight']);
		$ems_info .= "<span class='p_color2'>(EMS배송 $data[nations] / $data[cart_weight]g)</span>";
	}

	// 결제/환불 내역
	$_mng_name = getMngNameCache();

	$repay_total = $repay_emoney = $repay_milage = $pay_prc = 0;
	$payres = $pdo->iterator("select * from $tbl[order_payment] where ono='$ono' order by no asc");
	$payments = $repayment = array();
    foreach ($payres as $payment) {
		$paycnt[$payment['type']]++;

		$payment['css'] = 'payment_list payment_type'.$payment['type'];
		$payment['reg_id'] = $_mng_name[$payment['reg_id']];
		$payment['confirm_id'] = $_mng_name[$payment['confirm_id']];

		// 정산 환불 내역 검색
        if($cfg['use_partner_shop'] == 'Y') {
            $account_refund = $pdo->assoc("select no, account_idx, del_yn from {$tbl['order_account_refund']} where payment_no='{$payment['no']}'");
            $payment['account_refund'] = $account_refund;
        }

        // 계좌번호 복호
        if ($scfg->comp('use_account_enc', 'Y') == true) {
            $payment['bank_name'] = aes128_decode($payment['bank_name']);
            $payment['bank_account'] = aes128_decode($payment['bank_account']);
        }

		$payments[] = $payment;

		if($payment['type'] == 0) {
			if($is_free_dlv_cpn == true) $_cpn_free_dlv = $payment['dlv_prc'];
			continue;
		}

		if($payment['type'] == 1 || $payment['repay_emoney'] > 0 || $payment['repay_milage'] > 0) {
			$repay_total += $payment['amount']+$payment['repay_emoney']+$payment['repay_milage'];
			$repay_emoney += $payment['repay_emoney'];
			$repay_milage += $payment['repay_milage'];
			if($payment['pay_type'] > 0) $repayment[] = $payment;
		}

		$total_ex_dlv += $payment['ex_dlv_prc'];
		$total_add_dlv += $payment['add_dlv_prc'];
	}

	if($_cpn_free_dlv > 0 && $data['sale5'] >= $_cpn_free_dlv) {
		$cpn_free_dlv = $_cpn_free_dlv;
	}

	if($data['openmarket_id']) {
		$openmarket_mall_name = $pdo->row("select name from $tbl[openmarket_cfg] where api_code='$data[openmarket_id]'");;
		$openmarket_mall_name = stripslashes($openmarket_mall_name);
	}

	// 업체별 배송비 데이터
	if($cfg['use_partner_delivery'] == 'Y') {
		function parseOrderDlvPrc($res) {
			$data = $res->current();
            $res->next();
			if($data == false) return false;

			$data['name'] = stripslashes($data['corporate_name']);
			$data['dlv_prc'] = parsePrice($data['dlv_prc']);

			if($data['partner_no'] == '0') {
				$data['name'] = $GLOBALS['cfg']['company_mall_name'];
			}

			return $data;
		}

		if($admin['level'] == 4) {
			$pquery = " and o.partner_no='$admin[partner_no]'";
		}
		$dlvres = $pdo->iterator("select o.no, o.partner_no, o.dlv_prc, o.first_prc, p.corporate_name from $tbl[order_dlv_prc] o left join $tbl[partner_shop] p on o.partner_no=p.no where o.ono='$ono' $pquery");
		$is_dlv_data = $dlvres->rowCount();
	}

	// 시스템에 의한 취소 확인
	if($data['stat'] == 13) {
		$last_log = $pdo->assoc("select * from $tbl[order_stat_log] where ono='$ono' order by no desc limit 1");
		if($last_log['stat'] == 13 && $last_log['ori_stat'] == 1 && $last_log['system'] == 'Y') {
			$recovery = true;
		}
	}

	// 다중 파트너 주문인지 확인
	$is_multi_partner = 1;
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
		$is_multi_partner = $pdo->rowCount("select no from {$tbl['order_product']} where ono='$ono' and (stat < 10 or stat in (12, 14, 16, 18)) group by partner_no");
	}

	// 정산 진행 중 확인
    if($cfg['use_partner_shop'] == 'Y') {
    	$is_account = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and account_idx>0");
    }

	addPrivacyViewLog(array(
		'page_id' => 'order',
		'page_type' => 'view',
		'target_id' => $data['member_id'],
		'target_cnt' => 1
	));

    // 주문 메모 조회 권한
    $perm_memo = ($admin['level'] == '3' && authCheck('order', 'C0183') == false) ? false : true;

    // 입점사 사은품 숨김 처리
    if ($admin['level'] > 3) {
        $data['order_gift'] = '';
    }

    // 결제방식 변경 시 최초 결제 방식
    if ($data['pay_type_changed']) {
        $org_pay_type = $pdo->row("select pay_type from {$tbl['order_payment']} where ono='{$ono}' order by no asc limit 1");
    }

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/resize.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/order.js?ver=<?=date('YmdHi')?>"></script>

<?php if ($scfg->comp('use_order_list_protect', 'Y') == true) { ?>
<script>
$(document).on('copy', function() {
    window.alert('개인 정보 보호를 위해 복사하기를 사용할 수 없습니다.');
    return false;
});
</script>
<style type="text/css" media="print">
#pop_crm {display: none;}
.noprint {display: block important;}
</style>
<div class="noprint">
    <h1>개인 정보 보호를 위해 인쇄 기능이 제한되었습니다.</h1>
</div>
<?php } ?>

<style type="text/css" title="">
body {background:#e8e8e8;}
</style>
<div id="pop_crm" class="crm_order">
	<div id="header">
		<h1><img src="<?=$engine_url?>/_manage/image/crm/title_crm_order.png" alt="wisa 주문정보"></h1>
		<div class="tab">
			<div class="tab_order">
				<?php if ($admin['level'] < 4) { ?>
				<a href="./?body=member@member_view.frm&smode=order&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_order_on.png" alt="주문서"></a>
				<form name="" method="post" action="" target="" onSubmit="return" class="search search_order">
					<div class="search_input">
						<input type="text" name="" value="" class="input_search" placeholder="이름, 주문번호로 주문조회가 가능합니다!" onkeyup="autoComplete('order', this)"><input type="image" src="<?=$engine_url?>/_manage/image/crm/btn_search.gif" class="btn_search">
					</div>
					<div id="auto_complete" class="auto">
					</div>
				</form>
				<?php } ?>
			</div>
			<?php if ($admin['level'] < 4) { ?>
			<div class="tab_member">
				<a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_member.png" alt="회원정보"></a>
			</div>
			<?php } ?>
		</div>
		<a href="javascript:;" onClick="layTgl2('setDiv');" class="btn_setup">설정</a>
		<!-- 설정 새창 -->
		<div id="setDiv" class="setDiv" style="display:none;">
			<table class="tbl_mini full">
				<tr>
					<th scope="row">주문상세창 새창설정</th>
					<td><?=selectArray($_omode,"omode", 2, "", $def_omode,"setConfig('def_omode',this.value);location.reload();");?></td>
				</tr>
			</table>
			<div class="pop_bottom"><span class="box_btn_s gray"><input type="button" value="닫기" onClick="layTgl2('setDiv');"></span></div>
		</div>
		<!-- //설정 새창 -->
	</div>
	<div id="container">
		<div class="snb">
			<div class="area_scroll">
				<div class="profile">
					<?php if ($amember['no'] && $admin['level'] < 4) { ?>
						<p class="name"><a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>"><?=$amember['name']?><?php if ($cfg['member_join_id_email'] != 'Y') { ?>(<?=$amember['member_id']?>)<?php } ?> <?=blackIconPrint($amember['blacklist'])?></a></p>
					<?php } else { ?>
						<p class="name"><?=$data['buyer_name']?></p>
					<?php } ?>
					<p class="email"><?=$amember['email']?></p>
					<?php if ($admin['level'] < 4) { ?>
					<div class="send">
						<span class="box_btn_s"><a onclick="smsSend('<?=$amember['cell']?>')">sms</a></span>
					</div>
					<?php } ?>
				</div>
				<div class="order_no">
					<dl>
						<dt>주문번호</dt>
						<dd>
							<?php if ($data['checkout'] == 'Y') { ?><img src="<?=$engine_url?>/_manage/image/order/ic_npay.png" alt="npay"><?php } ?>
							<?php if ($data['external_order'] == 'talkpay') { ?><img src="<?=$engine_url?>/_manage/image/order/ic_talkpay.png" alt="talkpay" style="width:16px"><?php } ?>
							<?php if ($data['talkstore'] == 'Y') { ?><img src="<?=$engine_url?>/_manage/image/order/ic_talkstore.png" alt="kakaoTalkStore"><?php } ?>
							<?php if ($data['smartstore'] == 'Y') { ?><img src="<?=$engine_url?>/_manage/image/order/ic_smartstore.png" alt="smartstore"><?php } ?>
							<?php if ($data['x_order_id'] == 'subscription') {?><img src="<?=$engine_url?>/_manage/image/order/ic_subscription.png"><?php } ?>
							<?=$ono?>
                            <?php if ($scfg->comp('use_order_shortcut', 'Y') == true) { ?>
                            <a href="?body=order@order_shortcut.exe&ono=<?=$ono?>" target="hidden<?=$now?>"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
                            <?php } ?>
						</dd>
					</dl>
				</div>
				<ul id="crm_order_list" class="menu">
					<li><a class="main selected" onclick="tabover(0, 'main')">전체</a></li>
					<li><a class="order_prd" onclick="tabover(1, 'order_prd')">주문 상품</a></li>
					<li><a class="order" onclick="tabover(2, 'order')">주문 정보</a></li>
					<li><a class="info" onclick="tabover(3, 'info')">주문자 정보</a></li>
					<li><a class="delivery" onclick="tabover(4, 'delivery')">배송지 정보</a></li>
					<?php if ($admin['level'] < 4) { ?>
					<li><a class="counsel" onclick="tabover(5, 'counsel')">주문 관련 상담</a></li>
					<?php } ?>
                    <?php if ($perm_memo == true) { ?>
					<li><a class="memo" onclick="tabover(6, 'memo')">주문메모</a></li>
                    <?php } ?>
					<?php if (is_array($_ord_add_info)) { ?>
					<li><a class="additional" onclick="tabover(7, 'additional')">추가 정보</a></li>
					<?php } ?>

				</ul>
			</div>
		</div>
		<div id="content">
			<div class="content_box">
				<div class="tabcnt tabcnt1">
					<form name="prdFrm" id="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
						<input type="hidden" name="body" value="order@order_prd_stat.exe">
						<input type="hidden" name="ono" value="<?=$data['ono']?>">
						<input type="hidden" name="ostat" value="<?=$data['stat']?>">
						<input type="hidden" name="mno" value="<?=$mno?>">
						<input type="hidden" name="exec">
						<input type="hidden" name="stat">
						<input type="hidden" name="ext">
						<div class="box_title first">
							<h3 class="title">주문 상품</h3>
							<div class="btns">
								<?php if ($is_account == 0) { ?>
									<?php if ($data['stat'] < 4 && $data['checkout'] != 'Y' && $data['talkstore'] != 'Y' && $data['smartstore'] != 'Y' && !$data['external_order'] && $admin['level'] < 4 && $is_multi_partner < 2){?>
									<span class="box_btn_s icon copy2" onclick='orderAddprd(<?=$data['no']?>, "<?=$partner_no?>")'><input type='button' value='상품추가'></a></span>
									<?php } ?>
									<?php if($data['stat'] < 3 && $data['checkout'] != 'Y' && $data['talkstore'] != 'Y' && $data['smartstore'] != 'Y' && !$data['external_order'] && $admin['level'] < 4){?>
									<span class="box_btn_s" onclick="orderAddgift('<?=$data['ono']?>')"><input type='button' value='+ 사은품추가'></span>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<?php if($cfg['repay_part'] == 'Y') { ?>
						<div id="controlTab" class="none_margin">
							<ul class="tabs">
								<?php
									foreach($_repay_part_stat as $key => $val){
										if($key > 0 && $key % 2 == 0) continue;

                                        $prdNum = 0;
                                        if ($key == 0) {
    										$prdNum = count($prdStat);
                                        } else {
                                            if (isset(${'prdStat'.$key}) == true && is_array(${'prdStat'.$key}) == true) {
                                                $prdNum = count(${'prdStat'.$key});
                                            }
                                        }
										$sel = ($key == 0) ? "class='selected'" : '';
								?>
								<li id="ctab_<?=$key?>" onclick="layerSH(<?=$key?>)" <?=$sel?> style="width:80px;"><?=$val?>상태(<?=number_format($prdNum)?>)</li>
								<?php } ?>
							</ul>
						</div>
						<?php } ?>
						<?php include $engine_dir.'/_manage/order/order_view_part.inc.php'; ?>
						<?php if ($admin['level'] < 4 || $cfg['use_partner_delivery'] == 'Y') { ?>
						<div class="box_middle2 left">
							<?php
							if($data['checkout'] == 'Y' || $data['smartstore'] == 'Y') { // 네이버페이 버튼세트 소스 길이 문제로 분리
								include 'order_checkout_btn_set.inc.php';
                            } else if($data['external_order'] == 'talkpay') { // 카카오 페이구매
                                include 'order_talkpay_btn_set.inc.php';
							} else if($data['talkstore'] == 'Y') {
							?>
							<div id="repayStat">
								<ul class="list_info">
									<li class="warning">카카오톡 스토어 주문은 '<?=$_order_stat[3]?>' 상태 변경 후 배송지 정보를 확인할 수 있습니다.</li>
									<li>반품요청 시 카카오톡 스토어 판매자센터에서 반품수거 및 승인을 진행해야 합니다.</li>
								</ul>
								<p>
									<span class="box_btn_s"><input type="button" value="품절취소" onClick="jsPrdStat(15);"></span>
									<span class="box_btn_s"><input type="button" value="배송지연" onclick="jsPrdStat(401)"></span>
								</p>
								<p style='margin: 5px 0;'>
									<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[3]?>" onClick="jsPrdStat(3);"></span>
									<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[4]?>" onClick="jsPrdStat(4);"></span>
								</p>
							</div>
							<?php
							} else if($recovery == true) {
							?>
							<div>
								<span class="box_btn_s blue"><input type="button" value="취소 이전 상태로 변경" onClick="recovery(1);"></span>
							</div>
							<?php } else if($data['stat'] == '11' || $data['stat'] == '31') { ?>
							<div>
								<span class="box_btn_s blue"><input type="button" value="승인대기 해제" onClick="recovery(2);"></span>
							</div>
							<?php } else { ?>
							<div id="repayStat">
								<?php
								foreach($_repay_part_stat as $key => $val) {
									if(!$key) continue;
									if($is_account > 0 && ($key == 18 || $key == 19)) continue;
								?>
								<span class="box_btn_s"><input type="button" value="<?=$val?>" onClick="jsPrdStat('<?=$key?>');"></span>
								<?php } ?>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<span class="box_btn_s"><input type="button" value="배송보류" onclick="setDlvHold('Y')"></span>
								<span class="box_btn_s"><input type="button" value="배송보류해제" onclick="setDlvHold('N')"></span>
								<span class="box_btn_s"><input type="button" value="배송지변경" onClick="jsAddr();"></span>
							</div>

							<div style='margin: 5px 0;'>
								<?php if($admin['level'] < 4) { ?>
								<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[1]?>" onClick="jsPrdStat(1);"></span>
								<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[2]?>" onClick="jsPrdStat(2);"></span>
								<?php } ?>
								<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[3]?>" onClick="jsPrdStat(3);"></span>
								<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[4]?>" onClick="jsPrdStat(4);"></span>
								<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[5]?>" onClick="jsPrdStat(5);"></span>
							</div>
							<?php } ?>
						</div>
						<?php } ?>
						<div id="repayDetail" style="border:2px solid #44affb; margin:10px; padding:10px; display:none;"></div>
					</form>
					<?php if ($data['pay_type'] == 17) { ?>
					<div class="box_middle2 left">
						<ul class="list_msg">
							<li><span class="p_color2">페이코 결제</span>는 페이코 자체 할인 적용으로 인해 관리자모드에서 보이는 실 결제금액보다 고객이 실제 지불한 금액이 작을 수 있으므로 현금으로 수동환불시 주의하시기 바랍니다.</li>
						</ul>
					</div>
					<?php } else if($data['pay_type'] == 22) { ?>
					<div class="box_middle2 left">
						<ul class="list_msg">
							<li><span class="p_color2">토스 결제</span>는 토스 자체 할인 적용으로 인해 관리자모드에서 보이는 실 결제금액보다 고객이 실제 지불한 금액이 작을 수 있으므로 현금으로 수동환불시 주의하시기 바랍니다.</li>
						</ul>
					</div>
					<?php } ?>
					<div class="box_title">
						<h3 class="title">결제금액정보</h3>
					</div>
					<div class="box_middle4" style="padding:0;">
						<?php include 'order_view_prc.inc.php'; ?>
					</div>

					<?php if ($is_dlv_data > 0) { ?>
					<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
						<input type="hidden" name="ono" value="<?=$ono?>">
						<input type="hidden" name="body" value="order@order_update.exe">
						<input type="hidden" name="exec" value="partner_dlv_prc">
						<div class="box_title">
							<h3 class="title">배송비 정산 정보</h3>
							<?php if ($admin['level'] < 4) { ?>
							<span class="box_btn_s blue btns"><input type="submit" value="배송비 정산 정보수정"></span>
							<?php } ?>
						</div>
						<table class="tbl_col">
							<caption class="hidden">배송비 정산 정보</caption>
							<colgroup>
								<col>
								<col style="width:200px">
								<col style="width:200px">
							</colgroup>
							<thead>
								<tr>
									<th scope="col">업체명</th>
									<th scope="col">최초배송비</th>
									<th scope="col">총배송비</th>
								</tr>
							</thead>
							<tbody>
								<?php while ($dlvdata = parseOrderDlvPrc($dlvres)) { ?>
								<tr>
									<td class="left"><?=$dlvdata['name']?></td>
									<td class="right"><?=parsePrice($dlvdata['first_prc'], true)?> <?=$cfg['currency']?></td>
									<td class="right">
										<?php if ($dlvdata['account_idx'] > 0) { ?>
										<?=number_format($dlvdata['dlv_prc'])?> <?=$cfg['currency']?> <span class="p_color">(정산됨)</span>
										<?php } else { ?>
										<input type="text"
											name="dlv_prc[<?=$dlvdata['no']?>]"
											value="<?=$dlvdata['dlv_prc']?>"
											class="input right"
											size="10"
											onfocus="this.select();"
										> <?=$cfg['currency']?>
										<?php } ?>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
						<?php if ($admin['level'] < 4) { ?>
						<div class="box_bottom left">
							<ul class="list_msg">
								<li>상단에서 제공되는 배송비는 정산용 배송비입니다.</li>
								<li>교환처리를 통해 추가 배송비가 발생될 경우 입점업체에 정산될 배송비를 수정할 수 있습니다.</li>
								<li>자동 계산된 업체별 배송비 정산 정보는 반품처 및 정산 방법에 따라 필요시 별도 수정할 수 있습니다.</li>
							</ul>
						</div>
						<?php } ?>
					</form>
					<br>
					<?php } ?>
				</div>
				<form name="ordFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
					<input type="hidden" name="ono" value="<?=$ono?>">
					<input type="hidden" name="body" value="order@order_update.exe">
					<input type="hidden" name="stat" value="<?=$data['stat']?>">
					<input type="hidden" name="exec" value="">
					<input type="hidden" name="ext" value="">
					<div class="tabcnt tabcnt2">
						<!-- 주문정보 -->
						<div class="box_title first">
							<h3 class="title">주문정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">주문정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">주문 번호</th>
								<td colspan="2">
									<b><?=$ono?></b>
									<?php if ($openmarket_mall_name) { ?>
									<span class="p_color2">(<?=$openmarket_mall_name?>)</span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th scope="row">주문 일시</th>
								<td colspan="2"><?=date("Y/m/d H:i:s",$data['date1'])?></td>
							</tr>
							<tr>
								<th scope="row">결제 방법</th>
								<td colspan="2">
									<!-- 결제방법 -->
									<div>
                                        <?php if ($data['pay_type'] == '25') { ?>
                                        <img src="<?=$engine_url?>/_manage/image/icon/pay25.gif">
                                        <?php } elseif ($data['pay_type'] == '27') { ?>
                                        <img src="<?=$engine_url?>/_manage/image/icon/pay27.gif">
                                        <?php } else { ?>
                                        <?=$pay_type?>
                                        <?php } ?>
                                    </div>
									<?php if ($data['milage_recharge']=="Y") {//적립금 환급 정보?>
									<div>
										<strong class="p_color3">* 적립금 환급일시</strong> : <?=date("Y/m/d H:i",$data['milage_recharge_date'])?>
										<span class="explain">(<strong>취소/환불/반품</strong>으로 인해 결제시 사용한 적립금을 환급해드렸습니다)</span>
									</div>
									<?php } ?>
									<?php if ($data['emoney_recharge'] == 'Y') {//예치금 환급정보?>
									<div>
										<strong class="p_color3">* 예치금 환급일시</strong> : <?=date("Y/m/d H:i",$data['emoney_recharge_date'])?>
										<span class='explain'>(<strong>취소/환불/반품</strong>으로 인해 결제시 사용한 예치금을 환급해드렸습니다)</span>
									</div>
									<?php } ?>
                                    <?php if ($data['pay_type_changed'] && $org_pay_type != $data['pay_type']) { ?>
                                    <div style="margin-top: 10px">
                                        <span class="box_btn_s"><input type="button" class="paytype_restore" value="<?=$_pay_type[$org_pay_type]?> 주문으로 원상복구"></span>
                                    </div>
                                    <?php } ?>
								</td>
							</tr>
							<?php
								if($admin['level'] < 4) {
									include 'order_payment.inc.php';
								}

                                // 카드 취소 다이얼로그
                                require 'order_card_info.inc.php';

								// 현금영수증 정보 조회
								if($data['pay_type']=="2" && $admin['level'] < 4) {
									$_cash = $pdo->assoc("select * from `$tbl[cash_receipt]` where `ono`='$data[ono]' order by no desc limit 1");

									// 타 상점과의 주문번호 충돌 방지
									if(defined('use_cash_receipt_prefix') == true) {
										$_cash['ono'] = $data['date1'].'_'.$_cash['ono'];
									}
							?>
							<tr>
								<th scope="row">현금영수증</th>
								<td colspan="2">
									<?php if ($_cash['no']) { ?>
										신청함 (발급번호: <u><?=$_cash['cash_reg_num']?></u>,
										금액: <u><?=number_format($_cash['amt1'])?></u>,
										현재발급상태: <u><?=$_order_cash_stat[$_cash['stat']]?></u>) <?=($_cash['mtrsno']) ? cashReceiptView($_cash['ono']) : '';?>
										<span class="box_btn_s"><a href="./?body=order@order_cash_receipt_new" target="_blank">현금영수증 관리</a></span>
									<?php } else { ?>
										신청내역없음 <span class="box_btn_s"><a href="?body=order@order_cash_receipt_sub&ono=<?=$ono?>" target="_blank">개별 현금영수증 등록</a></span>
									<?php } ?>
								</td>
							</tr>
							<?php
								}
							?>
							<?php if ($cpn_cnt > 0) { ?>
							<tr>
								<th scope="row">사용 쿠폰</th>
								<td colspan="2">
									<table class="tbl_inner line full">
										<thead>
											<tr>
												<th scope="col">구분</th>
												<th scope="col">쿠폰명</th>
												<th scope="col">할인액(율)</th>
												<th scope="col">사용기한</th>
												<th scope="col">제한사항</th>
											</tr>
										</thead>
										<tbody>
											<?php while ($cpn = myCouponList('')) { ?>
											<tr>
												<td><?php if ($cpn['is_type'] == "B") echo "시리얼"; else echo "온라인"; ?></td>
												<td class="left"><a href="#" onclick="cpndetail.open('no=<?=$cpn['cno']?>&readOnly=true'); return false;"><?=$cpn['name']?></a></td>
												<td class="right"><?=parsePrice($cpn['sale_prc'], true)?> <?=$cpn['sale_type_k']?></td>
												<td><?=$cpn['udate_type']?></td>
												<td>최소주문금액 <?=parsePrice($cpn['prc_limit'], 2)?> <?=$cfg['currency_type']?> <?=$cpn['sale_limit_k']?></td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th scope="row">상태</th>
								<td colspan="2">
									<ul class="ordStat">
										<?php
											for($ii = 1; $ii <=5; $ii++) {
												$class = ($data['stat'] < 10 && $ii <= $data['stat']) ? 'blue' : '';
												$date = ($data['date'.$ii]) ? date("y/m/d g:i A",$data['date'.$ii]) : '-';
												if($ii == 5) $childno = "class='last-child'";
										?>
										<li <?=$childno?>>
											<dl>
												<dt><span class="<?=$class?>"><?=$_order_stat[$ii]?></span></dt>
												<dd><?=$date?></dd>
											</dl>
										</li>
										<?php } ?>
									</ul>
								</td>
							</tr>
							<tr>
								<th scope="row">상태변경내역</th>
								<td colspan="2">
									<ul class="list_msg">
										<?php
											$stat_log_q = $pdo->iterator("select * from `$tbl[order_stat_log]` where `ono`='$data[ono]' order by `no`");
                                            foreach ($stat_log_q as $stat_log_r) {
												if($stat_log_r['system'] == "Y"){
													$log_u="시스템";
												}elseif($stat_log_r['system'] == "D"){
													$log_u="데이콤";
												}else{
													if($stat_log_r['admin_id']){
														$log_u="관리자[".$stat_log_r['admin_id']."]님";
													}
													if(!$stat_log_r['admin_id'] || ($stat_log_r['member_id'] && ($stat_log_r['stat'] == 5 && $data['receive_date'] == $stat_log_r['reg_date']))){
														$log_u="<a href=\"javascript:viewMember('$stat_log_r[member_no]')\">".$stat_log_r['member_name']."고객[".$stat_log_r['member_id']."]님</a>";
													}
												}

												if($stat_log_r['stat'] == 100) {
													echo '<li>'.date("Y-m-d H:i", $stat_log_r['reg_date'])." : ".$log_u."에 의해 ".stripslashes($stat_log_r['content']);
													if($stat_log_r['payment_no']) {
														echo " <a href='#' onclick='cdetail.open(\"ono=$stat_log_r[ono]&payment_no=$stat_log_r[payment_no]\"); return false;' class='p_color'>[상세]</a>";
													}
													echo '</li>';
												} else if($stat_log_r['spt'] != null) {
													switch($stat_log_r['stat']) {
														case "70" :
															$oprd = $pdo->assoc("select name, pno, buy_ea, total_prc from $tbl[order_product] where no='$stat_log_r[spt]'");
															$oprd['name'] = stripslashes($oprd['name']);
															$oprd['total_prc'] = parsePrice($oprd['total_prc'], true);
															$spt_mode = "상품 <a href='?body=product@product_register&pno=$oprd[pno]' target='_blank'>'$oprd[name]'</a> $oprd[buy_ea]개가 $oprd[total_prc] $cfg[currency_type] 에 추가";
															break;
														case "80" :
															$stat_log_r['spt'] = parsePrice($stat_log_r['spt'], true);
															$spt_mode = "배송비 수정 -> $stat_log_r[spt] $cfg[currency_type]";
															break;
														case "81" :
															$stat_log_r['spt'] = parsePrice($stat_log_r['spt'], true);
															$spt_mode = "배송비가 적립금으로 환불 $stat_log_r[spt] $cfg[currency_type]";
															break;
													}

													if($stat_log_r['spt'] == '70') $spt_mode = '<strong>배송보류 처리</strong>';
													if($stat_log_r['spt'] == '71') $spt_mode = '<strong>배송보류 해제</strong>';

													echo '<li>'.date("Y-m-d H:i", $stat_log_r['reg_date'])." : ".$log_u." 에 의해 $spt_mode</li>\n";
												} else {
													if($stat_log_r['member_name'] == 'erpsafedlv') $log_u .= "이 <span class='p_color2'>안전배송</span>";
													echo '<li>'.date("Y-m-d H:i", $stat_log_r['reg_date'])." : ".$log_u." 에 의해 <strong>".$_order_stat[$stat_log_r['stat']]."</strong>(으)로 변경</li>\n";
												}
											}
										?>
									</ul>
								</td>
							</tr>
							<tr>
								<th scope="row">수취확인일시</th>
								<td colspan="2"><?php if($data['receive'] == "Y" && $data['receive_date']){ echo date("Y-m-d H:i", $data['receive_date']); }else{ echo "수취 확인을 하지 않았습니다."; } ?></td>
							</tr>
							<?php if (str_replace('@', '',$data['order_gift'])) { ?>
							<tr>
								<th scope="row" name="gift">사은품</th>
								<td colspan="2">
									<ul class="list_msg">
										<?php
											$_ord_gift = explode('@', $data['order_gift']);
											for($ii=0; $ii<sizeof($_ord_gift); $ii++){
												if($_ord_gift[$ii]){
													$gift = get_info($tbl['product_gift'],"no",$_ord_gift[$ii]);
													$gift['name'] = strip_tags(stripslashes($gift['name']));
													$gift_url = ($gift['no'] && $gift['delete'] != "Y") ? "./?body=promotion@product_gift_register&gno=$gift[no]\" target=\"_blank" : "javascript:;\" onclick=\"alert('삭제된 사은품입니다');";
                                                    if ($admin['level'] == 4) {
                                                        $gift_url = '#gift';
                                                    }
										?>
										<li><a href="<?=$gift_url?>"><?=$gift['name']?></a></li>
										<?php
												}
											}
										?>
								</td>
							</tr>
							<?php } ?>
						</table>
						<!-- //주문정보 -->
					</div>

					<!-- 주문자 정보 -->
					<div class="tabcnt tabcnt3">
						<div class="box_title first">
							<h3 class="title">주문자 정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">주문자 정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">이름</th>
								<td>
                                    <?=$data['buyer_name']?>
                                    <?php if ($admin['level'] == 4) { ?>
                                    (<?=$data['member_id']?>) <?=blackIconPrint('',$data)?>
                                    <?php } else { ?>
                                    <a href="javascript:viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')">(<?=$data['member_id']?>) <?=blackIconPrint('',$data)?></a>
                                    <?php } ?>
                                </td>
							</tr>
							<?php if ($amember['no'] > 0) { ?>
							<tr>
								<th scope="row">회원그룹</th>
								<td>
									<ul class="list_msg">
										<li><?=stripslashes($pdo->row("select name from $tbl[member_group] where no='$amember[level]'"))?></li>
										<?php foreach ($member_checker as $val) { ?><li><?=$val?></li><?php } ?>
									</ul>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th scope="row">전화번호</th>
								<td><?=$data['buyer_phone']?></td>
							</tr>
							<tr>
								<th scope="row">휴대폰</th>
								<td>
									<?=$data['buyer_cell']?>
									<?php if ($admin['level'] < 4) { ?>
									(주문 관련 SMS 수신 :
									<label class="p_cursor"><input type="radio" name="order_sms_chage" value="Y" <?=checked($data['sms'], 'Y')?>> 동의</label>
									<label class="p_cursor"><input type="radio" name="order_sms_chage" value="N" <?=checked($data['sms'], 'N')?>> 거부</label>
									<span class="box_btn_s"><input type="button" value="변경" onClick="updeteOrder('order_sms_chage','')"></span>)
									<span class="box_btn_s"><input type="button" value="SMS" onClick="smsSend('<?=$data['buyer_cell']?>')"></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th scope="row">이메일</th>
								<td>
									<?=$data['buyer_email']?>
                                    <?php if ($admin['level'] < 4) { ?>
									<span class="box_btn_s"><input type="button" value="메일보내기" onClick="smsMail('<?=$data['buyer_email']?>')"></span>
                                    <?php } ?>
								</td>
							</tr>
							<tr>
								<th scope="row">주문자 아이피</th>
								<td>
									<?php if ($data['checkout'] == 'Y') { ?>
									네이버페이 주문은 주문자 아이피가 표시되지 않습니다.
									<?php } else if ($data['talkstore'] == 'Y') { ?>
									카카오톡 스토어 주문은 주문자 아이피가 표시되지 않습니다.
									<?php } else if ($data['external_order'] == 'talkpay') { ?>
									톡체크아웃 주문은 주문자 아이피가 표시되지 않습니다.
									<?php } else if ($data['smartstore'] == 'Y') { ?>
									네이버 스마트스토어 주문은 주문자 아이피가 표시되지 않습니다.
                                    <?php } else if ($admin['level'] > 3) { ?>
                                    <?=$data['ip']?>
									<?php } else { ?>
									<a href="./?body=log@count_log_list&exec=search&ext=&y1=<?=date("Y", $data['date1'])?>&m1=<?=date("m", $data['date1'])?>&d1=01&h1=00&y2=<?=date("Y", $data['date1'])?>&m2=<?=date("m", $data['date1'])?>&d2=<?=date("t", $data['date1'])?>&h2=23&search_type=ip&search_str=<?=$data['ip']?>" target="_blank"><?=$data['ip']?></a>
									<?php } ?>
								</td>
							</tr>
						</table>
					</div>
					<!-- //주문자 정보 -->

					<!-- 배송지 정보 -->
					<div class="tabcnt tabcnt4">
						<div class="box_title first">
							<h3 class="title">배송지 정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">배송지 정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">받는 분</th>
								<td><?=$data['addressee_name']?></td>
							</tr>
							<?php if($cfg['order_add_field_use'] == 'Y') { ?>
							<tr>
								<th scope="row">ID 번호</th>
								<td><?=$data['addressee_id']?></td>
							</tr>
							<?php } ?>
							<?php if($data['nations']) { ?>
							<tr>
								<th scope="row">배송국가</th>
								<td><?=getCountryNameFromCode($data['nations'])?></td>
							</tr>
							<?php } ?>
							<tr>
								<th scope="row">주소</th>
								<td>
									(우) <?=$data['addressee_zip']?>
									<?=$data['nations']?><br>
									<?=$data['addressee_addr1']?><br>
									<?php if(fieldExist($tbl['order'],'addressee_addr3')) { ?>
										<?=inputText($data['addressee_addr3'])?>
									<?php } ?>
									<?php if (fieldExist($tbl['order'],'addressee_addr4')) { ?>
										<?=inputText($data['addressee_addr4'])?>
									<?php } ?>
									<?=inputText($data['addressee_addr2'])?>
								</td>
							</tr>
							<tr>
								<th scope="row">전화번호</th>
								<td><?=$data['addressee_phone']?></td>
							</tr>
							<tr>
								<th scope="row">휴대폰</th>
								<td>
									<?=$data['addressee_cell']?>
                                    <?php if ($admin['level'] < 4) { ?>
									<span class="box_btn_s"><input type="button" value="SMS" onClick="smsSend('<?=$data['addressee_cell']?>')"></span>
                                    <?php } ?>
								</td>
							</tr>
							<tr>
								<th scope="row">주문메세지</th>
								<td><?=stripslashes($data['dlv_memo'])?></td>
							</tr>
							<?php if ($_use['order_gift'] == 'Y') {?>
							<tr>
								<th scope="row">보내는 분 표기</th>
								<td><?=$data['gift_name']?></td>
							</tr>
							<tr>
								<th scope="row">배송 희망 시간</th>
								<td><?=$data['dlv_date']?></td>
							</tr>
							<tr>
								<th scope="row">메세지 종류</th>
								<td><?=$_gift_type[$data['gift_type']]?></td>
							</tr>
							<tr>
								<th scope="row">메세지</th>
								<td><?=nl2br(stripslashes($data['gift_msg']))?></td>
							</tr>
							<?php } ?>
						</table>
					</div>
					<!-- //배송지 정보 -->

					<?php if ($admin['level'] < 4) { ?>
					<!-- 주문 관련 상담 -->
					<div class="tabcnt tabcnt5">
						<div class="box_title first">
							<h3 class="title">주문 관련 상담</h3>
						</div>
						<table class="tbl_col">
							<caption class="hidden">주문 관련 상담</caption>
							<colgroup>
								<col style="width:60px">
								<col style="width:120px">
								<col>
								<col style="width:100px">
								<col style="width:100px">
							</colgroup>
							<thead>
								<tr>
									<th scope="col">번호</th>
									<th scope="col">분류</th>
									<th scope="col">제목</th>
									<th scope="col">질문일</th>
									<th scope="col">답변일</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$sql="select * from `$tbl[cs]` where `ono`='$ono' order by `reg_date` desc";
									$res = $pdo->iterator($sql);
									$idx = $res->rowCount();
                                    foreach ($res as $cs) {
										$rclass=($idx%2==0) ? "tcol2" : "tcol3";
										if($cs['reply_date']) {
											$cs['reply_date']=date("Y/m/d",$cs['reply_date']);
										}
										else {
											$cs['reply_date']="답변없음";
										}
								?>
								<tr>
									<td><?=$idx?></td>
									<td><?=$_cust_cate[$cs['cate1']][$cs['cate2']]?></td>
									<td class="left"><a href="javascript:;" onClick="wisaOpen('pop.php?body=member@1to1_view.frm&no=<?=$cs['no']?>')"><?=cutStr(stripslashes($cs['title']),50)?></a></td>
									<td><?=date("Y/m/d",$cs['reg_date'])?></td>
									<td><?=$cs['reply_date']?></td>
								</tr>
								<?php
										$idx--;
									}
								?>
							</tbody>
						</table>
					</div>
					<!-- 주문 관련 상담 -->
					<?php } ?>

                    <?php if ($perm_memo == true) { ?>
					<!-- 주문메모 리스트 -->
					<div class="tabcnt tabcnt6">
						<div class="box_title first">
							<h3 class="title">주문메모</h3>
						</div>
						<div id="product_memo_list_in">
						<?PHP
							$memo_type = 1;
							$pno = $ono;
							require $engine_dir.'/_manage/product/product_memo_list_in.exe.php';
						?>
						</div>
					</div>
					<!-- 주문메모 리스트 -->
                    <?php } ?>

					<?php if (is_array($_ord_add_info)) { ?>
					<div class="tabcnt tabcnt7">
						<!-- 추가 정보 -->
						<div class="box_title first">
							<h3 class="title">추가 정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">추가 정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<?php foreach ($_ord_add_info as $key => $val) { ?>
							<tr>
								<th scope="row"><?=stripslashes($_ord_add_info[$key]['name'])?></th>
								<td><?=orderAddFrm($key, 0, $data)?></td>
							</tr>
							<?php } ?>
						</table>
					</div>
					<!-- //추가 정보 -->
					<?php } ?>
					<?php if ($data['tax_receipt'] == 'Y' && $admin['level'] < 4) { ?>
					<div class="tabcnt tabcnt7">
						<!-- 세금 계산서 -->
						<div class="box_title first">
							<h3 class="title">세금 계산서</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">세금 계산서</caption>
							<colgroup>
								<col style="width:15%">
								<col style="width:35%">
								<col style="width:15%">
								<col style="width:35%">
							</colgroup>
							<?php
								$tax=get_info($tbl['tax_receipt'],"ono",$ono);
								$tprc2=$data['prd_prc']*0.1;
								$tprc1=$data['prd_prc']-$tprc2;
							?>
							<tr>
								<th scope="row">공급가액</td>
								<td><?=number_format($tprc1)?> <?=$cfg['currency_type']?></td>
								<th scope="row">세액</td>
								<td><?=number_format($tprc2)?> <?=$cfg['currency_type']?></td>
							</tr>
							<tr>
								<th scope="row">회사명</th>
								<td><?=$tax['com_name']?></td>
							</tr>
							<tr>
								<th scope="row">대표자명</th>
								<td><?=$tax['owner']?></td>
								<th scope="row">사업자번호</th>
								<td><?=$tax['biz_num']?></td>
							</tr>
							<tr>
								<th scope="row">사업장 주소</th>
								<td colspan='3'><?=$tax['address']?></td>
							</tr>
							<tr>
								<th scope="row">업태</th>
								<td><?=$tax['cate1']?></td>
								<th scope="row">종목</th>
								<td><?=$tax['cate2']?></td>
							</tr>
							<tr>
								<th scope="row">이메일</th>
								<td colspan="3"><?=$tax['email']?></td>
							</tr>
						</table>
						<!-- 세금 계산서 -->
					</div>
					<?php } ?>
				</form>

				<div class="pop_bottom" style="margin-bottom:50px;">
					<?php if ($admin['level'] < 4 && $data['talkstore'] != 'Y') { ?>
					<span class="box_btn gray"><input type="button" value="삭제" onClick="updeteOrder('delete','')"></span>
					<?php } ?>
                    <?php if ($scfg->comp('use_order_list_protect', 'Y') == false) { ?>
					<span class="box_btn blue"><input type="button" value="인쇄하기" onClick="printOrder('<?=$data['no']?>')"></span>
                    <?php } ?>
					<span class="box_btn gray"><input type="button" value="창닫기" onClick="wclose()"></span>
				</div>
			</div>
			<div class="aside">
				<div id="mng_memo_area">
					<?php if ($data['mng_memo']) { ?>
					<div class="frame_memo write">
						<textarea name="mng_memo" class="input_memo" placeholder="메모를 입력하세요."><?=stripslashes($data['mng_memo'])?></textarea>
						<div class="btn_bottom">
							<input type="button" onClick="updeteOrder('mng_memo','')">
						</div>
					</div>
					<?php } else { include $engine_dir."/_manage/member/member_memo_list.exe.php"; } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	// 세로길이


	// 탭
	function tabover(no, tab_name) {
		var tabs = $('#crm_order_list').find('li>a');

        window.scrollTo(0,0);

        if (no > 0) {
            $('.tabcnt').hide();
            $('.tabcnt'+no).show();
        } else{
            $('.tabcnt').show();
        }
        tabs.removeClass('selected');
        tabs.filter('.'+tab_name).addClass('selected');
	}

	var f = document.ordFrm;
	var is_postpone = <?=$is_postpone?>;
	this.focus();

	// 주문서 인쇄
	function printOrder(no){
		window.open(manage_url+'/_manage/?body=order@order_print.frm&check_pno[]='+no,'prt_viewOrder','top=10,left=10,width=650,status=no,toolbars=no,scrollbars=yes,height=400');
	}

	// 부분 취소
	function jsPrdStat(st){
		var pf=document.prdFrm;
        var checkboxes = pf.querySelectorAll('input[type="checkbox"]:checked');
        var checkMsg = '처리하실 상품을 선택해주세요.'; //선택된 체크박스가 없는 경우 메시지
        var deniedMsg = ''; //상태가 맞지않는 주문상품 포함시 메시지
        for (var i = 0; i < checkboxes.length; i++) {
            var checkbox = checkboxes[i];
            if (st == -1) {
                //변경하려는 상태가 재고확인완료인경우
                if (checkbox.getAttribute('data-stat') != '20') {
                    //재고확인중이 아닌 주문상품은 체크해제
                    checkbox.checked = false;
                    checkMsg = '재고확인중 상태의 상품을 1개 이상 선택해주세요.';
                }
            } else if (st != '13') {
                //변경하려는 상태가 취소/환불이 아닌경우
                if (checkbox.getAttribute('data-stat') == '20' && checkbox.getAttribute('data-naverpay') == 'Y') {
                    //네이버관련 거래건이면서 재고확인중인 주문상품은 체크해제
                    checkbox.checked = false;
                    deniedMsg = '재고확인중 상태에서는 재고추가완료 또는 취소/환불만 가능합니다.';
                }
            }
        }
        if (deniedMsg) {
            alert(deniedMsg);
            return;
        }
		if(!checkCB(pf["pno[]"], checkMsg)) return;
		if(st >= 97 && st <= 99) {
			if(!confirm('네이버페이 주문건의 경우 상태 철회 후 배송중으로 변경됩니다.\n변경하시겠습니까?')) return;
		}
		//스마트스토어
		<?php if($data['checkout'] != 'Y' && $data['smartstore'] != 'Y') { ?>
		if(st == 19) {
			var opno = '';
			var normal_dlv = ind_dlv = 0;
			$(":checked[name='pno[]']").each(function() {
				if(opno) opno += ',';
				opno += this.value;

				if($(this).data('delivery-set').toNumber() == 0) {
					ind_dlv++;
				} else {
					normal_dlv++;
				}
			});

			// 일반 상품과 개별 배송 상품의 동시 교환 금지
			if(ind_dlv > 0 && normal_dlv > 0) {
				window.alert('일반 배송상품과 개별 배송 상품은 동시에 교환할수 없습니다.');
				return false;
			}

			if(win = window.open('./pop.php?body=order@order_exchg.frm&no='+opno, 'orderExchg', 'status=no, width=800px, scrollbars=yes')) win.focus();
			return false;
		}
		<?php } ?>

		pf.body.value = (st < 10) ? "order@order_prd_dlv.exe" : "order@order_prd_stat.exe";
		pf.exec.value = '';
		pf.stat.value=st;
		pf.submit();
	}

	// 부분 취소 실행
	function jsPrdStatSet(f){
		if(!prdStat) return;

		$('#stpBtn').hide();

		if(!confirm("실행하시겠습니까?")) {
			$('#stpBtn').show();
			return;
		}

        printLoading();

		var f=document.prdFrm;
		f.body.value = (prdStat < 10) ? "order@order_prd_dlv.exe" : "order@order_prd_stat.exe";
		f.stat.value=prdStat;
		f.exec.value="process";
		f.submit();
	}

	// 자동 취소 및 승인 대기 주문서 복구
	function recovery(stat) {
		var msg = (stat == 1) ? '자동취소 주문서를 이전상태로 복구합니다.' : '<?=$_order_stat[11]?> 주문을 <?=$_order_stat[2]?> 상태로 복구합니다.';
		if(confirm(msg+'\n진행하시겠습니까?') == true) {
			var f = document.getElementById('prdFrm');
			f.body.value = 'order@order_prd_dlv.exe';
			f.stat.value = stat;
			f.exec.value = 'recovery';
			f.submit();
		}
	}

	// input field 숫자만 입력가능
	function numCk(){
		if(event.keyCode != 8 && ((event.keyCode < 48) || (event.keyCode > 57)))  event.returnValue=false;
	}

	// 상태변경 레이어 처리
	var prev_sh = '0';
	function layerSH(no){
		for(var i = 0; i <= 19; i++) {
			var layer = document.getElementById('edt_layer_'+i);
			var ctab = document.getElementById('ctab_'+i);

			if(!ctab || !layer) continue;

			if(i == no) {
				layer.style.display = '';
				ctab.className = 'selected';
			} else {
				layer.style.display = 'none';
				ctab.className = '';
			}
		}
		if(no > 12) {
			$('#repayStat').hide();
		}
		if (no == 0) {
			$('#repayStat').show();
		}

		$('.check_pno_'+prev_sh).prop('checked', false);
		$('.grp_check').prop('checked', false);
		prev_sh = no;
	}

	function show(obj) {
		var obj = document.getElementById (obj);
		obj.style.display = ( obj.style.display == "none") ? "block" : "none";
	}

	function ordExecFromPrdbox(exec, stat, ext) {
		if (stat == 2 && !confirm("실행하시겠습니까?")) return false;

		var f=document.prdFrm;
		f.body.value = "order@order_update.exe";
		f.exec.value = exec;
		f.stat.value = stat;
		if(ext) f.ext.value = ext;
		f.submit();
		f.stat.value = 2;
	}

	// PG 취소 처리
	function cardCancel(mode){
		var addmsg = '';

		if(mode == 'ALL') {
			if(!confirm('해당 거래내역을 취소하시겠습니까?'+addmsg)) return;
			window.frames[hid_frame].location.href='./?body=order@order_card_cancel.exe&cno=<?=$card['no']?>';
		} else {
			if(!confirm('환불을 진행하시겠습니까?\n환불금액을 입력하신 후 확인 버튼을 눌러주시기 바랍니다.'+addmsg)) return;
			$('.card_cancel_div').show();
		}
	}

    /**
     * 과세, 비과세 금액 입력 취소 방식
     **/
    function cancelTax(cno, o)
    {
        var url = './?body=order@order_card_cancel.exe&cno='+cno;
        $('.card_cancel_div').find('input').each(function() {
            if (this.name) {
                url += '&'+this.name+'='+this.value;
            }
        });

        preventClick(o);
        window.frames[hid_frame].location.href = url;
    }

    function preventClick(o)
    {
        o.disabled = true;
        printLoading();
    }

	// 주문상품 추가 윈도우
	function orderAddprd(no, partner_no) {
		if(!partner_no) partner_no = 0;
		if(win = window.open('./pop.php?body=order@order_exchg.frm&ono=<?=$ono?>&partner_no='+partner_no, 'orderExchg', 'status=no, width=800px, scrollbars=yes')) win.focus();
	}

	// 사은품 추가 윈도우
	function orderAddgift(ono) {
		if(win = window.open('?body=order@order_add_gift.frm&ono='+ono, 'orderAddGift', 'status=no, width=800px, scrollbars=yes')) win.focus();
	}

	// 사은품 삭제
	function orderDeletegift(ono, no) {
		if(confirm('삭제하시겠습니까?')) {
			frm=document.getElementsByName(hid_frame);
			frm[0].src='?body=order@order_add_gift.exe&exec=delete&ono='+ono+'&no='+no;
		}
	}

	// 상품 추가, 교환시 옵션선택시 이벤트
	function optionCal(f, n, v, o) {
		var t = null;
		$('.wing_multi_option').each(function() {
			if(t) $(this).val('');
			if(o == this) t = this;
		});

		var otype = o.getAttribute('data-otype');
		var id = o.id;
		var opt_prc = 0;
		if(otype == '4B') {
			var add_price = o.getAttribute('data-add-price').toNumber();
			var add_price_option = o.getAttribute('data-add-price-option').toNumber();
			if(v.length > 0) {
				opt_prc += add_price;
				if(add_price_option > 0) {
					opt_prc += (v.length*add_price_option);
				}
			}
		} else {
			var v = v.split('::');
			var opt_prc = parseFloat(v[1]);
			if(isNaN(opt_prc)) opt_prc = 0;
		}

		var tmp_opt_prc = 0;
		tot_option_prc[id] = opt_prc;
		for(var key in tot_option_prc) {
			tmp_opt_prc += tot_option_prc[key];
		}
		f.sell_prc.value = parseFloat(f.sell_prc_org.value)+tmp_opt_prc;

		setMultiOptionSoldout();
		setAddPrdMilage(o);
	}

	var tot_option_prc = new Array();
	function initOptionPrc() { // 상품단위의 총 옵션 추가가격 합산데이터 초기화
		tot_option_prc = new Array();
	}

	// 배송보류 처리
	function setDlvHold(s, no) {
		var pnos = '';
		if(no) pnos = no;
		else {
			var mode = s == 'Y' ? '처리' : '해제';
			var cno = $(":checked[name='pno[]']");
			if(cno.length < 1) {
				window.alert('배송보류 '+mode+' 할 주문상품을 선택해 주세요.');
				return false;
			}
			pnos = '';
			cno.each(function() {
				if(pnos) pnos += ',';
				pnos += this.value;
			});
		}

		$.post('./index.php', {'body':'order@order_update.exe', 'exec':'setDlvHold', 'ono':f.ono.value, 'val':s, 'pno':pnos}, function(r) {
			if(r != 'Y' && r != 'N') {
				window.alert(r);
				return;
			}

			pnos = pnos.split(',');
			for(var key in pnos) {
				if(r == 'Y') $('.prd_hold_btn_'+pnos[key]).removeClass('hidden');
				else $('.prd_hold_btn_'+pnos[key]).addClass('hidden');
			}
		});
	}

	// 배송보류 순위 변경
	function setHoldOrder(opno, o) {
		<?php if($cfg['erp_auto_release'] != 'Y') { ?>return;<?php } ?>

		$.get('./index.php?body=order@order_hold_order.exe&opno='+opno, function(r) {
			var tr = $(o).parents('tr').eq(0);
			var colspan = tr.find('td').length;

			$('.holdOrderArea').remove();
			tr.after("<tr class='holdOrderArea nh'><td colspan='"+colspan+"'>"+r+"</td></tr>");
		});
	}

	function holdOrderChg(obj, val) {
		var tr = $(obj).parents('tr').eq(0);
		var next = tr.parent().children().eq(tr.index()+val);
		var bg_ori = tr.css('backgroundColor');
		tr.css('backgroundColor', '#ffffcc');

		if(val > 0) { // down
			next.after(tr);
		} else { // up
			next.before(tr);
		}

		tr.animate({'backgroundColor':bg_ori});
	}

	function holdOrderSave(o) {
		var f = o.form;
		var tmp = '';
		$('.holdOrderNo').each(function() {
			tmp += ','+this.value;
		});

		$.post('./index.php?body=order@order_hold_order.exe', {'exec':'process', 'ono':o.form.ono.value, 'complex_no':o.form.holdOrderComplexNo.value, 'data':tmp}, function(r) {
			alert('배송 우선순위가 변경되었습니다.');
			holdOrderClose();
		});
	}

	function holdOrderClose() {
		$('.holdOrderArea').find('.dialogLayout').slideUp(100, function() {
			$('.holdOrderArea').remove();
		});
	}

	// 주문상품별 주소 변경
	function jsAddr(no) {
		if(!no) {
			var cno = $(":checked[name='pno[]']");
			if(cno.length < 1) {
				window.alert('주소를 변경할 주문상품을 선택해 주세요.');
				return false;
			}
			no = '';
			cno.each(function() {
				if(no) no += ',';
				no += this.value;
			});
		}
		$.post('./index.php?body=order@order_prd_addr.exe', {'no':no}, function(r) {
			$('#repayDetail').html(r).slideDown('fast', function() {
				$(document).scrollTop($(this).offset().top - 200);
			});
		});
	}

	// 주문 상품 추가시 지금 적립금 계산
	var msale_mile_type = "<?=$cfg['msale_mile_type']?>";
	var msale_round = "<?=$cfg['msale_round']?>";
	function setAddPrdMilage(o) {
		var f = o.form;
		var sell_prc = sell_prc_org = f.sell_prc.value.toNumber();
		var total_sale_prc = 0;
		for(var key in _order_sales) {
			var prc = $(f).find('input[name="'+key+'[]"]').val().toNumber();
			if(prc != 0) total_sale_prc += prc;
		}

		// 상품 적립금
		if(milage_type == '2') {
			var pmile = (pmile_per > 0) ? (sell_prc-total_sale_prc)*(pmile_per/100) : 0;
			if(currency_decimal == '0') pmile = Math.floor(pmile);
			f.milage.value = pmile.toFixed(currency_decimal);
		}

		// 회원 적립금
		if(f.member_milage && msale_mile_type == '2') {
			var mmile = (mmile_per > 0) ? (sell_prc-total_sale_prc)*(mmile_per/100) : 0;
			if(currency_decimal == '0') mmile = Math.floor(mmile);
			if(msale_round > 1) {
				mmile = Math.floor(mmile/msale_round)*msale_round;
			}
			f.member_milage.value = mmile.toFixed(currency_decimal);
		}
	}

	// 취소 상품 복구시 배송비 계산
	function rollBackDlvPrc(o) {
		var f = o.form;
		var total_prc = f.total_prc.value.toNumber();

		if(f.dlv_prc_tmp) {
			f.dlv_prc.value = (f.dlv_prc_tmp.value.toNumber()*-1);
		}
		if(f.dlv_prc.checked == true) {
			$('.total_input_prc').html(setComma(total_prc+f.dlv_prc.value.toNumber()));
		} else {
			$('.total_input_prc').html(setComma(total_prc));
		}
	}

	function add_banks() {
		setDimmed();
		ordbank.open();
	}

	function accountRefundDel(refund_no) {
		$.post('./index.php', {'body':'income@partner_account_req.exe', 'exec':'refundDel', 'no':refund_no}, function(r) {
			window.alert(r.message);
			location.reload();
		});
	}

	var cpndetail = new layerWindow('member@able_cpn_detail.exe');
	var cdetail = new layerWindow('order@order_cancel_info.exe');
	var ordbank = new layerWindow('order@order_bank.frm');

    $(function() {
        removeLoading();

        $(window).on('keydown', function(e) {
            if (e.ctrlKey == true && e.altKey == true && e.keyCode == 76) {
                window.open(
                    './pop.php?body=order@order_log&ono=<?=$ono?>',
                    'order_log_<?=$ono?>',
                    'top=20px, left=20px, status=no, toolbars=no, scrollbars=yes, height=400px, width=1200px'
                );
                return false;
            }
        });

        // 결제 방법 원상 복구
        $('.paytype_restore').on('click', function() {
            if (confirm('결제 방법을 원상복구 하시겠습니까?') == true) {
                printLoading();
                $.post('./index.php', {'body': 'order@order_update.exe', 'exec': 'paytype_restore', 'ono': '<?=$ono?>'}, function() {
                    location.reload();
                });
            }
        });
    });
</script>
</html>