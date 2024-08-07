<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  smartMD 삽입 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	$sm_domain = preg_replace('/https?:\/\/(www)?/', '', $GLOBALS['root_url']);
	$sm_sid = $cfg['logger_smartMD_sid'];
	$tbl = $GLOBALS['tbl'];

	switch($GLOBALS['_file_name']) {
		case 'member_join_step3.php' :
			$TRK_PI = 'RGR';
		break;
		case 'shop_detail.php' :
			$prd = $GLOBALS['prd'];
			$TRK_PI = 'PDV';
			$TRK_PN = preg_replace('/\n|\r|\"/', '', strip_tags(stripslashes($prd['name'])));
			if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) $_imgurl = $cfg['cdn_url'];
			else $_imgurl = getFileDir($prd['updir']);
			$img = $_imgurl.'/'.$prd['updir'].'/'.$prd['upfile2'];
			$TRK_PN_ID = "<img id='_TRK_PN_ID' src='$img' style='display:none; width:0; height:0;' />\n";
		break;
		case 'shop_order_finish.php' :
			$ord = $GLOBALS['ord'];
			$TRK_PI = 'ODR';
			$res = $pdo->iterator("select name, buy_ea, total_prc from `$tbl[order_product]` where ono='$ord[ono]'");
            foreach ($res as $data) {
				if($TRK_OP) $TRK_OP .= ';';
				if($TRK_OE) $TRK_OE .= ';';
				if($TRK_OA) $TRK_OA .= ';';

				$TRK_OP .= preg_replace('/\n|\r|\"|;/', '', strip_tags(stripslashes($data['name'])));
				$TRK_OE .= $data['buy_ea'];
				$TRK_OA .= $data['total_prc'];
			}
		break;
	}

?>
<!-- LOGGER TRACKING SCRIPT V.40 FOR logger.co.kr / <?=$sm_sid?> : COMBINE TYPE / DO NOT ALTER THIS SCRIPT. -->
<?=$TRK_PN_ID?>
<script type="text/javascript">
	var _TRK_LID="<?=$sm_sid?>";
	var _L_TD="ssl.logger.co.kr";
	var _TRK_CDMN="<?=$sm_domain?>";

	var _TRK_PI='<?=$TRK_PI?>';
	var _TRK_PN="<?=$TRK_PN?>";
	var _TRK_OP="<?=$TRK_OP?>";
	var _TRK_OE="<?=$TRK_OE?>";
	var _TRK_OA="<?=$TRK_OA?>";
</script>
<script type="text/javascript">var _CDN_DOMAIN = location.protocol == "https:" ? "https://fs.bizspring.net" : "http://fs.bizspring.net";document.write(unescape("%3Cscript src='" + _CDN_DOMAIN +"/fs4/bstrk.1a.js' type='text/javascript'%3E%3C/script%3E"));</script> <noscript><img alt="Logger Script" width="1" height="1" src="http://ssl.logger.co.kr/tracker.tsp?u=<?=$sm_sid?>&amp;js=N" /></noscript> <!-- END OF LOGGER TRACKING SCRIPT -->
<?unset($subdata)?>