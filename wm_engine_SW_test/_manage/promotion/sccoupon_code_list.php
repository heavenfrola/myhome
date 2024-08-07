<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 코드확인
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);

	$cpn=get_info($tbl['sccoupon'], "no", $no);

	$w="";
	$acode = addslashes(trim($_GET['acode']));
	if($acode) $w.=" and `code` like '%$acode%'";
	$code_detail = "Y";

	$sql="select *, (select `reg_date` from `$tbl[sccoupon_use]` where `code`=a.`code`) as `use_date` from `$tbl[sccoupon_code]` a where `scno`='$no' $w order by `use` desc";
	$sql_t="select count(*) from `$tbl[sccoupon_code]` where `scno`='$no' $w";

	$xls_query = makeQueryString('page', 'body');

	if($body == 'promotion@sccoupon_code_xls.exe') return;

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);

	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block=10;

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];

	$NumTotalRec = $pdo->row($sql_t);

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

?>
<form method="get">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">소셜쿠폰 코드확인</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">소셜쿠폰 코드확인</caption>
		<colgroup>
			<col style="width:15%">
		<colgroup>
		<tr>
			<th scope="row">쿠폰명</th>
			<td><?=$cpn[name]?></td>
		</tr>
		<tr>
			<th scope="row">코드검색</th>
			<td><input type="text" name="acode" class="input" size="15" value="<?=inputText($acode)?>"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 코드가 검색되었습니다.
	<span class="box_btn_s btns icon excel"><a href="./?body=promotion@sccoupon_code_xls.exe<?=$xls_query?>&code_detail=Y">엑셀다운</a></span>
</div>
<table class="tbl_col">
	<caption class="hidden">쿠폰 코드</caption>
	<colgroup>
		<col style="width:100px">
		<col>
		<col style="width:200px">
		<col style="width:200px">
	<colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">코드</th>
			<th scope="col">사용여부</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
			$data['use_str']=($data['use'] == 1) ? '미사용' : date('Y-m-d', $data['use_date']);
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><?=$data['code']?></td>
			<td><?=$data['use_str']?></td>
			<td>
				<?=($data['use'] == 2) ? "-" : "<span class=\"box_btn_s gray\"><input type=\"button\" onclick=\"deleteCode('$data[no]');\" value=\"삭제\"></span>";?>
			</td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pageRes?>
</div>
<form name="dcFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@sccoupon.exe">
	<input type="hidden" name="exec" value="delete_code">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="cdno" value="">
</form>

<script type='text/javascript'>
	function deleteCode(cdno){
		if (!confirm('\n 선택하신 쿠폰코드를 삭제하시겠습니까?              \n\n 삭제된 코드는 복구할 수 없습니다\n')) return;
		f=document.dcFrm;
		f.cdno.value=cdno;
		f.submit();
	}
</script>