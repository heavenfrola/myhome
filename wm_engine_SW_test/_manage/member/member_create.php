<?php

/**
 * 수동 회원 등록
 **/

// 회원 등급
$_member_level = array();
$res = $pdo->iterator("select no, name from {$tbl['member_group']} where use_group='Y' order by no desc");
foreach ($res as $data) {
    $_member_level[$data['no']] = stripslashes($data['name']);
}

// 생일
$birth1_arr = array();
for ($ii = date('Y'); $ii >= 1900; $ii--) {
    $birth1_arr[] = $ii;
}
for ($ii=1; $ii <= 12; $ii++) {
    if ($ii <  10) $ii = '0'.$ii;
    $birth2_arr[] = $ii;
}
for ($ii=1; $ii <= 31; $ii++) {
    if ($ii < 10) $ii = '0'.$ii;
    $birth3_arr[] = $ii;
}

?>
<form id="joinFrm" name="joinFrm" method="POST" action="./index.php" onsubmit="this.target = hid_frame">
    <input type="hidden" name="body" value="member@member_update.exe">
    <input type="hidden" name="exec" value="register">
    <input type="hidden" name="no" value="<?=$data['no']?>">

	<div class="box_title first">
		<h2 class="title">수동 회원 등록</h2>
	</div>
    <table class="tbl_row">
        <caption class="hidden">수동 회원 등록</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row"><strong>회원아이디</strong></th>
                <td>
                    <input type="text" name="member_id" class="input input_disabled" size="20" readonly>
                    <label><input type="checkbox" name="editable" value="Y" checked> 임시아이디 사용(마이페이지에서 아이디 1회 변경 가능)</label>
                    <ul class="list_info">
                        <li>임시아이디는 휴대전화번호/이메일 주소로 생성되며 정보 입력 시 자동 기입됩니다.</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th scope="row">비밀번호</th>
                <td>
                    <input type="password" name="pwd" class="input" size="20">
                    <ul class="list_info">
                        <li>비밀번호 미입력 시 고객이 비밀번호 찾기 기능을 이용해 생성할수 있습니다.</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th scope="row"><strong>이름</strong></th>
                <td>
                    <input type="text" name="name" class="input" size="20">
                </td>
            </tr>
            <tr>
                <th scope="row">회원등급</th>
                <td>
                    <?=selectArray($_member_level, 'level', false)?>
                </td>
            </tr>
            <tr>
                <th scope="row">전화번호</th>
                <td>
                    <input type="text" name="phone" class="input" size="20">
                </td>
            </tr>
            <tr>
                <th scope="row"><strong>휴대전화번호</strong></th>
                <td>
                    <input type="text" name="cell" class="input" size="20">
                    <label><input type="checkbox" name="sms" value="Y"> 이벤트 정보 SMS 수신</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><strong>이메일주소</strong></th>
                <td>
                    <input type="text" name="email" class="input" size="60">
                    <label><input type="checkbox" name="mailing" value="Y"> 이벤트 정보 메일 수신</label>
                </td>
            </tr>
            <tr>
                <th scope="row" rowspan="2">주소</th>
                <td>
                    <input type="text" name="zip" class="input" size="10">
                    <span class="box_btn_s">
                        <input
                            type="button"
                            value="우편번호검색"
                            onclick="zipSearchM('joinFrm','zip','addr1','addr2')"
                        >
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" name="addr1" class="input" size="60" style="margin-bottom: 5px"><br>
                    <input type="text" name="addr2" class="input" size="60">
                </td>
            </tr>
			<?php if($scfg->comp('join_birth_use', 'Y') == true) { ?>
			<tr>
				<th scope="col">생년월일</th>
				<td>
					<?=selectArray($birth1_arr, 'birth1', true, '----',$_birth[0])?>년
					<?=selectArray($birth2_arr, 'birth2', true, '--', $_birth[1])?>월
					<?=selectArray($birth3_arr, 'birth3', true, '--', $_birth[2])?>일
					<label class="p_cursor"><input type="radio" name="birth_type" value="양" checked>양력</label>
					<label class="p_cursor"><input type="radio" name="birth_type" value="음">음력</label>
				</td>
			</tr>
			<?php } ?>
			<?php if ($scfg->comp('join_sex_use', 'Y') == true) { ?>
			<tr>
				<th scope="col">성별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="sex" value="남" checked>남</label>
					<label class="p_cursor"><input type="radio" name="sex" value="여">여</label>
				</td>
			</tr>
			<?php } ?>
            <tr>
                <th scope="row">평생회원</th>
                <td>
                    <label><input type="radio" name="whole_mem" value="Y"> 동의함</label>
                    <label><input type="radio" name="whole_mem" value="N" checked> 동의안함</label>
                </td>
            </tr>
			<?php if (isset($amember['14_limit']) == true && $amember['14_limit'] == 'Y') { ?>
			<tr>
				<th scope="col">법정대리인 동의</th>
				<td>
					<label class="p_cursor"><input type="radio" name="limit_agree" value="Y"> 확인</label>
					<label class="p_cursor"><input type="radio" name="limit_agree" value="N"> 확인안됨</label>
				</td>
			</tr>
			<?php } ?>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="button" value="닫기" onclick="createMember.close();"></span>
    </div>
</form>
<script>
$(function() {
    var f = document.querySelector('#joinFrm');

    $(':checkbox[name=editable], input[name=cell], input[name=email]').on('change keyup', function() {
        var member_id = $(f.member_id);
        var editable = $(f.editable);

        if (editable.prop('checked') == true) {
            member_id.prop('readonly', true).addClass('input_disabled');

            if (f.cell.value) member_id.val(f.cell.value);
            else if (f.email.value) member_id.val(f.email.value);
        } else {
            member_id.prop('readonly', false).removeClass('input_disabled');
        }
    });
});
</script>