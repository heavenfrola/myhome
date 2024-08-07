<?PHP

/* +----------------------------------------------------------------------------------------------+
' | 관리자 2단계 인증 (독립/다이렉트)
' +----------------------------------------------------------------------------------------------+*/

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

$admin_no = numberOnly($_SESSION['access_admin_no']);
$mng_data = $pdo->assoc("select email, cell from `$tbl[mng]` where no='$admin_no'");

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/intra_factor.js"></script>
<style type="text/css" title="">
    body {background-color:#303742;}
</style>
<div class="admin_login">
    <h1><a href="http://www.wisa.co.kr" target="_blank"><img src="<?=$engine_url?>/_manage/image/intra/logo.png" alt="WISA."></a></h1>
    <div class="box factor">
        <h2 class="factor">관리자 2단계 인증</h2>
        <p class="msg">관리자 로그인 보안강화로 인하여 <span>2단계 인증</span>을 실시합니다.<br>등록되어 있는 휴대폰번호 또는 이메일 인증 후 로그인이 가능합니다.</p>
        <form name="unlockFrm" method="post" target="hidden<?=$now?>" action="./index.php" onSubmit="return unlockchk(this);">
            <input type="hidden" name="body" value="intra@access_limit.exe">
            <input type="hidden" name="exec" value="">
            <input type="hidden" name="cert_type" value="factor2">
            <div class="select">
                <?php if ($cfg['intra_2factor_phone']==='Y') {?>
                <label><input type="radio" id="find_cell" name="find_type" value="1" onclick="typechk(1)"> 휴대폰 번호로 인증</label>
                <?php } ?>
                <?php if ($cfg['intra_2factor_email']==='Y') {?>
                <label><input type="radio" id="find_email" name="find_type" value="2" onclick="typechk(2)"> 이메일로 인증</label>
                <?php } ?>
            </div>
            <div>
                <input type="text" id="text_cell" name="cell" value="<?=$mng_data['cell']?>" class="form_input_access disabled block" placeholder="휴대폰 (-없이 입력)" readOnly><span id="counter" style="display:none"></span>
                <input type="text" id="text_email" name="email" value="<?=$mng_data['email']?>" class="form_input_access disabled block" placeholder="이메일" readOnly>
            </div>
            <div><input type="button" id="btn_confirm" name="btn_confirm" value="인증번호 받기" onclick="confirmSend(document.unlockFrm);"  class="btn white"></div>
            <div><input type="text" id="reg_code" name="reg_code" class="form_input_access block" placeholder="인증번호 입력"></div>
            <div><input type="submit" value="확인" class="btn"></div>
        </form>
    </div>
</div>