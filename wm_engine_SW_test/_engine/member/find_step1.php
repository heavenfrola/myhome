<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  아이디 암호찾기 프론트페이지
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	if($member['level']!=10) msg("","/","");

	common_header();
	$JUMINuse=0;

	// 아이핀 체크 플러스 사용 시 휴대폰 인증으로 강제 전환
	if ($cfg['ipin_checkplus_use'] == 'Y') {
		$cfg['member_confirm_sms'] = 'Y';
	}

?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js?20220714"></script>
<?php
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>