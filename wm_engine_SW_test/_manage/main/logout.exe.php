<?PHP

	$_SESSION['admin_no'] = '';
	setcookie("autologin_code", "", ($now+31536000), "/");
	setcookie('session_id', '', 0, '/');

?>
<form name="deleteCookieFrm" method="post" action="<?=$root_url?>/main/exec.php">
	<input type="hidden" name="exec_file" value="common/makeCookie.php">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="urlfix" value="Y">
</form>
<script type="text/javascript">
	window.onload=function() {
		document.deleteCookieFrm.submit();
	}
</script>