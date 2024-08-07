<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 사용내역
	' +----------------------------------------------------------------------------------------------+*/

	if($admin[level]>1 || $swisa) {
		$w2=" and `admin_id`!='wisa'";
	}
	$search_str = addslashes(trim($_GET['search_str']));
	if($sFrom && $search_str) $w .= " and `$sFrom` like '%{$search_str}%'";
	if($stat) $w .= " and `stat`='$stat'";

	$sql="select * from `$tbl[coupon_log]` where 1 $w $w2 order by `no` desc";

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

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[coupon_log]` where 1 $w $w2");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$_stat=array(1=>"발행", 2=>"수정", 3=>"삭제");

?>
<form method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">온라인/시리얼쿠폰 사용내역</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">온라인/시리얼쿠폰 사용내역</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">검색</th>
			<td>
				<select name="sFrom">
					<option value="cname" <?=checked($sFrom,"cname",1)?>>쿠폰명</option>
					<option value="admin_id" <?=checked($sFrom,"admin_id",1)?>>관리자 아이디</option>
					<option value="ip" <?=checked($sFrom,"ip",1)?>>아이피</option>
				</select>
				<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="15">
			</td>
		</tr>
		<tr>
			<th scope="row">실행</td>
			<td>
				<select name="stat">
					<option value="">전체</option>
					<option value="1" <?=checked($stat,"1",1)?>><?=$_stat[1]?></option>
					<option value="2" <?=checked($stat,"2",1)?>><?=$_stat[2]?></option>
					<option value="3" <?=checked($stat,"3",1)?>><?=$_stat[3]?></option>
				</select>
			</td>
		</tr>
	</table>
	<?if($admin[level]==1) {?>
	<div class="box_middle2 left">
		<label class="p_cursor"><input type="checkbox" name="swisa" value="1" <?=checked($swisa,1)?> onClick="this.form.submit();"> wisa 제외</label>
		<span class="explain">(일반 고객은 wisa 아이디가 나타나지 않습니다)</span>
	</div>
	<?}?>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 로그가 검색되었습니다.
</div>
<table class="tbl_col">
	<caption class="hidden">운영자 정보</caption>
	<colgroup>
		<col style="width:60px">
		<col>
	<colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">실행</th>
			<th scope="col">쿠폰타입</th>
			<th scope="col">쿠폰명</th>
			<th scope="col">관리자 아이디</th>
			<th scope="col">수정결과</th>
			<th scope="col">실행일시</th>
			<th scope="col">IP</th>
		</tr>
	</thead>
	<tbody>
		<?
			$_result=array("name"=>"쿠폰명", "code"=>"쿠폰 코드", "stype"=>"쿠폰 종류", "down_type"=>"쿠폰 다운권한", "sale_prc"=>"할인 금액", "prc_limit"=>"사용 제한 결제액", "sale_limit"=>"최대 할인 금액", "release_limit"=>"발급 제한", "release_limit_ea"=>"발급 제한 장수", "download_limit"=>"다운로드 제한", "download_limit_ea"=>"다운로드 제한 장수", "rdate_type"=>"발급 기간", "udate_type"=>"사용 기간");
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				$data[result]="";
				if($data['stat'] != "3"){
					$_s1=explode("<wisa>", $data[content]);
					foreach($_s1 as $k=>$v){
						$_s2=explode(":", $v);
						if($_result[$_s2[0]]){
							$val=$_s2[1];
							if($_s2[0] == "stype"){
								$val=($val == "1") ? "장바구니 쿠폰 " : "상품별 할인 쿠폰";
							}
							if($_s2[0] == "down_type"){
								if($val == "A") $val="전체";
								elseif($val == "B") $val="회원등급별";
								elseif($val == "C") $val="회원가입시 자동발급";
							}
							if($_s2[0] == "release_limit"){
								$val=($val == "1") ? "무한" : "한정";
							}
							if($_s2[0] == "download_limit"){
								$val=($val == "1") ? "무한" : "한정";
							}
							if($_s2[0] == "rdate_type"){
								$val=($val == "1") ? "무제한" : "기간 설정";
							}
							if($_s2[0] == "udate_type"){
								$val=($val == "1") ? "무제한" : "기간 설정";
							}
							$data[result] .= $_result[$_s2[0]]." : ".$val."<br>";
						}
					}
				}
		?>
		<tr>
			<td><?=$idx?></td>
			<td><?=$_stat[$data['stat']]?></td>
			<td><? if($data[is_type]){ echo ($data[is_type] == "A") ? "온라인" : "시리얼"; } ?></td>
			<td><?=$data[cname]?></td>
			<td><?=$data[admin_id]?></td>
			<td><? if($data[result]){ ?><a href="javascript:;" onmouseover="showToolTip(event,'<?=$data[result]?>')" onmouseout="hideToolTip();" >결과내역</a><? }else echo "&nbsp;"; ?></td>
			<td><?=date("Y-m-d H:i", $data[reg_date])?></td>
			<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank"><?=$data[ip]?></a></td>
		</tr>
		<?
				$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>