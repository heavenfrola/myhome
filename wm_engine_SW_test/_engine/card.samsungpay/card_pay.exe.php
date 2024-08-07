<?php

/**
 * 삼성페이 결제 후 결과 DB처리
 */

header("Pragma: No-Cache");

include $engine_dir."/_engine/include/common.lib.php";
include $engine_dir."/_engine/card.samsungpay/inc/function.php";

$RES_STR = toDecrypt($_POST['RETURNPARAMS']);
$RET_MAP = str2data($RES_STR);

$RET_RETURNCODE = $RET_MAP['RETURNCODE'];
$RET_RETURNMSG = $RET_MAP['RETURNMSG'];
$RET_ISBILL = $RET_MAP['ISBILL'];

$RES_DATA = array();

if (is_null($RET_RETURNCODE) || $RET_RETURNCODE !== '00000') {
    // returnCode가 없거나 또는 그 결과가 성공이 아니라면 실패 처리
    $RES_DATA['RETURNCODE'] = $RET_RETURNCODE;
    $RES_DATA['RETURNMSG'] = $RET_RETURNMSG;
} else {
    //***** 신용카드 인증결과 확인 *****************
    $REQ_DATA = array();

    /**************************************************
     * CP 정보
     **************************************************/
    $REQ_DATA['CPID'] = $ID_MERCHANT; // 필수

    /**************************************************
     * 결제 정보
     **************************************************/
    $REQ_DATA['TID'] = $RET_MAP['TID']; // 필수

    /**************************************************
     * 기본 정보
     **************************************************/
    $REQ_DATA['TXTYPE'] = 'BILL'; // 필수 (고정값. 변경하지 마세요.)
    $REQ_DATA['SERVICETYPE'] = 'ISPAY'; // 필수 (고정값. 변경하지 마세요.)

    $RES_DATA = CallCredit($REQ_DATA, false);
}

$ono = addslashes($RES_DATA['ORDERID']);
if ($RES_DATA['RETURNCODE'] === '00000') {
    $pg_note_url = 'Y';

    $app_time = $RES_DATA['TRANDATE'].'-'.$RES_DATA['TRANTIME'];

    $RES_DATA['CARDNAME'] = strToEncoding($RES_DATA['CARDNAME']);
    $RES_DATA['RETURNMSG'] = strToEncoding($RES_DATA['RETURNMSG']);
    $RES_DATA['USERNAME'] = strToEncoding($RES_DATA['USERNAME']);
    $RES_DATA['USEREMAIL'] = strToEncoding($RES_DATA['USEREMAIL']);
    $RES_DATA['ITEMNAME'] = strToEncoding($RES_DATA['ITEMNAME']);

    $pdo->query("
            update {$tbl['card']}
                set stat=?, card_cd=?, card_name=?, app_time=?, app_no=?, noinf=?, quota=?, res_cd=?, res_msg=?, ordr_idxx=?, tno=?, good_mny=?, good_name=?, buyr_name=?, buyr_mail=?, use_pay_method=?, env_info=?
            where wm_ono=?
            ", array(
                '2', $RES_DATA['CARDCODE'], $RES_DATA['CARDNAME'], $app_time, $RES_DATA['CARDAUTHNO'], $RES_DATA['INTERESTFREE'], $RES_DATA['QUOTA'], $RES_DATA['RETURNCODE'], $RES_DATA['RETURNMSG'], $ono, $RES_DATA['TID'], $RES_DATA['AMOUNT'], $RES_DATA['ITEMNAME'], $RES_DATA['USERNAME'], $RES_DATA['USEREMAIL'], 'samsungpay', $_SERVER['REMOTE_ADDR'], $ono)
    );

    makePGLog($ono, 'samsungpay success', $RES_DATA);
    include_once $engine_dir."/_engine/order/order2.exe.php";

    if ($_SESSION['browser_type'] == 'pc') {
        ?>
        <script type="text/javascript">
            opener.parent.location.replace("<?=$root_url?>/shop/order_finish.php");
            self.close();
        </script>
        <?
    } else {
        header("Location:".$root_url."/shop/order_finish.php");
    }
    exit;

} else {
    $RES_DATA['RETURNMSG'] = strToEncoding($RES_DATA['RETURNMSG']);

    // 실패 로그 업데이트
    if ($ono) {
        $stat = $pdo->row("select stat from {$tbl['order']} where ono='$ono'");
        if ($stat == '11') {
            $pdo->query("
                update {$tbl['card']}
                    set res_cd=?, res_msg=?
                where wm_ono=?
                ", array(
                    $RES_DATA['RETURNCODE'], addslashes($RES_DATA['RETURNMSG']), $ono
                )
            );
            $pdo->query("update {$tbl['order']} set stat='31' where ono='$ono' and stat != '2'");
            $pdo->query("update {$tbl['order_product']} set stat='31' where ono='$ono' and stat != '2'");

            ordStatLogw($ono, 31, 'Y');
            makeOrderLog($ono, "order2.exe.php");
        }
    }

    makePGLog($ono, 'samsungpay failed', $RES_DATA);
    include $engine_dir."/_engine/card.samsungpay/Error.php";
}
?>
