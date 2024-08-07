<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  교환 분리 주문서 생성
	' +----------------------------------------------------------------------------------------------+*/

	if(isset($cfg['milage_type']) == false) $cfg['milage_type'] = '1';
	$cpn_fd = (fieldExist($tbl['order_product'], 'sale7') == true) ? '+sale7' : '';
	$partner_fd = ($cfg['use_partner_shop'] == "Y") ? ", partner_no, account_idx" : "";

    $add_field = '';
    $search_no = array();

    // 세트 사용 시
    if ($scfg->comp('use_set_product', 'Y') == true) {
        $add_field .= ", set_idx, count(*) as qty, sum(if(set_idx='', 0, 1)) as set_qty ";
    }

	if($_GET['no']) $opno = explode(',', $_GET['no']);
	if(is_array($opno)) {
		foreach($opno as $key => $val) {
			if(preg_match('/[^0-9]/', $val)) unset($opno[$key]);
		}
		$opno = implode(',', $opno);

		if($cfg['use_partner_shop'] == 'Y') {
			$add_field .= ", partner_no, account_idx";
		}
		$prd = $pdo->assoc("select ono, pno, sum(sale5 $cpn_fd) as cpn_sale $add_field from $tbl[order_product] where no in ($opno)");
		$ono = $prd['ono'];
        if ($prd['set_qty'] > 0) {
            define('__SET_EXCHANGE__', true);

            if ($prd['qty'] > 1) {
                msg('세트상품은 1개씩 교환 가능합니다.', 'close');
            }
        }
        if ($prd['set_idx']) {
            $search_no[] = $prd['pno'];
        }
	} else {
		$ono = addslashes($_GET['ono']);
	}
    $search_no = implode(',', $search_no); // 지정된 상품만 교환상품추가 가능

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	$ord = array_map('stripslashes', $ord);

	$ex_title = '교환';
	if(empty($opno) == true) {
		if($ord['stat'] > 3) {
			msg('배송 후 또는 취소상태일 경우 상품을 추가하실수 없습니다.', 'close');
		}
		$ex_title = '추가';
		$preload_products = '<tr class="prd_blank"><td colspan="10">추가할 상품을 선택해 주세요.</td></tr>';
	}

	if($prd['account_idx'] > 0) {
		msg("정산등록 된 상품입니다.\\n교환처리가 불가능하며 반품 후 재주문으로 처리해 주시기 바랍니다.", 'close');
	}

	// 입점사 개별 배송처리
	if((int)$_GET['partner_no'] > 0) $prd['partner_no'] = (int)$_GET['partner_no'];
	$partner_exchange_qry = $partner_exchange_param = '';
	$order_dlv_prc = $ord['dlv_prc'];
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
		$partner_exchange = ($prd['partner_no'] > 0) ? $prd['partner_no'] : $admin['partner_no'];
		if($partner_exchange == '') $partner_exchange = 0;
		$partner_exchange_param = '&partner_mode='.$partner_exchange;
		$partner_exchange_qry = " and partner_no='$partner_exchange'";
		setPartnerDlvConfig($partner_exchange);

		$order_dlv_prc = $pdo->row("select sum(dlv_prc) from {$tbl['order_dlv_prc']} where ono='$ono' and partner_no='{$prd['partner_no']}'");
	}

	// 동일상품 교환만 가능한 상태
	if(
        (isset($partner_exchange) == true && $prd['cpn_sale'] > 0) ||
        (isset($prd['set_idx']) == true && $prd['set_idx'] != '')
    ) {
		$exchange_same_prd_only = true;
	}

	$pmile_per = $mmile_per = 0;
	if($cfg['milage_use'] == '1') {
		$pmile_per = ($ord['member_no'] > 0 && $cfg['milage_type_per'] > 0) ? $cfg['milage_type_per'] : 0;
		if($ord['member_no'] > 0) {
			$amember = $pdo->assoc("select milage, emoney, level from $tbl[member] where no='$ord[member_no]'");
			$mmile_per = getMemberMilagePer($amember['level']);
		}
	}
	$nopay = $pay = 0;

	$mall_banks = array();
	$res2 = $pdo->iterator("select * from $tbl[bank_account] where type='' order by sort asc");
    foreach ($res2 as $bank) {
		$mall_banks[$bank['no']] .= stripslashes(trim($bank['bank'].' '.$bank['account'].' '.$bank['owner']));
	}

	$cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$ono'");

	$reasons = '';
	$rres = $pdo->iterator("select reason from $tbl[claim_reasons] order by sort asc");
    foreach ($rres as $rdata) {
		$rdata['reason'] = stripslashes($rdata['reason']);
		$reasons .= "<option>$rdata[reason]</option>";
	}

	include 'order_exchg.exe.php';

	//if($expay > 0) msg('교환 상품을 다시 교환하실수 없습니다.\n환불처리 후 상품추가를 이용해 주시기 바랍니다.', 'close');
	if(count($all_stats) > 1) msg('교환 시 상태가 서로 다른 주문은 같이 교환하실 수 없습니다.', 'close');
	if($pay > 0 && $nopay > 0) msg('입금 전 상품과 입금 된 상품은 같이 교환하실 수 없습니다.', 'close');

	if($ex_title == '추가') {
		$title = '주문상품 추가';
		$end_stat = 2;
		$pay = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and stat between 2 and 5 $partner_exchange_qry");
        if ($order_dlv_prc > 0) $exc_mode = -1;
	} elseif($nopay > 0) {
		$title = '입금 전 주문상품 교환';
		$end_stat = 1;
		$exc_mode = 1;
	} elseif($all_stats[2] > 0 || $all_stats[3] > 0) {
		$title = '배송 전 주문상품 교환';
		$end_stat = 3;
		$exc_mode = 2;
	} else {
		$title = '배송 후 주문상품 교환';
		$end_stat = 3;
		$exc_mode = 3;
	}

	$cpn = $pdo->assoc("select no, name, cno, sale_prc, sale_type from $tbl[coupon_download] where no='$cpn_no'");
	$cpn['name'] = stripslashes($cpn['name']);

	$sale_fd = getOrderSalesField('', '-');
	$total_add_dlv_prc = $pdo->row("select sum(add_dlv_prc) from $tbl[order_payment] where ono='$ono'");
	$ori_prd_prc = $pdo->row("select sum(total_prc-$sale_fd) from $tbl[order_product] where ono='$ono' and stat between 1 and 6 $partner_exchange_qry");
	if($prd_dlv_prc > 0) { // 개별 배송 정책 상품이 있을 경우
		$ori_prd_prc += $prd_dlv_prc; // 총 상품금액에 개별 배송비 추가
	}
	if(empty($ori_prd_prc) == true) $ori_prd_prc = 0;
	if($prd_dlv_prc > 0) { // 개별 배송 정책 상품이 있을 경우 교환 배송비 변경
		$cfg['delivery_fee'] = $prd_dlv_prc;
		$ex_dlv_type[2] = 'checked';
		$ex_dlv_prc = $prd_dlv_prc;
		$exchange_same_prd_only = true;
	} else {
		$ex_dlv_type[3] = 'checked';
		$ex_dlv_prc = ($cfg['delivery_fee']*2);
	}

	if(!$cfg['delivery_free_limit']) $cfg['delivery_free_limit'] = 0;

	if($order_dlv_prc+$total_add_dlv_prc > 0) {
		$add_dlv_msg = '총 주문상품금액이 '.number_format($cfg['delivery_free_limit']).' 원 이상이 될 경우 차감합니다.';
		$add_dlv_mode = '-';
	} else {
		$add_dlv_msg = '총 주문상품금액이 '.number_format($cfg['delivery_free_limit']).' 원 미만이 될 경우 추가합니다.';
		$add_dlv_mode = '+';
	}

	$bank_codes['gsshop'] = 'GS SHOP';
	$bank_codes['ssgmall'] = '신세계몰';
	$bank_codes['storefarm'] = '스토어팜';
	$bank_codes['naverpay'] = '네이버페이';
	if(isTable($tbl[bank_customer]) == true) {
		foreach($bank_codes as $key=>$val) {
			$chk_no = $pdo->row("select no from $tbl[bank_customer] where code='$key'");
			if($chk_no) {
				$pdo->query("delete from $tbl[bank_customer] where no='$chk_no'");
			}
		}
		$res = $pdo->iterator("select * from $tbl[bank_customer] order by no");
        foreach ($res as $data) {
			$bank_codes[$data['code']] = $data['bank'];
		}
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/order.js?<?=date('YmdHi')?>"></script>
<form id="ordFrm" name="adminOrdFrm" method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="return ordCheck(this)" style="min-width:1000px; min-height:600px;">
	<input type="hidden" name="parent" value="<?=$ono?>">
	<input type="hidden" name="opno" value="<?=$opno?>">
	<input type="hidden" name="body" value="order@order_exchg.exe">
	<input type="hidden" name="ex_prd_prc" value="">
	<input type="hidden" name="add_dlv_mode" value="<?=$add_dlv_mode?>">
	<input type="hidden" name="exchange_same_prd_only" value="<?=$exchange_same_prd_only?>">
	<div class="box_title first">
		<h2 class="title">상품정보</h2>
		<?if(isset($exchange_same_prd_only) == false) {?>
		<div class="btns">
			<?if(empty($opno) == false) {?>
			<span class="box_btn blue"><input type="button" onclick="psearch.open()" value="+ 교환 상품추가"></span>
			<span class="box_btn blue"><input type="button" onclick="getEqual()" value="+ 동일 상품추가"></span>
			<?} else {?>
			<span class="box_btn blue"><input type="button" onclick="psearch.open()" value="+ 신규 상품추가"></span>
			<?}?>
		</div>
		<?}?>
	</div>
	<table class="tbl_col">
		<caption class="hidden">상품정보</caption>
		<colgroup>
			<col>
			<col style="width:160px">
			<col style="width:100px">
			<col style="width:70px">
			<col style="width:140px">
			<?if($cfg['use_prd_dlvprc'] == 'Y') {?>
			<col style="width:100px">
			<?}?>
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">상품명</th>
				<th scope="col">옵션</th>
				<th scope="col">판매가</th>
				<th scope="col">수량</th>
				<th scope="col">할인</th>
				<?if($cfg['use_prd_dlvprc'] == 'Y') {?>
				<th scope="col">개별 배송비</th>
				<?}?>
				<th scope="col">실결제금액</th>
				<th scope="col">상품적립금</th>
				<th scope="col">회원적립금</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody id="ord_prd">
			<?=$preload_products?>
		</tbody>
	</table>

	<div class="box_title">
		<h2 class="title"><?=$ex_title?> 금액 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden"><?=$ex_title?> 금액 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?if(empty($opno) == false) {?>
			<tr>
				<th scope="row"><?=$ex_title?> 전 상품금액</th>
				<td><?=number_format($prd_prc)?> 원</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row"><?=$ex_title?> 후 상품금액</th>
				<td class="p_color2"><strong id="prd_prc"><?=number_format($prd_prc)?></strong> 원</td>
			</tr>
			<?if($exc_mode == 3) {?>
			<tr>
				<th scope="row">교환 배송비</th>
				<td>
					<label class="p_cursor"><input type="radio" name="ex_dlv_type" value="1" onclick="setExDlv(0);"> 없음</label>
					<label class="p_cursor"><input type="radio" name="ex_dlv_type" value="2" onclick="setExDlv(<?=$cfg['delivery_fee']?>);" <?=$ex_dlv_type[2]?>> 편도</label>
					<label class="p_cursor"><input type="radio" name="ex_dlv_type" value="3" onclick="setExDlv(<?=$cfg['delivery_fee']*2?>);" <?=$ex_dlv_type[3]?>> 왕복</label>
					<input type="text" name="ex_dlv_prc" value="<?=$ex_dlv_prc?>" class="input right autorepayprc" size="10"> 원
					<span class="explain">(주문배송비에 합산됩니다.)</span>
				</td>
			</tr>
			<?} else {?>
			<tr>
				<th scope="row">추가 배송비</th>
				<td>
					<label class="p_cursor"><input type="radio" name="ex_dlv_type" value="1" onclick="setExDlv(0);" checked> 없음</label>
					<label class="p_cursor"><input type="radio" name="ex_dlv_type" value="2" onclick="setExDlv(<?=$cfg['delivery_fee']?>);"> 추가</label>
					<input type="text" name="ex_dlv_prc" value="0" class="input right autorepayprc" size="10"> 원
				</td>
			</tr>
			<?}?>
			<?if($exc_mode < 3 && ($non_prd_dlv > 0 || $exc_mode == -1)) {?>
			<tr>
				<th scope="row">주문상품금액<br>배송비</th>
				<td>
					<label class="p_cursor"><input type="radio" name="add_dlv_type" class="autorepayprc" value="1" checked> 변경하지 않습니다.</label>
					<label class="p_cursor"><input type="radio" name="add_dlv_type" class="autorepayprc" value="2"> <?=$add_dlv_msg?></label>
					<p class="list_msg">(<span class="p_color"><?=$title?></span> 후 총 일반 상품 금액 : <strong class="new_prd_prc"></strong> 원)</p>
					<input type="text" name="add_dlv_prc" class="input autorepayprc" size="12" value="<?=$cfg['delivery_fee']?>">
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">예치금 사용</th>
				<td><input type="text" name="emoney_prc" value="0" class="input autorepayprc" size="12"> (보유 예치금 : <?=number_format($amember['emoney'])?> 원)</td>
			</tr>
			<tr>
				<th scope="row">적립금 사용</th>
				<td><input type="text" name="milage_prc" value="0" class="input autorepayprc" size="12"> (보유 적립금: <?=number_format($amember['milage'])?> 원)</td>
			</tr>
			<tr>
				<th scope="row" rowspan="2">총 결제 차액</th>
				<td>
					<strong class="repay_calc p_color2">0</strong> 원
					(
						상품차액 <span class="repay_prc1"></span>
						- 교환배송비 <span class="repay_prc2"></span>
						<?=$add_dlv_mode?> 상품배송비 <span class="repay_prc3"></span>
						<!--
						- 쿠폰승계 <span class="cpn_prce"></span>
						-->
						- 예치금사용 <span class="repay_prce"></span>
						- 적립금사용 <span class="repay_prcm"></span>
					)
				</td>
			</tr>
			<tr>
				<td>
					<span class="p_color2">최종 차액</span> <input type="text" class="repay_calc input right" name="total_repay_prc" size="10" onchange="changeExcMode()"> 원 /
					<?if($nopay > 0) {?>
					미입금주문의 총 주문금액이 변경됩니다.
					<?} else {?>
					<span class="repay_mode">고객환불(-)</span>, <span class="input_mode">추가입금(+)</span>
					<?}?>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="box_title">
		<h2 class="title">공통 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">공통 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">주문상태</th>
				<td>
					<select name="stat">
						<?for($i = 1; $i <= $end_stat; $i++) {?>
						<option value="<?=$i?>"><?=$_order_stat[$i]?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?if($cpn['no'] > 0) {?>
			<tr>
				<th scope="row">쿠폰</th>
				<td>
					<table class="tbl_inner full line">
						<colgroup>
							<col>
							<col style="width:80px;">
						</colgroup>
						<thead>
							<tr>
								<th class="left"><?=$cpn['name']?></th>
								<th><span class="box_btn_s blue"><a href="#" onclick="cpndetail.open('no=<?=$cpn['cno']?>&readOnly=true'); return false;">상세정보</a></span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="left"colspan="2"><label class="p_cursor"><input type="radio" name="cpn_repay" value="N" checked onclick="getPrd_prc()"> 변경없음</label></td>
							</tr>
							<tr>
								<td class="left" colspan="2">
									<label class="p_cursor"><input type="radio" name="cpn_repay" value="Y" class="autorepayprc" onclick="getPrd_prc()"> 사용한 쿠폰을 고객에게 반환합니다.</label>
									<div class="list_info tp">
										<p>적용된 쿠폰할인가는 자동 반영되지 않습니다.</p>
									</div>
								</td>
							</tr>
							<!--
							<tr>
								<td class="left">
									<input type="hidden" name="cpn_sale_type" value="<?=$cpn['sale_type']?>">
									<?if($cpn['sale_type'] == 'p') {?>
									<label class="p_cursor"><input type="radio" name="cpn_repay" value="<?=$cpn['sale_prc']?>" onclick="getPrd_prc()"> 쿠폰할인 <strong class="p_color2"><?=$cpn['sale_prc']?>%</strong>를 적용합니다.</label>
									<span class="add_cpn_sale"></span>
									<?}?>
									<?if($cpn['sale_type'] == 'm') {?>
									<label class="p_cursor"><input type="radio" name="cpn_repay" value="<?=$sale5?>" onclick="getPrd_prc()"> 쿠폰할인 <strong class="p_color2"><?=number_format($sale5)?>원</strong>을 적용합니다.</label>
									<?}?>
								</td>
							</tr>
							-->
						</tbody>
					</table>
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row"><?=$ex_title?>사유</th>
				<td>
					<select name="reason">
						<option value="">:: 사유를 선택해주세요 ::</option>
						<?=$reasons?>
					</select>
					<label class="p_cursor"><input type="checkbox" name="copytomemo" value="Y"> 입력한 상세 사유를 메모에도 등록</label>
					<div style="margin-top:5px;"><textarea class="txta" name="comment" cols="80" rows="5"></textarea></div>
				</td>
			</tr>
		</tbody>
	</table>

	<?if($pay > 0) {?>
	<div class="box_title repayProcess">
		<h2 class="title">환불 정보</h2>
	</div>
	<table class="tbl_row repayProcess">
		<caption class="hidden">환불 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row" rowspan="3">환불방법</th>
				<td>
					<ul class="list_info">
						<li>환불방법은 예치금, 적립금, 무통장 입금, 신용카드(간편결제), 휴대폰 중 선택할 수 있습니다.</li>
						<li>신용카드(간편결제), 휴대폰 환불 시 카드 정보 내 'PG사 결제 환불' 버튼을 통해 환불을 진행해주시기 바랍니다.</li>
						<li>휴대폰결제는 부분환불을 지원하지 않습니다.</li>
						<li>무통장 입금 환불 시 직접 고객 은행정보로 환불을 진행해주셔야 합니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
                    <?php
                    if ($ord['member_no'] > 0) {
                        if ($scfg->comp('emoney_use', 'Y')) { // 예치금 사용일 때만 예치금으로 환불 가능하도록
                            echo '<label class="p_cursor"><input type="radio" name="pay_type" value="6" class="repay_pay_method" onchange="showRepayMethod()"> '.$_pay_type[6].'</label>';
                        }
                        if ($scfg->comp('milage_use', '1')) { // 적립금 사용일 때만 적립금으로 환불 가능하도록
                            echo '<label class="p_cursor"><input type="radio" name="pay_type" value="3" class="repay_pay_method" onchange="showRepayMethod()"> '.$_pay_type[3].'</label>';
                        }
                    }
                    ?>
					<label class="p_cursor p_color"><input type="radio" name="pay_type" value="2" class="repay_pay_method" onchange="showRepayMethod()"> <?=$_pay_type[2]?></label>
					<label class="p_cursor p_color4"><input type="radio" name="pay_type" value="1" class="repay_pay_method" onchange="showRepayMethod()"> <?=$_pay_type[1]?>(간편결제)</label>
					<label class="p_cursor p_color4"><input type="radio" name="pay_type" value="7" class="repay_pay_method" onchange="showRepayMethod()"> <?=$_pay_type[7]?></label>
				</td>
			</tr>
			<tr>
				<td>
					<div class="repay_method bank">
						<span class="box_btn_s blue"><input type="button" value="입금은행 추가" onclick="add_banks(); return false;"></span>
						<?=selectArray($bank_codes, 'bank', 1, '::입금은행 선택::')?>
						<input type="text" name="bank_account" class="input" size="20" placeholder="계좌번호">
						<input type="text" name="bank_name" class="input" size="10" placeholder="예금주"><br>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="box_title inputProcess">
		<h2 class="title">추가입금 정보</h2>
	</div>
	<table class="tbl_row inputProcess">
		<caption class="hidden">추가입금 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">입금방법</th>
				<td>
					<select name="input_pay_type" class="input_pay_method" onchange="showInputMethod(this)">
						<option value="2"><?=$_pay_type[2]?></option>
						<option value="0">개인결제창</option>
					</select>
				</td>
			</tr>
			<tr class="input_method bank">
				<th scope="row">입금계좌</th>
				<td><?=selectArray($mall_banks, 'input_bank', 0)?></td>
			</tr>
		</tbody>
	</table>
	<?}?>
	<div class="box_bottom">
		<span class="box_btn blue" id="stpBtn"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close();"></span>
	</div>
</form>

<style type="text/css">
tr.disable {
	background: #fff9f9;
	opacity: .7;
}

.p_color2.strong {
	font-weight: bold;
}
</style>

<script type="text/javascript">
	var f = $('#ordFrm')[0];

	// 상품 검색
	var psearch = new layerWindow('product@product_inc.exe&type=add&stat[]=2&stat[]=4&search_no=<?=$search_no?>&<?=$partner_exchange_param?>');
	psearch.psel = order_prd_add_func;
    psearch.ono = '<?=$ono?>';

	$(window).bind({
		'keydown': function(e) {
			if(e.ctrlKey == true) $('.popupContent').find(':button[value=선택]').val('계속선택');
		},
		'keyup': function(e) {
			if(e.ctrlKey == false) $('.popupContent').find(':button[value=계속선택]').val('선택');
		}
	});

	// 교환할 상품 삭제
	function pdel(obj) {
		while(obj[0].tagName != 'TR') {
			obj = obj.parent();
		}
		obj.remove();
		getPrd_prc();
	}

	// 상품 금액 계산
	// 상품 금액 계산
	var exc_origin_prc = '<?=$prd_prc?>'; // 교환 전 상품 총액 (할인 전)
	var exc_new_prc = 0; // 교환 후 상품 총액
	var exc_exception_prc = 0; // 개별 배송 상품 금액
	var pmile_per = <?=$pmile_per?>; // 상품 적립금 적립율
	var mmile_per = <?=$mmile_per?>; // 회원 적립금 적립율
	var milage_type = '<?=$cfg['milage_type']?>'; // 상품 적립금 계산 방법
	var msale_mile_type = '<?=$cfg['msale_mile_type']?>'; // 회원적립금 상품금액/실결제 금액 기준
	var member_milage_type = "<?=$cfg['member_milage_type']?>"; // 회원 적립금 계산 방법(병합, 치환)
	var msale_round = '<?=$cfg['msale_round']?>';
	var ori_prd_prc = <?=$ori_prd_prc?>; // 교환 전 상품 총액 (할인 후)
	var add_dlv_mode = '<?=$add_dlv_mode?>'; // 금액별 무료배송 전환 상태
	var delivery_free_limit = <?=$cfg['delivery_free_limit']?>; // 금액별 무료배송 기준 '할인 후' 상품 금액

	$('#mngTab_pop').html('<?=$title?>');

	$(document).ready(function() {
		getPrd_prc();
		<?php if (isset($exchange_same_prd_only) == true || defined('__SET_EXCHANGE__') == true) { ?>
		getEqual(<?=$exchange_same_prd_only?>);
		<?php } ?>
	});

	parent.autoRepayPrc();
	parent.$('.autorepayprc').bind({
		'focus' : function() { parent.autoRepayPrc(); },
		'keyup' : function() { parent.autoRepayPrc(); },
		'change' : function() { parent.autoRepayPrc(); }
	});

	parent.$('.repay_calc').change(function() {
		parent.showRepayMethod();
	});

	// 총 교환 차액 자동 계산
	function autoRepayPrc() {
		var prc = 0;
		var prc1 = prc2 = prc3 = prcm = prce = 0;

		// 상품별 차액
		prc1 = exc_new_prc-exc_origin_prc;

		if($('.autorepayprc[name=ex_dlv_prc]').length == 1) {
			prc2 += $('.autorepayprc[name=ex_dlv_prc]').val().toNumber();
		}

		$('.new_prd_prc').html(setComma((ori_prd_prc+prc1-exc_exception_prc).toFixed(currency_decimal)));

		// 주문상품 금액별 배송비
		$('.autorepayprc[name=add_dlv_prc]').attr('disabled', true);
		if(add_dlv_mode == '-' && (ori_prd_prc+prc1-exc_exception_prc) >= delivery_free_limit) { // 차감
			$('.autorepayprc[name=add_dlv_type]').attr('disabled', false);
			if($(':checked.autorepayprc[name=add_dlv_type]').val() == '2') {
				$('.autorepayprc[name=add_dlv_prc]').attr('disabled', false);
				prc3 = $('.autorepayprc[name=add_dlv_prc]').val().toNumber()*-1;
			}
		}
		if(add_dlv_mode == '+' && (ori_prd_prc+prc1-exc_exception_prc) < delivery_free_limit) { // 추가
			$('.autorepayprc[name=add_dlv_type]').attr('disabled', false);
			if($(':checked.autorepayprc[name=add_dlv_type]').val() == '2') {
				$('.autorepayprc[name=add_dlv_prc]').attr('disabled', false);
				prc3 = $('.autorepayprc[name=add_dlv_prc]').val().toNumber();
			}
		}

		if($('.autorepayprc[name=emoney_prc], .autorepayprc[name=emoney_repay]').length == 1) {
			prce = $('.autorepayprc[name=emoney_prc], .autorepayprc[name=emoney_repay]').val().toNumber();
		}

		if($('.autorepayprc[name=milage_prc], .autorepayprc[name=milage_repay]').length == 1) {
			prcm = $('.autorepayprc[name=milage_prc], .autorepayprc[name=milage_repay]').val().toNumber();
		}

		prc = prc1+prc2+prc3-prce-prcm;
		$('.repay_calc').val(setComma(prc.toFixed(currency_decimal)));
		$('.repay_calc').html(setComma(prc.toFixed(currency_decimal)));
		$('.repay_prc1').html(setComma(prc1.toFixed(currency_decimal)));
		$('.repay_prc2').html(setComma(prc2.toFixed(currency_decimal)));
		$('.repay_prc3').html(setComma(prc3.toFixed(currency_decimal)));
		$('.repay_prce').html(setComma(prce.toFixed(currency_decimal)));
		$('.repay_prcm').html(setComma(prcm.toFixed(currency_decimal)));

		if(prc > 0) {
			$('select[name=stat]').val(1);
		} else {
			$('select[name=stat][value=2]').prop('checked', true);
		}

		if(typeof changeExcMode != 'undefined') changeExcMode();

		showRepayMethod();
	}

	// 교환환불, 교환추가입금 여부에 따른 처리
	function changeExcMode() {
		var prc = $('input[name=total_repay_prc]').val().toNumber();
		$('.inputProcess, .repayProcess').hide();
		if(prc > 0) {
			$('.repay_mode').removeClass('p_color2').removeClass('strong');
			$('.input_mode').addClass('p_color2').addClass('strong');

			$('.inputProcess').show();
		} else if(prc < 0) {
			$('.repay_mode').addClass('p_color2').addClass('strong')
			$('.input_mode').removeClass('p_color2').removeClass('strong');

			$('.repayProcess').show();
		} else {
			$('.repay_mode').removeClass('p_color2').removeClass('strong')
			$('.input_mode').removeClass('p_color2').removeClass('strong');
		}
	}

	// 교환처리 최종 완료
	function ordCheck(f) {
		var msg = '<?=$ex_title?>처리를 완료하시겠습니까?';
		<?if($ord['member_no'] > 0) {?>
		var total_milage = 0;
		$('tr.add_products').not('.disable').find('.a_prd_milage, .a_mem_milage').each(function() {
			total_milage += parseInt(this.value);
		});
		if(total_milage == 0) {
			msg = '할인 및 적립금 등이 입력되지 않았습니다.\n'+msg;
		}
		<?}?>

		if(confirm(msg)) {
			printLoading();
			$('#stpBtn').hide();
			return true;
		}

		return false;
	}

	// 동일상품 추가
	function getEqual(exchange_same_prd_only) {
		if(!exchange_same_prd_only) exchange_same_prd_only = null;
		var multi = ($('input[name="m[]"]').length+1);
		$.get('./index.php', {'body':'order@order_exchg.exe', 'exec':'prd', 'mode':'getEqualProducts', 'opno':'<?=$opno?>', 'multi':multi, 'exchange_same_prd_only':exchange_same_prd_only}, function(r) {
			if($("<table>"+r+"</table>").find('.is_max_ord_mem').length > 0) {
				if(confirm('추가하신 상품 중\n회원주문한도 설정되어있는 상품이 포함되어있습니다.\n그래도 추가하시겠습니까?') == false) {
					return false;
				}
			}
			$('#ord_prd').append(r);

			getPrd_prc();
		});
	}

	// 입금은행 추가
	function add_banks() {
		setDimmed();
		ordbank.open();
	}

	// 사용 쿠폰 정보 보기
	var cpndetail = new layerWindow('member@able_cpn_detail.exe');
	// 입금은행 추가
	var ordbank = new layerWindow('order@order_bank.frm');
</script>