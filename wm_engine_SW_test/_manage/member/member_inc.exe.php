<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$search_key = addslashes(trim($_GET['search_key']));
	$search_str = mb_convert_encoding(addslashes(trim($_GET['search_str'])), _BASE_CHARSET_, array('utf8', 'euckr'));
	if($search_str) $w .= " and `$search_key` like '%$search_str%'";
	$sql = "select no, member_id, name, level, cell, phone, email, zip, addr1, addr2 from $tbl[member] where 1 $w  order by reg_date desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$NumTotalRec = $pdo->row("select count(*) from $tbl[member] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, 10, 10);
	$PagingInstance->addQueryString(makeQueryString('page'));
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
		<div id="mngTab_pop">회원검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return msearch.fsubmit(this);">
				<input type="hidden" name="body" value="<?=$_GET['body']?>">
				<select name="search_key">
					<option value="member_id" <?=checked($search_key, 'member_id', 1)?>>회원아이디</option>
					<option value="name" <?=checked($search_key, 'name', 1)?>>회원이름</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=inputText($search_str)?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</form>
		</div>
		<table class="tbl_col">
			<caption class="hidden">회원검색</caption>
			<colgroup>
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
				<col>
			</colgroup>
			<thead>
				<tr>
					<th scope="col">아이디</th>
					<th scope="col">이름</th>
					<th scope="col">등급</th>
					<th scope="col">주소</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $data) {
						$json  = "{";
						$json .= "'member_id':'$data[member_id]',";
						$json .= "'name':'".addslashes(inputtext($data['name']))."',";
						$json .= "'cell':'$data[cell]',";
						$json .= "'phone':'$data[phone]',";
						$json .= "'email':'$data[email]',";
						$json .= "'zip':'$data[zip]',";
						$json .= "'addr1':'".addslashes(inputtext($data['addr1']))."',";
						$json .= "'addr2':'".addslashes(inputtext($data['addr2']))."',";
						$json .= "'addr_name':'',";
						$json .= "'addr_phone':'',";
						$json .= "'addr_cell':''";
						$json .= "}";
				?>
				<tr>
					<td><a href="javascript:;" onclick="msearch.msel(<?=$json?>)"><strong><?=$data['member_id']?></strong></a></td>
					<td><?=$data['name']?></td>
					<td><?=$group[$data['level']]?></td>
					<td class="left"><?=$data['addr1']?> <?=$data['addr2']?></td>
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