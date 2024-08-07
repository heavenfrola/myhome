<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  닉네임 중복체크
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	if($member[no]) $modify = " and `no` != '$member[no]'";
	$nick = addslashes($_GET['nick']);

	if(strlen($nick) > 24) $msg = __lang_member_error_nick__;
	if($pdo->row("select * from `$tbl[member]` where `nick` = '$nick' $modify")) $msg = __lang_member_error_existsNick__;
	if(checkNameFilter($nick) == false) msg(__lang_member_error_nick2__);
	if(!$msg) $msg = __lang_member_info_usableNick__;
	else {
		javac("parent.joinFrm.nick.value = ''");
	}

?>
<script type='text/javascript'>
	if(parent.browser_type == 'mobile') {
		window.alert('<?=$msg?>');
		location.href='about:blank';
	} else {
		var pr = parent;
		parent.dialogConfirm(null, '<?=str_replace("\n", "\\n", $msg)?>', {
			Ok: function() {
				pr.dialogConfirmClose();
			}
		});
	}
</script>