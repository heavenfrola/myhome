<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  탈퇴요청 완료
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if(!$_SESSION['out_member_no']) {
		msg(__lang_common_error_ilconnect__, 'back');
	}
	common_header();

	if($cfg['ace_counter_gcode']) javac("var CL_jn = 'withdraw';");

	$withdraw_step = "Y";

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>