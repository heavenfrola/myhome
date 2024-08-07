<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상세접속로그 검색
	' +----------------------------------------------------------------------------------------------+*/

	$search_str = addslashes(trim($_GET['search_str']));
	$search_type = addslashes($_GET['search_type']);
	$y1 = numberOnly($_GET['y1']);
	$m1 = numberOnly($_GET['m1']);
	$d1 = numberOnly($_GET['d1']);
	$h1 = numberOnly($_GET['h1']);
	$y2 = numberOnly($_GET['y2']);
	$m2 = numberOnly($_GET['m2']);
	$d2 = numberOnly($_GET['d2']);
	$h2 = numberOnly($_GET['h2']);
	$no_direct = addslashes($_GET['no_direct']);
	$exec = addslashes($_GET['exec']);
	$conversion_s = $_GET['conversion_s'];

	if($cfg['log_file']=="Y") {
		include_once $engine_dir."/_manage/log/count_log_list_file.php";
		return;
	}

	$_search_type[referer]='접속경로';
	$_search_type[ip]='아이피';

	if($no_direct) {
		$w.=" and `referer`!=''";
	}

	if($_search_type[$search_type] && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}

	if (is_array($_GET[conversion_s])) {
		if (count($_GET[conversion_s]) == 1) $w .= " and `conversion` like '%@{$_GET[conversion_s][0]}%'";
		else {
			foreach ( $_GET[conversion_s] as $key => $val) {
				$val = addslashes($val);
				$_w[] = "`conversion` like '%@$val%'";
			}
			$w .= " and (".implode(" or ", $_w).")";
		}
	}

	if(!$exec) {
		$y1=$y2=date("Y",$now);
		$m1=$m2=date("m",$now);
		$d1="01";
		$d2=date("d",$now);
		$h1="00";
		$h2=23;
	}

	if($y1 && $m1 && $d1 && $h1 && $y1 && $m1 && $d2 && $h2) {
		$start_date = strtotime("$y1-$m1-$d1 $h1:00:00");
		$finish_date = strtotime("$y1-$m1-$d2 $h2:59:59");
		$w.=" and `time` between  $start_date and $finish_date";
	}

	$log_table = 'wm_log_count_'.date('ym', $start_date);

	$_browser_type = addslashes($_GET['_browser_type']);
	if($_browser_type && $start_date >= 1362063600 && $finish_date >= 1362063600) {
		$w .= " and browser_type='$_browser_type'";
	}

	foreach($_GET as $key=>$val) {
		if (is_array($val)) {
			foreach ( $val as $arr1 => $arr2) {
				if ($arr2) $QueryString.="&".$key."[$arr1]=".$arr2;
			}
		} else {
			if($key!="page") $QueryString.="&".$key."=".$val;
		}
	}

	$mix = $pdo->assoc("select min(`yy`) as min ,max(`yy`) as max from `$tbl[log_day]`");
	if(!$mix['min'] || !$mix['max']) $mix['min']=$mix['max']=date("Y");

    if ($body == 'log@count_log_list_excel.exe') return;

	if(isTable($log_table)) {
		$sql="select * from `$log_table` where 1 $w $union_sql order by `time` desc";

		include $engine_dir."/_engine/include/paging.php";

		$page = numberOnly($_GET['page']);
		$row = numberOnly($_GET['row']);

		if($page<=1) $page=1;
		$row=20;
		$block=10;

		$NumTotalRec += $pdo->row("select count(*) from `$log_table` where 1 $w");
		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString($QueryString);
		$PagingResult=$PagingInstance->result($pg_dsn);
		$sql.=$PagingResult[LimitQuery];

		$pg_res=$PagingResult[PageLink];
		$res = $pdo->iterator($sql);
		$idx=$NumTotalRec-($row*($page-1));
	}

?>
<form id="logFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="ext" value="">
	<div class="box_title first">
		<h2 class="title">상세접속로그 검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상세접속로그 검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row" rowspan="2">기간</th>
			<td>
				<?=dateSelectBox($mix['min'],$mix['max'],"y1",$y1)?> 년
				<?=dateSelectBox(1,12,"m1",$m1)?> 월
			</td>
		</tr>
		<tr>
			<td>
				<?=dateSelectBox(1,31,"d1",$d1)?> 일
				<?=dateSelectBox(0,23,"h1",$h1)?> 시
				~
				<?=dateSelectBox(1,31,"d2",$d2)?> 일
				<?=dateSelectBox(0,23,"h2",$h2)?> 시
			</td>
		</tr>
		<tr>
			<th scope="row">유입경로</th>
			<td>
				<?=selectArrayConv("conversion_s")?>
			</td>
		</tr>
		<?
			$convarr2 = selectArrayConv("conversion_s", 2);
			if($convarr2) {
		?>
		<tr>
			<th scope="row">
				배너광고유입
				<a href="?body=openmarket@ban_list" class="p_color">설정</span>
			</th>
			<td>
				<?=$convarr2?>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">접속경로</th>
			<td>
				<label class="p_cursor"><input type="radio" name="_browser_type" value="" <?=checked($_browser_type, '')?>> 전체</label>
				<label class="p_cursor"><input type="radio" name="_browser_type" value="pc" <?=checked($_browser_type, 'pc')?>> PC</label>
				<label class="p_cursor"><input type="radio" name="_browser_type" value="mobile" <?=checked($_browser_type, 'mobile')?>> 모바일</label>
			</td>
		</tr>
		<tr>
			<th scope="row">검색설정</th>
			<td><label class="p_cursor"><input type="checkbox" name="no_direct" value="Y" <?=checked($no_direct,"Y")?>> '즐겨찾기, 주소창에 직접입력' 제외</label></td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">&nbsp;&nbsp;
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<div class="box_title">
	* 현재 관리자님의 아이피는 <?=$_SERVER['REMOTE_ADDR']?> 입니다.
    <div class="btns">
        <span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="xlsdown()"></span>
    </div>
</div>
<table class="tbl_col">
	<caption class="hidden">상세접속로그 검색 리스트</caption>
	<colgroup>
		<col style="width:60px">
		<col style="width:150px">
		<col>
		<col style="width:120px">
		<col style="width:170px">
		<col style="width:150px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">아이피</th>
			<th scope="col">접속경로</th>
			<th scope="col">OS</th>
			<th scope="col">브라우저</th>
			<th scope="col">일시</th>
			<th scope="col">경로</th>
		</tr>
	</thead>
	<tbody>
		<?php
			if(isset($res)) {
            foreach ($res as $data) {
			$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				if($data[referer]=="") {
					$data[referer]="즐겨찾기, 주소창에 직접입력";
					$oc="";
				} else {
					$oc="title=\"$data[referer]\" style=\"cursor:pointer\" onClick=\"window.open('$data[referer]')\"";
				}
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left">
				<a href="https://wq.apnic.net/static/search.html?query=<?=$data['ip']?>" target="_blank" title="IP 정보"><?=$data['ip']?></a>
				<?if($data['browser_type'] == 'mobile'){?>
				&nbsp;<img src="<?=$engine_url?>/_manage/image/mobile_icon.gif" alt="모바일">
				<?}?>
			</td>
			<td class="left"><?//=cutStr($data[referer],200)?><?=$data[referer]?></td>
			<td><?=$data[os]?></td>
			<td><?=$data[browser]?></td>
			<td><?=date("y-m-d H:i:s",$data[time])?></td>
			<td><?=dispConversion($data['conversion'])?></td>
		</tr>
		<?
			$idx--;
			}}
		?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>
<script>
function xlsdown()
{
    let f = document.querySelector('#logFrm');
    let param = $(f).serialize().replace(/body=[^&]+/, '');

    location.href = './index.php?body=log@count_log_list_excel.exe'+param;
}
</script>