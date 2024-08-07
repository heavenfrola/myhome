<?PHP

	$sql = "insert into erp_inout (complex_no, inout_kind, qty, reg_user, reg_date, remote_ip, sno, in_price) " .
		   "values ({$complex_no}, 'I', {$in_qty}, '{$admin[admin_id]}', now(), '{$_SERVER[REMOTE_ADDR]}', {$sno}, {$in_price})";

	$pdo->query($sql);
    echo $pdo->lastInsertId();
    exit;

?>