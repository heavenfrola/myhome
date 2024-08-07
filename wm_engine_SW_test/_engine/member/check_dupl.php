<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  아이디 중복체크
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	$exec = 1;
	$member_id = addslashes(trim($_POST['member_id']));
	$data = addslashes(trim($_POST['data']));
	if(!$member_id && $data) $member_id = $data;

	if($exec==1) {
		checkBlank($member_id, __lang_member_input_memberid__);
		$check=checkID($member_id);
		if($check==1) {
			$msg = __lang_member_info_memberid1__;
			$member_id_checked=0;
		}
		elseif($check==4) {
			$msg = __lang_member_info_memberid2__;
			$member_id_checked=0;
		}
		elseif($check>1) {
			$msg = __lang_member_error_existsid__;
			$member_id_checked=0;
		}
		else {
			$msg = __lang_member_info_usableid__;
			$member_id_checked=1;
		}
	}

	$msg=$member_id." : ".$msg;

	close();

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
	parent.joinFrm.member_id_checked.value='<?=$member_id_checked?>';
</script>