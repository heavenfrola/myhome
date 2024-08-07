<?php

/**
 * 관리자모드 보안 설정
 **/

if ($admin['level'] != '1' && $admin['level'] != '2') {
    msg('접근 권한이 없습니다.', 'back');
}

$scfg->def('use_member_list_protect', 'N');
$scfg->def('use_order_list_protect', 'N');
$scfg->def('use_oexcel_protect', 'N');
$scfg->def('use_mexcel_protect', 'N');
$scfg->def('use_cexcel_protect', 'N');
$scfg->def('mexcel_otp_method', 'sms');
$scfg->def('oexcel_otp_method', 'sms');
$scfg->def('cexcel_otp_method', 'sms');
$scfg->def('use_account_enc', 'N');

$enc_disabled = ($cfg['use_account_enc'] == 'Y') ? 'disabled' : '';

$zip_disabled = 'disabled';
if (class_exists('ZipArchive') == true) {
    if (method_exists('ZipArchive', 'setEncryptionName') == true) {
        $zip_disabled = '';
    }
}

?>
<form name="accessFrm" method="post" action="./index.php"  target="hidden<?=$now?>" onSubmit="return printLoading()">
    <input type="hidden" name="body" value="intra@scm.exe">

    <div class="box_title first">
        <h2 class="title">개인정보보호 설정</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">개인정보보호 설정</caption>
        <colgroup>
            <col style="width:17%">
            <col style="width:83%">
        </colgroup>
        <tr>
            <td colspan="2"><strong>회원 조회</strong></td>
        </tr>
        <tr>
            <th scope="row">마스킹 사용여부</th>
            <td>
                <li>
                    <label><input type="radio" name="use_member_list_protect" value="Y" <?=checked($cfg['use_member_list_protect'], 'Y')?>> 사용함</label>
                    <label><input type="radio" name="use_member_list_protect" value="N" <?=checked($cfg['use_member_list_protect'], 'N')?>> 사용안함</label>
                </li>
            </td>
        </tr>
        <tr>
            <th scope="row">회원 엑셀 2단계 인증</th>
            <td>
                <li>
                    <label><input type="radio" name="use_mexcel_protect" value="Y" <?=checked($cfg['use_mexcel_protect'], 'Y')?> <?=$zip_disabled?>> 사용함</label>
                    (
                        <label><input type="radio" name="mexcel_otp_method" value="sms" <?=checked($cfg['mexcel_otp_method'], 'sms')?>> SMS</label>
                        <label><input type="radio" name="mexcel_otp_method" value="mail" <?=checked($cfg['mexcel_otp_method'], 'mail')?>> 이메일</label>
                    )
                    <label><input type="radio" name="use_mexcel_protect" value="N" <?=checked($cfg['use_mexcel_protect'], 'N')?>> 사용안함</label>
                </li>
            </td>
        </tr>
        <tr>
            <td colspan="2"><strong>주문 배송</strong></td>
        </tr>
        <tr>
            <th scope="row">마스킹 사용여부</th>
            <td>
                <li>
                    <label><input type="radio" name="use_order_list_protect" value="Y" <?=checked($cfg['use_order_list_protect'], 'Y')?>> 사용함</label>
                    <label><input type="radio" name="use_order_list_protect" value="N" <?=checked($cfg['use_order_list_protect'], 'N')?>> 사용안함</label>
                </li>
            </td>
        </tr>
        <tr>
            <th scope="row">주문 엑셀 2단계 인증</th>
            <td>
                <li>
                    <label><input type="radio" name="use_oexcel_protect" value="Y" <?=checked($cfg['use_oexcel_protect'], 'Y')?> <?=$zip_disabled?>> 사용함</label>
                    (
                        <label><input type="radio" name="oexcel_otp_method" value="sms" <?=checked($cfg['oexcel_otp_method'], 'sms')?>> SMS</label>
                        <label><input type="radio" name="oexcel_otp_method" value="mail" <?=checked($cfg['oexcel_otp_method'], 'mail')?>> 이메일</label>
                    )
                    <label><input type="radio" name="use_oexcel_protect" value="N" <?=checked($cfg['use_oexcel_protect'], 'N')?>> 사용안함</label>
                </li>
            </td>
        </tr>
        <tr>
            <th scope="row">현금영수증 엑셀 2단계 인증</th>
            <td>
                <li>
                    <label><input type="radio" name="use_cexcel_protect" value="Y" <?=checked($cfg['use_cexcel_protect'], 'Y')?> <?=$zip_disabled?>> 사용함</label>
                    (
                        <label><input type="radio" name="cexcel_otp_method" value="sms" <?=checked($cfg['cexcel_otp_method'], 'sms')?>> SMS</label>
                        <label><input type="radio" name="cexcel_otp_method" value="mail" <?=checked($cfg['cexcel_otp_method'], 'mail')?>> 이메일</label>
                    )
                    <label><input type="radio" name="use_cexcel_protect" value="N" <?=checked($cfg['use_cexcel_protect'], 'N')?>> 사용안함</label>
                </li>
            </td>
        </tr>
        <?php if (!$enc_disabled) { ?>
        <tr>
            <th scope="row">계좌번호 암호화</th>
            <td>
                <label><input type="radio" name="use_account_enc" value="Y" <?=checked($cfg['use_account_enc'], 'Y')?>> 사용함</label>
                <label><input type="radio" name="use_account_enc" value="N" <?=checked($cfg['use_account_enc'], 'N')?>> 사용안함</label>
                <ul class="list_info">
                    <li>설정 시 수분 이상 소요될 수 있으며, 쇼핑몰 주문에 지장이 생길 수 있습니다. 유휴시간을 이용하여 설정해주세요.</li>
                </ul>
            </td>
        </tr>
        <?php } ?>
    </table>
    <div class="box_middle2 left">
        <ul class="list_info">
            <?php if ($zip_disabled) { ?>
            <li class="warning">2단계 인증 기능은 PHP 7.2 이상 zipArchive모듈이 설치되어있어야 이용 가능합니다.</li>
            <?php } ?>
            <li>2단계 인증으로 다운로드한 개인정보는 압축을 해제하여 보관하시지 마시고 압축한 상태로 사용하시거나 사용 후 폐기해 주시기 바랍니다.</li>
            <li><a href="?body=intra@admin_confirm" target="_blank">중요설정 2단계 인증</a> 메뉴와 연계하여 다운로드 시 담당자에게 알림이 발송되도록 설정할 수 있습니다.</li>
        </ul>
    </div>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>
<script>
var otp_event = null;
(otp_event = function() {
    if ($(':checked[name=use_mexcel_protect]').val() == 'N') {
        $(':radio[name=mexcel_otp_method]').prop('disabled', true);
    } else {
        $(':radio[name=mexcel_otp_method]').prop('disabled', false);
    }

    if ($(':checked[name=use_oexcel_protect]').val() == 'N') {
        $(':radio[name=oexcel_otp_method]').prop('disabled', true);
    } else {
        $(':radio[name=oexcel_otp_method]').prop('disabled', false);
    }

    if ($(':checked[name=use_cexcel_protect]').val() == 'N') {
        $(':radio[name=cexcel_otp_method]').prop('disabled', true);
    } else {
        $(':radio[name=cexcel_otp_method]').prop('disabled', false);
    }
})();

$('[name=use_mexcel_protect], [name=use_oexcel_protect], [name=use_cexcel_protect]').change(otp_event)
</script>