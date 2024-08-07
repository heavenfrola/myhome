<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  자주쓰는 댓글 설정
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/shop_detail.lib.php";

	if(!isTable($tbl['often_comment'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['often_comment']);
	}

	$where = '';

	$often = $_GET['often'];
	$cate = addslashes($_GET['cate']);
	if(!$cate && isset($_GET['cate']) == false) $cate = $often;

	$_search_m = array("title" => "제목", "content" => "내용");
	$search_method = $_GET['search_method'];
	$a_search_str = addslashes(trim($_GET['search_str']));
	if(array_key_exists($search_method, $_search_m) && $a_search_str) {
		$where .= " and `$search_method` like '%$a_search_str%'";
		$cate = 0;
	}
	if($cate && $cate != "all") {
		$where2 = " and `cate` = '$cate'";
	}

	$sql = "select * from `$tbl[often_comment]` where 1 $where $where2 order by `reg_date` desc";

	// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 10;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[often_comment]` where 1 $where $where2 ");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	// 상태별 통계
	$_tabcnt = array();
	$_tmpres = $pdo->iterator("select cate, count(distinct no) as cnt from $tbl[often_comment]  where 1 $where group by cate");
    foreach ($_tmpres as $_tmp) {
		$_tabcnt[$_tmp['cate']] = $_tmp['cnt'];
		$_tabcnt['total'] += $_tmp['cnt'];
	}
	${'list_tab_active_'.($cate ? $cate : 'all')} = 'class="active"';

	$list_tab_qry = makeQueryString(true, 'cate', 'page');
	$qs_without_row = makeQueryString(true, 'row', 'page');

?>
<form name="searchFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="often" value="<?=$often?>">
	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title">자주쓰는 댓글 설정</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_m, 'search_method', 2, null, $search_method)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($_GET['search_str'])?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>&often=<?=$often?>'"></span>
		</div>
	</div>
	<!-- //검색 폼 -->
	<!-- 검색 총합 -->
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>&cate=all" <?=$list_tab_active_all?>>전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&cate=qna" <?=$list_tab_active_qna?>>상품Q&A<span><?=number_format($_tabcnt['qna'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&cate=cs" <?=$list_tab_active_cs?>>1:1상담<span><?=number_format($_tabcnt['cs'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&cate=review" <?=$list_tab_active_review?>>상품후기<span><?=number_format($_tabcnt['review'])?></span></a></li>
		</ul>
	</div>
	<!-- //검색 총합 -->
	<!-- 검색 테이블 -->
	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="20" <?=checked($row,20,1)?>>20</option>
					<option value="30" <?=checked($row,30,1)?>>30</option>
					<option value="50" <?=checked($row,50,1)?>>50</option>
					<option value="100" <?=checked($row,100,1)?>>100</option>
					<option value="500" <?=checked($row,500,1)?>>500</option>
				</select>
			</dd>
		</dl>
	</div>
</form>

<form name="prdFrm" method="get" action="./">
<input type="hidden" name="body" value="<?=$body?>">
<input type="hidden" name="exec" value="">
<input type="hidden" name="ext" value="">
	<!-- //정렬 -->
	<table class="tbl_col">
		<caption class="hidden">자주쓰는 댓글 설정</caption>
		<colgroup>
			<col style="width:50px;">
			<col style="width:50px;">
			<col style="width:120px;">
			<col style="width:300px;">
			<col>
			<col style="width:80px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">분류</th>
				<th scope="col">제목</th>
				<th scope="col">내용</th>
				<th scope="col">수정</th>
			</tr>
		</thead>
		<tbody>
            <?php foreach ($res as $data) {?>
			<tr>
				<td>
					<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>">
				</td>
				<td><?=$idx--?></td>
				<td><?=$_often_cate_name[$data['cate']]?></td>
				<td class="left"><a href="#" onclick="wisaOpen('./pop.php?body=member@often_comment_register.frm&cno=<?=$data['no']?>', 'oftenComment_pop', 'status=no, width=850px, height=420px');return false;"><?=stripslashes($data['title'])?></a></td>
				<td class="left"><?=$data['content']?></td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="wisaOpen('./pop.php?body=member@often_comment_register.frm&cno=<?=$data['no']?>', 'oftenComment_pop', 'status=no, width=850px, height=420px');return false;"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pageRes?>
	</div>
		<div class="box_middle2 left">
		<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="deleteRev(document.prdFrm, '<?=$cfg['use_trash_rev']?>');"></span>
		<div class="right_area">
			<span class="box_btn blue"><input type="button" value="등록" onclick="wisaOpen('./pop.php?body=member@often_comment_register.frm', 'oftenComment_pop', 'status=no, width=850px, height=420px');return false;"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

<script type="text/javascript">

function deleteRev(f, is_trash){
	if(!checkCB(f.check_pno,'삭제할 자주쓰는 댓글을 선택해주세요.')) return;
	if (!confirm('선택하신 댓글을 삭제 하시겠습니까?')) return;
	f.body.value = 'member@often_comment_register.exe';
	f.exec.value = 'delete';
	f.method = 'post';
	f.target = hid_frame;
	f.submit();
}
</script>