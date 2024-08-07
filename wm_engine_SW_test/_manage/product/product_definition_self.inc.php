<?PHP

	include_once $engine_dir.'/_engine/include/paging.php';

	addField($tbl['category'], 'reg_date', 'datetime not null');

	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 15;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['category']} where ctype=3");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator("select no, name, code, reg_date from {$tbl['category']} where ctype=3 order by no desc ".$PagingResult['LimitQuery']);
	$idx = $NumTotalRec-($row*($page-1));

	function parseList($res) {
		$data = $res->current();
        $res->next();
		if($data == false) return false;

		if($data['reg_date'] == '0000-00-00 00:00:00') $data['reg_date'] = '-';

		return $data;
	}

?>
<form id="definitionFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@product_definition_register.exe">
	<input type="hidden" name="exec" value="remove">

	<table class="tbl_col">
		<caption class="hidden">상품정보제공고시 관리</caption>
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
			<?while($data = parseList($res)) {?>
			<tr>
				<td><input type="checkbox" class="check_one" name="no[]" value="<?=$data['no']?>"></td>
				<td class="left"><?=$data['code']?></td>
				<td class="left"><a href="?body=product@product_definition_register&no=<?=$data['no']?>"><strong><?=$data['name']?></strong></a></td>
				<td><?=$data['reg_date']?></td>
				<td>
					<span class="box_btn_s"><a href="?body=product@product_definition_register&no=<?=$data['no']?>">수정</a></span>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeDefinition(<?=$data['no']?>);"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택삭제" onclick="removeDefinition(this.form);"></span>
		</div>
		<div class="right_area">
			<span class="box_btn_s icon setup"><a href="?body=product@product_definition_register">추가</a></span>
		</div>
		<?=$pg_res?>
	</div>
</form>
<script type="text/javascript">
function removeDefinition(f) {
	var param = null;
	if(typeof f == 'object') {
		if($('.check_one:checked').length == 0) {
			window.alert('삭제할 데이터를 선택해주세요.');
			return false;
		}
		param = $(f).serialize();
	} else {
		var form = document.getElementById('definitionFrm');
		param = {'body':form.body.value, 'exec':'remove', 'no[]':f};
	}

	if(confirm('선택한 정보고시를 삭제하시겠습니까?')) {
		printLoading();
		$.post('./index.php', param, function(r) {
			location.reload();
		});
	}
}

$(function() {
	new chainCheckbox(
		$('.check_all'),
		$('.check_one')
	);
});
</script>