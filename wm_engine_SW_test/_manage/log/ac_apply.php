<?PHP

	if($cfg['ace_counter_gcode']) {
		msg('이미 에이스카운터 신청이 완료되었습니다.', 'back');
	}

	$wec = new weagleEyeClient($_we, 'account');
	$account_id = $wec->call('getAccountId');

	if(!preg_match('/^[a-z][a-z0-9]+$/', $account_id)) {
		echo 'API 연동 오류';
		return;
	}

	$mall_id = 'ws_AC'.$account_id;
	$key = "27bb20ad944932f37bbdfdccf048d57b4232a67f047560c6f3eb1099fe11d2a1";

	$domain_list = preg_replace('@https?://@', '', $root_url);
	$com = "wisamall";

	$send_string = "{
		  \"DATA\":{
		   \"ACE_ID\":\"$mall_id\",
		   \"ACE_COM\":\"$com\",
		   \"ACE_KEY\":\"$key\",
		   \"ACE_DOMAIN\":\"$domain_list\"
		  }
		}";

	$q = base64_encode($send_string);

?>
<form id="acecounterFrm" method="post" action="//wisamall.acecounter.com/register/partner_mInfo.amz">
	<input type="hidden" name="q" value="<?=$q?>">
</form>
<div class="box_full">
	<iframe id="acecounter" name="acecounter" style="width:100%; height:2450px;" frameborder="0"></iframe>
</div>

<script type="text/javascript">
	var f = document.getElementById('acecounterFrm');
	f.target = 'acecounter';
	f.submit();
</script>