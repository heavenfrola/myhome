<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  입점사 선택
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/paging.php";

	printAjaxHeader();

	$row = (isset($_GET['row'])) ? numberOnly($_GET['row']) : 10;
	$page = (isset($_GET['page'])) ? numberOnly($_GET['page']) : 1;

	$NumTotalRec=$pdo->row("select count(*) from $tbl[partner_shop]");
	$PagingInstance = new Paging($NumTotalRec, $page, 5, 10);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator("select no, corporate_name, stat from $tbl[partner_shop] order by corporate_name asc ".$PagingResult['LimitQuery']);
	$idx=$NumTotalRec-($row*($page-1));

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="targetSelector.open(\'$1\')"', $pg_res);

	function parsePartnerShop($res) {
		global $_partner_stats;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['corporate_name'] = stripslashes($data['corporate_name']);
		$data['stat'] = $_partner_stats[$data['stat']];

		return $data;
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">혜택을 적용할 입점사를 등록해주세요.</div>
	</div>
	<div id="popupContentArea">
		<table class="tbl_col">
			<thead>
				<tr>
					<th scope="col" style="width:40px;">선택</th>
					<th scope="col">입점사명</th>
					<th scope="col">상태</th>
				</tr>
			</thead>
			<tbody>
				<?if($page == 1) {?>
				<tr>
					<td class="left">
						<input type="checkbox" class="category_items" value="0">
					</td>
					<td class="left"><?=$cfg['company_mall_name']?></td>
					<td>본사</td>
				</tr>
				<?}?>
				<?while($data = parsePartnerShop($res)) {?>
				<tr>
					<td class="left">
						<input type="checkbox" class="category_items" value="<?=$data['no']?>">
					</td>
					<td class="left"><?=$data['corporate_name']?></td>
					<td><?=$data['stat']?></td>
				</tr>
				<?}?>
			</tbody>
		</table>
		<div class="box_bottom">
			<?=$pg_res?>
		</div>
		<div class="box_middle2">
			<span class="box_btn blue"><input type="button" value="확인" onclick="closePartnerInc();"></span>
		</div>
	</div>
</form>

<script type='text/javascript'>
	var data = $('input[name=attach_items_5]').val().replace(/^\[/, '').replace(/\]$/, '').split('][');
	for(var i = 0; i < data.length; i++) {
		if(data[i]) {
			$(':checkbox.category_items').filter('[value='+data[i]+']').prop('checked', true);
		}
	}

	function closePartnerInc() {
		setTargetValue(5);
		targetSelector.close();
	}
</script>