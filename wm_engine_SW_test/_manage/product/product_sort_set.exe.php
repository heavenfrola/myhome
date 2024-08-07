<?PHP

	checkBasic();

	if($_POST['exec'] == 'reset') {
		$pdo->query("truncate table $tbl[product_sort]");
		exit;
	}

	$no = numberOnly($_POST['no']);
	$use = $_POST['use'];
	$name = $_POST['name'];
	$sort = numberOnly($_POST['sort']);
	foreach($no as $key=>$val) {
		$_use = ($use[$key] == 'Y') ? 'Y' : 'N';
		$_name = trim(addslashes($name[$key]));
		$_sort = $sort[$key];
		if (!$_name || $_name == '') $_name = constant('__lang_sort_info_'.$key.'__');
		$pdo->query("update $tbl[product_sort] set `use`='$_use', name='$_name', sort='$_sort' where no='$val'");
	}

	include $engine_dir."/_manage/config/config.exe.php";

?>