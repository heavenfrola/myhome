<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스크립트 매니저
	' +----------------------------------------------------------------------------------------------+*/

	if(!isTable('wm_mkt_script')) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$qry = $pdo->query($tbl_schema['mkt_script']);
		$_POST['use_mkt_script'] = 'Y';

		$no_reload_config = true;
		include $engine_dir.'/_manage/config/config.exe.php';
	}

	$_SESSION['listURL'] = getURL();

	$sql = "select * from `wm_mkt_script` order by name asc";

	// 페이징 설정
	include $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$QueryString = '';
	foreach($_GET as $key => $val) {
		if($key == 'paging') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from `wm_mkt_script`");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form name="scriptFrm" method="post" target="hidden<?=$now?>" onsubmit="return scriptDelete(this)">
	<input type="hidden" name="body" value="design@mkt_script.exe">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">스크립트 매니저</h2>
	</div>
	<table class="tbl_col">
		<colgroup>
			<col style="width:60px">
			<col>
			<col style="width:100px">
			<col style="width:200px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.scriptFrm.check_pno, this.checked)"></th>
				<th scope="col">제목</th>
				<th scope="col">사용여부</th>
				<th scope="col">등록일</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					$use_on = ($data['use_yn'] == 'Y') ? 'on' : '';
			?>
			<tr>
				<td><input type="checkbox" id="check_pno" name="check_pno[]" value="<?=$data['no']?>"></td>
				<td class="left"><a href="?body=design@mkt_script_regist&no=<?=$data['no']?>&listURL=<?=$listURL?>"><?=$data['name']?></a></td>
				<td>
					<div class="switch <?=$use_on?>" onclick="toggleUseScript(<?=$data['no']?>, $(this))"></div>
				</td>
				<td><?=date('Y-m-d', $data['reg_date'])?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="등록" onclick="location.href='./?body=design@mkt_script_regist';"></span>
		</div>
		<div class="right_area">
			<span class="box_btn gray"><input type="submit" value="선택삭제" onclick="scriptDelte();"></span>
		</div>
	</div>
</form>

<!-- 하단 탭 메뉴 -->
<div id="controlTab">
	<ul class="tabs">
		<li id="ctab_1" onclick="tabSH(1)" class="selected">일괄상태변경</li>
	</ul>
	<div class="context">
		<!-- 적립금 관리 -->
		<div id="edt_layer_1">
			<div class="box_bottom left">
				<div class="list_btn">
					<p class="title">선택한 스크립트의 상태를 일괄변경합니다.</p>
					<ul>
						<li>
							<span class="box_btn_s blue"><input type="button" value="사용함 상태로 변경" onclick="scriptOn();"></span>
							선택한 스크립트를 사용합니다.
						</li>
						<li>
							<span class="box_btn_s blue"><input type="button" value="사용안함 상태로 변경" onclick="scriptOff();"></span>
							선택한 스크립트를 사용하지 않습니다.
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- //하단 탭 메뉴 -->

<script type="text/javascript">
	var f=document.scriptFrm;
	function scriptOn(){
		if(!checkCB(f.check_pno,"사용 처리할 스크립트를 선택해주세요.")) return false;
		if (!confirm('선택한 스크립트를 사용함 처리 하시겠습니까?')) return;
		f.exec.value='useon';
		f.body.value='design@mkt_script.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
	function scriptOff(){
		if(!checkCB(f.check_pno,"사용안함 처리할 스크립트를 선택해주세요.")) return false;
		if (!confirm('선택한 스크립트를 사용안함 처리 하시겠습니까?')) return;
		f.exec.value='useoff';
		f.body.value='design@mkt_script.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
	function scriptDelete(f) {
		if(!checkCB(f.check_pno,"삭제 처리할 스크립트를 선택해주세요.")) return false;
		if (!confirm('선택한 스크립트를 삭제 처리 하시겠습니까?')) return;
		f.exec.value='delete';
		f.body.value='design@mkt_script.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
	function toggleUseScript(no, o) {
		$.post('?body=design@mkt_script.exe', {'exec':'toggle', 'no':no}, function(r) {
			if(r.changed == 'Y') o.addClass('on');
			else o.removeClass('on');
		});
	}
</script>
