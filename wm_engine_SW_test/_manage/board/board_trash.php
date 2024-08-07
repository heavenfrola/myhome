<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시물 휴지통
	' +----------------------------------------------------------------------------------------------+*/

	if(!$tblname) $tblname = 'mari_board';
	$w = " and tblname='$tblname'";

	$_tblname = array(
		'mari_board' => '게시물',
		$tbl['qna'] => '상품Q&A',
		$tbl['review'] => '상품후기',
	);
	$tblname_s = $_tblname[$tblname];
	$_tbltype = array(
		'mari_board' => 'bbs',
		$tbl['qna'] => 'qna',
		$tbl['review'] => 'rev',
	);
	$tbltype = $_tbltype[$tblname];

	if($tblname == 'mari_board') {
		$_db = array();
		$res = $pdo->iterator("select db, title from mari_config");
        foreach ($res as $data) {
			$_db[$data['db']] = stripslashes($data['title']);
		}
	}

	// 검색
	$_search_type = array(
		'title' => '글제목',
		'name' => '작성자',
	);

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		$w .= " and $search_type like '%$search_str%'";
	}

	// 삭제 리스트
	$sql = "select no, tblname, db, title, name, reg_date, del_date from $tbl[common_trashbox] where 1 $w order by reg_date desc ";
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page <= 1) $page=1;
	$row = 20;
	$block = 10;

	$QueryString = '';
	$NumTotalRec = $pdo->row("select count(*) from $tbl[common_trashbox] where 1 $w order by reg_date desc");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];
	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	function parseTrash($res) {
		global $_tblname, $_db, $scfg;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data = array_map('stripslashes', $data);
		$data['db_s'] .= $_db[$data['db']];
		$data['reg_date_s'] = date('Y-m-d', $data['reg_date']);
		$data['del_date_s'] = date('Y-m-d', $data['del_date']);

        // 리스트에서 개인정보 마스킹
        if ($scfg->comp('use_member_list_protect', 'Y') == true) {
            $data['name'] = strMask($data['name'], 2, '＊');
        }

		return $data;
	}

?>
<form method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title"><?=$tblname_s?> 휴지통</h2>
	</div>
	<div class="box_search box_search">
		<div class="box_input">
			<div class="select_input shadow">
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
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
	</div>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 데이터가 <?=$tblname_s?> 휴지통 에서 검색되었습니다.
	</div>
</form>
<form id="trashFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="board@board_trash.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="tblname" value="<?=$tblname?>">

	<table class="tbl_col">
		<caption class="hidden">휴지통 리스트</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:250px">
			<col>
			<col style="width:100px">
			<col style="width:120px">
			<col style="width:120px">
			<?if($cfg['use_trash_'.$tbltype] == "Y" && $cfg['trash_'.$tbltype.'_trcd'] > 0) {?>
			<col style="width:120px">
			<?}?>
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="$('.ano').prop('checked', this.checked);"></th>
				<th scope="col">게시판</th>
				<th scope="col">제목</th>
				<th scope="col">작성자</th>
				<th scope="col">작성일시</th>
				<th scope="col">삭제일시</th>
				<?if($cfg['use_trash_'.$tbltype] == "Y" && $cfg['trash_'.$tbltype.'_trcd'] > 0) {?>
				<th scope="col">삭제 예정일</th>
				<?}?>
			</tr>
		</thead>
		<tbody>
			<?while($data = parseTrash($res)) {?>
			<tr>
				<td><input type="checkbox" name="no[]" class="ano" value="<?=$data[no]?>"></td>
				<td><?=$data['db_s']?></td>
				<td class="left"><?=$data['title']?></td>
				<td><?=$data['name']?></td>
				<td><?=$data['reg_date_s']?></td>
				<td><?=$data['del_date_s']?></td>
				<?if($cfg['use_trash_'.$tbltype] == "Y" && $cfg['trash_'.$tbltype.'_trcd'] > 0) {?>
				<td><?=date('Y-m-d', strtotime($cfg['trash_'.$tbltype.'_trcd'].'day', $data['del_date']))?></td>
				<?}?>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn"><input type="submit" value="복구" onclick="return commonTrash(1)"></span>
		</div>
		<div class="right_area">
			<span class="box_btn gray"><input type="submit" value="휴지통 비우기" onclick="return commonTrash(2, true)"></span>
			<span class="box_btn gray"><input type="submit" value="영구 삭제" onclick="return commonTrash(2)"></span>
		</div>
		<?=$pg_res?>
	</div>
</form>
<script type="text/javascript">
function commonTrash(exec, trnc) {
	if(trnc != true && $(':checked.ano').length < 1) {
		window.alert(((exec == 1) ? '복구' : '삭제')+'할 게시물을 선택해주세요.');
		return false;
	}

	var f = document.getElementById('trashFrm');
	var del_msg = '선택한 모든 게시물(첨부파일 포함)이 영구삭제됩니다.\n영구삭제된 게시물은 절대 복구할 수 없습니다.\n선택한 모든 게시물을 정말로 영구삭제하시겠습니까?';
	switch(exec) {
		case 1 :
			f.exec.value = 'restore';
			del_msg = '선택한 게시물을 복구하시겠습니까?';
		break;
		case 2 :
			f.exec.value = (trnc == true) ? 'truncate' : 'remove';
			if(trnc == true) {
				del_msg = '휴지통을 비우면 휴지통의 모든 게시물(첨부파일 포함)이 영구삭제됩니다.\n영구삭제된 게시물은 절대 복구할 수 없습니다.\n정말로 <?=$tblname_s?> 휴지통을 비우시겠습니까?';
			}
		break;
	}

	return confirm(del_msg);
}
</script>