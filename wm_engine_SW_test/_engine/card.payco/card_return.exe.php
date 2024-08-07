<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이코 결제 후 완료 페이지 리다이렉트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if($_GET['code'] == '0' && $_GET['ono']) {
		$_SESSION['last_order'] = trim(addslashes($_GET['ono']));

		if($_GET['orderChannel'] == 'MOBILE') msg('', '/shop/order_finish.php');
		else {
			javac("opener.parent.location.href='/shop/order_finish.php'");
			javac("self.close();");
		}
	} else {
		if($_GET['orderChannel'] == 'MOBILE') msg('', '/shop/order.php');
		else {
			javac("opener.parent.layTgl3('order1', 'Y');opener.parent.layTgl3('order2', 'N');opener.parent.layTgl3('order3', 'Y');");
			msg($_GET['message'], 'close');
		}
	}

?>