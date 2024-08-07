<?PHP

	$main_intra = $pdo->assoc("select `db`, `no` from `$tbl[intra_board_config]` where auth_list>='$admin[level]' order by `no` limit 1");
	if($main_intra['no'] && $admin['level']==4) {
		msg('', '?body=board@board&db='.$main_intra['db']);
	}else {
		msg('', '?body=config@info');
	}

?>