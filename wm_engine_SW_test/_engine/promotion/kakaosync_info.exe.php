<?php

/**
 *  카카오싱크 정보조회 API
 **/

$urlfix = 'Y';
include_once $engine_dir.'/_engine/include/common.lib.php';

// 스토어키 설정여부 체크
if (isset($cfg['kakaoSync_StoreKey']) == false) {
    exit(json_encode(array(
        'error' => 'storekey is not set'
    )));
}

// 스토어키 인증
if ($scfg->comp('kakaoSync_StoreKey', $_REQUEST['storeKey']) == false) {
    exit(json_encode(array(
        'error' => 'wrong storekey'
    )));
}

// PHP 5.4 or newer
$json_flag = 0;
if (defined('JSON_PRETTY_PRINT') == true) {
    $json_flag = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ;
}

if (empty($logo_img) == true) {
    $logo_img = '';
}

// 약관
$terms = array(
    array(
        'title' => array('ko' => '이용약관', 'en' => 'Terms of Use'),
        'url' => $root_url.'/content/content.php?cont=uselaw',
        'tag' => 'wisa_agree1',
        'required' => true
    ),
    array(
        'title' => array('ko' => '개인정보 수집 및 이용 동의', 'en' => 'Personal Data Processing Policy Agree'),
        'url' => $root_url.'/content/content.php?cont=privacy',
        'tag' => 'wisa_agree2',
        'required' => true
    )
);

// 메일링 동의
if ($scfg->comp('kakao_mailing_use', 'Y') == true) {
    $terms[] = array(
        'title' => array('ko' => '광고성 SMS 수신 동의', 'en' => 'Agree to receive advertising SMS.'),
        'url' => '',
        'tag' => 'wisa_sms',
        'required' => false
    );
    $terms[] = array(
        'title' => array('ko' => '광고성 이메일 수신 동의', 'en' => 'Agree to receive advertising e-mail.'),
        'url' => '',
        'tag' => 'wisa_email',
        'required' => false
    );
}

// 14세 이상 가입 약관
if ($cfg['join_14_limit'] == 'B' || $cfg['join_14_limit'] == 'C') {
    $terms[] = array(
        'title' => array('ko' => '만 14세 이상입니다', 'en' => '14 years old and older'),
        'url' => '',
        'tag' => 'user_age_check',
        'required' => true
    );
}

// 수신 항목
$items = array(
    array('type' => 'EMAIL', 'required' => true),
    array('type' => 'PROFILE', 'required' => true),
    array('type' => 'PHONE_NUMBER', 'required' => true),
    array('type' => 'NAME', 'required' => true),
);
if ($cfg['join_birth_use'] == 'Y') {
    $items[] = array('type' => 'BIRTH_YEAR', 'required' => ($cfg['member_join_birth'] == 'Y') ? true : false);
    $items[] = array('type' => 'BIRTH_DAY', 'required' => ($cfg['member_join_birth'] == 'Y') ? true : false);
}
if ($cfg['join_sex_use'] == 'Y') {
    $items[] = array('type' => 'GENDER', 'required' => ($cfg['member_join_sex'] == 'Y') ? true : false);
}
if ($cfg['join_addr_use'] == 'Y') {
    $items[] = array('type' => 'ADDRESS', 'required' => ($cfg['member_join_addr'] == 'Y') ? true : false);
}

exit(json_encode(array(
    'appKey' => $cfg['kakao_rest_api'],
    'business' => array(
        'identificationNumber' => $cfg['company_biz_num'],
        'name' => $cfg['company_name'],
        'representativeName' => $cfg['company_owner'],
        'category' => $cfg['company_biz_type1'],
        'categoryItem' => $cfg['company_biz_type2'],
        'address' => array(
            'zipcode' => $cfg['company_zip'],
            'baseAddress' => $cfg['company_addr1'],
            'detailAddress' => $cfg['company_addr2'],
        )
    ),
    'siteData' => array(
        'name' => $cfg['company_mall_name'],
        'url' => $root_url.'/_data/config/'.$cfg['kakao_site_logo'],
        'image' => $logo_img
    ),
    'sync' => array(
        'items' => $items,
        'privacyPolicyUrl' => $root_url.'/content/content.php?cont=privacy',
        'terms' => $terms,
        'url' => array(
            $root_url,
            $m_root_url
        ),
        'redirectUri' => array(
            $root_url.'/_data/compare/kakao/kakao_login_auth.php',
            $m_root_url.'/_data/compare/kakao/kakao_login_auth.php'
        )
    ),
), $json_flag));

?>