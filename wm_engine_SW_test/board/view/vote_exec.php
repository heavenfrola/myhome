<?PHP

	if($temp<=0 || $temp>5) {
		msg(__lang_common_error_ilconnect__);
	}

	if(!$member[no]) msg(__lang_common_error_memberOnly__);
	if(!$no) msg(__lang_common_error_required__, "/");

	$sql="select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'";
	$data = $pdo->assoc($sql);
	if(!$data[no]) msg(__lang_common_error_nodata__);

	$_vote_members=explode(",",$data[vote_members]);
	if(is_array($_vote_members) && in_array($member[no],$_vote_members)) {
		msg(__lang_board_error_reted__);
	}

	$data[vote_members].=",".$member[no];


	$sql="update  `$mari_set[mari_board]` set `vote_sum`=`vote_sum`+$temp, `vote_cnt`=`vote_cnt`+1, `vote_avg`=`vote_sum`/`vote_cnt`, `vote_members`='$data[vote_members]' where `no`='$no'";
	$pdo->query($sql);

	msg(__lang_board_error_reteOK__, "reload","parent");

?>