<?PHP

	addField($tbl['partner_delivery'], "delivery_prd_free2", "char(1) not null default 'N'");

	$tmp = $pdo->assoc("select * from $tbl[partner_delivery] where partner_no='{$admin['partner_no']}'");

	$cfg['delivery_type'] = $tmp['delivery_type'];
	$cfg['delivery_fee'] = $tmp['delivery_fee'];
	$cfg['dlv_fee2'] = $tmp['dlv_fee2'];
	$cfg['dlv_fee3'] = $tmp['dlv_fee3'];
	$cfg['delivery_base'] = $tmp['delivery_base'];
	$cfg['delivery_free_limit'] = $tmp['delivery_free_limit'];
	$cfg['delivery_free_milage'] = $tmp['delivery_free_milage'];
	$cfg['delivery_prd_free'] = $tmp['delivery_prd_free'];
	$cfg['free_delivery_area'] = $tmp['free_delivery_area'];
	$cfg['adddlv_type'] = $tmp['partner_adddlv_type'];

	$partner_delivery = "Y";

	include $engine_dir.'/_manage/config/delivery.php';

?>