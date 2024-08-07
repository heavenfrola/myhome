<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  가입 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['password_min']) $cfg['password_min'] = 4;
	if(!$cfg['password_max']) $cfg['password_max'] = 0;
	if(!$cfg['join_check_cell']) $cfg['join_check_cell'] = 'N';
	if(!$cfg['join_check_email']) $cfg['join_check_email'] = 'N';
	if(!$cfg['member_join_addr']) $cfg['member_join_addr'] = 'Y';
	if(!$cfg['join_birth_use']) $cfg['join_birth_use'] = 'N';
	if(!$cfg['join_birth_sex']) $cfg['join_birth_sex'] = 'N';
	if(!$cfg['member_join_nickname']) $cfg['member_join_nickname'] = 'Y';
	if(!$cfg['member_join_id_email']) $cfg['member_join_id_email'] = 'N';
	if(!$cfg['use_whole_mem']) $cfg['use_whole_mem'] = 'N';
	if(!$cfg['join_addr_use']) $cfg['join_addr_use'] = 'Y';
	if(!$cfg['nickname_essential']) $cfg['nickname_essential'] = 'N';
	if(!$cfg['member_join_birth']) $cfg['member_join_birth'] = 'N';
	if(!$cfg['member_join_sex']) $cfg['member_join_sex'] = 'N';

	//주소설정시 pc/mobile 파일 체크
	$pc_skin = "";
	$mobile_skin = "";
	$_skin = getSkinCfg();
	$pc_skin_name = ($design['edit_skin']) ? $design['edit_skin'] : $design['skin'];
	$pc_skin = $root_dir."/_skin/".$pc_skin_name."/MODULE/join_addr.wsm";

	if($cfg['mobile_use'] == 'Y') {
		include_once $_skin['dir']."/mconfig.".$_skin_ext[g];
		$mobile_skin_name = ($design['edit_skin']) ? $design['edit_skin'] : $design['skin'];
		$mobile_skin = $root_dir."/_skin/".$mobile_skin_name."/MODULE/join_addr.wsm";
	}

	// 아이디, 이름, 닉네임 필터
	$res = $pdo->iterator("select code, value from {$tbl['default']} where code like 'name_filter_%'");
    foreach ($res as $data) {
		$cfg[$data['code']] =  $data['value'];
	}

    // ipin 체크플러스 사용 시 휴대폰 인증 사용 체크 필수
    if ($scfg->comp('ipin_checkplus_use', 'Y')) {
        $cfg['member_confirm_sms'] = 'Y';
        $cfg['join_check_cell'] = 'Y';
    }

?>
<input type="hidden" name="join_jumin_use" value="N">
<table class="tbl_row">
	<caption class="hidden">가입 설정</caption>
	<colgroup>
		<col style="width:15%">
		<col>
	</colgroup>
	<tr>
		<th scope="row">아이디</th>
		<td colspan="2">
			<label><input type="radio" name="member_join_id_email" class="joinConfigEvent" value="Y" <?=checked($cfg['member_join_id_email'],'Y')?>> 이메일로 받기</label>
			<label><input type="radio" name="member_join_id_email" class="joinConfigEvent" value="N" <?=checked($cfg['member_join_id_email'],'N')?>> 아이디로 받기</label>
		</td>
	</tr>
	<tr>
		<th scope="row">이름</th>
		<td colspan="2">
			<label><input type="checkbox" name="member_join_nm_num" class="joinConfigEvent" value="Y" <?=checked($cfg['member_join_nm_num'], 'Y')?>> 숫자 허용</label>
			<label><input type="checkbox" name="member_join_nm_spc" class="joinConfigEvent" value="Y" <?=checked($cfg['member_join_nm_spc'], 'Y')?>> 특수문자 허용</label>
		</td>
	</tr>
	<tr>
		<th scope="row">닉네임</th>
		<td colspan="2">
			<label><input type="radio" name="member_join_nickname" value="Y" <?=checked($cfg['member_join_nickname'],'Y')?> class="joinConfigEvent"> 사용함</label>
			(<label><input type="radio" name="nickname_essential" value="Y" <?=checked($cfg['nickname_essential'], 'Y')?>> 필수</label>
			<label><input type="radio" name="nickname_essential" value="N" <?=checked($cfg['nickname_essential'], 'N')?>> 선택</label>)
			<label><input type="radio" name="member_join_nickname" value="N" <?=checked($cfg['member_join_nickname'],'N')?> class="joinConfigEvent"> 사용안함</label>
		</td>
	</tr>
	<tr>
		<th scope="row">이메일</th>
		<td colspan="2">
			<label class="p_cursor"><input type="radio" name="join_check_email" value="N" <?=checked($cfg['join_check_email'],'N')?>> 중복가입 허용</label>
			<label class="p_cursor"><input type="radio" name="join_check_email" value="Y" <?=checked($cfg['join_check_email'],'Y')?>> 중복가입 금지</label>
			<div>
				<label class="p_cursor"><input type="checkbox" name="member_confirm_email" class="joinConfigEvent" value="Y" <?=checked($cfg['member_confirm_email'], 'Y')?>> 이메일 인증 사용</label>
				<ul class="list_info tp">
                    <?php if ($scfg->comp('ipin_checkplus_use', 'Y')) { ?>
                    <li>
                        <a href="?body=member@ipin" target="_blank">아이핀 체크플러스가 설정되어있습니다.</a>
                        아이핀 체크플러스 이용 시 미인증된 과거 회원이 없는 경우 해당 기능을 해제해주시기 바랍니다.
                    </li>
                    <?php } ?>
					<li>이메일 인증 사용 시 회원가입 완료 후 1시간 이내 발송된 이메일을 통해 인증을 진행하면 가입이 최송 승인됩니다.</li>
					<li>1시간 초과 시 다시 인증을 받아야 합니다.</li>
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row" rowspan="2">휴대폰</th>
        <td colspan="2">
            <label class="p_cursor"><input type="radio" name="join_check_cell" value="N" <?=checked($cfg['join_check_cell'],'N')?>> 중복가입 허용</label>
            <label class="p_cursor"><input type="radio" name="join_check_cell" value="Y" <?=checked($cfg['join_check_cell'],'Y')?>> 중복가입 금지</label>
        </td>
	</tr>
    <tr>
        <td>
            <h3>
                휴대폰 인증 사용
                <a href="#" class="tooltip_trigger" data-child="tooltip_select_board" style="float:none;">설명</a>
                <div class="info_tooltip tooltip_select_board">
                    <h3>휴대폰 인증 사용</h3>
                    <ul class="list_info">
                        <li>휴대폰 인증 사용 시 휴대폰으로 전송된 인증번호를 정확히 입력해야 가입이 가능합니다.</li>
                        <li>쇼핑몰의 윙문자포인트를 사용하므로 잔여 포인트가 없는 경우 고객에게 오류메시지가 출력됩니다.</li>
                    </ul>
                    <a href="#" class="tooltip_closer">닫기</a>
                </div>
            </h3>
            <ul>
                <li><label><input type="radio" name="member_confirm_sms" class="joinConfigEvent" value="Y" <?=checked($cfg['member_confirm_sms'], 'Y')?>> 사용함</label></li>
                <li><label><input type="radio" name="member_confirm_sms" class="joinConfigEvent" value="N" <?=checked($cfg['member_confirm_sms'], 'N')?>> 사용안함</label></li>
            </ul>
        </td>
        <td class="lb">
            <h3>인증번호 설정</h3>
            <ul>
                <li>
                    인증번호 자릿수는
                    <?=selectArray(array(3, 4, 5, 6, 7, 8, 9, 10), 'member_confirm_sms_cnt', 1, '', $scfg->get('member_confirm_sms_cnt'))?> 자리로 설정
                </li>
                <li>
                    구분문자는
                    <?=selectArray(array(1 => '사용안함', 2 => '-', 3 => '공백'), 'member_confirm_sms_str', 2, '', $scfg->get('member_confirm_sms_str'), 'smsStrCheck(this.form,this.value)')?> 을 <?=selectArray(array(3, 4, 5), 'member_confirm_sms_strplace', 1, '', $scfg->get('member_confirm_sms_strplace'))?> 자리 마다 지정
                </li>
                <li>
                    <span class="list_info2">구분문자를 설정하여 문자를 발송하면 인증번호를 특수기호나 공백으로 쉽게 구별할 수 있습니다.</span>
                    <span class="list_info2">구분문자를 사용하는 경우 ①숫자로만 구성된 인증번호 또는 ②구분문자를 포함한 전체 인증번호 둘 다 동일하게 인증합니다.</span>
                </li>
            </ul>
        </td>
    </tr>
	<tr>
		<th scope="row">생년월일</th>
		<td colspan="2">
			<label class="p_cursor"><input type="radio" name="join_birth_use" value="Y" <?=checked($cfg['join_birth_use'],'Y')?> class="joinConfigEvent"> 사용함</label>
			(<label><input type="radio" name="member_join_birth" value="Y" <?=checked($cfg['member_join_birth'], 'Y')?>> 필수</label>
			<label><input type="radio" name="member_join_birth" value="N" <?=checked($cfg['member_join_birth'], 'N')?>> 선택</label>)
			<label class="p_cursor"><input type="radio" name="join_birth_use" value="N" <?=checked($cfg['join_birth_use'],'N')?> class="joinConfigEvent"> 사용안함</label>
			<?if($cfg['join_14_limit_method'] == 1 && $cfg['join_14_limit'] != "A") {?>
				<span class="msg_bubble warning">가입연령제한 설정에 의해 생년월일 설정을 변경할 수 없습니다.</span>
			<?}?>
			<div>
				<label class="p_cursor"><input type="checkbox" name="birth_modify_use" class="joinConfigEvent" value="Y" <?=checked($cfg['birth_modify_use'], 'Y')?>> 수정제한</label>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">성별</th>
		<td colspan="2">
			<label class="p_cursor"><input type="radio" name="join_sex_use" value="Y" <?=checked($cfg['join_sex_use'],'Y')?> class="joinConfigEvent"> 사용함</label>
			(<label><input type="radio" name="member_join_sex" value="Y" <?=checked($cfg['member_join_sex'], 'Y')?>> 필수</label>
			<label><input type="radio" name="member_join_sex" value="N" <?=checked($cfg['member_join_sex'], 'N')?>> 선택</label>)
			<label class="p_cursor"><input type="radio" name="join_sex_use" value="N" <?=checked($cfg['join_sex_use'],'N')?> class="joinConfigEvent"> 사용안함</label>
		</td>
	</tr>
	<tr>
		<th scope="row">주소</th>
		<td colspan="2">
			<label><input type="radio" name="join_addr_use" value="Y" <?=checked($cfg['join_addr_use'], 'Y')?> class="joinConfigEvent"> 사용함</label>
			(<label><input type="radio" name="member_join_addr" value="Y" <?=checked($cfg['member_join_addr'], 'Y')?>> 필수</label>
			<label><input type="radio" name="member_join_addr" value="N" <?=checked($cfg['member_join_addr'], 'N')?>> 선택</label>)
			<label><input type="radio" name="join_addr_use" value="N" <?=checked($cfg['join_addr_use'], 'N')?> class="joinConfigEvent"> 사용안함</label>
			<?if(!is_file($pc_skin) || !is_file($mobile_skin)) {?>
				<ul class="list_info tp">
					<li>
						설정에 따라 주소항목 노출이 제어되지 않을 경우 회원 가입/정보 수정 내 {{$주소}} 디자인코드 삽입 및 편집유무를 확인해 주세요.<br>
						[PC 쇼핑몰] <a href="?body=design@editor&type=&edit_pg=5%2F6" target="_blank">바로가기</a>
						[모바일 쇼핑몰] <a href="?body=wmb@editor&type=mobile&edit_pg=5%2F5" target="_blank">바로가기</a>
					</li>
				</ul>
			<?}?>
		</td>
	</tr>
	<tr id="jumin_md5" style="display:<? if($cfg['join_jumin_use'] == 'N') echo "none;"; ?>">
		<th scope="row">주민번호 암호화</th>
		<td colspan="2">
			<input type="radio" name="jumin_encode" value="Y" checked> 설정 &nbsp;
			<ul class="list_msg">
				<li>개인정보취급방침에 의해 회원의 주민등록번호 뒷자리 7자리중 끝6자리가 암호화됩니다. 암호화된 정보는 복호화가 되지 않습니다.<br>
				<a href="./?body=member@namecheck" target="_blank"><u>아이핀</u></a>을 통한 회원가입자는 주민번호 입력받기가 미적용됩니다.</li>
			</ul>
		</td>
	</tr>
	<!-- 2016-08-05 by zardsama
	<tr>
		<th scope="row">기존회원 처리방법</th>
		<td>
			<label class="p_cursor"><input type="checkbox" name="member_reconfirm" value="Y" <?=checked($cfg['member_reconfirm'], 'Y')?>> 기존 주민번호로 가입된 회원의 경우 개인확인 방법'에 따라 인증을 받은 뒤 이용 가능합니다.</label>
			<ul class="list_msg">
				<li>가입시 개인확인 방법(이메일인증/휴대폰인증)을 선택한 경우에만 유효합니다.</li>
				<li>선택시 미인증 회원의 경우 자동으로 회원정보 수정 페이지로 이동됩니다.</li>
			</ul>
			<label class="p_cursor"><input type="checkbox" name="rebirth" value="Y"> 생년월일 및 성별을 주민등록번호를 기준으로 추출합니다.</label>
			<ul class="list_msg">
				<li>생일은 주민등록번호의 앞자리를 기준으로 추출됩니다.</li>
				<li>회원이 많은 쇼핑몰의 경우 변환시간이 오래걸릴 수 있습니다.</li>
			</ul>
		</td>
	</tr>
	-->
	<tr>
		<th scope="row">비밀번호</th>
		<td colspan="2">
			<ul>
				<li>최소길이 <input type="text" name="password_min" class="input right" size="3" value="<?=$cfg['password_min']?>"> 자 이상</li>
				<li>최대길이 <input type="text" name="password_max" class="input right" size="3" value="<?=$cfg['password_max']?>"> 자 이하</li>
				<li><label class="p_cursor"><input type="checkbox" name="password_engnum" value="Y" <?=checked($cfg['password_engnum'], 'Y')?>> 영문숫자혼용 필수</label></li>
				<li><label class="p_cursor"><input type="checkbox" name="password_special" value="Y" <?=checked($cfg['password_special'], 'Y')?>> 특수문자혼용 필수</label></li>
			</ul>
			<ul class="list_info tp">
				<li>비밀번호는 복호가 불가능한 최신 알고리즘으로 데이터베이스에 저장됩니다.</li>
				<li>비밀번호 최대길이 설정 시 로그인/회원가입폼의 비밀번호 입력란의 maxlength 속성을 미리 확인해 주세요.</li>
				<li>최소길이는 4미만으로 설정할 수 없습니다.</li>
				<li>최대길이를 0~4 사이로 입력하면 <strong>무제한</strong>으로 설정됩니다.</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th scope="row">평생회원동의</th>
		<td colspan="2">
			<label class="p_cursor"><input type="radio" name="use_whole_mem" value="Y" <?=checked($cfg['use_whole_mem'], 'Y')?>> 사용함</label>
			<label class="p_cursor"><input type="radio" name="use_whole_mem" value="N" <?=checked($cfg['use_whole_mem'], 'N')?>> 사용안함</label>
			<ul class="list_info tp">
				<li>운영중인 사이트의 경우 최초 설정 시 시간이 다소 소요될 수 있습니다.</li>
				<li>
					사용 전 사용중인 스킨 내 {{$평생회원동의}} 디자인코드 삽입 및 편집유무를 확인해 주세요.<br>
					[PC 쇼핑몰] <a href="?body=design@editor&type=&edit_pg=5%2F7" target="_blank">바로가기</a>
					[모바일 쇼핑몰] <a href="?body=wmb@editor&type=mobile&edit_pg=5%2F6" target="_blank">바로가기</a>
				</li>
				<li>평생회원으로 가입 및 전환된 회원은 휴면회원으로 전환되지 않습니다.</li>
				<li>평생회원에게는 휴면회원 사전메일이 발송되지 않습니다.</li>
			</ul>
			<div class="list_info tp">
				<p class="title"><strong>[관련 법령 근거]</strong></p>
				<ul class="list_info">
					<li>정보통신망 이용촉진 및 정보보호 등에 관한 법률 제29조 2항</li>
					<li>정보통신서비스 제공자등은 정보통신서비스를 1년의 기간 동안 이용하지 아니하는 이용자의 개인정보를 보호하기 위하여 대통령령으로 정하는 바에 따라 개인정보의 파기 등 필요한 조치를 취하여야 한다.<br>다만, 그 기간에 대하여 다른 법령 또는 이용자의 요청에 따라 달리 정한 경우에는 그에 따른다.</li>
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">사업자회원</th>
		<td colspan="2">
			<label class="p_cursor"><input type="radio" name="use_biz_member" value="Y" <?=checked($cfg['use_biz_member'], 'Y')?>> 사용함</label>
			<label class="p_cursor"><input type="radio" name="use_biz_member" value="" <?=checked($cfg['use_biz_member'], '')?>> 사용안함</label>
			<ul class="list_info tp">
				<li>사용함으로 설정 시 8등급 회원이 사업자회원으로 변경됩니다. 기존 운영 중인 쇼핑몰에서는 설정 시 주의 바랍니다.</li>
				<li>사업자가입 시 <span class="p_color">고객 CRM > 회원종합관리 > 회원 조회 > 회원 - 개인정보</span>에서 승인 처리가 필요합니다.</li>
			<ul>
		</td>
	</tr>
	<?php /* 사업자회원 API 추가 */?>
    <tr>
        <th scope="row">사업자회원 API 여부</th>
        <td colspan="2">
            <label class="p_cursor"><input type="radio" name="use_biz_api_yn" value="Y" <?php echo checked($cfg['use_biz_api_yn'], 'Y'); ?>> 사용함</label>
            <label class="p_cursor"><input type="radio" name="use_biz_api_yn" value="" <?php echo checked($cfg['use_biz_api_yn'], ''); ?>> 사용안함</label>
            <ul class="list_info tp">
                <li>국세청 사업자등록정보 진위확인 및 상태조회 서비스 입니다.</li>
                <li>사용 시 <span class="p_color">2년마다 서비스키 갱신</span>이 필요합니다.</li>
            </ul>
            <table class="tbl_inner full line">
                <caption class="hidden">사이트키</caption>
                <colgroup>
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <td>사이트 키</td>
                    <td>
                        <textarea name="use_biz_api_skey" class="txta" style="height:40px;"><?php echo inputText($cfg['use_biz_api_skey']); ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

	<tr>
		<th scope="row">아이디/이름/닉네임 금지어</th>
		<td colspan="2">
			<table class="tbl_inner full line">
				<caption class="hidden">아이디/이름/닉네임 금지어</caption>
				<colgroup>
					<col style="width:120px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th>구분</th>
					<th>금지어</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>포함단어</td>
					<td>
						<textarea name="name_filter_1" class="txta" style="height:50px;"><?=inputText($cfg['name_filter_1'])?></textarea>
						<div class="list_info tp left">
							<p>예) wisa - wisa(x), awisa(x), wisaa(x)</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>시작단어</td>
					<td>
						<textarea name="name_filter_2" class="txta" style="height:50px;"><?=inputText($cfg['name_filter_2'])?></textarea>
						<div class="list_info tp left">
							<p>예) wisa - wisa(x), awisa(o), wisaa(x)</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>끝단어</td>
					<td>
						<textarea name="name_filter_3" class="txta" style="height:50px;"><?=inputText($cfg['name_filter_3'])?></textarea>
						<div class="list_info tp left">
							<p>예) wisa - wisa(x), awisa(x), wisaa(o)</p>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
			<ul class="list_info tp">
				<li>회원가입을 제한할 아아디, 이름, 닉네임을 설정할 수 있습니다. 단, SNS로그인을 통해 수신된 이메일 아이디는 대상에서 제외됩니다.</li>
				<li>여러 개의 금지어가 있을 경우 쉼표(,)로 구분하여 입력하세요. 예) wisa,admin</li>
			</ul>
		</td>
	</tr>
</table>

<script type="text/javascript">
	var check_method = "<?=$cfg['join_14_limit_method']?>";
	var join_limit = "<?=$cfg['join_14_limit']?>";
	function vno(f){
		obj1=document.getElementById('jumin_no');
		obj2=document.getElementById('jumin_md5');
		if(f.join_jumin_use[0].checked){
			obj1.style.display='none';
			obj2.style.display='block';
		}else if(f.join_jumin_use[1].checked){
			obj1.style.display='block';
			obj2.style.display='none';

			f.join_birth_use.checked = true;
			f.join_sex_use.checked = true;
		}
	}
	function use_checkbox(f){
		for(ii=0; ii<f.elements.length; ii++){
			fd=f.elements[ii];
			if(fd.type == 'checkbox' && !fd.checked){
				fd.value='N'; fd.style.visibility='hidden'; fd.checked=true;
			}
		}
	}

    const use_ipincheckplus = '<?=$scfg->get('ipin_checkplus_use')?>';
	function checkJoinConfigFrm(f) {
		if(f.member_join_id_email.value == 'Y' || f.member_confirm_email.checked == true) {
			f.join_check_email[1].checked = true;
			f.join_check_email[0].disabled = true;
		} else {
			f.join_check_email[0].disabled = false;
		}

		if(f.member_confirm_sms.value == 'Y') {
			f.join_check_cell[1].checked = true;
			f.join_check_cell[0].disabled = true;
            f.member_confirm_sms_cnt.disabled = false;
            f.member_confirm_sms_str.disabled = false;
            if (f.member_confirm_sms_str.value == '1') {
                f.member_confirm_sms_strplace.disabled = true;
            } else {
                f.member_confirm_sms_strplace.disabled = false;
            }
		} else {
            if (use_ipincheckplus == 'Y') {
                window.alert('아이핀 체크플러스 이용시 휴대폰 인증을 해제할 수 없습니다.');
                return false;
            } else {
			    f.join_check_cell[0].disabled = false;
                //가입시 휴대폰인증 미사용 시에도 인증번호 설정(자릿수, 구분문자 등)은 가능.
            }
		}

		if(f.join_addr_use.value == "N") {
			f.member_join_addr[0].disabled = true;
			f.member_join_addr[1].disabled = true;
		} else {
			f.member_join_addr[0].disabled = false;
			f.member_join_addr[1].disabled = false;
		}
		if(f.join_sex_use.value == "N") {
			f.member_join_sex[0].disabled = true;
			f.member_join_sex[1].disabled = true;
		} else {
			f.member_join_sex[0].disabled = false;
			f.member_join_sex[1].disabled = false;
		}
		if(f.join_birth_use.value == "N") {
			f.member_join_birth[0].disabled = true;
			f.member_join_birth[1].disabled = true;
			f.birth_modify_use.disabled = true;
		} else {
			f.member_join_birth[0].disabled = false;
			f.member_join_birth[1].disabled = false;
			f.birth_modify_use.disabled = false;

		}
		if(f.member_join_nickname.value == "N") {
			f.nickname_essential[0].disabled = true;
			f.nickname_essential[1].disabled = true;
		} else {
			f.nickname_essential[0].disabled = false;
			f.nickname_essential[1].disabled = false;
		}

		if(check_method == 1 && join_limit != "A") {
			$('[name="join_birth_use"]').eq(1).attr('disabled' , true);
			f.member_join_birth[1].disabled = true;
		}
	}

    function smsStrCheck(f, v) {
        if (v == '1') {
            f.member_confirm_sms_strplace.disabled = true;
        } else {
            f.member_confirm_sms_strplace.disabled = false;
        }
    }

	$('.joinConfigEvent', document.getElementById('joinConfig')).click(function() {
		return checkJoinConfigFrm(this.form);
	});

	checkJoinConfigFrm(document.getElementById('joinConfig'));
</script>