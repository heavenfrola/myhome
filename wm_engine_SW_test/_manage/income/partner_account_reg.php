<?PHP

	if(!isTable($tbl['order_dlv_prc'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['order_dlv_prc']);
	}

	function parseStat(&$res) {
		global $tbl, $pdo;

		$data = current($res);
        next($res);
		if(!$data) return false;

		$data = array_map('stripslashes', $data);

		$data['dlv_prc'] = $pdo->row("select sum(dlv_prc) from (select sum(distinct dlv_prc) as dlv_prc from {$tbl['order_dlv_prc']} a inner join {$tbl['order_product']} b using(ono) where a.partner_no='$data[partner_no]' and b.no in ($data[opno]) group by ono) a");
		$data['prd_prc'] = ($data['total_prc']-$data['cpn_sale']);
		$data['pay_prc'] = $data['prd_prc']+$data['dlv_prc'];
		$data['account_prc'] = $data['total_prc']-$data['cpn_fee']-$data['fee_prc']+$data['dlv_prc'];
		$data['cpn_master'] = $data['cpn_sale']-$data['cpn_fee'];
		$data['op_stat'] = ($data['pay_prc'] < 0) ? '2' : '1';

		return $data;
	}

	$args = '';
	foreach($_GET as $key => $val) {
		if($key == 'body') continue;
		if(is_array($val)) {
			foreach($val as $key2 => $val2) {
				if($val2) $args .= sprintf('&%s[%s]=%s', $key, $key2, urlencode($val2));
			}
		} else {
			$args .= "&$key=".urlencode($val);
		}
	}

	// 정산 대상 검색
	$_partner_account_date = array(
		2 => $_order_stat[2].'일',
		4 => $_order_stat[4].'일',
		5 => $_order_stat[5].'일',
	);
	include 'partner_account_search.inc.php';
	if(empty($cfg['partner_account_date']) == true) $cfg['partner_account_date'] = 5;
	$search_date_nm = $_partner_account_date[$cfg['partner_account_date']];
	$sale_fd = getOrderSalesField('op', '+', array('sale5', 'sale7'));

	$cfd = 'op.sale5';
	if(fieldExist($tbl['order_product'], 'sale7')) {
		$cfd = '(op.sale5+op.sale7)';
	}

	$res = $pdo->iterator("
		select
			op.partner_no, pn.corporate_name,
			count(distinct o.ono) as cnt, sum(if(op.stat<10, op.buy_ea, 0)) as buy_ea,
			sum(if(op.stat<10, op.total_prc, 0)) as total_prc,
			sum(if(op.stat<10, $sale_fd, 0)) as sale_prc,
			sum(if(op.stat<10, $cfd, 0)) as cpn_sale,
			sum(if(op.stat<10, fee_prc, 0)) as fee_prc,
			sum(if(op.stat<10, cpn_fee, 0)) as cpn_fee,
			od.dlv_prc as dlv_prc,
			group_concat(op.no) as opno
		from $tbl[order] o
			inner join $tbl[order_product] op using(ono)
			inner join $tbl[partner_shop] pn on op.partner_no=pn.no
			left join $tbl[order_dlv_prc] od on (od.ono=o.ono and od.partner_no=op.partner_no)
		where 1 $w $pwhere1
			and
			(
				(op.stat=5 and o.date{$cfg['partner_account_date']} between $_dates and $_datee)
				or ((o.stat=17 or o.stat=5) and op.stat=17 and od.dlv_prc>0 and (op.repay_date between $_dates and $_datee))
			)
			and op.partner_no > 0
			and op.account_idx=0
		group by partner_no
	");


	$accres = array();
    foreach ($res as $tmp) {
		$accres[] = $tmp;
	}

	if($page_mode == 'reg') return;

    // 세일 항목명
    $sales_name = '';
    foreach ($_order_sales as $key => $val) {
        if ($key == 'sale5' || $key == 'sale7') continue;
        if ($sales_name) $sales_name .= ', ';
        $sales_name .= $val;
    }

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
</style>
<form id="search" method="get" action="./">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<input type="hidden" name="log_mode" value="<?=$log_mode?>">
	<div class="box_title first">
		<h2 class="title">검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">검색</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><?=$search_date_nm?></th>
			<td>
				<input type="text" name="dates" class="input datepicker" size="15" value="<?=$dates?>">
				~
				<input type="text" name="datee" class="input datepicker" size="15" value="<?=$datee?>">
			</td>
		</tr>
		<tr>
			<th scope="row">결제수단</th>
			<td>
				<?foreach($_pay_type as $key => $val) {?>
				<label><input type="checkbox" name="sel_pay_type[]" value="<?=$key?>" <?=checked(in_array($key, $sel_pay_type), true)?>> <?=$val?></label>
				<?}?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<br>

<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="$('#stpBtn').hide()">
	<input type="hidden" name="body" value="income@partner_account_req.exe">
	<input type="hidden" name="dates" value="<?=$_GET['dates']?>">
	<input type="hidden" name="datee" value="<?=$_GET['datee']?>">

	<table class="tbl_col tbl_stat">
		<colgroup>
			<col />
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<!--<col style="width:120px;">-->
			<col style="width:120px;">
			<col style="width:120px;">
		</colgroup>
		<thead>
			<tr>
				<th rowspan="2"><label><input type="checkbox" class="all_chkbox">업체명</label></th>
				<th rowspan="2">총주문(상품수)</th>
				<th rowspan="2">실결제금액</th>
				<th rowspan="2">상품금액</th>
				<th rowspan="2">쿠폰</th>
				<th rowspan="2">
                    할인
                    <a href="#" class="tooltip_trigger" data-child="tooltip_sales_name"></a>
                    <div class="info_tooltip tooltip_sales_name"><?=$sales_name?></div>
                </th>
				<th colspan="2">입점수수료</th>
				<th rowspan="2">정산배송비</th>
				<!--
				<th rowspan="2">정산후취소</th>
				-->
				<th rowspan="2">총 정산금액</th>
				<th rowspan="2">수정</th>
			</tr>
			<tr>
				<th>판매수수료</th>
				<th>쿠폰 업체부담</th>
			</tr>
		</thead>
		<tbody>
		<?while($data = parseStat($accres)) {?>
		<tr>
			<th class="left">
				<label><input type="checkbox" name="partner_no[]" class="sub_chkbox" value="<?=$data['partner_no']?>"> <?=$data['corporate_name']?></label>
			</th>
			<td><?=number_format($data['cnt'])?>건 (<?=number_format($data['buy_ea'])?>개)</td>
			<td class="right"><?=parsePrice($data['pay_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['total_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['cpn_sale'], true)?></td>
			<td class="right"><?=parsePrice($data['sale_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['fee_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['cpn_fee'], true)?></td>
			<td class="right"><?=parsePrice($data['dlv_prc'], true)?></td>
			<!--
			<td class="right"><?=parsePrice($data['account_cancel'], true)?></td>
			-->
			<td class="right"><?=parsePrice($data['account_prc'], true)?></td>
			<td><span class="box_btn_s"><input type="button" value="상세" onclick="location.href='?body=income@partner_account_edt&partner_no=<?=$data['partner_no']?><?=$args?>&op_stat=<?=$data['op_stat']?>'"></span></td>
		</tr>
		<?}?>
		</tbody>
	</table>

	<div class="box_bottom" id="stpBtn">
		<span class="box_btn blue"><input type="submit" value="정산등록"></span>
	</div>
</form>

<div class="box_middle2 left">
	<ul class="list_msg">
		<li>총 정산금액 = 상품금액-쿠폰업체부담-판매수수료-정산배송비+정산후취소</li>
	</ul>
</div>
<script type="text/javascript">
// 체크박스
new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
</script>