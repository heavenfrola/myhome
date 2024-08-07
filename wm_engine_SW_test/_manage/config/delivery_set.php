<?php

	include $engine_dir."/_engine/include/paging.php";

	if($cfg['use_partner_delivery']) {
		$partner_no = $admin['partner_no'];
		$w = " and partner_no='$partner_no'";
	}

	$sql = "select * from {$tbl['product_delivery_set']} where 1 $w order by no desc ";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['product_delivery_set']} where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page - 1));

	function parseData($res) {
		global $cfg;

        $data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['set_name'] = stripslashes($data['set_name']);
		$data['reg_date_s'] = date('Y-m-d',strtotime($data['reg_date']));
		if($data['delivery_type'] == '6') $data['dlv_prc'] = parsePrice($data['delivery_free_limit'], true).$cfg['currency_type'];
		else {
			$dlv_prc = array();
			$tmp1 = json_decode($data['delivery_free_limit']);
			$tmp2 = array();
			if(is_array($tmp1) == true) {
				foreach($tmp1 as $key => $val) {
					$tmp2[] = $val[2];
				}
				if(min($tmp2) == max($tmp2)) {
					$data['dlv_prc'] = parsePrice(min($tmp2), true).$cfg['currency_type'];
				} else {
					$data['dlv_prc'] = parsePrice(min($tmp2), true).$cfg['currency_type'].'~'.parsePrice(max($tmp2), true).$cfg['currency_type'];
				}
			}
		}

		return $data;
	}

	setListURL('delivery_set');

?>
<table class="tbl_col">
	<caption>개별 배송비 관리</caption>
	<colgroup>
		<col style="width:80px">
		<col>
		<col>
		<col>
		<col style="width:100px">
		<col>
		<col>
		<col style="width:80px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">세트명</th>
			<th scope="col">유형</th>
			<th scope="col">배송비</th>
			<th scope="col">개별배송비 코드</th>
			<th scope="col">등록일시</th>
			<th scope="col">등록자</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseData($res)) {?>
		<tr>
			<td><?=$idx--?></td>
			<td class="left"><a href="?body=config@delivery_set_regist&no=<?=$data['no']?>"><?=$data['set_name']?></a></td>
			<td>
				<?=$_delivery_types[$data['delivery_type']]?>
				<?if($data['delivery_loop_type'] == 'Y') {?>
				<div>(범위 반복)</div>
				<?}?>
			</td>
			<td><?=$data['dlv_prc']?></td>
			<td><?=$data['no']?></td>
			<td><?=$data['reg_date_s']?></td>
			<td><?=$data['admin_id']?></td>
			<td><span class="box_btn_s"><input type="button" value="삭제" onclick="removeDeliverySet(<?=$data['no']?>);"></span></td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
	<div class="right_area">
		<span class="box_btn blue"><input type="button" value="등록" onclick="goM('config@delivery_set_regist');"></span>
	</div>
</div>
<script type="text/javascript">
function removeDeliverySet(no) {
	if(confirm('선택한 개별 배송비 세트를 삭제하시겠습니까?\n개별 배송비 세트가 적용된 상품은 기본 배송비로 변경됩니다.')) {
		$.post('./index.php', {'body':'config@delivery_set_regist.exe', 'exec':'removeDeliverySet', 'no':no}, function(r) {
			location.reload();
		});
	}
}
</script>