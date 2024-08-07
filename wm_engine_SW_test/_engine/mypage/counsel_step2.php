<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문정보 수정요청 작성글 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member[no]) {
		$cs_link=$root_url."/mypage/counsel_list.php";
	}
	else {
		$cs_link=$root_url;
	}
	common_header();

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>