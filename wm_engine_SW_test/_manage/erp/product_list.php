<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$_search_type['name']='상품명';
	if($wp_stat > 2) $_search_type['barcode']='바코드';
	$_search_type['content2']='내용';
	$_search_type['keyword']='검색 키워드';
	$_search_type['code']='상품 코드';
	$_search_type['hash']='시스템 코드';
	$_search_type['seller']='사입처';
	$_search_type['origin_name']='장기명';
	$_search_type['mng_memo']='관리자 메모';

	// 매장분류
	for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
		$cl = $_cate_colname[1][$i];
		$cval = numberOnly($_GET[$cl]);
		if($cval) {
			$w .= " and a.$cl='$cval'";
			break;
		}
	}

	// 검색어
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str) {
		$_search_str = addslashes($search_str);
		$w.=" and `$search_type` like '%$_search_str%'";
	}

	// 정렬순서
	$stock_sort = array('a.edt_date desc', 'a.edt_date', 'a.reg_date desc', 'a.reg_date', 'a.name desc', 'a.name', 'b.barcode desc', 'b.barcode');
	$sort = numberOnly($_GET['sort']);
	if($sort == '') $sort = 2;
	if(!$sort_str) $sort_str = $stock_sort[$sort];

	// 실시간 재고 조회
	$add_field = '';
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", depth4";
	}
	$list_sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.origin_prc, a.wm_sc, a.big, a.mid, a.small $add_field " .
		   "      , b.complex_no, b.barcode, b.opts, a.seller_idx " .
		   "      , b.qty as curr_qty" .
		   "      , (select in_price FROM erp_inout x WHERE x.complex_no = b.complex_no and x.inout_kind = 'I' order by x.inout_no desc limit 1) as in_price" .
		   "  from wm_product a inner join erp_complex_option b on a.no=b.pno " .
		   "  inner join erp_inout d using(complex_no) " .
		   " where a.stat in (2, 3, 4) " .
		   "   and b.del_yn = 'N'" .
		   $w .
		   " group by a.no, a.name, a.updir, a.upfile3, a.w3, a.h3" .
		   "     , b.barcode " .
		   " order by {$sort_str}";

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
		$sel = ($_GET[$cl] == $cate['no']) ? "selected" : "";
		${"item_1_".$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}

	$sql = "select count(*)" .
		   "  from $tbl[product] a, erp_complex_option b " .
		   " where a.stat in (2, 3, 4) and a.no = b.pno" .
		   "   and b.del_yn = 'N'" .
		   $w;

	$NumTotalRec = $pdo->row($sql);

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 10;
	if($row > 100) $row = 100;
	$block=10;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$list_sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];

	$res = $pdo->iterator($list_sql);
	$listURL = urlencode(getURL());
	$view_link = "shop";
	$edit_link = "erp@stock_detail";

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript">
var json = new Array();
</script>
<!-- 검색폼 -->
<form id="search" name="prdSearchFrm" class="pop_width">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">매장분류</th>
			<td>
				<select name="big" onchange="chgCate(this,'mid','small')">
					<option value="">::대분류::</option>
					<?=$item_1_1?>
				</select>
				<select name="mid" onchange="chgCate(this,'small')">
					<option value="">::중분류::</option>
					<?=$item_1_2?>
				</select>
				<select name="small">
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
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="20">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다.
</div>
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
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">검색 리스트</caption>
		<colgroup>
			<col style="width:60px">
			<col>
			<col>
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">선택</th>
				<th scope="col"><a href='<?=$sort3?>'>상품명 <img src='<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif' class='arrow <?=$arrowcolor3?>'></a></th>
				<th scope="col"><a href='<?=$sort4?>'>바코드 <img src='<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif' class='arrow <?=$arrowcolor4?>'></a></th>
				<th scope="col">옵션</th>
				<th scope="col">현재고</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					// 이미지 파일명
					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}
					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					$opt_name = getComplexOptionName($data['opts']);
					$category_name = makeCategoryName($data, 1);
					if(!$data['in_price']) $data['in_price'] = $data['origin_prc'];
					$json = json_encode(array(
						'imgstr' => $imgstr,
						'category_name' => $category_name,
						'productname' => $productname,
						'seller_idx' => $data['seller_idx'],
						'complex_no' => $data['complex_no'],
						'in_price' => $data['in_price'],
						'complex_option_name' => $opt_name,
						'barcode' => $data['barcode'],
						'current_qty' => $data['curr_qty'],
					));

			?>
			<tr>
				<td>
					<script type="text/javascript">
						json[<?=$data['complex_no']?>] = <?=$json?>;
					</script>
					<input type="checkbox" name="selPrd" value="<?=$data['complex_no']?>">
				</td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dd class="title"><a href="#" onclick='opener.setProd(json[<?=$data['complex_no']?>]); self.close(); return false;'><?=$productname?></a></dd>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=$data['barcode']?></td>
				<td><?=$opt_name?></td>
				<td><span <?=$data['curr_qty'] < 0 ? "style='background-color:yellow;font-weight:bold;'" : ""?>><?=number_format($data['curr_qty'])?></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="pop_bottom">
		<?=$pageRes?>
		<div class="left_area">
			<span class="box_btn_s gray"><input type="button" value="복수선택" onclick="multiSelect()"></span>
		</div>
	</div>
</form>

<script type="text/javascript">
	prdSearchFrm.search_str.focus();
	prdSearchFrm.search_str.select();

	function multiSelect() {
		var sel = $(':checked[name=selPrd]');
		sel.each(function() {
			opener.setProd(json[this.value]);
			opener.insProd();
		});
		self.close();
	}
</script>