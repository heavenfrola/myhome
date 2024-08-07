<?PHP

	if(!isTable($tbl['order_account_refund'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['order_account_refund']);
	}

	include 'partner_account_search.inc.php';

	// 검색
	$w = " and a.reg_date between '$dates 00:00:00' and '$datee 23:59:59'";

	// 대상 데이터 조회
	$res = $pdo->iterator("select a.*, b.corporate_name from {$tbl['order_account_refund']} a inner join {$tbl['partner_shop']} b on a.partner_no=b.no where a.account_idx=0 and a.del_yn='N' $w order by a.no desc");

	function parseData($res) {
        $data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['corporate_name'] = stripslashes($data['corporate_name']);
		$data['refund_prc'] = $data['prd_prc']-$data['dlv_prc']+$data['dlv_prc_return']-$data['fee_prc']+$data['cpn_fee_m'];
		$data['reg_date_str'] = date('Y-m-d H:i', strtotime($data['reg_date']));

		return $data;
	}

?>
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
			<th scope="row">반품일</th>
			<td>
				<input type="text" name="dates" class="input datepicker" size="15" value="<?=$dates?>">
				~
				<input type="text" name="datee" class="input datepicker" size="15" value="<?=$datee?>">
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
	<input type="hidden" name="exec" value="refund">

	<table class="tbl_col tbl_stat">
		<thead>
			<tr>
				<th><input type="checkbox" class="all_chkbox"></th>
				<th>주문번호</th>
				<th>업체명</th>
				<th>취소상품금액</th>
				<th>추가배송비</th>
				<th>배송비 정산 환불</th>
				<th>입점수수료</th>
				<th>쿠폰본사부담</th>
				<th>환불요청금액</th>
				<th>상세내역</th>
				<th>반품처리자</th>
				<th>반품일시</th>
			</tr>
		</thead>
		<tbody>
			<?while($data = parseData($res)) {?>
			<tr>
				<td><input type="checkbox" class="sub_chkbox" name="refund_no[]" value="<?=$data['no']?>"></td>
				<td><a href='#' onclick="viewOrder('<?=$data['ono']?>'); return false;"><strong><?=$data['ono']?></strong></a></td>
				<td class="left"><?=$data['corporate_name']?></td>
				<td><?=parsePrice($data['prd_prc'], true)?></td>
				<td><?=parsePrice($data['dlv_prc'], true)?></td>
				<td><?=parsePrice($data['dlv_prc_return'], true)?></td>
				<td><?=parsePrice($data['fee_prc'], true)?></td>
				<td><?=parsePrice($data['cpn_fee_m'], true)?></td>
				<td><?=parsePrice($data['refund_prc'], true)?></td>
				<td><a href="#" onclick="cdetail.open('ono=<?=$data['ono']?>&payment_no=<?=$data['payment_no']?>'); return false;" class="p_color4">[보기]</a></td>
				<td><?=$data['admin_id']?></td>
				<td><?=$data['reg_date_str']?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom left">
		<span class="explain">환불 요청금액 = 취소상품금액 - 추가배송비 + 배송비 정산환불 - 입점수수료 + 쿠폰본사부담</span>
	</div>
	<div class="box_bottom" id="stpBtn">
		<span class="box_btn blue"><input type="submit" value="정산등록"></span>
	</div>
</form>
<script type="text/javascript">
// 주문서 상세보기
var cdetail = new layerWindow('order@order_cancel_info.exe');

// 체크박스
new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
</script>