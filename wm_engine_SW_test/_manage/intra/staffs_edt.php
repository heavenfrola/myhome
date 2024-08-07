<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원 등록/관리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	$_team=getIntraTeam();
	$_team1=array();
	foreach($_team as $key=>$val){
		if(!$_team[$key][ref]){
			$_team1[$key]=$_team[$key][name];
		}
	}

	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	$no = numberOnly($_GET['no']);
	$body = $_GET['body'];
	$w = '';

	$search_str = addslashes(trim($_GET['search_str']));
	$search_type = addslashes(trim($_GET['search_type']));
	if($search_type && $search_str) {
		$w.=" and `$search_type` like '%$search_str%'";
	}

	include $engine_dir."/_engine/include/paging.php";
	if($page < 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$sql = "select * from $tbl[mng] where `level` != '1' $w order by `name` ";
	$NumTotalRec = $pdo->row("select count(*) from $tbl[mng] where `level` != '1' $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

?>
<div class="box_title first">
    <h2 class="title">사원 등록/관리</h2>
</div>
<form id="prdFrm" name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
					<select name="search_type">
						<option <?=checked($search_type, 'name',1)?> value="name">이름</option>
						<option <?=checked($search_type, 'admin_id',1)?> value="admin_id" >아이디</option>
						<option <?=checked($search_type, 'position',1)?> value="position" >직급</option>
						<option <?=checked($search_type, 'phone',1)?> value="phone" >전화번호</option>
						<option <?=checked($search_type, 'cell',1)?> value="cell" >휴대폰</option>
						<option <?=checked($search_type, 'email',1)?> value="email" >이메일</option>
						<option <?=checked($search_type, 'address',1)?> value="address" >주소</option>
					</select>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>

<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 명의 사원이 검색되었습니다.
</div>
<table class="tbl_col">
	<caption class="hidden">사원 등록/관리</caption>
	<colgroup>
		<col style="width:100px">
		<col style="width:150px">
		<col style="width:80px">
		<col span="7">
		<col style="width:120px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">성명</th>
			<th scope="col">아이디</th>
			<th scope="col">등급</th>
			<th scope="col">직급</th>
			<th scope="col">소속</th>
			<th scope="col">생년월일</th>
			<th scope="col">전화번호</th>
			<th scope="col">휴대폰</th>
			<th scope="col">이메일</th>
			<th scope="col">등록일</th>
			<th scope="col">관리</th>
		</tr>
	</thead>
	<tbody>
		<?php
			if($cfg['use_partner_shop'] != 'Y') unset($_mng_levels[4]);
            foreach ($res as $data) {
				$data[team]=$data[team2] ? $data[team2] : $data[team1];
				$teamname=($data[team]) ? $_team[$data[team]][name] : "-";
				$position=($data[position]) ? $data[position] : "-";
		?>
		<tr>
			<td><?=$data[name]?></td>
			<td><?=$data[admin_id]?></td>
			<td><?=$_mng_levels[$data['level']]?></td>
			<td><?=$position?></td>
			<td><?=$teamname?></td>
			<td><?=$data[birth]?></td>
			<td><?=$data[phone]?></td>
			<td><?=$data[cell]?></td>
			<td><?=$data[email]?></td>
			<td><?=date("Y-m-d", $data[reg_date])?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="수정" onclick="location.href='./?body=<?=$body?>&no=<?=$data[no]?>'"></span>
				<span class="box_btn_s"><input type="button" value="삭제" onclick="delSubAdmin(<?=$data[no]?>);"></span>
			</td>
		</tr>
		<?
			}
			unset($data);
		?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>

<?php
	if($no) {
		$data = get_info($tbl['mng'], 'no', $no);
        if (is_array($data) == false) unset($no);
		if ($data['level'] == '1') msg('사원이 아닙니다', 'back');
	}
?>
<form Id="staffFrm" name="frm" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkFrm(this)">
	<input type="hidden" name="body" value="intra@staffs_edt.exe">
	<input type="hidden" name="no" value="<?=$data['no']?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ver" value="2">
	<div class="box_title">
		<h2 class="title"><?=$_mng_group[3]?> <?if(!$data[no]){?>등록<?}else{?>수정<?}?></h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden"><?=$_mng_group[3]?> <?if(!$data[no]){?>등록<?}else{?>수정<?}?></caption>
		<?
			$mng=1;
			include $engine_dir."/_manage/intra/staffs_frm.php";
		?>
	</table>
</form>

<script language="JavaScript">
	function checkFrm(f){
		//if (!checkBlank(f.admin_id,"아이디를 입력해주세요.")) return false;

		if(/[^a-zA-Z-0-9@._-]/i.test(f.admin_id.value)) {
			alert('아이디는 영문,숫자만 사용 가능합니다');
			return false;
		}
		if(!checkBlank(f.name,"성명을 입력해주세요.")) return false;
		if(!checkBlank(f.root_pwd,"최고 관리자 비밀번호를 입력해주세요.")) return false;
		if(!checkBlank(f.cell,"휴대폰번호를 입력해주세요.")) return false;
		if(!checkBlank(f.email,"이메일을 입력해주세요.")) return false;
	}

	function delSubAdmin(no){
		if(!confirm('선택한 <?=$_mng_group[3]?>을 삭제하시겠습니까?')) return;
		f=document.frm;
		f.exec.value='delete';
		f.no.value=no;
		f.submit();
	}
</script>