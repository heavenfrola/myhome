<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_manage/erp/stock_adjust_list_search.inc.php';

	$xls_query = makeQueryString('body', 'exec');
	$qs_without_row = makeQueryString(true, 'row');
	$qs_without_sort = makeQueryString(true, 'sort');
	for($i = 1; $i <= 3; $i++) {
		$var1 = ($i-1)*2;
		$var2 = $var1+1;
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

	$NumTotalRec = $pdo->row("select count(*) from erp_complex_option b inner join erp_inout c using(complex_no) inner join $tbl[product] a on a.no=b.pno where a.stat!=1 and b.del_yn='N'".$w);

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

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
	$listURL = urlencode(getURL());

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<!-- 검색 폼 -->
<form id="search" name="prdSearchFrm" style="margin-bottom:35px;">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h2 class="title">재고조정 내역 조회</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">재고조정 내역 조회</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">조정일자</th>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" readonly class="input datepicker">
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date, 'Y')?> onClick="searchDate(this.form)"> 전체 기간</label>
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
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type, 'search_type', 2, '', $search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 조정이력이 검색되었습니다
	<span class="box_btn_s btns icon excel"><a href="./?body=erp@stock_adjust_list_excel.exe<?=$xls_query?>">엑셀다운</a></span>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			조정이력수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row, 10, 1)?>>10</option>
				<option value="20" <?=checked($row, 20, 1)?>>20</option>
				<option value="30" <?=checked($row, 30, 1)?>>30</option>
				<option value="50" <?=checked($row, 50, 1)?>>50</option>
				<option value="70" <?=checked($row, 70, 1)?>>70</option>
				<option value="100" <?=checked($row, 100, 1)?>>100</option>
			</select>
		</dd>
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<!-- //정렬 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">재고조정 내역 조회 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:70px">
			<col>
			<col style="width:120px">
			<col style="width:120px">
			<col style="width:80px">
			<col style="width:60px">
			<col style="width:60px">
			<col>
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick='$(":checkbox[name=\"no[]\"]").attr("checked", this.checked)'></th>
				<th scope="col"><a href='<?=$sort1?>'>조정일자 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></th>
				<th scope="col"><a href="<?=$sort2?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
				<th scope="col"><a href="<?=$sort3?>">바코드 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col">옵션</th>
				<th scope="col">조정전재고</th>
				<th scope="col">차이</th>
				<th scope="col">조정재고</th>
				<th scope="col">조정사유</th>
				<th scope="col">조정자</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/$data[updir]/{$data[upfile3]}' $is[2]>";
					} else {
						$imgstr = '';
					}

					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					$category_name = makeCategoryName($data, 1);

					$data['qty'] = ($data['inout_kind'] == 'U' || $data['inout_kind'] == 'I') ? '+'.$data['qty'] : '-'.$data['qty'];
					$data['stock_qty'] = $data['prev_qty']+$data['qty'];
			?>
			<tr>
				<td><input type="checkbox" name="no[]" value="<?=$data['qty']?>@<?=$data['stock_qty']?>@<?=$data['barcode']?>"></td>
				<td><?=$data['reg_date'] == "1900-01-01 00:00:00" ? "기초재고<br>등록" : $data['reg_date']?></td>
				<td class="left">
					<input type="hidden" name="pno[]" value="<?=$data['no']?>">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=number_format($data['prev_qty'])?></td>
				<td style="color:<?=($data['qty'] > 0) ? 'blue' : 'red'?>;"><?=number_format($data['qty'])?></td>
				<td><?=number_format($data['stock_qty'])?></td>
				<td class="left"><?=stripslashes($data['remark'])?></td>
				<td><?=$data['reg_user']?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn"><input type="button" value="재고증가 바코드출력" onclick="barcode(1)"></span>
			<span class="box_btn"><input type="button" value="조정재고 바코드출력" onclick="barcode(2)"></span>
		</div>
		<?=$pageRes?>
	</div>
</form>

<script type="text/javascript">
	var f = document.getElementById('search');
	function barcode(mode) {
		if($(":checked[name='no[]']").length < 1) {
			window.alert('바코드 출력할 상품을 선택 해 주십시오.');
			return false;
		}
		window.open('', 'barcode_print', 'status=no, scrollbars=yes');
		var f = document.getElementById('prdFrm');
		f.body.value = 'erp@barcode_print.exe';
		f.exec.value = 'in_barcode'+mode;
		f.target = 'barcode_print';
		f.submit();
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

	searchDate(f);
	searchDate2(f);
</script>