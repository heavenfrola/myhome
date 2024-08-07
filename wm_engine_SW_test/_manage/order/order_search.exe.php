<?PHP

	$save_fd=array("ostat", "period", "period_all", "seach_date_period", "paytype", "orderby", "search", "sort_fd");
	$content="";
	foreach($save_fd as $key=>$val){
		$_re=$_POST[$val];
		if(is_array($_re)){
			$re="@";
			foreach($_re as $val2){
				$re .= $val2."@";
			}
			$_re=$re;
		}
		$content .= $val.":".$_re."<wisa>";
	}
	$content = addslashes($content);

	$r = $pdo->query("update `$tbl[mng]` set `ordersearch`='$content' where `no`='$admin[no]' limit 1");
	if(!$r) msg("저장이 실패하였습니다");

	msg("설정이 저장되었습니다", "reload", "parent");

?>