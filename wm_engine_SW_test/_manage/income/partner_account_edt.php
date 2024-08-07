<?PHP

	function parseStat($res) {
		global $_prev_ono, $sum;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data = array_map('stripslashes', $data);

		$data['fee_prc'] = abs($data['fee_prc']);
		$data['pay_prc'] = $data['total_prc']-$data['sale_prc']-$data['sale5'];
		$data['account_prc'] = $data['total_prc']-$data['cpn_fee']-$data['fee_prc'];

		if($data['stat'] > 10) {
			$data['account_prc'] *= -1;
		}
		$data['op_stat'] = ($data['stat'] > 10) ? '2' : '1';
		$data['cpn_rate'] = ($data['cpn_fee'] > 0) ? floor(($data['cpn_fee']/$data['sale5'])*100) : 0;

		$sum['buy_ea'] += $data['buy_ea'];
		$sum['total_prc'] += $data['total_prc'];
		$sum['fee_prc'] += $data['fee_prc'];
		$sum['cpn_fee'] += $data['cpn_fee'];
		$sum['pay_prc'] += $data['pay_prc'];
		$sum['sale_prc'] += $data['sale_prc'];
		$sum['sale5'] += ($data['sale5']);
		$sum['account_prc'] += $data['account_prc'];
		$sum['dlv_prc'] += $data['dlv_prc'];

		if($_prev_ono != $data['ono']) {
			$sum['pay_prc'] += $data['dlv_prc'];
		}

		return $data;
	}

	// 정산 대상 검색
	include 'partner_account_search.inc.php';
	$op_stat = numberOnly($_GET['op_stat']);
	$partner_no = numberOnly($_GET['partner_no']);
	$account_idx = numberOnly($_GET['account_idx']);
	if(!$partner_no) {
		msg('입점업체를 선택해 주세요.', 'back');
	}
	if($account_idx > 0 && $body != 'income@partner_account_excel.exe') {
		$account = $pdo->assoc("select * from $tbl[order_account] where no='$account_idx' and partner_no='$partner_no'");
		if(!$account['no']) msg('존재하지 않는 정산정보입니다.', 'back');
		if($account['startdate'] == 0 && $account['prd_prc'] < 0) {
			include 'partner_account_edt_refund.inc.php';
			return;
		}
		if($account['stat'] > 1 && $admin['level'] < 4) alert('정산 승인된 데이터는 수정이 불가능하며, 열람만 가능합니다.');
		$_dates = $account['startdate'];
		$_datee = $account['finishdate'];
	}

	$partner_name = stripslashes($pdo->row("select corporate_name from $tbl[partner_shop] where no='$partner_no'"));

	$sale_fd = getOrderSalesField('op', '+', array('sale5', 'sale7'));

	$cfd = 'op.sale5';
	if(fieldExist($tbl['order_product'], 'sale7')) {
		$cfd = '(op.sale5+op.sale7)';
	}

	if($account_idx > 0) {
		$res = $pdo->iterator("
			select
				o.ono,
				op.partner_no, op.no as opno, op.stat,
				op.name, op.buy_ea, op.sell_prc,
				op.fee_rate, op.cpn_rate,
				od.dlv_prc, od.no as odno,
				if(op.stat<10, op.total_prc, 0) as total_prc,
				if(op.stat<10, $sale_fd, 0) as sale_prc,
				if(op.stat<10, $cfd, 0) as sale5,
				if(op.stat<10, fee_prc, 0) as fee_prc,
				if(op.stat<10, cpn_fee, 0) as cpn_fee
			from $tbl[order] o
				inner join $tbl[order_product] op using(ono)
				left join $tbl[order_dlv_prc] od using(ono, partner_no)
			where op.partner_no='$partner_no' and op.account_idx='$account_idx'
		");
	} else {
		$res = $pdo->iterator("
			select
				o.ono,
				op.partner_no, op.no as opno, op.stat,
				op.name,
				if(op.stat<10, op.buy_ea, 0) as buy_ea,
				if(op.stat<10, op.sell_prc, 0) as sell_prc,
				if(op.stat<10, op.total_prc, 0) as total_prc,
				if(op.stat<10, $sale_fd, 0) as sale_prc,
				if(op.stat<10, $cfd, 0) as sale5,
				op.fee_rate,
				if(op.stat<10, op.fee_prc, 0) as fee_prc,
				cpn_rate,
				if(op.stat<10, op.cpn_fee, 0) as cpn_fee,
				od.dlv_prc, od.no as odno
			from $tbl[order] o
				inner join $tbl[order_product] op using(ono)
				left join $tbl[order_dlv_prc] od using(ono, partner_no)
			where op.partner_no='$partner_no' $w
				and
				(
					(op.stat=5 and o.date{$cfg['partner_account_date']} between $_dates and $_datee)
					or ((o.stat=17 or o.stat=5) and op.stat=17 and od.dlv_prc>0 and (op.repay_date between $_dates and $_datee))
				)
				and op.partner_no > 0
				and op.account_idx=0
		");
	}

	$cnt = 1;

	if($admin['level'] == 4) {
		$back_url = './?body=order@account_list';
	} else if($account_idx > 0) {
		$back_url = './?body=income@partner_account';
	} else {
		$back_url = './?body=income@partner_account_reg';
	}
	foreach($_GET as $key => $val) {
		if ($key != 'page') $qs .= '&'.$key.'='.$val;
		if($key == 'body') continue;
		if($key == 'partner_no') continue;
		if (!is_array($val)) $back_url .= "&$key=".urlencode($val);
	}

	$xls_query = preg_replace('/&body=[^&]+/', '', $qs);
	$xls_query = str_replace('&body='.$pgCode, '', $xls_query);
	$xls_query = preg_replace('/&?exec=[^&]+/', '', $xls_query);

    // 세일 항목명
    $sales_name = '';
    foreach ($_order_sales as $key => $val) {
        if ($key == 'sale5' || $key == 'sale7') continue;
        if ($sales_name) $sales_name .= ', ';
        $sales_name .= $val;
    }
	if ($body == 'income@partner_account_excel.exe') return;

?>
<style type="text/css">
.tbl_stat thead th {
	white-space: nowrap;
}
.tbl_stat tbody th, .tbl_stat tbody td {
	font-size: 11px;
	font-family: 'malgun', 'verdana';
	white-space: nowrap;
}
.tbl_stat tbody th {
	background: #f5f5f5;
	text-align: right;
	font-weight: bold;
}
tr.op_stat2 {
	background: #ffeeee;
}
</style>

<form method="post" action="./index.php" target="hidden<?=$now?>">
	<?if($admin['level'] < 4) {?>
	<input type="hidden" name="body" value="income@partner_account_edt.exe">
	<input type="hidden" name="partner_no" value="<?=$partner_no?>">
	<input type="hidden" name="account_idx" value="<?=$account_idx?>">
	<?}?>

	<div class="box_title">
		<?=$partner_name?> 정산 정보 편집 (<?=date('Y-m-d', $_dates)?> ~ <?=date('Y-m-d', $_datee)?>)
		<span class="box_btn_s btns icon excel"><a href="./?body=income@partner_account_excel.exe<?=$xls_query?>">엑셀다운</a></span>
	</div>
	<table class="tbl_col tbl_stat">
		<colgroup>
			<col />
		</colgroup>
		<thead>
			<tr>
				<th>주문번호</th>
				<th>상품명</th>
				<th>주문상태</th>
				<th>단가</th>
				<th>주문수량</th>
				<th>총상품금액</th>
				<th>
                    할인금액
                    <a href="#" class="tooltip_trigger" data-child="tooltip_sales_name"></a>
                    <div class="info_tooltip tooltip_sales_name"><?=$sales_name?></div>
                </th>
				<th>쿠폰할인</th>
				<th>쿠폰정산비율</th>
				<th>실결제액</th>
				<th>수수료율</th>
				<th>입점수수료</th>
				<th>쿠폰 업체부담</th>
				<th>총정산금액</th>
			</tr>
		<tbody>
			<?while($data = parseStat($res)) {?>
			<?if($_prev_ono != $data['ono']) { $sum['account_prc']+=$data['dlv_prc']?>
			<?if (!$data['odno']) {
				 $cnt++;
			     $data['odno'] = $pdo->row("select max(no) from {$tbl['order_dlv_prc']}")+$cnt;
			}?>
			<tr>
				<th class="right"><a href="#" onclick="viewOrder('<?=$data['ono']?>'); return false;"><?=$data['ono']?></a></th>
				<td class="left">배송비</td>
				<td colspan="7"></td>
				<td class="right">
					<?if($admin['level'] == 4) {?>
						<?=parsePrice($data['dlv_prc'], true)?>
					<?} else {?>
					<input type="text" name="dlv_prc[<?=$data['odno']?>]" class="input right" size="8" value="<?=parsePrice($data['dlv_prc'])?>">
					<?}?>
					<input type="hidden" name="ono[<?=$data['odno']?>]" value="<?=$data['ono']?>">
				</td>
				<td colspan="3"></td>
				<td class="right"><?=parsePrice($data['dlv_prc'], true)?></td>
			</tr>
			<?}?>
			<tr class="op_stat<?=$data['op_stat']?>">
				<th class="right"></th>
				<td class="left"><?=$data['name']?></td>
				<td><span style="color:<?=$_order_color[$data['stat']]?>"><?=$_order_stat[$data['stat']]?></span></td>
				<td class="right"><?=parsePrice($data['sell_prc'], true)?></td>
				<td><?=$data['buy_ea']?></td>
				<td class="right"><?=parsePrice($data['total_prc'], true)?></td>
				<td class="right"><?=parsePrice($data['sale_prc'], true)?></td>
				<td class="right"><?=parsePrice($data['sale5'], true)?></td>
				<td class="right"><?=$data['cpn_rate']?> %</td>
				<td class="right"><?=parsePrice($data['pay_prc'], true)?></td>
				<td class="right"><?=$data['fee_rate']?> %</td>
				<td class="right">
					<?if($admin['level'] == 4) {?>
					<?=parsePrice($data['fee_prc'], true)?>
					<?} else {?>
					<input type="text" name="fee_prc[<?=$data['opno']?>]" class="input right" size="8" value="<?=parsePrice($data['fee_prc'])?>">
					<?}?>
				</td>
				<td class="right">
					<?if($admin['level'] == 4) {?>
					<?=parsePrice($data['cpn_fee'], true)?>
					<?} else {?>
					<input type="text" name="cpn_fee[<?=$data['opno']?>]" class="input right" size="8" value="<?=parsePrice($data['cpn_fee'])?>">
					<?}?>
				</td>
				<td class="right"><?=parsePrice($data['account_prc'], true)?></td>
			</tr>
			<?$_prev_ono = $data['ono'];}?>
			<tr>
				<th class="right">합계</th>
				<td colspan="3"></td>
				<td class="center"><?=number_format($sum['buy_ea'])?></td>
				<td class="right"><?=parsePrice($sum['total_prc'], true)?></td>
				<td class="right"><?=parsePrice($sum['sale_prc'], true)?></td>
				<td class="right"><?=parsePrice($sum['sale5'], true)?></td>
				<td>-</td>
				<td class="right"><?=parsePrice($sum['pay_prc'], true)?></td>
				<td>-</td>
				<td class="right"><?=parsePrice($sum['fee_prc'], true)?></td>
				<td class="right"><?=parsePrice($sum['cpn_fee'], true)?></td>
				<td class="right"><?=parsePrice($sum['account_prc'], true)?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<?if($admin['level'] < 4) {?>
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?}?>
		<span class="box_btn"><input type="button" value="뒤로" onclick="location.href='<?=$back_url?>'"></span>
	</div>
</form>