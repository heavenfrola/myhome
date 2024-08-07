<?PHP

	if(!isTable($tbl['hs_code'])){
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['hs_code']);
	}

	$from = ($from) ? $from : $_GET['from'];

	if($from != 'popup') {
		include $engine_dir."/_manage/product/hs_code.frm.php";
	}

	$w = "";
	$search_key = addslashes(trim($_GET['search_key']));
	$search = addslashes(trim($_GET['search']));
	if ($search_key && $search) {
		$w .= " and `$search_key` like '%$search%'";
	}

	$_search = array('name'=>'항목명','hs_code'=>'HS코드');

	$query = "select * from ${tbl['hs_code']} where no is not null $w order by regdate desc";
	$res = $pdo->iterator($query);

?>
<br>
<form method="get">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<input type="hidden" name="from" value="<?=$_GET['from']?>">
	<div class="box_title first">
		<h2 class="title">HS코드 검색</h2>
	</div>
	<div class="box_bottom top_line">
		<?=selectArray($_search, "search_key", 2, "", $search_key)?>
		<input type="text" name="search" value="<?=inputText($search)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&from=<?=$_GET['from']?>'"></span>
	</div>
</form>

<div class="box_title">
	<h2 class="title">HS코드 관리</h2>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">Code 추가</caption>
	<colgroup>
		<col style="width:40%">
		<col style="width:40%">
		<col style="width:20%">
	</colgroup>
	<thead>
		<tr>
			<th>항목명</th>
			<th>HS 코드</th>
			<th><?=$from=='popup'?'선택':'관리'?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($res as $data) {?>
		<tr>
			<td><?=stripslashes($data['name'])?></td>
			<td><?=$data['hs_code']?></td>
			<td>
				<?php if($from == 'popup') { php ?>
					<span class="box_btn_s blue"><input type="button" value="선택" onclick="selectHsCode('<?=$data['hs_code']?>')"></span>
				<?php } else { ?>
					<span class="box_btn_s"><input type="button" value="수정"  onclick="wisaOpen('./pop.php?body=product@hs_code.frm&no=<?=$data['no']?>','hsCodeEdit')"></span>
					<span class="box_btn_s gray"><input type="button" value="삭제" onClick="deletePrdHsCode(<?=$data['no']?>)"></span>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<script>
function deletePrdHsCode(no){
	f=document.hsfieldFrm;
	if(!confirm('해당 항목을 삭제하시겠습니까?')) return;
	f.no.value=no;
	f.exec.value='delete';
	f.submit();
}
function selectHsCode(hs_code){
	opener.$("input:text[name='hs_code']").val(hs_code);
	self.close();
}
</script>