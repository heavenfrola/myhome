<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원근태통계 처리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	checkBasic();

    $mod = $_POST['mod'];

	if(count($mod)){
		foreach($mod as $key=>$val){
			$pdo->query("update `$tbl[intra_day_check]` set `late`='N', `mod_date`='$now' where `no`='$val'");
		}
	}

	msg("","reload","parent");

?>