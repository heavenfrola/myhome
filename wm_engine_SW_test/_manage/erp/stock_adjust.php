<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_manage/erp/stock_adjust_search.inc.php';

	if(!isTable($tbl['erp_storage'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['erp_storage']);
		$pdo->query("alter table $tbl[product] add storage_no int(10) not null default '0'");
		$pdo->query("alter table $tbl[product] add index storage_no(storage_no)");
	}

	$xls_query = makeQueryString('body', 'exec');

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$sort = numberOnly($_GET['sort']);
	if($sort === '') $sort = 0;
	for ($i = 1; $i <= 4; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	// 카테고리 검색
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
	}
	$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($sql as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ($_GET[$cl] == $cate['no']) ? 'selected' : '';
		${'item_'.$cate['ctype'].'_'.$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}

	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype` in (2, 6) order by `level`,`sort`");
    foreach ($sql as $data) {
		$sel = ($data['no'] == $_GET['ebig'] || $data['no'] == $_GET['mbig']) ? 'selected' : '';
		${'item_'.$data['ctype']} .= "\n\t<option value='$data[no]' $sel>".stripslashes($data['name'])."</option>";
	}

	$shortage = ($_GET['shortage'] == 'Y') ? 'Y' : 'N';
	if($shortage == 'Y') $w .= " and curr_stock(b.complex_no) <= 0";

	// 페이징 처리
	if($_GET['exec'] == 'search') {
		$sql = "select count(*) from `wm_product` a inner join `erp_complex_option` b on a.no = b.pno where a.stat in (2,3,4) and b.del_yn = 'N' $w";
		$NumTotalRec = $pdo->row($sql);

		include $engine_dir.'/_engine/include/paging.php';

		$page = numberOnly($_GET['page']);
		$row = numberOnly($_GET['row']);
		if($page <= 1) $page = 1;
		if(!$row) $row = 20;
		if($row > 100) $row = 100;
		$block=10;

		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString(makeQueryString('page'));
		$PagingResult = $PagingInstance->result($pg_dsn);

		$list_sql .= $PagingResult['LimitQuery'];
		$pageRes = $PagingResult['PageLink'];

		$res = $pdo->iterator($list_sql);

		$view_link = "shop";
		$edit_link = "erp@stock_detail";
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<!-- 검색 폼 -->
<form id="search" name="prdSearchFrm" style="margin-bottom:35px;">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h2 class="title">재고 조정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">재고 조정</caption>
		<colgroup>
			<col style="width:15%">
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
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">기획전</th>
			<td>
				<select name="ebig">
					<option value="">::기획전::</option>
					<?=$item_2?>
				</select>
				<select name="mbig">
					<option value="">::모바일 기획전::</option>
					<?=$item_6?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">상품등록일</th>
			<td>
				<input type="text" name="start_rdate" value="<?=$start_rdate?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="finish_rdate" value="<?=$finish_rdate?>" size="10" readonly class="input datepicker">
				<label class="p_cursor"><input type="checkbox" name="all_date2" value="Y" <?=checked($all_date2, 'Y')?> onClick="searchDate2(this.form)"> 전체 기간</label>
			</td>
		</tr>
		<tr>
			<th scope="row">부족재고</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="shortage" value="Y" <?=checked($shortage,"Y")?>> 부족재고만 조회</label>
			</td>
		</tr>
		<tr>
			<th scope="row">품절방식</th>
			<td>
				<?=$force_select?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>
<!-- //검색 폼 -->
<!-- //검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다
	<span class="box_btn_s btns icon excel"><a href="./?body=erp@stock_adjust_excel.exe<?=$xls_query?>">엑셀다운</a></span>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			상품수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
		<dd><a href="<?=$sort1?>">수정일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></dd>
		<dd><a href="<?=$sort2?>">등록일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></dd>
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>

<?if($_GET['exec'] != 'search') {?>
<br>
<div class="box_middle4 p_color2">
	검색조건을 입력하신후 검색버튼을 클릭해주세요.
</div>
<?} else {?>
<!-- //정렬 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@stock_adjust.exe">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">재고 조정 리스트</caption>
		<colgroup>
			<col style="width:60px;">
			<col>
			<col style="width:120px">
			<col style="width:130px">
			<col style="width:120px">
			<col style="width:60px">
			<col style="width:80px">
			<col style="width:80px">
			<?if($cfg['erp_force_limit'] == 'Y') {?>
			<col style="width:80px">
			<?}?>
			<col style="width:230px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="$(':checkbox[name^=complex_no]').attr('checked', this.checked)"></th>
				<th scope="col"><a href="<?=$sort3?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col">옵션</th>
				<th scope="col"><a href="<?=$sort4?>">바코드 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="col">품절방식</th>
				<th scope="col">현재고</th>
				<th scope="col">조정재고</th>
				<th scope="col">안전재고</th>
				<?if($cfg['erp_force_limit'] == 'Y') {?>
				<th scope="col">한계재고</th>
				<?}?>
				<th scope="col">조정사유</th>
			</tr>
		</thead>
		<tbody>
			<?PHP

				$idx = -1;
                foreach ($res as $data) {
					$idx++;
					// 이미지 파일명
					$imgstr = '';
					if(!$file_dir && $data['upfile3']) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}
					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					$category_name = makeCategoryName($data, 1);

					if($data['storage_no'] > 0) {
						$data['storage_name'] = $pdo->row("select name from $tbl[erp_storage] where no='$data[storage_no]'");
						$data['storage_name'] = stripslashes($data['storage_name']);
					}

			?>
			<tr>
				<td><input type="checkbox" name="complex_no[<?=$idx?>]" value="<?=$data['complex_no']?>"></td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="#" onclick="viewStockDetail('<?=$data['complex_no']?>'); return false;"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
							<dd class="cstr"><?=$data['storage_name']?></dd>
						</dl>
						<span class="box_btn_s btnp"><a href="#" onclick="viewStockDetail('<?=$data['complex_no']?>'); return false;">상세</a></span>
					</div>
				</td>
				<td class="left"><?=getComplexOptionName($data['opts'])?></td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td>
					<input type="hidden" name="org_soldout[<?=$data['complex_no']?>]" value="<?=$data['force_soldout']?>">
					<select name="force_soldout[<?=$data['complex_no']?>]">
						<option value="N" <?=checked($data['force_soldout'],'N',1)?>><?=$_erp_force_stat['N']?></option>
						<option value="Y" <?=checked($data['force_soldout'],'Y',1)?> style="color:#ff1111;"><?=$_erp_force_stat['Y']?></option>
						<option value="L" <?=checked($data['force_soldout'],'L',1)?> style="color:#00cc00;"><?=$_erp_force_stat['L']?></option>
					</select>
				</td>
				<td><span <?=$data['current_qty'] < 0 ? "style='background-color:yellow;font-weight:bold;'" : ""?>><?=number_format($data['current_qty'])?></span></td>
				<td>
					<input type="hidden" name="org_stock_qty[]" value="<?=$data[current_qty] ? $data[current_qty] : "0"?>">
					<input type="text" name="stock_qty[]" value="" class="input number right" size="4">
				</td>
				<td>
					<input type="hidden" name="org_safe_qty[]" value="<?=$data['safe_stock_qty']?>">
					<input type="text" name="safe_qty[]" value="<?=$data['safe_stock_qty']?>" class="input number right" size="4">
				</td>
				<?if($cfg['erp_force_limit'] == 'Y') {?>
				<td>
					<input type="hidden" name="org_limit_qty[]" value="<?=$data['limit_qty']?>">
					<input type="text" name="limit_qty[]" value="<?=$data['limit_qty']?>" class="input number right" size="4">
				</td>
				<?}?>
				<td><input type="text" name="adjust_reason[]" value="" class="input" size="25"></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<span class="box_btn_s"><input type="button" value="재고조정" onclick="adjust();"></span>
		<div style="float:right;">
			조정자 <input type="text" class="input" disabled value="<?=$admin['name']?>" size="8">
			조정사유 <input type="text" name="reason" value="" class="input" size="24">
			<span class="box_btn_s"><input type="button" value="조정사유 일괄수정" onclick="all_reason();"></span>
		</div>
	</div>
	<div class="box_bottom">
		<?=$pageRes?>
	</div>
</form>
<?}?>

<script type="text/javascript">
	function all_reason() {
		var reason = prdFrm.reason.value;
		var reasons = document.getElementsByName("adjust_reason[]");
		for (var i=0; i<reasons.length; i++) {
			reasons[i].value = reason;
		}
	}
	function adjust() {
		var org_qty = document.getElementsByName("org_stock_qty[]");
		var stock_qty = document.getElementsByName("stock_qty[]");
		var reasons = document.getElementsByName("adjust_reason[]");
		var limit_qty = document.getElementsByName("limit_qty[]");
		var isChk = true;
		for (var i=0; i<reasons.length; i++) {
			if(org_qty[i].value != stock_qty[i].value && stock_qty[i].value != '') {
				if(reasons[i].value.length == 0) {
					alert('조정사유를 꼭 입력해주세요.');
					reasons[i].focus();
					return;
				}
				isChk = false;
			}
		}

		for(var i = 0; i < limit_qty.length; i++) {
			tmp = parseInt(limit_qty[i].value);
			if(tmp > 0) {
				window.alert('한계재고는 마이너스(-)로만 입력 가능합니다.');
				limit_qty[i].focus();
				return false;
			}
		}

		if(!confirm("재고를 조정하시겠습니까?")) return;
        printLoading();
		prdFrm.submit();
	}

	function edtConfirm(f) {
		if(f.where.value == 1) {
			if($(':checked[name=complex_no[]]').length < 1) {
				window.alert('변경할 상품을 선택 해 주십시오.');
				return false;
			}
			var nums = '';
			$(':checked[name=complex_no[]]').each(function() {
				if(nums == '') nums += this.value;
				else nums += ','+this.value;
			});
			f.nums.value = nums;
		}
	}

	function searchDate2(f){
		if(f.all_date2.checked == true) {
			textDisable(f.start_rdate, 1);
			textDisable(f.finish_rdate, 1);
		} else {
			textDisable(f.start_rdate, '');
			textDisable(f.finish_rdate, '');
		}
	}

	searchDate2(document.getElementById('search'));

	$('input[name^=limit_qty]').focus(function() {
		this.select();
	});
</script>