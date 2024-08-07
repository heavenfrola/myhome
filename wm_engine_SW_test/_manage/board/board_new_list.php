<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리
	' +----------------------------------------------------------------------------------------------+*/

	$listURL=urlencode(getURL());

	$sql="select * from `mari_config` order by `no` desc";
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=15;
	$block=10;

	$NumTotalRec = $pdo->row('select count(*) from mari_config');
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx = (($page-1)*$row)+1;

?>
<div class="box_title first">
	<h2 class="title">게시판 관리</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">게시판 관리</caption>
	<colgroup>
		<col style="width:60px">
		<col>
		<col style="width:120px">
		<col style="width:140px">
		<col style="width:120px">
		<col style="width:100px">
		<col style="width:120px">
		<col style="width:120px">
	<colgroup>
	<thead>
		<tr>
			<th scope="col">no</th>
			<th scope="col">게시판명</th>
			<th scope="col">게시판 종류</th>
			<th scope="col">사용 스킨</th>
			<th scope="col">스킨 편집</th>
			<th scope="col">등록된 글수</th>
			<th scope="col">수정</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$_board_type=array("basic"=>"일반", "gallery"=>"갤러리", "blog"=>"블로그", "bank"=>"입금확인");
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				$_total=$pdo->row("select count(*) from `mari_board` where `db`='$data[db]'");
				$board_type=preg_replace("/_(.*)/", "", $data[skin]);
				$board_type=$_board_type[$board_type] ? $board_type : "basic";
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><a href="<?=$root_url?>/board/?db=<?=$data[db]?>" target="_blank"><?=$data[title]?> <img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="<?=$data[title]?> 게시판 보기" width="11" height="11" align="absmiddle"></a></td>
			<td><?=$_board_type[$board_type]?> 게시판</td>
			<td><?=$data[skin]?></td>
			<td><span class="box_btn_s"><input type="button" value="편집하기" onClick="location.href='./?body=design@board&skin_name=<?=$data[skin]?>';"></span></td>
			<td><?=$_total?></td>
			<td><span class="box_btn_s"><input type="button" value="속성 수정" onClick="location.href='./?body=board@board_new&board_type=<?=$board_type?>&no=<?=$data[no]?>&listURL=<?=$listURL?>';"></span></td>
			<td><span class="box_btn_s"><input type="button" value="게시판 삭제" onClick="delBoard('<?=str_replace("'", "", $data[title])?>', '<?=$data[db]?>');"></span></td>
		</tr>
		<?
				$idx++;
			}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
	<div class="left">
		<span class="box_btn blue"><input type="button" value="신규 게시판생성" onclick="goM('board@board_new')"></span>
	</div>
</div>

<script language="JavaScript">
	function delBoard(bname, db){
		if(!confirm('\n※ 주의 ※ \n\n['+bname+'] 에 등록된 모든 글과 관련 첨부 파일 자료들이 모두 삭제됩니다.        \n\n삭제를 진행하시겠습니까?')) return;
		window.frames[hid_frame].location.href='./?body=board@board_edit.exe&delete_db='+db;
	}
</script>