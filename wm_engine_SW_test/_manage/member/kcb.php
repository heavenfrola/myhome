<?php

/**
 * KCB 본인 인증 설정
 */

$scfg->def('use_kcb', 'N');

?>
<?php if (!function_exists('okcert3')) { ?>
<div class="msg_topbar warning">
    KCB 암호화 모듈이 설치되어있지 않습니다. 서버 관리자에게 문의하여 설치 후 사용해주시기 바랍니다.
</div><br>
<?php } ?>

<?php if (!$scfg->comp('ssl_type', 'Y')) { ?>
    <div class="msg_topbar warning">
        KCB 본인인증을 사용하기 위해서는 보안서버 설정이 필요합니다.
        <a href="http://smartqa2.mywisa.com/_manage/?body=config@ssl"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
    </div><br>
<?php } ?>

<form method="post" action="?" onsubmit="return kcbSet(this)">
    <div class="box_title first">
        <h2 class="title">KCB 본인인증 설정</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">KCB 본인인증 설정</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tr>
            <th scope="row">서비스 사용</th>
            <td>
                <label class="p_cursor"><input type="radio" name="use_kcb" value="Y" <?=checked($cfg['use_kcb'], 'Y')?>> 사용함</label>
                <label class="p_cursor"><input type="radio" name="use_kcb" value="N" <?=checked($cfg['use_kcb'], 'N')?>> 사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">회원사 코드</th>
            <td><input type="text" name="kcb_cpcd" class="input" value="<?=$scfg->get('kcb_cpcd')?>"></td>
        </tr>
        <tr>
            <th scope="row">라이센스 파일</th>
            <td>
                <input type="file" name="kcb_license" class="input" value="">
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>

<script>
function kcbSet(f) {
    printLoading();
    fetch('./?body=member@kcb.exe', {
        method: 'POST',
        body: new FormData(f)
    })
        .then(ret => ret.json())
        .then(ret => {
            removeLoading();
            if (ret.message) {
                window.alert(ret.message)
            }
        })

    return false;
}
</script>