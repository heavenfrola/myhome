<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  아이디 중복체크
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	$sns_type  = trim($_REQUEST['sns_type']);
	$member_id = trim($_REQUEST['member_id']);
	$exec = numberOnly($_REQUEST['exec']);

	// 회원가입 체크
	$sql = "SELECT COUNT(*) as cnt, reg_email FROM ".$tbl['member']." AS A  WHERE A.member_id='".$member_id."'";
	$sdata = $pdo->assoc($sql);

	$member_id_checked=0;
	if (!$sdata['cnt']) {
		if($exec==1) {
			checkBlank($member_id, __lang_member_input_memberid__);
			$check=checkID($member_id);
			if($check==1) {
				$msg = __lang_member_info_memberid1__;
			} elseif ($check == 4) {
				$msg = __lang_member_info_memberid2__;
			} elseif($check>1) {
				$msg = __lang_member_error_existsid__;
			} else {
				$msg = __lang_member_info_usableid__;
				$member_id_checked=1;
			}
		}
	} else {
		if(isset($_sns_type[$sns_type])) {
			$sql = "SELECT COUNT(*) FROM ".$tbl['sns_join']." AS A INNER JOIN  ".$tbl['member']." AS B ON (A.member_no=B.no)  WHERE B.member_id='".$member_id."' AND A.type='" . $_sns_type[$sns_type] ."'";
			$snsCnt = $pdo->row($sql);

			if($snsCnt) {
				$msg = __lang_member_error_snsIdJoin__;
			} else {
				$msg = __lang_member_sns_error_existsid__;
				$member_id_checked=2;
			}
			if ($sdata['reg_email'] == 'W' && $cfg['member_confirm_email'] == 'Y') {
				$msg = __lang_member_error_notValidMember__;
			    $member_id_checked = 1;
			}
		} else {
			$msg = __lang_member_error_snsAllow__;
		}
	}

	$msg=$member_id." : ".$msg;
	close();

?>
<script type='text/javascript'>
	var member_id_chk = '<?=$member_id_checked?>';
	var intergrated_filedset_obj = parent.document.getElementById('intergrated_filedset');

	//1:SNS일반회원가입, 2:SNS통합회원가입
	if(member_id_chk == "1") {
		intergrated_filedset_obj.style.display = "";
		window.alert('<?=$msg?>');
	} else if(member_id_chk == "2") {
		if(confirm('<?=$msg?>')) {
			if(parent.joinFrm.name && !parent.joinFrm.name.value) parent.joinFrm.name.value='Name';
			if(parent.joinFrm.birth1 && !parent.joinFrm.birth1.value) parent.joinFrm.birth1.value='2000';
			if(parent.joinFrm.birth2 && !parent.joinFrm.birth2.value) parent.joinFrm.birth2.value='01';
			if(parent.joinFrm.birth3 && !parent.joinFrm.birth3.value) parent.joinFrm.birth3.value='01';
			intergrated_filedset_obj.style.display = "none";
			parent.$('.sns_pwd').show();
		} else {
			intergrated_filedset_obj.style.display = "";
			parent.$('.sns_pwd').hide();
			member_id_chk = 1;
		}
	} else {
		window.alert('<?=$msg?>');
	}

	parent.joinFrm.member_id_checked.value=member_id_chk;
	location.href='about:blank';
</script>