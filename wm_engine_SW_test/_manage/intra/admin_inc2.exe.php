<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$search_str = mb_convert_encoding(addslashes(trim($_GET['search_str'])), _BASE_CHARSET_, array('utf8', 'euckr'));
	$search_key = addslashes($_GET['search_key']);
	if($search_str) $w .= " and `$search_key` like '%$search_str%'";
	$sql = "select no, admin_id, name, cell from $tbl[mng] where 1 $w  order by reg_date desc";
	$sql2 = $pdo->row("select no from $tbl[mng] where 1 $w  order by reg_date desc");

	include $engine_dir."/_engine/include/paging.php";

	foreach($_GET as $key => $val) {
		if($key != 'page' && $val) $QueryString .= "&$key=".urlencode($val);
	}

	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);

	if($page <= 1) $page = 1;
	$NumTotalRec = $pdo->row("select count(*) from $tbl[mng] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, 10, 10);
	$PagingInstance -> addQueryString($QueryString);
	$PagingResult = $PagingInstance -> result($pg_dsn);
	$sql .= $PagingResult[LimitQuery];

	$pg_res = $PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page - 1));

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="msearch.open(\'$1\')"', $pg_res);

	$aa = '<img src="<?=$engine_url?>/_manage/image/icon/clock_icon.png" alt="">';

	$ress = $pdo->iterator("select `cell` from `wm_mng` where `level` = '2'");

	include_once $engine_dir.'/_manage/manage.lib.php';
?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">관리자검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return msearch.fsubmit(this);">
				<input type="hidden" name="body" value="<?=addslashes($_GET['body'])?>">
				<select name="search_key">
					<option value="name" <?=checked($search_key, 'name', 1)?>>성명</option>
					<option value="admin_id" <?=checked($search_key, 'admin_id', 1)?>>아이디</option>
					<option value="cell" <?=checked($search_key, 'cell', 1)?>>휴대폰</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=inputText($search_str)?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</form>
		</div>
	<form method="post" name="popupContent" id="popupContent" action="<?=$_SERVER['PHP_SELF']?>">
		<input type="hidden" name="body" value="intra@joinSmsFrm2.exe">
		<input type="hidden" name="exec" value="">
		<input type="hidden" name="no" value="">

		<table class="tbl_col">
			<caption class="hidden">관리자검색</caption>
			<colgroup>
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
				<col>
			</colgroup>
			<thead>
				<tr>
					<th scope="col">선택</th>
					<th scope="col">성명</th>
					<th scope="col">아이디</th>
					<th scope="col">휴대폰</th>
					<th scope="col">정보수정</th>
				</tr>
			</thead>
			<tbody>
				<?if($sql2) {?>
					<?php
                        foreach ($res as $data) {
					?>
					<tr>
						<td><?if($data['cell']) {?><input type="checkbox" name="check_no[]" id="check_no" value="<?=$data['no']?>"><?} else {?>-<?}?></td>
						<td><?=$data['name']?></td>
						<td><?=$data['admin_id']?></td>
						<td><?if($data['cell']) {?><?=$data['cell']?><?} else {?>-<?}?></td>
						<td><span class="box_btn_s"><input type="button" value="정보수정" onclick="location.href='./?body=intra@staffs_edt&no=<?=$data[no]?>'"></span></td>
					</tr>
					<?}?>
				<?} else {?>
					<tr>
						<td colspan="5">해당하는 관리자가 없습니다.</td>
					</tr>
				<?}?>
			</tbody>
		</table>
		<div class="box_bottom">
			<?=$pg_res?>
		</div>
		<div class="box_title">최고관리자 휴대폰 인증</div>
		<div class="popupContent">
			<table class="tbl_row">
				<caption class="hidden">수신번호 설정</caption>
				<colgroup>
					<col style="width:17%">
				</colgroup>
				<tr>
					<th scope="row">휴대폰</th>
						<td>
						<select name="cell1">
							<option value="010">010</option>
							<option value="011">011</option>
							<option value="016">016</option>
							<option value="017">017</option>
							<option value="018">018</option>
							<option value="019">019</option>
						</select>
						<input type="text" name="cell2" class="input" size="2" maxlength="4">
						<input type="text" name="cell3" class="input" size="2" maxlength="4">
						<span class="box_btn_s gray"><input type="button" id = "confirmcall" value="인증번호 요청" onclick="getreg();"></span>
						</td>
				</tr>
				<tr>
					<th scope="row">인증번호</th>
					<td>
						<input type="text" name="reg_code" value="" class="input" size="19" maxlength="6"> <span id="counter"></span>
					</td>
				</tr>
			</table>
		</div>
	</form>

	<div class="pop_bottom">
		<span class="box_btn blue"><input type="button" value="<?=__lang_common_btn_confirm__?>" onclick="receivereg()"></span>
		<span class="box_btn"><input type="button" value="<?=__lang_common_btn_close__?>" onclick="popclose()"></span>
	</div>
</div>

<script type="text/javascript">
	var basetime = 0;
	var cnt = 300;
	var interval = null;
	$("#counter").html("");
	function init() {
		if(interval) {
			clearInterval(interval);
		}
		basetime = Math.floor(new Date().getTime()/1000);
		interval = setInterval(function() {
			var now = Math.floor(new Date().getTime()/1000);
			var lefttime = 300-(now-basetime);
			if(lefttime < 0) {
				lefttime = 0;
			}
			cnt--;
			$("#counter").html("<img src='<?=$engine_url?>/_manage/image/icon/clock_icon.png' style= 'vertical-align:top; margin-top:8px;'>  잔여시간 <strong>"+padZero(parseInt(lefttime/60))+"</strong>분 <strong>"+padZero(parseInt(lefttime%60))+"</strong>초");
		}, 1000);
	}

	function padZero(n) {
		return n>9?n:"0"+n;
	}

	function getreg() {
		if($(':checked[name="check_no[]"]').length < 1) {
			window.alert('선택하신 관리자가 없습니다.\n관리자 선택 후 최고 관리자 휴대폰 인증 절차를 통해 등록이 가능합니다.');
			return false;
		}
		f=document.popupContent;
		if(!checkBlank(f.cell2,'최고 관리자 휴대폰 인증 후 등록이 가능합니다.')) return false;
		var phone1 = f.cell1.value+f. cell2.value+f.cell3.value;
		var phone2 = f.cell1.value+'-'+f. cell2.value+'-'+f.cell3.value;
		var phone4;
		<?php foreach ($ress as $data) {?>
			var phone3 = "<?=$data[cell]?>";
			if(phone1 == phone3 || phone2 == phone3) {
				var phone4 = "OK";
			}
		<?}?>
			if(!phone4) {
				window.alert("최고관리자로 등록된 휴대폰 번호가 아닙니다.");
				return false;
			}

			init();

		document.getElementById("confirmcall").value = '인증번호 재요청';
		f.exec.value='getreg';
		f.target=hid_frame;
		f.submit();
	}

	function receivereg() {
		if($(':checked[name="check_no[]"]').length < 1) {
			window.alert('선택하신 관리자가 없습니다.\n관리자 선택 후 최고 관리자 휴대폰 인증 절차를 통해 등록이 가능합니다.');
			return false;
		}
		f=document.popupContent;
		if(!checkBlank(f.cell2,'최고 관리자 휴대폰 인증 후 등록이 가능합니다.')) return false;
		if(!checkBlank(f.reg_code,'인증번호가 입력되지 않았습니다.')) return false;
		f.exec.value='receivereg';
		f.target=hid_frame;
		f.submit();
	}

	function popclose() {
		$("#counter").html("");
		msearch.close();
		clearInterval(interval);
	}
</script>