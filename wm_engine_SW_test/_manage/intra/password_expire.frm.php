<?php

/* +----------------------------------------------------------------------------------------------+
' | 관리자 비밀번호 재설정
' +----------------------------------------------------------------------------------------------+*/

$mng_pass_expire = $cfg['mng_pass_expire'];
if ($mng_pass_expire % 12 == 0) {
    $mng_pass_expire = ($mng_pass_expire/12).'년';
} else {
    $mng_pass_expire .= '개월';
}

?>
<style type="text/css" title="">
    body {background-color:#303742;}
</style>
<div class="admin_login">
    <h1><a href="http://www.wisa.co.kr" target="_blank"><img src="<?=$engine_url?>/_manage/image/intra/logo.png" alt="WISA."></a></h1>
    <div class="box password">
        <h2 class="password">비밀번호 </h2>
        <p class="msg">
            개인정보의 기술적·관리적 보호조치 기준에 의거하여,<br>
            개인 정보 취급자는 <?=$mng_pass_expire?>에 한 번씩 비밀번호를 변경해야 합니다.
        </p>
        <form name="unlockFrm" method="post" target="hidden<?=$now?>" action="./index.php" onSubmit="return unlockchk(this);">
            <input type="hidden" name="body" value="intra@password_expire.exe">
            <input type="hidden" name="exec" value="">
            <input type="hidden" name="admin_no" value="<?=$admin_no?>">
            <input type="hidden" name="cert_type" value="factor2">
            <div><input type="password" name="password_old" class="form_input_access block" placeholder="현재 비밀번호 입력"></div>
            <div><input type="password" name="password_new" class="form_input_access block" placeholder="신규 비밀번호 입력"></div>
            <div><input type="password" name="password_cfm" class="form_input_access block" placeholder="신규 비밀번호 확인"></div>
            <div><input type="submit" value="확인" class="btn"></div>
        </form>
    </div>
</div>