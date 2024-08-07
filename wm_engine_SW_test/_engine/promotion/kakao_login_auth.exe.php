<?php

/**
 * 카카오 로그인 Rest API
 **/

use Wing\HTTP\CurlConnection;
use Wing\API\Kakao\KakaoSync;

$urlfix = 'Y';
include $engine_dir.'/_engine/include/common.lib.php';

$code = $_GET['code'];
if (!$code) {
    if ($_GET['error'] == 'consent_required') { // 신규 가입. 동의 화면 필요
        $sync = new KakaoSync(
            $cfg['kakaoSync_StoreKey'],
            $cfg['kakao_rest_api']
        );
        $sync->autoLogin(null, false);
        return;
    }

    if ($_GET['error'] == 'access_denied') { // 실패 시 메인으로 이동
        msg('', $_SESSION['sns_login']['rURL']);
    }

    $back_url = $_SERVER['HTTP_REFERER'];
    if (!$back_url) $back_url = $root_url;
    header('Location: '.$back_url);
    exit;
}

if ($_GET['state'] != $_SESSION['sns_login_state']) {
    msg('wrong state', 'back');
}

// get access_token
$kakao = new CurlConnection('https://kauth.kakao.com/oauth/token', 'POST', http_build_query(array(
    'grant_type' => 'authorization_code',
    'client_id' => $cfg['kakao_rest_api'],
    'redirect_uri' => $p_root_url.'/_data/compare/kakao/kakao_login_auth.php',
    'code' => $code
)));
$kakao->exec();
$token = json_decode($kakao->getResult(true));
if ($token->msg) {
    msg($token->msg, 'back');
}

// get userinfo
$kakao = new CurlConnection('https://kapi.kakao.com/v2/user/me');
$kakao->setheader(array('Authorization: Bearer '.$token->access_token));
$kakao->exec();
$res = json_decode($kakao->getResult(true));
if ($res->msg) {
    msg($res->msg, 'back');
}

// login
$name = $res->properties->nickname;
if (isset($res->kakao_account->name) == true) {
    $name = $res->kakao_account->name;
}
$name = preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $name);
if (empty($name) == true) {
    $name = $res->kakao_account->email;
}
$snsrurl = $_SESSION["sns_login"]["rURL"];
unset($_SESSION['sns_login']);
$_SESSION["sns_login"]["rURL"] = $snsrurl;
$_SESSION['sns_login']['cid'] = $res->id;
$_SESSION['sns_login']['name'] = $name;
$_SESSION['sns_login']['sns_type'] = 'KA';
$_SESSION['sns_login']['agreed'] = true;

// 전화번호
if ($res->kakao_account->has_phone_number == true) {
    $_SESSION['sns_login']['cell'] = str_replace('+82 ', '0', $res->kakao_account->phone_number);
}

// 이메일
if ($res->kakao_account->has_email == true) {
    $_SESSION['sns_login']['email'] = $res->kakao_account->email;
}

// 생년월일
if ($res->kakao_account->has_birthyear == true) {
    $_SESSION['sns_login']['birth'] = $res->kakao_account->birthyear.'-'.preg_replace('/^([0-9]{2})([0-9]{2})$/', '$1-$2', $res->kakao_account->birthday);
}

// 성별
if ($res->kakao_account->has_gender == true) {
    $_SESSION['sns_login']['gender'] = ($res->kakao_account->gender == 'male') ? '남' : '여';
}

// 배송지 정보
$kakao = new CurlConnection('https://kapi.kakao.com/v1/user/shipping_address');
$kakao->setheader(array('Authorization: Bearer '.$token->access_token));
$kakao->exec();
$res = json_decode($kakao->getResult(true));
if ($res->has_shipping_addresses > 0) {
    $addr = $res->shipping_addresses[0];
    $_SESSION['sns_login']['addr_zip'] = $addr->zone_number;
    $_SESSION['sns_login']['addr_addr1'] = $addr->base_address;
    $_SESSION['sns_login']['addr_addr2'] = $addr->detail_address;
}

// sms 및 e-mail 수신 동의
$kakao = new CurlConnection('https://kapi.kakao.com/v1/user/service/terms', 'GET', 'extra=app_service_terms');
$kakao->setheader(array('Authorization: Bearer '.$token->access_token));
$kakao->exec();
$res = json_decode($kakao->getResult(true));
foreach ($res->allowed_service_terms as $term) {
    if ($term->tag == 'wisa_sms') {
        $_SESSION['sns_login']['term_sms'] = strtotime($term->agreed_at);
    }
    if ($term->tag == 'wisa_email') {
        $_SESSION['sns_login']['term_email'] = strtotime($term->agreed_at);
    }
}

/* 약관 가입동의 일자 확인
$sync = new KakaoSync(
    $cfg['kakaoSync_StoreKey'],
    $cfg['kakao_rest_api']
);
$ret = $sync->getTerms($token->access_token);
*/

header('Location: /member/apijoin.php?rURL='.$_SESSION['sns_login']['rURL']);

?>