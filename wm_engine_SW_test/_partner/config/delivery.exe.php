<?PHP

	$adddlv_type = $pdo->row("select partner_adddlv_type from {$tbl['partner_delivery']} where partner_no = '{$admin['partner_no']}'");
	$cfg['adddlv_type'] = $adddlv_type;

	include $engine_dir.'/_manage/config/delivery.exe.php';

?>