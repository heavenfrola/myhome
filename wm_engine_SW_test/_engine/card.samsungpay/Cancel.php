<?php

/**
 * 삼성페이 결제 취소
 */

header("Pragma: No-Cache");
include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir.'/_engine/card.samsungpay/inc/function.php';

$RES_STR = toDecrypt($_POST['RETURNPARAMS']);
$RET_MAP = str2data($RES_STR);

$RET_RETURNCODE = $RET_MAP["RETURNCODE"];
$RET_RETURNMSG = $RET_MAP["RETURNMSG"];

$rm_charset = mb_detect_encoding($RET_RETURNMSG, array('UTF-8', 'EUC-KR', 'CP949'));
$RET_RETURNMSG = iconv($rm_charset, _BASE_CHARSET_, $RET_RETURNMSG);

$pdo->query("
    update {$tbl['card']}
        set res_cd=?, res_msg=?
    where wm_ono=?
    ", array(
        $RET_RETURNCODE, addslashes($RET_RETURNMSG."(사용자 취소)"), $RET_MAP["ORDERID"])
);

$pdo->query("
    update {$tbl['order']}
        set stat='31'
    where ono=? and stat != '2'
    ", array(
        $RET_MAP["ORDERID"])
);

$pdo->query("
    update {$tbl['order_product']}
        set stat='31'
    where ono=? and stat != '2'
    ", array(
        $RET_MAP["ORDERID"])
);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>*** 다날 간편결제 ***</title>
</head>
<body>

<script type="text/javascript">
    var returncode = '<?php echo $RET_RETURNCODE ?>';
    var returnmsg = '<?php echo $RET_RETURNMSG ?>';

    alert("["+returncode+"]"+returnmsg);
    self.close();
</script>

</body>
</html>
