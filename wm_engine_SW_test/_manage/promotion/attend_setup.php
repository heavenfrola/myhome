<?PHP

	function adjustAttend($no){
		global $tbl;
		$data=get_info($tbl[attend],"no",$no);
		if(!$data[no]) return;

		$today=date("Y-m-d");
		$_fdate=$today;
		if($today > $data[fdate]) $_fdate=$data[fdate];
		$_sdate_c=explode("-",$data[sdate]);
		$_fdate_c=explode("-",$_fdate);
		$_sdate_mk=mktime(0,0,0,$_sdate_c[1],$_sdate_c[2],$_sdate_c[0]);
		$_fdate_mk=mktime(0,0,0,$_fdate_c[1],$_fdate_c[2],$_fdate_c[0]);

		$_term=$_fdate_mk-$_sdate_mk;
		$_term_day=$_term/86400;
		if($today > $data[fdate]) $_term_day++;

		$pdo->query("delete from `$tbl[attend_member]` where `ano`='$no' and `total` < $_term_day");
	}

?>