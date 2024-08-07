<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  포털/검색엔진 통계
	' +----------------------------------------------------------------------------------------------+*/

	$expr = '/-([0-9])(?![0-9])/';

	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	if(!$start_date) $start_date = date('Y-m-d');
	if(!$finish_date) $finish_date = $start_date;

	if($all_date != 'Y' && $start_date && $finish_date) {
		$w .= " and date(concat(`yy`,'-',`mm`,'-',`dd`)) between '$start_date' and '$finish_date'";
	}

	$engine = array();
	$keyword = array();


	// 검색 엔진별 통계
	$idx = 0;

	$sql = $pdo->iterator("select `engine`, sum(`hit`) as `cnt`, `yy`,`mm`,`dd` from `$tbl[log_search_engine]` where 1 $w group by `engine` order by `cnt` desc");
    foreach ($sql as $data) {
		if (!$max_engine) $max_engine = $data[cnt];
		$engine[$idx][name] = $data[engine];
		$engine[$idx][cnt] = $data[cnt];
		$engine_total_cnt += $data[cnt];
		$idx++;
	}
	foreach ($_GET as $key => $val) {
		if ($val && $key != "choice_e" && $key != "body") $query_string .= "&$key=$val";
	}


	// 검색어별 통계
	if ($_GET[choice_e]) {
		$wk .= " and `engine` = '$_GET[choice_e]'";
		$wtitle = "<font color='red'>[$_GET[choice_e] 서치]</font> ";
	}

	$keyword_total_cnt = $pdo->row("select sum(`hit`) from `$tbl[log_search_engine]` where 1 $w $wk");
	$idx = 0;
	$sql = $pdo->iterator("select `keyword`, sum(`hit`) as `cnt` from `$tbl[log_search_engine]` where 1 $w $wk group by `keyword` order by `cnt` desc limit 30");
    foreach ($sql as $data) {
		if (!$max_keyword) $max_keyword = $data[cnt];
		if (!$data[keyword]) continue;
		$keyword[$idx][name] = rawurldecode($data[keyword]);
		$keyword[$idx][cnt] = $data[cnt];
		$idx++;
	}

	$min_year = $pdo->row("select min(`yy`) from `$tbl[log_search_engine]`");
	if (!$min_year) $min_year = date("Y");


	for ($x = 0; $x <= 1; $x++) {
		$prefix = ($x == 0) ? "_s" : "_e";

		for($i = $min_year; $i <= date("Y"); $i++) {
			$sel = ($i == ${"yy".$prefix}) ? "selected" : "";
			${"select_yy".$prefix} .= "<option value='$i' $sel>{$i}년</option>\n";
		}

		for($i = 1; $i <= 12; $i++) {
			$sel = ($i == ${"mm".$prefix}) ? "selected" : "";
			${"select_mm".$prefix} .= "<option value='$i' $sel>{$i}월</option>\n";
		}

		for($i = 1; $i <= 31; $i++) {
			$sel = ($i == ${"dd".$prefix}) ? "selected" : "";
			${"select_dd".$prefix} .= "<option value='$i' $sel>{$i}일</option>\n";
		}
	}

?>
<input type="hidden" name="body" value="<?=$body?>">
<input type="hidden" name="choice_e" value="<?=$choice_e?>">
<div class="box_title first">
	<h2 class="title">포털/검색엔진 통계</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">포털/검색엔진 통계/caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th scope="row">기간</th>
		<td>
			<?=setDateBunttonSet('start_date', 'finish_date', $start_date, $finish_date, true)?>
		</td>
	</tr>
</table>
<div class="box_bottom">
	<span class="box_btn blue"><input type="submit" value="기간별 보기" ></span>
</div>
<div class="box_title">
	<h2 class="title">검색엔진별 통계</h2>
</div>
<div class="graphFrm width bottom_line">
	<table>
		<caption class="hidden">검색엔진별 통계</caption>
		<tr>
			<th><a href="?body=<?=$body?>"><u>전체</u></a></th>
			<td>
				<dl class="grp">
					<dt style="width:500px;"><span>전체</span></dt>
					<dd><?=number_format($engine_total_cnt)?></dd>
				</dl>
			</td>
		</tr>
		<tr>
		<?
			foreach ($engine as $key => $val) {
				$val['per'] = @ceil(($val['cnt']/ $engine_total_cnt) * 100);
				$val['width'] = (500 / 100) * @ceil(($val['cnt']/ $engine_total_cnt) * 100);
		?>
		<tr>
			<th><a href="?body=<?=$body?>&choice_e=<?=$val[name]?><?=$query_string?>"><u><?=$val[name]?></u></a> (<?=$val['per']?>%)</th>
			<td>
				<dl class="grp">
					<dt style="width:<?=$val['width']?>px;"><span><?=$val['keyword']?></span></dt>
					<dd><?=number_format($val['cnt'])?></dd>
				</dl>
			</td>
		</tr>
		<?}?>
	</table>
</div>
<div class="box_title">
	<h2 class="title"><?=$wtitle?>키워드별 통계 (상위 30개)</h2>
</div>
<div class="graphFrm width bottom_line">
	<table>
		<caption class="hidden"><?=$wtitle?>키워드별 통계 (상위 30개)</caption>
		<?foreach ( $keyword as $key => $val) {?>
		<tr>
			<?
				$val['per'] = @ceil(($val['cnt']/ $keyword_total_cnt) * 100);
				$val['width'] = (500 / 100) * @ceil(($val['cnt']/ $keyword_total_cnt) * 100);
			?>
			<th><a href="?body=<?=$body?>&choice_e=<?=$val[name]?><?=$query_string?>"><u><?=$val[name]?></u></a> (<?=$val['per']?>%)</th>
			<td>
				<dl class="grp">
					<dt style="width:<?=$val['width']?>px;"><span><?=$val['keyword']?></span></dt>
					<dd><?=number_format($val['cnt'])?></dd>
				</dl>
			</td>
		</tr>
		<?}?>
	</table>
</div>