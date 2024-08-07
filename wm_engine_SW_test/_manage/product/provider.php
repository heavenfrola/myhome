<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 관리
	' +----------------------------------------------------------------------------------------------+*/
	$_row = array(20 => 20, 30 => 30, 50 => 50, 100 => 100);
	$_search = array("provider" => "사입처명", "ptel" => "전화번호", "pcel" => "휴대폰번호", "plocation" => "위치");
	$_sort = array("no desc", "no asc", "cnt desc", "cnt asc", "provider asc", "provider desc", "plocation asc", "plocation desc", "arcade asc", "arcade desc");

	$search_key = addslashes(trim($_GET['search_key']));
	$search = addslashes(trim($_GET['search']));
	if ($search_key && $search) {
		$w .= " and `$search_key` like '%$search%'";
	}

	$sort = numberOnly($_GET['sort']);
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	if(!$sort) $sort = 0;
	for ($i = 1; $i <= 5; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}
	$order = $_sort[$sort];

	$sql = "select *, (select count(*) from `$tbl[product]` where `seller_idx` = p.`no`) as `cnt` from `$tbl[provider]` p where 1 $w order by $order";
	$sql_t = "select count(*) from `$tbl[provider]` where 1 $w";

	$xls_query = makeQueryString('page', 'body');
	$_SESSION['listURL'] = getURL();

	if($mode == 'xls') return;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=20;

	$NumTotalRec = $pdo->row($sql_t);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	function provider_list() {
		global $res, $pidx;

		$data = $res->current();
        $res->next();
		if ($data == false) return false;

		$data[cnt] = number_format($data[cnt]);

		$pidx++;

		return $data;
	}
?>
<!-- 검색 폼 -->
<form method="get">
	<input type="hidden" name="body" value="<?=$_GET[body]?>">
	<div class="box_title first">
		<h2 class="title">사입처 관리</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search, "search_key", 2, "", $search_key)?>
					</div>
					<div class="area_input">
						<input type="text" name="search" value="<?=inputText($search)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 사입처가 검색되었습니다.
	<div class="btns">
		<span class="box_btn_s icon excel"><a href="?body=product@provider_excel.exe&<?=$xls_query?>">엑셀다운</a></span>
	</div>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
		<dd><a href='<?=$sort1?>'>등록일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></dd>
		<dd><a href="<?=$sort2?>">등록상품수 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></dd>
	</dl>
</div>
<!-- //정렬 -->
<!-- 검색 테이블 -->
<form method="post" name="prdFrm" target="hidden<?=$now?>" class="contentFrm" onsubmit="return providerDel(this)">
	<input type="hidden" name="body" value="product@provider.exe">
	<input type="hidden" name="exec" value="delete">
	<table class="tbl_col">
		<caption class="hidden">사입처 목록</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:120px">
			<col>
			<col style="width:100px">
			<col style="width:100px">
			<col>
			<col style="width:80px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno, this.checked)"></th>
				<th scope="col"><a href="<?=$sort5?>">상가 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir5?>.gif" class="arrow <?=$arrowcolor5?>"></a></th>
				<th scope="col"><a href="<?=$sort3?>">사입처 명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col">전화번호</th>
				<th scope="col">휴대폰</th>
				<th scope="col"><a href="<?=$sort4?>">위치 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="col"><a href="<?=$sort2?>">상품수 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
				<th scope="col">수정</th>
			</tr>
		</thead>
		<tbody>
			<?while($data = provider_list()) {?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
				<td><?=stripslashes($data['arcade'])?></td>
				<td class="left"><a href="?body=product@provider_register&no=<?=$data[no]?>"><strong><?=$data[provider]?></strong></a></td>
				<td><?=$data[ptel]?></td>
				<td><?=$data[pcell]?></td>
				<td><?=$data[plocation]?></td>
				<td><?=$data[cnt]?></td>
				<td><span class="box_btn_s"><input type="button" value="수정" onclick="location.href='?body=product@provider_register&no=<?=$data['no']?>'"></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn gray"><input type="submit" value="선택삭제"></span>
			<span class="box_btn blue"><input type="button" value="등록" onclick="location.href='?body=product@provider_register'"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>
<!-- //검색 테이블 -->

<script type="text/javascript">
	function providerDel(f) {
		if(!checkCB(f.check_pno,"삭제할 사입처를")) return false;
		if (confirm('사입처 삭제시 상품에 정의된 해당 사입처 정보도 같이 삭제됩니다.\n\n정말 삭제하시겠습니까?')) return true;
		else return false;
	}
</script>