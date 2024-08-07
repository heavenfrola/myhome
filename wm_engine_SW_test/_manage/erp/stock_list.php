<?PHP

	$pno = numberOnly($_GET['pno']);
	$option = $_GET['option'];

	// 상품검색 가공
	if($pno) $w .=" and a.no='$pno'";
	if($option) {
		$tempoption = explode (",", $option);
		if(is_array($tempoption)) {
			foreach($tempoption as $key=>$val) {
				$tempwhere[] = " b.opts like '%_".addslashes($val)."_%' ";
			}
			$tempwhere = implode(' and ',$tempwhere);
			$w .= ' and (' . $tempwhere . ')' ;
		}
	}

	include_once $engine_dir."/_manage/erp/stock_search.inc.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	// 엑셀용
    $qs = makeQueryString('page');
	$xls_query = preg_replace('/&body=[^&]+/','',$qs); // 2007-10-29 - Han
	$xls_query = str_replace("&body=$pgCode","",$xls_query); // 2008-09-26 - Han
	$xls_query = preg_replace("/&?exec=[^&]+/","",$xls_query); // 2008-09-26 - Han

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
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

	$NumTotalRec = 0;
	if($_GET['exec']) {
        $NumTotalRec = $pdo->rowCount($list_sql);
    }


	// 페이징 처리
	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block=10;

	foreach($_GET as $key=>$val) {
		if($key!="page") {
			if(is_array($val)) {
				foreach($val as $akey => $aval) {
					$QueryString.="&{$key}[]=".urlencode($aval);
				}
			}
			else $QueryString.="&".$key."=".urlencode($val);
		}
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$list_sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];

	$res = array();
	if($_GET['exec']) $res = $pdo->iterator($list_sql);

	$listURL = urlencode(getURL());
	$view_link = "shop";
	$edit_link = "erp@stock_detail";

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<!-- 검색 폼 -->
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h2 class="title">실시간재고 조회</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">실시간재고 조회</caption>
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
				<?if($cfg['max_cate_depth'] >= 4) {?>
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
				<?}?>
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
			<th scope="row">검색옵션</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="shortage" value="Y" <?=checked($shortage,"Y")?>> 부족재고만 조회</label>
				<label class="p_cursor"><input type="checkbox" name="outtoday" value="Y" <?=checked($outtoday,"Y")?>> 금일 출고</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품상태</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="stat[]" value="2" <?=checked(in_array(2, $stat), true)?>> 정상</label>
				<label class="p_cursor"><input type="checkbox" name="stat[]" value="3" <?=checked(in_array(3, $stat), true)?>> 품절</label>
				<label class="p_cursor"><input type="checkbox" name="stat[]" value="4" <?=checked(in_array(4, $stat), true)?>> 숨김</label>
			</td>
		</tr>
		<tr>
			<th scope="row">품절방식</th>
			<td>
				<?=$force_select?>
			</td>
		</tr>
		<tr>
			<th scope="row">주문 상품</th>
			<td>
				<span class="box_btn_s"><input type="button" value="찾기" onclick="searchPrd(this)"></span>
				<span class="box_btn_s"><input type="button" value="검색취소" onclick="$('#search_prd').html('')"></span>
				<div id="search_prd" style="margin: 5px 0; ">
					<?if($pno) include $engine_dir.'/_manage/erp/stock_product_search.exe.php';?>
				</div>
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

<?if(!$_GET['exec']) {?>
<br>
<div class="box_middle4 p_color2">
	검색조건을 입력하신후 검색버튼을 클릭해주세요.
</div>
<?}?>

<script type="text/javascript">
<!--

	var psearch = new layerWindow('product@product_inc.exe&exec=stock_list');
	psearch.psel = function(pno) {
		var doc;
		var elementOption;
		var option = Array();

		doc = $(document);
		elementOption = doc.find("select[name='option[" + pno + "]']");

		if(elementOption.size()>0) {
			elementOption.each( function( i, item ) {
				if($(this).val()) option.push($(this).val().split('::')[3]);
			});
		}

		$.post('?body=erp@stock_product_search.exe', {"exec":"stock_list", "pno":pno, "option":option.join(',')}, function(data) {
            if (data == 'error') {
                window.alert('옵션을 선택해주세요.');
                return false;
            }
			$('#search_prd').html(data);
		})
		this.close();
	}

	function searchPrd(obj) {
		psearch.input = obj;
		psearch.open();
	}
//-->
</script>

<!-- //검색 폼 -->
<? if(!$_GET['exec']) return; //처음들어오면 여기까지 보이게 처리?>
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다.
	<span class="box_btn_s btns icon excel"><a href="./?body=erp@stock_excel.exe<?=$xls_query?>">엑셀다운</a></span>
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
<!-- //정렬 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="height:100%">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">실시간재고 조회 리스트</caption>
		<colgroup>
			<col>
			<col style="width:140px">
			<col style="width:140px">
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:65px">
			<?if($cfg['erp_hit_orders'] == 'Y') {?>
			<col style="width:65px">
			<?}?>
			<col style="width:65px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><a href="<?=$sort3?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col">옵션</th>
				<th scope="col"><a href="<?=$sort4?>">바코드 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="col">전일재고</th>
				<th scope="col">입고</th>
				<th scope="col">출고</th>
				<th scope="col">현재고</th>
				<th scope="col">출고예정</th>
				<?if($cfg['erp_hit_orders'] == 'Y') {?>
				<th scope="col">총주문수</th>
				<?}?>
				<th scope="col">품절방식</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {

				// 이미지 파일명
				$imgstr = '';
				if(!$file_dir && $data['upfile3']) $file_dir = getFileDir($data[updir]);
				if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
					$is = setImageSize($data['w3'], $data['h3'], 50, 50);
					$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
				}

				$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
				$category_name = makeCategoryName($data, 1);

				$data['in_qty'] = $pdo->row("select sum(qty) from erp_inout where complex_no='$data[complex_no]' and reg_date >= '$_todate' and inout_kind in ('I', 'U')");
				switch($qty_now) {
					case 0: $qty_css = 'qty_zero'; break;
					case ($qty_now > 0) : $qty_css = 'qty_over'; break;
					case $qty_now < 0 : $qty_css = 'qty_min'; break;
				}

				switch($data['force_soldout']) {
					case 'Y' : $out_status = "<span class='desc3'>$_erp_force_stat[Y]</span>"; break;
					case 'L' : $out_status = "<span style='color:#00cc00'>$_erp_force_stat[L]</span>"; break;
					case 'N' : $out_status = $_erp_force_stat['N']; break;
				}

				$tmp = $pdo->assoc("select sum(a.buy_ea) as hit_orders, sum(if(a.stat between $cfg[erp_timing] and 3, a.buy_ea, 0)) as dlv_est from $tbl[order_product] a inner join $tbl[order] b using(ono) where a.stat between 1 and 5 and a.complex_no='$data[complex_no]'");
				$data['hit_orders'] = $tmp['hit_orders'];
				$data['dlv_est'] = $tmp['dlv_est'];

				if($data['storage_no'] > 0) {
					$data['storage_name'] = $pdo->row("select name from $tbl[erp_storage] where no='$data[storage_no]'");
					$data['storage_name'] = stripslashes($data['storage_name']);
				}

			?>
			<tr>
				<td class="left">
					<input type="hidden" name="pno[]" value="<?=$data[no]?>">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="./?body=<?=$edit_link?>&pno=<?=$data['complex_no']?>&listURL=<?=$listURL?>"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
							<dd class="cstr"><?=$data['storage_name']?></dd>
						</dl>
						<span class="box_btn_s btnp"><a href="./?body=<?=$edit_link?>&pno=<?=$data['complex_no']?>&listURL=<?=$listURL?>">상세</a></span>
					</div>
				</td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=$data['barcode']?></td>
				<td class="bignumber"><?=number_format($data['prev_qty'])?></td>
				<td class="bignumber"><?=number_format($data['in_qty'])?></td>
				<td class="bignumber"<?=$data[out_qty] > 0 ? "style='color:red;'" : ""?>><?=number_format($data['out_qty'])?></td>
				<td class="<?=$qty_css?> bignumber"><?=number_format($data['stock_qty'])?></td>
				<td class="bignumber"><?=number_format($data['dlv_est'])?></td>
				<?if($cfg['erp_hit_orders'] == 'Y') {?>
				<td class="bignumber"><?=number_format($data['hit_orders'])?></td>
				<?}?>
				<td><?=$out_status?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pageRes?>
	</div>
</form>