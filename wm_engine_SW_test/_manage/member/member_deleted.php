<?PHP

	$_search_type = array('member_id' => '회원아이디', 'name' => '이름', 'cell' => '휴대폰');
	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($search_str) {
		if($search_type == 'cell' && strpos($search_type, '-') === false) {
			$w .= " and replace(a.$search_type, '-', '') like '%$search_str%'";
		} else {
			$w .= " and a.$search_type like '%$search_str%'";
		}
	}

	include $engine_dir."/_engine/include/paging.php";

    $add_fd = '';
    if(fieldExist($tbl['member'], 'last_order') == true) {
        $add_fd .= ", b.last_order";
    }
	$sql = "select a.*, b.level, b.last_con $add_fd from $tbl[member_deleted] a inner join $tbl[member] b using(no) where 1 $w order by no desc";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page=1;
	if(!$row) $row=10;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[member_deleted] a inner join {$tbl['member']} b using(no) where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$group=getGroupName();

	function parseDeletedMember($res) {
		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$temp = '';
		for($i = 0; $i < strlen($data['name']); $i++) {
			if($i> 2) {
				$temp .= '＊';
				if(preg_match('/[\x80-\xFE]/', substr($data['name'], $i, 1))) $i+=2;
			} else {
				$temp .= substr($data['name'], $i, 1);
			}
		}
		$data['name'] = $temp;

		$email = explode('@', $data['email']);
		if(strlen($email[0]) < 3) {
			$temp = '**';
		} else {
			$temp  = substr($email[0], 0, 2);
			$temp .= str_repeat('*', strlen(preg_replace('/@.*$/', '', $email[0]))-2);
		}

		$data['email'] = $temp.'@'.$email[1];

		$data['last_con'] = ($data['last_con']> 0) ? date('Y-m-d', $data['last_con']) : '-';
		$data['last_order'] = ($data['last_order']> 0) ? date('Y-m-d', $data['last_order']) : '-';

		return $data;
	}

?>
<form id="search" method="get" action="./index.php">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<div class="box_title first">
		<h2 class="title">휴면 회원 조회</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type, 'search_type', 2, '', $search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
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
<form id="memberFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@member_deleted.exe">
	<input type="hidden" name="exec">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 명의 휴면 회원이 검색되었습니다.
	</div>
	<table class="tbl_col">
		<caption class="hidden">휴면 회원 리스트</caption>
		<colgroup>
			<col style="width:40px;">
			<col style="width:70px;">
			<col>
			<col style="width:120px;">
			<col>
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:120px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type='checkbox' onclick="checkAll($('.list_check'), this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">아이디</th>
				<th scope="col">이름</th>
				<th scope="col">이메일</th>
				<th scope="col">적립금</th>
				<th scope="col">예치금</th>
				<th scope="col">휴면계정전환일</th>
				<th scope="col">최종접속일</th>
				<th scope="col">최종주문일</th>
			</tr>
		</thead>
		<tbody>
			<?while($data = parseDeletedMember($res)) {?>
			<tr>
				<td><input type="checkbox" name="mno[]" class="list_check" value="<?=$data['no']?>"></td>
				<td><?=$idx--?></td>
				<td><a href="#" onclick="viewMember('<?=$data['no']?>', '<?=$data['member_id']?>'); return false;"><?=$data['member_id']?></a></td>
				<td><?=$data['name']?></td>
				<td><?=$data['email']?></td>
				<td><?=number_format($data['milage'])?></td>
				<td><?=number_format($data['emoney'])?></td>
				<td><?=date('Y-m-d', $data['reg_date'])?></td>
				<td><?=$data['last_con']?></td>
				<td><?=$data['last_order']?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="pageRes"><?=$pg_res?></div>
		<div class="left_area">
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="removeDeleted()"></span>
			<span class="box_btn gray"><input type="button" value="휴면 해제" onclick="cancelDeleted()"></span>
		</div>
	</div>
</form>
<script type="text/javascript">
	var f = document.getElementById('memberFrm');
	function removeDeleted() {
		if($(':checked[name="mno[]"]').length < 1) {
			window.alert('삭제할 회원을 선택해 주세요.');
			return false;
		}
		if(confirm('선택한 회원을 데이터베이스에서 완전히 삭제하시겠습니까?')) {
			f.exec.value = 'remove';
			f.submit();
		}
	}
	function cancelDeleted() {
		if($(':checked[name="mno[]"]').length < 1) {
			window.alert('휴면 해제할 회원을 선택해 주세요.');
			return false;
		}
		if(confirm('선택한 회원을 휴면 해제 하시겠습니까?')) {
			f.exec.value = 'restore';
			f.submit();
		}
	}
</script>