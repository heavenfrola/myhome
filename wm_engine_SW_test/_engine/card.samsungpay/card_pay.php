<?php

/**
 * 삼성페이 사용자 인증
 */

include_once $engine_dir.'/_engine/card.samsungpay/inc/function.php';

if (!$scfg->get('samsungpay_id') || !$scfg->get('samsungpay_pwd')) msg('삼성페이 결제를 위한 CPID, PWD가 설정되어 있지 않습니다.');

$itemname = cutstr(preg_replace("/(;|&|\"|=|'|\|,|\+|)/", '', strip_tags($title)), 30); // 상품명
$itemname = iconv('UTF-8', 'EUC-KR', $itemname);

$buyer_name = iconv('UTF-8', 'EUC-KR', $buyer_name);

$userid = ($member['no']) ? $member['member_id'] : '비회원'; // 주문자 ID

$useragent = ($_SESSION['browser_type'] == 'pc') ? 'WP' : 'WM'; // 사용자 환경

// 필수 데이터
$samsungpay_rq = array();

// CP 정보
$samsungpay_rq['CPID'] = $scfg->get('samsungpay_id'); // 가맹점

// 결제 정보
$samsungpay_rq['AMOUNT'] = $pay_prc; // 결제금액
$samsungpay_rq['ITEMNAME'] = $itemname; // 상품명
$samsungpay_rq['CURRENCY'] = '410'; // 통화코드(410: KRW) (* 고정값 변경하지 마세요 *)

// 고객 정보
$samsungpay_rq['USERID'] = $userid; // 주문자 ID
$samsungpay_rq['USERAGENT'] = $useragent; // 사용자 환경

// 기본 정보
$samsungpay_rq['TXTYPE'] = 'AUTH'; // (* 고정값 변경하지 마세요 *)
$samsungpay_rq['SERVICETYPE'] = 'ISPAY'; // 삼성페이 통합간편결제 : ISPAY (* 고정값 변경하지 마세요 *)

// 가맹정 URL 정보
$CANCELURL = $root_url.'/main/exec.php?exec_file=card.samsungpay/Cancel.php';
$RETURNURL = $root_url.'/main/exec.php?exec_file=card.samsungpay/card_pay.exe.php';
$samsungpay_rq['CANCELURL'] = $CANCELURL; // CANCEL Full URL. 사용자가 결제취소를 선택했을 때 해당 값들이 GET으로 전달.
$samsungpay_rq['RETURNURL'] = $RETURNURL; // RETURN Full URL. 사용자인증이 완료되었을 때 해당 값들이 GET으로 전달. CPCGI

$samsungpay_rq['ITEMCODE'] = ''; // 휴대폰결제를 위해 다날이 발급한 코드값 (필수)

// 선택사항
$samsungpay_rq['ORDERID'] = $ono; // 가맹점 주문번호
$samsungpay_rq['USERNAME'] = $buyer_name; // 주문자명
$samsungpay_rq['USEREMAIL'] = $buyer_email; // 주문자이메일 (결제확인 메일 발송용)


$samsungpay_rq['SUBCPID'] = ''; // 하위 가맹점
$samsungpay_rq['DEPOSIT_AMT'] = ''; // 일회용 컵 보증금
$samsungpay_rq['TAX'] = ''; // 세금(가맹점특수)
$samsungpay_rq['BYPASSVALUE'] = ''; // 추가필드 값 Field1=abc;field2=def;
$samsungpay_rq['PAYMETHODBASE'] = '02'; // 인증에 사용할 수단 정보 (01 휴대폰만 노출, 02 신용카드만 노출, 01:02 휴대폰/신용카드 노출(디폴트))
$samsungpay_rq['CARDCODEBASE'] = ''; // 가입 시 노출할 카드사 코드
$samsungpay_rq['QUOTABASE'] = ''; // 신용카드 결제 시 노출할 할부 개월 수
$samsungpay_rq['MOBILECARRIERBASE'] = ''; // 가입 시 노출할 이통사 코드
//$samsungpay_rq['NOTIURL'] = ''; // Noti페이지의 Full URL을 넣어주세요 (성공 시 결제 완료에 대한 작업)

cardDataInsert($tbl['card'], 'samsungpay');

$samsungpay_rs = CallCredit($samsungpay_rq, false);

if ($samsungpay_rs['RETURNCODE'] === '00000') {
    makePGLog($ono, 'samsungpay auth success', $samsungpay_rs);
?>

<script type="text/javascript">
    let sspay = parent.samsungpayFrm;

    sspay.TID.value = '<?=$samsungpay_rs['TID']?>';
    sspay.STARTPARAMS.value = '<?=$samsungpay_rs['STARTPARAMS']?>';

    parent.samsungpayAuth('<?=$samsungpay_rs['STARTURL']?>', '<?=$_SESSION['browser_type']?>');
</script>

<?php
} else {
    $samsungpay_rs["RETURNMSG"] = strToEncoding($samsungpay_rs["RETURNMSG"]);
    msg('CP 인증 실패 : '.$samsungpay_rs["RETURNCODE"]."\\n".$samsungpay_rs["RETURNMSG"]);
}