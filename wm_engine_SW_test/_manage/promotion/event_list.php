<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  할인/적립 이벤트 리스트
	' +----------------------------------------------------------------------------------------------+*/

	define('evnet_install', true);
	include_once $engine_dir."/_manage/promotion/event_install.exe.php";


	$event_use = addslashes($_REQUEST['event_use']);
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	$w = '';

	if($event_use) $w .= " and event_use='$event_use'";
	$sql = "select * from $tbl[event] where 1 $w order by event_begin desc";

	include $engine_dir."/_engine/include/paging.php";
	if($page < 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[event] where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
?>
<form name="mnseFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">할인/적립 이벤트</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">할인/적립 이벤트</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">상태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_use" value="" <?=checked($event_use, '')?>> 전체</label>
				<label class="p_cursor"><input type="radio" name="event_use" value="Y" <?=checked($event_use, 'Y')?>> 정상/대기</label>
				<label class="p_cursor"><input type="radio" name="event_use" value="N" <?=checked($event_use, 'N')?>> 종료/중단</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$body?>'"></span>
	</div>
</form>
<div class="box_title">
	<h2 class="title">할인/적립 이벤트</h2>
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


<table class="tbl_col">
	<thead>
		<tr>
			<th scope="col">사용여부</th>
			<th scope="col">이벤트명</th>
			<th scope="col">시작일</th>
			<th scope="col">종료일</th>
			<th scope="col">대상</th>
			<th scope="col">이벤트방식</th>
			<th scope="col">결제수단</th>
			<th scope="col">할인률</th>
			<th scope="col">절사단위</th>
			<th scope="col">등록일</th>
			<th scope="col">관리</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$data['event_begin']  = date('Y-m-d H:i',$data['event_begin']);
				$data['event_finish'] = date('Y-m-d H:i',$data['event_finish']);
				if(!$data['event_name'])$data['event_name']=$data['event_begin']." ~ ".$data['event_finish'];
				$data['reg_date'] = date('Y-m-d H:i', $data['reg_date']);
				if($data['event_obj'] == "1")$data['event_obj']="전체";
				if($data['event_obj'] == "2")$data['event_obj']="회원만";
				if($data['event_obj'] == "3")$data['event_obj']="기업회원만";
		?>
		<tr>
			<td><?=$data['event_use'] == 'Y' ? '○' : '×'?></td>
			<td><?=$data['event_name']?></td>
			<td><?=$data['event_begin']?></td>
			<td><?=$data['event_finish']?></td>
			<td><?=$data['event_obj']?></td>
			<td><?=$data['event_type'] == '1' ? '적립' : '할인'?></td>
			<td><?=$data['event_ptype'] =='0' ? '모든결제' : '현금 결제만'?></td>
			<td><?=$data['event_per']?> %</td>
			<td><?=$data['event_round']?></td>
			<td><?=$data['reg_date']?></td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="editAttend(<?=$data['no']?>)"></span>
				</td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>
<div class="box_bottom">
	<span class="box_btn blue">
		<input type="button" value="이벤트 생성하기" onclick="location.href='./?body=promotion@event'">
	</span>
</div>
<?include $engine_dir.'/_manage/promotion/event_apply.php';?>
<script type="text/javascript">
	function editAttend(no) {
		window.open('./pop.php?body=promotion@event&no='+no, 'event', 'status=no, scrollbars=yes');
	}
</script>