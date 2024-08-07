<?php

    /* +----------------------------------------------------------------------------------------------+
    ' | 재입고 알림 신청 내역 처리
    ' +----------------------------------------------------------------------------------------------+*/

    include_once $engine_dir."/_engine/include/common.lib.php";

    memberOnly(1,"");

    $exec = $_REQUEST['exec'];
    $no = numberOnly($_REQUEST['no']);
    switch($exec) {
    	// 신청취소
        case "ajax_cancel":
            $sql = "UPDATE `$tbl[notify_restock]` SET `stat`='3', update_date='$now' WHERE `no`='$no' AND `member_no`='$member[no]'";
            $result = $pdo->query($sql);
            $return['stat'] = ($result) ? 1 : 0;
            $return['msg'] = ($result) ? "" : "DB ERROR";
            echo json_encode($return);
            break;
        default:
			$return['stat'] = 0;
			$return['msg'] = __lang_notify_restock_invalid_access__;
			echo json_encode($return);
            break;
    }


?>