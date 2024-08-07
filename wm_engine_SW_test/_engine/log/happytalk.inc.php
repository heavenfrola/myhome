<?PHP

	if($GLOBALS['_this_pop_up']) return;

	$alimtalk_id = urlencode(stripslashes($cfg['alimtalk_id']));
	$happytalk_site_id = stripslashes($cfg['happytalk_site_id']);
	$happytalk_big_code = stripslashes($cfg['happytalk_big_code']);
	$happytalk_mid_code = stripslashes($cfg['happytalk_mid_code']);
	$amember = $GLOBALS['member'];
	$site_uid = stripslashes($amember['member_id']);

	if($cfg['happytalk_button'] == "B" && $cfg['happytalk_img']) {
	    $img = $root_url.$cfg['happytalk_img'];

	} else {
	    $img = "//happytalk.io/assets/main/img/btn-chat-kakao.png";
	}
?>
<!-- happytalk-->
<style type="text/css" title="">
@-webkit-keyframes happytalk_a {from{opacity:0; -webkit-transform:scale(0.8); -ms-transform:scale(0.8); transform:scale(0.8);} to{opacity:1; -webkit-transform:scale(1); -ms-transform:scale(1); transform:scale(1);}}
.happaytalk_c {-webkit-animation:happytalk_a 0.2s cubic-bezier(0.1,0,0.6,1) !important; animation:happytalk_a 0.2s cubic-bezier(0.1,0,0.6,1) !important; -webkit-animation-delay:1.6s !important; animation-delay:1.6s !important; -webkit-animation-fill-mode:backwards !important; animation-fill-mode:backwards !important;}
</style>
<a href="http://api.happytalk.io/api/kakao/chat_open?yid=<?=$alimtalk_id?>&site_id=<?=$happytalk_site_id?>&category_id=<?=$happytalk_big_code?>&division_id=<?=$happytalk_mid_code?>&site_uid=<?=$site_uid?>" style="position:fixed; bottom:25px; right:25px; z-index:10000000;" class="happaytalk_c" target="_blank">
	<img src=<?=$img?> style="width:58px;">
</a>
<!-- End happytalk -->