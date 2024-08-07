<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보엑셀 설정
	' +----------------------------------------------------------------------------------------------+*/

	$mbr_excel_fd = array(
		'idx' => '번호', 'name' => '이름', 'member_id' => '아이디', 'sex' => '성별', 'age' => '나이', 'birth' => '생년월일',
		'zip' => '우편번호', 'addr1' => '주소', 'addr2' => '상세주소', 'email' => '이메일', 'phone' => '전화', 'cell' => '휴대폰',
		'milage' => '적립금', 'emoney' => '예치금', 'mailing' => '메일수신', 'sms' => 'SMS수신', 'level' => '회원등급',
		'total_con' => '접속횟수', 'total_ord' => '주문횟수','total_prc' => '총구매액', 'last_ord' => '최근구매일',
		'reg_date_a' => '가입일', 'reg_date_b' => '가입일시', 'last_con' => '최근접속일',
		'recom_member' => '추천인', 'nick' => '닉네임'
	);

	if(@file_exists($root_dir.'/_config/member.php')){
		include_once $root_dir."/_config/member.php";
		if(@is_array($_mbr_add_info)) {
			foreach($_mbr_add_info as $key=>$val){
				$mbr_excel_fd["add_info".$key] = $_mbr_add_info[$key]['name'];
			}
		}
	}

	// 구버전 데이터 마이그레이션
	if(!isTable($tbl['excel_preset'])) {
		include $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['excel_preset']);
	}

	if($pdo->row("select count(*) from $tbl[excel_preset] where type='member'") == 0) {
		$mbr_excel_fd_default = 'idx,name,member_id,jumin,sex,age,zip,addr1,email,phone,cell,total_con,total_ord,milage,reg_date_a';
		$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('member', '기본', '$mbr_excel_fd_default', '1', '$now')");

		if(file_exists($root_dir.'/'.$dir['upload'].'/mng_excel_set/member_set.php')) {
			$file = file($root_dir.'/'.$dir['upload'].'/mng_excel_set/member_set.php');
			foreach($file as $key => $val) {
				$sort = $key+2;
				list($name, $set_data) = explode('@', addslashes(preg_replace('/^@|@#/' ,'', $val)));
				$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('member', '$name', '$set_data', '$sort', '$now')");
			}
		}
	}

	$xls_set = numberOnly($_GET['xls_set']);
	if($_REQUEST['xls_set_temp']) $xls_set = $_REQUEST['xls_set_temp'];

	$res = $pdo->iterator("select * from $tbl[excel_preset] where type='member' order by sort asc");
    foreach ($res as $set) {
		$mbr_excel_set[$set['no']] = $set['data'];
		$mbr_excel_set_name[$set['no']] = stripslashes($set['name']);
		if(!$xls_set) $xls_set = $set['no'];
	}
	$_mbr_excel_fd_selected = explode(',', $mbr_excel_set[$xls_set]);
	if(is_null($mbr_excel_set[$xls_set])) $xls_set = 0;

	if(strchr($body, 'member@member_excel.exe') || strchr($body, 'member@member_list')) {
		return;
	}

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return saveSet(this);">
	<input type="hidden" name="body" value="config@excel_config.exe" />
	<input type="hidden" name="type" value="member" />
	<input type="hidden" name="exec" value="saveSet" />
	<input type="hidden" name="xls_set" value="<?=$xls_set?>" />
	<input type="hidden" name="set_data" value="" />

	<div class="box_title first">
		<h2 class="title">회원정보엑셀 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>원하시는 EXCEL 파일의 내용을 하단 오른쪽필드로 순서를 지정해주시기 바랍니다.</li>
			<li>저장된 형식은 주문리스트의 <u>"현재 검색결과를 엑셀 파일로 저장"</u> 버튼을 클릭하셔서 다운받으실 수 있습니다.</li>
			<li>불러오기 기능을 이용하신 경우 해당세트로 엑셀출력을 하시려면 우선 저장버튼을 클릭하여 설정을 저장하셔야 합니다.</li>
			<li>필드를 지정하시더라도 현재 사이트내에서 사용하지 않는 필드는 출력이 되지 않습니다.</li>
			<li><strong>최근구매일</strong> 필드 사용시 대용량의 엑셀을 출력할 경우 다량의 부하가 발생하여 서버가 불안정해 질수 있으므로 사용시 주의 해 주십시오.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">회원정보엑셀 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">세트 선택</th>
			<td>
				<select name="tmp_set">
					<option value="">======선택======</option>
					<?foreach($mbr_excel_set as $key=>$val){?>
					<option value="<?=$key?>" <?=checked($key, $xls_set, true)?>><?=$mbr_excel_set_name[$key]?></option>
					<?}?>
				</select>
				<span class="box_btn_s"><input type="button" value="불러오기" onClick="loadSet(this.form);"></span>
				<span class="box_btn_s"><input type="button" value="새양식추가" onClick="makeSet(this.form);"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">세트명</th>
			<td>
				<input type="text" name="set_name" value="<?=$mbr_excel_set_name[$xls_set]?>" class="input">
				<?if($xls_set > 0){?>
				<span class="box_btn_s gray"><input type="button" value="삭제하기" onClick="delSet(<?=$xls_set?>);"></span>
				<?}?>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<div class="add_fld">
			<div class="fld_list">
				<h3>추가할 필드 선택</h3>
				<select id="sel1" class="select_n" name="fd_list" size="25" multiple>
					<?foreach($mbr_excel_fd as $key=>$val) {?>
						<option value='<?=$key?>'><?=$val?></option>
					<?}?>
				</select>
			</div>
			<div class="add">
				<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
			</div>
			<div class="add_list">
				<h3>파일내용</h3>
				<select id="sel2" class="select_n" name="fd_list_selected" size="25" multiple>
					<?foreach($_mbr_excel_fd_selected as $key=>$val){?>
					<option value='<?=$val?>'><?=$mbr_excel_fd[$val]?></option>
					<?}?>
				</select>
				<span class="box_btn_s icon delete"><input type="button" value="삭제" onclick="select2.remove();"></span>
				<span class="box_btn_s icon up"><input type="button" value="위로" onclick="select2.move(-1);"></span>
				<span class="box_btn_s icon down"><input type="button" value="아래로" onclick="select2.move(1);"></span>
			</div>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript" src='<?=$engine_url?>/_engine/common/R2Select.js?ver=20200821'></script>
<script type="text/javascript">
	var select1 = new R2Select('sel1');
	var select2 = new R2Select('sel2');

	function loadSet(f) {
		location.href = './?body=<?=$body?>&xls_set='+f.tmp_set.value;
	}

	function delSet(xls_set) {
		if(confirm('선택한 세트를 삭제하시겠습니까?')) {
			$.post('./index.php?body=config@excel_config.exe', {'exec':'remove', 'no':xls_set}, function(r) {
				location.reload();
			});
		}
	}

	function makeSet(f) {
		$.post('./index.php?body=config@excel_config.exe', {'exec':'make', 'type':f.type.value}, function(r) {
			location.href = './index.php?body=member@member_excel_config&xls_set='+r;
		});
	}

	function saveSet(f) {
		var tmp = '';
		$(f.fd_list_selected).find('option').each(function() {
			if(tmp) tmp += ',';
			tmp += this.value;
		});
		f.set_data.value = tmp;
	}
</script>