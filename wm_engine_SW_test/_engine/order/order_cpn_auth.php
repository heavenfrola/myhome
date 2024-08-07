<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  오프라인 쿠폰 인증
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$auth_code = addslashes(trim($_GET['auth_code']));
	$cpn=offCouponAuth($auth_code);
	$salew=($cpn[sale_type]=="m") ? __currency__ : "%";

	if(strlen($cpn['weeks']) > 0) {
		$_weeks = array(1 => __lang_common_week_mon__, 2 => __lang_common_week_tue__, 3 => __lang_common_week_wed__, 4 => __lang_common_week_thu__, 5 => __lang_common_week_fri__, 6 => __lang_common_week_sat__, 0 => __lang_common_week_sun__);
		$weeks = explode('@', $cpn['weeks']);

		if(!in_array(date('w'), $weeks)) {
			$cpnerr = sprintf(__lang_cpn_error_week__, $cpn['weeks']);
			foreach($weeks as $val) {
				$cpnerr = str_replace($val, $_weeks[$val], $cpnerr);
			}
			$cpnerr = str_replace('@', ', ', $cpnerr);
			$cpn_no = 0;
			msg($cpnerr);
		}
	}

	if($cpn){
		$cpn_msg = __lang_cpn_info_authok1__." : $cpn[name] $cpn[sale_prc] $salew ".__lang_cpn_info_authok2__;
?>
		<script type='text/javascript'>
			window.alert("<?=__lang_cpn_info_confirmAuth__?>");
			f=parent.document.ordFrm;
			f.off_cpn_sale.value="<?=$cpn[sale_prc]?>";
			f.off_cpn_type.value="<?=$cpn[sale_type]?>";
			f.off_cpn_min.value="<?=$cpn[prc_limit]?>";
			f.off_cpn_limit.value="<?=$cpn[sale_limit]?>";
			f.off_cpn_pay_type.value="<?=$cpn[pay_type]?>";

			if(!f.off_cpn_use_limit) {
				//f.innerHTML += "<input type='hidden' name='off_cpn_use_limit' />";
				parent.$(f).append("<input type='hidden' name='off_cpn_use_limit' />");
			}
			f.off_cpn_use_limit.value='<?=$cpn[use_limit]?>';

			parent.document.all.off_cpn_msg.innerText="<?=addslashes($cpn_msg)?>";
			parent.document.all.off_cpn_div1.style.display="none";
			parent.document.all.off_cpn_div2.style.display="block";
			parent.useOffCpn();
		</script>
<?
		exit;
	}else{
		msg(__lang_cpn_info_confirmAuthF__);
	}

?>