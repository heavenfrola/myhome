<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 클릭초이스 구매전환 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	if($_SERVER['SCRIPT_NAME'] == '/member/join_step3.php') $cchoice_env_name = '회원가입';
	if($_SERVER['SCRIPT_NAME'] == '/shop/order_finish.php') {
		$cchoice_env_name = '구매완료';
		$cchoice_env_conv = numberOnly($GLOBALS['ord']['total_prc']);
	}

	if(!$cfg['cchoice_env_JV']) $cfg['cchoice_env_JV'] = 'AMZ2008090201';
	if(!$cfg['cchoice_env_TGUL']) $cfg['cchoice_env_TGUL'] = 'cts19.acecounter.com';
	?>

	<!-- Naver Click Choice -->
	<script type='text/javascript'>
	<?if($GLOBALS['NVADKWD'] && $GLOBALS['NVAR']) { // 클릭초이스 랜딩 페이지일 경우?>
	var _RFL  = "<?=$_SERVER['HTTP_REFERER']?>";
	<?}?>
	var _CDM  = "<?=preg_replace('/^https?:\/\//', '', $root_url)?>";
	<?if($cchoice_env_name){?>
	var _CNM  = "<?=$cchoice_env_name?>";
	var _CNV  = "<?=$cchoice_env_conv?>";
	<?}?>
	var _TGCD = "<?=$cfg['cchoice_env_code']?>";
	var _JV = "<?=$cfg['cchoice_env_JV']?>";
	var _TGUL = "<?=$cfg['cchoice_env_TGUL']?>";
	</script>
	<script type="text/javascript" src='<?=$engine_url?>/_engine/log.acecounter/ad_cchoice_AceCounter.js'></script>
	<?unset($cchoice_env_name, $cchoice_env_conv, $cfg['cchoice_env_code']);

?>