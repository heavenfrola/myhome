<?PHP

	printAjaxHeader();

	$data['mode'] = 'vertify_url';
	$data['charset'] = 'utf-8';
	$data['page_type'] = $_GET['page_type'];

	$pdo->query("alter table {$tbl['order']} modify mobile enum('Y','N','A') default 'N' not null");

	if(!trim($_we['wm_key_code'])) msg('사이트의 인증이 확인되지 않았습니다', 'back');

	$wec = new weagleEyeClient($_we, 'push');
	$result = $wec->call('vertifyUser',$data);
	$tmp_result = $result;
	$result = stripslashes(urldecode($result));
	$result = json_decode($result,true);

	$wec_app = new weagleEyeClient($_we, 'push');
	$app = $wec_app->call('appStatus');
	$app = json_decode($app);

    $msg = "매직앱 서비스가 신청되어 있지 않습니다.\\n신청페이지로 이동하시겠습니까?";
    $link = 'http://redirect.wisa.co.kr/magicapp';

    switch($app->status) {
        case 'C' :
        case 'Q' :
            $msg = "매직앱 서비스 신청이 완료되었습니다.\\n자료 등록을 위해 마이페이지로 이동하시겠습니까?";
            $link = 'http://redirect.wisa.co.kr/appmypage';
            break;
        /*
        case 'W' :
            $msg = "제출해주신 자료를 토대로 매직앱을 제작하고 있습니다.\\n문의사항은 위사 1:1 고객센터로 접수해 주세요.";
            $link = '';
            break;
        case 'I' :
            $msg = "매직앱 제작이 완료되어 iOS, AOS 마켓 등록 검수가 진행되고 있습니다.\\n문의사항은 위사 1:1 고객센터로 접수해 주세요.";
            $link = '';
            break;
        */
        case 'N' :
            $msg = "매직앱 업데이트 서비스가 만료되었습니다.\\n연장을 위해 마이페이지로 이동하시겠습니까?";
            $link = 'http://redirect.wisa.co.kr/appmypage';
            break;
        case 'D' :
            $msg = "매직앱 서비스가 신청되어 있지 않습니다.\\n신청페이지로 이동하시겠습니까?";
            break;
        case 'H' :
            $msg = "매직앱 제작 자료가 제출되지 않아 제작진행이 보류되었습니다.\\n매직앱 제작 희망 시 위사 1:1 고객센터로 접수해 주세요.";
            $link = '';
            break;
    }

	if($app->status == 'Y' || $app->status == 'I' || $app->status == 'W'){
		if($app_config=='Y') return;
?>
	<form name="clientfrm" method="post" action="<?=$result['url']?>" style="display:none" target="_parent">
		<input type="hidden" name="pack_name" value="<?=$result['pack_name']?>">
		<input type="hidden" name="account_id" value="<?=$result['account_id']?>">
		<input type="hidden" name="key_code" value="<?=$_we['wm_key_code']?>">
		<input type="hidden" name="api_key" value="<?=$_we['api_key']?>">
		<input type="hidden" name="account_idx" value="<?=$result['account_idx']?>">
		<input type="hidden" name="site_root_url" value="http://<?=$_SERVER['HTTP_HOST']?>">
	</form>

	<script language="JavaScript">
		f=document.clientfrm;
		f.submit();
	</script>
<?} else {?>
<script>
<?php if ($link) { ?>
if(confirm('<?=$msg?>')) {
	parent.location.href = '<?=$link?>';
}
<?php } else { ?>
window.alert('<?=$msg?>');
<?php } ?>
</script>
<?}?>