<?PHP

	$no = numberOnly($_GET['no']);
	$page = numberOnly($_GET['page']);
	$_acode = trim(addslashes($_GET['acode']));
	$QueryString = '';

	$cpn = $pdo->assoc("select no, name from $tbl[coupon] where no='$no'");
	if(!$cpn['no']) msg('존재하지 않는 쿠폰코드입니다.', 'back');

	if($_acode) {
		$w .= " and c.auth_code='$_acode'";
	}

	$sql = "select c.cno, c.auth_code, d.use_date from $tbl[coupon_auth_code] c left join $tbl[coupon_download] d using(auth_code) where c.cno='$no' $w";

	include $engine_dir."/_engine/include/paging.php";

	if($page <= 1) $page=1;
	$row = 20;
	$block = 20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['coupon_auth_code']} c left join {$tbl['coupon_download']} d using(auth_code) where c.cno='$no' $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	function parseAuthCode($res) {
		$data = $res->current();
        $res->next();
		if ($data == false) return false;

		$data['used'] = ($data['use_date'] > 0) ? 'Y' : '';
		$data['use_date_r'] = ($data['used'] == 'Y') ? date('Y-m-d H:i', $data['use_date']) : '미사용';

		return $data;
	}

?>
<form method="get">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">시리얼쿠폰 코드확인</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">시리얼쿠폰 코드확인</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">쿠폰명</th>
			<td><?=stripslashes($cpn['name'])?></td>
		</tr>
		<tr>
			<th scope="row">코드검색</th>
			<td><input type="text" name="acode" class="input" size="15" value="<?=inputText($_GET['acode'])?>"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn gray"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&no=<?=$no?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=$NumTotalRec?></strong>개의 코드가 검색되었습니다.
</div>
<table class="tbl_col">
	<caption class="hidden">쿠폰 코드</caption>
	<colgroup>
		<col style="width:60px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">코드</th>
			<th scope="col">사용여부</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseAuthCode($res)) {?>
		<tr>
			<td><?=$idx--?></td>
			<td><?=$data['auth_code']?></td>
			<td><?=$data['use_date_r']?></td>
			<td>
				<?if($data['used'] != 'Y') {?>
				<span class="box_btn_s gray"><input type="button" onclick="deleteCode('<?=$data[auth_code]?>');" value="삭제"></span>
				<?}?>
			</td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>
<form name="dcFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@coupon.exe">
	<input type="hidden" name="exec" value="delete_authcode">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="auth_code" value="">
</form>

<script type="text/javascript">
	function deleteCode(cd){
		if (!confirm('\n 선택하신 쿠폰을 삭제하시겠습니까?              \n\n 삭제된 쿠폰은 복구할 수 없습니다\n')) return;
		f=document.dcFrm;
		f.auth_code.value=cd;
		f.submit();
	}
</script>