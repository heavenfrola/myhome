<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상세접속로그 검색
	' +----------------------------------------------------------------------------------------------+*/

	$_search_type[referer]='접속경로';
	$_search_type_no[referer]=10;
	$_search_type[ip]='아이피';
	$_search_type_no[ip]=0;

	if(!$y1 || !$m1 || !$d1) {
		$y1=$y2=date("Y",$now);
		$m1=$m2=date("m",$now);
		$d1=$d2=date("d",$now);
	}

	$_log=array();

	for($ii=$y1.$m1.$d1; $ii<=$y2.$m2.$d2; $ii++) {
		$y=left($ii,4);
		$m=substr($ii,4,2);
		$d=right($ii,2);
		if($m>12 || $d>31) continue;
		$m+=0;
		$d+=0;

		$count_file=$root_dir."/".$dir['upload']."/".$dir['conut_log']."/".$y.$m."/".$d.".log";
		if(!is_file($count_file)) continue;
		$tmp_log=file($count_file);

		if($search_str && $search_type) {
			$tmp_log2=$tmp_log;
			$tmp_log=array();
			foreach($tmp_log2 as $key=>$val) {
				$data=explode("||",$val);
				$tmp=$data[$_search_type_no[$search_type]];
				if(!$tmp) continue;
				if(preg_match("/$search_str/",$tmp)) {
					$tmp_log[]=$val;
				}
			}
			unset($tmp_log2);

		}

		if ($conversion_s && is_array($conversion_s)) {
			$tmp_log2=$tmp_log;
			$tmp_log=array();
			foreach($tmp_log2 as $key=>$val) {
				$data=explode("||",$val);

				$conv = split("@",preg_replace("/^@|@$/","", trim($data[11])));
				for ($i = 0; $i <= count($conv); $i++) {
					if(in_array($conv[$i], $conversion_s)) {
						$tmp_log[]=$val;
						break;
					} else if ($conversion_s == $conv[$i]) {
						$tmp_log[]=$val;
						break;
					}
				}
			}
			unset($tmp_log2);
		}

		$_log=array_merge($_log,$tmp_log);
		unset($tmp_log);
	}

	$_browser_type = addslashes($_GET['_browser_type']);
	if($_browser_type) {
		$tmp_log = array();
		foreach($_log as $val) {
			$tmp = explode('||', $val);
			$tmp[14] = trim($tmp[14]);
			if($tmp[14] == '') $tmp[14] = 'pc';
			if($tmp[14] == $_browser_type) $tmp_log[] = $val;
		}
		$_log = $tmp_log;
	}

	$_log=array_reverse($_log);

	foreach($_GET as $key=>$val) {
		if (is_array($val)) {
			foreach ( $val as $arr1 => $arr2) {
				if ($arr2) $QueryString.="&".$key."[$arr1]=".$arr2;
			}
		} else {
			if($key!="page") $QueryString.="&".$key."=".$val;
		}
	}


	$mix = $pdo->assoc("select min(yy), max(yy) from `$tbl[log_day]`");
	if(!$mix[0] || !$mix[1]) $mix[0]=$mix[1]=date("Y");


	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	$row=40;
	$block=10;
	$NumTotalRec=count($_log);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);

	$pg_res=$PagingResult[PageLink];
	$idx=$NumTotalRec-($row*($page-1));

	$pstart=$row*($page-1);
	$pfinish=$pstart+$row;

?>
<form name="prdFrm" method="get" action="./">
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
			<th scope="row">기간</th>
			<td>
				<?=dateSelectBox($mix[0],$mix[1],"y1",$y1,"","","","",2)?> 년
				<?=dateSelectBox(1,12,"m1",$m1,"","","","",2)?> 월
				<?=dateSelectBox(1,31,"d1",$d1,"","","","",2)?> 일
				~
				<?=dateSelectBox($mix[0],$mix[1],"y2",$y2,"","","","",2)?> 년
				<?=dateSelectBox(1,12,"m2",$m2,"","","","",2)?> 월
				<?=dateSelectBox(1,31,"d2",$d2,"","","","",2)?> 일
			</td>
		</tr>
		<tr>
			<th scope="row">검색어</th>
			<td>
				<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
				<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">
				유입경로
				<div><a href="http://help.wisa.co.kr/manual/index/C0130" target="_blank" class="p_color">광고코드 등록안내</a></div>
			</th>
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
				<a href="?body=promotion@ban_list" class="sclink">설정</span>
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
				<p class="explain">접속경로 검색기능 업데이트 전인 <u>2013년 3월 이전</u> 접속은 모두 PC버전 접속으로 체크됩니다.</p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
	</div>
</form>
<div class="box_title">
	현재 관리자님의 아이피는 <strong id="total_prd"><?=$_SERVER['REMOTE_ADDR']?></strong> 입니다.
</div>
<table class="tbl_col">
	<caption class="hidden">상세접속로그 검색 리스트</caption>
	<colgroup>
		<col style="width:60px">
		<col style="width:150px">
		<col>
		<col style="width:100px">
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
			<th scope="col">키워드</th>
			<th scope="col">OS</th>
			<th scope="col">브라우저</th>
			<th scope="col">일시</th>
			<th scope="col">경로</th>
		</tr>
	</thead>
	<tbody>
		<?
			for($ii=$pstart; $ii<$pfinish; $ii++) {
				if(!$_log[$ii]) {
					break;
				}
				$_log[$ii]=str_replace("\n","",$_log[$ii]);
				$data=explode("||",$_log[$ii]);

				$data[ip]=$data[0];
				$data[id]=$data[1];
				$data['time']=$data[2];
				$data[yy]=$data[3];
				$data[mm]=$data[4];
				$data[dd]=$data[5];
				$data[hh]=$data[6];
				$data[week]=$data[7];
				$data[os]=$data[8];
				$data[browser]=$data[9];
				$data[referer]=$data[10];
				$data[conversion]=$data[11];
				$data[engine]=$data[12];
				$data[keyword]=$data[13];
				$data['browser_type'] = $data[14];

				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				if(!$data[referer]) {
					$data[referer]="즐겨찾기, 주소창에 직접입력";
					$oc="";
				}
				else {
					$oc="title=\"$data[referer]\" style=\"cursor:pointer\" onClick=\"window.open('$data[referer]')\"";
				}

				$search_engine = getSearchQuery($data[referer]);
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left">
				<a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank" title="IP 정보"><?=$data[ip]?></a>
				<?if($data['browser_type'] == 'mobile'){?>
				&nbsp;<img src="<?=$engine_url?>/_manage/image/mobile_icon.gif" alt="모바일">
				<?}?>
			</td>
			<td class="left" <?=$oc?>><?=cutStr($data[referer],55)?></td>
			<td <?=$oc?>><?=$data[keyword]?></td>
			<td><?=$data[os]?></td>
			<td><?=$data[browser]?></td>
			<td><?=date("y/m/d H:i:s",$data[time])?></td>
			<td><?=dispConversion($data[conversion])?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>