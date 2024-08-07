<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑광고 - 크리테오 처리
	' +----------------------------------------------------------------------------------------------+*/

	$script = "
	<?
		\$urlfix = 'Y';
		chdir('../..');
		\$exec_file = \$_REQUEST['exec_file'] = 'promotion/criteocatalog.php';
		include '../main/exec.php';
	?>
	";

	$script_path = '_data/compare/criteo/';
	makeFullDir($script_path);

	$fp = fopen("$root_dir/$script_path/criteocatalog.php", 'w');
	fwrite($fp, trim($script));
	fclose($fp);
	chmod("$root_dir/$script_path/criteocatalog.php", 0777);

	$wec = new weagleEyeClient($_we, 'Etc');
	$wec->call('setExternalService', array(
		'service_name' => 'criteo',
		'use_yn' => ($_POST['criteo_use'] == '1' ? 'Y' : 'N'),
		'root_url' => $root_url,
		'extradata' => $_POST['criteo_P']
	));

	include $engine_dir.'/_manage/config/config.exe.php';

?>