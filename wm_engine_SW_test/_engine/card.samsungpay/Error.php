<?php

/**
 * 삼성페이 결제 실패
 */

header("Pragma: No-Cache");
include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir.'/_engine/card.samsungpay/inc/function.php';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>*** 다날 결제 실패 ***</title>
</head>
<body>

<script type="text/javascript">
    var returncode = '<?php echo $RES_DATA['RETURNCODE'] ?>';
    var returnmsg = '<?php echo $RES_DATA['RETURNMSG'] ?>';

    alert("["+returncode+"]"+returnmsg);
    self.close();
</script>

</body>
</html>