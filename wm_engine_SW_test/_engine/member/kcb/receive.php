<?php

/**
 * okname 인증 결과 수신
 */

require_once __ENGINE_DIR__ . '/_engine/include/common.lib.php';
require_once __ENGINE_DIR__ . '/_engine/member/kcb/lib.php';

// 변수 세팅
$MDL_TKN = $_GET['mdl_tkn'];
$CP_CD = $scfg->get('kcb_cpcd');
$target = 'PROD';
$license = $root_dir . '/_data/okname/license.dat';
$params = json_encode(array(
    'MDL_TKN' => $MDL_TKN
));

// 인증 요청
$svcName = "IDS_HS_POPUP_RESULT";
$out = NULL;
$ret = okcert3_u($target, $CP_CD, $svcName, $params, $license, $out);

if($ret == 0) {		// 함수 실행 성공일 경우 변수를 결과에서 얻음
    $output = json_decode($out, true);

    $RSLT_CD = $output['RSLT_CD'];
    $RSLT_MSG =  $output['RSLT_MSG'];

    if (isset($output["TX_SEQ_NO"])) $TX_SEQ_NO = $output["TX_SEQ_NO"]; // 필요 시 거래 일련 번호 에 대하여 DB저장 등의 처리
    if (isset($output["RETURN_MSG"]))  $RETURN_MSG  = $output['RETURN_MSG'];

    // 성공
    if($RSLT_CD == 'B000') {
        $RSLT_NAME      = $output['RSLT_NAME'];
        $RSLT_BIRTHDAY  = (DateTime::createFromFormat('Ymd', $output['RSLT_BIRTHDAY']))->format('Y-m-d');
        $RSLT_SEX_CD	= $output['RSLT_SEX_CD'];
        $RSLT_NTV_FRNR_CD = ($output['RSLT_NTV_FRNR_CD'] == 'L') ? 'N' : 'Y';

        $DI				= $output['DI'];
        $CI 			= $output['CI'];
        $CI_UPDATE		= $output['CI_UPDATE'];
        $TEL_COM_CD		= $output['TEL_COM_CD'];
        $TEL_NO			= $output['TEL_NO'];

        // 인증 세션
        $_SESSION['ipin_res'] = array(
            'name' => $RSLT_NAME,
            'birth' => $RSLT_BIRTHDAY,
            'gender' => ($RSLT_SEX_CD == '남') ? '남' : '여',
            'is_foreign' => $RSLT_NTV_FRNR_CD,
            'DI' => $DI,
            'CI' => $CI
        );

        // DB 저장
        if ($member['no']) {
            setMemberCert();
        }

        // redir
        $rurl = $_SESSION['okname_rurl'] ? $_SESSION['okname_rurl'] : $root_url;
        unset($_SESSION['okname_rurl']);

        if ($rurl != 'join') {
            javac("
                opener.location.href = '$rurl'; 
                self.close();
            ");
        }
    }
} else {
    msg('인증에 실패하였습니다.', 'close');
}

?>
<form id='joinAgreeFrm' name="joinAgreeFrm" method="post" action="<?=$root_url?>/member/join_step2.php" style="margin:0px" >
    <input type="hidden" name="agree" value="Y">
    <input type="hidden" name="privacy" value="Y">
    <input type="hidden" name="member_type" value="Y">
</form>
<script type="text/javascript">
    var browser_type = "<?=$_SESSION['browser_type']?>";
    var f = document.getElementById('joinAgreeFrm');
    if (browser_type=='pc') {
        f.target = 'Parent_window';
        window.close('_self');
        f.submit();
    } else {
        //페이지 이동
        f.target = '_self';
        f.submit();
    }
</script>