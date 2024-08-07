<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 이벤트 리스트
	' +----------------------------------------------------------------------------------------------+*/

	function getPrizeCpn($cno) {
		global $tbl, $cname_cache, $pdo;

		if(!$cno) return;

		if($cname_cache[$cno]) {
			return $cname_cache[$cno];
		}

		$name = $pdo->row("select name from $tbl[coupon] where no='$cno'");
		if(!$name) $name = '[삭제된 쿠폰]';
		$cname_cache[$cno] = stripslashes($name);

		return $name;
	}
	$no = numberOnly($_REQUEST['no']);
	$attend = $pdo->assoc("select * from $tbl[attend_new] where no='$no'");
	if(!$attend['no']) msg('존재하지 않는 출석체크 코드입니다.', 'back');

	$start_date = $_REQUEST['start_date'];
	$finish_date = $_REQUEST['finish_date'];
	$all_date = $_REQUEST['all_date'];
	$prize_ok = $_REQUEST['prize_ok'];
	$_search_str = addslashes(trim($_GET['search_str']));
	$_st = trim($_GET['search_type']);
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	$w = " and eno='$no'";

	$_search_type = array(
		'member_id' => '회원아이디',
		'name' => '회원명',
	);

	if(!$start_date || !$finish_date) {
		$all_date = 'Y';
	}
	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w .= " and a.reg_date >= '$_start_date'";
		$w .= " and a.reg_date <= '$_finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-01');
		$finish_date = date('Y-m-t');
	}

	if($prize_ok == 'N') {
		$g = " group by member_no";
		$afield = ", count(*) as total_cnt, sum(prize_milage) as prize_milage, sum(prize_point) as prize_point, sum(if(prize_cno>0,1,0)) as total_cpn";
	}
	else if($prize_ok == 'P') $w .= " and (prize_cno>0 or prize_milage>0 or prize_point>0)";

	if($_search_str && array_key_exists($_st, $_search_type)) {
		$w .= " and b.`$_st` like '%$_search_str%'";
	}

	$sql = "select a.*, b.member_id, b.name as member_name $afield from $tbl[attend_list] a inner join $tbl[member] b on a.member_no=b.no where 1 $w $g order by a.reg_date desc";

	include $engine_dir."/_engine/include/paging.php";

	if($body == 'promotion@attend_list_excel.exe') return;

	if($page < 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
		if($key == 'body') continue;
		$QueryString2 .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[attend_list] a inner join $tbl[member] b on a.member_no=b.no where 1 $w");

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
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">출석체크 내역 상세검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">출석체크 내역 상세검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">체크 일자</th>
			<td>
				<input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
				~
				<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">

				<script type="text/javascript">
				searchDate(document.mnseFrm);
				</script>
			</td>
		</tr>
		<tr>
			<th scope="row">보기</th>
			<td>
				<label class="p_cursor"><input type="radio" name="prize_ok" value="" <?=checked($prize_ok, '')?>> 전체보기</label>
				<label class="p_cursor"><input type="radio" name="prize_ok" value="N" <?=checked($prize_ok, 'N')?>> 회원별 모아보기</label>
				<label class="p_cursor"><input type="radio" name="prize_ok" value="P" <?=checked($prize_ok, 'P')?>> 혜택 받은날만 조회</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type, 'search_type', 2, '', $search_type)?>
		<input type="text" name="search_str" size="40" value="<?=inputText($search_str)?>" class="input">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$body?>&no=<?=$no?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 건의 내역이 감색 되었습니다.
	<span class="box_btn_s btns"><a href="?body=promotion@attend_list_excel.exe&<?=$QueryString2?>" target="hidden<?=$now?>">엑셀다운</a></span>
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
				<th>이벤트명</th>
				<th>아이디</th>
				<th>이름</th>
				<th>체크일</th>
				<th>체크시간</th>
				<th>지급쿠폰</th>
				<th>지급적립금</th>
				<th>지급포인트</th>
				<th>총참여수</th>
				<th>연속참여수</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$data['reg_date'] = date('H:i:s', $data['reg_date']);
					$prize_cpn = '';
					$prize_cpn = getPrizeCpn($data['prize_cno']);

					if($prize_ok == 'N') {
						$data['straight_cnt'] = $pdo->row("select straight_cnt from $tbl[attend_list] where eno='$no' and member_no='$data[member_no]' order by no desc limit 1");
						$prize_cpn = $data['total_cpn'].'개';
					}
			?>
			<tr>
				<td><?=stripslashes($attend['name'])?></td>
				<td><?=stripslashes($data['member_id'])?></td>
				<td><?=$data['member_name']?></td>
				<td><?=$data['check_date']?></td>
				<td><?=$data['reg_date']?></td>
				<td><?=$prize_cpn?></td>
				<td><?=number_format($data['prize_milage'])?></td>
				<td><?=number_format($data['prize_point'])?></td>
				<td><?=number_format($data['total_cnt'])?></td>
				<td><?=number_format($data['straight_cnt'])?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom"><?=$pg_res?></div>
</form>

<script type="text/javascript">
	function editAttend(no) {
		window.open('./pop.php?body=config@attend&no='+no, 'attend_edit', 'status=no, scrollbars=no, width=500px, height=450px');
	}

	function viewAttend(no) {
		location.href = '?body=promotion@attend_detail&no='+no
	}
</script>