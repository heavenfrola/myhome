<?PHP

	$no_qcheck = true;
	set_time_limit(0);

	header('Content-type:text/html; charset=utf-8;');

	chdir(dirname(__FILE__));

	$urlfix = 'Y';
	$no_qcheck = true;
	include '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$today = date('Y-m-d');

	$tmp = $pdo->assoc("select no from $tbl[attend_new] where prize_reserve='$today'");
	if(!$tmp['no']) exit('no data');

	$_POST['exec'] = 'give';
	$no = $tmp['no'];

	include $engine_dir.'/_manage/config/attend.exe.php';

?>