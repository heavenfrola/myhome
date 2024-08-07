<?PHP

	$timestamp = $_POST['timestamp'];
	$is_holiday = $_POST['is_holiday'];
	$description = $_POST['description'];
	$admin_id = addslashes($admin['admin_id']);

	foreach($timestamp as $key=> $_timestamp) {
		$_is_holiday = ($is_holiday[$key] == 'Y') ? 'Y' : 'N';
		$_description = addslashes(trim($description[$key]));
		$_datestring = date('Y-m-d', $_timestamp);

		$hno = $pdo->row("select no from $tbl[sbscr_holiday] where timestamp='$_timestamp'");
		if($hno) {
			$pdo->query("update $tbl[sbscr_holiday] set is_holiday='$_is_holiday', description='$_description', admin_id='$admin_id' where no='$hno'");
            if ($pdo->lastRowCount() > 0) {
    			$pdo->query("update $tbl[sbscr_holiday] set mod_date=now() where no='$hno'");
            }
		} else {
			if($_is_holiday == 'N' && !$_description) continue;

			$pdo->query("
				insert into $tbl[sbscr_holiday]
					(is_holiday, timestamp, datestring, description, admin_id, mod_date)
					values
					('$_is_holiday', '$_timestamp', '$_datestring', '$_description', '$admin_id', now())
			");
		}
	}
	msg('', 'reload', 'parent');

?>
