<?PHP

	printAjaxHeader();

	$no = numberOnly($_REQUEST['no']);
	$level = numberOnly($_REQUEST['level']);
	$parent = $_cate_colname[1][$level-1];
	if($_REQUEST['parent']) {
		$parent = addslashes($_REQUEST['parent']);
	}

	$res = $pdo->iterator("select `no`,`name` from `$tbl[category]` where `$parent`='$no' and `level`='$level' order by sort asc");
    foreach ($res as $data) {
		$line[] = $data['no']."◀▶".stripslashes($data['name']);
	}

	if(is_array($line)) echo implode ("◁▷",$line);

?>