<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  입점신청 관리
	' +----------------------------------------------------------------------------------------------+*/
	$_row = array(20 => 20, 30 => 30, 50 => 50, 100 => 100);
	$_search = array("corporate_name" => "업체명", "email" => "이메일", "cell" => "연락처");

	$search_key = addslashes(trim($_GET['search_key']));
	$search = addslashes(trim($_GET['search']));
	if ($search_key && $search) {
		$w .= " and `$search_key` like '%$search%'";
	}

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$sql = "select * from `$tbl[partner_shop]` p where stat<5 $w order by reg_date";
	$sql_t = "select count(*) from `$tbl[partner_shop]` where stat<5 $w";

	$xls_query = makeQueryString('page', 'body');

	if($_GET['mode'] == 'xls') return;

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

	function joinshop_list() {
		global $res, $pidx;

		$data = $res->current();
        $res->next();
		if ($data == false) return;

		$pidx++;

		return $data;
	}

	$wec = new WeagleEyeClient($_we, 'account');
	$partnershop_ea = $wec->call('getPartnershopInfo');
	$total_ea = $pdo->row("select count(*) from {$tbl['partner_shop']} where stat<5");

	$_SESSION['list_url'] = getURL();

?>
<!-- 검색 폼 -->
<form method="get">
	<input type="hidden" name="body" value="<?=$_GET[body]?>">
	<div class="box_title first">
		<h2 class="title">
			입점사 관리
			<dl class="total">
				<dt class="hidden">현황</dt>
				<?if($partnershop_ea != 'unlimited') {?>
				<dd>등록가능 수 <strong style="color:#e05e17"><?=number_format($partnershop_ea)?></strong></dd>
				<?}?>
				<dd>사용 <strong style="color:##330000"><?=number_format($total_ea)?></strong></dd>
			</dl>
		</h2>
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
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 입점사가 검색되었습니다.
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
	</dl>
</div>
<!-- //정렬 -->
<!-- 검색 테이블 -->
<table class="tbl_col">
	<caption class="hidden">입점사 목록</caption>
	<colgroup>
		<col style="width:350px">
		<col>
		<col style="width:200px">
		<col style="width:75px">
		<col style="width:75px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">입점사명</th>
			<th scope="col">업체설명</th>
			<th scope="col">연락처</th>
			<th scope="col">접속</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = joinshop_list()) {?>
		<tr>
			<td><a href="?body=product@product_join_shop_register&no=<?=$data['no']?>"><?=stripslashes($data['corporate_name'])?></a></td>
			<td class="left"><a href="?body=product@product_join_shop_register&no=<?=$data['no']?>"><strong><?=$data['title']?></strong></a></td>
			<td><?=$data[cell]?></td>
			<td><span class="box_btn_s blue"><input type="button" value="접속" onclick="partnerAdmin(<?=$data['no']?>)"></span></td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="removePartner(<?=$data['no']?>)"></span></td>
		</tr>
		<?}?>
	</tbody>
	</tfoot>
</table>
<!-- 페이징 & 버튼 -->
<div class="box_bottom">
	<?=$pg_res?>
</div>
<!-- //페이징 & 버튼 -->
<div class="box_middle2 right">
	<span class="box_btn blue"><input type="button" value="수동등록" onclick="goM('product@product_join_shop_register')"></span>
</div>
<!-- //검색 테이블 -->

<script type="text/javascript">
function partnerAdmin(no) {
	$.post('?body=product@product_join_shop.exe', {'exec':'connect', 'no':no}, function() {
		location.reload();
	});
}

function removePartner(partner_no) {
	if(confirm('해당 입점사를 삭제하시겠습니까?\n\n삭제된 입점사는 정산내역을 포함한\n모든 정보를 복구할 수 없습니다.')) {
		$.post('./index.php', {'body':'product@product_join_shop.exe', 'exec':'remove', 'partner_no':partner_no}, function(r){
			if(r.status == 'success') {
				window.alert(r.message);
				location.href='./?body=product@product_join_shop';
			} else {
				window.alert(r.message);
			}
		});
	}
}
</script>