<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  IP차단 설정
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_engine/include/common.lib.php';

	if(!isTable($tbl['deny_ip'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['deny_ip']);
	}

	if(file_exists($root_dir.'/_data/ip_msg.txt')) {
		$fp = fopen($root_dir.'/_data/ip_msg.txt', 'r');
		$data['msg'] = fgets($fp);
		$msg = $data['msg'];
		fclose($fp);
	}

	$_row = array(20 => 20, 30 => 30, 50 => 50, 100 => 100);
	$_search = array('ip' => '아이피', 'title' => '등록사유');

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$search_key = addslashes(trim($_GET['search_key']));
	$search = addslashes(trim($_GET['search']));
	if($search_key && $search) {
		$w .=" and `$search_key` like '%$search%'";
	}

	$sql = "select * from `".$tbl['deny_ip']."` where 1 $w order by `no` desc ";
	$sql_t = "select count(*) from `".$tbl['deny_ip']."` where 1 $w";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=20;

	$NumTotalRec = $pdo->row($sql_t);
	$PagingInstance=new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form method="get">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<div class="box_title first">
		<h2 class="title">IP접속 차단 설정</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search, "search_key", 2, "", $search_key)?>
					</div>
					<div class="area_input">
						<input type="text" name="search" value="<?=inputText($search)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 아이피가 검색되었습니다.
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
	</dl>
</div>
<!-- //정렬 -->
<!-- 검색 테이블 -->

<form name="ipFrm" method="post" target="hidden<?=$now?>" class="contentFrm" onsubmit="return scriptDelete(this)">
	<input type="hidden" name="body" value="config@ip_block.exe">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">아이피 목록</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:120px">
			<col>
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.ipFrm.check_pno, this.checked)"></th>
				<th scope="col">아이피</th>
				<th scope="col">등록사유</th>
				<th scope="col">등록자</th>
				<th scope="col">등록일</th>
				<th scope="col">수정</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>"></td>
				<td><?=stripslashes($data['ip'])?></td>
				<td class="left"><a href="?body=config@ip_block_register&no=<?=$data['no']?>"><strong><?=$data['title']?></strong></a></td>
				<td><?=$data['admin_id']?></td>
				<td><?=date('Y-m-d H:i',$data['reg_date'])?></td>
				<td><span class="box_btn_s"><input type="button" value="수정" onclick="location.href='?body=config@ip_block_register&no=<?=$data['no']?>'"></span></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn gray"><input type="submit" value="선택삭제"></span>
			<span class="box_btn blue"><input type="button" value="등록" onclick="location.href='?body=config@ip_block_register'"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" >
	<input type="hidden" name="body" value="config@ip_block.exe">
	<input type="hidden" name="exec" value="msg">
	<div class="box_title">
		<h2 class="title">차단메시지</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">차단메시지</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">차단메시지</th>
			<td>
				<input type="text" name="msg" class="input" size="50" value="<?=$msg?>">
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<dl class="list_msg left">
			<li>접속차단 IP로 접근시 노출되는 메시지 입니다.</li>
		</dl>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- //검색 테이블 -->

<script type="text/javascript">
	function scriptDelete(f) {
		if(!checkCB(f.check_pno,"삭제 처리할 아이피를")) return false;
		if (!confirm('선택한 아이피를 삭제 하시겠습니까?')) return;
		f.exec.value='delete';
		f.body.value='config@ip_block.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();

        printLoading();
	}
</script>