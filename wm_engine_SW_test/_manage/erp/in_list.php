<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_manage/erp/in_list_search.inc.php";

	// 사입처 데이터
	$_seller = array();
	$prql = $pdo->iterator("select no, provider, plocation from $tbl[provider] order by plocation !='' desc, plocation asc, provider asc");
    foreach ($prql as $prdata) {
		$_provider = stripslashes($prdata['provider']);
		$_plocation = stripslashes($prdata['plocation']);
		$_seller[$prdata['no']] = ($_plocation) ? '['.$_plocation.'] '.$_provider : $_provider;
	}

	// 엑셀용
	foreach($_GET as $key=>$val) {
		if($key != 'page') $qs .= '&'.$key.'='.$val;
	}
	$xls_query = preg_replace('/&body=[^&]+/', '', $qs);
	$xls_query = str_replace('&body='.$pgCode, '', $xls_query);
	$xls_query = preg_replace('/&?exec=[^&]+/', '', $xls_query);

	// 정렬 및 상품수
	$sort = numberOnly($_GET['sort']);
	if(!$sort) $sort = 0;
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	for($i = 1; $i <= 4; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	// 카테고리 출력
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
	}
	$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($sql as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ($_GET[$cl] == $cate['no']) ? 'selected' : '';
		${'item_'.$cate['ctype'].'_'.$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}

    $NumTotalRec = $pdo->row("select count(1)" .
		   "  from wm_product a, erp_complex_option b, erp_inout c, wm_provider d" .
		   " where a.stat in (2, 3, 4) and a.no = b.pno and c.inout_kind = 'I'" .
		   "   and b.complex_no = c.complex_no and c.sno = d.no and b.del_yn = 'N'" .
		   $w
   );

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block = 10;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$list_sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];

	$res = $pdo->iterator($list_sql);
	$listURL = urlencode(getURL());

?>
<!-- 검색 폼 -->
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h2 class="title">입고내역 조회</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">입고내역 조회</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">입고일자</th>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" readonly class="input datepicker">
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
			</td>
			<th scope="row">사입처</th>
			<td><?=selectArray($_seller, "seller", 2, "::전체::", $seller)?></td>
		</tr>
		<tr>
			<th scope="row">매장분류</th>
			<td colspan="3">
				<select name="big" onchange="chgCateInfinite(this, 2, '');">
					<option value="">::대분류::</option>
					<?=$item_1_1?>
				</select>
				<select name="mid" onchange="chgCateInfinite(this, 3, '');">
					<option value="">::중분류::</option>
					<?=$item_1_2?>
				</select>
				<select name="small" onchange="chgCateInfinite(this, 4, '');">
					<option value="">::소분류::</option>
					<?=$item_1_3?>
				</select>
				<?if($cfg['max_cate_depth'] >= 4) {?>
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
				<?}?>
				<input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외
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
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 입고상품이 검색되었습니다
	<span class="box_btn_s btns icon excel"><a href="./?body=erp@in_list_excel.exe<?=$xls_query?>">엑셀다운</a></span>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			입고수
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
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<!-- //정렬 -->
<!-- 검색 테이블 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">입고내역 조회 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:130px">
			<col style="width:100px">
			<col>
			<col style="width:140px">
			<col style="width:100px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="row"><input type="checkbox" onclick="checkAll(document.prdFrm.check_ino,this.checked)"></th>
				<th scope="row"><a href="<?=$sort1?>">입고일자 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></th>
				<th scope="row"><a href="<?=$sort2?>">사입처 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
				<th scope="row"><a href="<?=$sort3?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="row"><a href="<?=$sort4?>">바코드 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="row">옵션</th>
				<th scope="row">입고수량</th>
				<th scope="row">입고단가</th>
				<th scope="row">입고금액</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$data['provider'] = stripslashes($data['provider']);
					$data['name'] = stripslashes($data['name']);

					$imgstr = '';
					if(!$file_dir && $data['updir']) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}
					$productname = $data['name'];
					$category_name = makeCategoryName($data, 1);
			?>
			<tr>
				<td><input type="checkbox" name="check_ino[]" id="check_ino" value="<?=$data['inout_no']?>"></td>
				<td><?=$data['reg_date']?></td>
				<td><?=$data['provider']?></td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;', 'stock', true, 800, 700); return false;"><?=$data['barcode']?></a></td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=number_format($data['qty'])?></td>
				<td><?=number_format($data['in_price'])?></td>
				<td><?=number_format($data['qty'] * $data['in_price'])?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pageRes?>
		<span class="box_btn gray left_area"><input type="button" value="바코드출력" onclick="barcode();"></span>
	</div>
</form>
<!-- //검색 테이블 -->

<script type="text/javascript">
	searchDate(document.getElementById('search'));
	function barcode() {
		var ino = document.getElementsByName("check_ino[]");
		var no;
		for (var i=0; i<ino.length; i++) {
			if(ino[i].checked) {
				if(no) {
					no += ',';
				} else {
					no = '';
				}
				no += ino[i].value;
			}
		}
		if(no) {
			wisaOpen("?body=erp@barcode_print.exe&no="+no,"barcode", "yes");
		} else {
			window.alert('선택 된 상품이 없습니다.');
		}
	}
</script>