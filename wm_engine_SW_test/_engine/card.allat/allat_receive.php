<?PHP

	$result_cd = $_POST['allat_result_cd'];
	$result_msg = $_POST['allat_result_msg'];
	$enc_data = $_POST['allat_enc_data'];

?>
<script>
	if(window.opener != undefined) {
		opener.result_submit('<?=$result_cd?>','<?=$result_msg?>','<?=$enc_data?>');
		self.close();
	} else {
		parent.result_submit('<?=$result_cd?>','<?=$result_msg?>','<?=$enc_data?>');
	}
</script>