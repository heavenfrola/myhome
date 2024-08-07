<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 교환내역
	' +----------------------------------------------------------------------------------------------+*/

	$search_str = addslashes(trim($_GET['search_str']));
	$search_sel = addslashes($_GET['search_sel']);
	$cuse = addslashes($_GET['cuse']);
	$mno = numberOnly($_GET['mno']);
	$smode = addslashes($_GET['smode']);
	$is_type = addslashes($_GET['is_type']);
	$body = addslashes($_GET['body']);

	if($search_sel && $search_str) {
		if($search_sel == 'name') $w.=" and b.name like '%$search_str%'";
		else $w.=" and a.`$search_sel` like '%{$search_str}%'";
	}
	if($cuse) $w.=" and `use`='$cuse'";

	$sql = "
        select
            a.member_name, a.member_no, a.member_id, a.code, a.milage_prc, a.reg_date, b.name as scname,
            (select `name` from `$tbl[coupon]` where `no`=a.`cno`) as `cname`
        from {$tbl['sccoupon_use']} a inner join {$tbl['sccoupon']} b on a.scno=b.no
        where 1 $w
        order by a.no desc
    ";

	$xls_query = makeQueryString('page', 'body');

	if($body == 'promotion@sccoupon_excel.exe') return;

	include $engine_dir."/_engine/include/paging.php";
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;
	$QueryString="&body=$body&is_type=$is_type&search_sel=$search_sel&search_str=$search_str&cuse=$cuse&smode=$smode&mno=$mno";

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['sccoupon_use']} a inner join {$tbl['sccoupon']} b on a.scno=b.no where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$rSql = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">소셜쿠폰 교환내역</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">소셜쿠폰 교환내역</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">검색</th>
			<td>
				<select name="search_sel">
					<option value="name" <?=checked($search_sel,"name",1)?>>쿠폰명</option>
					<option value="code" <?=checked($search_sel,"code",1)?>>쿠폰코드</option>
					<option value="member_name" <?=checked($search_sel,"member_name",1)?>>회원이름</option>
					<option value="member_id" <?=checked($search_sel,"member_id",1)?>>회원아이디</option>
				</select>
				<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="15">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<form name="cdFrm" action="./" method="post" target="hidden<?=$now?>" onSubmit="return checkFrm()">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 내역이 검색되었습니다.
		<span class="box_btn_s btns icon excel"><a href="./?body=promotion@sccoupon_excel.exe<?=$xls_query?>">엑셀다운</a></span>
	</div>
	<table class="tbl_col">
		<caption class="hidden">소셜쿠폰 교환내역 리스트</caption>
		<colgroup>
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">회원</th>
				<th scope="col">쿠폰코드</th>
				<th scope="col">쿠폰명</th>
				<th scope="col">교환적립금</th>
				<th scope="col">교환쿠폰명</th>
				<th scope="col">교환일자</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($rSql as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					$data['reg_date']=date('Y-m-d', $data['reg_date']);

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['member_name'] = strMask($data['member_name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }
			?>
			<tr>
				<td><?=$idx?></td>
				<td><?=$data[member_name]?>(<a href="javascript:;" onclick="viewMember('<?=$data[member_no]?>', '<?=$data[member_id]?>');"><?=$data['member_id_v']?></a>)</td>
				<td><?=$data[code]?></td>
				<td><?=cutStr(stripslashes($data[scname]),30)?></td>
				<td><?=parsePrice($data[milage_prc], true)?> 원</td>
				<td><?=cutStr(stripslashes($data[cname]),30)?></td>
				<td><?=$data['reg_date']?></td>
			</tr>
			<?
					$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pageRes?>
	</div>
</form>