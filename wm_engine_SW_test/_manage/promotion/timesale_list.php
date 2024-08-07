<?PHP

	if(isTable($tbl['product_timesale_set']) == false) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['product_timesale_set']);
	}

	// 검색
	$w = '';
	$use = addslashes($_GET['use']);
	if($use) {
		$w .= " and ts_use='$use'";
	}

	// 페이징
	$_rows = array(20, 50, 100);

	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	$rows = numberOnly($_GET['rows']);
	if($page < 1) $page = 1;
	if($rows < 1 || $rows > 100) $rows = 10;
	$block = 10;

	$sql = "select * from {$tbl['product_timesale_set']} where 1 $w order by no desc ";

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['product_timesale_set']} where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $rows, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($rows*($page-1));

	setListURL('timesale_regist');

	function parseTimesale($res) {
		global $cfg, $now;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['ts_event_type_s'] = ($data['ts_event_type'] == '1') ? '할인' : '적립';
		$data['ts_saletype_s'] = ($data['ts_saletype'] == 'percent') ? '%' : $cfg['currency_type'];
		$data['expired'] = (strtotime($data['ts_dates']) >= $now && strtotime($data['ts_datee']) <= $now) ? 'Y' : '';
		$data['use_on'] = ($data['ts_use'] == 'Y') ? 'on' : '';
        $data['ts_datee_str'] = ($data['ts_datee'] > 0) ? date('Y-m-d H:i', strtotime($data['ts_datee'])) : '무제한';

		return $data;
	}

	$counts = $pdo->assoc("select count(*) as tot, sum(if(ts_use='Y', 1, 0)) as use_y from {$tbl['product_timesale_set']}");
	$cnt_tot = number_format($counts['tot']);
	$cnt_y = number_format($counts['use_y']);
	$cnt_n = number_format($cnt_tot-$counts['use_y']);

	${'list_tab_active'.$use} = " class='active'";

?>
<div class="box_title first">
	<h2 class="title">타임세일 세트 설정</h2>
</div>
<div class="box_tab first">
	<ul>
		<li><a href="?body=<?=$_GET['body']?>" <?=$list_tab_active?>>전체<span><?=$cnt_tot?></span></a></li>
		<li><a href="?body=<?=$_GET['body']?>&use=Y" <?=$list_tab_activeY?>>사용함<span><?=$cnt_y?></span></a></li>
		<li><a href="?body=<?=$_GET['body']?>&use=N" <?=$list_tab_activeN?>>사용안함<span><?=$cnt_n?></span></a></li>
	</ul>
</div>
<div class="box_sort">
	<?=selectArray($_rows, 'rows', true, null, $rows, "location.href='?body=promotion@timesale_list&rows='+this.value")?>
</div>
<table class="tbl_col">
	<colgroup>
		<col />
		<col>
		<col>
		<col style="width:120px;">
		<col style="width:120px;">
		<col style="width:120px;">
		<col style="width:100px;">
		<col style="width:120px;">
	</colgroup>
	<thead>
		<tr>
			<th>세트명</th>
			<th>시작일</th>
			<th>종료일</th>
			<th>사용함</th>
			<th>이벤트 방식</th>
			<th>할인율</th>
			<th>종료 후 상태</th>
			<th>삭제</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseTimesale($res)) {?>
		<tr>
			<td><a href="?body=promotion@timesale_regist&no=<?=$data['no']?>"><?=$data['name']?></a></td>
			<td><?=date('Y-m-d H:i', strtotime($data['ts_dates']))?></td>
			<td><?=$data['ts_datee_str']?></td>
			<td><div class="switch <?=$data['use_on']?>" onclick="toggleUsePreset(<?=$data['no']?>, $(this))" data-expired="<?=$data['expired']?>"></div></td>
			<td><?=$data['ts_event_type_s']?></td>
			<td><?=parsePrice($data['ts_saleprc'], true)?><?=$data['ts_saletype_s']?></td>
			<td><?=$_prd_stat[$data['ts_state']]?></td>
			<td>
				<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removePreset(<?=$data['no']?>);"></span>
			</td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
	<div class="right_area">
		<span class="box_btn blue"><a href="?body=promotion@timesale_regist">타임세일 세트 등록</a></span>
	</div>
</div>
<script type="text/javascript">
	function toggleUsePreset(no, o) {
		$.post('?body=promotion@timesale_regist.exe', {'exec':'toggle', 'no':no}, function(r) {
			if(r.changed == 'Y') {
				o.addClass('on');
				if(o.attr('data-expired') == 'Y') {
					window.alert('종료일이 지난 세트를 사용함으로 설정하셨습니다.');
				}
			} else {
				o.removeClass('on');
			}
		});
	}

	function removePreset(no){
		if (!confirm("선택한 타임세일 세트를 삭제하시겠습니까?\n타임세일 세트가 적용된 상품의 타임세일이 해제됩니다.")) return;

		$.post('?body=promotion@timesale_regist.exe', {'exec':'delete', 'no':no}, function(r) {
			if(r.changed == 'Y') {
				window.alert('삭제되었습니다.');
				location.reload();
			}
		});
	}
</script>