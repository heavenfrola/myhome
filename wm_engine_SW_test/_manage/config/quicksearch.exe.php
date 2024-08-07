<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  단축검색등록 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = addslashes($_POST['exec']);
	$menu = addslashes($_POST['menu']);

	if($exec == "delete"){
		$no = numberOnly($_POST['no']);

		$pdo->query("delete from `$tbl[search_preset]` where `menu`='$menu' and `no`='$no'");
		$preset_menu = $menu;
		include_once $engine_dir."/_manage/config/quicksearch.inc.php";
		exit;
	}

	if($menu) {
		if ($menu == 'log' || $menu == 'keyword_log' || $menu == 'memAnalysis') {
			$_POST['string'] = unserialize($_POST['string']);
			$_POST['string']['setterm'] = $_POST['setterm'];
			$_POST['string'] = serialize($_POST['string']);
		}
		$tmp_string = unserialize($_POST['string']);
		$exclude_fields = array('body', 'page', 'execmode', 'hid_frame');
		if($_POST['limitconfig'] != 'Y') {
			$exclude_fields = array_merge($exclude_fields, array('date1', 'date2', 'start_date', 'finish_date', 'all_date', 'search_date_type'));
		}
		foreach($tmp_string as $key => $val) {
			if($val=='' || in_array($key, $exclude_fields) == true) {
				unset($tmp_string[$key]);
			}
		}
		$content = str_replace('"', "", $_POST['content']);
		$querystring = json_encode($tmp_string);
		$sort_no = $pdo->row("select max(no) from $tbl[search_preset]")+1;
		$pdo->query("INSERT INTO `$tbl[search_preset]` (`menu`, `querystring`, `title`, `content`, `admin_no`, `reg_date`, `sort`) VALUES ('$menu', '".addslashes($querystring)."','".addslashes($_POST['title'])."','".addslashes($content)."','".numberOnly($admin['no'])."','".$now."','".$sort_no."')");

		$preset_menu = $menu;
		include_once $engine_dir."/_manage/config/quicksearch.inc.php";
		exit;
	}
?>