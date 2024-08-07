<?PHP

	$w = '';

	$partner_no = numberOnly($_GET['partner_no']);
	if($admin['level'] == 4) $w .= " and a.partner_no='$admin[partner_no]'";
	elseif($partner_no > 0) $w .= " and a.partner_no='$partner_no'";

	if(!$admin['partner_no'] && !isset($_GET['req_stat'])) {
		$_GET['req_stat'] = 1;
	}

	$prd_stat = numberOnly($_GET['prd_stat']);
	$req_stat = numberOnly($_GET['req_stat']);
	if($prd_stat > 0) $w .= " and b.stat='$prd_stat'";
	if($req_stat > 0) $w .= " and a.stat='$req_stat'";

	$all_date1 = addslashes($_GET['alldate1']);
	$all_date2 = addslashes($_GET['alldate2']);
	$reg_dates = addslashes($_GET['reg_dates']);
	$reg_datee = addslashes($_GET['reg_datee']);
	$req_dates = addslashes($_GET['req_dates']);
	$req_datee = addslashes($_GET['req_datee']);
	if(!$reg_dates || !$reg_datee) {
		$all_date1 = 'Y';
		$reg_dates = date('Y-m-d', strtotime('-3 months'));
		$reg_datee = date('Y-m-d');
	}
	if(!$req_dates || !$req_datee) {
		$all_date2 = 'Y';
		$req_dates = date('Y-m-d', strtotime('-3 months'));
		$req_datee = date('Y-m-d');
	}
	if($all_date1 != 'Y') {
		$_reg_dates = strtotime($reg_dates);
		$_reg_datee = strtotime($reg_datee)+86399;
		$w .= " and b.reg_date between $_reg_dates and $_reg_datee";
	}
	if($all_date2 != 'Y') {
		$_req_dates = strtotime($req_dates);
		$_req_datee = strtotime($req_datee)+86399;
		$w .= " and a.reg_date between $_req_dates and $_req_datee";
	}

	$_search_type = array(
		'b.name' => '상품명',
		'a.content' => '변경사유',
	);
	$_search_key = trim(addslashes($_GET['search_type']));
	$_search_str = trim(addslashes($_GET['search_str']));
	if($_search_str && array_key_exists($_search_key, $_search_type)) {
		$w .= " and $_search_key like '%$_search_str%'";
	}

	$sql = "select a.*, b.updir, b.upfile3, b.stat as pstat from $tbl[partner_product_log] a inner join $tbl[product] b on a.pno=b.no where 1 $w order by a.reg_date desc";

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block = 10;

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];

	$NumTotalRec = $pdo->row("select count(*) from $tbl[partner_product_log] a inner join $tbl[product] b on a.pno=b.no where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$listURL = urlencode(getURL());
	$_prd_stat[1] = '등록대기';

	function parseProductLog($res) {
		global $tbl, $_prd_stat, $_partner_prd_stat;

		$data = $res->current();
        $res->next();
		if ($data == false) return false;

		$data = array_map('stripslashes', $data);

		$file_url = getFileDir($data['updir']);
		$data['img'] = $file_url.'/'.$data['updir'].'/'.$data['upfile3'];
		$data['reg_date_s'] = date('Y-m-d H:i', $data['reg_date']);
		$data['stat_s'] = $_partner_prd_stat[$data['stat']];
		$data['pstat'] = $_prd_stat[$data['pstat']];

		return $data;
	}

	$ptns = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat > 1 order by corporate_name asc");
    foreach ($ptns as $ptn) {
		$_partner_names[$ptn['no']] = stripslashes($ptn['corporate_name']);
	}

	$mody_btn_name = ($admin['level'] == 4) ? '수정' : '검토';

?>
<!-- 검색폼 -->
<div class="box_title first">
	<h2 class="title">상품 등록/수정 신청 내역</h2>
</div>
<form id="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">상품상태별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="2" <?=checked($prd_stat, 2)?>> <?=$_prd_stat[2]?></label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="3" <?=checked($prd_stat, 3)?>> <?=$_prd_stat[3]?></label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="4" <?=checked($prd_stat, 4)?>> <?=$_prd_stat[4]?></label>
				</td>
				<th scope="row">등록일</th>
				<td>
					<input type="text" name="reg_dates" value="<?=$reg_dates?>" size="10" class="input datepicker"> ~ <input type="text" name="reg_datee" value="<?=$reg_datee?>" size="10" class="input datepicker">
					<label class="p_cursor"><input type="checkbox" name="all_date1" value="Y" <?=checked($all_date1,"Y")?> onclick='ckAllDate()'> 전체 기간</label>
				</td>
			</tr>
			<tr>
				<th scope="row">신청상태별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="req_stat" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="req_stat" value="5" <?=checked($req_stat, 5)?>> <?=$_partner_prd_stat[5]?></label>
					<label class="p_cursor"><input type="radio" name="req_stat" value="1" <?=checked($req_stat, 1)?>> <?=$_partner_prd_stat[1]?></label>
					<label class="p_cursor"><input type="radio" name="req_stat" value="2" <?=checked($req_stat, 2)?>> <?=$_partner_prd_stat[2]?></label>
					<label class="p_cursor"><input type="radio" name="req_stat" value="3" <?=checked($req_stat, 3)?>> <?=$_partner_prd_stat[3]?></label>
				</td>
				<th scope="row">신청일</th>
				<td>
					<input type="text" name="req_dates" value="<?=$req_dates?>" size="10" class="input datepicker"> ~ <input type="text" name="req_datee" value="<?=$req_datee?>" size="10" class="input datepicker">
					<label class="p_cursor"><input type="checkbox" name="all_date2" value="Y" <?=checked($all_date2,"Y")?> onclick='ckAllDate()'> 전체 기간</label>
				</td>
			</tr>
			<?if(!$admin['partner_no']) {?>
			<tr>
				<th scope="row">입점파트너</th>
				<td>
					<?=selectArray($_partner_names, 'partner_no', 2, ':: 전체 ::', $partner_no)?>
				</td>
			</tr>
			<?}?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 상품이 검색되었습니다.
</div>
<!-- //검색 총합 -->
<table class="tbl_col">
	<colgroup>
		<col style="width:120px">
		<col>
		<col>
		<col>
		<col style="width:90px">
		<col style="width:110px">
		<col style="width:80px">
		<col style="width:80px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col" colspan="2">상품명</th>
			<th scope="col">변경사유</th>
			<th scope="col">관리자 코멘트</th>
			<th scope="col">상품상태</th>
			<th scope="col">신청일시</th>
			<th scope="col">처리상태</th>
			<th scope="col"><?=$mody_btn_name?></th>
		</tr>
	</thead>
	<tbody>
	<?while($data = parseProductLog($res)) {?>
	<tr>
		<td><?if($data['upfile3']) {?><img src="<?=$data['img']?>" style="width: 80px;"><?}?></td>
		<td class="left">
			<ul>
				<li><?=$data['name']?></li>
				<li class="p_color"><?=$_partner_names[$data['partner_no']]?></li>
			</ul>
		</td>
		<td class="left"><?=nl2br($data['content'])?></td>
		<td class="left"><?=nl2br($data['content2'])?></td>
		<td><?=$data['pstat']?></td>
		<td><?=$data['reg_date_s']?></td>
		<td class="p_color<?=$data['stat']?>"><?=$data['stat_s']?></td>
		<td>
			<?if($data['stat'] == 1 || $data['stat'] == 3 || ($data['stat'] == '5' && $admin['level'] == '4')) {?>
			<span class="box_btn_s blue"><input type="button" value="<?=$mody_btn_name?>" onclick="goM('product@product_register&pno=<?=$data['pno']?>&listURL=<?=$listURL?>');"></span>
			<?} else {?>
			-
			<?}?>
		</td>
	</tr>
	<?}?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pageRes?>
</div>
<script type="text/javascript">
function ckAllDate() {
	var f = document.getElementById('prdSearchFrm');
	if(f.all_date1.checked == true) {
		f.reg_dates.disabled = true;
		f.reg_datee.disabled = true;
		f.reg_dates.style.backgroundColor = '#f2f2f2';
		f.reg_datee.style.backgroundColor = '#f2f2f2';
	} else {
		f.reg_dates.disabled = false;
		f.reg_datee.disabled = false;
		f.reg_dates.style.backgroundColor = '';
		f.reg_datee.style.backgroundColor = '';
	}

	if(f.all_date2.checked == true) {
		f.req_dates.disabled = true;
		f.req_datee.disabled = true;
		f.req_dates.style.backgroundColor = '#f2f2f2';
		f.req_datee.style.backgroundColor = '#f2f2f2';
	} else {
		f.req_dates.disabled = false;
		f.req_datee.disabled = false;
		f.req_dates.style.backgroundColor = '';
		f.req_datee.style.backgroundColor = '';
	}
}
ckAllDate();
</script>