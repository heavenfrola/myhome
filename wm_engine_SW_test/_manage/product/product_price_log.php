<?PHP

	// 키워드 검색
	$where = '';
	$_search_type = array(
		'name' => '상품명',
		'code' => '상품코드',
	);
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if(array_key_exists($search_type, $_search_type) == true && empty($search_str) == false) {
		$_search_str = addslashes($search_str);
		$where .= " and p.{$search_type} like '%$_search_str%'";
	}

	// 날짜 검색
	if($_GET['all_date']) $all_date = $_GET['all_date'];
	if($_GET['start_date']) $start_date = $_GET['start_date'];
	if($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-1 months'));
		$finish_date = date('Y-m-d', $now);
	}
	if($all_date != 'Y') {
		$_start_date = $start_date.' 00:00:00';
		$_finish_date = $finish_date.' 23:59:59';
		$where .= " and l.reg_date between '$_start_date' and '$_finish_date'";
	}

	// 데이터 조회
	$sql = "
	select l.*, p.name, (select count(*) from {$tbl['product_price_log']} where runid=l.runid) as cnt
	from {$tbl['product_price_log']} l inner join {$tbl['product']} p on l.pno=p.no
	where prd_type=1 $where group by l.runid
	order by l.reg_date desc
	";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if($row < 20) $row = 20;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from (
		select l.no from {$tbl['product_price_log']} l inner join {$tbl['product']} p on l.pno=p.no where 1 $where group by l.runid order by null
	) a");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page - 1));

	function parsePriceLog($res) {
		$data = $res->current();
        $res->next();
		if($data == false) return false;

		if($data['cnt'] > 1) $data['name'] .= ' 외 '.($data['cnt']-1);
		$data['name'] = stripslashes($data['name']);
		$data['cnt'] = number_format($data['cnt']);
		$data['reg_date_str'] = date('Y/m/d H:i', strtotime($data['reg_date']));
		$data['is_rollback_str'] = ($data['is_rollback'] == 'Y') ? '<span class="p_color5">복구</span>' : '변경';

		return $data;
	}

?>
<form name="searchFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">

	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title"><?=$cfg['product_sell_price_name']?> 변경 내역</h2>
	</div>
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
			<caption class="hidden"><?=$cfg['product_sell_price_name']?> 변경 내역</caption>
			<colgroup>
				<col style="width:12%;">
				<col style="width:38%;">
				<col style="width:12%;">
				<col style="width:38%;">
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">변경일</th>
					<td colspan="3">
						<label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
						<input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
						<?PHP
							$date_type = array(
								'오늘' => '-0 days',
								'1주일' => '-1 weeks',
								'15일' => '-15 days',
								'1개월' => '-1 months',
								'3개월' => '-3 months',
								'6개월' => '-6 months',
							);
							foreach($date_type as $key => $val) {
								$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
								$_sdate=$_fdate = null;
								if($val) {
									$_sdate=date("Y-m-d", strtotime($val));
									$_fdate=date("Y-m-d", $now);
								}
								?> <span class="box_btn_d <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span><?php
							}
						?>
						<script type="text/javascript">
							searchDate(document.searchFrm);
						</script>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
	<!-- 검색 폼 -->
</form>
<br>
<table class="tbl_col tbl_col2">
	<caption class="hidden"><?=$cfg['product_sell_price_name']?> 변경 내역</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:100px;">
		<col>
		<col style="width:100px;">
		<col style="width:140px;">
		<col style="width:200px;">
		<col style="width:80px;">
	</colgroup>
	<thead>
		<tr>
			<th>번호</th>
			<th>유형</th>
			<th>상품명</th>
			<th>상품개수</th>
			<th>변경일시</th>
			<th>처리자</th>
			<th>상세보기</th>
		</tr>
	</thead>
	<tbody>
		<?php while ($data = parsePriceLog($res)) { ?>
		<tr>
			<td><?=$idx--?></td>
			<td><?=$data['is_rollback_str']?></td>
			<td class="left"><?=$data['name']?></td>
			<td><?=$data['cnt']?></td>
			<td><?=$data['reg_date_str']?></td>
			<td><?=$data['admin_id']?></td>
			<td><span class="box_btn_s blue"><input type="button" value="상세" onclick="viewDetail(<?=$data['runid']?>, this);"></span></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<!-- 페이징 & 버튼 -->
<div class="box_bottom">
	<?=$pg_res?>
</div>
<!-- //페이징 & 버튼 -->

<script type="text/javascript">
var _runid;
function viewDetail(runid, obj, page) {
	var detail = $('.detail_'+runid);
	if(!page) page = 1;

	if(detail.length == 0 || page > 1) {
		$('.details').remove();
		$.post('?body=product@product_price_log.exe', {'exec':'viewDetail', 'runid':runid, 'page':page}, function(r) {
			$(obj).parents('tr').after("<tr class='details detail_"+runid+"' style='background:#f2f2f2;'><td colspan='7' class='right'>"+r+"</td></tr>");
			_runid = runid;
		});
	} else {
		detail.remove();
	}
}

function viewDetailPage(rows, page) {
	$.post('?body=product@product_price_log.exe', {'exec':'viewDetail', 'runid':_runid, 'page':page}, function(r) {
		$('.detail_'+_runid+'>td').html(r);
	});
}

function restorePrice(f) {
	if($('.sub_chkbox:checked').length == 0) {
		window.alert('복구할 상품을 선택해주세요.');
		return false;
	}

	if(confirm('선택한 상품의 가격을 이전으로 복구하시겠습니까?')) {
		f.target = hid_frame;

		$('#stpBtn').hide();
		return true;
	}
	return false;
}
</script>