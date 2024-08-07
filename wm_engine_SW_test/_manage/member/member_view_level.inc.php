<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 등급 변경 내역
	' +----------------------------------------------------------------------------------------------+*/

	$mno = numberOnly($_GET['mno']);
	$mid = addslashes($_GET['mid']);
	$sql = "select * from $tbl[member_level_log] where member_no='$mno' and member_id='$mid' order by reg_date desc";

	include_once $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['member_level_log']} where member_no='$mno' and member_id='$mid'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	function parseLog($res) {
		global $idx, $group, $ref;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['level'] = $group[$data['level']];
		$data['ori_level'] = $group[$data['ori_level']];
		$data['ref2'] = $ref[$data['ref']];
		$data['reg_date'] = date('Y-m-d H:i', strtotime($data['reg_date']));
		if(empty($data['admin_id']) == true && empty($data['ref']) == false) $data['admin_id'] = 'scheduler';

		$idx--;

		return $data;
	}

	$group = array();
	$gres = $pdo->iterator("select no, name from {$tbl['member_group']}");
    foreach ($gres as $data) {
		$group[$data['no']] = stripslashes($data['name']);
	}

	$ref = array(
		'' => '기타',
		'manage' => '관리자 수정',
		'all' => '전체 등급조정',
		'member' => '개별 등급조정',
	);

?>
<table class="tbl_col">
	<caption class="hidden">회원그룹 변경내역</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:140px;">
		<col>
		<col>
		<col>
		<col>
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">일시</th>
			<th scope="col">변경 방법</th>
			<th scope="col">변경 전</th>
			<th scope="col">변경 후</th>
			<th scope="col">처리자</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseLog($res)) {?>
		<tr>
			<td><?=$idx?></td>
			<td><?=$data['reg_date']?></td>
			<td><?=$data['ref2']?></td>
			<td><?=$data['ori_level']?></td>
			<td class="p_color"><?=$data['level']?></td>
			<td><?=$data['admin_id']?></td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>