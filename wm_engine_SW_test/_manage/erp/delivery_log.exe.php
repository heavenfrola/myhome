<?PHP

	include_once $engine_dir.'/_engine/include/file.lib.php';

	$updir = '_data/erp_log/'.date('Ym/d');
	$upfile = 'erp_delivery_'.$admin['admin_id'].basename($_POST['prefix']).'.txt';

	makeFullDir($updir);
	fwriteTo($updir.'/'.$upfile, date('Y-m-d H:i:s').",$ono ($cnt 개의 상품을 확인 : $codes)\n");

?>