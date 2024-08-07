<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보 수정전 비밀번호 확인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	memberOnly(1,"");

	$sns_cid = $_SESSION['sns_login']['cid'];
	$sns_join = $pdo->assoc("select * from $tbl[sns_join] where member_no='$member[no]' and cid='$sns_cid'");
	if($sns_join) {
		$_SESSION['pwd_check'] = 1;
		msg('', $root_ur.'/member/edit_step2.php');
	}

	common_header();

?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js"></script>
<?php
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>