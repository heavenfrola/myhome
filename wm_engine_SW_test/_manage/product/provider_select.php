<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 검색
	' +----------------------------------------------------------------------------------------------+*/

	$_search_type = array('provider' => '사입처명', 'arcade' => '상가명', 'location' => '위치', 'ptel' => '전화번호');
	$search_str = addslashes(trim($_GET['search_str']));
	$search_type = addslashes($_GET['search_type']);
	if($search_type && $search_str) $w .= " and `$search_type` like '%$search_str%'";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	$row = 15;
	$block = 10;

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[provider]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result('admin');

	$pageRes = $PagingResult['PageLink'];

	$qry = "select * from `$tbl[provider]` where 1 $w order by `provider` asc ".$PagingResult['LimitQuery'];
	$res = $pdo->iterator($qry);

?>
<form id="search" style="width: 600px;">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_bottom angle">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="20">
		<span class="box_btn_s blue"><input type="submit" value="검색"></span>
		<span class="box_btn_s gray"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>
<div class="box_title first angle">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다
</div>
<table class="tbl_col">
	<colgroup>
		<col span="2">
		<col style="width:50px;">
		<col span="2">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">사입처명</th>
			<th scope="col">상가</th>
			<th scope="col">층</th>
			<th scope="col">위치</th>
			<th scope="col">전화번호</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($res as $data) {?>
		<tr>
			<td class="left"><strong><a href="javascript:;" onclick="selectPrivider(<?=$data['no']?>)"><?=stripslashes($data['provider'])?></a></strong></td>
			<td class="left"><?=stripslashes($data['arcade'])?></td>
			<td><?=stripslashes($data['floor'])?></td>
			<td><?=stripslashes($data['location'])?></td>
			<td><?=$data['ptel']?></td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pageRes?></div>

<script type="text/javascript">
	function selectPrivider(pno) {
		if(!opener.closed) {
			opener.readSellers(pno);
			self.close();
		}
	}
	setPoptitle('사입처 검색');
</script>