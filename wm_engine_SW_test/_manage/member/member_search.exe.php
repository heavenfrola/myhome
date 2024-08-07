<?PHP

	$save_fd=array("milage_up", "milage_limit", "visit_up", "visit_limit", "order_up", "order_limit", "prc_up", "prc_limit", "search");
	$content="";
	foreach($save_fd as $key=>$val){
		$_re=$_POST[$val];
		if(!$_POST[$val]) msg("필수값이 없습니다");
		if(is_array($_re)){
			$re="@";
			foreach($_re as $val2){
				$re .= $val2."@";
			}
			$_re=$re;
		}
		$content .= $val.":".$_re."<wisa>";
	}

	$fname_arr=array(milage_up=>"적립금", visit_up=>"접속횟수", order_up=>"주문횟수", prc_up=>"구매금액");
	for($ii=0; $ii<=6; $ii+=2){
		if($_POST[$save_fd[$ii+1]]/$_POST[$save_fd[$ii]] > 100){
			msg("\\n범위가 너무 넓은 경우 페이지의 실행시간이 길어질 수 있으므로      \\n\\n[".$fname_arr[$save_fd[$ii]]."]의 범위를 재설정해주시기 바랍니다.         ");
		}
	}

	$r=$pdo->query("update `$tbl[mng]` set `membersearch`='$content' where `no`='$admin[no]' limit 1");
	if(!$r) msg("저장이 실패하였습니다");

	msg("설정이 저장되었습니다", "reload", "parent");

?>