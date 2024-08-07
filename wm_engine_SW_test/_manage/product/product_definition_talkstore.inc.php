<?PHP

	require_once $engine_dir.'/_config/set.talkStore.php';

    if (isTable($tbl['product_talkstore_announce']) == false) {
        include $engine_dir.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['product_talkstore_announce']);
    }

	$sql = "select idx, title, type, reg_date from $tbl[product_talkstore_announce] where 1 $w order by reg_date desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[product_talkstore_announce] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page - 1));

	$_SESSION['listURL'] = getURL();

?>
<form id="definitionFrm" method="post" action="?" target="hidden<?=$now?>" onsubmit="return removeAnnouce(this);">
	<input type="hidden" name="body" value="product@product_definition_talkstore_register.exe">
	<input type="hidden" name="exec" value="remove">

	<table class="tbl_col">
		<caption class="hidden">카카오톡 정보고시 목록</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:312px">
			<col>
			<col style="width:150px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" class="check_all"></th>
				<th scope="col">상품군</th>
				<th scope="col">제목</th>
				<th scope="col">등록일시</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td><input type="checkbox" name="idx[]" class="check_one" value="<?=$data['idx']?>"></td>
				<td class="left"><?=$_talkstore_announce[$data['type']]['name']?></td>
				<td class="left"><a href="?body=product@product_definition_talkstore_register&idx=<?=$data['idx']?>"><strong><?=$data['title']?></strong></a></td>
				<td><?=date('Y-m-d H:i', strtotime($data['reg_date']))?></td>
				<td>
					<span class="box_btn_s"><a href="?body=product@product_definition_talkstore_register&idx=<?=$data['idx']?>">수정</a></span>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeAnnouce(<?=$data['idx']?>);"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="submit" value="선택 삭제"></span>
		</div>
		<div class="right_area">
			<span class="box_btn_s icon setup"><input type="button" value="등록" onclick="goM('product@product_definition_talkstore_register')"></span>
		</div>
		<?=$pg_res?>
	</div>
	<!-- //페이징 & 버튼 -->
</form>
<script type="text/javascript">
function removeAnnouce(f) {
	var param = null;
	if(typeof f == 'object') {
		if($('.check_one:checked').length == 0) {
			window.alert('삭제할 데이터를 선택해주세요.');
			return false;
		}
		param = $(f).serialize();
	} else {
		var form = document.getElementById('definitionFrm');
		param = {'body':form.body.value, 'exec':'remove', 'idx[]':f};
	}

	if(confirm('선택한 정보고시를 삭제하시겠습니까?')) {
		printLoading();
		$.post('./index.php', param, function(r) {
			location.reload();
		});
	}
	return false;
}

$(function() {
	new chainCheckbox(
		$('.check_all'),
		$('.check_one')
	);
});
</script>