<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품수정 내역
	' +----------------------------------------------------------------------------------------------+*/

	$w = '';
	if($admin['level'] > 1) {
		$w .= " and admin_id!='wisa'";
	}
	$sFrom = addslashes(trim($_GET['sFrom']));
	$search_str = addslashes(trim($_GET['search_str']));
	if($sFrom && $search_str) $w .= " and `$sFrom` like '%{$search_str}%'";

	$stat = numberOnly($_GET['stat']);
	if($stat) $w .= " and `stat`='$stat'";

	$sql="select * from `$tbl[product_log]` where 1 $w $w2 order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$rows = numberOnly($_GET['rows']);
	if($page<=1) $page=1;
	$row=($_GET['rows']) ? $_GET['rows'] : 20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[product_log]` where 1 $w $w2");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$_stat=array(1=>"등록", 2=>"수정", 3=>"삭제");
	$_pagerows = array(20, 50, 100, 500, 1000);
	$qs_without_row = '?'.preg_replace('/&rows=[^&]+/', '', $_SERVER['QUERY_STRING']);

?>
<form method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">상품수정 내역</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<select name="sFrom">
							<option value="pname" <?=checked($sFrom,"pname",1)?>>상품명</option>
							<option value="admin_id" <?=checked($sFrom,"admin_id",1)?>>관리자 아이디</option>
							<option value="ip" <?=checked($sFrom,"ip",1)?>>아이피</option>
						</select>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품수정 내역 검색</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tr>
				<th scope="row">실행</th>
				<td>
					<select name="stat">
						<option value="">전체</option>
						<option value="1" <?=checked($stat,"1",1)?>><?=$_stat[1]?></option>
						<option value="2" <?=checked($stat,"2",1)?>><?=$_stat[2]?></option>
						<option value="3" <?=checked($stat,"3",1)?>><?=$_stat[3]?></option>
					</select>
				</td>
			</tr>
			<?if($admin['admin_id'] == 'wisa') {?>
			<tr>
				<th scope="row">wisa 제외</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="swisa" value="1" <?=checked($swisa,1)?> onClick="this.form.submit();"> wisa 제외</label>
					<span class="explain">일반 고객에게는 wisa 아이디가 나타나지 않습니다</div>
				</td>
			</tr>
			<?}?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<form>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 로그가 검색되었습니다.
		<dl class="total">
			<dt class="hidden">출력설정</dt>
			<dd class="first-child">
				로그수
				<?=selectArray($_pagerows, 'rows', 1, null, $rows, "location.href='$qs_without_row&rows='+this.value")?>
			</dd>
		</dl>
	</div>
	<table class="tbl_col">
		<caption class="hidden">상품수정 내역 리스트</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:80px">
			<col>
			<col style="width:120px">
			<col style="width:120px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">실행</th>
				<th scope="col">상품명</th>
				<th scope="col">관리자 아이디</th>
				<th scope="col">실행일시</th>
				<th scope="col">IP</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td><?=$idx?></td>
				<td><?=$_stat[$data['stat']]?></td>
				<td class="left"><?=strip_tags(stripslashes($data['pname']))?></td>
				<td><?=$data[admin_id]?></td>
				<td><?=date("Y-m-d H:i", $data[reg_date])?></td>
				<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank"><?=$data[ip]?></a></td>
			</tr>
			<?$idx--;}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
</form>