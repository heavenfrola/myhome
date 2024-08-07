<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  조직도 관리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	checkBasic();

	$exec = $_POST['exec'];
	$no = numberOnly($_POST['no']);
	$gname = addslashes($_POST['gname']);
	$ref = numberOnly($_POST['ref']);
	$level = numberOnly($_POST['level']);

	if($exec == "delete"){
		$data = $pdo->assoc("select * from `$tbl[intra_group]` where `no`='$no'");
		if($data[no]){
			$sql="delete from `$tbl[intra_group]` where `no`='$no'";
			$r = $pdo->query($sql);
			if($r){
				if($data[level] == 1){
					$pdo->query("delete from `$tbl[intra_group]` where `level`=2 and `ref`='$no'");
					$pdo->query("update `$tbl[mng]` set `team1`=0, `team2`=0 where `team1`='$no'");
				}else{
					$pdo->query("update `$tbl[mng]` set `team2`=0 where `team2`='$no'");
				}
			}
		}
		msg("", "reload", "parent");
	}else{
		if($no){
			$sql="update `$tbl[intra_group]` set `name`='$gname' where `no`='$no'";
		}else{
			$level=$level ? 2 : 1;
			$ref=$ref ? $ref : 0;
			$sql="insert into `$tbl[intra_group]`(`name`, `ref`, `level`) values('$gname', '$ref', '$level')";
		}
		$pdo->query($sql);
		msg("", "reload", "parent");
	}

?>