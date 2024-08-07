<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배너광고코드 관리
	' +----------------------------------------------------------------------------------------------+*/

	$_SESSION['listURL'] = getURL();

	$sql="select * from `$tbl[pbanner_group]` where 1 $w order by `no` desc";

	// 페이징 설정
	include $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	$row=20;
	$block=10;
	$QueryString="&body=".$_GET[body];

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[pbanner_group]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form name="pbnFrm" method="post" target="hidden<?=$now?>" onsubmit="return pbnDelete(this)">
	<input type="hidden" name="body" value="openmarket@ban_register.exe">
	<input type="hidden" name="exec" value="delete">
	<div class="box_title first">
		<h2 class="title">배너광고코드 관리</h2>
	</div>
	<table class="tbl_col">
		<colgroup>
			<col style="width:60px">
			<col>
			<col style="width:100px">
			<col style="width:200px">
			<col style="width:200px">
			<col style="width:170px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.pbnFrm.check_pno, this.checked)"></th>
				<th scope="col">프로모션명</th>
				<th scope="col">누적클릭수</th>
				<th scope="col">코드</th>
				<th scope="col">등록일</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td><input type="checkbox" id="check_pno" name="check_pno[]" value="<?=$data['no']?>"></td>
				<td class="left"><a href="?body=openmarket@ban_register&no=<?=$data['no']?>&listURL=<?=$listURL?>"><?=$data['name']?></a></td>
				<td><?=number_format($data['visited'])?></td>
				<td><?=$data['code']?></td>
				<td><?=date('Y-m-d', $data['reg_date'])?></td>
				<td>
					<span class="box_btn_s"><input type="button" value="배너보기" onclick="viewPBanner(<?=$data[no]?>)"></span>
					<span class="box_btn_s"><input type="button" value="배너관리" onclick="location.href='?body=openmarket@ban_register&no=<?=$data['no']?>&listURL=<?=$listURL?>'"></span>
				</td>
			</tr>
			<tr id="area_tr_<?=$data['no']?>" style="display:none;">
				<td id="area_<?=$data['no']?>" colspan="6"></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<span class="box_btn gray"><input type="submit" value="선택삭제"></span>
	</div>

	<div class="box_middle2 left">
		<ul class="list_info">
			<li>온라인 배너광고를 위한 광고를 등록하고, 해당 광고에 대한 배너들을 등록합니다.</li>
			<li>하나의 프로모션명 아래에 여러 개의 배너를 등록할 수 있으며, 각 프로모션 단위로 회원가입, 접속, 주문 효과를 분석하실 수 있습니다.</li>
			<li>누적 클릭수는 각 프로모션 별로 누적된 클릭수로 중복체크하지 않습니다.</li>
		<ul>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
</form>

<script type="text/javascript">
	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});

	function pbnDelete(f) {
		if(!checkCB(f.check_pno,"삭제할 프로모션을")) return false;

		return confirm('선택한 프로모션과 배너광고코드를 삭제하시겠습니까?\t\n삭제시 접속통계 검색이 되지 않습니다.');
	}

	function viewPBanner(no) {
		var area = document.getElementById('area_'+no);
		var area_tr = document.getElementById('area_tr_'+no);

		if(area_tr.style.display == 'none') {
			area.style.display = '';
			area_tr.style.display = '';
		}else {
			area.style.display = 'none';
			area_tr.style.display = 'none';
		}

		$.post('?body=openmarket@ban_register.exe', {'exec':'banners', 'no':no}, function(r) {
			area.innerHTML = r;
		});
	}
</script>