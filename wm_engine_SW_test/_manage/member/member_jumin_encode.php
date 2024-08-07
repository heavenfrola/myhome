<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주민등록번호 암호화 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg[join_jumin_use] != "N" && $cfg[jumin_encode] != "N"){
		set_time_limit(0);
		flush();
		ob_flush();
		$pdo->query("alter table `$tbl[member]` modify `jumin` varchar(40) not null");

		$sql = $pdo->iterator("select `no`, `jumin` from `$tbl[member]`");
        foreach ($sql as $data) {
			if(!$data[jumin]) continue;
			$jumin=encode_jumin($data[jumin]);
			if($jumin != $data[jumin]) $pdo->query("update `$tbl[member]` set `jumin`='$jumin' where `no`='$data[no]' limit 1");
		}

	}

?>