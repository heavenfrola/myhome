<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  로그아웃 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

    if(isset($_COOKIE['smartwing_al']) == true) {
        $pdo->query("delete from {$tbl['member_auto_login']} where member_no='{$member['no']}'");
        unset($_COOKIE['smartwing_al']);
    }

	unset($_SESSION['member_no']);

	if(!$url) $url=$root_url;

	msg("",$url,"parent");

?>