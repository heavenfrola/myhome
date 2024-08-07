<?PHP

	if(!isTable($tbl['erp_storage'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['erp_storage']);
		$pdo->query("alter table $tbl[product] add storage_no int(10) not null default '0'");
		$pdo->query("alter table $tbl[product] add index storage_no(storage_no)");
	}

	$_search_type = array('name' => '창고별명', 'dam' => '담당자');
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str) {
		$_search_str = addslashes($search_str);
		$w .= " and $search_type like '%$_search_str%'";
	}

	$sql = "select *, (select count(*) from $tbl[product] where storage_no=x.no) as cnt from $tbl[erp_storage] x where 1 $w order by no desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 10;
	$block = 10;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[erp_storage] where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1))+1;

	setListURL('?body='.$body);

	function parseStorage($res) {
		global $idx;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['name'] = stripslashes($data['name']);
		$data['dam'] = stripslashes($data['dam']);
		$data['location'] = stripslashes($data['location']);
		$data['cnt'] = number_format($data['cnt']);

		$idx--;

		return $data;
	}

	$csql = $pdo->iterator("select no, name from $tbl[category] where ctype=9 and level='1' order by sort asc");
    foreach ($csql as $cate) {
		$item_9_1 .= "\n<option value='".$cate['no']."' $sel>".stripslashes($cate['name'])."</option>";
	}

?>
<div class="box_title first">
    <h2 class="title">창고 관리</h2>
</div>
<form id='search' method='get' action='./index.php'>
	<input type='hidden' name='body' value='<?=$_GET['body']?>' />

	<div class="box_search">
		<div class="box_input">
			<div class="select_input shadow">
				<div class="select">
					<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
				</div>
				<div class="area_input">
					<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
				</div>
			</div>

		</div>
	</div>

	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
	<!-- 검색 총합 -->
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 창고가 검색되었습니다.
	</div>
	<!-- //검색 총합 -->
</form>

<form method='post' action='./index.php' target='hidden<?=$now?>'>
	<input type="hidden" name='body' value='erp@storage.exe' />
	<input type="hidden" name='exec' value='remove' />

	<table class="tbl_col">
		<caption class="hidden">창고관리</caption>
		<thead>
			<tr>
				<th>선택</th>
				<th>번호</th>
				<th>창고별명</th>
				<th>창고위치</th>
				<th>담당자</th>
				<th>등록상품</th>
			</tr>
		</thead>
		<tbody>
			<?while($data = parseStorage($res)) {?>
			<tr>
				<td class="center"><input type='checkbox' name='no[]' value='<?=$data['no']?>'></td>
				<td class='center'><?=$idx?></td>
				<td class="left"><a href='?body=erp@storage_register&no=<?=$data['no']?>'><?=$data['name']?></a></td>
				<td><a href='?body=erp@storage_register&no=<?=$data['no']?>'><?=getStorageLocation($data)?></a></td>
				<td class='center'><?=$data['dam']?></td>
				<td class='center'><?=$data['cnt']?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn_s gray"><input type='submit' value='삭제' onclick="return confirm('선택한 창고를 삭제하시겠습니까?')"></span>
			<span class="box_btn_s blue"><input type='button' value='창고등록' onclick='location.href="?body=erp@storage_register"'></span>
		</div>
	</div>
</form>


<div id="controlTab">
	<ul class="tabs">
		<li id='ctab_1' onclick="tabSH(1)" class='selected'>창고 이동</li>
		<li id='ctab_2' onclick="tabSH(2)">자동 생성</li>
	</ul>
	<div class="context">

		<form id='edt_layer_1' method='post' action='./' target='hidden<?=$now?>' onsubmit='return edtConfirm(this)'>
			<input type='hidden' name='body' value='erp@storage.exe'>
			<input type='hidden' name='exec' value="move">

			<div class="box_middle2 left">
				<select name="obig" onchange="chgCateInfinite(this, 2, 'o')">
					<option value="">::대분류::</option>
					<?=$item_9_1?>
				</select>
				<select name="omid" onchange="chgCateInfinite(this, 3, 'o')">
					<option value="">::중분류::</option>
					<?=$item_9_2?>
				</select>
				<select name="osmall"onchange="chgCateInfinite(this, 4, 'o')">
					<option value="">::소분류::</option>
					<?=$item_9_3?>
				</select>
				<select name="odepth4">
					<option value="">::세분류::</option>
					<?=$item_9_4?>
				</select>
				창고의 상품을<br><br>

				<select name="nbig" onchange="chgCateInfinite(this, 2, 'n')">
					<option value="">::대분류::</option>
					<?=$item_9_1?>
				</select>
				<select name="nmid" onchange="chgCateInfinite(this, 3, 'n')">
					<option value="">::중분류::</option>
					<?=$item_9_2?>
				</select>
				<select name="nsmall" onchange="chgCateInfinite(this, 4, 'n')">
					<option value="">::소분류::</option>
					<?=$item_9_3?>
				</select>
				<select name="ndepth4">
					<option value="">::세분류::</option>
					<?=$item_9_4?>
				</select>
				창고로
				<span class="box_btn_s blue"><input type="submit" value="이동"></span>
			</div>
		</form>

		<div id='edt_layer_2' style="display:none;">
			<div class="box_middle2 left">
				<div>
					<div class="explain">
						창고분류 관리에 등록된 데이터를 토대로 창고정보를 자동 생성합니다.
						<span class="box_btn_s blue"><input type="button" value="생성" onclick="createStorage()"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function createStorage() {
    printLoading();

	$.get('./index.php', {'body':'erp@storage.exe', 'exec':'createStorage'}, function(json) {
		if(json.result == '0') {
			window.alert('새롭게 추가된 창고가 없습니다.');
            removeLoading();
		} else {
			window.alert(json.message);
			location.reload();
		}
	});
}
</script>