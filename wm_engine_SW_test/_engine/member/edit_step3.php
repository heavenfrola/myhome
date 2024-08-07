<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보 수정완료
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member[level]==10) msg("","/","");
	//if($_SESSION[pwd_check]!=2) msg(__lang_common_error_ilconnect__, $root_url."/member/join_step1.php");

	common_header();

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>