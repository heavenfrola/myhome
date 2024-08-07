<?PHP

	printAjaxHeader();

	if(!fieldExist($tbl['mng'], "quickmenu")) addField($tbl['mng'], 'quickmenu', 'varchar(255) NOT NULL');
	$exec = $_GET['exec'];


	/* +----------------------------------------------------------------------------------------------+
	' |  퀵메뉴 등록
	' +----------------------------------------------------------------------------------------------+*/
	if($exec == 'add') {
		$menu = explode('@', $admin['quickmenu']);
		if(in_array($_GET['pgcode'], $menu)) exit('이미 퀵메뉴에 등록된 메뉴입니다');
		if(count($menu) >= 10) exit("퀵 메뉴는 10개 이상 등록할수 없습니다.\n사용하지 않을 메뉴를 퀵메뉴에서 삭제하신후 다시 등록해 주세요.");

		$admin['quickmenu'] .= '@'.$_GET['pgcode'];
		$admin['quickmenu'] = preg_replace('/^@/', '', $admin['quickmenu']);
		$pdo->query("update `$tbl[mng]` set `quickmenu` = '$admin[quickmenu]' where `no` = '$admin[no]'");
		exit('등록되었습니다');
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  퀵메뉴 삭제
	' +----------------------------------------------------------------------------------------------+*/
	if($exec == 'del') {
		$menu = explode('@', $admin['quickmenu']);
		$new_menu = '';
		foreach($menu as $val) {
			if($_GET['pgcode'] != $val) $new_menu .= '@'.$val;
		}
		$new_menu = preg_replace('/^@/', '', $new_menu);

		$pdo->query("update `$tbl[mng]` set `quickmenu` = '$new_menu' where `no` = '$admin[no]'");

		msg('', 'reload', 'parent');
	}

?>