<?php
header('Content-Type: text/html; charset=utf-8');
/* ****************************************************
     * 연동에 필요한 Function 및 변수값 설정
     *
     * 연동에 대한 문의사항 있으시면 기술지원팀으로 연락 주십시오.
     * DANAL Commerce Division Technique supporting Team
     * EMail : tech@danal.co.kr
     ******************************************************/

/******************************************************
 *  DN_CREDIT_URL	: 결제 서버 정의 (URL은 연동매뉴얼 참고)
 ******************************************************/
$DN_CREDIT_URL = "https://tx-uns.danalpay.com/unsgw/";

/******************************************************
 *  Set Timeout
 ******************************************************/
$DN_CONNECT_TIMEOUT = 5000;
$DN_TIMEOUT = 30000; //max-time setting.
$DN_RTIME = 30000;

$ERC_NETWORK_ERROR = "-1";
$ERM_NETWORK = "Network Error";

$ID_MERCHANT = $scfg->get('samsungpay_id');  // 실서비스를 위해서는 반드시 교체필요..
$PW_MERCHANT = $scfg->get('samsungpay_pwd'); // 암호화Key. 실서비스를 위해서는 반드시 교체필요.
$IV_MERCHANT = "daa8b33bcaaca5b822474cccd99a68f9";
$CHARSET = "UTF-8";
$TEST_AMOUNT = "100";

function CallCredit($REQ_DATA, $Debug)
{
    global $ID_MERCHANT;
    global $DN_CREDIT_URL, $DN_CONNECT_TIMEOUT, $DN_TIMEOUT;
    global $ERC_NETWORK_ERROR, $ERM_NETWORK;

    $REQ_STR = toEncrypt(data2str($REQ_DATA));
    $REQ_STR = urlencode($REQ_STR);
    $REQ_STR = "CPID=".$ID_MERCHANT."&DATA=".$REQ_STR;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $DN_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, $DN_TIMEOUT);
    curl_setopt($ch, CURLOPT_URL, $DN_CREDIT_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/x-www-form-urlencoded; charset=utf-8"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $REQ_STR);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    //curl_setopt( $ch,CURLOPT_SSLVERSION, 'all' ); //ssl 관련 오류가 발생할 경우 주석을 해제하고 6( TLSv1.2) 또는 1(TLSv1)로 설정


    $RES_STR = curl_exec($ch);
    //echo print_r($RES_STR);
    if (($CURL_VAL = curl_errno($ch)) != 0) {
        $RES_STR = "RETURNCODE=".$ERC_NETWORK_ERROR."&RETURNMSG=".$ERM_NETWORK."(" . $CURL_VAL . ":" . curl_error($ch) . ")";
    }

    if ($Debug) {
        $CURL_MSG = "";
        if (function_exists("curl_strerror")) {
            $CURL_MSG = curl_strerror($CURL_VAL);
        } else if (function_exists("curl_error")) {
            $CURL_MSG = curl_error($ch);
        }

        echo "REQ[" . $REQ_STR . "]<BR>";
        echo "RET[" . $CURL_VAL . ":" . $CURL_MSG . "]<BR>";
        echo "RES[" . urldecode($RES_STR) . "]<BR>";
        echo "<BR>" . print_r(curl_getinfo($ch));
        exit();
    }

    curl_close($ch);

    $RES_DATA = str2data($RES_STR);

    if (isset($RES_DATA["DATA"])) {
        $RES_DATA = str2data(toDecrypt($RES_DATA["DATA"]));
    }
//    echo print_r($RES_DATA);
    return $RES_DATA;
}

/*******************************************************
 * curl_init() 사용이 불가능할 때, 바이너리를 컴파일하여 실행
 *******************************************************/
function CallCreditExec($REQ_DATA, $Debug)
{

    $CP_CURL_PATH = "/usr/bin/curl ";

    global $CPID;
    global $DN_CREDIT_URL, $DN_CONNECT_TIMEOUT, $DN_TIMEOUT;
    global $ERC_NETWORK_ERROR, $ERM_NETWORK;

    $REQ_STR = toEncrypt(data2str($REQ_DATA));
    $REQ_STR = urlencode($REQ_STR);
    $REQ_STR = "CPID=".$ID_MERCHANT."&DATA=".$REQ_STR;

    $REQ_CMD = $CP_CURL_PATH;
    $REQ_CMD = $REQ_CMD . ' -k --connect-timeout ' . $DN_CONNECT_TIMEOUT;
    $REQ_CMD = $REQ_CMD . ' --max-time ' . $DN_TIMEOUT;
    $REQ_CMD = $REQ_CMD . ' --data ' . "\"" . $REQ_STR . "\"";
    $REQ_CMD = $REQ_CMD . ' '. "\"" . $DN_CREDIT_URL . "\"";

    exec($REQ_CMD, $RES_STR, $CURL_VAL);

    if ($Debug) {
        echo "Request : " . $REQ_CMD . "<BR>\n";
        echo "Ret : " . $CURL_VAL . "<BR>\n";
        echo "Out : " . $RES_STR[0] . "<BR>\n";
    }

    $RES_DATA = null;
    if ($CURL_VAL != 0) {
        $RES_STR = "RETURNCODE=" . $ERC_NETWORK_ERROR ."&RETURNMSG=" . $ERM_NETWORK ."( " . $CURL_VAL . " )";
        $RES_DATA = str2data($RES_STR);
    } else {
        $RES_DATA = str2data($RES_STR);
        $RES_DATA = str2data(toDecrypt($RES_DATA["DATA"]));
    }

    return $RES_DATA;
}

function str2data($str)
{
    $data = array(); //return variable
    $in = "";

    if ((string)$str == "Array") {
        for ($i = 0; $i < count($str); $i++) {
            $in .= $str[$i];
        }
    } else {
        $in = $str;
    }

    $pairs = explode("&", $in);

    foreach ($pairs as $line) {
        $parsed = explode("=", $line, 2);

        if (count($parsed) == 2) {
            $data[$parsed[0]] = urldecode($parsed[1]);
        }
    }

    return $data;
}

function data2str($data)
{

    $pairs = array();
    foreach ($data as $key => $value) {
        array_push($pairs, $key . '=' . urlencode($value));
    }

    return implode('&', $pairs);
}


function toEncrypt($plaintext)
{
    global $CPID, $PW_MERCHANT, $IV_MERCHANT;

    $iv = convertHexToBin($IV_MERCHANT);
    $key = convertHexToBin($PW_MERCHANT);
    $ciphertext = openssl_encrypt($plaintext, "aes-256-cbc", $key, true, $iv);
    $ciphertext = base64_encode($ciphertext);

    return $ciphertext;
}

function toDecrypt($ciphertext)
{
    global $CPID, $PW_MERCHANT, $IV_MERCHANT;

    $iv = convertHexToBin($IV_MERCHANT);
    $key = convertHexToBin($PW_MERCHANT);
    $ciphertext = base64_decode($ciphertext);
    $plaintext = openssl_decrypt($ciphertext, "aes-256-cbc", $key, true, $iv);

    return $plaintext;
}

function convertHexToBin($str)
{
    if (function_exists('hex2bin')) {
        return hex2bin($str);
    }

    $sbin = "";
    $len = strlen($str);
    for ($i = 0; $i < $len; $i += 2) {
        $sbin .= pack("H*", substr($str, $i, 2));
    }

    return $sbin;
}

/*
 * 리턴 값 문자열 확인 후 인코딩
 *
 * @param string $s 변경할 문자열
 * @param string $toEncoding 바꿀 인코딩 (기본 : _BASE_CHARSET_)
 * 
 * @return 인코딩 후 리턴
 */
function strToEncoding($s, $toEncoding = _BASE_CHARSET_) {
    $nowEncoding = mb_detect_encoding($s, array('UTF-8', 'EUC-KR', 'CP949'));
    $s = iconv($nowEncoding, $toEncoding, $s);

    return $s;
}