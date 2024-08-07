<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기간별 통계
	' +----------------------------------------------------------------------------------------------+*/

	$def_y=date("Y",$now);
	if(!$exec) {
		$m1=$m2=date("m",$now);
		$d1=$d2=date("d",$now);
	}

	$year = $pdo->assoc("SELECT min( FROM_UNIXTIME( `date` , '%Y' ) ) , max( FROM_UNIXTIME( `date` , '%Y' ) ) FROM `$tbl[log_search_day]`");
	if(!$year[0]) $year[0]=$def_y;
	$year[1]=$def_y;
	if(!$y1) $y1=$def_y;
	if(!$y2) $y2=$def_y;

	$unixtime='%Y';
	$ymd1=$y1;
	$ymd2=$y2;
	if($m1) {
		$unixtime.='%m';
		$ymd1.='-'.$m1;
		$ymd2.='-'.$m2;
		if($d2) {
			$unixtime.='%d';
			$ymd1.='-'.$d1;
			$ymd2.='-'.$d2;
		}
	}

	$ymd1 = strtotime($ymd1);
	$ymd2 = strtotime($ymd2)+86399;

	$w="date between '$ymd1' and '$ymd2'";
	$total=$pdo->row("select sum(`hit`) from `$tbl[log_search_day]` WHERE $w");

	$sql="SELECT sum( `hit` ) as shit , `keyword` FROM `$tbl[log_search_day]` WHERE $w GROUP BY `keyword` order by shit desc";

	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("SELECT count(*) FROM `$tbl[log_search_day]` WHERE $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$QueryString="&body=$body&stype=$stype";
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$r = array();
    foreach ($res as $data) {
		$per1=$per2=0;
		if($total>0) $per1=round((($data[shit]/$total)*100),2);
		if($data['shit'] > $max) $max=$data[shit];

		$data[keyword]=stripslashes($data[keyword]);
		$r[] = $data;
	}

?>
<form method="get" action="./" onSubmit="return searchKeyword(this)">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<div class="box_title first">
		<h2 class="title">기간별 통계</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">기간별 통계</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">기간</th>
			<td>
				<?=dateSelectBox($year[0],$year[1],"y1",$y1)?> 년
				<?=dateSelectBox(1,12,"m1",$m1,"전체")?> 월
				<?=dateSelectBox(1,31,"d1",$d1,"전체")?> 일
				~
				<?=dateSelectBox($year[0],$year[1],"y2",$y2)?> 년
				<?=dateSelectBox(1,12,"m2",$m2,"전체")?> 월
				<?=dateSelectBox(1,31,"d2",$d2,"전체")?> 일
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$body?>'"></span>
	</div>
</form>
<div class="box_title">
	<h2 class="title">사이트별 접속통계</h2>
</div>
<div class="box_middle">
	<p class="explain left"> 사이트 이동시 상대 서버에 링크된 흔적을 남기지 않습니다.</p>
</div>
<div class="graphFrm width">
	<table>
		<caption class="hidden">사이트별 접속통계</caption>
		<tr>
			<?foreach($r as $key => $val) {?>
			<?
				$val['per'] = @ceil(($val['shit']/ $max) * 100);
				$val['width'] = (500 / 100) * @ceil(($val['shit']/ $max) * 100);
				$val['tper'] = @round((($val[shit]/$total)*100),2);
			?>
			<th><?=$val['keyword']?> (<?=$val['tper']?>%)</th>
			<td>
				<dl class="grp">
					<dt style="width:<?=$val['width']?>px;"><span><?=$val['keyword']?></span></dt>
					<dd><?=$val['shit']?></dd>
				</dl>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom top_line"><?=$pg_res?></div>
</div>