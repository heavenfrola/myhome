<?PHP

	if(!isTable($tbl['sbscr_set'])) {
		include_once $engine_dir.'/_plugin/subScription/tbl_schema.php';
		$pdo->query($tbl_schema['sbscr_set']);
		$pdo->query($tbl_schema['sbscr_set_product']);
	}

	$sql = "select * from `$tbl[sbscr_set]` order by no desc";
	$res = $pdo->iterator($sql);
	$total_cnt = $pdo->row("select count(*) from `$tbl[sbscr_set]`");

	$period_text = array('1days' => '매일', '1' => "매주", '2' => "2주마다", '3' => "3주마다", '4' => "4주마다", '1months' => "매월");
	function parseSubSet($res) {
		global $tbl, $pdo, $period_text;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['name'] = stripslashes($data['name']);
		$data['admin_name'] = $pdo->row("select name from $tbl[mng] where admin_id='$data[admin_id]'");

		$tmp = explode('|', $data['dlv_period']);
		$data['dlv_period'] = '';
		foreach($tmp as $val) {
			if($data['dlv_period']) $data['dlv_period'] .= ', ';
			$data['dlv_period'] .= $period_text[$val];
		}

		$week_text = array("월", "화", "수", "목", "금", "토", "일");
		$tmp = explode('|', $data['dlv_week']);
		$data['dlv_week'] = '';
		foreach($tmp as $val) {
			if($data['dlv_week']) $data['dlv_week'] .= ', ';
			$data['dlv_week'] .= $week_text[$val-1];
		}

		$data['dlv_end'] = ($data['dlv_type'] == 'Y') ? $data['dlv_end'].'개월' : '기간없음';

		return $data;
	}

?>
<form id="pannel_set" name="sbscrsetFrm" method="post" target="hidden<?=$now?>" action="<?=$_SERVER['PHP_SELF']?>" style="display: none">
	<table class="tbl_col" style="border-top: 0">
		<caption class="hidden">정기배송 세트관리</caption>
		<colgroup>
			<col>
			<col style="width:150px">
			<col style="width:150px">
			<col style="width:150px">
			<col style="width:200px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">세트명</th>
				<th scope="col">배송주기</th>
				<th scope="col">배송요일</th>
				<th scope="col">배송기간</th>
				<th scope="col">작성자</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php while($data = parseSubSet($res)) { ?>
			<tr>
				<td class="left"><a href="#" onclick="wisaOpen('./pop.php?body=product@sub_set.frm&popup=Y&sbscr_set_no=<?=$data['no']?>', '', 'no', 600, 560); return false;"><strong><?=$data['name']?></strong></a></td>
				<td><?=$data['dlv_period']?></td>
				<td><?=$data['dlv_week']?></td>
				<td><?=$data['dlv_end']?></td>
				<td><?=$data['admin_name']?>(<?=$data['admin_id']?>)</td>
				<td>
					<span class="box_btn_s btnp"><a onclick="wisaOpen('./pop.php?body=product@sub_set.frm&popup=Y&sbscr_set_no=<?=$data['no']?>', '', 'no', 600, 560);">수정</a></span>
					<span class="box_btn_s btnp"><a onclick="removeSubSet(<?=$data['no']?>);">삭제</a></span>
				</td>
			</tr>
			<?php } ?>
            <?php if ($total_cnt == 0) { ?>
            <tr>
                <td colspan="6">
                    <p class="nodata">등록된 상품별 설정 세트가 없습니다.</p>
                </td>
            </tr>
            <?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="세트 등록" onclick="wisaOpen('./pop.php?body=product@sub_set.frm&popup=Y', '', 'no', 600, 560);"></span>
	</div>
</form>
<script type="text/javascript">
function removeSubSet(no) {
	if(confirm('선택한 세트를 삭제하시겠습니까?') == true) {
		printLoading();
		$.post('./index.php', {'body':'product@sub_set.exe', 'exec':'remove', 'no':no}, function() {
			location.reload();
		});
	}
}
</script>