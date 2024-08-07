<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$admin_no = numberOnly($_GET['admin_no']);
	$skey = addslashes($_GET['skey']);
	$data = $db_session_handler->parse($skey);

	if($admin_no != $data['admin_no']) exit;

	$mng = $pdo->assoc("select * from $tbl[mng] where no='$admin_no'");

	if(!fieldExist($tbl['mng'], 'ssoKey')) {
		addField($tbl['mng'], 'ssoKey', 'varchar(128) not null');
	}

	if(!$mng['ssoKey']) {
		$mng['ssoKey'] = sql_password(time().rand(0,99999).$mng['admin_id']);
		$pdo->query("update $tbl[mng] set ssoKey='{$mng['ssoKey']}' where admin_id='{$mng['admin_id']}'");
	}

	exit(json_encode(array(
		'sess_id' => $skey,
		'admin_no' => $mng['no'],
		'admin_id' => $mng['admin_id'],
		'ssoKey' => $mng['ssoKey']
	)));

?>