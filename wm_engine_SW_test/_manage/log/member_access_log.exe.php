<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원접속통계 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	if($exec=="delete") {
		$r=0;
		$w="";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$w.=" or `no`='$no'";
		}

		$w=substr($w,3);
		$pdo->query("delete from `$tbl[member_log]` where $w",2);
		msg($total." 건의 회원 접속 로그를 성공적으로 삭제되었습니다","reload","parent");
	}

?>