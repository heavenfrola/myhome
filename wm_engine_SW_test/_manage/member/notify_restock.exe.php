<?PHP

	$exec = $_REQUEST['exec'];
	$check_no = numberOnly($_REQUEST['check_no']);
	$ext = numberOnly($_REQUEST['ext']);

	switch($exec) {
        case "delete":
            $nos = implode(", ", $check_no);
            $sql = "UPDATE $tbl[notify_restock] SET `del_stat`='Y', `update_date`='$now' WHERE `no` IN ($nos)";
            $result = $pdo->query($sql);
            if($result) {
                msg(__lang_notify_restock_manage_delete_success__, "reload", "parent");
            } else {
				msg(__lang_notify_restock_manage_delete_fail__);
			}
            break;
        case "update_stat":
			$nos = implode(", ", $check_no);
			$sql = "UPDATE $tbl[notify_restock] SET `stat`='$ext', `update_date`='$now' WHERE `no` IN ($nos)";
			$result = $pdo->query($sql);
			if($result) {
				msg(__lang_notify_restock_manage_change_success__, "reload", "parent");
			} else {
				msg(__lang_notify_restock_manage_change_fail__);
			}
            break;
        default:
            msg(__lang_notify_restock_invalid_access__);
            break;
    }
?>