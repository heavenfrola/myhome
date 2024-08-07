<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 로그인보안 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($admin['level'] > 2) msg('접근 권한이 없습니다.', 'back', 'parent');

	$mng_count = $pdo->row("select count(*) from $tbl[mng] where cell='' or email=''");

    if (empty($cfg['intra_2factor_use'])) {
        //관리자 2차인증 사용여부 (기본값 : 사용안함)
        $cfg['intra_2factor_use'] = "N";
    }

    if (empty($cfg['intra_2factor_email'])) {
        //관리자 2차인증 이메일사용 (기본값 : 사용안함)
        $cfg['intra_2factor_email'] = 'N';
    }

    if (empty($cfg['intra_2factor_phone'])) {
        //관리자 2차인증 SMS사용 (기본값 : 사용안함)
        $cfg['intra_2factor_phone'] = 'N';
    }

    // 비밀번호 만료 설정
    $scfg->def('mng_pass_expire', '');
    $_mng_pass_expire = array(
        '' => '사용안함',
        '1' => '1개월',
        '3' => '3개월',
        '6' => '6개월',
        '12' => '1년',
    );

    $scfg->def('use_prevent_dup_admin', 'N');

    if (!file_exists(__ENGINE_DIR__.'/_engine/include/account/setHosting.inc.php') && $_use['direct_login'] == 'Y') {
        define('__STAND_ALONE__', true);
    }

?>
<div class="access_limit_items">
<?php if (defined('__STAND_ALONE__') == true) { ?>
<form name="accessFrm" method="post" action="./index.php"  target="hidden<?=$now?>" onSubmit="return access_check(this)">
<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">관리자 계정 잠금 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">관리자 계정 잠금 설정</caption>
		<colgroup>
			<col style="width:17%">
			<col style="width:83%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" id="staffs_access_limit_y" name="staffs_access_limit" value="Y" onclick="access_disable();" <?=checked($cfg['staffs_access_limit'] ,'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" id="staffs_access_limit_n" name="staffs_access_limit" value="N" onclick="access_disable();" <?=checked($cfg['staffs_access_limit'] ,'N').checked($cfg['staffs_access_limit'],"")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">1차 경고</th>
			<td>
				<span class="box_btn_s"><input type="text" name="access_warning" value="<?=$cfg['access_warning']?>" class="input" size="5"> 회 실패 시</span>
			</td>
		</tr>
		<tr>
			<th scope="row">계정 잠금</th>
			<td>
				<span class="box_btn_s"><input type="text" name="access_lock" value="<?=$cfg['access_lock']?>" class="input" size="5"> 회 실패 시</span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form name="intraFactorF" method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="return factor_check(this)">
    <input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="config_code" value="intra_2factor">
    <div class="box_title">
        <h2 class="title">관리자 2단계 인증</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">관리자 2단계 인증</caption>
        <colgroup>
            <col style="width:17%">
            <col style="width:83%">
        </colgroup>
        <tr>
            <th scope="row" rowspan="2">사용여부</th>
            <td>
                <label class="p_cursor"><input type="radio" id="intra_2factor_use_y" name="intra_2factor_use" value="Y" onClick="factor_disable();" <?=checked($cfg['intra_2factor_use'] ,'Y')?>> 사용함</label>
                <label class="p_cursor"><input type="radio" id="intra_2factor_use_n" name="intra_2factor_use" value="N" onClick="factor_disable();" <?=checked($cfg['intra_2factor_use'] ,'N')?>> 사용안함</label>
            </td>
        </tr>
        <tr>
            <td>
                <label class="p_cursor"><input type="checkbox" id="intra_2factor_type_phone" name="intra_2factor_phone" class="factor_type" value="Y" <?=checked($cfg['intra_2factor_phone'] ,'Y')?>> 휴대폰</label>
                <label class="p_cursor"><input type="checkbox" id="intra_2factor_type_email" name="intra_2factor_email" class="factor_type" value="Y" <?=checked($cfg['intra_2factor_email'] ,'Y')?>> 이메일</label>
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>

<?php if (!file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) { ?>
<form method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="printLoading();">
    <input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="config_code" value="mng_pass_expire">
    <div class="box_title">
        <h2 class="title">관리자 비밀번호 변경 주기 설정</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">관리자 비밀번호 변경 주기 설정</caption>
        <colgroup>
            <col style="width:17%">
            <col style="width:83%">
        </colgroup>
        <tr>
            <th scope="row">변경 주기</th>
            <td>
                <?=selectArray($_mng_pass_expire, 'mng_pass_expire', null, null, $cfg['mng_pass_expire'])?>
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>
<?php } ?>
<?php } ?>

<form method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="printLoading();">
    <input type="hidden" name="body" value="config@config.exe">
    <div class="box_title">
        <h2 class="title">중복 로그인 제한 설정</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">중복 로그인 제한 설정</caption>
        <colgroup>
            <col style="width:17%">
            <col style="width:83%">
        </colgroup>
        <tr>
            <th scope="row">관리자 중복 로그인 제한</th>
            <td>
                <label><input type="radio" name="use_prevent_dup_admin" value="Y" <?=checked($cfg['use_prevent_dup_admin'], 'Y')?>> 사용함</label>
                <label><input type="radio" name="use_prevent_dup_admin" value="N" <?=checked($cfg['use_prevent_dup_admin'], 'N')?>> 사용안함</label>
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>
</div>

<style>
.access_limit_items > form:first-child .box_title {
    margin-top: 0;
}
</style>

<script type="text/javascript">
    let alert_confirm = <?=$mng_count?>; //전역변수로 변경
	$(document).ready(function() {
		access_disable();
        factor_disable();//관리자 2차인증 옵션구성
	});
	function access_check(f) {
		alert_confirm = <?=$mng_count?>;
		var f = document.accessFrm;

		if(parseInt(f.access_warning.value)>=eval(f.access_lock.value)) {
			alert("1차 경고 횟수보다 더 큰 횟수를 입력하세요.");
			return false;
		}
		if(alert_confirm>0 && f.staffs_access_limit.value == "Y") {
			if(confirm("휴대폰/이메일 정보가 없는 사원이 있을 경우 사용이 불가능합니다. \n사원 등록/관리 페이지로 이동하시겠습니까?")) {
				window.location.href="./?body=intra@staffs_edt";
				return false;
			}
			return false;
		}
	}
    function factor_check(){
        let f = $("form[name=intraFactorF]");
        if ( $("input[name=intra_2factor_use]:checked", f).val() == "Y" && !$("input.factor_type:checked", f).length ) {
            alert("관리자 2차 인증에 사용할 인증수단을 선택해 주세요.");
            return false;
        }
        if(alert_confirm>0 && $("input[name=intra_2factor_use]:checked", f).val() == "Y") {
            if(confirm("휴대폰/이메일 정보가 없는 사원이 있을 경우 사용이 불가능합니다. \n사원 등록/관리 페이지로 이동하시겠습니까?")) {
                window.location.href="./?body=intra@staffs_edt";
                return false;
            }
            return false;
        }
        return true;
    }
	function access_disable() {
		var f = document.accessFrm;
		if(f.staffs_access_limit.value == "Y") {
			f.access_warning.disabled = false;
			f.access_lock.disabled = false;
			f.access_warning.style.background = '';
			f.access_lock.style.background = '';
		} else {
			f.access_warning.disabled = true;
			f.access_lock.disabled = true;
			f.access_warning.style.background = '#eee';
			f.access_lock.style.background = '#eee';
		}
	}
    function factor_disable() {
        let f = $("form[name=intraFactorF]");
        if ($("input[name=intra_2factor_use]:checked", f).val() == "Y") {
            $("input.factor_type").prop("disabled",false);
        } else {
            $("input.factor_type").prop("disabled",true);
        }
    }
</script>