<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 CPA 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	$_milage_check_ip = explode('@', trim($cfg['milage_check_ip']));
	$is_milage_check = in_array($_SERVER['REMOTE_ADDR'], $_milage_check_ip);

?>
<script type="text/javascript">
	if(typeof wcs != 'undefined') {
		if(typeof cpa == 'undefined') var cpa = {};
		if(cpa['order']) {
			cpa['cnv'] = wcs.cnv("1", "<?=parsePrice($GLOBALS['ord']['pay_prc'])?>");
		}
		wcs_do(cpa);
	}
</script>