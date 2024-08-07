<?PHP

	define("_wisa_manage_edit_", true);
	include_once "manage.lib.php"; // 관리자 라이브러리

    // 중복 로그인 체크
    if ($body != 'intra@intra_factor.frm' && $body != 'intra@access_limit.exe' && $scfg->comp('use_prevent_dup_admin', 'Y') == true) {
        if ($db_session_handler->checkDuplicate('admin_no') == false) {
            msg("관리자님의 아이디가 다른 환경에서 로그인 되어,\\n현재 사용중인 환경에서 로그아웃 되었습니다.", '/_manage', 'parent');
        }
    }

	$body = $_REQUEST['body'];
	if(!$body) {
		$body = $_GET['body'];
		$body = preg_replace('/[^a-z0-9_@.]/i', '', $body);
	}
	if($body == 'main@main' || strpos($body, '_trash') > 0) {
		trncTrash();
	}

	$def_mmain = './?body=main@main';
	if($admin['level'] == 4) {
		$main_intra = $pdo->assoc("select `db`, `no` from `$tbl[intra_board_config]` where auth_list>='$admin[level]' order by `no` limit 1");
		if($main_intra['no']) {
			$def_mmain = '?body=board@board&db='.$main_intra['db'];
		}else {
			$def_mmain = '?body=config@info';
		}
	}
	$menu_file = $engine_dir.'/_manage/menu/menu.xml.php';
	if($admin['level'] == 4) {
		$menu_file = $engine_dir.'/_manage/menu/partner.xml.php';
	}
	$redirect_url = 'http://redirect.wisa.co.kr';
	if(!$body) msg('', $def_mmain);

	if(preg_match('/\.{2,}|\//', trim($body))) {
		msg('', $def_mmain);
	}

	// APP 구매 정보
	if($body == 'main@main') {
		$_apps = array();
		$wec = new WeagleEyeClient($_we, 'account');
		$appinfo = $wec->call('getAppInfo');
		if(is_array($appinfo) == true && count($appinfo) > 0) {
			foreach($appinfo as $key => $val) {
				if($val->app_idx[0] > 0) {
					$_apps[] = $val->app_idx[0];
				}
			}
		}
		$_apps = implode('@', $_apps);
		if(implode('@', $_apps_n) != $_apps) {
			$exists_info = $pdo->row("select count(*) from $tbl[default] where code='plugin_info'");
			if($exists_info < 1) {
				$pdo->query("insert into $tbl[default] (code, value, ext) values ('plugin_info', '$_apps', '$now')");
			} else {
				$pdo->query("update $tbl[default] set value='$_apps', ext='$now' where code='plugin_info'");
			}
		}
	}

	$only_read_menu = 1;
	include $engine_dir.'/_manage/menu/menu.php';
	$only_read_menu = 0;

	$_inc = explode('@', $body);
	$body_file = ($_REQUEST['edir'] == 'root') ? $root_dir.'/_manage/' : $engine_dir.'/_manage/';
	if($admin['level'] == 4) {
		$body_file = ($edir == 'root') ? $root_dir.'/_partner/' : $engine_dir.'/_partner/';
	}

	// 부가서비스 사용정보
	if($is_alone == true) $wp_stat = 3;
	if($body == 'product@product_register' || $_inc[0] == 'main' || $_inc[1] == 'multi_shop' || $_GET['body'] == '1010' || !is_array($_SESSION['wisa_manager'])) {
		$weca = new weagleEyeClient($_we, 'account');
		$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code'], 'use_cdn'=>$cfg['use_cdn']));
		$wp_stat = $asvcs[0]->wp_stat[0];

		// 추가계정 정보
		$_SESSION['myAccounts'] = $weca->call('getMyAccounts');
		unset($weca);

		$_SESSION['wisa_manager'] = array(
			'name' => $asvcs[0]->manager_name[0],
			'phone' => $asvcs[0]->manager_phone[0],
			'photo' => $asvcs[0]->manager_photo[0],
			'pos' => $asvcs[0]->manager_pos[0],
		);

		$_SESSION['disk_svc_name'] = ($asvcs[0]->disk_svc_name[0]) ? $asvcs[0]->disk_svc_name[0] : '저장소';
		$_SESSION['mall_goods_idx'] = $asvcs[0]->mall_goods_idx[0];
		$_SESSION['mall_goods_name'] = $asvcs[0]->mall_goods_name[0];

		if(file_exists($engine_dir.'/_engine/include/account/getHspec.inc.php')) {
			include $engine_dir.'/_engine/include/account/getHspec.inc.php';
		} else {
			// 이미지 업로드 스펙
			include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
			$_h_spec['img_upload_limit'] = $up_cfg['prdBasic']['filesize'];
			$_h_spec['img_limit'] = 0;
			$_SESSION['h_spec'] = $_h_spec;
		}
	}

	if(@strchr('support|customer|marketing|wftp|wing|cooperate', $_inc[0])){
		if($admin['level']=='3' && !@strchr($admin['auth'], $_inc[0]) && !preg_match('/css|support|wing/',$_inc[0])){ msg('접근 권한이 없습니다.','back'); }
		if(!@strchr($_inc[1], 'sso')) $def_mmain='./?body=support@sso&obody='.urlencode($_SERVER['QUERY_STRING']);
	}

	$body_file .= $_inc[0].'/'.$_inc[1].'.php';
    if(!is_file($body_file)) msg('', $def_mmain);

	if($body == 'design@editor.exe' && $_POST['type'] == "mobile") {
		$_inc[0] = 'wmb';
	}

	// 페이지별 접근 권한 체크
	if($body == 'extension@mms_callback.exe' || $body == 'extension@kakao_otp.exe') $admin['auth'] .= '@extension';
    if ($body == 'extension@sms_attach.exe' && preg_match('/@member/', $admin['auth']) == true) $admin['auth'] .= '@extension';
    if ($body == 'member@member_memo.exe' || $body == 'member@member_memo_list.exe') {
        if (preg_match('/@order/', $admin['auth']) == true) {
            $_bypass = true;
        }
    }
    if ($body == 'product@product_memo_list_in.exe') {
        if (preg_match('/@(order|member)/', $admin['auth']) == true) {
            $_bypass = true;
        }
    }
    if ($body == 'main@sso.exe') {
        $_bypass = true;
    }
    if($admin['level'] == '3' && !preg_match('/intra|R2Na2|R2Na3|neko_upper|css|support|menu/',$_inc[0]) && !strchr($body, 'member@msg') && !strchr($body, 'main@wQuickmenu.exe') && isset($_bypass) == false) {
        if(!preg_match("/$_inc[0]/", $admin['auth'])) { // 대메뉴 권한 체크
            if($_inc[0] == 'main'){
                if(!@strchr($_inc[1],'log')) msg('', './?body=intra@main');
			}else {
				msg('접근 권한이 없습니다.', 'back', 'parent');
			}
		} else { // 세부 권한 체크
			if(@strchr($admin['auth'], '@auth_detail')){
				$_auth_detail = $pdo->assoc("select * from `$tbl[mng_auth]` where `admin_no`='$admin[no]' limit 1");
				if(trim($_auth_detail[$_inc[0]])){
					if(is_object($current_menu) && $current_menu->val('mcode') && !preg_match('/@'.$current_menu->val('mcode').'@/', $_auth_detail[$_inc[0]])){
						if(preg_match('/^[0-9]+$/', $_GET['body'])) { // 메인메뉴 클릭시 링크가 권한이 없으면 해당 대메뉴 내에 다른 권한페이지가 있는지 검색
							foreach($current_big->mid as $mid) {
								foreach($mid->small as $small) {
									if($small->val('hidden') == 'Y' || !$small->val('link')) continue;
									if(preg_match('/@'.$small->val('mcode').'@/', $_auth_detail[$_inc[0]])) {
										msg('', '?body='.$small->val('link'));
									}
								}
							}
						}
						msg('접근 권한이 없습니다.','back','parent');
					}
				}
			}
		}
	}

	if(preg_match('/\.(exe|css|xml)/',$_inc[1]) || $_REQUEST['execmode'] == 'ajax') {
		return;
	}

	$manage_url = (($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

	if($body == 'log@ac_view' && !$cfg['ace_counter_id']) {
		msg("현재 고급접속통계를 신청하지 않으셨거나\\t\\n기간이 만료되었습니다.\\n안내페이지로 이동합니다.", './?body=log@ac_apply');
	}

	if(defined('_mobile_manager_')) return;
	if($_GET['body'] == 'product@product_register.frm') printAjaxHeader();

	$_manage_title = (is_object($current_menu) && $current_menu->val('name')) ? $current_menu->val('name') : stripslashes($cfg['company_mall_name']);

    if (function_exists('sendMProtocol')) {
        sendMProtocol($_manage_title);
    }

	$_cache_admin_name = array();
	$mngres = $pdo->query("select admin_id, name from {$tbl['mng']}");
    foreach ($mngres as $tmp) {
		$_cache_admin_name[$tmp['admin_id']] = stripslashes($tmp['name']);
	}

?>
<!DOCTYPE html>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=_BASE_CHARSET_?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>[관리자] <?=$_manage_title?></title>
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_manage/css/manage.css?20220725">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/jquery/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/xeicon/xeicon.min.css">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/select2/css/select2.css"  />
<link rel="shortcut icon" href="<?=$engine_url?>/_manage/image/wing.ico">
<!--[if lte IE 8]>
<script src="<?=$engine_url?>/_engine/common/html5.js"></script>
<![endif]-->
<script>
var currency='<?=$cfg['currency_type']?>';
var currency_decimal = '<?=$cfg['currency_decimal']?>';
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery/jquery-ui-1.11.3.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/lang/lang_kor.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/common.js?<?=date('YmdHis')?>"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Tip.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/clipboard.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/select2/js/select2.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/select2/js/i18n/ko.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/manage.js?20210215"></script>
<?php if($cfg['use_sbscr']=='Y') { ?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<?php } ?>
<script type="text/javascript">
var hid_frame='hidden<?=$now?>';
var mlv='<?=$member['level']?>';
var alv='<?=$admin['level']?>';
var root_url='<?=$root_url?>';
var engine_url='<?=$engine_url?>';
var this_url='<?=$this_url?>';
var manage_url='<?=$manage_url?>';
var ace_counter_gcode='<?=$cfg['ace_counter_gcode']?>'; // 에이스카운터 GCODE
var msg_use='<?=$cfg['msg_use']?>';
var uip = "<?=$_SERVER['REMOTE_ADDR']?>";
var _order_sales = new Array();
var browser_type = 'pc';
<?php foreach($_order_sales as $key => $val) { ?>
_order_sales['<?=$key?>'] = '<?=$val?>';
<?php } ?>

$(document).ready(function() {
	$(':checkbox[name="check_pno\[\]"], :checkbox.list_check').click(function() {
		if($(this).is(':checked') == true) $(this).parents('tr').addClass('checked');
		else $(this).parents('tr').removeClass('checked');
	});
    $('.searching_select>select').select2({'language':'ko'});
});
</script>
</head>

<body id="manage">
<iframe name="hidden<?=$now?>" src="" width="0" height="0" scrolling="no" frameborder="0" style="display:none"></iframe>
<div id="ToolTip"></div>