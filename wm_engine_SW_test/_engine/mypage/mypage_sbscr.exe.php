<?PHP

    /* +----------------------------------------------------------------------------------------------+
    ' | 정기배송 처리
    ' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Naver\NaverSimplePay;

    include_once $engine_dir."/_engine/include/common.lib.php";
    include_once __ENGINE_DIR__.'/_plugin/subScription/sbscr.lib.php';

    $sbono = $_POST['sbono'];
    $last = numberOnly($_POST['last']);
    $type = addslashes($_POST['type']);

    if (isset($_SESSION['my_order']) == false || strcmp($_SESSION['my_order'], $sbono) !== 0) {
        memberOnly(1,"");
    }

    switch($type) {
        case "cancel":
			if(!$last) $last = 1;
			$pdo->query("update $tbl[sbscr_schedule_product] set `stat`=13 where sbono='$sbono' and stat=1");
			$pdo->query("update $tbl[sbscr_product] set `stat`=13 where sbono='$sbono'");
			$pdo->query("update $tbl[sbscr] set `stat`=13 where sbono='$sbono'");

            $stat = $pdo->row("select pay_type from {$tbl['sbscr']} where sbono=?", array($sbono));
            if ($stat == '2') exit('OK'); // 무통장 주문은 즉시 취소

            // 네이버페이 정기결제 해지
            $sbkey = $pdo->assoc("select * from {$tbl['subscription_key']} where ono='$sbono'");
            if ($sbkey && $sbkey['pg'] == 'nsp') {
                $pay = new NaverSimplePay($scfg);
                $ret = $pay->recurrentExpire($sbkey['recurrentId'], '1');
            } else {
                expireBillKey($sbono);
            }

			echo "OK";
            break;
        case "edit":
            $order_type = $_POST['order_type'];
            $no_fd = ($order_type == 'order') ? 'ono' : 'sbono';

            if ($order_type == 'order') {
                $stat = $pdo->row("select stat from {$tbl[$order_type]} where $no_fd=?", array(
                    $sbono
                ));
                if ($stat > 2) {
                    msg('처리 불가능한 주문상태입니다.');
                }
            }

			$addressee_name = addslashes($_POST['addressee_name']);
			$addressee_phone = addslashes($_POST['addressee_phone']);
			$addressee_cell = addslashes($_POST['addressee_cell']);
			$addressee_zip = addslashes($_POST['addressee_zip']);
			$addressee_addr1 = addslashes($_POST['addressee_addr1']);
			$addressee_addr2 = addslashes($_POST['addressee_addr2']);
            $sql = "update `$tbl[$order_type]` set `addressee_name`='$addressee_name', `addressee_phone`='$addressee_phone',`addressee_cell`='$addressee_cell',`addressee_zip`='$addressee_zip',`addressee_addr1`='$addressee_addr1',`addressee_addr2`='$addressee_addr2' where `$no_fd`='$sbono'";
            $result = $pdo->query($sql);
			if($result) msg("배송지 변경이 완료되었습니다.", "popup");
            break;
    }

?>