<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$level = numberOnly($_GET['level']);
	$search_key = addslashes(trim($_GET['search_key']));
	$search_str = mb_convert_encoding(addslashes(trim($_GET['search_str'])), _BASE_CHARSET_, array('utf8', 'euckr'));
	if($search_str && in_array($search_key, array('admin_id', 'name', 'phone', 'cell'))) {
		$w .= " and `$search_key` like '%$search_str%'";
	}
	if($level) $w .= " and level='$level'";

	$sql = "select no, level, admin_id, name, phone, cell, email, reg_date from $tbl[mng] where 1 $w order by name asc";

	include $engine_dir."/_engine/include/paging.php";

	foreach($_GET as $key => $val) {
		if($key != 'page' && $val) $QueryString .= "&$key=".urlencode($val);
	}

	$page = $_GET['page'];
	if($page<=1) $page=1;
	$NumTotalRec = $pdo->row("select count(*) from $tbl[mng] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, 10, 10);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$group=getGroupName();

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="msearch.open(\'$1\')"', $pg_res);

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">사원검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return msearch.fsubmit(this);">
				<input type="hidden" name="body" value="<?=$_GET['body']?>">
				<select name="search_key">
					<option value="name" <?=checked($search_key, 'name', 1)?>>사원명</option>
					<option value="admin_id" <?=checked($search_key, 'admin_id', 1)?>>사원아이디</option>
					<option value="cell" <?=checked($search_key, 'cell', 1)?>>휴대폰</option>
					<option value="phone" <?=checked($search_key, 'phone', 1)?>>전화번호</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=inputText($search_str)?>">
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
					<th scope="col">이름</th>
					<th scope="col">아이디</th>
					<th scope="col">휴대폰</th>
					<th scope="col">전화번호</th>
					<th scope="col">등록일시</th>
					<th scope="col">선택</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $data) {
						$data = array_map('stripslashes', $data);
						$data['reg_date'] = date('y-m-d H:i', $data['reg_date']);
				?>
				<tr>
					<td><?=$data['name']?></td>
					<td><?=$data['admin_id']?></td>
					<td class="left"><?=$data['cell']?></td>
					<td class="left"><?=$data['phone']?></td>
					<td><?=$data['reg_date']?></td>
					<td><span class="box_btn_s blue"><input type="button" value="선택" onclick="msearch.msel(<?=$data['no']?>);"></span></td>
				</tr>
				<?}?>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="msearch.close()"></span>
	</div>
</div>