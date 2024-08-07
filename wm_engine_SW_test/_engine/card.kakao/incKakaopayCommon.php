<?php
/* *
 * 아래 3개의 확장모듈을 활성화 하시기 바랍니다.
 * 사용 기능   확장모듈
 * ------------  ---------------------------------
 * - 암/복호화 : mcrypt (php 5.3 이하 버전)
 * - 서버 통신 : curl
 * - 인코딩    : mbstring
 */


//인증,결제 및 웹 경로
$CNSPAY_WEB_SERVER_URL = "https://kmpay.lgcns.com:8443";
$targetUrl = "https://kmpay.lgcns.com:8443";
$msgName = "/merchant/requestDealApprove.dev";
$CnsPayDealRequestUrl = "https://pg.cnspay.co.kr:443";

//TXN_ID 호출전용 키값
$MID = $cfg['kakao_id'];
$merchantEncKey = $cfg['kakao_enc_key'];
$merchantHashKey = $cfg['kakao_hash_key'];
$cancelPwd = $cfg['kakao_cancel'];

//버전
$phpVersion = "PLP-0.1.1.3";

//가맹점서명키 (꼭 해당 가맹점키로 바꿔주세요)
$merchantKey = $cfg['kaka_key'];

//로그 경로
$LogDir = $root_dir."/_data/KMPay/Log";
?>