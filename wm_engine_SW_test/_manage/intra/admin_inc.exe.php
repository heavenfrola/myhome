<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();


	$search_str = mb_convert_encoding(addslashes(trim($_GET['search_str'])), _BASE_CHARSET_, array('utf8', 'euckr'));
	$search_key = addslashes($_GET['search_key']);
	if($search_str) $w .= " and `$search_key` like '%$search_str%'";
	$sql = "select no, admin_id, name from $tbl[mng] where 1 $w  order by reg_date desc";

	include $engine_dir."/_engine/include/paging.php";

	foreach($_GET as $key => $val) {
		if($key != 'page' && $val) $QueryString .= "&$key=".urlencode($val);
	}

	$row = (int) $_GET['row'];
	$page = (int) $_GET['page'];

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

	include_once $engine_dir.'/_manage/manage.lib.php';

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">관리자검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return msearch.fsubmit(this);">
				<input type="hidden" name="body" value="<?=$_GET['body']?>">
				<select name="search_key">
					<option value="admin_id" <?=checked($search_key, 'admin_id', 1)?>>관리자아이디</option>
					<option value="name" <?=checked($search_key, 'name', 1)?>>관리자이름</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=inputText($search_str)?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</form>
		</div>
		<table class="tbl_col">
			<caption class="hidden">관리자검색</caption>
			<colgroup>
				<col style="width:15%">
				<col style="width:15%">
				<col>
			</colgroup>
			<thead>
				<tr>
					<th scope="col">아이디</th>
					<th scope="col">이름</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $data) {
						$json  = "{";
						$json .= "'admin_id':'$data[admin_id]',";
						$json .= "'name':'".addslashes(inputtext($data['name']))."',";
						$json .= "}";
				?>
				<tr>
					<td><a href="javascript:;" onclick="msearch.msel(<?=$json?>)"><strong><?=$data['admin_id']?></strong></a></td>
					<td><?=$data['name']?></td>
				</tr>
				<?php } ?>
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