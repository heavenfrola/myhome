<?PHP

	if(function_exists('getSkinCfg')) {
		$_skin = getSkinCfg();
		$jquery_ver = ($_skin['jquery_ver']) ? $_skin['jquery_ver'] : 'jquery-1.4.min.js';
	}
	$jquery_ui_ver = str_replace('jquery-', 'jquery-ui-', $_skin['jquery_ver']);

	if($cfg['use_seo_advanced'] != 'Y' && $_SERVER['SCRIPT_NAME'] == '/shop/detail.php' && $prd['no'] > 0) {
		$cfg['og_title'] = strip_tags($prd['name']);
		$cfg['og_image'] = getListImgURL($prd['updir'], $prd['upfile2']);
		$cfg['og_description'] = strip_tags(htmlspecialchars(stripslashes($prd['content1'])));
	}

	if($nvcpa) {
		$tmp = preg_replace("@^https?://(www\.)?@", '', $root_url);
		preg_match("/(([^.]+)\.([^.]+|co\.kr))$/", $tmp, $tmp);
		$nvcpa_domain = $tmp[0];
	}

    $favicon = '';
    if ($cfg['favicon'] == 'Y') {
        $favicon = $root_url.'/favicon.ico';
    } else if ($cfg['favicon']) {
        $favicon = getListImgURL('_data/favicon', $cfg['favicon']);
    }

?>
<title><?=stripslashes($cfg['br_title'])?></title>
<meta name="keywords" content="<?=$cfg['meta_key']?>">
<meta name="description" content="<?=$cfg['meta_des']?>">
<meta property="og:site_name" content="<?=$cfg['company_mall_name']?>" />
<meta property="og:url" content="<?=getURL()?>" />
<?php if ($cfg['use_seo_advanced'] == 'Y') { ?>
<meta property="og:title" content="<?=$cfg['og_title']?>" />
<meta property="og:description" content="<?=$cfg['og_description']?>" />
<meta property="og:image" content="<?=$cfg['og_image']?>" />
<?php } ?>
<?php if ($favicon) { ?>
<link rel="shortcut icon" type="image/x-icon" href="<?=$favicon?>">
<link rel="apple-touch-icon" href="<?=$favicon?>">
<?php } ?>
<link rel="stylesheet" type="text/css" href="<?=$_css_url?>">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/jquery/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/loading.css??<?=date('YmdHi')?>">
<script type="text/javascript">
var hid_frame='hidden<?=$now?>';
var mlv='<?=$member['level']?>';
var alv='<?=$admin['level']?>';
var root_url='<?=$root_url?>';
var engine_url='<?=$engine_url?>';
var this_url='<?=$this_url?>';
var ssl_url='<?=$ssl_host?>';
var soldout_name='<?=$cfg['soldout_name']?>';
var ace_counter_gcode='<?=$cfg['ace_counter_gcode']?>';
var uip = "<?=$_SERVER['REMOTE_ADDR']?>";
var currency = "<?=$cfg['currency']?>";
var currency_type = "<?=$cfg['currency_type']?>";
var currency_decimal='<?=$cfg['currency_decimal']?>';
var r_currency_type = "<?=$cfg['r_currency_type']?>";
var r_currency_decimal='<?=$cfg['r_currency_decimal']?>';
var exchangeRate = '<?=$GLOBALS['exchangeRate']?>';
var juso_api_use = '<?=$cfg['juso_api_use']?>';
var browser_type = '<?=$_SESSION['browser_type']?>';
var mobile_browser = '<?=$GLOBALS['mobile_browser']?>';
var ssl_type = '<?=$cfg['ssl_type']?>';
<?php if($admin['level'] == 1 || $admin['level'] == 2){ ?>
if(typeof this.parent != 'undefined') {
	if(this.parent.mngDirectV == 1) this.parent.window.frames['direct_mng_top_frm'].dm_url();
}
<?php } ?>

<?php if($cfg['today_click_ok']){ ?>
var click_prd=new Array();
<?php
$cpii=0;
while($cprd=clickPrdLoop2(2)) {?>
click_prd[<?=$cpii?>]="<?=$cprd?>";
<?php }
if(is_array($_click_prd)) reset($_click_prd);
$cpii=0;
?>
var click_prd_limit=<?=$cfg['today_cilck_limit']?>;
var click_prd_start=1;
var click_prd_finish=click_prd_limit+1;
<?php } ?>

//카카오 지도 설정 추가
<?php if($cfg['use_kakao_location'] == 'Y') { ?>
const store_marker_yn = '<?php echo $cfg['store_marker_yn']; ?>';
const store_marker_updir = '<?php echo $cfg['store_marker_updir']; ?>';
const store_marker_upfile1 = '<?php echo $cfg['store_marker_upfile1']; ?>';
const store_marker_clusterer = '<?php echo $cfg['store_marker_clusterer']; ?>';
const store_marker_clusterer_color = '<?php echo $cfg['store_marker_clusterer_color']; ?>';
const store_location_gps = '<?php echo $cfg['store_location_gps']; ?>';
let gps_center_lat = '<?php echo $cfg['gps_center_lat']; ?>';
let gps_center_lng = '<?php echo $cfg['gps_center_lng']; ?>';
let store_marker_w = '<?php echo $cfg['store_marker_w']; ?>';
let store_marker_h = '<?php echo $cfg['store_marker_h']; ?>';
<?php } ?>
</script>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery/<?=$jquery_ver?>'></script>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery/<?=$jquery_ui_ver?>'></script>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery.serializeObject.js'></script>
<script type="text/javascript" src="<?=$root_url?>/_skin/<?=$_skin_name?>/script.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/lang/lang_<?=$cfg['language_pack']?>.js?00000002"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/common.js?<?=date('YmdHi')?>"></script>
    <script type="text/javascript" src="<?=$engine_url?>/_engine/common/custom.js?<?=date('YmdHis')?>"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.review.js?<?=date('YmdHi')?>"></script>
<script>
    // IE일때 URLSearchParams() 사용 가능하도록 js 호출
    let agentChk = navigator.userAgent.toLowerCase();
    if ((navigator.appName == 'Netscape' && agentChk.indexOf('trident') != -1) || (agentChk.indexOf('msie') != -1)) { // IE 체크
        $.getScript('<?=$engine_url?>/_engine/common/url-search-params.js');
    }
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery-wingNextPage.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<script type="text/javascript">
var date_picker_default = {
	'monthNamesShort':['1','2','3','4','5','6','7','8','9','10','11','12'],
	'dayNamesMin':[_lang_pack.data_week_sun, _lang_pack.data_week_mon, _lang_pack.data_week_tue, _lang_pack.data_week_wed, _lang_pack.data_week_thu, _lang_pack.data_week_fri, _lang_pack.data_week_sat],
	'weekHeader':'Wk',
	'dateFormat':'yy-mm-dd',
	'autoSize':false,
	'changeYear':true,
	'changeMonth':true,
	'showButtonPanel':true,
	'currentText':_lang_pack.common_info_today+'<?=date("Y-m-d", $now)?>',
	'closeText':_lang_pack.coommon_info_close
}

/* Timer */
<?php if($cfg['ts_use'] == 'Y') {for($i = 1; $i <= 4; $i++) { ?>
const use_ts_mark_<?=$i?> = '<?=$cfg['use_ts_mark_'.$i]?>';
const ts_mark_<?=$i?> = '<?=$cfg['ts_mark_'.$i]?>';
<?php }} ?>

<?php if(isset($_SESSION['cpn_message']) == true && is_array($_SESSION['cpn_message']) == true) { ?>
// 로그인 쿠폰 발급 안내
$(function() {
	<?foreach($_SESSION['cpn_message'] as $val) {?>
		window.alert("<?=$val?>");
	<?} unset($_SESSION['cpn_message']);?>
});
<?php } ?>
</script>
<?php if($cfg['secutiry_click'] == 'Y') { ?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/sec.js"></script>
<?php } ?>
<?php
	if(!$_GET['popno']) {

		// naver cpa
		if($nvcpa) {?>
<script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>
<script type="text/javascript">
if(!wcs_add) var wcs_add = {};
wcs_add["wa"] = "<?=trim($cfg['ncc_AccountId'])?>";
if(typeof wcs != 'undefined') {
	wcs.inflow("<?=$nvcpa_domain?>");
}
</script>
		<?php }

		if($cfg['use_fb_pixel'] == 'Y' && $cfg['fb_pixel_id']) include $engine_dir.'/_engine/promotion/fb_pixel.js.php';

		// naver click choice
		if($cfg['cchoice_env_code'] && $cfg['cchoice_env_type']) {
			include_once $GLOBALS['engine_dir'].'/_engine/log/ad_cchoice_'.$cfg['cchoice_env_type'].'.inc.php';
		}

		$com_script_dir = $GLOBALS['root_dir']."/".$GLOBALS["dir"]['upload']."/".$GLOBALS["dir"]['compare'].'/common_script.php';
		if(is_file($com_script_dir)){
			include_once $com_script_dir;
		}

		// recopick
		if($cfg['recopick_use'] == '1' && $cfg['recopick_id'] && $cfg['recopick_url']) {
			include_once $engine_dir.'/_engine/log/recopick_header.inc.php';
		}

		// 옥션 OPEN BAR
		if($cfg['auction_use'] == "Y" && ($_GET['clickid'] != "" && $_GET['ref'] == 'auction_open' || $_SESSION['auction_open'] == 'Y')){
			$_SESSION['auction_open']="Y";
			?>
			<script type="text/javascript" src="http://openshopping.auction.co.kr/bar/open_bar.js"></script>
			<script>open_bar()</script>
		<?php }

		// Custom header
        global $pdo;
		$res = $pdo->iterator("select value from $tbl[default] where code like 'head_%' order by code asc");
		foreach($res as $data) {
			echo stripslashes(trim($data['value']))."\n";
		}

		echo setMktScript('header');

		// google analytics 향상된 전자상거래
		if($cfg['use_ga'] == 'Y' && $cfg['ga_code'] && $cfg['use_ga_enhanced_ec']=="Y") {
			include_once $GLOBALS['engine_dir'].'/_engine/log/google_upAnalytics.inc.php';
		}

		if($cfg['app_config_use']=="Y" && strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP')===false && $_SESSION['browser_type']=="mobile") {
			?><script type="text/javascript" src="//magicapp.wisacdn.com/install/market.min.js?<?=date("Ymda")?>"></script><?php
		}
	}

	if($cfg['apple_login_use'] == 'Y' && $cfg['apple_login_client_id']) { // 애플 로그인
		echo ("
		<script type=\"text/javascript\" src=\"https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js\"></script>
        <script type=\"text/javascript\">
			AppleID.auth.init({
				clientId : '{$cfg['apple_login_client_id']}',
				scope : 'name email',
				redirectURI : '{$root_url}/main/exec.php?exec_file=promotion/apple_login.exe.php',
				usePopup : (browser_type == 'pc' ? true : false)
			});
			function appleLogin() {
				if(browser_type == 'pc') {
					var data = AppleID.auth.signIn().then(function(response) {
						$.post('/main/exec.php?exec_file=promotion/apple_login.exe.php', response, function(r) {
							if(r == 'OK') {
								location.href = '/member/apijoin.php';
							} else {
								window.alert('애플 로그인 연동 오류');
							}
						});
					}, function(err) {
						if(err.error != 'popup_closed_by_user') {
							window.alert(err.error);
						}
					});
				} else {
					AppleID.auth.signIn();
				}
			}
        </script>
		");
	}
?>
</head>
<body <?php if($cfg['secutiry_drag'] == 'Y'){ ?>onselectstart="return false" ondragstart="return false"<?php } ?>>
<iframe name="hidden<?=$now?>" src="about:blank" width="0" height="0" scrolling="no" frameborder="0" style="display:none"></iframe>

<?php if($admin['no'] && $_SESSION['skin_preview_name'] != ""){ ?>
<div style="position:fixed; z-index:999; width:100%; padding:10px; background-color:#E7E7E7; border-bottom:1px solid #CECECE; font-size:12px; color:#464646; font-weight:bold; letter-spacing:-1; font-family:맑은 고딕, 돋움; text-align:left;">
	현재 <u><?=$_SESSION['skin_preview_name']?></u> 스킨 미리보기 중입니다.
	&nbsp;<a href="<?=$root_url?>/_manage/?body=design@skin_preview.frm&skin_preview_end=Y&urlfix=Y"><u style="color:#2EADEB; font-size:12px; letter-spacing:-1; font-family:맑은 고딕, 돋움;">미리보기 종료하기</u></a>
</div>
<?php } ?>