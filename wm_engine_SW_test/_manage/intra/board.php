<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷게시판
	' +----------------------------------------------------------------------------------------------+*/

	$db=addslashes(trim($_GET['db']));
	$mode = addslashes($_GET['mode']);
	$_bconfig = $pdo->assoc("select * from `$tbl[intra_board_config]` where `db`='$db' limit 1");
	if(!$_bconfig[no]) return;
	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
		if($key!="body") $QueryString3.="&".$key."=".$val;
		if($key!="mode") $QueryString4.="&".$key."=".$val;
		if($key!="body" && $key!="mode") $QueryString5.="&".$key."=".$val;
		$QueryString2.="&".$key."=".$val;
	}

	function authChk($mode){
		global $_bconfig, $admin;
		if($_bconfig["auth_".$mode] >= $admin['level']) return 1;
		else return 0;
	}
	function editAuth($data){
		global $admin;
		if(!$data[no]) return 0;
		if($admin[no] == $data[member_no] || adminAuth()) return 1;
		else return 0;
	}
	function adminAuth(){
		global $admin;
		if($admin[level] == 1 || $admin[level] == 2) return 1;
		else return 0;
	}

	$mode=$mode ? $mode : "list";

	include $engine_dir."/_manage/intra/board_".$mode.".php";

?>