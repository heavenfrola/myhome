<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	$no = numberOnly($_GET['no']);
	$db = addslashes(trim($_GET['db']));

	if(!$no) msg(__lang_common_error_required__, "/");
	$data = $pdo->assoc("select * from `$mari_set[mari_comment]` where `no`='$no' and `db`='$db'");
	if(!$data[no]) msg(__lang_common_error_nodata__, "back");
	$auth=getDataAuth($data,1);

	$listURL = $_SESSION['bbs_rURL'];

	if(!$listURL) $listURL=$PHP_SELF.$db_que2;
	include $skin_path."comment_del.php";

?>