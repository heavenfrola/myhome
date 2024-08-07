<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입 - 약관출력
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Kakao\KakaoSync;

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member['level']<10) msg("","/","");

	common_header();

	$reUrl = 'join_step2.php';

    // 카카오싱크 자동 로그인
    if (!$member['no'] && $scfg->comp('kakao_login_use', 'S') == true && $scfg->comp('kakao_autologin_use', 'Y') == true) {
		if ($cfg['member_return_page'] == '3' && $cfg['member_return_page_custom']) {
			$rURL=$cfg['member_return_page_custom'];
		} else if ($cfg['member_return_page'] == '1' && $_SERVER['HTTP_REFERER']) {
			$rURL = $_SERVER['HTTP_REFERER'];
		} else {
			$rURL = $root_url;
		}
        $sync = new KakaoSync(
            $cfg['kakaoSync_StoreKey'],
            $cfg['kakao_rest_api']
        );
        $sync->autoLogin($rURL);
    }

?>
<script type='text/javascript'>
var use_biz_memebr='<?=$cfg['use_biz_member']?>';
<?php if($cfg['ipin_use'] == 'Y') { ?>
var use_ipin = true;
<?php } ?>
<?php if($cfg['ipin_checkplus_use'] == 'Y') { ?>
var use_ipin_checkplus = true;
<?php } ?>
<?php if($scfg->comp('use_kcb', 'Y')) { ?>
var use_kcb = true;
<?php } ?>

$(document).ready(function() {
    window.name = "Parent_window";
	$('input[name=cprvd]').eq(0).attr('checked', true);
});
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js?t=20220110"></script>
<form id='joinAgreeFrm' name="joinAgreeFrm" method="post" action="<?=$reUrl?>" style="margin:0px">
<input type="hidden" name="agree" value="Y">
<input type="hidden" name="privacy" value="Y">
<input type="hidden" name="member_type" value="Y">
<?php if($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y'){ ?>
<input type="hidden" name="reg_data" value=''>
<?php } ?>
</form>
<?php
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>