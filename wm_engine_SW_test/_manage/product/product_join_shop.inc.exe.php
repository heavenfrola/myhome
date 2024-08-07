<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  입점파트너 검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$search_key = addslashes($_GET['search_key']);
	$search_str = mb_convert_encoding(trim($_GET['search_str']), _BASE_CHARSET_, array('utf8', 'euckr'));
	if($search_str && in_array($search_key, array('corporate_name', 'email', 'cell'))) {
		$w .= " and `$search_key` like '%$search_str%'";
	}


	$sql = "select * from `$tbl[partner_shop]` p where stat between 2 and 4 $w order by corporate_name";
	$sql_t = "select count(*) from `$tbl[partner_shop]` where stat between 2 and 4 $w";

	include $engine_dir."/_engine/include/paging.php";

	foreach($_GET as $key => $val) {
		if($key != 'page' && $val) $QueryString .= "&$key=".urlencode($val);
	}

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$NumTotalRec = $pdo->row($sql_t);
	$PagingInstance = new Paging($NumTotalRec, $page, 10, 10);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$group=getGroupName();

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="ptn_search.open(\'$1\')"', $pg_res);

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">파트너검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return ptn_search.fsubmit(this);">
				<input type="hidden" name="body" value="<?=$_GET['body']?>">
				<select name="search_key">
					<option value="corporate_name" <?=checked($search_key, 'corporate_name', 1)?>>파트너명</option>
					<option value="email" <?=checked($search_key, 'email', 1)?>>이메일</option>
					<option value="cell" <?=checked($search_key, 'cell', 1)?>>연락처</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=$search_str?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</form>
		</div>
		<table class="tbl_col">
			<caption class="hidden">주문검색</caption>
			<colgroup>
				<col style="width:80px">
				<col style="width:100px">
				<col>
				<col>
				<col style="width:100px">
				<col style="width:60px">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">파트너명</th>
					<th scope="col">요약정보</th>
					<th scope="col">이메일</th>
					<th scope="col">연락처</th>
					<th scope="col">상태</th>
					<th scope="col">선택</th>
				</tr>
			</thead>
			<tbody>
				<?php
                foreach ($res as $data) {
					$data = array_map('stripslashes', $data);
					if($data['stat']=="1")$data['stat'] = "신청";
					if($data['stat']=="2")$data['stat'] = "정상";
					if($data['stat']=="3")$data['stat'] = "보류";
					if($data['stat']=="4")$data['stat'] = "만료";
				?>
				<tr>
					<td><?=$data['corporate_name']?></td>
					<td><?=$data['title']?></td>
					<td class="left"><?=$data['email']?></td>
					<td class="left"><?=$data['cell']?></td>
					<td><?=$data['stat']?></td>
					<td><span class="box_btn_s blue"><input type="button" value="선택" onclick="ptn_search.psel('<?=$data['no']?>','<?=$data['stat']?>');"></span>
					</td>
				</tr>
				<?}?>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="ptn_search.close()"></span>
	</div>
</div>