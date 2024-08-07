<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입/수정폼 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	$agree = $_POST['agree'];
	$privacy = $_POST['privacy'];

	if($skin_preview_name) {
		$agree = 'Y';
		$privacy = 'Y';
	}

	if($member['level']==10) {
		checkBasic(2);
		if(($cfg['ipin_use'] == 'Y' || $cfg['ipin_checkplus_use'] == 'Y' || $scfg->comp('use_kcb', 'Y')) && !$_SESSION['ipin_res']['name']) {
			msg(__lang_member_error_ipin__, $root_url.'/member/join_step1.php');
		}

		if(!$agree) msg(__lang_member_confirm_agree1__, $root_url."/member/join_step1.php");

		$new=1;
		$mailing_check=$sms_check="checked";

		$member_type = numberOnly($_POST['member_type']);
		if(!$member_type) $member_type = 1;
		$mtype=($member_type==1) ? 1 : 2;
	}
	else {
		if($_SESSION['pwd_check']!=1) msg(__lang_member_info_pwdCheck__, $root_url."/member/edit_step1.php");
		$new="";
		$mailing_check=checked($member['mailing'],"Y");
		$sms_check=checked($member['sms'],"Y");
		$member['jumin']=cutStr($member['jumin'],7,"*******");
		$member_birth=explode("-",$member['birth']);
		$member['birth1']=$member_birth[0];
		$member['birth2']=$member_birth[1];
		$member['birth3']=$member_birth[2];
		$birth_modify_use = trim($member['birth']);

		$_phone=explode("-",$member['phone']);
		$_cell=explode("-",$member['cell']);
		$_email=explode("@",$member['email']);
		$member['addr2']=inputText($member['addr2']);

		$mtype=($member['level']>4) ? 1 : 2;
	}

	if($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y') {
		if($_POST['reg_data']) {
			$reg_data = numberOnly($_POST['reg_data']);
			$rdata = $pdo->assoc("select * from $tbl[join_sms_new] where no='$reg_data'");
			if($rdata['type'] == 1) {
				$_cell = explode(preg_replace('/^([0-9]{3})([0-9]+)([0-9]{4})$/', '$1-$2-$3', $rdata['phone']));
				$_readonly_cell = true;
			} elseif($rdata['type'] == 2) {
				$_email = explode('@', $rdata['phone']);
				$_readonly_email = true;
			}
		}
	}

	$total_add_info=0;
	$_mbr_add_info=array();
	$add_info_file=$root_dir."/_config/member.php";
	if(is_file($add_info_file)) {
		include_once $add_info_file;
		$total_add_info=count($_mbr_add_info);
	}

	$JUMINuse=0; $BIRTHuse=0; $SEXuse=0;
	if($cfg['join_jumin_use'] == "Y"){
		$JUMINuse=1;
	}else{
		if($cfg['join_birth_use'] == "Y"){
			$BIRTHuse=1;
			for($ii=date('Y'); $ii>=1900; $ii--){
				$birth1_arr[]=$ii;
			}
			for($ii=1; $ii<=12; $ii++){
				if($ii<10) $ii="0".$ii;
				$birth2_arr[]=$ii;
			}
			for($ii=1; $ii<=31; $ii++){
				if($ii<10) $ii="0".$ii;
				$birth3_arr[]=$ii;
			}
			$birth1_select=selectArray($birth1_arr,'birth1',1,'Year',$member['birth1']);
			$birth2_select=selectArray($birth2_arr,'birth2',1,'Month',$member['birth2']);
			$birth3_select=selectArray($birth3_arr,'birth3',1,'Day',$member['birth3']);
			$birth_type_ck1=" checked";
			if($member['birth_type'] == '음'){ $birth_type_ck1=''; $birth_type_ck2=" checked"; }
		}
		if($cfg['join_sex_use'] == "Y"){
			$SEXuse=1;
			if($member['sex']){
				$sex_ck1=($member['sex'] == "남") ? " checked" : "";
				$sex_ck2=($member['sex'] == "여") ? " checked" : "";
			}
		}
		if($cfg['use_whole_mem'] == "Y"){
			$whole_n = "checked";
			if($member['whole_mem']){
				if($member['whole_mem'] == 'Y'){ $whole_n=''; $whole_y=" checked"; }
			}
		}
	}

	if(isset($cfg['password_min']) == false || empty($cfg['password_min']) == true) $cfg['password_min'] = 4;
	if(isset($cfg['password_max']) == false || empty($cfg['password_max']) == true) $cfg['password_max'] = 0;

	common_header();

	loadPlugIn('join_frm_start');

?>
<script type='text/javascript'>
var use_biz_memebr='<?=$cfg['use_biz_member']?>';
var member_type='<?=$mtype?>';

var nec_member_phone='<?=$_use['nec_member_phone']?>';
var total_add_info=<?=$total_add_info?>;
var skip_add_info=new Array();
var email_org = "<?=$member['email']?>";
var reg_sms = "<?=$member['reg_sms']?>";
var reg_email = "<?=$member['reg_email']?>";
var password_min = <?=$cfg['password_min']?>;
var password_max = <?=$cfg['password_max']?>;
var member_join_addr = '<?=$cfg['member_join_addr']?>';
var member_join_id_email = '<?=$cfg['member_join_id_email']?>';
var browser_type = "<?=$_SESSION['browser_type']?>";
var nickname_essential = '<?=$cfg['nickname_essential']?>';
var member_join_birth = '<?=$cfg['member_join_birth']?>';
var member_join_sex = '<?=$cfg['member_join_sex']?>';
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js?20210428"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/emailAutoComplete.js"></script>
<script type="text/javascript">
window.onload = function() {
	chgEmail(document.joinFrm.email2, document.joinFrm.email3, '<?=$_email[1]?>');
	f=document.joinFrm;

	<?php if($_readonly_cell == true) { ?>
	$('input[name^="cell["]', f).attr('readOnly', true).css('backgroundColor', '#eee');
	<?php } ?>

	<?php if($_readonly_email == true) { ?>
	$('input[name^="email"]', f).attr('readOnly', true).css('backgroundColor', '#eee');
	$('select[name=email3]').hide();
	<?php } ?>

	<?php if ($member['no'] && $member['level'] == 8) { ?>
		$('input[name="dam"]', f).attr('readOnly', true).css('backgroundColor', '#eee');
		$('input[name="owner"]', f).attr('readOnly', true).css('backgroundColor', '#eee');
		$('input[name^="biz_num["]', f).attr('readOnly', true).css('backgroundColor', '#eee');
		$('input[name="biz_type1"]', f).attr('readOnly', true).css('backgroundColor', '#eee');
		$('input[name="biz_type2"]', f).attr('readOnly', true).css('backgroundColor', '#eee');
	<?php } ?>

	<?php if($_SESSION['ipin_res']['name']) { ?>
	f.name.value = '<?=$_SESSION['ipin_res']['name']?>';
	f.name.readOnly = true;
	<?php } ?>
	<?php if($cfg['birth_modify_use'] == "Y" && $birth_modify_use) { ?>
		$('select[name=birth1]', f).attr('disabled', true).css('backgroundColor', '#e0e0e0');
		$('select[name=birth2]', f).attr('disabled', true).css('backgroundColor', '#e0e0e0');
		$('select[name=birth3]', f).attr('disabled', true).css('backgroundColor', '#e0e0e0');
		f.birth_type1.disabled = true;
		f.birth_type2.disabled = true
	<?php } ?>

	//도로명주소를 Default로 설정
	if($('#zip_mode1').length) {
		if(typeof $.prop == 'function') {
			$('#zip_mode1').prop('checked',true);
		} else {
			$('#zip_mode1').attr('checked',true);
		}
	}
	setJoinFormResult();

    <?php if ($member['join_ref'] == 'mng') { ?>
    f.member_id.value = '<?=$member['member_id']?>';
    <?php } ?>
}

$(function() {
	if($('.auto_complete_email').length > 0) {
		attachEmailAutoComplete();
	}
});
</script>
<?PHP
	include_once $engine_dir.'/_engine/common/skin_index.php';
?>