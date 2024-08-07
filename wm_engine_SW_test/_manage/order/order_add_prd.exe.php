<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품 추가
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/shop_detail.lib.php';

	$pno = $ext;
	$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$pno'");
	$prd = shortCut($prd);
	$prd['name'] = addslashes(stripslashes($prd['name']));
	$prd['thumb'] = getFileDir($prd['updir'])."/$prd[updir]/$prd[upfile3]";
	$prd['pno'] = $prd['no'];
	if($_POST['add_dlv_type'] == 2) {
		$repay_dlv_prc = ($_POST['add_dlv_prc']*-1);
	}

	// 입점사 개별 배송처리
	$partner_exchange = null;
	$partner_exchange_qry = '';
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
		$partner_exchange = $prd['partner_no'];
		$partner_exchange_qry = " and partner_no='$partner_exchange'";
		setPartnerDlvConfig($partner_exchange);
	}

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['stat'] > 5) msg('취소 상태에서는 상품 추가가 불가능 합니다.');

	if($prd['member_sale'] == 'Y' && $ord['member_no'] > 0) {
		$amember = $pdo->assoc("select no, milage, emoney, level from $tbl[member] where no='$ord[member_no]'");
		$mmile_per = getMemberMilagePer($amember['level']);
	}
	if(!$mmile_per) $mmile_per = 0;
	$pmile_per = ($ord['member_no'] > 0) ? $cfg['milage_type_per'] : 0;
	if(!$pmile_per) $pmile_per = 0;
	if($mmile_per > 0 && $cfg['member_milage_type'] == 1) $pmile_per = 0; // 회원 적립금 치환

	$reasons = '';
	$rres = $pdo->iterator("select reason from $tbl[claim_reasons] order by sort asc");
    foreach ($rres as $rdata) {
		$rdata['reason'] = stripslashes($rdata['reason']);
		$reasons .= "<option>$rdata[reason]</option>";
	}

	$mall_banks = array();
	$res2 = $pdo->iterator("select * from $tbl[bank_account] where type='' order by sort asc");
    foreach ($res2 as $bank) {
		$mall_banks[$bank['no']] .= stripslashes(trim($bank['bank'].' '.$bank['account'].' '.$bank['owner']));
	}

	$prd['member_milage']  = floor($prd['sell_prc'] * ($mmile_per/100));

	if($_POST['stat'] == '2') {

		// 기본 입력사항 체크
		$buy_ea = numberOnly($_POST['buy_ea']);
		$sell_prc = numberOnly($_POST['sell_prc'], true);
		$milage = numberOnly($_POST['milage'], true);
		$milage_prc = numberOnly($_POST['milage_prc'], true);
		$emoney_prc = numberOnly($_POST['emoney_prc'], true);
		$pay_type = numberOnly($_POST['pay_type']);
		$input_bank = numberOnly($_POST['input_bank']);
		$milage = numberOnly($_POST['milage'], true);
		$member_milage = numberOnly($_POST['member_milage'], true);

		if($buy_ea < 1) msg('판매 수량을 입력해 주십시오.');
		if($sell_prc < 0) msg('상품 금액을 입력해 주십시오.');
		//if(!$_POST['reason']) msg('사유를 선택해 주세요.');

		if($pay_type == 2) {
			if(!$input_bank) msg('입금 은행을 선택해 주세요.');
			$account = $pdo->assoc("select * from $tbl[bank_account] where no='$input_bank'");
			$bank = $account['bank'];
			$bank_account = $account['account'];
			$bank_name = $account['owner'];
		}

		$opt = prdCheckStock($prd, $buy_ea);

		$option			= $opt['option'];
		$option_prc		= $opt['option_prc'];
		$option_idx		= $opt['option_idx'];
		$complex_no		= $opt['complex_no'];

		$psql1 = $psql2 = '';
		$total_prc		= ($sell_prc * $buy_ea);
		foreach($_order_sales as $key => $val) {
			${$key} = $tmp = numberOnly($_POST[$key][0]);
			$total_sale_prc += $tmp;

			if($tmp != 0) {
				$asql .= ", `$key`=`$key`+'$tmp'";
				$psql1 .= ", `$key`";
				$psql2 .= ", '$tmp'";
			}
		}
		$_pay_prc		= ($sell_prc * $buy_ea) - $total_sale_prc;

		$total_member_milage = $member_milage * $buy_ea;
		$total_milage	= ($milage * $buy_ea)+$total_member_milage;
		$stat			= $_POST['add_stat'];

		// 적립금, 예치금으로 추가 결제
		if($ord['member_no'] < 1 && ($pay_type == 3 || $pay_type == 6)) {
			msg('비회원은 적립금/예치금으로 추가결제를 하실수 없습니다.');
		}
		if($ord['member_no'] > 0) {
			$amember = $pdo->assoc("select * from $tbl[member] where no='$ord[member_no]'");
			if($pay_type == 3 && $amember['milage'] < $_pay_prc) {
				msg("회원의 보유 적립금이 부족합니다.");
			}
			if($pay_type == 6 && $amember['emoney'] < $_pay_prc) {
				msg("회원의 보유 예치금이 부족합니다.");
			}
		}
		if($pay_type == 3) {
			$asql .= ", milage_prc=milage_prc+$_pay_prc, pay_prc=pay_prc-$_pay_prc";
		}
		if($pay_type == 6) {
			$asql .= ", emoney_prc=emoney_prc+$_pay_prc, pay_prc=pay_prc-$_pay_prc";
		}

		$pdo->query("update $tbl[order] set total_prc=total_prc+'$total_prc', prd_prc=prd_prc+$total_prc, total_milage=total_milage+$total_milage, member_milage=member_milage+'$total_member_milage', sale5=sale5+'$total_sale5' $asql where ono='$ono'");

		// 입점사 정산 정보
		if($cfg['use_partner_shop'] == 'Y') {
			if($prd['partner_rate'] > 0) $prd['fee_prc'] = getPercentage($total_prc, $prd['partner_rate']);
			$psql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
			$psql2 .= ", '$prd[partner_no]', '$prd[partner_rate]', '$prd[fee_prc]', '$prd[dlv_type]'";
		}

		$pdo->query("insert into $tbl[order_product] (ono, pno, prd_type, name, sell_prc, milage, member_milage, buy_ea, total_prc, total_milage, `option`, option_prc, complex_no, option_idx, stat, ex_pno $psql1) ".
				  " values ('$ono', '$prd[parent]', '$prd[prd_type]', '$prd[name]', '$sell_prc', '$milage', '$member_milage', '$buy_ea', '$total_prc', '$total_milage', '$option', '$option_prc', '$complex_no', '$option_idx', '1', 'add' $psql2)");
		$no = $pdo->row("select max(no) from $tbl[order_product] where ono='$ono'");

		// 윙포스 재고변경
		include_once $engine_dir.'/_engine/include/wingPos.lib.php';
		orderStock($ono, 0.99, $stat, $no);
		$pdo->query("update $tbl[order_product] set stat='$stat' where no='$no'");

		// 로깅
		ordStatLogw($ono, '70', '', $no);

		if($memo) {
			$memo = addslashes(trim($memo));
			$pdo->query("insert into $tbl[order_memo] (admin_no, admin_id, ono, content, type, reg_date) values ('$admin[no]', '$admin[admin_id]', '$ono', '$memo', '1', '$now')");
		}

		$type = null;
		if($pay_type == 3) {
			$milage_prc = $_pay_prc;
			$total_prc = 0;
			$type = 2;
			ctrlMilage("-", 0, $milage_prc, $amember, "[$ono] 상품 추가로 인한 적립금 사용");
		}
		if($pay_type == 6) {
			$emoney_prc = $_pay_prc;
			$total_prc = 0;
			$type = 2;
			ctrlEmoney("-", 0, $emoney_prc, $amember, "[$ono] 상품 추가로 인한 예치금 사용");
		}

		createPayment(array(
			'ono' => $ono,
			'pno' => array($no),
			'pay_type' => $_POST['pay_type'],
			'amount' => ($total_prc-$total_sale_prc)+$repay_dlv_prc,
			'milage_prc' => $milage_prc,
			'emoney_prc' => $emoney_prc,
			'add_dlv_prc' => $repay_dlv_prc,
			'reason' => $_POST['reason'],
			'comment' => $_POST['comment'],
			'bank' => $bank,
			'bank_account' => $bank_account,
			'bank_name' => $bank_name,
			'copytomemo' => $_POST['copytomemo']
		));

		if(isset($partner_exchange) == true) { // 입점사 배송비 정산 테이블 갱신
			$change_dlv_prc = $repay_dlv_prc;
			if($change_dlv_prc != 0) {
				$pdo->query("update $tbl[order_dlv_prc] set dlv_prc=dlv_prc+$change_dlv_prc where ono='$ono' and partner_no='$partner_exchange'");
			}
		}

		ordChgPart($ono);

		// 배송완료에서 상품 추가시 적립금 처리
		$data = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		if($ord['stat'] == 5 && $data['stat'] != 5) {
			$ext = $data['stat'];
			$asql = '';
			orderMilageChg($pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ono' and stat=5"));
			if($asql) $pdo->query("update $tbl[order] set stat='$ext' $asql where ono='$ono'");
			reloadOrderMilage($ono);
		}

		// 현금영수증 재계산
		chgCashReceipt($ono);

        $order_title = makeOrderTitle($ono);
		$pdo->query("update {$tbl['order']} set title='$order_title' where ono='$ono'");

		if($ord['member_no'] > 0) {
			setMemOrd($ord['member_no'], 1);
		}

		makeOrderLog($ono, "order_add_prd.exe.php");

		msg("상품 추가가 완료되었습니다.", "reload" ,"parent");
		return;
	}

	$_prd_stat = array('2' => '정상', '3' => '품절', '4' => '숨김');

	ob_start();

	// 추가배송비 취소 상황
	if(isset($partner_exchange) == true) {
		$sale_fd = getOrderSalesField('', '-');
		$new_base_prc = $pdo->row("select sum(total_prc-$sale_fd) from $tbl[order_product] where ono='$ono' and stat between 1 and 6 $partner_exchange_qry");
	} else {
		$new_base_prc = $ord['prd_prc'];
	}
	$new_base_prc += $prd['sell_prc'];
	$add_dlv_prc = $pdo->row("select sum(add_dlv_prc) from $tbl[order_payment] where ono='$ono'");
	if($ord['dlv_prc']+$add_dlv_prc > 0 && $cfg['delivery_free_limit'] <= $new_base_prc) {
		$free_deilvery = true;
	}

	// 쿠폰승계 가능 체크
	$cpn = $pdo->assoc("select * from $tbl[coupon_download] where ono='$ono' and sale_type='P'");
	if($cpn['sale_prc'] > 0) {
		include_once $engine_dir.'/_engine/include/shop2.lib.php';
		$cdata = $pdo->assoc("select * from $tbl[coupon] where no='$cpn[cno]'");
		if(isCpnAttached($cdata, $prd) == false) {
			$cpn_disabled = true;
		}
	}

?>
<table class="tbl_row">
	<caption>새로운 상품을 주문서에 추가합니다.</caption>
	<colgroup>
		<col style="width:180px">
		<col>
	</colgroup>
	<tr>
		<th scope="row">주문상태</th>
		<td>
			<select name="add_stat">
			<?for($i= 1; $i <= 3; $i++) {?>
				<option value="<?=$i?>" <?=$checked?>><?=$_order_stat[$i]?></option>
			<?}?>
			</select>
			<ul class="list_msg">
				<li>무통장 미입금 상태에서 상품을 추가할 경우 변경된 총 결제금액이 입금되어야 자동입금처리가 가능합니다.</li>
				<li>카드 결제 주문일 경우 통계상으로 추가분의 상품 금액은 카드결제에의한 매출로 표시되며, 에스크로 계좌이체도 동일하게 처리됩니다.</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th scope="row">상품명</th>
		<td>
			<div class="box_setup">
				<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><img src="<?=$prd['thumb']?>" width="50px;"></a></div>
				<dl>
					<dt class="title"><a href="?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><?=$prd['name']?></a></dt>
					<dd class="cstr"><?=$prd['origin_name']?></dd>
				</dl>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">옵션</th>
		<td>
			<ul>
			<?
				$prdOptionNoTR = true;
				while($opt = prdOptionList()) {
					echo "<li><strong>$opt[name]</strong> : $opt[option_str]</li>";
				}
			?>
			</ul>
		</td>
	</tr>
	<tr id="ord_prd">
		<th scope="row">판매가격</th>
		<td>
			<input type="text" name="sell_prc" class="input right add_sell_prc" value="<?=$prd['sell_prc']?>" onkeyup="setAddPrdMilage(this)">
			<input type="hidden" name="sell_prc_org" class="input" value="<?=$prd['sell_prc']?>">
			<span class="box_btn_s blue"><input type="button" value="+ 할인추가" onclick="setAddProductSale(this)"></span>

			<?foreach($_order_sales as $fn => $fv) {?>
			<dl class="admin_order_<?=$fn?> hidden">
				<dt class="p_color explain">- <?=$fv?></dt>
				<dd><input type="text" name="<?=$fn?>[]" class="input right saleobj" size="7" value="0"></dd>
			</dl>
			<?}?>
		</td>
	</tr>
	<tr>
		<th scope="row">판매수량</th>
		<td><input type="text" name="buy_ea" class="input" value="<?=$prd['min_ord']?>" size="4"></td>
	</tr>
	<?if($cpn['sale_prc'] > 0) {?>
	<tr>
		<th scope="row">쿠폰 적용</th>
		<td>
			<?=stripslashes($cpn['name'])?> <a href="#" onclick="cpndetail.open("no=<?=$cpn['cno']?>"); return false;" class="sclink blank">쿠폰정보</a>
			<?if($cpn_disabled != true) {?>
			<div>
				<label class="p_cursor"><input type="checkbox" name="use_cpn_calc" value="<?=$cpn['sale_prc']?>" onclick="setAddPrdMilage(this);"> 쿠폰할인 <strong class="p_color"><?=$cpn['sale_prc']?>%</strong>를 적용합니다.</label>
				<span class="add_cpn_sale"></span>
			</div>
			<?} else {?>
			<div class="explain">쿠폰 적용이 불가능한 카테고리의 상품입니다.</div>
			<?}?>
		</td>
	</tr>
	<?}?>
	<tr>
		<th scope="row">상품적립금 지급</th>
		<td><input type="text" name="milage" class="input" value="<?=parsePrice($prd['milage'])?>"></td>
	</tr>
	<?if($prd['member_sale'] == 'Y') {?>
	<tr>
		<th scope="row">회원적립금 지급</th>
		<td><input type="text" name="member_milage" class="input" value="<?=$prd['member_milage']?>"></td>
	</tr>
	<?}?>
	<?if($free_deilvery) {?>
	<tr>
		<th scope="row">무료배송</th>
		<td>
			<ul class="list_common">
				<li class="p_color">상품 추가로 인해 무료배송이 가능한 주문서입니다.</li>
				<li><label class="p_cursor"><input type="radio" name="add_dlv_type" value="1" checked> 처리하지 않습니다..</label></li>
				<li>
					<label class="p_cursor"><input type="radio" name="add_dlv_type" value="2" class="autorepayprc"> 결제금액에서 차감합니다.</label><br>
					<input type="text" name="add_dlv_prc" value="<?=$cfg['delivery_fee']?>" class="input right" size="10"> 원
				</li>
			</ul>
		</td>
	</tr>
	<?}?>
	<tr>
		<th scope="row">상품추가 사유</th>
		<td>
			<select name="reason">
				<option value="">:: 사유를 선택해주세요 ::</option>
				<?=$reasons?>
			</select>
			<label class="p_cursor"><input type="checkbox" name="copytomemo" value="Y"> 입력한 상세 사유를 메모에도 등록</label>
			<div style="margin-top: 5px;"><textarea class="txta" name="comment" cols="80" rows="5"></textarea></div>
		</td>
	</tr>
	<tr>
		<th scope="row">결제방법</th>
		<td>
			<select name="pay_type" class="input_pay_method" onchange="showInputMethod(this)">
				<option value="6"><?=$_pay_type[6]?></option>
				<option value="3"><?=$_pay_type[3]?></option>
				<option value="2"><?=$_pay_type[2]?></option>
			</select>
			<?if($amember['no'] > 0) {?>
			잔여 예치금 <strong class="p_color3"><?=number_format($amember['emoney'])?></strong> 원 /
			잔여 적립금 <strong class="p_color4"><?=number_format($amember['milage'])?></strong> 원
			<?}?>
		</td>
	</tr>
	<tr class="input_method bank" style="display:none;">
		<th scope="row">입금계좌</th>
		<td><?=selectArray($mall_banks, 'input_bank', 0, ':: 입금은행 선택 ::')?></td>
	</tr>
</table>
<div class="box_bottom">
	<span class="box_btn blue"><input type="button" id="stpBtn" value="추가" onclick="ordExecFromPrdbox('prdAdd', 2);"></span>
	<span class="box_btn gray"><input type="button" value="닫기" onclick="layTgl(repayDetail);"></span>
</div>
<?

	$script = php2java(ob_get_contents());
	ob_end_clean();

?>
<script type="text/javascript">
	parent.mmile_per = <?=$mmile_per?>;
	parent.pmile_per = <?=$pmile_per?>;
	parent.milage_type = '<?=$cfg['milage_type']?>';
	var obj = parent.document.getElementById("repayDetail");
	if(obj){
		obj.innerHTML="<?=$script?>";
		obj.style.display="block";
	} else {
		window.alert('상품 추가는 부분취소/환불 기능이 활성화 되어있어야 사용가능합니다.\n해당 설정은 쇼핑몰관리-운영정보-주문설정에서 가능합니다.');
	}

	parent.setAddPrdMilage(parent.$('.add_sell_prc', document.parent)[0]);

	parent.$('.saleobj', document.parent).change(function() {
		parent.setAddPrdMilage(parent.$('.add_sell_prc', document.parent)[0]);
	});

	parent.initOptionPrc(); // 옵션 추가가격 초기화
</script>