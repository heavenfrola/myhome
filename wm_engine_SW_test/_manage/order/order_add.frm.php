<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품 추가
	' +----------------------------------------------------------------------------------------------+*/

	$_search_type['name']='상품명';
	$_search_type['content2']='내용';
	$_search_type['keyword']='검색 키워드';
	$_search_type['code']='상품 코드';
	$_search_type['hash']='시스템 코드';
	$_search_type['seller']='사입처';
	$_search_type['origin_name']='장기명';
	$_search_type['mng_memo']='관리자 메모';
	$cate_sprit = ' &gt; ';

	// 매장분류
	$only_cate = $_GET['only_cate'];
	if($only_cate) {
		for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if(!$cval) $cval = 0;
			$w .= " and p.$cl='$cval'";
		}
	} else {
		for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if($cval) {
				$w .= " and p.$cl='$cval'";
				break;
			}
		}
	}

	// 상태
	$_prd_stat = array('2' => '정상', '3' => '품절', '4' => '숨김');

	$w.=" and `wm_sc`='0'";


	// 검색어
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str!="") {
		$_search_str = addslashes($search_str);
		$w.=" and `$search_type` like '%$_search_str%'";
	}

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);

	// 카테고리 검색
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
	}
	$res = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($res as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ($_GET[$cl] == $cate['no']) ? "selected" : "";
		${"item_1_".$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 10;
	if($row > 100) $row = 100;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from wm_product p where stat not in (1, 5) $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$pageRes = $PagingResult['PageLink'];

	$add_field = '';
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", depth4";
	}
	if($cfg['use_partner_shop'] == 'Y') {
		$add_field .= ', partner_no';
	}
	$list_sql = "select no, hash, name, big, mid, small, seller, origin_name, stat, sell_prc, updir, upfile3, w3, h3 $add_field ".
				"from $tbl[product] p where stat not in (1, 5) $w ".
				"order by no desc ".$PagingResult['LimitQuery'];
	$res = $pdo->iterator($list_sql);
	$listURL = urlencode(getURL());

	$_cname_cache = getCategoriesCache(1);

?>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h3 class="title">상품추가</h3>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품추가</caption>
		<colgroup>
			<col style="width:20%">
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
				<label class="p_cursor"><input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
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
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col tbl_col_bottom">
		<caption class="hidden">상품 리스트</caption>
		<colgroup>
			<col>
			<col style="width:200px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">상품명</th>
				<th scope="col">사입처</th>
				<th scope="col">상태</th>
				<th scope="col"><?=$cfg['product_sell_price_name']?></th>
				<th scope="col">선택</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					$imgstr = '';
					$file_dir = getFileDir($data[updir]);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}

					$productname = stripslashes(strip_tags($data['name']));
					$category_name = makeCategoryName($data, 1);
					$data['seller'] = stripslashes($data['seller']);
					$data['origin_name'] = stripslashes($data['origin_name']);
					$data['stat'] = $_prd_stat[$data['stat']];

					$partner_name = getPartnerName($data['partner_no']);
					if(!$partner_name) $partner_name = $cfg['company_mall_name'];
			?>
			<tr>
				<td class="left">
					<div class="box_setup btn_none">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$productname?></a></dt>
							<dd class="cstr"><?=$data['origin_name']?></dd>
							<dd class="cstr"><?=$categoryname?></dd>
							<?if($data['partner_no'] > 0) {?>
							<dd class="p_color4" ><strong><?=$partner_name?></strong></dd>
							<?}?>
						</dl>
					</div>
				</td>
				<td><?=$data['seller']?></td>
				<td><?=$data['stat']?></td>
				<td><?=parsePrice($data['sell_prc'], true)?></td>
				<td><span class="box_btn_s blue"><input type="button" value="선택" onclick="selectAddPrd(<?=$data['no']?>)"></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="pop_bottom"><?=$pageRes?></div>
</form>

<script type="text/javascript">
	function selectAddPrd(no) {
		if(opener.closed) {
			window.alert('주문상세 윈도우를 찾을 수 없습니다.\n창을 닫고 다시 처리 해 주십시오.');
			return;
		}
		$('#prdFrm', opener.document)[0].ext.value = no;
		opener.ordExecFromPrdbox('prdAdd');
		opener.focus();
		self.close();
	}
</script>