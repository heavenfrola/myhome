<?PHP

	if(!defined("_lib_inc")) exit();

	if(!$no) msg(__lang_common_error_required__, "/");
	if(!$link_no) msg(__lang_common_error_required__, "/");

	$sql="select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'";
	$data=$pdo->assoc($sql);
	if(!$data[no]) msg(__lang_common_error_nodata__, "");

	$link_url=$data["link".$link_no];
	if(!$link_url) msg(__lang_board_error_urlNotExist__);

	$sql="update `$mari_set[mari_board]` set `link_hit$link_no`=`link_hit$link_no`+1 where `no`='$no'";
	$pdo->query($sql);

	if($target) {
		msg("",$link_url,$target);
	}

?>
<script type='text/javascript'>
window.open('<?=$link_url?>');
</script>