<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원그룹 재정의
	' +----------------------------------------------------------------------------------------------+*/

    set_time_limit(0);
    ini_set('memory_limit', -1);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/milage.lib.php';

	if($_POST['member_auto_move'] != 'Y') {
		$cfg['member_auto_move_down'] = $cfg['member_level_day_down'];
	}

	$pdo->query("SET @member_chg_ref='all';");

	$cnt = memberLevelUp();

	echo $cnt;

?>