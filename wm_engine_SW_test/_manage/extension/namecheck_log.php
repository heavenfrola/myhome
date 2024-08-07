<?PHP

	// 실명인증 로그
	$search_str = addslashes(trim($_GET['search_str']));
	if($sFrom && $search_str)  {
		$w .= " and `$sFrom` like '%{$search_str}%'";
	}
	if($res_cd) $w .= ($red_cd == "1") ? " and `res_cd`='1'" : " and `res_cd` != '1'";

	$sql="select * from `$tbl[namecheck_log]` where 1 $w order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	$row=20;
	$block=10;

	foreach($_GET as $key=>$val) {
		if($key!="page" && !is_array($val)) $add_QueryString="&".$key."=".$val;

		if($add_QueryString) {
			$QueryString.=$add_QueryString;
			if($key!="body") {
				$xls_query.=$add_QueryString;
			}
		}
	}

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[namecheck_log]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];
	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<table class="tbl_row">
		<caption class="hidden">실행</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">실행</th>
			<td>
				<select name="res_cd">
					<option value="">전체</option>
					<option value="1" <?=checked($res_cd,"1",1)?>>성공</option>
					<option value="2" <?=checked($res_cd,"2",1)?>>실패</option>
				</select>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<select name="sFrom">
			<option value="name" <?=checked($sFrom,"name",1)?>>성명</option>
			<option value="jumin" <?=checked($sFrom,"jumin",1)?>>주민번호</option>
			<option value="ip" <?=checked($sFrom,"ip",1)?>>아이피</option>
			<option value="res_msg" <?=checked($sFrom,"res_msg",1)?>>결과메세지</option>
		</select>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="submit" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 내역이 검색되었습니다.
</div>
<table class="tbl_col">
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">실행</th>
			<th scope="col">성명</th>
			<th scope="col">주민번호</th>
			<th scope="col">조회타입</th>
			<th scope="col">실행코드</th>
			<th scope="col">실행일시</th>
			<th scope="col">IP</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
		?>
		<tr>
			<td><?=$idx?></td>
			<td><?=($data[res_cd] == "1") ? "<span class=\"p_color2\">성공</span>" : "<span class=\"p_color3\">실패</span>";?></td>
			<td><?=$data[name]?></td>
			<td><?=substr($data[jumin],0,6)?>-XXXXXXX</td>
			<td><?=($data[foreigner] == "1") ? "내국인" : "외국인";?></td>
			<td><?=$data[res_dcd]?></td>
			<td><?=date("Y-m-d H:i", $data[reg_date])?></td>
			<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank"><?=$data[ip]?></a></td>
		</tr>
		<?
				$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>