<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 이벤트 리스트
	' +----------------------------------------------------------------------------------------------+*/

	if(!isTable($tbl['attend_new'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';

		$pdo->query($tbl_schema['attend_new']);
		$pdo->query($tbl_schema['attend_list']);
	}

	$start_date = preg_replace('/[0-9-]/', '', $_GET['start_date']);
	$finish_date = preg_replace('/[0-9-]/', '', $_GET['finish_date']);
	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	$check_use = addslashes($_GET['check_use']);
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	$w = '';

	if(!$start_date || !$finish_date) {
		$all_date = 'Y';
	}
	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w .= " and start_date >= '$_start_date'";
		$w .= " and finish_date <= '$_finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-01');
		$finish_date = date('Y-m-t');
	}
	if($check_use) $w .= " and check_use='$check_use' or finish_date < $now";

	$sql = "select * from $tbl[attend_new] where 1 $w order by start_date desc";

	include $engine_dir."/_engine/include/paging.php";

	if($page < 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[attend_new] where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$_event_type = array(
		1 => '누적 참여형',
		2 => '연속 참여형',
	);

?>
<form name="mnseFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="type" value="<?=$type?>">
	<div class="box_title first">
		<h2 class="title">출석체크</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">출석체크</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">이벤트 기간</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
				~
				<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">

				<script type="text/javascript">
				searchDate(document.mnseFrm);
				</script>
			</td>
		</tr>
		<tr>
			<th scope="row">상태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="check_use" value="" <?=checked($check_use, '')?>> 전체</label>
				<label class="p_cursor"><input type="radio" name="check_use" value="Y" <?=checked($check_use, 'Y')?>> 정상/대기</label>
				<label class="p_cursor"><input type="radio" name="check_use" value="N" <?=checked($check_use, 'N')?>> 종료/중단</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$body?>'"></span>
	</div>
</form>
<div class="box_title">
	<h2 class="title">출석체크 리스트</h2>
</div>
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			목록수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
				<option value="500" <?=checked($row,500,1)?>>500</option>
				<option value="1000" <?=checked($row,1000,1)?>>1000</option>
			</select>&nbsp;&nbsp;
		</dd>
	</dl>
</div>
<form name="mnFrm" method="post" action="./" style="margin:0px" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@attend.exe">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<thead>
			<tr>
				<th scope="col">이름</th>
				<th scope="col">사용여부</th>
				<th scope="col">시작일</th>
				<th scope="col">종료일</th>
				<th scope="col">참여방식</th>
				<th scope="col">달성조건</th>
				<th scope="col">참여수</th>
				<th scope="col">달성수</th>
				<th scope="col">등록일</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$data['start_date'] = date('Y-m-d', $data['start_date']);
					$data['finish_date'] = $data['finish_date'] == 9999999999 ? '무제한' : date('Y-m-d', $data['finish_date']);
					$data['reg_date'] = date('Y-m-d H:i', $data['reg_date']);
			?>
			<tr>
				<td><?=stripslashes($data['name'])?></td>
				<td><?=$data['check_use'] == 'Y' ? '○' : '×'?></td>
				<td><?=$data['start_date']?></td>
				<td><?=$data['finish_date']?></td>
				<td><?=$_event_type[$data['event_type']]?></td>
				<td><?=$data['complete_day']?> 일</td>
				<td><?=number_format($data['check_cnt'])?></td>
				<td><?=number_format($data['prize_cnt'])?></td>
				<td><?=$data['reg_date']?></td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="editAttend(<?=$data['no']?>)"></span>
					<span class="box_btn_s"><input type="button" value="내역" onclick="viewAttend(<?=$data['no']?>)"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom"><?=$pg_res?></div>
</form>

<script type="text/javascript">
	function editAttend(no) {
		window.open('./pop.php?body=promotion@attend&no='+no, 'attend_edit', 'status=no, scrollbars=no, width=500px, height=450px');
	}

	function viewAttend(no) {
		location.href = '?body=promotion@attend_detail&no='+no
	}
</script>