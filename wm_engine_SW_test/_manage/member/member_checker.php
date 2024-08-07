<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  특별회원그룹 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!isTable($tbl['member_checker'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['member_checker']);
	}

	$res = $pdo->iterator("select * from $tbl[member_checker] order by no asc");

	function parseData($res) {
		global $parse_idx;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['name'] = stripslashes($data['name']);
		$data['members'] = number_format($data['members']);
		$parse_idx++;
		return $data;
	}

?>
<div class="box_title first">
	<h2 class="title">특별회원그룹 설정</h2>
</div>
<div class="box_middle left">
	<ul class="list_msg">
		<li>특정VIP/블랙리스트 회원들을 별도의 특별그룹으로 관리하실수 있습니다.</li>
		<li>이벤트/사은품 지급 완료여부 등 다양한 용도로 사용 가능하며, <a href="?body=5010" target="_blank">회원 조회</a>메뉴에서 특별그룹별로 검색하실수 있습니다.</li>
		<li>몇 만 이상의 많은 회원이 있는 경우, 가급적 접속자가 적은 시간을 이용해서 등록/삭제를 진행해 주시기 바랍니다.</li>
	</ul>
</div>

<table class="tbl_col">
	<caption class="hidden">특별회원그룹 설정</caption>
	<colgroup>
		<col style="width:100px">
		<col>
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순번</th>
			<th scope="col">그룹명</th>
			<th scope="col">회원수</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseData($res)){?>
		<tr>
			<td><?=$parse_idx?></td>
			<td class="left"><a href="?body=member@member_checker_detail&no=<?=$data['no']?>"><?=$data['name']?></a></td>
			<td><?=$data['members']?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="삭제" onclick="deleteMemberChecker(<?=$data['no']?>)"></span>
			</td>
		</tr>
		<?}?>
	</tbody>
</table>

<form method="post" action="./index.php" target="hidden<?=$now?>" id="controlTab">
	<input type="hidden" name="body" value="member@member_checker.exe">
	<input type="hidden" name="exec" value="register">
	<ul class="tabs">
		<li class="selected">신규등록</li>
	</ul>
	<div class="context">
		<div class="box_bottom left">
			신규그룹명 <input type="text" name="name" class="input" size="20"> <span class="box_btn_s"><input type="submit" value="확인"></span>
			<p class="explain icon">회원이 많은 경우 등록에 몇초간의 시간이 소요될수 있습니다.</p>
		</div>
	</div>
</form>

<script type="text/javascript">
	function deleteMemberChecker(no) {
		if(confirm('선택한 그룹을 삭제하시겠습니까?')) {
			$.post('?body=member@member_checker.exe', {'exec':'delete', 'no':no}, function() {
				location.reload();
			});
		}
	}
</script>