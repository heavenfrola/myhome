<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보 수집기능 변경 기능 안내
	' +----------------------------------------------------------------------------------------------+*/

?>
	<style type='text/css'>
	.ordStat dd {
		font-size: 11px;
		letter-spacing: -1px;
	}

	.ordStat .btn *:hover, .ordStat .btn *:active, .ordStat .btn *:focus {
		color: #fff;
	}
	</style>
	<ul class="ordStat" style="width: 660px; margin: 10px; float: left;">
		<li>
			<dl>
				<dt><span id="step_btn1" class="box_btn blue"><input type="button" value="STEP1" onclick="setNextStep(1)"></span></dt>
				<dd>정책설정</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt><span id="step_btn2" class="box_btn gray"><input type="button" value="STEP2" onclick="setNextStep(2)"></span></dt>
				<dd>회원가입/정보수정</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt><span id="step_btn3" class="box_btn gray"><input type="button" value="STEP3" onclick="setNextStep(3)"></span></dt>
				<dd>아이디/암호찾기</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt><span id="step_btn4" class="box_btn gray"><input type="button" value="STEP4" onclick="setNextStep(4)"></span></dt>
				<dd>정책적용</dd>
			</dl>
		</li>
		<li class="last-child">
			<dl class="lastchild">
				<dt><span id="step_btn5" class="box_btn gray"><input type="button" value="STEP5" onclick="setNextStep(5)"></span></dt>
				<dd>설정완료</dd>
			</dl>
		</li>
	</ul>

<div id='step1' class='register' style='clear: both;'>
	<table cellspacing='0' cellpadding='0'>
		<caption>개인정보 수집기능 변경 기능 안내</caption>
		<tr>
			<td>
				<p class='desc2'><strong>회원가입 페이지</strong></p>
				<ul class='desc1 square'>
					<li>주민등록번호 입력받지 않음(필수 변경사항)</li>
					<li>생년월일 및 성별 입력 선택 추가</li>
					<li>회원가입시 인증방식 선택 : 이메일 인증 / 휴대폰 인증</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<p class='desc2'><strong>정보수정 페이지</strong></p>
				<ul class='desc1 square'>
					<li>이메일 정보수정시 중복 체크</li>
					<li>이메일 정보 수정시 인증단계 추가</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<p class='desc2'><strong>아이디 비밀번호 찾기</strong></p>
				<ul class='desc1 square'>
					<li>이름, 이메일주소(아이디 찾기)기능 변경</li>
					<li>아이디, 이름, 이메일주소(해당 이메일로 임시비밀번호 인증번호 발송) 기능 변경</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<p class='desc2'><strong>최종 검토 필요사항</strong></p>
				<ul class='desc1 square'>
					<li>휴대폰 인증 사용시 윙문자 이용으로 문자 충전 수시 확인 필요</li>
					<li>
						포털 사이트 스팸메일 제외처리를 위하여 화이트 도메인 신청필요
						(화이트 도메인은 한국인터넷 진흥원 <a href='https://www.kisarbl.or.kr/' target='_blank' class='sclink'>https://www.kisarbl.or.kr/</a>에서 신청하실 수 있습니다.
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div class='footer'>
		<span class='btn large blue'><input type='button' value='다음' onclick='setNextStep(2)'></span>
		<div class='desc1' style='margin: 5px 0;'>모든 단계가 끝난 후 최종적으로 '적용'을 누르면 설정이 완료됩니다.</div>
	</div>
</div>

<form id='step2' class='register hidden' method='post' target='hidden<?=$now?>'>
	<?
		include_once $engine_dir.'/_engine/include/design.lib.php';
		$_skin = getSkinCfg();
		$design['skin'] = str_replace('_jedt', '', $design['skin']);

		if(is_dir($root_dir.'/_skin/'.$design['skin'].'_jedt')) {
			$_skin['folder'] = $root_dir.'/_skin/'.$design['skin'].'_jedt';
		}

		$file1 = $_skin['folder'].'/CORE/member_join_frm.wsr';
		$fp = fopen($file1, 'r');
		$file_content1 = fread($fp, filesize($file1));
		fclose($fp);
	?>
	<input type='hidden' name='body' value='design@editor.exe'>
	<input type='hidden' name='exec' value='modify'>
	<input type='hidden' name='exec2' value=''>
	<input type='hidden' name='skin_name' value='<?=$design['skin']?>_jedt'>
	<input type='hidden' name='file_src' value='<?=$_skin['dir']?>/<?=$design['skin']?>_jedt/CORE/member_join_frm.wsr'>
	<input type='hidden' name='viewpage' value='/member/join_step2.php'>

	<table cellspacing='0' cellpadding='0'>
		<caption>
			2. 회원가입/정보수정
			<a href='http://help.wisa.co.kr/manual/index/EX0001' target='_blank' style='margin-left: 20px;'><img src='<?=$engine_url?>/_manage/image/btn/bt_help.gif'></a>
		</caption>
		<tr>
			<td class="desc3">
				<ul class="desc1 square">
					<li>디자인수정을 직접 처리하기 힘드실 경우 유지보수를 신청해 주시기 바랍니다.</li>
					<li>유지보수 처리 및 잘못된 수정으로 인한 복구 시 작업내용에 따라 비용이 청구될수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<textarea name='edt_content' class='txta' style='height: 300px; width: 99%;' onkeydown='editorKeyUp(this);'><?=$file_content1?></textarea>
			</td>
		</tr>
	</table>

	<div class='footer'>
		<span class='btn blue large'><input type='button' value='이전' onclick='setNextStep(1)'></span>
		<span class='btn blue large'><input type='button' value='다음' onclick='setNextStep(3)'></span>
		<span class='btn large'><input type='button' value='미리보기' onclick='skinSave(2, true)'></span>
	</div>

	<div id="code_list1"></div>
</form>

<form id='step3' class='register hidden' method='post' target='hidden<?=$now?>'>
	<?
	$file2 = $_skin['folder'].'/CORE/member_find_step1.wsr';
	$fp = fopen($file2, 'r');
	$file_content2 = fread($fp, filesize($file2));
	fclose($fp);
	?>
	<input type='hidden' name='body' value='design@editor.exe'>
	<input type='hidden' name='exec' value='modify'>
	<input type='hidden' name='exec2' value=''>
	<input type='hidden' name='skin_name' value='<?=$design['skin']?>_jedt'>
	<input type='hidden' name='file_src' value='<?=$_skin['dir']?>/<?=$design['skin']?>_jedt/CORE/member_find_step1.wsr'>
	<input type='hidden' name='viewpage' value='/member/find_step1.php'>

	<table cellspacing='0' cellpadding='0'>
		<caption>
			3. 아이디 비밀번호 찾기
			<a href='http://help.wisa.co.kr/manual/index/EX0002' target='_blank' style='margin-left: 20px;'><img src='<?=$engine_url?>/_manage/image/btn/bt_help.gif'></a>
		</caption>
		<tr>
			<td class="desc3">
				<ul class="desc1 square">
					<li>디자인수정을 직접 처리하기 힘드실 경우 유지보수를 신청해 주시기 바랍니다.</li>
					<li>유지보수 처리 및 잘못된 수정으로 인한 복구 시 작업내용에 따라 비용이 청구될수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<textarea name='edt_content' class='txta' style='height: 300px; width: 99%;' onkeydown='editorKeyUp(this);'><?=$file_content2?></textarea>

				<div class='footer'>
					<span class='btn blue large'><input type='button' value='이전' onclick='setNextStep(2)'></span>
					<span class='btn blue large'><input type='button' value='다음' onclick='setNextStep(4)'></span>
					<span class='btn large'><input type='button' value='미리보기' onclick='skinSave(3, true)'></span>
				</div>

				<div id="code_list2"></div>
			</td>
		</tr>
	</table>
</form>

<form id='confirmFrm' method='post' action='?' target='hidden<?=$now?>' onsubmit='use_checkbox(this);'>
	<input type='hidden' name='body' value='config@setPrivateinfo.exe'>
	<input type='hidden' name='exec' value='complete'>
	<input type="hidden" name="config_code" value="member_jumin">

	<div id='step4' class='register hidden'>
		<?include_once $engine_dir.'/_manage/config/member_join.inc.php';?>
		<div class='footer'>
			<span class='btn blue large'><input type='button' value='이전' onclick='setNextStep(3)'></span>
			<span class='btn blue large'><input type='button' value='다음' onclick='setNextStep(5)'></span>
		</div>
	</div>

	<div id='step5' class='register hidden'>
		<table cellspacing='0' cellpadding='0'>
			<caption>쇼핑몰의 개인정보수집기능이 아래와 같이 설정 되었습니다.
			<tr>
				<th>주민번호 입력받기</th>
				<td id='confirm_section1'></td>
			</tr>
			<tr>
				<th>주민번호 대체방법</th>
				<td id='confirm_section2'></td>
			</tr>
			<tr>
				<th>가입시 개인확인 방법</th>
				<td id='confirm_section3'></td>
			</tr>
		</table>

		<div class='footer'>
			<span class='btn blue large'><input type='button' value='이전' onclick='setNextStep(4)'></span>
			<span class='btn blue large'><input type='submit' value='적용''></span>
		</div>
	</div>
</form>

<form name="popFrm" action="./pop.php" method="get">
	<input type="hidden" name="body" value="design@editor.frm">
	<input type="hidden" name="design_edit_key">
	<input type="hidden" name="design_edit_code">
</form>

<script type='text/javascript'>
var skin = "<?=$design['skin']?>";
var this_step = 1;
var makeskin = false;
function setNextStep(step) {
	var f = document.getElementById('confirmFrm');

	if(step > 2 && makeskin == false) {
		window.alert(makeskin+'STEP2 회원가입/정보수정 스킨편집을 먼저 진행해 주세요.');
		return false;
	}

	if(step == 5 && $(':checked[name=join_jumin_use]', f).val() != 'N') {
		window.alert("주민등록번호 '받음'으로 설정하실수 없습니다.");
		if(this_step != 4) setNextStep(4);
		return false;
	}

	if(step == 5 && makeskin == 'pass') {
		if(f.member_confirm_email.checked == true || f.member_confirm_sms.checked == true) {
			if(confirm('가입 시 개인확인 방법을 사용하실 수 없습니다.\n사용하시려면 STEP2로 이동하신 후 디자인 수정을 진행해 주세요.\n다시 진행하시겠습니까?')) {
				setNextStep(1);
			}
			return false;
		}
	}

	$('.register').addClass('hidden');
	$('#step'+step).removeClass('hidden');

	$('.ordStat').find('.blue').removeClass('blue').addClass('gray');
	$('#step_btn'+step).removeClass('gray').addClass('blue');

	if(this_step < step) {
		switch(step) {
			case 2 :
				if(makeskin != true) {
					if(confirm('가입시 개인확인방법(선택사항)을 사용하지 않으시면 디자인페이지 수정을 하실필요가 없습니다.\n디자인 수정을 통과하시겠습니까?\n\n확인=스킨 수정안함(개인확인 사용안함)\n취소=스킨 수정함(개인확인 사용가능)')) {
						makeskin = 'pass';
						setNextStep(4);
					} else {
						$.post('?body=config@setPrivateinfo.exe', {"exec":"start"}, function(result) {
							if(result) window.alert(result);
							getCodeList('code_list1', 'member_join_frm.php');
							makeskin = true;
						});
					}
				}
			break;
			case 3 :
				skinSave(2);
				getCodeList('code_list2', 'member_find_step1.php');
			break;
			case 4 :
				skinSave(3);
			break;
			case 5 :
				var section1 = $(':checked', f.join_jumin_use).val() == 'Y' ? '받음' : '받지않음';
				section1 = '<ul class="desc1 square">'+section1+'</ul>';

				var section2 = '';
				if($(f.join_jumin_use).val() != 'Y') {
					if($(f.join_birth_use).attr('checked') == true) section2 += '<li>생년월일 엽력받기</li>';
					if($(f.join_sex_use).attr('checked') == true) section2 += '<li>성별 엽력받기</li>';
					if($(f.rebirth).attr('checked') == true) section2 += '<li><br>생년월일 및 성별 자동 입력</li>';
				}
				if(section2 == '') section2 = '<li>사용하지 않음</li>';
				section2 = '<ul class="desc1 square">'+section2+'</li>';

				var section3 = '';
				if($(f.member_confirm_email).attr('checked') == true) section3 += '<li>이메일 인증 사용</li>';
				if($(f.member_confirm_sms).attr('checked') == true) section3 += '<li>유대폰 인증 사용</li>';
				if(section3 == '') section3 += '<li>사용하지 않음</li>';
				section3 = '<ul class="desc1 square">'+section3+'</li>';

				$('#confirm_section1').html(section1);
				$('#confirm_section2').html(section2);
				$('#confirm_section3').html(section3);

			break;
		}
	}

	this_step = step;
}

function getCodeList(target, page) {
	$.get('?body=design@editor_code.exe', {"code_key":"edit_pg", "_edit_pg":page}, function(result){
		$('#'+target).html(result);
	});
}

function editCode(key, code){
	var f = document.popFrm;
	f.design_edit_key.value=key;
	f.design_edit_code.value=code;

	var viewId = 'codePop';
	if(getCookie('def_dmode') != '0') viewId+=code;

	var a = window.open('about:blank',viewId,'top=10,left=10,width=950,status=no,toolbars=no,scrollbars=yes,height=700');
	if(a) a.focus();
	f.target=viewId;
	f.submit();
}

var skinPreview = null;
function skinSave(step, prv) {
	var f = document.getElementById('step'+step);
	f.exec2.value = prv == true ? 'preview' : 'exit';

	if(prv == true) {
		skinPreview = window.open('', 'skinPreview');
		if(skinPreview) skinPreview.focus();
	}

	f.submit();
}
</script>