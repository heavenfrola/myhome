<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시물 리스트
	' +----------------------------------------------------------------------------------------------+*/

	if(!authChk("list")) msg("해당 게시판에 대한 권한이 없습니다.", "back");

	if(addField($tbl['intra_board'], 'partner_no', 'VARCHAR(20) NOT NULL default "0"')) {
		$pdo->query("alter table $tbl[intra_board] add index partner_no(partner_no)");
		echo '인덱스추가';
	}

	$w="";
	$search_txt = addslashes(trim($_GET['search_txt']));
	$db = addslashes(trim($_GET['db']));
	$body = addslashes($_GET['body']);

	if($w) $w=" and (".substr($w, 4).")";
	if($search_txt) $w .= " and a.`$_GET[search_key]` like '%{$search_txt}%'";

	//입점사정보
	$partner_f = "";
	$partner_j = "";
	if($cfg['use_partner_shop'] == 'Y') {
		if($admin['partner_no']>0) {
			$partner_no = $admin['partner_no'];
			$w .= " and a.`view_member` like '%@$partner_no@%'";
		}
		$_GET['partner_no'] = numberOnly($_GET['partner_no']);
		if($_GET['partner_no']) {
			$w .= " and a.`view_member` like '%@$_GET[partner_no]@%'";
		}
		$partner_f = ", b.corporate_name";
		$partner_j = "left join `$tbl[partner_shop]` b on a.partner_no=b.no";
		if($admin['level'] == 4){
			$tmpasql = " and no='$admin[partner_no]'";
		}
		$_partners = array();
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where `stat` between 2 and 4 $tmpasql order by corporate_name asc");
        foreach ($pres as $pdata) {
			$_partners[$pdata['no']] = stripslashes($pdata['corporate_name']);
		}
		unset($tmpasql, $pres, $pdata);
	}

	$sql="select a.* $partner_f from `$tbl[intra_board]` a $partner_j where a.`db`='$db' $w order by a.`no` desc";
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=10;

	$NumTotalRec = $pdo->row(str_replace("select a.* $partner_f from", "select count(*) from", $sql));
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$db_title = $_bconfig['title'];
	if(!$db_title) {
		switch($db) {
			case 'notice' :
				$db_title = '공지사항';
			break;
			case 'community' :
				$db_title = '사내커뮤니티';
			break;
		}
	}

	$_search = array('title'=>'제목','content'=>'내용','name'=>'작성자');
?>
<!-- 검색 폼 -->
<form name="seFrm" method="get" action="<?=$PHP_SELF?>">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="db" value="<?=$db?>">
	<input type="hidden" name="search" value="1">
	<div class="box_title first">
		<h2 class="title"><?=$db_title?></h2>
	</div>

	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search, "search_key", 2, "", $search_key)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_txt" value="<?=inputText($search_txt)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<?if($admin['partner_no']==0 && $cfg['use_partner_shop'] == 'Y') {?>
		<table class="tbl_search">
			<caption class="hidden"><?=$db_title?></caption>
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th>입점사</th>
				<td>
					<?=selectArray($_partners, 'partner_no', null, '전체', $_GET['partner_no'])?>
					<span class="box_btn_s blue"><input type="button" value="입점사 검색" onclick="ptn_search.open();"></span>
				</td>
			</tr>

		</table>
		<?}?>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$body?>&db=<?=$db?>'"></span>
		</div>
	</div>
</form>
<!-- // 검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 건의 내역이 검색되었습니다.
</div>
<!-- //검색 총합 -->
<table class="tbl_col">
	<colgroup>
		<col style="width:50px">
		<?if($_bconfig['auth_list'] == 4) {?>
		<col>
		<?}?>
		<col>
		<col style="width:50px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr class="tcol1">
			<th scope="col">번호</th>
			<?if($_bconfig['auth_list'] == 4) {?>
			<th scope="col">입점사</th>
			<?}?>
			<th scope="col">제목</th>
			<th scope="col">조회</th>
			<th scope="col">작성자</th>
			<th scope="col">등록일</th>
		</tr>
	</thead>
	<tbody>
		<?php
        foreach ($res as $data) {
			$rclass=($idx%2==0) ? "tcol2" : "tcol3";
			$fileicon=($data[upfile1] || $data[upfile2]) ? $fileicon="<img src=\"$engine_url/_manage/image/icon/ic_atc.gif\" align=\"absmiddle\" width=\"16\"> " : "";
			$data[title]=cutStr($data[title],120);
			if($_bconfig['auth_list'] == 4) {
				if($data['member_level']==4) {
					$bo_corporate_name = stripslashes($data['corporate_name']);
				}else {
					$bo_corporate_name = stripslashes($cfg['company_name']);
				}
			}
		?>
		<tr>
			<td><?=$idx?></td>
			<?if($_bconfig['auth_list'] == 4) {?>
			<td><?=$bo_corporate_name?></td>
			<?}?>
			<td class="left">&nbsp;<?=$fileicon?><a href="./?mode=view&no=<?=$data[no].$QueryString2?>"><?=$data[title]?></a><?=$data[total_comment] ? " [".$data[total_comment]."]" : "";?></td>
			<td><?=$data[hit]?></td>
			<td><?=$data[name]?></td>
			<td><?=date("Y-m-d", $data[reg_date])?></td>
		</tr>
		<?
			$idx--;
		}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
	<?if(authChk("write")){?>
	<div class="left_area">
		<span class="box_btn blue"><input type="button" value="글쓰기" onclick="location.href='./?mode=write<?=$QueryString4?>'"></span>
	</div>
	<?}?>
</div>
<script>
var ptn_search = new layerWindow('product@product_join_shop.inc.exe');
	ptn_search.psel = function(no,stat) {
		if(stat == "신청") {
			alert("선택한 입점사는 ["+stat+"] 상태입니다.");
			return false;
		}
		document.seFrm.partner_no.value = no;
		ptn_search.close();
	}
</script>