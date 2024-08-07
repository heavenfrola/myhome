<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  가입/탈퇴/로그인 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";

	if(!$cfg['member_return_page']) $cfg['member_return_page']=2;
	if(!$cfg['jumin_encode']) $cfg['jumin_encode']='Y'; // 2008-12-05 : 주민번호 암호화 - Han
	if(!$cfg['change_pwd']) $cfg['change_pwd'] = 6;
	if(!$cfg['change_pwd_re']) $cfg['change_pwd_re'] = 7;
	if(!$cfg['change_pwd_m']) $cfg['change_pwd_m'] = 'm';
	if(!$cfg['change_pwd_m_re']) $cfg['change_pwd_m_re'] = 'd';
	if(!$cfg['use_pwd_change']) $cfg['use_pwd_change'] = 'N';
	if(!$cfg['join_14_limit']) $cfg['join_14_limit'] = 'A';
	if(!$cfg['del_send_type1']) $cfg['del_send_type1'] = 'Y';
    $scfg->def('session_engine', 'MySQL');
    $scfg->def('redis_host', '');

	//아이디/비밀번호찾기 파일 체크
	$find_search_disabled = "";
	if(!file_exists($root_dir.'/_skin/'.$design['skin'].'/CORE/member_search_id_pwd.wsr')) {
		$find_search_disabled = "disabled";
	}

	$file_url = getFileDir("_data/member_addinfo");
	$cfg['14_joinform'] = "14_joinform.docx";

    $disabled_Redis = (class_exists('\Redis') == false) ? 'disabled' : '';

	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));
	if ($asvcs[0]->type[0] == 10) {
		define('__STAND_ALONE__', true);
	}

?>
<form id="joinConfig" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="use_checkbox(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="member_jumin">
	<div class="box_title first">
		<h2 class="title">가입 설정</h2>
	</div>
	<?php include_once $engine_dir.'/_manage/member/member_join.inc.php'; ?>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="joinlimitConfig" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return method_check(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="14_joinform">
	<div class="box_title">
		<h2 class="title">가입연령제한 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">가입연령제한 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>가입연령제한</th>
			<td>
				<label><input type="radio" name="join_14_limit" value='A' <?=checked($cfg['join_14_limit'], 'A')?> class="joinLimitConfigEvent" /> 제한 안함</label><br><br>
				만 14세 미만의 경우
				<label><input type="radio" name="join_14_limit" value='B' <?=checked($cfg['join_14_limit'], 'B')?> class="joinLimitConfigEvent" /> 승인 후 가입</label>
				<label><input type="radio" name="join_14_limit" value='C' <?=checked($cfg['join_14_limit'], 'C')?> class="joinLimitConfigEvent" /> 가입 불가</label>
			</td>
		</tr>
		<tr>
			<th scope="row">인증수단</th>
			<td>
				<label><input type="radio" class="joinLimitConfigEvent" name="join_14_limit_method" value='1' <?=checked($cfg['join_14_limit_method'], '1')?> /> 생년월일</label>
				<label><input type="radio" class="joinLimitConfigEvent" name="join_14_limit_method" value='2' <?=checked($cfg['join_14_limit_method'], '2')?> /> 자가 체크</label>
			</td>
		</tr>
		<tr>
			<th scope="row">법정대리인 동의서</th>
			<td>
				<span class="box_btn_s gray"><a href="<?=$engine_url?>/_manage/member/14_joinform.docx">법정대리인 동의서 샘플</a></span>
				<?php if ($cfg['14_join_form_file']) { ?><span class="box_btn_s"><a href="<?=$file_url?>/_data/member_addinfo/<?=$cfg['14_join_form_file']?>" target="_blank">기존파일</a></span><?php } ?>
				<input type="file" name="14_join_form_file" class="upfile">
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<div class="list_info">
			<p class="title">[법규안내]</p>
			<p>정보통신망 이용촉진 및 정보보호 등에 관한 법률 제31조 제1항에 따라, 만 14세 미만의 아동은 법정대리인의 동의 확인 후 회원가입이 가능합니다.</p>
		</div>
		<div class="list_info tp">
			<p class="title">[설정안내]</p>
			<ul class="list_info">
				<li>인증수단으로 생년월일을 사용하기 위해서는 가입 설정 내 '생년월일(필수설정)'을 사용해야 합니다.</li>
				<li>자가 체크 사용 시 ‘승인 후 가입‘ 설정은 불가능합니다.</li>
				<li>첨부된 법정대리인 동의서는 디자인코드 삽입 시 회원가입 완료페이지에서 확인이 가능하며, 법정대리인 동의서가 접수 후 확인되었다면 CRM 종합정보 내 수동인증하기 절차가 필요합니다.</li>
				<li>법정대리인 동의서는 일반게시판 게시물을 통해 언제든지 고객들이 확인할 수 있도록 안내하는 것이 좋습니다.</li>
			</ul>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id='join19limitConfig'  method='post' action="<?=$_SERVER['PHP_SELF']?>" target='hidden<?=$now?>' onsubmit='use_checkbox(this);'>
	<input type='hidden' name='body' value='config@config.exe'>
	<input type='hidden' name='config_code' value='member_search'>
	<div class='box_title'>
		<h2 class="title">미성년자 이용 불가 설정</h2>
	</div>
	<table class='tbl_row'>
		<caption class='hidden'>미성년자 이용 불가 설정</caption>
		<colgroup>
			<col style='width:15%'>
			<col>
		</colgroup>
		<tr>
			<th>사용 여부</th>
			<td>
				<label><input type='radio' name='limit_19' value='Y' <?=checked($cfg['limit_19'], 'Y')?>  /> 사용함</label>
				<label><input type='radio' name='limit_19' value=''  <?=checked($cfg['limit_19'],  '')?> <?=$find_search_disabled?>/> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class='box_bottom'>
		<span class='box_btn blue'><input type='submit' value='확인'></span>
	</div>
</form>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="pwd_change">
	<div class="box_title">
		<h2 class="title">비밀번호 변경 안내 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">비밀번호 변경 안내 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>사용 여부</th>
			<td>
				<label><input type="radio" name="use_pwd_change" value='Y' <?=checked($cfg['use_pwd_change'], 'Y')?>  /> 사용함</label>
				<label><input type="radio" name="use_pwd_change" value='N'  <?=checked($cfg['use_pwd_change'],  'N')?> /> 사용안함</label>
				<div class="list_info tp">
					<p>비밀번호 변경 주기를 설정하여 6개월에 1회 이상 변경하도록 안내가 필요합니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">변경 안내 주기 설정</th>
			<td>
				비밀번호 마지막 변경일부터
				<select name="change_pwd" id="change_pwd">
					<option value="<?=$cfg['change_pwd']?>" <?=checked($cfg['change_pwd'], $cfg['change_pwd'], 1)?>></option>
				</select>
				<select name="change_pwd_m" onchange="changeDay(this.value)">
					<option value="d" <?=checked('d', $cfg['change_pwd_m'], 1)?>>일</option>
					<option value="m" <?=checked('m', $cfg['change_pwd_m'], 1)?>>개월</option>
				</select> 마다 로그인 시 안내
				<div class="list_info tp">
					<p>회원의 개인정보보호를 위하여 비밀번호 변경 안내를 설정합니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">변경 재안내 주기 설정</th>
			<td>
				<select name="change_pwd_re" id="change_pwd_re">
+					<option value="<?=$cfg['change_pwd_re']?>" <?=checked($cfg['change_pwd_re'], $cfg['change_pwd_re'], 1)?>></option>
				</select>
				<select name="change_pwd_m_re" onchange="changeDayre(this.value)">
					<option value="d" <?=checked('d', $cfg['change_pwd_m_re'], 1)?>>일</option>
					<option value="m" <?=checked('m', $cfg['change_pwd_m_re'], 1)?>>개월</option>
				</select> 마다 로그인 시 재안내
				<div class="list_info tp">
					<p>다음에 변경하기 선택 시 재안내 주기를 설정합니다.</p>
				</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="use_checkbox(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="member_search">
	<div class="box_title">
		<h2 class="title">아이디/비밀번호 찾기 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">아이디/비밀번호 찾기 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>아이디/비밀번호 찾기 설정</th>
			<td>
				<label><input type="radio" name="find_search" value='' <?=checked($cfg['find_search'], '')?>  /> 가입 설정에 선택한 인증 수단으로 아이디/비밀번호 찾기</label><br>
				<label><input type="radio" name="find_search" value='Y'  <?=checked($cfg['find_search'],  'Y')?> <?=$find_search_disabled?>/> 가입 설정에 선택한 인증 수단과 관계없이 아이디/비밀번호 찾기</label>
				<?php if ($find_search_disabled) { ?>
				<ul class="list_info pt">
					<li>
						설정 변경이 불가능한 경우 사용 중인 스킨 내 아이디 비밀번호 찾기 인증 페이지 작업유무를 확인해주세요.<br>
						[PC 쇼핑몰] <a href="?body=design@editor&type=&edit_pg=5%2F4" target="_blank">바로가기</a>
						[모바일 쇼핑몰] <a href="?body=wmb@editor&type=mobile&edit_pg=5%2F4" target="_blank">바로가기</a>
					</li>
				</ul>
				<?php } ?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return withdraw_type(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="deleteMember">
	<div class="box_title">
		<h2 class="title">휴면회원 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">휴면회원 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>사용 여부</th>
			<td>
				<label><input type="radio" name="use_dormancy" value='Y' <?=checked($cfg['use_dormancy'], 'Y')?>  /> 사용함</label>
				<label><input type="radio" name="use_dormancy" value=''  <?=checked($cfg['use_dormancy'],  '')?> /> 사용안함</label>
				<ul class="list_info tp">
					<li>개인정보유효기간제 시행에 따라 쇼핑몰에 로그인한지 1년 이상 경과된 고객들은 자동으로 휴면회원 처리되며, 개인정보가 분리 보관 됩니다.</li>
					<li>휴면회원 전환은 매일 새벽 4시에 진행됩니다.</li>
					<li>사이트의 도메인이 변경되면 반드시 설정을 새로 저장해야 합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">사전안내 수단</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="del_send_type1" value="Y" <?=checked($cfg['del_send_type1'],'Y')?>> 이메일</label>
				<label class="p_cursor"><input type="checkbox" name="del_send_type2" value="Y" <?=checked($cfg['del_send_type2'],'Y')?>> SMS</label>
				<div class="list_info tp">
					<p>
						쇼핑몰 운영자는 최대 30일 전에 대상 회원에게 안내를 시행해야 합니다.<br>
						{자동메일 설정} <a href="?body=member@email_config" target="_blank">바로가기</a>
						{고객 문자알림 설정} <a href="?body=member@sms_config" target="_blank">바로가기</a>
					</p>
				</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="use_checkbox(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="member_withdraw">
	<div class="box_title">
		<h2 class="title">탈퇴요청회원 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">탈퇴요청회원 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">탈퇴요청회원<br>자동삭제 기한</th>
			<td>
				<select name="withdrawal">
					<option value="">사용 안함</option>
                    <?php foreach (array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 30) as $i) { ?>
					<option value="<?=$i?>" <?=checked($i, $cfg['withdrawal'], 1)?>><?=$i?>일 후 삭제</option>
					<?php } ?>
                    <option value="immediately" <?=checked('immediately', $cfg['withdrawal'], true)?>>즉시 삭제</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">탈퇴요청회원<br>자동삭제 자료</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="del_option1" value="1" <?=checked($cfg['del_option1'], '1')?>> 상품평</label>
				<label class="p_cursor"><input type="checkbox" name="del_option2" value="1" <?=checked($cfg['del_option2'], '1')?>> 상품 질문</label>
				<label class="p_cursor"><input type="checkbox" name="del_option3" value="1" <?=checked($cfg['del_option3'], '1')?>> 주문 내역</label>
				<label class="p_cursor"><input type="checkbox" name="del_option4" value="1" <?=checked($cfg['del_option4'],  '1').checked($cfg['del_option4'] , '')?>> 1:1 상담 내역</label>
				<label class="p_cursor"><input type="checkbox" checked disabled> 위시리스트, 적립금/예치금 내역(필수)</label>
				<ul class="list_info tp">
					<li>자동삭제시 함께 삭제할 자료를 선택해주세요.</li>
					<li>주문 내역 삭제시 매출 분석등이 변동됩니다. </li>
					<li>삭제한 회원정보 및 관련자료는 복구되지 않습니다. </li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		예 : 2월 22일 회원이 탈퇴 요청을 하였고, 탈퇴요청회원 자동삭제 기한일이 3일일 경우, 2월 26일에 자동삭제됩니다.
		<table class="tbl_inner line">
			<thead>
				<tr>
					<th>2/22</th>
					<th>2/23</th>
					<th>2/24</th>
					<th>2/25</th>
					<th>2/26</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>탈퇴요청일</td>
					<td>1일</td>
					<td>2일</td>
					<td>3일</td>
					<td>탈퇴요청회원 자동삭제일</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php
	$login_session = array(20, 30, 60, 120, 180, 240, 300);
	$session_default = ini_get('session.gc_maxlifetime') / 60;

	if($cfg['session_lifetime'] == null) {
		$cfg['session_lifetime'] = $session_default;
		$session_caution = true;
		if(!in_array($session_default, $login_session)) {
			$login_session[] = $session_default;
			sort($login_session);
		}
	}
?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="session">
	<div class="box_title">
		<h2 class="title">로그인 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">로그인 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">로그인 후 리턴 페이지</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_return_page" value="1" <?=checked($cfg['member_return_page'],'1')?>> 로그인 전 페이지</label><br>
				<label class="p_cursor"><input type="radio" name="member_return_page" value="2" <?=checked($cfg['member_return_page'],'2')?>> 메인페이지</label><br>
				<label class="p_cursor"><input type="radio" name="member_return_page" value="3" <?=checked($cfg['member_return_page'],'3')?>> 지정페이지 : <input type="text" name="member_return_page_custom" value="<?=$cfg['member_return_page_custom']?>" class="input" size="30"></label>
			</td>
		</tr>
		<tr>
			<th scope="row">로그인 유지시간</th>
			<td>
				<?php if ($session_caution) { ?>
				<ul class="list_info pt">
					<li>현재 로그인 유지시간은 서버 기본 로그인 유지시간인 <span class="p_color"><?=$session_default?>분</span> 입니다.</li>
					<li class="p_color">로그인 유지시간이 서버 기본으로 설정되어있으신 고객님은 <u>첫 한번에 한해,</u> 설정시 <u>현재 설정을 변경중인 관리자를 포함한 모든 관리자 로그인과 고객들의 로그인이 해제되며, 장바구니가 모두 비워집니다.</u></li>
					<li class="p_color">가급적 사용자 불편이 없는시간을 선택해 신중하게 변경 해 주시기 바랍니다.</li>
					<li class="p_color">처음 한 번 변경하신 이후부터는 운영중에 자유롭게 로그인 유지시간을 변경 하셔도 됩니다.</li>
				</ul>
				<?php } ?>
				<?=selectArray($login_session,"session_lifetime",1,"",$cfg['session_lifetime'])?> 분
				<ul class="list_info pt">
					<li>로그인 유지 시간은 페이지가 로딩된 후 아무 행동도 하지 않았을 경우 유지되는 시간입니다.</li>
					<li>너무 긴 시간 (30분 권장) 로그인을 유지할 경우 보안에 문제가 생길 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<?php if ($admin['admin_id'] == 'wisa' || defined('__STAND_ALONE__') == true) { ?>
        <tr>
            <th scope="row">로그인 엔진</th>
            <td>
                <select name="session_engine">
                    <option value="MySQL" <?=checked($cfg['session_engine'], 'MySQL', true)?>>MySQL</option>
                    <option value="Redis" <?=checked($cfg['session_engine'], 'Redis', true)?> <?=$disabled_Redis?>>Redis</option>
                </select>
                <input
                    type="text"
                    name="redis_host"
                    class="input session_info session_info_Redis"
                    placeholder="Redis서버 접속정보"
                    value="<?=$cfg['redis_host']?>"
                >
                <ul class="list_info">
                    <li>MySQL : 현재 접속자들의 실시간 현황을 파악할수 있습니다.</li>
                    <li>Redis : 접속자가 많을 경우 안정적인 서비스를 제공할수 있습니다.</li>
                    <li class="warning">변경 시 관리자와 쇼핑몰의 모든 로그인이 해제되며 장바구니가 비워집니다.</li>
                </ul>
            </td>
        </tr>
		<?php } ?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function() {
		var val = '<?=$cfg['change_pwd_m']?>';
		var val2 = '<?=$cfg['change_pwd']?>';
		var val3 = '<?=$cfg['change_pwd_m_re']?>';
		var val4 = '<?=$cfg['change_pwd_re']?>';
		if(val == 'd') cnt = 100;
		else if(val == 'm') cnt = 12;
		if(val3 == 'd') cnt2 = 100;
		else if(val3 == 'm') cnt2 = 12;

		var sel1 = $('#change_pwd')[0];
		var sel2 = $('#change_pwd_re')[0];

		//sel1.length = 0;
		//sel2.length = 0;

		for(var i=0;i<cnt;i++){
			sel1.options[i] = new Option(i+1,i+1);
		}
		for(var i=0;i<cnt2;i++){
			sel2.options[i] = new Option(i+1,i+1);
		}
		sel1.options[val2-1].selected = true;
		sel2.options[val4-1].selected = true;
	})

	function changeDay(val) {//cnt 인자로받기
		var cnt = 0;
		if(val == 'd') cnt = 100;
		else if(val == 'm') cnt = 12;

		var sel1 = $('#change_pwd')[0];

		sel1.length = 0;

		for(var i=0;i<cnt;i++){
			sel1.options[i] = new Option(i+1,i+1);
		}
	}
	function changeDayre(val) {
		var cnt2 = 0;
		if(val == 'd') cnt2 = 100;
		else if(val == 'm') cnt2 = 12;

		var sel2 = $('#change_pwd_re')[0];

		sel2.length = 0;

		for(var i=0;i<cnt2;i++){
			sel2.options[i] = new Option(i+1,i+1);
		}
	}
	function withdraw_type(f) {
		if(f.del_send_type1.checked == false && f.del_send_type2.checked == false) {
			alert('사전안내 수단을 선택해주세요');
			location.reload();
			return false;
		}
	}

	function checkJoinLimitConfigFrm(f) {
		if($('[name="join_14_limit"]').eq(0).is(':checked')) {
			f.join_14_limit_method[0].disabled = true;
			f.join_14_limit_method[1].disabled = true;
		} else if($('[name="join_14_limit"]').eq(1).is(':checked')){
			f.join_14_limit_method[0].disabled = false;
			f.join_14_limit_method[1].disabled = true;
		} else {
			f.join_14_limit_method[0].disabled = false;
			f.join_14_limit_method[1].disabled = false;
		}
		if($('[name="join_birth_use"]').eq(1).is(':checked') == true || ($('[name="join_birth_use"]').eq(0).is(':checked') == true &&  $('[name="member_join_birth"]').eq(1).is(':checked') == true)) {
			f.join_14_limit_method[0].disabled = true;
		}
	}

	function method_check(f) {
		if($('[name="join_14_limit"]').eq(1).is(':checked') || $('[name="join_14_limit"]').eq(2).is(':checked')) {
			if($('[name="join_14_limit_method"]').eq(0).is(':checked') == false && $('[name="join_14_limit_method"]').eq(1).is(':checked') == false){
				alert("인증수단을 선택해주세요.");
				return false;
			}
		}
		if($('[name="join_14_limit"]').eq(1).is(':checked') && $('[name="join_14_limit_method"]').eq(1).is(':checked')) {
			alert("인증수단을 선택해주세요.");
			return false;
		}
        printLoading();
	}

	function checkJoin19LimitConfigFrm(f) {
		var ipin = '<?=$cfg[ipin_use]?>';
		var checkplus = '<?=$cfg[ipin_checkplus_use]?>';
		if (ipin != 'Y' && checkplus != 'Y') {
			f.limit_19[0].disabled = true;
		}
	}

	$('.joinLimitConfigEvent', document.getElementById('joinlimitConfig')).click(function() {
		checkJoinLimitConfigFrm(this.form);
	});

	checkJoinLimitConfigFrm(document.getElementById('joinlimitConfig'));

	checkJoin19LimitConfigFrm(document.getElementById('join19limitConfig'));

    (setSessionEngine = function () {
        var val = $('select[name=session_engine]').val();
        $('.session_info').hide();
        $('.session_info_'+val).show();
    })();
    $('select[name=session_engine]').change(setSessionEngine);

</script>