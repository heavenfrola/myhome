<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 모바일 결과값 중계
	' +----------------------------------------------------------------------------------------------+*/

	// 결과값
	$result_cd  = $_POST["allat_result_cd"];
	$result_msg = mb_convert_encoding($_POST["allat_result_msg"], _BASE_CHARSET_, 'euckr');
	$enc_data   = $_POST["allat_enc_data"];

	// 결과값 Return
	exit("<script>parent.approval_submit('".$result_cd."','".$result_msg."','".$enc_data."');</script>");

?>