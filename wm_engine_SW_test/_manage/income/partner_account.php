<?PHP

	function parseStat($res) {
		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['startdate'] = ($data['startdate'] == 0) ? '-' : date('Y-m-d', $data['startdate']);
		$data['finishdate'] = ($data['finishdate'] == 0) ? '-' : date('Y-m-d', $data['finishdate']);
		$data['request_date'] = date('Y-m-d H:i', $data['request_date']);
		$data['confirm_date'] = ($data['confirm_date'] > 0) ? date('Y-m-d H:i', $data['confirm_date']) : '-';
		$data['complete_date'] = ($data['complete_date'] > 0) ? date('Y-m-d H:i', $data['complete_date']) : '-';

		return $data;
	}

	$w = '';
	if($admin['level'] == 4) {
		$w .= " and pn.no='$admin[partner_no]'";
	}

	$dates = addslashes($_GET['dates']);
	$datee = addslashes($_GET['datee']);

	if(!$dates) $dates = date('Y-m-d', strtotime('-3 months'));
	if(!$datee) $datee = date('Y-m-d');
	$_dates = strtotime($dates);
	$_datee = strtotime($datee)+86399;

	$corp_name = trim($_GET['corp_name']);
	$_corp_name = addslashes($corp_name);
	if($_corp_name) {
		$w .= " and pn.corporate_name like '%$corp_name%'";
	}

	$stat = numberOnly($_GET['stat']);
	$page = numberOnly($_GET['page']);
	if($stat > 0) $w .= " and ac.stat='$stat'";


	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;
	$QueryString = '';
	foreach($_GET as $key => $val) {
		if($key =='page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[order_account] ac inner join $tbl[partner_shop] pn on ac.partner_no=pn.no where ac.request_date between $_dates and $_datee $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$pg_res = $PagingResult['PageLink'];
	$idx = $NumTotalRec - ($row * ($page - 1));

	$res = $pdo->iterator("
		select pn.corporate_name, ac.*
            from {$tbl['order_account']} ac inner join {$tbl['partner_shop']} pn on ac.partner_no=pn.no
            where ac.request_date between $_dates and $_datee $w
            order by request_date desc, ac.no desc
	".$PagingResult['LimitQuery']);

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
.defined {
	background: #ffffee;
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
			<th scope="row">정산등록일</th>
			<td>
				<input type="text" name="dates" class="input datepicker" size="15" value="<?=$dates?>">
				~
				<input type="text" name="datee" class="input datepicker" size="15" value="<?=$datee?>">
			</td>
		</tr>
		<tr>
			<th scope="row">정산처리상태</th>
			<td>
				<?=selectArray($_order_account_stat, 'stat', null, '전체', $stat)?>
			</td>
		</tr>
		<?if($admin['level'] < 4) {?>
		<tr>
			<th scope="row">업체명</th>
			<td>
				<input type="text" name="corp_name" class="input" value="<?=inputText($corp_name)?>">
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<br>

<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="income@partner_account.exe">

	<table class="tbl_col tbl_stat">
		<thead>
			<tr>
				<th rowspan="2"><input type="checkbox" class="all_chkbox"></th>
				<th rowspan="2">업체명</th>
				<th rowspan="2" colspan="2">기준입금일</th>
				<th rowspan="2">상품판매금액</th>
				<th rowspan="2">배송비</th>
				<th rowspan="2">입점수수료</th>
				<th colspan="2">쿠폰할인</th>
				<th rowspan="2">총정산금액</th>
				<th rowspan="2">정산입금액</th>
				<th rowspan="2">정산상태</th>
				<th rowspan="2">등록일시</th>
				<th rowspan="2">승인일시</th>
				<th rowspan="2">완료일시</th>
				<th rowspan="2" colspan="3">관리</th>
			</tr>
			<tr>
				<th>본사부담</th>
				<th>입점사부담</th>
			</tr>
		</thead>
		<tbody>
		<?while($data = parseStat($res)) {?>
		<tr>
			<th><input type="checkbox" class="sub_chkbox" name="no[<?=$data['no']?>]" value="<?=$data['no']?>"></th>
			<th class="left"><?=$data['corporate_name']?></th>
			<td><?=$data['startdate']?></td>
			<td><?=$data['finishdate']?></td>
			<td class="right"><?=parsePrice($data['prd_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['dlv_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['fee_prc'], true)?></td>
			<td class="right"><?=parsePrice($data['cpn_master'], true)?></td>
			<td class="right"><?=parsePrice($data['cpn_partner'], true)?></td>
			<td class="right defined"><?=parsePrice($data['prd_prc']+$data['cpn_master']+$data['dlv_prc']-$data['fee_prc'], true)?></td>
			<?if($admin['level'] > 3) {?>
			<td class="right"><?=parsePrice($data['input_prc'], true)	?></td>
			<td class="center"><?=$_order_account_stat[$data['stat']]?></td>
			<?} else {?>
			<td class="right"><input type="text" name="input_prc[<?=$data['no']?>]" class="input right input_prc" size="10" value="<?=parsePrice($data['input_prc'], true)?>"></td>
			<td><?=selectArray($_order_account_stat, "stat[$data[no]]", null, null, $data['stat'])?></td>
			<?}?>
			<td class="center"><?=$data['request_date']?></td>
			<td class="center"><?=$data['confirm_date']?></td>
			<td class="center"><?=$data['complete_date']?></td>
			<td><span class="box_btn_s"><input type="button" value="상세" onclick="location.href='?body=income@partner_account_edt&partner_no=<?=$data['partner_no']?>&account_idx=<?=$data['no']?><?=$args?>'"></span></td>
			<!--
			<td><span class="box_btn_s"><input type="button" value="이력"></span></td>
			-->
			<td>
				<?if($admin['level'] < 4) {?>
				<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeAccount(<?=$data['no']?>);"></span>
				<?}?>
				<?if($data['stat'] == 1 && $admin['level'] == 4) {?>
				<span class="box_btn_s gray"><input type="button" value="승인" onclick="acceptAccount(<?=$data['no']?>);"></span>
				<?}?>
			</td>
		</tr>
		<?}?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<span class="explain">총정산금액 = 상품금액 + 쿠폰할인(본사부담) + 배송비 - 입점수수료</span>
	</div>

	<div class="box_bottom">
		<?=$pg_res?>
		<?if($admin['level'] < 4) {?>
		<div class="left_area">
		<span class="box_btn blue"><input type="submit" value="변경"></span>
		</div>
		<?}?>
	</div>
</form>

<script type="text/javascript">
$('.input_prc').focus(function() {
	this.select();
});
<?if($admin['level'] < 4) {?>
function removeAccount(no) {
	if(confirm('정산데이터를 삭제하시면 관련 주문들의 정산등록이 해제되어 입점업체 정산등록 메뉴에서 재조회 및 신규 정산등록이 가능합니다.\n\n정말 삭제하시겠습니까?')) {
		$.post('./?body=income@partner_account.exe', {'exec':'remove', 'account_idx':no}, function(r) {
			if(r == 'OK') location.reload();
			else window.alert(r);
		});
	}
}
<?}?>

function acceptAccount(no) {
	if(confirm('정산 내역에 동의 하십니까?')) {
		$.post('./?body=income@partner_account.exe', {'exec':'accept', 'account_idx':no}, function(r) {
			if(r == 'OK') location.reload();
			else window.alert(r);
		});
	}
}

new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
</script>