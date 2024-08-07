<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  자동입금확인 서비스 키코드 입력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$key_code = $_REQUEST['key_code'];

	if($cfg['bank_key_code']) exit('OK');

	foreach($_POST as $key => $val) {
		if ($key != "bank_key_code") {
			$_POST[$key] = "";
			unset($_POST[$key]);
		}
	}

	$_POST['bank_key_code'] = $key_code;
	foreach($_POST as $key => $val) {
		echo "$key => $val<br />";
	}
	$no_reload_config = true;
	include $engine_dir."/_manage/config/config.exe.php";

?>