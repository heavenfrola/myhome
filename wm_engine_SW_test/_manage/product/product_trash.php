<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 휴지통
	' +----------------------------------------------------------------------------------------------+*/

	$is_trash = 'Y';
	include_once $engine_dir."/_manage/product/product_search.inc.php";

	$qs = makeQueryString('page');

	// 카테고리 검색
	for($i = 0; $i <= 2; $i++) {
		switch($i) {
			case "0" : $cm = ""; $ct = "1"; break;
			case "1" : $cm = "x"; $ct = "4"; break;
			case "2" : $cm = "y"; $ct = "5"; break;
		}
		if($i == 0 || $_use[$cm."big"] == "Y") {
			if($_GET[$cm."big"]) $cw .= " or (`level`= '2' and `big` = '".$_GET[$cm."big"]."')";
			if($_GET[$cm."mid"]) $cw .= " or (`level`= '3' and `mid` = '".$_GET[$cm."mid"]."')";
			if($cfg['max_cate_depth'] > 3 && $_GET[$cm.'small']) $cw .= " or (`level`= '4' and `small` = '".$_GET[$cm."small"]."')";

			$sql = $pdo->iterator("select `no`,`name`,`ctype`,`level` from `$tbl[category]` where `ctype`= '$ct' and (`level` = '1' $cw )order by `level`,`sort`");
            foreach ($sql as $cate) {
				$cl = $cm.$_cate_colname[$ct][$cate['level']];
				$sel = ($_GET[$cl] == $cate[no]) ? "selected" : "";
				${"item_".$cate['ctype']."_".$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
			}
		}
	}

	// 기획전 검색
	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype` in (2, 6) order by `level`,`sort`");
    foreach ($sql as $data) {
		$sel = ($data['no'] == $_GET['cno'] || $data['no'] == $_GET['mno']) ? 'selected' : '';
		${'item_'.$data['ctype']} .= "\n\t<option value='$data[no]' $sel>".stripslashes($data['name'])."</option>";
	}

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

?>
<!-- 검색 폼 -->
<form name="prdTrashFrm" id="prdTrashFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<input type="hidden" name="detail_search" value="<?=$detail_search?>">
	<div class="box_title first">
		<h2 class="title">상품 휴지통</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">매장분류</th>
				<td>
					<select name="big" onchange="chgCateInfinite(this, 2, '')">
						<option value="">::대분류::</option>
						<?=$item_1_1?>
					</select>
					<select name="mid" onchange="chgCateInfinite(this, 3, '')">
						<option value="">::중분류::</option>
						<?=$item_1_2?>
					</select>
					<select name="small" onchange="chgCateInfinite(this, 4, '')">
						<option value="">::소분류::</option>
						<?=$item_1_3?>
					</select>
					<?if($cfg['max_cate_depth'] >= 4) {?>
					<select name="depth4">
						<option value="">::세분류::</option>
						<?=$item_1_4?>
					</select>
					<?}?>
					<label class="p_cursor" style="display:none;"><input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외</label>
				</td>
				<th scope="row"><?=$_ctitle[2]?></th>
				<td>
					<select name="cno">
						<option value="">:: 기획전 :;</option>
						<?=$item_2?>
					</select>
					<select name="mno">
						<option value="">:: 모바일기획전 :;</option>
						<?=$item_6?>
					</select>
				</td>
			</tr>
			<?if($cfg['xbig_mng'] == "Y"){?>
			<tr>
				<th scope="row"><?=$cfg['xbig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="xbig" onchange="chgCateInfinite(this, 2, 'x')">
						<option value="">::대분류::</option>
						<?=$item_4_1?>
					</select>
					<select name="xmid" onchange="chgCateInfinite(this, 3, 'x')">
						<option value="">::중분류::</option>
						<?=$item_4_2?>
					</select>
					<select name="xsmall" onchange="chgCateInfinite(this, 4, 'x')">
						<option value="">::소분류::</option>
						<?=$item_4_3?>
					</select>
					<?if($cfg['max_cate_depth'] >= 4) {?>
					<select name="xdepth4">
						<option value="">::세분류::</option>
						<?=$item_4_4?>
					</select>
					<?}?>
				</td>
			</tr>
			<?}?>
			<?if($cfg['ybig_mng'] == "Y"){?>
			<tr>
				<th scope="row"><?=$cfg['ybig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="ybig" onchange="chgCateInfinite(this, 2, 'y')">
						<option value="">::대분류::</option>
						<?=$item_5_1?>
					</select>
					<select name="ymid" onchange="chgCateInfinite(this, 3, 'y')">
						<option value="">::중분류::</option>
						<?=$item_5_2?>
					</select>
					<select name="ysmall" onchange="chgCateInfinite(this, 4, 'y')">
						<option value="">::소분류::</option>
						<?=$item_5_3?>
					</select>
					<?if($cfg['max_cate_depth'] >= 4) {?>
					<select name="ydepth4">
						<option value="">::세분류::</option>
						<?=$item_5_4?>
					</select>
					<?}?>
				</td>
			</tr>
			<?}?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 상품이 상품 휴지통에서 검색되었습니다.
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
	</dl>
</div>
<!-- //정렬 -->

<!-- 검색 테이블 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col tbl_col2">
		<caption class="hidden">상품수정/관리 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:40px">
			<col style="width:70px">
			<col>
			<?if($cfg['prd_reg_date'] == "Y") {?>
			<col style="width:90px">
			<?}?>
			<col style="width:90px">
			<?if($cfg['prd_normal_prc'] == 'Y'){?>
			<col style="width:90px">
			<?}?>
			<col style="width:90px">
			<col style="width:90px">
			<col style="width:65px">
			<?if($cfg['use_trash_prd'] == "Y" && $cfg['trash_prd_trcd'] > 0) {?>
			<col style="width:90px">
			<?}?>
			<col style="width:90px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">이미지</th>
				<th scope="col">상품명</th>
				<?if($cfg['prd_reg_date'] == "Y") {?>
				<th scope="col">등록일</th>
				<?}?>
				<th scope="col"><?=$cfg['product_sell_price_name']?></th>
				<?if($cfg['prd_normal_prc'] == 'Y'){?>
				<th scope="col"><?=$cfg['product_normal_price_name']?></th>
				<?}?>
				<th scope="col">적립금</th>
				<th scope="col">상태</th>
				<th scope="col">판매설정</th>
				<th scope="col">삭제일자</th>
				<?if($cfg['use_trash_prd'] == "Y" && $cfg['trash_prd_trcd'] > 0) {?>
				<th scope="col">삭제 예정일</th>
				<?}?>
				<th scope="col">삭제 처리자</th>
			</tr>
		</thead>
		<?include $engine_dir."/_manage/product/product_list.inc.php";?>
	</table>
</form>
<!-- //검색 테이블 -->

<?if($admin['level'] > 3) return;?>

<!-- 페이징 & 버튼 -->
<div class="box_bottom">
	<?=$pageRes?>
	<div class="left_area">
		<span class="box_btn"><input type="button" value="선택복구" onclick="restorePrd();"></span>
	</div>
	<div class="right_area">
		<span class="box_btn gray"><input type="button" value="휴지통 비우기" onclick="deletePrd(true);"></span>
		<span class="box_btn gray"><input type="button" value="영구삭제" onclick="deletePrd();"></span>
	</div>
</div>
<!-- //페이징 & 버튼 -->
<script type="text/javascript">
var f = document.getElementById('prdFrm');
function restorePrd() {
	if(!checkCB(f.check_pno,"복구할 상품을 선택하세요.")) return;
	if(confirm('선택한 상품을 모두 복구하시겠습니까?\n복구한 상품은 삭제 전 상태로 상품 조회 페이지에 나타납니다.')) {
		f.body.value = 'product@product_update.exe';
		f.exec.value = 'restore';
		f.submit();
	}
}

function deletePrd(trnc) {
	if(trnc != true && $(':checked[name="check_pno[]"]').length < 1) {
		window.alert('영구삭제할 상품을 선택해 주세요.');
		return false;
	}

	var del_msg = '선택한 모든 상품(이미지 포함)이 영구삭제됩니다.\n영구삭제된 상품은 절대 복구할 수 없습니다.\n선택한 모든 상품을 정말로 영구삭제하시겠습니까?';
	if(trnc == true) {
		del_msg = '휴지통을 비우면 휴지통의 모든 상품(이미지 포함)이 영구삭제됩니다.\n영구삭제된 상품은 절대 복구할 수 없습니다.\n정말로 상품 휴지통을 비우시겠습니까?';
	}
	if(confirm(del_msg)) {
		f.body.value = 'product@product_update.exe';
		f.exec.value = (trnc == true) ? 'truncate' : 'delete';
		f.submit();
	}
}
</script>