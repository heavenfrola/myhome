<?PHP

   if(function_exists("mb_http_input")) mb_http_input('euc-kr');
   if(function_exists("mb_http_output")) mb_http_output('euc-kr');

    $rcid = $_POST['reCommConId'];
    $rctype = $_POST['reCommType'];
    $rhash = $_POST['reHash'];

	$p_protocol = 'http';
	if(strlen($_SERVER['SERVER_PROTOCOL']) > 4 && 'https' == substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) {
		$p_protocol = 'https';
	}

?>
<script type="text/javascript">
    function init()	{
 		if(typeof(top.opener) == "undefined" || typeof(top.opener.eparamSet) == "undefined" || typeof(top.opener.goResult) == "undefined") {
 			alert("ERROR: 주문페이지를 확인할 수 없어 결제를 중단합니다!!");
 			self.close();
 			return;
 		}
		<?if(!empty($rcid) && 10 > strlen($rcid)) {?>
		alert("ERROR: 결제요청정보(<?echo($rcid)?>)를 확인할 수 없어 결제를 중단합니다!!");
		self.close();
		return;
		<?} else {?>
        top.opener.eparamSet("<?echo($rcid)?>", "<?echo($rctype)?>", "<?echo($rhash)?>");
        top.opener.goResult();
		<?}?>
		setTimeout('self.close()', '3000');
    }
    init();
</script>