<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보엑셀 다운로드내역
	' +----------------------------------------------------------------------------------------------+*/

    if (fieldExist($tbl['member_xls_log'], 'reason') == false) {
        $pdo->query("
            alter table {$tbl['member_xls_log']}
                add column reason varchar(500) not null default '',
                add column ip varchar(15) not null default ''
        ");
    }

	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($search_type && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}
	if($admin[level] > 1){
		$w.=" and `admin_level` != 1"; // 위사는 제외
	}

	$_search_type[admin_id]='관리자아이디';

	$sql="select * from `$tbl[member_xls_log]` where 1 $w order by `no` desc";

	// 페이징 설정
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[member_xls_log]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
?>
<form name="prdFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">회원정보엑셀 다운로드내역</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 건의 로그가 검색되었습니다.
</div>
<table class="tbl_col">
	<caption class="hidden">회원정보엑셀 다운로드내역 리스트</caption>
	<colgroup>
		<col style="width:80px">
		<col style="width:150px">
		<col style="width:150px">
		<col>
		<col style="width:150px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">다운로드 일시</th>
			<th scope="col">관리자</th>
			<th scope="col">다운로드 사유</th>
			<th scope="col">접속 아이피</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$admin_level_arr=array(1=>"위사 시스템 관리자", 2=>"최고 관리자", 3=>"부관리자");
            foreach ($res as $key => $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
		?>
		<tr>
			<td><?=$idx-$key?></td>
			<td><?=date("Y-m-d H:i", $data['reg_date'])?></td>
			<td><?=$data['admin_id']?></td>
            <td class="left"><?=$data['reason']?></td>
			<td><?=$data['ip']?></td>

		</tr>
		<?php } ?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>