<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 개인정보
	' +----------------------------------------------------------------------------------------------+*/

	$mailing_check=checked($amember[mailing],"Y");
	$sms_check=checked($amember[sms],"Y");

	$_birth=explode("-",$amember[birth]);
	$_phone=explode("-",$amember[phone]);
	$_cell=explode("-",$amember[cell]);
	$_email=explode("@",$amember[email]);

	$_biz=explode("-",$abiz[biz_num]);

	if (!$amember['join_ref']) {
		$amember['join_ref'] = '즐겨찾기 또는 주소 직접입력';
	} else if ($amember['join_ref'] == 'mng' || $amember['join_ref'] == 'mng') {
        $amember['join_ref'] = '수동등록 회원';
	} else {
		$amember['join_ref'] = "<span onClick=\"window.open('{$amember['join_ref']}')\" class=\"p_cursor\" title=\"$amember[join_ref]\">".cutStr($amember['join_ref'], 70)."</span>";
	}

	$conversion = '';
	if ($amember['conversion']) {
		$conversion = dispConversion($amember['conversion']);
	}

	$add_info_file=$root_dir."/_config/member.php";
	if(is_file($add_info_file)) {
		include_once $engine_dir."/_engine/include/member.lib.php";
		include_once $add_info_file;
	}
	if($cfg[join_jumin_use] <> "Y") {
		if($cfg[join_birth_use] == "Y"){
			for($ii=date('Y'); $ii>=1900; $ii--){
				$birth1_arr[]=$ii;
			}
			for($ii=1; $ii<=12; $ii++){
				if($ii<10) $ii="0".$ii;
				$birth2_arr[]=$ii;
			}
			for($ii=1; $ii<=31; $ii++){
				if($ii<10) $ii="0".$ii;
				$birth3_arr[]=$ii;
			}
		}
		if(!$amember[birth_type]) $amember[birth_type]="양";
		if(!$amember[sex]) $amember[sex]="남";
	}

?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js"></script>
<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkEditMember(this)" enctype="multipart/form-data">
	<input type="hidden" name="body" value="member@member_update.exe">
	<input type="hidden" name="mno" value="<?=$mno?>">
	<input type="hidden" name="member_join_addr" value="<?=$cfg['member_join_addr']?>">
	<table class="tbl_row">
		<caption class="hidden">개인정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?if($amember['14_limit'] == "Y"){?>
			<tr>
				<th scope="col">법정대리인 동의</th>
				<td>
					<label class="p_cursor"><input type="radio" name="limit_agree" value="Y" <?=checked($amember['14_limit_agree'],"Y")?>> 확인</label>
					<label class="p_cursor"><input type="radio" name="limit_agree" value="N" <?=checked($amember['14_limit_agree'],"N").checked($amember['14_limit_agree'],"")?>> 확인안됨</label>
				</td>
			</tr>
			<?}?>
			<?if($abiz[ref]){?>
			<tr>
				<th scope="col">사업자 승인</th>
				<td>
					<label class="p_cursor"><input type="radio" name="auth" value="Y" <?=checked($abiz[auth],"Y")?>> 승인</label>
					<label class="p_cursor"><input type="radio" name="auth" value="N" <?=checked($abiz[auth],"N").checked($abiz[auth],"")?>> 미승인</label>
				</td>
			</tr>
			<tr>
				<th scope="col">회사명</th>
				<td>
					<input type="hidden" name="level" value="<?=$amember[level]?>">
					<input type="text" name="name" value="<?=$amember[name]?>" class="input">
				</td>
			</tr>
			<tr>
				<th scope="col">담당자명</th>
				<td><input type="text" name="dam" value="<?=$abiz[dam]?>" class="input"></td>
			</tr>
			<tr>
				<th scope="col">대표자명</th>
				<td><input type="text" name="owner" value="<?=$abiz[owner]?>" class="input"></td>
			</tr>
			<tr>
				<th scope="col">사업자번호</th>
				<td>
					<input type="text" name="biz_num[]" id="biz_num" value="<?=$_biz[0]?>" class="input" size="3" maxlength="3"> - <input type="text" name="biz_num[]" id="biz_num" value="<?=$_biz[1]?>" class="input" size="2" maxlength="2"> - <input type="text" name="biz_num[]" id="biz_num" value="<?=$_biz[2]?>" class="input" size="5" maxlength="5">
				</td>
			</tr>
			<tr>
				<th scope="col">업태</th>
				<td><input type="text" name="biz_type1" value="<?=$abiz[biz_type1]?>" class="input"></td>
			</tr>
			<tr>
				<th scope="col">종목</th>
				<td><input type="text" name="biz_type2" value="<?=$abiz[biz_type2]?>" class="input"></td>
			</tr>
			<tr>
				<th scope="col">창립기념일</th>
				<td><input type="text" name="biz_birthday" value="<?=$abiz[biz_birthday]?>" class="input"></td>
			</tr>
			<?}else{?>
			<tr>
				<th scope="col">이름</th>
				<td>
                    <?php if ($amember['first_name'] || $amember['familay_name']) { ?>
                    <input type="text" name="first_name" value="<?=$amember['first_name']?>" class="input">
                    <input type="text" name="family_name" value="<?=$amember['family_name']?>" class="input">
                    <?php } else { ?>
                    <input type="text" name="name" value="<?=$amember[name]?>" class="input">
                    <?php } ?>
                </td>
			</tr>
			<?}?>
			<?if($cfg['member_join_id_email'] != 'Y') {?>
			<tr>
				<th scope="col">아이디</th>
				<td><strong><?=$amember[member_id]?></strong></td>
			</tr>
			<?}?>
			<?if($cfg['member_join_nickname'] == 'Y') {?>
			<tr>
				<th scope="col">닉네임</th>
				<td><input type="text" name="nick" value="<?=$amember['nick']?>" class="input"></td>
			</tr>
			<?}?>
			<tr>
				<th scope="col">새 비밀번호</th>
				<td><input type="password" name="pwd[]" id="pwd" class="input" size="10" autocomplete="new-password"> <span class="explain">(시스템 암호화로 현재 비밀번호는 알 수 없습니다, 변경시에만 입력하세요.)</span></td>
			</tr>
			<tr>
				<th scope="col">새 비밀번호 확인</th>
				<td><input type="password" name="pwd[]" id="pwd" class="input" size="10" autocomplete="new-password"> <span class="explain">(변경시에만 입력하세요.)</span></td>
			</tr>
			<tr>
				<th scope="col">전화번호</th>
				<td>
					<input type="text" name="phone" id="phone" value="<?=$amember['phone']?>" class="input">
				</td>
			</tr>
			<tr>
				<th scope="col">휴대폰번호</th>
				<td>
					<input type="text" name="cell" id="cell" value="<?=$amember['cell']?>" class="input">
					<label class="p_cursor"><input type="checkbox" name="sms" value="Y" <?=$sms_check?>> <span class="explain">전체문자메세지수신</span></label>
				</td>
			</tr>
			<tr>
				<th>이메일</th>
				<td>
					<input type="text" name="email1" value="<?=$_email[0]?>" class="input"> @
					<input type="text" name="email2" value="<?=$_email[1]?>" class="input">
					<select name="email3" onChange="chgEmail(this.form.email2,this,'')">
						<option value="">::주소선택::</option>
						<option value="naver.com">naver.com (네이버)</option>
						<option value="paran.com">paran.com (파란)</option>
						<option value="empal.com">empal.com (엠파스)</option>
						<option value="nate.com">nate.com (네이트)</option>
						<option value="yahoo.co.kr">yahoo.co.kr (야후코리아)</option>
						<option value="dreamwiz.com">dreamwiz.com (드림위즈)</option>
						<option value="freechal.com">freechal.com (프리챌)</option>
						<option value="hotmail.com">hotmail.com (핫메일)</option>
						<option value="hanafos.com">hanafos.com (하나포스닷컴)</option>
						<option value="korea.com">korea.com (코리아닷컴)</option>
						<option value="chollian.net">chollian.net (천리안)</option>
						<option value="">::직접입력::</option>
					</select>
					<label class="p_cursor"><input type="checkbox" name="mailing" value="Y" <?=$mailing_check?>> <span class="explain">전체메일수신</span></label>
				</td>
			</tr>
			<tr>
				<th>주소</th>
				<td>
					<input type="text" name="zip" value="<?=$amember[zip]?>" class="input"  size="7">
					<span class="box_btn_s"><input type="button" value="우편번호검색" class="btn2" onClick="zipSearchM('joinFrm','zip','addr1','addr2')"></span><br>
					<input type="text" name="addr1" value="<?=$amember[addr1]?>" class="input" size="50" maxlength="50" style="margin:5px 0;"><br>
					<input type="text" name="addr2" value="<?=inputText($amember[addr2])?>" class="input" size="50" maxlength="100">
				</td>
			</tr>
			<?if($cfg[join_jumin_use] == "Y"){?>
			<tr>
				<th scope="col">주민등록번호</th>
				<td>
					<?=substr($amember[jumin],0, 8)?>****** <span class="explain">(주민등록번호 뒷자리는 암호화 되어 확인하실수 없습니다)</span>
				</td>
			</tr>
			<?} else {?>
			<?if($cfg[join_birth_use] == "Y"){?>
			<tr>
				<th scope="col">생년월일</th>
				<td>
					<?=selectArray($birth1_arr,'birth1',1,'----',$_birth[0])?>년
					<?=selectArray($birth2_arr,'birth2',1,'--',$_birth[1])?>월
					<?=selectArray($birth3_arr,'birth3',1,'--',$_birth[2])?>일
					<label class="p_cursor"><input type="radio" name="birth_type" value="양" <?=checked($amember[birth_type],"양")?>>양력</label>
					<label class="p_cursor"><input type="radio" name="birth_type" value="음" <?=checked($amember[birth_type],"음")?>>음력</label>
				</td>
			</tr>
			<?}?>
			<?if($cfg[join_sex_use] == "Y"){?>
			<tr>
				<th scope="col">성별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="sex" value="남" <?=checked($amember[sex],"남")?>>남</label>
					<label class="p_cursor"><input type="radio" name="sex" value="여" <?=checked($amember[sex],"여")?>>여</label>
				</td>
			</tr>
			<?}?>
			<?}?>
			<?if($_use[recom_member]){?>
			<tr>
				<th scope="col">추천인</th>
				<td>
					<input type="text" name="recom_member" value="<?=$amember[recom_member]?>" class="input">
				</td>
			</tr>
			<?}?>
			<?if($cfg[use_whole_mem] == "Y"){?>
			<tr>
				<th scope="col">평생회원</th>
				<td>
					<label class="p_cursor"><input type="radio" name="whole_mem" value="Y" <?=checked($amember[whole_mem],"Y")?>>동의함</label>
					<label class="p_cursor"><input type="radio" name="whole_mem" value="N" <?=checked($amember[whole_mem],"N")?>>동의안함</label>
				</td>
			</tr>
			<?}?>
			<?if (is_array($_mbr_add_info)){foreach($_mbr_add_info as $key => $val) {?>
			<tr>
				<th scope="col"><?=$_mbr_add_info[$key][name]?></th>
				<td><?=memberAddFrm($key)?></td>
			</tr>
			<?}}?>
			<tr>
				<th scope="col">가입경로</th>
				<td style="word-break;break-all"><?=$amember[join_ref]?></td>
			</tr>
			<?php if ($amember['conversion']){?>
			<tr>
				<th scope="col">유입경로</th>
				<td style="word-break;break-all"><?=$conversion?></td>
			</tr>
			<?php }?>
			<tr>
				<th scope="col">관리자 메모</th>
				<td><textarea name="mng_memo" class="txta" rows="5" cols="100"><?=stripslashes($amember[mng_memo])?></textarea></td>
			</tr>
		</tbody>
	</table>
	<div class="pop_bottom"><span class="box_btn blue"><input type="submit" value="수정"></span></div>
</form>

<script language="JavaScript" type="text/javascript">
	chgEmail(document.joinFrm.email2,document.joinFrm.email3,'<?=$_email[1]?>');

	function zipSearch(form_nm,zip_nm,addr1_nm,addr2_nm){
		srurl=manage_url+'/common/zip_search.php?urlfix=Y&form_nm='+form_nm+'&zip_nm='+zip_nm+'&addr1_nm='+addr1_nm+'&addr2_nm='+addr2_nm;
		window.open(srurl,'zip', ('scrollbars=yes,resizable=no,width=374, height=170'));
	}
</script>