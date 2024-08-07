<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크
	' +----------------------------------------------------------------------------------------------+*/

	$ctype = addslashes($_GET['ctype']);
	$search_type = addslashes($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	$attend = addslashes($_GET['attend']);
	$all_date = addslashes($_GET['all_date']);

	if($cfg['use_attend'] != 'Y') {
		include 'attend_list_new.php';
		return;
	}

	$tbl_str = ($cfg['attendMP']=='M') ? $tbl[milage]:$tbl[point];
	$_ctype[1]="+";
	$_ctype[2]="-";
	$ctype=$_ctype[$type];

	if($ctype=="+") {
		$milage_title[0]=$milage_title[12]="";
	}
	if($ctype=="-") {
		$milage_title[0]=$milage_title[1]=$milage_title[2]="";
	}

	$w.=" and `title` like '%출석체크이벤트%'";

	$_search_type[member_name]='이름';
	$_search_type[member_id]='아이디';

	if(!$start_date || !$finish_date) {
		$all_date="Y";
	}
	if(!$all_date) {
		$w.=" and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') >= '$start_date'";
		$w.=" and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') <= '$finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date=$finish_date=date("Y-m-d",$now);
	}

	if($_search_type[$search_type] && $search_str!="") $w.=" and `$search_type` like '%$search_str%'";

	$xls_query=$QueryString;
	foreach($_GET as $key=>$val) {
		if($key!="page" && !is_array($val)) $add_QueryString="&".$key."=".$val;

		if($add_QueryString) {
			$QueryString.=$add_QueryString;
			if($key!="body") {
				$xls_query.=$add_QueryString;
			}
		}
	}

	if($attend) $w .= " and `ctype` = '$attend'";

	$sql="select * from `$tbl_str` where 1 $w order by `no`desc, `reg_date` desc";

	include $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=20;
	if(!$QueryString) $QueryString="&body=".$_GET[body];

	$NumTotalRec = $pdo->rowCount($sql);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	if(!$admin[level]==1) {
		echo $sql;
	}
	$idx=$NumTotalRec-($row*($page-1));

	$group=getGroupName();

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

?>
<form name="mnseFrm" method="get" action="./">
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
			<th scope="row">기간</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				<script type="text/javascript">
				searchDate(document.mnseFrm);
				</script>
			</td>
		</tr>
		<tr>
			<th scope="row">선택</th>
			<td>
				<input type="radio" name="attend" value="" <?=checked($attend,'')?>> 전체
				<input type="radio" name="attend" value="+" <?=checked($attend,'+')?>> 발급
				<input type="radio" name="attend" value="-" <?=checked($attend,'-')?>> 취소
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" size="40" class="input">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$body?>&type=<?=$type?>'"></span>
	</div>
</form>
<form name="mnFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@money_list.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="tbn" value="milage">
	<input type="hidden" name="amount">
	<div class="box_title">
		<dl>
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
		<caption class="hidden">출석체크 리스트</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.mnFrm.mno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">이름</th>
				<th scope="col">아이디</th>
				<th scope="col">구분</th>
				<th scope="col">적요</th>
				<th scope="col">적립금</th>
				<th scope="col">회원소계</th>
				<th scope="col">날짜</th>
				<th scope="col">취소</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
			?>
			<tr>
				<td><?if($data[ctype]!='-' && ($data[title] != '출석체크이벤트(취소)')) {?><input type="checkbox" name="mno[]" id="mno" value="<?=$data[no]?>"><?}?></td>
				<td><?=$idx?></td>
				<td><a href="#" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>'); return false;"><b><?=$data['member_name']?></b></a></td>
				<td><a href="#" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>'); return false;"><?=$data['member_id']?></a></td>
				<td><?=$milage_title[$data[mtype]]?></td>
				<td><?=stripslashes($data[title])?></td>
				<td><?=$data[ctype]?><?=number_format($data[amount])?></td>
				<td><?=number_format($data[member_milage])?></td>
				<td title="<?=date("Y/m/d H:i:s",$data[reg_date])?>"><?=date("Y/m/d",$data[reg_date])?></td>
				<td title="내역을 삭제합니다"><?if($data[ctype]!='-' && ($data[title] != '출석체크이벤트(취소)')) {?><span class="box_btn_s"><a href="javascript:;"  onclick="delList('<?=$data[no]?>');">취소</a></span><?}?></td>
			</tr>
			<?
				$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="선택 취소" onclick="delMoney();"></span>
		</div>
	</div>
</form>
<form name="money_frm" action="./" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@money_list.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="tbn" value="milage">
	<input type="hidden" name="no">
</form>

<script type="text/javascript">
	function delList(no, amount){
		if(!confirm("\해당 출석체크를 취소하시고 적립금을 반환하시겠습니까?")) return;
		f=document.money_frm;
		f.no.value=no;
		f.submit();
	}

	function delMoney(){
		f=document.mnFrm;
		if(!checkCB(f.mno,"취소하실 내역을")) return;
		if(!confirm("\해당 출석체크들을 취소하시고 적립금을 반환하시겠습니까?")) return;
		f.submit();
	}
</script>