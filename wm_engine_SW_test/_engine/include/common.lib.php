<?PHP

    use Wing\DB\PDODatabase;
    use Wing\Session\MySQLSession;
    use Wing\Session\RedisSession;
    use Wing\common\Config;
	use Wing\common\ErrorReport;
    use Wing\common\kakaoLocation;

	if(!defined("_wisa_set_included")) exit("필수 구성 요소를 찾지 못했습니다");

	if(defined("_wisa_lib_included")) return;
    define("_wisa_lib_included", true);

	include $engine_dir.'/_engine/include/vendor/autoload.php';
    include_once $engine_dir."/_engine/include/custom.lib.php"; // [커스텀] 추가 된 함수

    include __ENGINE_DIR__ . '/_engine/include/errorHandler.php';

	if(isset($_REQUEST['urlfix']) == true) $urlfix = $_REQUEST['urlfix'];
	if(isset($_POST['sess_id']) == true) {
		$urlfix = 'Y';
	}
	if(isset($_REQUEST['exec_file']) == true && preg_match('/^smartEditor/', $_REQUEST['exec_file']) == true) {
		$urlfix = 'Y';
	}

	// IP차단 설정
    if (defined('_wisa_manage_edit_') == false && file_exists($root_dir.'/_data/ip_block.txt') == true) {
        rename($root_dir.'/_data/ip_block.txt', $root_dir.'/_data/config/ip_block.txt');
    }
	if(defined('_wisa_manage_edit_') == false && file_exists($root_dir.'/_data/config/ip_block.txt')) {
		$fp = fopen($root_dir.'/_data/config/ip_block.txt', 'r');
		while($ip = fgets($fp, 18)) {
			$ip = trim($ip) ;
			if($ip == $_SERVER['REMOTE_ADDR']) {
                include_once $engine_dir.'/_engine/include/design.lib.php';
                $cfg['design_version'] = 'V3';
                $_skin = getSkinCfg();

				if(file_exists($_skin['folder'].'/CORE/forbid.wsr')) {
					$skinfile = $_skin['folder'].'/CORE/forbid.wsr';
				} else {
					$skinfile = $engine_dir.'/_engine/skin_module/default/CORE/forbid.wsr';
				}

				if(file_exists($root_dir.'/_data/ip_msg.txt')) {
					$fp = fopen($root_dir.'/_data/ip_msg.txt', 'r');
					$deny_ip_msg = fgets($fp);
					fclose($fp);
				} else {
					$deny_ip_msg ='비정상적인 이용이 아닐 경우 관리자에게 문의 해주세요.';
				}

				$content = file_get_contents($skinfile);
				$content = str_replace('{{$차단메시지}}', $deny_ip_msg, $content);
				$content = str_replace('{{$엔진이미지경로}}', $engine_url.'/_manage/image/', $content);
				$content = str_replace('{{$이미지경로}}', $_skin['url'].'/img', $content);

				exit($content);
			}
		}
		fclose($fp);
	}

	include_once $engine_dir.'/_config/set.hosting.php';
	if(file_exists($engine_dir.'/_engine/include/account/setHosting.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/setHosting.inc.php';
	}

    // Database
    require_once __ENGINE_DIR__.'/_engine/common/db_lb.inc.php'; // 읽기 데이터서버 랜덤 분배
    $pdo = new PDODatabase(array(
        'driver' => 'mysql',
        'host' => $con_info[1],
        'user' => $con_info[2],
        'password' => $con_info[3],
        'db' => $con_info[4],
    ), str_replace('-', '', _BASE_CHARSET_));
	include_once $engine_dir."/_engine/include/db.lib.php"; // 구버전 호환성 유지 코드

    // 설정 로딩
	if(defined('__WING_SETUP__') != true) {
        $scfg = new Config($pdo, $tbl['config'], 'cfg');
	}
	include_once $engine_dir."/_engine/include/shop.lib.php";

	if(isset($_GET['exec_file']) == true && $_GET['exec_file'] == 'api/erp.exe.php') $urlfix = 'Y';

	if($urlfix == 'Y' && ($_SERVER['HTTPS'] != 'on' && $_SERVER['HTTP_X_FORWARDED_PORT'] != '443')) {
		$cfg['ssl_type'] = 'N';
	}

	if($urlfix == 'Y' && $cfg['ssl_type'] != 'Y' && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTP_X_FORWARDED_PORT'] == '443')) {
		$cfg['ssl_type'] = 'Y';
		$root_url = 'https://' . $_SERVER['HTTP_HOST'];
	}    

    // 관리자모드에서 https 접속 시 강제로 https 적용
    $protocol = (
        (isset($_SERVER['HTTPS']) == true && $_SERVER['HTTPS'] == 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) == true && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443')
    ) ? 'https' : 'http';
    if (defined('_wisa_manage_edit_') == true && $protocol == 'https' && $scfg->comp('ssl_type', 'Y') == false) {
        $engine_url = preg_replace('@^[a-z]+://@', 'https://', $engine_url);
    }

	if((isset($cfg['ssl_type']) == true && $cfg['ssl_type'] == 'Y')) {
		$root_url = preg_replace('@^[a-z]+://@', 'https://', $root_url);
		$engine_url = preg_replace('@^[a-z]+://@', 'https://', $engine_url);
		$cfg['ssl_host'] = $root_url.'/main/exec.php';
	}
	$_http_schema = ($scfg->comp('ssl_type', 'Y') == true) ? 'https://' : 'http://';

    if ($cfg['ssl_type'] == 'Y' && isset($urlfix) == false) {
        $uri = parse_url(getURL());
        if ($uri['scheme'] == 'http') {
            $root_url = preg_replace('/^http:\/\//', 'https://', $root_url);
            $reurl = str_replace('http://'.$uri['host'], $root_url, getURL());

            header('Location: '.$reurl);
            exit;
        }
    }

	if(headers_sent() == false) {
        // 세션 핸들러 등록
        if($scfg->comp('session_engine', 'Redis') == true && $scfg->comp('redis_host')) {
            $db_session_handler = new RedisSession($scfg->get('redis_host'));
            define('__SESSION_ENGINE__', 'Redis');
        } else {
            $db_session_handler = new MySQLSession();
            define('__SESSION_ENGINE__', 'MySQL');
        }

		if(isset($_REQUEST['sesskey']) == true) {
			$sesskey = trim(addslashes($_REQUEST['sesskey']));
			$sessck = $db_session_handler->exists($sesskey);
			if($sessck == true) {
				session_id($sesskey);
			}
		}

        $o_root_domain = preg_replace('/^(https?:\/\/)?(www\.|m\.)?|:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
		define('__COOKIE_DOMAIN__', (isset($cfg['set_cookie_domain']) == true) ? $cfg['set_cookie_domain'] : $o_root_domain);

        $scfg->def('session_lifetime', 30);
        ini_set('session.gc_maxlifetime', $cfg['session_lifetime']*60);
	    if ($scfg->comp('ssl_type', 'Y') == true) {
            session_set_cookie_params(0, '/; samesite=None', __COOKIE_DOMAIN__, true, true);
        } else {
        	session_set_cookie_params(0, '/', __COOKIE_DOMAIN__);
        }
		session_start();
		header("P3P: CP=\"NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM\"");
	}

	if(isset($_SESSION['skin_preview_name']) == true) { // 스킨 미리보기
		$root_url = (($cfg['ssl_type'] == 'Y') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
	}

	// 언어팩
    $scfg->def('language_pack', 'kor');
	if(!$cfg['currency']) $cfg['currency'] = '원';
	if(!$cfg['currency_type']) $cfg['currency_type'] = '원';
	if(!$cfg['m_currency_type']) $cfg['m_currency_type'] = '원';
	if(!$cfg['b_currency_type']) $cfg['b_currency_type'] = '원';
	if(!$cfg['currency_decimal']) $cfg['currency_decimal'] = 0;
	if(!$cfg['m_currency_decimal']) $cfg['m_currency_decimal'] = 0;
	if(!$cfg['r_currency_decimal']) $cfg['r_currency_decimal'] = 0;
	define("__currency__", $cfg['currency']);

	if(!$cfg['cart_delete_term']) $cfg['cart_delete_term'] = "7";

	include_once $engine_dir.'/_engine/common/lang/lang.'.$cfg['language_pack'].'.php';

    // 주문상태명 커스텀
    $_order_stat_o = $_order_stat;
    foreach ($_order_stat as $_k => $_v) {
        $_v = $scfg->get('order_stat_custom_'.$_k);
        if (empty($_v) == false) { // custom
            define('__lang_order_stat'.$_k.'__', $_v);
            $_order_stat[$_k] = $_v;
        } else { // default
            if (defined('__lang_order_ostat'.$_k.'__') == true) {
                define('__lang_order_stat'.$_k.'__', constant('__lang_order_ostat'.$_k.'__'));
            }
        }
    }

	if(isset($cfg['mypage_search_date_type']) == false || count($cfg['mypage_search_date_type'])==0) {
		$cfg['mypage_search_date_type'] = array(
			__lang_common_today__ => '-0 days',
			__lang_common_one_month__ => '-1 months',
			__lang_common_three_month__ => '-3 months',
			__lang_common_six_month__ => '-6 months',
			__lang_common_one_year__ => '-1 years',
			__lang_common_all__ => ''
		);
	}

	if($cfg['mobile_use'] != 'Y' && ($_GET['body'] == 'wmb@config' || $_GET['body'] == 'wmb@category_config' || $_GET['body'] == 'wmb@category_config2')) {
		msg(__lang_common_error_notsetmobile__, '/_manage/?body=wmb@what');
	}

	$now = time();
	$this_url = strip_tags(getURL());

	if($scfg->comp('use_erp_interface', 'Y') == true && $scfg->comp('erp_interface_name') == true) { // 두손 관련 함수
		include_once $engine_dir.'/_engine/include/classes/ErpInterface.class.php';
		include_once $engine_dir.'/_engine/include/classes/'.$cfg['erp_interface_name'].'.class.php';

		$erpListener = new $cfg['erp_interface_name']($cfg['erp_interface_param']);
	}

    //[매장지도] 프로세스
    if($scfg->comp('use_kakao_location', 'Y') == true) {
		$_kakao_store_handler = new kakaoLocation();
    }

    $cfg['mobile_name'] = '모바일';
	$p_root_url = $root_url;
	$m_root_url = preg_replace('/^((https?:)?\/\/)(www\.)?/', '$1m.', $root_url);
	$m_root_url = str_replace('m.m.', 'm.', $m_root_url);
	if(isset($cfg['custom_mobile_domain']) == true) {
		$m_root_url = $cfg['custom_mobile_domain'];
	}

	if(defined('_wisa_manage_edit_')) {
		$_SESSION['browser_type'] = 'pc';
	}

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'WISAAPP') > -1) {
		$_SESSION['is_wisaapp'] = true;
	} else {
		$_SESSION['is_wisaapp'] = false;
	}

	$mPattern = '/mobile/i';
	$uPattern = '/'.preg_replace('@https?://@', '', $m_root_url).'/i';

	preg_match($mPattern, $_SERVER['HTTP_USER_AGENT'], $mMatches);
	preg_match($uPattern, $this_url, $uMatches);
	$mobile_browser=(empty($mMatches[0])) ? '' : 'mobile';
	$mobile_url=(empty($uMatches[0])) ? '' : 'mobile';
	if($cfg['mobile_use'] == 'Y') {
		if(!empty($_GET['browser_type'])) {
			if(isset($_SESSION['browser_type'])) unset($_SESSION['browser_type']);
			$_SESSION['browser_type']=$_GET['browser_type'];
			$next_root_url=($_GET['browser_type'] == 'pc') ? $root_url : $m_root_url;

			setConversion();
			include_once $engine_dir."/_engine/log/counter.php";
			if($_GET['browser_type'] == 'pc' && $_GET['page_fix'] == 'Y' && $_SERVER['SCRIPT_NAME'] != '/shop/category.php') {
				$next_root_url = str_replace($m_root_url, $root_url, getURL());
				$next_root_url = preg_replace('/(\?|&)(browser_type|page_fix)=[a-z]+/i', '', $next_root_url);
			}

			if ($urlfix != 'Y') msg('', $next_root_url);
		}
		if($mobile_url == 'mobile') $_SESSION['browser_type']='mobile';
		if($mobile_browser == 'mobile' && $cfg['wmb_auto_redir'] != 'N') {
			if($cfg['mobile_use'] == 'Y' && ($_SESSION['browser_type'] == 'mobile' || empty($_SESSION['browser_type']))) $compare_root_url = $m_root_url;
			else $compare_root_url = $root_url;
		} else {
			if(isset($_SESSION['browser_type'])) unset($_SESSION['browser_type']);
			if($mobile_url == 'mobile' || $_GET['browser_type'] == 'mobile') {
				$_SESSION['browser_type'] = 'mobile';
				$compare_root_url = $m_root_url;
			} else {
				$_SESSION['browser_type'] = 'pc';
				$compare_root_url = $root_url;
			}
		}
        $parse_url = parse_url($this_url);
        $this_root_url = $parse_url['scheme'].'://'.$parse_url['host'];
	} else {
		$compare_root_url = $root_url;
        $parse_url = parse_url($this_url);
        $this_root_url = $parse_url['scheme'].'://'.$parse_url['host'];

        $_SESSION['browser_type'] = 'pc';
	}
	if($_SESSION['browser_type'] == 'mobile') $cfg['design_version'] = 'V3';

	if(is_admin() == false && $this_root_url != $compare_root_url && !$cfg['not_fix_root_url'] && !$_SESSION['dmng'] && $urlfix != 'Y') { // 도메인 유지
		$new_this_url = $compare_root_url.getURL(1);
		msg('', $new_this_url);
	}

    if(isset($cfg['secutiry_url']) == false) $cfg['secutiry_url'] = '1';
	if($_SESSION['browser_type'] == 'mobile' && $cfg['mobile_use'] == 'Y') {
		$cfg['secutiry_url'] = '1';
		$root_url=$m_root_url;
		if($cfg['ssl_type'] == 'Y') {
			$cfg['ssl_host'] = $root_url.'/main/exec.php';
		}
	}
	if(isset($_GET['NaPm']) == true && $_GET['NaPm'] && $cfg['secutiry_url'] == '2') $cfg['secutiry_url'] = '1';

	// 기본 설정
	if(!$cfg['soldout_name']) $cfg['soldout_name'] = __lang_common_info_soldoutname__;
	if(!$cfg['card_pg']) $cfg['card_pg'] = 'dacom';
	if(!$cfg['card_mobile_pg']) $cfg['card_mobile_pg'] = 'dacom';
	if(!$cfg['content1']) $cfg['content1'] = '요약 설명<br><font class="help">(사용하지 않을 경우 입력하지 않습니다)</font>';
	if(!$cfg['sale4_use']) $cfg['sale4_use'] = 'N';
	if(!$cfg['product_qna_secret']) $cfg['product_qna_secret'] = 'Y';
	if(!$cfg['join_jumin_use']) $cfg['join_jumin_use'] = 'N';
	if(!$cfg['delivery_prd_free']) $cfg['delivery_prd_free'] = 'Y';
	if(!$cfg['click_prd_make']) $cfg['click_prd_make'] = array ('no', 'hash', 'name', 'sell_prc', 'updir', 'upfile1', 'w1', 'h1', 'upfile2', 'w2', 'h2', 'upfile3', 'w3', 'h3', 'upurl');
	if(!$cfg['log_file']) $cfg['log_file'] = 'N';
	if(!$cfg['free_delivery_area']) $cfg['free_delivery_area'] = 'Y';
	if(!$cfg['bank_name']) $cfg['bank_name']="Y";
	if(!$cfg['erp_timing']) $cfg['erp_timing'] = 1;
	if(!$cfg['erp_soldout']) $cfg['erp_soldout'] = 'Y';
	if(!$cfg['sms_module']) $cfg['sms_module'] = 'mms';
	if(!$cfg['date_type_review']) $cfg['date_type_review'] = 'Y@/@m@/@d';
	if(!$cfg['date_type_qna']) $cfg['date_type_qna'] = 'Y@/@m@/@d';
	if(!$cfg['wmb_auto_redir']) $cfg['wmb_auto_redir'] = 'Y';
	if($cfg['use_biz_member'] == 'Y') $_use['use_biz_member'] = 'Y';
	if(!$cfg['refprds']) $cfg['refprds'] = 1;
	if(!$cfg['use_cpn_milage']) $cfg['use_cpn_milage'] = 'Y';
	if(!$cfg['delivery_free_limit']) $cfg['delivery_free_limit'] = 0;
	if(!$cfg['board_add_temp']) $cfg['board_add_temp'] = 3;
	if(!$cfg['pg_charge_1'] && !$cfg['pg_charge_4'] && !$cfg['pg_charge_5'] && !$cfg['pg_charge_7'] && !$cfg['pg_charge_E']) unset($_order_sales['sale0']);
	if($scfg->comp('use_set_product', 'Y') == false) unset($_order_sales['sale1']);
	if($cfg['ts_use'] != 'Y') unset($_order_sales['sale3']);
	if($cfg['use_sbscr'] != 'Y') unset($_order_sales['sale8']);
	if($cfg['use_qty_discount'] != 'Y') unset($_order_sales['sale9']);
	if($cfg['card_pg'] == 'dacom' && empty($cfg['pg_version']) == true) $cfg['pg_version'] = 'XpayNon';
	if($cfg['card_pg'] == 'card_mobile_pg' && empty($cfg['pg_mobile_version']) == true) $cfg['pg_mobile_version'] = 'smartXpaySubmit';
	if($cfg['pg_version'] == 'Xpay') $cfg['pg_version'] = 'XpayNon';
	if(!$cfg['delivery_fee']) $cfg['delivery_fee'] = 0;
	if(!$cfg['dlv_fee2']) $cfg['dlv_fee2'] = 0;
    if (isset($cfg['use_order_pohne']) == true) {
        $cfg['use_order_phone'] = $cfg['use_order_pohne'];
        $scfg->import(array(
            'use_order_phone' => $cfg['use_order_pohne']
        ));
        $scfg->remove('use_order_pohne');
    }

	// 앱 구매 여부 체크
	$_apps_n = $pdo->row("select value from {$tbl['default']} where code='plugin_info'");
	$_apps_n = explode('@', $_apps_n);

	// 아이핀 사용시 개인확인 Off
	if(defined('_wisa_manage_edit_') == false && $cfg['ipin_checkplus_use'] == 'Y') {
		$cfg['member_confirm_email'] = 'N';
		$cfg['member_confirm_sms'] = 'N';
	}

	// 이분류 기본값
	if($cfg['xbig_mng']) $_use['xbig'] = $cfg['xbig_mng'];
	if($cfg['ybig_mng']) $_use['ybig'] = $cfg['ybig_mng'];
	if($cfg['xbig_name_mng']) $cfg['xbig_name'] = $cfg['xbig_name_mng'];
	if($cfg['ybig_name_mng']) $cfg['ybig_name'] = $cfg['ybig_name_mng'];

	// 상품이미지 기본값
	$cfg['noimg1'] = $cfg['noimg1_mng'] ? $cfg['noimg1_mng'] : $cfg['noimg1'];
	$cfg['noimg2'] = $cfg['noimg2_mng'] ? $cfg['noimg2_mng'] : $cfg['noimg2'];
	$cfg['noimg3'] = $cfg['noimg3_mng'] ? $cfg['noimg3_mng'] : $cfg['noimg3'];
	$cfg['noimg4'] = '/_image/_default/prd/noimg4.gif';
	$cfg['noimg4'] = $cfg['noimg4_mng'] ? $cfg['noimg4_mng'] : $cfg['noimg4'];

    //[매장지도] 이미지 추가
    $cfg['store_noimg'] = '/_image/_default/store/store_noimg.gif';
	$cfg['thumb2_w'] = $cfg['thumb2_w_mng'] ? $cfg['thumb2_w_mng'] : $cfg['thumb2_w'];
	$cfg['thumb2_h'] = $cfg['thumb2_h_mng'] ? $cfg['thumb2_h_mng'] : $cfg['thumb2_h'];
	$cfg['thumb3_w'] = $cfg['thumb3_w_mng'] ? $cfg['thumb3_w_mng'] : $cfg['thumb3_w'];
	$cfg['thumb3_h'] = $cfg['thumb3_h_mng'] ? $cfg['thumb3_h_mng'] : $cfg['thumb3_h'];

	// 타임세일 기본값
	if(isset($cfg['use_ts_mark_1']) == false) $cfg['use_ts_mark_1'] = 'Y';
	if(isset($cfg['use_ts_mark_2']) == false) $cfg['use_ts_mark_2'] = 'Y';
	if(isset($cfg['use_ts_mark_3']) == false) $cfg['use_ts_mark_3'] = 'Y';
	if(isset($cfg['use_ts_mark_4']) == false) $cfg['use_ts_mark_4'] = 'Y';
	if(isset($cfg['ts_mark_1']) == false) $cfg['ts_mark_1'] = '일 ';
	if(isset($cfg['ts_mark_2']) == false) $cfg['ts_mark_2'] = ':';
	if(isset($cfg['ts_mark_3']) == false) $cfg['ts_mark_3'] = ':';
	if(isset($cfg['ts_mark_4']) == false) $cfg['ts_mark_4'] = '';

	// 추가이미지 필드
	if($cfg['mng_add_prd_img']){
		$cfg['add_prd_img'] = $cfg['mng_add_prd_img'] + 3;
		$_add_prd_img_tmp = explode(";", $cfg['mng_add_prd_info']);
		$api_num = 4;
		foreach($_add_prd_img_tmp as $api_key=>$api_val) {
			if(!$api_val) continue;
			list($api_name, $api_w, $api_h) = explode('^', $api_val);
			$cfg['prd_img'.$api_num] = $api_name;
			$cfg['thumb'.$api_num.'_w'] = $api_w;
			$cfg['thumb'.$api_num.'_h'] = $api_h;
			$api_num++;
		}
	}

    // 게시판 작성자 표기 설정 기본 값
    if ($scfg->comp('use_global_protect_name', 'Y') == false) {
        require __ENGINE_DIR__.'/_engine/include/migration/cfg_product_name.inc.php';
    }

	// 파일서버 설정체크
	$_use['file_server'] = null;
	$_file_server_dir = $root_dir.'/_config/file_server.php';
	if($cfg['file_server_option'] == '2'){
		if(file_exists($_file_server_dir)) {
			$_use['file_server'] = 'Y';
			include_once $_file_server_dir;
		}
	}

	// 사이트키
	$_site_key_file_dir = $root_dir."/_config/site_key.php";
	if(file_exists($_site_key_file_dir)){
		$_site_key_file_info = file($_site_key_file_dir);
		$_site_key_file_info[1] = trim($_site_key_file_info[1]);
		$_we['api_key'] = trim($_site_key_file_info[3]);
		$account_id = trim($_site_key_file_info[4]);
	}


	// WeagleEye API
	if(file_exists($engine_dir.'/_engine/include/account/wec.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/wec.inc.php';
	} else {
		define('__WEAGLEEYE_OUTSIDE__', true);

		$_we['wm_key_code'] = trim($_site_key_file_info[2]);
		$_we['account_idx'] = 'api_account_key';
		include_once $engine_dir.'/_engine/include/classes/API/Wisa/weagleEyeClient.php';
	}

	$wec = new weagleEyeClient($_we);

	if(file_exists($engine_dir.'/_engine/include/account/wm_expire.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/wm_expire.inc.php';
	}

	// 관리자모드 URL
	$mng_url_file = $engine_dir.'/_engine/include/account/getMngUrl.inc.php';
	if(file_exists($mng_url_file)) {
		include_once $mng_url_file;
	} else {
		$_use['direct_login'] = 'Y';
		$manage_url = ($scfg->comp('manage_url') == true) ? $scfg->get('manage_url') : $root_url;
	}

	// 윙POS 업데이트 체크
	if(!file_exists($root_dir.'/_data/cache/wingpos_update_log.txt')) {
		if(!fieldExist('erp_complex_option', 'opts')) {
			addField('erp_complex_option', 'opts', 'varchar(200) not null default ""');

			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query("drop function if exists `curr_stock`");
			$pdo->query($tbl_func['curr_stock']);
			unset($tbl_schema);
		}

		$mres = $pdo->iterator("select complex_no, opt1, opt2 from erp_complex_option where opts=''");
        foreach ($mres as $data) {
			if($data['opt1'] > 0) $opts = '_'.$data['opt1'].'_';
			if($data['opt2'] > 0) $opts .= $data['opt2'].'_';
			if($opts == '__') $opts = '';
			$pdo->query("update erp_complex_option set opts='$opts' where complex_no='{$data['complex_no']}'");
		}
		$fp = @fopen($root_dir.'/_data/cache/wingpos_update_log.txt', 'w');
		if($fp) {
			fwrite($fp, date('Y-m-d H:i:s'));
			fclose($fp);
		}
	}

	// 주문 상품 로그
	if($cfg['use_order_product_log'] != '1.0') {
		include_once $engine_dir.'/_config/tbl_schema.php';
		if(!$cfg['use_order_product_log']) {
			$pdo->query("drop table {$tbl['order_product_log']}");
			$pdo->query($tbl_schema['order_product_log']);
		}
		$pdo->query($tbl_schema['trigger_orderStatUpdate']);
		$pdo->query($tbl_schema['trigger_orderStatInsert']);
		$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('use_order_product_log', '1.0', '$now')");
	}

	// 회원 그룹 로그
	if($cfg['use_member_level_log'] != '1.0') {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['member_level_log']);
		$r = $pdo->query($tbl_schema['trigger_memberLevelUpdate']);
		if($r != false) {
			$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('use_member_level_log', '1.0', '$now')");
		} else {
            echo $pdo->getError();
        }
	}

	// 가격 변경 로그
	if(empty($cfg['use_product_price_log']) == true) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['product_price_log']);
		$pdo->query($tbl_schema['trigger_productUpdateAfter']);

		$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('use_product_price_log', 'Y', '$now')");
	}

	// wingpos routine check
	if(empty($cfg['create_wingpos_currstock']) == true) {
		if(routineExists('curr_stock') == false) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_func['curr_stock']);
			if($pdo->getError()) {
				exit('DB permission error : create routine');
			}
		}
		$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('create_wingpos_currstock', 'Y', '$now')");
	}

	setConversion(); // 쇼핑광고 컨버젼 체크

	if($_COOKIE['NVADID']) {
		$_SESSION['NVADID'] = $_COOKIE['NVADID'];
	}

	include_once $engine_dir."/_engine/log/counter.php";

    // 자동 로그인
    if (
        isset($_COOKIE['smartwing_al']) == true &&
        (isset($_SESSION['member_no']) == false || empty($_SESSION['member_no']) == true) &&
        (isset($_POST) == false || count($_POST) == 0) &&
        (isset($_REQUEST['exec_file']) == false || $_REQUEST['exec_file'] != 'common/sso.exe.php')
    ) {
        // 자동 로그인 시 각종 로그인 혜택을 받기 위해 로그인 실행파일 이용
        $_POST['rURL'] = getURL();
        $target = '_self';
        $mode = 'auto_login';
        require __ENGINE_DIR__.'/_engine/member/login.exe.php';
    }

	// 회원 정보
	if($_SESSION['member_no']) {
		if($_SESSION['m_member_id']) {
			$member = $pdo->assoc("select * from {$tbl['member']} where `no`=:member_no and `member_id`=:member_id and `withdraw` != 'Y'", array(
                ':member_no' => $_SESSION['member_no'],
                ':member_id' => $_SESSION['m_member_id']
            ));
			if(is_array($member)) {
				$member = getMemberAttr($member);
			}

			// 위시리스트 캐시
			if(defined('_wisa_manage_edit_') == false) {
				$member['wishlist'] = explode(',', $pdo->row("select group_concat(pno) from {$tbl['wish']} where member_no='{$member['no']}'"));
			}
		}
		if($member['level'] == 8 && $cfg['use_biz_member'] == 'Y') { // 도매/사업자 회원
			$biz = get_info($tbl['biz_member'], "ref", $_SESSION['member_no']);
		}
	}
	if(!$member['no'] || !$_SESSION['member_no']) {
		$_SESSION['member_no'] = '';
		$_SESSION['m_member_id'] = '';
		$member['level'] = 10;
	}
	if(!preg_match("/(xml\.php)|(pop\.php)$/", $_SERVER['SCRIPT_NAME'])) {
		$_SESSION['now_visit'] = $root_url.$_SERVER['REQUEST_URI'];
		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		if(!$_SESSION['ctime']) $_SESSION['ctime'] = $now;
	}

	// 비회원 임시 번호 발급
	if(!$member['no'] && !$_SESSION['guest_no']) {
		$_SESSION['guest_no'] = md5(str_replace('.', '', $_SERVER['REMOTE_ADDR'])*$now);
	}
	// 회원 가입 인증 체크
	if($member['no'] > 0 && $member['level'] > 1 && $cfg['member_reconfirm'] == 'Y' && ($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y')) {
		if($member['reg_email'] != 'Y' && $member['reg_sms'] != 'Y') {
			$access_check = explode('/', $_SERVER['SCRIPT_NAME']);
			if($access_check[1] != 'member' && $access_check[1] != 'mypage' && $access_check[2] != 'exec.php') {
				if(defined('_wisa_manage_edit_') == false){
					msg(__lang_common_error_nocert__, $root_url.'/member/edit_step1.php');
				}
			}
		}
	}

	// 관리자 정보
	if($_SESSION['admin_no']) {
		if(preg_match('/^token_/', $_SESSION['admin_no'])) {
			include_once $engine_dir.'/_engine/include/account/adminToken.inc.php';
		} else {
			$admin = get_info($tbl['mng'], 'no', $_SESSION['admin_no']);
		}
		if($admin['auth']) $admin['auth'] = str_replace('@service', '@service@support@wing@wftp', $admin['auth']);
		if($cfg['use_partner_shop'] == 'Y' && $admin['partner_no'] == '') {
			$admin['partner_no'] = '0';
		}
        settype($admin['level'], 'integer');
	}
	elseif($_COOKIE['autologin_id'] && $_COOKIE['autologin_code']) {
		$tmp_admin = get_info($tbl['mng'], 'admin_id', $_COOKIE['autologin_id']);
		if(md5($tmp_admin['no'].$tmp_admin['pwd']) == $_COOKIE['autologin_code']) {
			$_SESSION['admin_no'] = $tmp_admin['no'];
			$admin = $tmp_admin;

			include_once $engine_dir."/_manage/manage2.lib.php";
			mngLoginLog($admin['admin_id'], 5);
		}
	}

	// 네이버 CPA 사용 여부 체크
	$nvcpa = false;
	if($cfg['checkout_id'] && $cfg['checkout_key']) $nvcpa = true;		// 네이버 체크아웃 사용시
	if($cfg['ncpa_use'] == 'Y' && $cfg['ncc_AccountId']) $nvcpa = true;	// 네이버 CPA 허용시
	if($_SESSION['nvcpas'] == true) $nvcpa = true;						// 네이버 광고를 통해 접속시
	if($cfg['ncc_AccountId'] && ($_GET['NVAR'] || $_GET['NaPm'])) {		// 네이버 광고를 통해 접속시
		$nvcpa = true;
		$_SESSION['nvcpas'] = true;
	}

	// 참조화폐 사용시 환율 가져오기
	if(trim($cfg['r_currency_type'])){
		$r_currency_type = $cfg['r_currency_type'];
		if($cfg['r_currency_type']=='원') $r_currency_type = 'KRW';

		$r = $pdo->assoc("select code,value,ext from wm_default where code='{$r_currency_type}Rate'");
		$exchangeRate = $r['value'];
		$currency_type = $cfg['currency_type'];
		if($cfg['currency_type']=='원') $currency_type = 'KRW';

		if(!$r['code']){
			$rate = getExchangeRate($currency_type."_".$r_currency_type);
			$pdo->query("insert into wm_default set code='{$r_currency_type}Rate', value='${rate[0]}', ext='${rate[1]}'");
			$exchangeRate = $rate[0];
		}else{
			if($r['ext'] < date('Y-m-d')) {
				$rate = getExchangeRate($currency_type."_".$r_currency_type);
				if(!$rate[0]) $rate[0] = $r['value'];
				if(is_array($rate) && $rate[0]){
					$pdo->query("update wm_default set value='${rate[0]}', ext='${rate[1]}' where code='{$r_currency_type}Rate'");
					$exchangeRate = $rate[0];
				}
			}
		}
	}

    if (is_null($admin['admin_id']) == true) {
        $admin['admin_id'] = '';
    }
    if (is_null($member['member_id']) == true) {
        $member['member_id'] = '';
    }
	if(defined('_wisa_manage_edit_') == true) {
        $pdo->query("SET @runid='$now'");
        $pdo->query("SET @admin_id=?", array($admin['admin_id']));
        $pdo->query("SET @member_id=''");
	} else {
        $pdo->query("SET @admin_id=''");
        $pdo->query("SET @member_id=?", array($member['member_id']));
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void msg(string 출력할메시지, string 이동페이지, string 이동할창) - 메시지박스를 출력하고 다른 페이지로 이동
	' +----------------------------------------------------------------------------------------------+*/
	function msg($msg, $url = 'about:blank', $target = '') {
		global $connect, $layer1, $layer2, $dacom_note_url, $root_dir, $ono, $_qcheck;

        $result = ($msg === 'ok') ? 'success' : 'fail';

		if(isset($_REQUEST['ajax_return']) == true) {
			$GLOBALS['ajax_return_message'] = $msg;
			return;
		}
		close();

		printAjaxHeader();

		if(defined('__pg_card_pay.exe__')) {
			makePGLog(__pg_card_pay.exe__, 'pg exit');
		}

		if($dacom_note_url || $_REQUEST['from_ajax'] || $_REQUEST['next'] == 'talkpay') exit(stripslashes($msg));
        if ($_REQUEST['accept_json'] == 'Y') {
            exit(json_encode(array(
                'result' => $result,
                'message' => $msg,
                'url' => $url
            )));
        }

		if($_POST['uptype'] == 'swf') {
			exit(iconv(_BASE_CHARSET_, 'utf-8', $msg));
		}

		if($msg) {
            $str = "<script type='text/javascript'>
            alert('".$msg."');
            if(parent && typeof parent.$ != 'undefined') parent.$('#stpBtn').show();
            if(typeof parent.removeLoading != 'undefined') parent.removeLoading();
            if(typeof parent.removeFLoading != 'undefined') parent.removeFLoading();
            </script>";
        }
		switch($url) {
			case '':
				break;
			case 'popup':
				if(!$target) $target = 'parent.opener';
				$str .= "<script type='text/javascript'>\n";
				if($target) $str .= "if($target)";
				$str .= $target.".location.reload();\n";
				$str .= "parent.wclose();\n";
				$str .= '</script>';
				break;
			case 'close':
				if(!$target) $target = 'window';
				$str .= '<script type="text/javascript">'.$target.'.close();</script>';
				break;
			case 'back':
				$str .= '<script type="text/javascript">history.back();</script>';
				break;
			case 'reload':
				if($target) $target .= '.';
				$str .= '<script type="text/javascript">'.$target.'location.reload();</script>';
				break;
			default :
				$str .= makeLocation($target, $url, '', $msg);
		}

		if($layer1 || $layer2) {
		?>
		<script type='text/javascript'>
		<!--
		if(parent.document.getElementById('loadingBar')) parent.$('#loadingBar').remove();
		parent.layTgl(parent.document.getElementById('<?=$layer1?>'));
		parent.layTgl(parent.document.getElementById('<?=$layer2?>'));
		//-->
		</script>
		<?php
		}

		exit($str);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void setConversion(void) - 쇼핑광고 구매전환 체크
	' +----------------------------------------------------------------------------------------------+*/
	function setConversion() {
        global $tbl, $pdo;

		if($_REQUEST['from_ajax']) return;

		$ref = $_SERVER['HTTP_REFERER'];

		if($_REQUEST['gclid']) $advname = 'google';														// 구글
		if($_GET['cm_id'] == 'GDN') $advname = 'gdn';													// gdn
		if($_REQUEST['ref2'] == 'daum_howshop') $advname = 'daum_show';										// 다음 쇼핑하우
		if($_REQUEST['ref'] == 'daumbox1') $advname = 'daum_bbox';										// 다음쇼핑박스 1탭
		if($_REQUEST['ref'] == 'daumbox3') $advname = 'daum_sbox';										// 다음소호박스 3탭
		if($_REQUEST['DMCOL'] == 'PM') $advname = 'daum_clicks';											// 다음클릭스
		if($_REQUEST['ref'] == 'naver_open'																// 네이버 지식쇼핑
			|| ($_REQUEST['nv_pchs'] && !preg_match('/^roi_/', $_REQUEST['nv_pchs']))
			) {
				$advname = 'naver_is';

				# 네이버 지식쇼핑 ROI Tracker
				if($_REQUEST['cfg']['roi_use'] == 'Y' && $_REQUEST['nv_pchs']) {
					$cookie_time = $_REQUEST['now']+60*60*24*$GLOBALS['cfg']['roi_term'];
					setCookie("nv_pchs", $_REQUEST['nv_pchs'], $cookie_time, '/');
				}
		}
		if($_REQUEST['ref'] == 'nate_box1') $advname = 'nate_box1';										// 네이트 쇼핑박스 1탭
		if($_REQUEST['ref'] == 'nate_box2') $advname = 'nate_box2';										// 네이트 쇼핑박스 2탭
		if($_REQUEST['ref'] == 'nate_box4') $advname = 'nate_box4';										// 네이트 쇼핑박스 4탭
		if($_REQUEST['ref'] == 'criteo') $advname = 'criteo';											// 크리테오
		if($_GET['n_keyword'] && $_GET['n_ad_group']) {													// 네이버 기타 매체 URL
			$nvar_list = array('PL' => '네이버검색광고');
			$keyword = urldecode($_GET['n_keyword']);

			if(preg_match('/%u([a-f\d]{4})/i', $keyword)) { // unescape
				$keyword = preg_replace('/%u([a-f\d]{4})/ie',"iconv('UTF-16LE','EUC-KR',chr(hexdec(substr('\\1',2,2))).chr(hexdec(substr('\\1',0,2))))", $keyword);
			}

			if(strcmp(iconv('euc-kr', 'euc-kr', $keyword), $keyword) != 0) { // 한글코드 체크
				$keyword = (strcmp(iconv('utf-8', 'utf-8', $keyword), $keyword) == 0) ? iconv("utf-8", "euc-kr", $keyword) : ''; // 유니코드일 경우 확장완성형으로 변환
			}
			$advname = 'naver_cbox';
		}
		if(preg_match('/^ntbox_[a-z]$/', $_REQUEST['ref'])) { // 네이버 테마쇼핑
			$advname = $_REQUEST['ref'];
		}
        if ($_GET['wingkr_mid'] && $_GET['wingkr_link_cd']) { // wingkr
            $advname = 'wingkr';
        }
        if ($_GET['ref'] == 'wsmk_zigzag') {
            $advname = 'wsmk_zigzag';
        }
		if($_GET['wsmk'] && !$advname) { // 배너광고코드
			$pdo->query("update {$tbl['pbanner_group']} set `visited` = `visited` + 1 where `code`='{$_GET['wsmk']}'");
			$advname = 'wsmk_'.$_GET['wsmk'];
		}

		// 매체 키워드 세션 생성
		$ret = "";
		if(is_array($_COOKIE['cookie_conv'])) {
			foreach($_COOKIE['cookie_conv'] as $key => $val) {
				if($advname == $key) continue;
				$ret .= "@$key";
			}
		}

		if($advname) {
			$cookie_time = $GLOBALS['now']+60*60*24*30;
			setCookie("cookie_conv[$advname]", '1', $cookie_time, '/');

			$ret .= '@'.$advname;
		}
		$_SESSION['conversion'] = $ret;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  mixed getSearchQuery(string 검색어) - 포털사이트 레퍼러에서 검색어 체크
	' +----------------------------------------------------------------------------------------------+*/
	function getSearchQuery($str) {
		$pattern = array (
			"^http://([a-z]+\.)?([a-z]+\.)?naver.com" => "naver@query", // 네이버
			"^http://search\.daum\.net" => "daum@q", // 다음
			"^http://search\.nate\.com" => "nate@q", // 네이트
			"^http://www\.google\.com" => "google@q", // 구글
			"empas\.com/search" => "empas@q", // 엠파스
			"^http://hanaro\.digitalnames\.net" =>"empas@q", // 엠파스
			"^http://search\.live\.com/results\.aspx" => "msn@q", // MSN
			"^http://search\.cyworld\.com" => "cyworld@q", // 싸이월드
			"^http://www\.gmarket\.co\.kr/" => "gmarket@keyword", // 지마켓
			"^http://sense\.web-guide\.co\.kr" => "webguide@keyword", // 웹가이드
			"^http://search\.nate\.com" => "nate@q", // 네이트
		);

		foreach($pattern as $key => $val) {
			list ($engine, $param) = explode("@", $val);
			$key = str_replace('/', '\/', $key);
			if(preg_match("/$key/i", $str) && preg_match("/(&|\?)$param=/",$str)) {
				$_str = preg_replace("/^.*(&|\?)$param=([^&]+).*$/i", "$2", $str);
				$_str = urldecode($_str);

				if(preg_match('/%u([a-f\d]{4})/i', $_str)) { // unescape
					$_str = preg_replace('/%u([a-f\d]{4})/ie',"iconv('UTF-16LE','"._BASE_CHARSET_."',chr(hexdec(substr('\\1',2,2))).chr(hexdec(substr('\\1',0,2))))", $_str);
				}

				$charset = mb_detect_encoding($_str, array('utf-8', 'euc-kr'));
				$_str = mb_convert_encoding($_str, _BASE_CHARSET_, $charset);
				$_str = trim($_str);

				return array($engine, $_str);
			}
		}
		return false;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string makeLocation(string target, string url, string replace/href선택, string 출력할 메시지) - 페이지 이동
	' +----------------------------------------------------------------------------------------------+*/
	function makeLocation($target,$url,$replace=3,$msg="") {
		if(!$target && !$msg) {
			if(!headers_sent() && $url!='about:blank') {
				header( "Location: $url");
			} else {
				$str = "<meta http-equiv=\"refresh\" content=\"0; url=$url\">";
			}
		} else {
			$str = '<script type="text/javascript">';
			if($target) $str .= $target.'.';
			$str .= "location.";
			if($replace == '3') $str .= "replace('".$url."');";
			else $str .= "href='".$url."';";
			$str.="</script>";
		}
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string cutStr(string 텍스트, int 길이[, string 줄임문자]) - 긴텍스트 cut
	' +----------------------------------------------------------------------------------------------+*/
	function cutStr($str, $length, $astr = '...') {
		if(mb_strwidth(strip_tags($str), _BASE_CHARSET_) <= $length) {
			return $str;
		}
		return mb_strimwidth(strip_tags($str), 0, $length, $astr, _BASE_CHARSET_);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void checkBasic(int 체크방식) - 레퍼러 체크
	' +----------------------------------------------------------------------------------------------+*/
	function checkBasic($tp="") {
		if($GLOBALS['sso'] && $GLOBALS['ssldata']) return true;

		// $tp="" : 모두, 1 : 포스트, 2 : 리퍼러
		if($tp!=1) {
			if(!preg_match("@$_SERVER[HTTP_HOST]@i", $_SERVER['HTTP_REFERER'])) msg(__lang_common_error_ilconnect__);
		}
		if($tp!=2) {
			if($_SERVER['REQUEST_METHOD']=='GET') msg(__lang_common_error_ilconnect__);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean isblank(string 비교문자열) - 공백 텍스트 여부 확인
	' +----------------------------------------------------------------------------------------------+*/
	function isblank($str) {
		if(is_array($str)) {
			if(count($str) == 0) return true;
		} else {
			$temp = preg_replace('/\s|&nbsp;/i', '', $str);
			$temp = strip_tags($temp);
			$temp = trim($temp);

			return $temp == '';
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void checkBlank(string 비교문자열, string 에러메시지) - isblank 확인 후 에러메시지 출력
	' +----------------------------------------------------------------------------------------------+*/
	function checkBlank($var,$msg) {
		if(isblank($var)) msg($msg);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string del_html(string 문자열) - Html '<', '>' 리플레이스
	' +----------------------------------------------------------------------------------------------+*/
	function del_html($str) {
		$str = str_replace(">", "&gt;",$str);
		$str = str_replace("<", "&lt;",$str);
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string inputText(string 문자열) - 인풋박스에서 따옴표에 의한 오류가 일어나지 않도록 처리
	' +----------------------------------------------------------------------------------------------+*/
	function inputText($str) {
		$str = stripslashes($str);
		$str = str_replace('"', '&quot;', $str);
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string checked(string 비교값A, string 비교값b, boolean 셀렉트/체크여부) - 두개의 값을 비교하여 checked 혹은 selected 를 출력
	' +----------------------------------------------------------------------------------------------+*/
	function checked($n, $v, $s='') {
		if($n == $v) $r = ($s) ? 'selected' : 'checked';
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string selectArray(array 배열데이터, string 객체이름, int 사용값종류, string 제목옵션, string 선택된값, string onchange스크립트)
	' +----------------------------------------------------------------------------------------------+*/
	function selectArray($array,$name,$valtype=1,$blank="",$select="",$onChange="") {
		if(is_array($array) == false) return;
		$str="<select name=\"$name\" onChange=\"$onChange\">\n";
		if($blank) {
			$str.="<option value=\"\">$blank</option>\n";
		}
		foreach($array as $key=>$val) {
			if($val) {
				if($valtype==1) $value=$val;
				else $value=$key;
				if($select!=="") $sel=checked($value,$select,1);
				$str.="<option value=\"$value\" $sel>$val</option>\n";
			}
		}
		$str.="</select>\n";
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string radioArray(array 배열데이터, string 객체이름, int 사용값종류, string 제목옵션, string 선택된값, string onchange스크립트)
	' +----------------------------------------------------------------------------------------------+*/
	function radioArray($array,$name,$valtype=1,$select="",$onClick="") {
		if(is_array($array) == false) return;
		$str="";

		foreach($array as $key=>$val) {
			if($val) {
				if($valtype==1) $value=$val;
				else $value=$key;
				if($select!="") $sel=($value==$select)?'checked':'';
				$str.="<label><input type=\"radio\" name=\"$name\" value=\"$value\" onclick=\"$onClick\" $sel>$val</label>\n";
			}
		}

		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string disabled(string A - 값이 없을 경우 disable 처리
	' +----------------------------------------------------------------------------------------------+*/
	function disabled($d) {
		$r = (!$d) ? 'disabled' : '';
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string parseParam(string 검색어) - 검색어에 사용할수 없는 공격성 검색어 차단
	' +----------------------------------------------------------------------------------------------+*/
	function parseParam($param) {
		$deny_pattern = "/\)\(|;|%00|%zz|\|\||passwd|<!--|sleep\s*\(|&&|';|\\$/i";
		if(preg_match($deny_pattern, $param, $hpattern) == true) {
			if(!$_SESSION['param_deny']) $_SESSION['param_deny'] = 0;
			$_SESSION['param_deny']++;
			msg("[ $hpattern[0] ] ".__lang_common_error_ilKeyword__, 'back');
		}

		$param = addslashes(strip_tags($param));
		return $param;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string strip_script(string 문자열) - 텍스트내의 자바스크립트 제거
	' +----------------------------------------------------------------------------------------------+*/
	function strip_script($str) {
		$str = preg_replace('/(<|&lt;)(\/?)(java|vb)?script/i', '$1$2noscript', $str);
        $str = preg_replace('/(<|&lt;)(\/?)object/i', '$1$2noscript', $str);
		$str = preg_replace('/(onclick|onmouseover|onactivae|onafterprint|onafterupdate|onbefore|onbeforeactivate|oncopy|onbeforecopy,oncut|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur| onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror| onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout,onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onscroll|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onselect|onselectionchange|onselectstart|onstart,onstop|onsubmit|onunload|onpointerover)/i', 'noevent', $str);

		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getExt(string 파일명) - 파일명으로부터 확장자 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getExt($upfile_name) {
		return pathinfo($upfile_name, PATHINFO_EXTENSION);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string filesizeStr(int 파일용량, int 미사용, string 최대단위지정) - 파일용량을 문자단위로 표시
	' +----------------------------------------------------------------------------------------------+*/
	function filesizeStr($size, $comma = 0, $limit = null) {
		if($size < 0) $minus = true;
		$size = preg_replace("/[^0-9]/", "", $size);

		$unit = 0;
		$unit_array = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		while($size / 1024 >= 1) {
			$size = $size / 1024;
			$unit++;
			if($limit && $unit >= $limit) break;
		}
		if(!$size) $size = 0;
		$size = number_format($size, $comma);
		$size = preg_replace("/\.0+$/", "", $size);
		$size = $size.$unit_array[$unit];
		if($minus) $size = '-'.$size;

		return rtrim($size);
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  boolean in_arary2(array1, array2) - array1 의 값이 array2 에 있는지 확인
	' +----------------------------------------------------------------------------------------------+*/
	function in_array2($args, $array) {
		foreach($args as $val) {
			if(in_array($val, $array)) return true;
		}
		return false;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array setImageSize(int 원본가로, int 사본가로, int 새가로, int 새세로) - 섬네일 사이즈 반환
	' +----------------------------------------------------------------------------------------------+*/
	function setImageSize($currentX, $currentY=0, $maxX=0, $maxY=0) {
		if(!$currentY) {
			if(is_file($currentX)) {
				list($currentX, $currentY)=GetImageSize($currentX);
			} else {
				$str="width=\"0\" height=\"0\"";
				return array("", "",$str);
			}
		}

		if($currentX<$maxX && $currentY<$maxY) {
			$newX=$currentX;
			$newY=$currentY;
		} else {
			$currentImgBType = ($currentX / $currentY) >= 1 ? "bigWidth" : "bigHeight";

			if($currentImgBType == 'bigWidth') {
				$ratio = $maxX*100/$currentX;

				$newX = $maxX;
				$newY = round($currentY*$ratio/100);

				if($newY > $maxY) {
					$ratio = $maxY*100/$newY;

					$newY = $maxY;
					$newX = round($newX*$ratio/100);
				}
			} elseif($currentImgBType == 'bigHeight') {
				$ratio = $maxY*100/$currentY;

				$newY = $maxY;
				$newX = round($currentX*$ratio/100);

				if($newX > $maxX) {
					$ratio = $maxX*100/$newX;

					$newX = $maxX;
					$newY = round($newY*$ratio/100);
				}
			}
		}

		$str="width=\"$newX\" height=\"$newY\"";
		return array($newX, $newY,$str);
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  주석출력
	' +----------------------------------------------------------------------------------------------+*/
	function JC($v1,$v2="",$t="",$id="") {
		if($v1==$v2) $r=($t) ? "·‥…S..Y..//-->" : "<!--..K..Y…‥·";
		return $r."\n";
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  array checkPrd(int 상품코드, int 오류출력여부) - 상품번호로부터 상품데이터 반환
	' +----------------------------------------------------------------------------------------------+*/
	function checkPrd($pno,$check=2) {
		global $tbl, $cfg, $pdo;

		$pno = preg_replace('/[^[:alnum:]]/', '', $pno);

		if(!$pno && $check==1) msg(__lang_common_error_nopno__, '' , 'parent');
		$prd=get_info($tbl['product'],"hash",$pno);
		$prd=shortCut($prd);

		if($check) {
			$noprdUrl= strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) ? 'back' : '/';
			if (!$prd['no']) {
				msg(__lang_common_error_noprd__, $noprdUrl,'parent');
			}
			if($check>1 && $prd['stat']!="2" && $prd['stat']!="3") {
				if($_SESSION['admin_no'] >0 && $GLOBALS['admin']['no'] == $_SESSION['admin_no']) {
                    if (isset($_GET['d_preview']) == false) {
    					$GLOBALS['_defer_scripts'] = "<script>window.alert('숨김상품을 관리자 권한으로 확인중입니다.');</script>";
                    }
				} else {
					msg(__lang_common_error_nosaleprd__, $noprdUrl, 'parent');
				}
			}

			if($cfg['use_partner_shop'] == 'Y' && $prd['partner_no'] > 0) {
				$partner_stat = $pdo->row("select stat from {$tbl['partner_shop']} where no='{$prd['partner_no']}'");
				if($partner_stat != 2) msg(__lang_common_error_nosaleprd__, $noprdUrl, 'parent');
			}
		}
		return $prd;
	}
	/* +----------------------------------------------------------------------------------------------+
	' |  int numberOnly(string 값) - 데이터에서 숫자 이외에 모든 내용 제거
	' +----------------------------------------------------------------------------------------------+*/
	function numberOnly($str, $float = false) {
		if(is_array($str)) {
			foreach($str as $key => $val) {
				$str[$key] = numberOnly($val, $float);
			}
			return $str;
		} else {
			$str = trim($str);
			$num = ($float == true) ? preg_replace('/[^0-9\.]/', '', $str) : preg_replace('/[^0-9]/', '', $str);
			if(substr($str, 0, 1) == '-') $num = '-'.$num;
		}
        return $num;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int parsePrice(int priec) 상품가격 소수점 처리, p_type=true (관리용 금액인지)
	' +----------------------------------------------------------------------------------------------+*/
	function parsePrice($price, $comma = false, $p_type=false) {
		global $cfg;

		if($comma && strpos($price,',')) $price = str_replace(',','',$price);

		$decimal = 0;
		if(empty($price) == true) $price = 0;

		if($p_type) $decimal = $cfg['m_currency_decimal'];
		else $decimal = $cfg['currency_decimal'];

		if($comma == true) $price = @number_format($price, $decimal);
		if($cfg['currency_type'] == '달러') return $price;

		if($decimal == 0) return preg_replace('/\.[0-9]+$/', '', $price);
		else return $price;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int getPercentage(int 전체수, int 퍼센트, int 절사단위(정수), int 절사단위(소수점))
	' +----------------------------------------------------------------------------------------------+*/
	function getPercentage($num, $percent, $cutoff1 = 0, $cutoff2 = 0) {
		$result = $num*($percent/100);

		if($cutoff1 > 0 || $cutoff2 > 0) {
			$result = cutOff($result, $cutoff1, $cutoff2);
		}

		return $result;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int cutOff(int 숫자, int 절사단위(정수), int 절사단위(소수점))
	' +----------------------------------------------------------------------------------------------+*/
	function cutOff($num, $unit1, $unit2 = 0) {
		global $cfg;

		if($unit1 > 0) {
			$pow = ($cfg['currency_decimal'] > 0) ? (pow(10, $cfg['currency_decimal'])) : 1;
			$num *= $pow;

			$unit = pow(10, strlen($unit1)-1);
			if($cfg['currency_decimal'] > 0) {
				$num = floor($num*(pow(10, $cfg['currency_decimal'])));
				$num = bcdiv(floor($num/$unit)*$unit, pow(10, $cfg['currency_decimal']));
			} else {
				$num = floor(bcdiv($num, $unit))*$unit;
			}

			$num /= $pow;
		}
		$num = parsePrice(preg_replace('/(\.[0-9]{'.$unit2.'}).*$/', '$1', $num));

		return $num;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string addBar(array 데이터) - 전화번호/우편번호 등을 배열로 받은 후 - 로 묶음
	' +----------------------------------------------------------------------------------------------+*/
	function addBar($arr) {
		$str = implode('-', $arr);
		$str = preg_replace('/-+/', '-', $str);
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int checkKorean(string 문자열, boolean 영문체크여부) - 문자열에 한글(영문)이 포함되어 있는지 체크
	' +----------------------------------------------------------------------------------------------+*/
	function checkKorean($str, $e = '') {
		for($i=0,$maxi=strlen($str); $i<$maxi; $i++) {
			if(ord($str[$i])< 128) {
				$buf_e++;
			}else{
				$buf_k++;
			}
		}
		if($e) return $buf_e; // 영문수
		else return $buf_k; // 한글수
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getGroupName(int 그룹번호) - 회원그룹명 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getGroupName($lv = '') {
		global $tbl, $_cache_groupname, $pdo;

		if(is_array($_cache_groupname)) {
			$group = $_cache_groupname;
		} else {
			$res = $pdo->iterator("select no, name from `$tbl[member_group]` where `use_group`='Y' and `no`!='1' order by `no` desc");
			$a=0;
			$group = array();
			$group[1] = __lang_common_info_grp1__;
			foreach ($res as $data) {
				$level=$data['no'];
				$group[$level]=stripslashes($data['name']);
				$a++;
			}
			$_cache_groupname = $group;
		}

		if($a == 0 && !$group[9]) $group[9] = __lang_common_info_grp9__;
		if($cfg['use_biz_member'] == 'Y') $group[8] = __lang_common_info_grp8__;

		if($lv) return $group[$lv];
		else return $group;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  string getURL(int url표시여부) - 현재 URL을 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getURL($tp="") {
		$file = $_SERVER['SCRIPT_NAME'];
		$file = str_replace('index.php', '', $file);

		$query = $_SERVER['QUERY_STRING'];
		if($query) $file .= '?'.$query;

		$file = preg_replace('/\'|"/', '', $file);
		$protocol = (
            (isset($_SERVER['HTTPS']) == true && $_SERVER['HTTPS'] == 'on')
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) == true && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443')
        ) ? 'https' : 'http';

		if($tp==1) return $file;
		else return $protocol.'://'.$_SERVER['HTTP_HOST'].$file;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void memberOnly(string 리다이렉트주소[, string 타겟창]) - 비회원 접근을 차단하고 리다이렉트
	' +----------------------------------------------------------------------------------------------+*/
	function memberOnly($rURL=1, $target='parent') {
		global $root_url,$member;
		if($member['no']) return;
		if(!$rURL || $rURL==1) $rURL = getURL();
		msg('', $root_url."/member/login.php?rURL=".urlencode($rURL), $target);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void trncCart(int 체크시간) - 지정된 시간이 초과된 장바구니를 제거
	' +----------------------------------------------------------------------------------------------+*/
	function trncCart($hr = 12) {
		global $now, $tbl, $cfg, $pdo;

		$del_time = $now-(3600 * $hr);
		$del_time2 = $now-(86400 * $cfg['cart_delete_term']);
		if($cfg['cart_member_delete'] == "Y"){ // 회원일경우 장바구니를 유지 설정
			if($cfg['cart_delete_term'] != 'N') { // 직접 삭제 설정을 제외한 경우 삭제
                // 회원일 경우는 설정된 기간만큼
				$pdo->query("delete from {$tbl['cart']} where `reg_date`<$del_time2 and `member_no` != '0'"); //일반 장바구니
                if ($cfg['use_sbscr'] == 'Y') {
                    $pdo->query("delete from {$tbl['sbscr_cart']} where `reg_date`<$del_time2 and `member_no` != '0'"); //정기배송 장바구니
                }
			}
            //비회원만
            $pdo->query("delete from {$tbl['cart']} where `reg_date`<$del_time and `member_no`='0'"); //일반 장바구니
            if ($cfg['use_sbscr'] == 'Y') {
                $pdo->query("delete from {$tbl['sbscr_cart']} where `reg_date`<$del_time and `member_no`='0'");//정기배송 장바구니
            }
		}else{
            //전체
            $pdo->query("delete from {$tbl['cart']} where `reg_date`<$del_time");//일반 장바구니
            if ($cfg['use_sbscr'] == 'Y') {
                $pdo->query("delete from {$tbl['sbscr_cart']} where `reg_date`<$del_time");//정기배송 장바구니
            }
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void();
	' +----------------------------------------------------------------------------------------------+*/
	function extDateReady() {
		return;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void setRevPt(int 상품번호) - 상품의 리뷰 요약정보 수정
	' +----------------------------------------------------------------------------------------------+*/
	function setRevPt($pno) {
		global $tbl, $admin, $pdo;

		$rev = $pdo->assoc("select count(*) as rev_cnt, sum(`rev_pt`) as rev_pt from {$tbl['review']} where stat in (2,3) and `pno`='$pno'");
		$rev_avg = ($rev['rev_cnt'] > 0) ? round($rev['rev_pt'] / $rev['rev_cnt'], 1) : 0;

		$pdo->query("update {$tbl['product']} set rev_cnt='{$rev['rev_cnt']}', rev_avg='$rev_avg', rev_total='{$rev['rev_pt']}' where no='$pno'");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int addZero(int 숫자[, len 자리수]) - 숫자의 디지트수 유지
	' +----------------------------------------------------------------------------------------------+*/
	function addZero($code, $len = 2) {
		return sprintf("%0{$len}d", $code);
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void deletePrdImage(array 데이터필드, int 시작번호[, int 끝번호]) - 게시물의 첨부이미지 일괄 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function deletePrdImage($data,$start=1,$finish=null) {
		global $cfg,$_use,$file_server;
		if(!$data['updir']) return;
		if($_use['file_server'] == 'Y'){
			include_once $GLOBALS['engine_dir'].'/_engine/include/file.lib.php';
			if(fsConFolder($data['updir'])) $fsDel="Y";
		}
		$updir=$GLOBALS['root_dir'].'/'.$data['updir'].'/';
		if(!$cfg['add_prd_img']) $cfg['add_prd_img'] = 3;
		if($finish === null) $finish = $cfg['add_prd_img'];
		for($ii = $start; $ii <= $finish; $ii++) {
			if(!$data['upfile'.$ii]) continue;
			if($data['ori_no'] > 0 && !preg_match('/^_data\/temp/', $data['updir'])) continue;
			deleteAttachFile($data['updir'], $data['upfile'.$ii]);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void deleteAttachFile(string 업로드디렉토리, string 업로드파일명) - 첨부이이미 삭제(서버 자동 검색)
	' +----------------------------------------------------------------------------------------------+*/
	function deleteAttachFile($updir, $file) {
		global $_use, $root_dir, $root_url, $engine_dir, $file_server, $matched_server;

		$file = basename($file);
		fsConFolder($updir);

		if($_use['file_server'] == 'Y' && $matched_server) {
			include_once $engine_dir.'/_engine/include/file.lib.php';
			$updir = preg_replace("@($root_dir)|($root_url)@", "", $updir);
			foreach ( $matched_server as $file_server_num) {
				$fs_ftp_con = "";
				fileServerCon($file_server_num);
				fsDeleteFile($updir, $file);
			}
		} else {
			if($updir && $file) {
				@unlink($root_dir.'/'.$updir.'/'.$file);
			}
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int getDataAuth2(array 게시물[, boolean 처리방식]) - 게시물에 대한 접근권한 체크
	' +----------------------------------------------------------------------------------------------+*/
	function getDataAuth2($data,$after = '') {
		global $member, $admin;
		if($admin['no']) $res = 1; // 관리자
		elseif(empty($_SESSION['review_auth']) == false && $_SESSION['review_auth'] == $data['no']) $res = 2; // 비밀번호 인증 완료
		elseif(!$data['member_no']) $res = 3; // 비회원
		elseif($member['no'] && $data['member_no'] && $data['member_no'] == $member['no']) $res = 2; // 회원 자료
		else $res=0;
		if($after && !$res) msg(__lang_common_error_noperm__, '/', 'parent');
		return $res;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  mixed disBanner(int 배너번호, int 출력방식) - 디자인배너 출력
	' +----------------------------------------------------------------------------------------------+*/
	function disBanner($key,$tp="",$w="",$h="",$fvs="",$wmd="") {
		global $now, $tbl, $cfg, $root_url, $root_dir, $_preload_banner, $pdo;

		if(is_array($_preload_banner) == false) {
			//배너 기간 추가 사용 유무
			$_addWhere = "";
			if(fieldExist($tbl['banner'],'start_date')) {
				$_ymdh = ($cfg['banner_minute'] == "Y") ? date("Y-m-d-H-i",$now) : date("Y-m-d-H",$now);
				$_addWhere = " and ((`start_date`<='$_ymdh' and `finish_date`>='$_ymdh') or (start_date='' and finish_date=''))";
			}

			$res = $pdo->iterator("select * from {$tbl['banner']} where `use_banner`='Y' $_addWhere ");
            foreach($res as $banner) {
				$_preload_banner[$banner['no']] = $banner;
				if($banner['updir'] && !$_preload_banner['url']) {
					$_preload_banner['url'] = getFileDir($banner['updir']);
				}
			}
		}
		$data = $_preload_banner[$key];
		if(!$data['no']) return;

		$file_url = $_preload_banner['url'];
		if($cfg['use_icb_storage'] == 'Y' && $data['upurl']) {
			$file_url = $data['upurl'];
		}
		$data['img']="$file_url/$data[updir]/$data[upfile1]";
		$data['img_on']="$file_url/$data[updir]/$data[upfile2]";
		$data['img']=str_replace("$file_url//","$file_url/",$data['img']);
		$data['img_on']=str_replace("$file_url//","$file_url/",$data['img_on']);
		$img_path = ($file_url == $root_url) ? "$root_dir/$data[updir]/$data[upfile1]" : $data['img'];
		if($data['obj_type']==2) { // 플래시
			if(!$w || !$h) {
				$size = @getimagesize($img_path);

				if(!$size[0]) { // getimagesize 지원 안할 경우
					if($w && $h) {
						$size[0] = $w;
						$size[1] = $h;
					}
				}
			} else {
				$size = array ($w,$h);
			}
			$data['src'] = "<div id=\"flashAreaWM".$key."\"  style=\"z-index:".(5+$key)."\"></div>";
			$data['src'] .= "<script language=\"JavaScript\">flashMovie('flashAreaWM".$key."','$data[img]',$size[0],$size[1],'$fvs','$wmd'); </script>";
		} else {
			if($data['link_type'] == 4 && $data['link']) $umap=" usemap=\"#$data[link]\""; else $umap="";
			if($data['obj_type'] == 3) {	// 마우스 오버의 경우
				$data['src'] = "<img src=\"$data[img]\" border=0 onmouseover=this.src=\"$data[img_on]\"; onmouseout=this.src=\"$data[img]\"; $umap>";
			} elseif($data['obj_type'] == 4) {
				$data['src'] = stripslashes($data['maptext']);
				if(strip_tags($data['src']) == $data['src']) $data['src'] = nl2br($data['src']);
			} else {
				$data['src'] = "<img src=\"$data[img]\" border=0 $umap>";
			}

			if($data['link'] && $data['link_type'] <> 4) {
				if($data['link_type']==2) {
					$data['link'] = (preg_match('/[A-Z]/', $data['link'])) ? $data['link'] : $pdo->row("select hash from {$tbl['product']} where no = '{$data['link']}'");
					$data['link']=$root_url.'/shop/detail.php?pno='.$data['link'];
				} elseif($data['link_type'] == 3) {
					$data['link']="$root_url/shop/big_section.php?cno1=".$data['link'];
				}

				if($data['target']) {
					$tgt="target=\"$data[target]\"";
				}
				$data['src']="<a href=\"$data[link]\" $tgt onfocus=this.blur()>".$data['src']."</a>";
			}

			if($data['link_type']== 4 && $data['link'] && $data['maptext']){
				$data['src'] .= $data['maptext'];
			}
		}

		if($tp==1) {
			return $data;
		} else {
			return $data['src'];
		}
	}


    /**
     * array skinBanner(string 스킨명, int 배너번호, string 리턴데이터타입)
     **/
    function getSkinBanner($skin, $no = null, $return = null)
    {
        global $root_dir, $skinbanner_cfg;

        $skin = str_replace('/', '', $skin);

        // 현재 스킨의 배너 설정 읽어 오기 (세션당 한번만 읽음)
        if (isset($skinbanner_cfg) == false) {
            $skin_folder = $root_dir.'/_skin/'.$skin;
            if (file_exists($skin_folder.'/banner.json') == true) {
                $skinbanner_cfg = file_get_contents($skin_folder.'/banner.json');
                $skinbanner_cfg = json_decode($skinbanner_cfg, true);
                if (is_array($skinbanner_cfg) == false) {
                    $skinbanner_cfg = array();
                }
            } else {
                $skinbanner_cfg = array();
            }
        }

        if (is_null($no) == false && isset($skinbanner_cfg[$no]) == true && is_array($skinbanner_cfg[$no]) == true) {
            $data = $skinbanner_cfg[$no];

            $data['no'] = $no;
            $data['updir'] = '_skin/'.$skin.$data['updir'];
            $data['src'] = getListImgURL('_data/internal_banner/'.$skin, $data['upfile1']);
            $data['src_local'] = getListImgURL($data['updir'], $data['upfile1']);

            if ($return == 'html') {
                if ($data['start_date'] && $data['finish_date']) {
                    if (strtotime($data['start_date']) < time()) $data['use_banner'] = 'N';
                    if (strtotime($data['finsih_date']) > time()) $data['use_banner'] = 'N';
                }
                if ($data['use'] == 'N') return;

                if ($data['obj_type'] == '4') { // 텍스트형 배너
                    $html = $data['maptext'];
                } else {
                    $html = "<img src='{$data['src']}'>";
                }
                if ($data['link']) {
                    $html = "<a href=\"{$data['link']}\" target=\"{$data['target']}\">$html</a>";
                }
                return $html;
            }
            return $data;
        }

        return $skinbanner_cfg;
    }

	/* +----------------------------------------------------------------------------------------------+
	' |  array shortCut(array 바로가기데이터) - 바로가기상품의 원본데이터 반환
	' +----------------------------------------------------------------------------------------------+*/
	function shortCut($data) {
		global $tbl, $member, $cfg, $pdo;

		$data['sell_prc'] = parsePrice($data['sell_prc']);
		$data['normal_prc'] = parsePrice($data['normal_prc']);

		if($data['parent'] > 0) return $data;

		if($data['wm_sc'] > 0) {
			$tmp = get_info($tbl['product'], 'no', $data['wm_sc']);
			$tmp['parent']=$tmp['no'];
			if($GLOBALS['shortcut_cart'] != true) {
				$tmp['no']=$data['no'];
				$tmp['hash']=$data['hash'];
			}
			$tmp['big']=$data['big'];
			$tmp['mid']=$data['mid'];
			$tmp['small']=$data['small'];
			$tmp['depth4']=$data['depth4'];
			$tmp['reg_date']=$data['reg_date'];
			$tmp['edt_date']=$data['edt_date'];
			$tmp['free_dlv']=$data['free_dlv'];

			if($data['member_no'] || $data['guest_no']) { //장바구니
				$tmp['cno']=$data['cno'];
				$tmp['pno']=$data['pno'];
				$tmp['price_no']=$data['price_no'];
				$tmp['option']=$data['option'];
				$tmp['option_prc']=$data['option_prc'];
				$tmp['buy_ea']=$data['buy_ea'];
				$tmp['reg_date']=$data['reg_date'];
				$tmp['etc']=$data['etc'];
				$tmp['etc2']=$data['etc2'];
				$tmp['anx_no']=$data['anx_no'];
				if($data['wno']) {
					$tmp['wno']=$data['wno'];
				}
			}
			//휴지통
			if($cfg['use_trash_prd'] == "Y") {
				$tmp['del_date']=$data['del_date'];
				$tmp['del_admin']=$data['del_admin'];
			}

			$data = $tmp;
			$data['sc'] = '바로가기';
		} else {
			$data['parent']=$data['no'];
		}

		if($data['hit_order'] < 1) $data['hit_order']=0;
		if($data['hit_sales'] < 1) $data['hit_sales']=0;
		if(!is_admin() && $member['no'] && $cfg['group_price'.$member['level']] == 'Y' && $data['sell_prc'.$member['level']]) {
			if($data['sell_prc'.$member['level']] > 0) {
				$data['sell_prc'] = $data['sell_prc'.$member['level']];
			}
		}
		if($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) {
			$data['milage'] = getPercentage($data['sell_prc'], $cfg['milage_type_per'], -1);
		}

		// 위시리스트 담김 여부
		if(is_array($member['wishlist'])) {
			$data['is_wish'] = in_array($data['parent'], $member['wishlist']) ? 'on' : '';
		}

		// 타임세일 세트 읽기
		if($data['ts_use'] && $data['ts_set'] > 0) {
			global $_ts_set_cache;
			$ts = ($_ts_set_cache[$data['ts_set']]) ? $_ts_set_cache[$data['ts_set']] : $pdo->assoc("select ts_use, ts_saletype, ts_dates, ts_datee, ts_saleprc, ts_event_type, ts_saletype, ts_cut from {$tbl['product_timesale_set']} where no='{$data['ts_set']}'");
			$_ts_set_cache[$data['ts_set']] = $ts;
			if($ts) {
				$ts['ts_dates'] = strtotime($ts['ts_dates']);
				$ts['ts_datee'] = strtotime($ts['ts_datee']);
				$data = array_merge($data, $ts);
			}
		}

		return $data;
	}

	function left($string, $length) {
		return substr($string, 0, $length);
	}

	function right($string, $length) {
		return substr($string, -$length, $length);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string setMktScript() - 광고스크립트 삽입
	' +----------------------------------------------------------------------------------------------+*/
	function setMktScript($_file_name) {
		global $tbl, $engine_dir, $cfg, $member, $prd, $_cno1, $ord, $total_buy_ea, $dlv_prc, $_mkt_data_list, $cart, $cart_sum_price_c, $dlv_prc, $total_order_price_c, $root_url, $pdo;

		if($cfg['use_mkt_script'] != 'Y') return;

		$page = array(
			'header' => 'scr_header',
			'top' => 'scr_top',
			'footer' => 'scr_bottom',
			'shop_detail.php' => 'scr_detail',
			'shop_cart.php' => 'scr_cart',
			'shop_order_finish.php' => 'scr_order',
			'member_join_step3.php' => 'scr_join'
		);

		$_page = $page[$_file_name];
		if(!$_page) return $_content_content;
		$sex = array("남","여");
		$sex_mf = array("M","F");
		$sex_ab = array("1","2");
		$orddlv_prc = explode(".", $ord['dlv_prc']);
		$prd_img = ($prd['upfile2']) ? getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile2'] : $root_url.$cfg['noimg2'];
		$str_code = array();
		switch($_page) {
				case 'scr_header' :
				$str_code = array(
					'{회원아이디}' => stripslashes($member['member_id']),
					'{회원나이}' => $member['birth'],
					'{성별(M/F)}' => str_replace($sex, $sex_mf, $member['sex']),
					'{성별(남/여)}' => $member['sex'],
					'{성별(1/2)}' => str_replace($sex, $sex_ab, $member['sex']),
				);
			break;
				case 'scr_top' :
				$str_code = array(
					'{회원아이디}' => stripslashes($member['member_id']),
					'{회원나이}' => $member['birth'],
					'{성별(M/F)}' => str_replace($sex, $sex_mf, $member['sex']),
					'{성별(남/여)}' => $member['sex'],
					'{성별(1/2)}' => str_replace($sex, $sex_ab, $member['sex']),
				);
			break;
				case 'scr_bottom' :
				$str_code = array(
					'{회원아이디}' => stripslashes($member['member_id']),
					'{회원나이}' => $member['birth'],
					'{성별(M/F)}' => str_replace($sex, $sex_mf, $member['sex']),
					'{성별(남/여)}' => $member['sex'],
					'{성별(1/2)}' => str_replace($sex, $sex_ab, $member['sex']),
				);
			break;
			case 'scr_detail' :
				$str_code = array(
					'{상품코드}' => $prd['code'],
					'{상품일련번호}' => $prd['parent'],
					'{시스템코드}' => $prd['hash'],
					'{상품명}' => $prd['name'],
					'{상품가격}' => str_replace(",", "", $prd['sell_prc_str']),
					'{카테고리명}' =>$_cno1['name'],
					'{상품이미지}' =>$prd_img,
				);
			break;
			case 'scr_cart' :
				$str_code = array(
					'{상품금액}' => $cart_sum_price_c,
					'{총주문금액}' => $total_order_price_c,
					'{배송비}' =>$dlv_prc,
				);
				$str_code_list = array(
					'{상품명}' => 'name',
					'{상품금액}' => 'sell_prc',
					'{총상품금액}' => 'sum_sell_prc_c',
					'{주문수량}' => 'buy_ea',
					'{상품코드}' => 'code',
					'{시스템코드}' => 'hash',
				);
			break;
			case 'scr_order' :
				$str_code = array(
					'{상품금액}' => str_replace(",", "", $ord['prd_prc']),
					'{총주문금액}' => str_replace(",", "", $ord['pay_prc']),
					'{배송비}' =>  $orddlv_prc[0],
					'{주문상품수량}' => $total_buy_ea,
					'{주문번호}' => $ord['ono'],
				);
				$str_code_list = array(
					'{상품명}' => 'name',
					'{상품금액}' => 'sell_prc',
					'{총주문금액}' => 'total_prc',
					'{주문수량}' => 'buy_ea',
					'{상품코드}' => 'code',
					'{시스템코드}' => 'hash',
				);
			break;
			case 'scr_join' :
				$str_code = array(
					'{회원아이디}' => stripslashes($member['member_id']),
					'{회원나이}' => $member['birth'],
					'{성별(M/F)}' => str_replace($sex, $sex_mf, $member['sex']),
					'{성별(남/여)}' => $member['sex'],
					'{성별(1/2)}' => str_replace($sex, $sex_ab, $member['sex']),
				);
			break;
		}
		$device = ($_SESSION['browser_type'] == 'mobile') ? 'mb' : 'pc';
		$res = $pdo->iterator("select * from {$tbl['mkt_script']} where use_yn='Y'");
		foreach ($res as $mkt) {
			$_code = '';
			if($mkt[$_page]) $_code .= stripslashes($mkt[$_page]);
			if($mkt[$_page.'_'.$device]) $_code .= stripslashes($mkt[$_page.'_'.$device]);
			if(!$_code) continue;

			foreach($str_code as $key => $val) {
				$_code = str_replace($key, $val, $_code);
			}
			$code .= "<!-- mkt script '$mkt[name]' $_page start-->\n".$_code."\n<!--mkt script '$mkt[name]' $_page end-->\n";
		}

		if($str_code_list) {
			$res = $pdo->iterator("select * from {$tbl['mkt_script']} where use_yn='Y'");
            foreach ($res as $mkt) {
				$listcode = '';
				foreach($_mkt_data_list as $pdata) {
					$pdata['sum_sell_prc_c'] = parsePrice($pdata['sum_sell_prc_c']);
					$pdata['sell_prc'] = parsePrice($pdata['sell_prc']);
					$pdata['total_prc'] = parsePrice($pdata['total_prc']);
					$tmp = '';
					if($mkt[$_page.'list']) $tmp .= stripslashes($mkt[$_page.'list']);
					if($mkt[$_page.'list_'.$device]) $tmp .= stripslashes($mkt[$_page.'list_'.$device]);
					if(!$tmp) continue;
					foreach($str_code_list as $key => $val) {
						$tmp = str_replace($key, $pdata[$val], $tmp);
					}
					$listcode .= $tmp."\n";
				}
				$code .= "<!--mkt script '$mkt[name]' List start-->\n".$listcode."\n<!--mkt script '$mkt[name]' List end-->\n";
			}
		}

		return $code;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getPageName() - 현재페이지 이름 출력
	' +----------------------------------------------------------------------------------------------+*/

	function getPageName(){
		global $root_dir, $engine_dir, $_content_add_info, $config, $root_url, $_file_name;
		if(file_exists($root_dir."/_config/title_name.php")){
			include $root_dir."/_config/title_name.php";
			$mod_title=1;
		}else{
			include $engine_dir."/_manage/design/template_name.php";
		}
		$pgname=$sn=$_SERVER['SCRIPT_NAME'];
		$pgname=$_pgname=preg_replace("/^\/(.*)\.(.*)/", "$1", $pgname);
		$pgname=explode("/", $pgname);

		$joint=$mod_title ? $_page_title['joint'] : " &gt; ";
		$home=$mod_title ? $_page_title['home'] : "Home";
		$member=$mod_title ? $_page_title['member'] : "Membership";
		$mypage=$mod_title ? $_page_title['mypage'] : "Mypage";

		$r=$home;
		if($sn == "/content/content.php"){
			$_r=$mod_title ? $_page_sub_title["content/".$_GET['cont']] : $dir_sub_arr[$pgname[1]][$_GET['cont'].".php"];
			if(!$_r){
				$_r=$_content_add_info[$_GET['cont']]['name'];
			}
		}elseif($sn == "/shop/big_section.php" || $sn == "/shop/detail.php"){
			$_r=getPrdPath($joint);
		}elseif(strchr($sn, "board") && $_GET['db']){
			$_r=$config[title];
		}else{
			if(${$pgname[1]}) $r .= $r ? $joint.${$pgname[1]} : ${$pgname[1]};
			$_r .= $mod_title ? $_page_sub_title[$_pgname] : $dir_sub_arr[$pgname[1]][$pgname[2]];
		}
		if($_r) $r .= $r ? $joint.$_r : $_r;
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getPrdPath(string 구분자, string 링크 제외 분류레벨 101@102, string 텍스트색상) - 현재페이지 상품 분류 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getPrdPath($splt = ' &gt; ', $exc_level = '', $Lcolor = '') {
		global $_cp,$root_url,$prd,$pno,$ctype,$tbl,$cno1, $cfg, $pdo;

		$cno1 = numberOnly($cno1);
		$r="";
		if($ctype=="4") {
			$name=getCateName($prd[xbig]);
			$r.="<a href=\"".$root_url."/shop/big_section.php?cno1=".$prd[xbig]."\">$name</a>";
			$r.=$splt;
		}
		else {
			$jj=1;
			$level = $pdo->row("select `level` from {$tbl['category']} where `no`='$cno1'");
			for($ii=101; $ii<=(100+$cfg['max_cate_depth']); $ii++) {
				if($_cp[$ii]) {
					$Ffc=$Fbc="";
					if($Lcolor && !$pno){
						$clevel=$pdo->row("select `level` from {$tbl['category']} where `no`='$_cp[$ii]'");
						if($level && $level == $clevel){ $Ffc="<font color='$Lcolor'>"; $Fbc="</font>"; }
					}
					$name=getCateName($_cp[$ii]);
					if(preg_match("/$ii/",$exc_level)) { // '링크 제외' 분류 레벨이 아닐 경우
						$r.=$name;
					}
					else {
						$r.="<a href=\"".$root_url."/shop/big_section.php?cno1=".$_cp[$ii]."\" class=\"prdpath".$jj."\">".$Ffc.$name.$Fbc."</a>";
					}
					$r.=$splt;

					$jj++;
				}
			}
		}
		if($pno && $prd['no']) {
			$Ffc=$Fbc="";
			if($Lcolor){ $Ffc="<font color='$Lcolor'>"; $Fbc="</font>"; }
			$r.="<a href=\"$root_url/shop/detail.php?pno=$pno\" class=\"prdpath3\">".$Ffc.$prd['name'].$Fbc."</a>";
		}
		else {
			$r=left($r,strlen($r)-strlen($splt));
		}
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getCateName(int 카테고리번호) - 카테고리 이름 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getCateName($cno) {
		global $tbl, $_cache_catename, $pdo;

		if($_cache_catename[$cno]) {
			return $_cache_catename[$cno];
		}

		$cno = numberOnly($cno);
		$r = $pdo->row("select name from {$tbl['category']} where no=:cno", array(
            ':cno' => $cno
        ));
		$r = $_cache_catename[$cno] = stripslashes($r);

		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getMemberIcon(int 회원번호, string 회원아이디) - 회원그룹 아이콘 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getMemberIcon($mno,$mid) {
		global $tbl,$root_dir,$root_url,$gimg_check,$_member_group_cache, $pdo;

		if($mno < 1) return '';
		//msg($mid);
		if(!isset($gimg_check)) { // 아이콘 사용여부 체크
			$gimg_check = $pdo->row("select count(*) from {$tbl['member_group']} where `upfile1`!=''");
		}
		if($gimg_check==0) {
			return;
		}
		$level=$pdo->row("select `level` from {$tbl['member']} where `no`='$mno' and `member_id`='$mid'");
		if ($level < 1) return '';
		if(is_array($_member_group_cache) && array_key_exists($level,$_member_group_cache)) { // 배열에 넣어 뒀을 경우
			$gimg=$_member_group_cache[$level];
		}
		else {
			$gr = $pdo->assoc("select updir, upfile1 from {$tbl['member_group']} where no = '$level'");
			$gimg=($gr['upfile1']) ? "<img src=\"".getListImgURL($gr['updir'], $gr['upfile1'])."\" border=0 align=\"absmiddle\" class=\"_member_icon\"> " : "";
			$_member_group_cache[$level]=$gimg;
		}
		return $gimg;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string autolink(string 텍스트) - 텍스트에 포함된 URL에 자동립크를 삽입
	' +----------------------------------------------------------------------------------------------+*/
	function autolink($str) {
		// URL 치환
		$homepage_pattern = "/([^\"\'\=\>])(mms|http|HTTP|ftp|FTP|telnet|TELNET|https)\:\/\/(.[^ \n\<\"\']+)/";
		$str = preg_replace($homepage_pattern,"\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", " ".$str);

		// 메일 치환
		$email_pattern = "/([ \n]+)([a-z0-9\_\-\.]+)@([a-z0-9\_\-\.]+)/";
		$str = preg_replace($email_pattern,"\\1<a href=mailto:\\2@\\3>\\2@\\3</a>", " ".$str);

		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean ordStatLogw(string 주문번호, int 변경되는상태[, string 자동처리여부][, string 분리주문서코드) - 주문상태변경로그 저장
	' +----------------------------------------------------------------------------------------------+*/
	function ordStatLogw($ono,$stat, $system="N", $spt = null, $content=null){
		global $tbl,$member, $admin,$now,$data, $pdo;

		if(is_array($content)) {
			$payment_no = numberOnly($content['payment_no']);
			$pno = implode('@', $content['pno']);
			if($pno) $pno = '@'.$pno.'@';
			$content = $content['content'];
		}

		if($GLOBALS['exec'] == 'OrderGetCheckout' || $GLOBALS['exec'] == 'OrderGetSmartstore') { // 체크아웃, 스마트스토어에서 주문서 가져올때 로그 겹치지 않도록 처리
			$ori_stat = $pdo->row("select stat from {$tbl['order_stat_log']} where ono='$ono' order by no desc limit 1");
			if($ori_stat == $stat) {
				$erpListener = $GLOBALS['erpListener'];
				if(is_object($erpListener)) {
					$erpListener->setOrder($ono);
				}
				return;
			}
		}

		ordChgHold($ono); // stat 1~3 까지만 배송보류(postpone_yn)

		if(defined('__CRON_SCRIPT__')) $system = 'Y';

		$content = addslashes($content);

		if($GLOBALS['mode'] == 'erp_delivery') $member['name'] = 'erpsafedlv';
		$sql="insert into `$tbl[order_stat_log]`(`ono`, `spt`, `stat`, `ori_stat`, `member_id`, `member_no`, `member_name`, `admin_id`, `admin_no`, `reg_date`, `system`, content, pno,payment_no) values('$ono', '$spt', '$stat', '$data[stat]', '$member[member_id]', '$member[no]', '$member[name]', '$admin[admin_id]', '$admin[no]', '$now', '$system', '$content', '$pno', '$payment_no')";
		$r=$pdo->query($sql);

		if($stat > 11) {
			$sql2="update {$tbl['order']} set ext_date='$now' where ono='$ono'";
			$pdo->query($sql2);
		}

		$erpListener = $GLOBALS['erpListener'];
		if(is_object($erpListener)) {
			$erpListener->setOrder($ono);
		}

		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array mainPoll(void) - 메인 설문조사 데이터 반환
	' +----------------------------------------------------------------------------------------------+*/
	function mainPoll(){
		 global $tbl, $pdo, $poll_items;
		 $poll = $pdo->assoc("select * from {$tbl['poll_config']} where 1 order by no desc limit 1");
		 if($poll == false) return false;

		 $poll_items = $pdo->iterator("select * from {$tbl['poll_item']} where ref='{$poll['no']}' order by sort");
		 $poll['title'] = stripslashes($poll['title']);
		 return $poll;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array mainPollItem(void) - 메인 설문의 아이템 반환
	' +----------------------------------------------------------------------------------------------+*/
	function mainPollItem(){
		 global $tbl,$poll,$poll_items;

		 $item = $poll_items->current();
         $poll_items->next();
		 if($item['no'] == false) {
             unset($poll_items);
             return false;
         }
		 $item['title'] = stripslashes($item['title']);

		 return $item;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string encode_jumin(array 회원데이터) - 주민등록번호 뒷자리를 인코딩
	' +----------------------------------------------------------------------------------------------+*/
	function encode_jumin($data) {
		global $cfg, $tbl;
		if(strlen($data) < 15 && $cfg[jumin_encode] != "N" && $cfg[join_jumin_use] != "N"){
			$data = explode('-', $data);
			$data = $data[0]."-".substr($data[1],0,1).md5(substr($data[1],1));
		}
		return $data;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean checkBizNo(string 사업자번호) - 사업자번호 형식 검증
	' +----------------------------------------------------------------------------------------------+*/
	function checkBizNo($num=""){
		$num=trim($num);
		$num=numberOnly($num);
		$sum=0;
		$chkvalue=array("1","3","7","1","3","7","1","3","5");

		if(!$num || strlen($num) != 10 || $num == "0000000000") return false;

		for($ii=0; $ii<9; $ii++){
			$sum += $num[$ii]*$chkvalue[$ii];
		}

		$sum += ($num[8]*5)/10;
		$sidliy=$sum%10;
		$sidchk=0;

		if($sidliy != 0) $sidchk=10-$sidliy;
		else $sidchk=0;

		if($sidchk != $num[9]) return false;

		return true;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void javac(string 스크립트내용) - <script></script> 줄임
	' +----------------------------------------------------------------------------------------------+*/
	function javac($script) {
		echo "<script type='text/javascript'>$script</script>";
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void alert(string 출력할 메시지) - javascript window.alert 함수 줄임
	' +----------------------------------------------------------------------------------------------+*/
	function alert($msg) {
		printAjaxHeader();

		javac("window.alert(\"$msg\");");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string php2java(string 텍스트) - 자바스크립트에 바로 사용할시 오류를 발생시키는 문자열을 처리
	' +----------------------------------------------------------------------------------------------+*/
	function php2java($content) {
		$content = stripslashes($content);
		$content = str_replace("\r", "\\r", $content);
		$content = str_replace("\n", "\\n", $content);
		$content = str_replace("\"", "\\\"", $content);
		return $content;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getMembercon(string 회원아이디[, string 추가attribute][,string 대체이미지) - 회원전용 아이콘 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getMembercon($id, $attr = null, $noimg = null) {
		global $root_dir, $root_url;
		if(file_exists($root_dir."/_data/mem_icons/$id.jpg")) return "<img src='$root_url/_data/mem_icons/$id.jpg' $attr>";
		else {
			if($noimg) return "<img src='$noimg' $attr>";
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean prdStatLogw(int 상품번호, int 변경할상태[, int 원상태]) - 주문상태변경로그 저장
	' +----------------------------------------------------------------------------------------------+*/
	function prdStatLogw($pno,$stat,$ori_stat="", $mng = null){
		global $tbl, $now, $ono, $engine_dir, $pdo;
		if(is_array($mng) == false) {
			$mng = $GLOBALS['admin'];
		}
		return $pdo->query(
				"insert into {$tbl['product_stat_log']} (pno, stat, ori_stat, admin_id, admin_no, ono, reg_date)
				values ('$pno', '$stat', '$ori_stat', '{$mng['admin_id']}', '{$mng['no']}', '$ono', '$now')"
		);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string memberIdOutput(string 회원아이디) - 회원아이디 일부 마스킹
	' +----------------------------------------------------------------------------------------------+*/
	function memberIdOutput($member_id = ''){
		global $admin, $member, $cfg;

		if(!$member_id) return;
		if($admin['level'] > 0 || $member['level'] == 1) return $member_id;

		$_tmp = '';
		$len = strlen($member_id);
		for($ii=0; $ii < $len; $ii++){
			$_tmp .= ($ii > 2) ? "*" : substr($member_id, $ii, 1);
		}
		return $_tmp;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string strip_domain(string 도메인) - 도메인의 프로토콜부분 제거
	' +----------------------------------------------------------------------------------------------+*/
	function strip_domain($url) {
		$url = preg_replace("@^[^:]+://@", "", $url);
		$url = substr($url, strpos($url, "/"));
		$url = preg_replace("@^/?~[^/]+/?@", "", $url);

		return $url;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean filterContent(string 패턴, string 텍스트) - 문장 패턴을 받아 일치하는 내용이 있는지 확인
	' +----------------------------------------------------------------------------------------------+*/
	function filterContent($pattern, $content) {
		if(!$pattern) return false;

		if(!is_array($pattern))	$pattern = explode(",", $pattern);
		foreach ( $pattern as $key => $val) {
			$val = trim(str_replace('/', '\/', $val));
			if(preg_match("/$val/i", $content)) { // 금지어가 검색될 경우 아스키코드로 다시 확인
				if(!$content_ord) $content_ord = content_ord($content);
				if(preg_match('/'.content_ord($val).'/i', $content_ord)) return $val;
			}
		}
		return false;
	}

	function content_ord($content) {
		$hangul = 0;
		$content_ord = "";
		$content = trim($content);

		$clen = strlen($content);
		for ($i = 0; $i < $clen; $i++) {
			$chr = ord($content[$i]);
			if ($chr > 127) $hangul++;
			$space =  ($chr > 127 && $hangul % 3 != 0) ? "" : " ";
			$content_ord .= $chr.$space;
		}

		return $content_ord;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void makeOrderLog(string 주문번호[, string 페이지명]) - 주문로그를 파일로 저장
	' +----------------------------------------------------------------------------------------------+*/
	function makeOrderLog($ono, $incname = "")
    {
        // 사용 안함
	}

    function startOrderLog($ono, $incname = '')
    {
        global $log_instance;

        if (class_exists('OrderLog') == false) {
            require_once __ENGINE_DIR__.'/_engine/include/classes/common/OrderLog.php';
        }

        if (is_object($log_instance) == true) {
            if ($log_instance->getOrderNo() != $ono) { // 다른 주문번호로 재호출
                unset($log_instance);
            } else { // 이미 호출 됨
                $log_instance->writeln('', '*** '.$incname.' ***');
                return;
            }
        }

        $log_instance = new Wing\common\OrderLog($ono);
        $log_instance->writeln('', '*** '.$incname.' ***');
        $log_instance->getRequests();

        return;
    }

	function makePGLog($ono, $title, $adddata = null) {
        global $log_instance;

        startOrderLog($ono, '*** '.$title.' ***');

        if ($adddata) {
            $log_instance->writeln(print_r($adddata, true), 'tx data');
        }
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean is_admin(void) - 관리자 로그인여부 리턴
	' +----------------------------------------------------------------------------------------------+*/
	function is_admin() {
		$in_admin = false;
		$include = get_included_files();
		foreach($include as $key => $val) {
            $val = str_replace(DIRECTORY_SEPARATOR, '/', $val);
			if(preg_match('/\/_manage\/manage\.lib\.php$/', $val)) $in_admin = true;
		}
		return $in_admin;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string setSmsHistory(string 주문번호, int 변경할주문상태) - 주문SMS 중복발송 제어
	' +----------------------------------------------------------------------------------------------+*/
	function setSmsHistory($ono, $stat) {
		global $tbl, $cfg, $pdo;
		if(!$ono || !$stat) return;

		$history_old = $pdo->row("select sms_history from {$tbl['order']} where ono='$ono'");
		$history_new = $history = explode('@', $history_old);

		$history_new[] = $stat;
		sort($history_new);
		$history_new = array_unique($history_new);
		$history_new = implode('@', $history_new);
		if($history_new != $history_old) $pdo->query("update {$tbl['order']} set sms_history='$history_new' where ono='$ono'");

		$result = ($cfg['order_sms_history'] == 'Y') ? array_search($stat, $history) : false;
		$return = ($result === false) ? true : false;

		return $return;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  string comm(string getURL[, string postValue]) - curl 로 페이지에 접근하여 결과를 반환
	' +----------------------------------------------------------------------------------------------+*/
	function comm($url, $post_args = null,$timeout=0, $custom_header = null) {
		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt($ch, CURLOPT_REFERER, 'mywisa.com');
			if($timeout > 0) curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
			if($post_args){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_args);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if($custom_header) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_header);
			}
			$result = curl_exec($ch);
			$test = curl_getinfo($ch);
			if($result === true || strpos($result, '<title>404 Not Found</title>') > 0) $result = '';
			curl_close($ch);
		} else {
			$host = preg_replace("/^https?:\/\//", "", $url);
			$host = preg_replace("/\/.*/", "", $host);

			if(!preg_match("/\?/",$url)) $post_args = '?'.$post_args;
			$addr = $url.'&'.$post_args;

			$fp = @fsockopen ($host, 80, $errno, $errstr, 30);
			if(!$fp) {
				echo "connection error to [$host]";
			} else {
			   fputs ($fp, "POST $addr HTTP/1.0\r\n\r\n");
			   $start = false;
			   while (!feof($fp)) {
					$part = trim(fgets($fp,128));
					if($start == true) $result .= $part;
					if(!$part) $start = true;
			   }
			}
		}
		return $result;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean compOr(int 원본값, int 비교값...) - 비교값 중 원본값과 일치하는 값이 하나 이상 있는지 반환
	' +----------------------------------------------------------------------------------------------+*/
	function compOr($source) {
		$args = func_get_args();
		$nums = func_num_args();

		for($i =1; $i < $nums; $i++) {
			if($source == $args[$i]) return true;
		}
		return false;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string orderAddFrm(int 추가필드번호, int 필드타입[, array 주문데이터]) - 주문서 추가필드 출력
	' +----------------------------------------------------------------------------------------------+*/
	function orderAddFrm($n, $ptype, $ord=array()) {
		global $_ord_add_info;

		$data=$_ord_add_info[$n];
		if(!$data) return;

		if($ptype==1){
			$r="<input type=\"hidden\"  name=\"add_info_style$n\" value=\"".$data['ncs']."::".$data['type']."::".inputText($data['name'])."\">";

			if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
				foreach($data['text'] as $key=>$val) {
					$ck="";
					if(($ord["add_info".$n]!="") && ((!strchr($ord["add_info".$n],"@") && $ord["add_info".$n]==$key) || strchr($ord["add_info".$n],"@".$key."@"))) {
						$ck="checked";
					}
					$_info_name=($data['type'] == "checkbox") ? "add_info".$n."[]" : "add_info".$n;
					$r.="<label><input type=\"$data[type]\" name=\"$_info_name\" id=\"add_info$n\" value=\"$key\" $ck> ".stripslashes($val)."</label> ";
				}
			}
			elseif($data['type']=="select") {
				$r="<select name=\"add_info".$n."\">";
				$r.="<option value=\"\" $ck>:: 선택 ::</option>";
				foreach($data[text] as $key=>$val) {
					$ck="";
					if($ord["add_info".$n]!="" && $ord["add_info".$n]==$key) {
						$ck="selected";
					}
					$r.="<option value=\"$key\" $ck>".stripslashes($val)."</option>";
				}
				$r.="</select>";
			}
			elseif($data['type']=="text") {
				$r.="<input type=\"text\" name=\"add_info".$n."\" value=\"".inputText($ord["add_info".$n])."\" size=\"$data[size]\" class=\"$data[class]\">";
			}
			elseif($data['type']=="textarea") {
				$r.="<textarea name=\"add_info".$n."\" class=\"$data[class]\">".stripslashes($ord["add_info".$n])."</textarea>";
			}
			elseif($data['type']=="date") {
				${'add_info'.$n} = explode(' ', $ord["add_info".$n]);
				$r.="<input type=\"text\" name=\"add_info".$n."\" value=\"".inputText(${'add_info'.$n}[0])."\" size=\"10\" class=\"input datepicker\">";
				if($data['format']==2){
					$r.=" <select name='add_info".$n."_h'>";
					$r.="<option value=''>--</option>";
					for($ii=0;$ii<=23;$ii++){
						if(${'add_info'.$n}[1])	$selected = checked(${'add_info'.$n}[1], $ii, 1);
						$r.="<option value='".$ii."' ".$selected.">".$ii."</option>";
					}
					$r.="</select>";
					$r.="시";
				}
			}
		}
		else{
			if($data['type']=="radio" || $data['type']=="checkbox" || $data['type']=="select") {
				$r="";
				foreach($data['text'] as $key=>$val) {
					$ck="";
					if(($ord["add_info".$n]!="") && ((!strchr($ord["add_info".$n],"@") && $ord["add_info".$n]==$key) || strchr($ord["add_info".$n],"@".$key."@"))) {
						if($r)	$r.=", ";
						$r.=stripslashes($val);
					}
				}
			}
			elseif($data['type']=="text" || $data['type']=="textarea") {
				$r=stripslashes($ord["add_info".$n]);
			}
			elseif($data['type']=="date") {
				$r=$ord["add_info".$n];
			}
		}

		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean putCoupon(int 쿠폰번호, array 대상회원) - 대상회원의 조건이 만족할 시 선택쿠폰 지급
	' +----------------------------------------------------------------------------------------------+*/
	function putCoupon($cpn, $member, $from = null) {
		global $tbl, $cfg, $pdo, $sms_replace;

		if(!$cpn['no'] || !$member['no']) return false;

		// 등급별 쿠폰 다운로드 권한 체크
		if($cpn['down_type'] == 'B' || $cpn['down_type'] == 'L' || $cpn['down_type'] == 'L2') {
			if($cpn['down_grade'] > 0) {
				if($cpn['down_gradeonly'] == 'Y' && $cpn['down_grade'] != $member['level']) return false;
				if($cpn['down_gradeonly'] != 'Y' && $cpn['down_grade'] < $member['level']) return false;
			}
		}

		// 발급갯수 제한
		if($cpn['release_limit'] == 2 && $cpn['release_limit_ea'] <= $cpn['down_hit']) return false;

		// 다운로드 제한
		if($cpn['download_limit'] == 2) {
			$now_date = date('Y-m-d', $GLOBALS['now']);
			if ($pdo->row("select count(*) from {$tbl['coupon_download']} where cno='{$cpn['no']}' and member_no='{$member['no']}' and ono = '' and (ufinish_date > '$now_date' or ufinish_date = '')") > 0) return false;

		}
		if($cpn['download_limit'] == 3){
			if($pdo->row("select count(*) from {$tbl['coupon_download']} where cno='{$cpn['no']}' and member_no='{$member['no']}'") >= $cpn['download_limit_ea']) return false;
		}

		// 발급일로부터 사용기간 제한
		if($cpn['udate_type'] == 3 && $cpn['udate_limit'] >= 0) {
			$cpn['ufinish_date']=date('Y-m-d', $GLOBALS['now']+($cpn['udate_limit']*86400));
		}

		if(strlen($cpn['weeks']) > 0) {
			$asql1 .= ",weeks";
			$asql2 .= ",'$cpn[weeks]'";
		}

		if($cpn['sale_prc_over']) {
			$asql1 .= ", sale_prc_over";
			$asql2 .= ", '$cpn[sale_prc_over]'";
		}

		if($cfg['use_partner_shop'] == 'Y') {
			$asql1 .= ", partner_type, partner_no, partner_fee";
			$asql2 .= ", '$cpn[partner_type]', '$cpn[partner_no]', '$cpn[partner_fee]'";
		}

		if($from) {
			$asql1 .= ", ono_from";
			$asql2 .= ", '$from'";
		}

		$member['name'] = addslashes($member['name']);

		// 발급 처리
		$now = time();
		$pdo->query("
			insert into {$tbl['coupon_download']}
				(`member_no`, `member_id`, `member_name`, `cno`, `name`, `device`, place, `sale_prc`, `prc_limit`, `sale_limit`, `ustart_date`, `ufinish_date`, `sale_type`, `down_date`, `use_date`, `ono`, `udate_type`, `stype`, `use_limit`, `pay_type` $asql1)
				values ('$member[no]', '$member[member_id]', '$member[name]', '$cpn[no]', '$cpn[name]', '$cpn[device]', '$cpn[place]', '$cpn[sale_prc]', '$cpn[prc_limit]', '$cpn[sale_limit]', '$cpn[ustart_date]', '$cpn[ufinish_date]', '$cpn[sale_type]', '$now', '', '', '$cpn[udate_type]', '$cpn[stype]', '$cpn[use_limit]', '$cpn[pay_type]' $asql2)"
		);
		$cno = $pdo->lastInsertId();
        if ($cno > 0) {
            $use_check = $pdo->row("select use_check from {$tbl['sms_case']} where `case`='38'");
            if($use_check == 'Y') {
                include_once __ENGINE_DIR__.'/_engine/sms/sms_module.php';

                if (defined('__NO_CPN_SMS__') == false) { // 생일 쿠폰일 경우 생일 문자 우선
                    $sms_replace['name'] = $member['name'];
                    $sms_replace['member_id'] = $member['member_id'];
                    $sms_replace['cpn_name'] = stripslashes($cpn['name']);
                    $sms_replace['cpn_finish_date'] = ($cpn['ufinish_date']) ? $cpn['ufinish_date'] : '무제한';
                    SMS_send_case(38, $member['cell']);
                }
            }
        }

		$erpListener = $GLOBALS['erpListener'];
		if(is_object($erpListener)) {
			$erpListener->setCoupon($cno);
		}

		return $cno;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string parseDateType(string 데이트포맷[, int 타임스탬프]) - 지정된 포맷에 따라 날짜표시
	' +----------------------------------------------------------------------------------------------+*/
	function parseDateType($data, $date = null) {
		$data = explode('@', $data);

		$data_array = array('Mon'=>'월', 'Tue'=>'화', 'Wed'=>'수', 'Thu'=>'목', 'Fri'=>'금', 'Sat'=>'토', 'Sun'=>'일');

		foreach($data as $key => $val) {
			if($val == '%' || $val == '^') {
				$val = 'D'.$val;
			}
			$format .= $val;
		}

		if(!$date) $date = time();
		$str = trim(date($format, $date));

		foreach($data_array as $key => $val) {
			$str = str_replace($key.'%', $val, $str);
			$str = str_replace($key.'^', $val.'요일', $str);
		}

		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean fwriteTo(string 파일패스, 저장할내용[, fopen옵션]) - 로그 파일 저장
	' +----------------------------------------------------------------------------------------------+*/
	function fwriteTo($path, $content, $type = 'a+') {
		global $root_dir;

		$fp = @fopen($root_dir.'/'.$path, $type);
		if($fp) {
			fwrite($fp, $content);
			fclose($fp);
			return true;
		}
		return false;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void loadPlugIn(string 플러그인이름) - 윙 플러그인 처리
	' +----------------------------------------------------------------------------------------------+*/
	function loadPlugIn($plug) {
		global $engine_dir, $root_dir, $define_plugin;

        if(is_array($define_plugin) == false) return;
		if(!@in_array($plug, $define_plugin)) return;

		$plugin_file = $root_dir.'/_plugin/'.$plug.'.plug.in';
		if(file_exists($plugin_file)) {
			foreach($GLOBALS as $key => $val) {
				${$key} = $val;
			}
			include $plugin_file;
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  boolean printAjaxHeader([string 캐릭터셋]) - 아작스 한글오류 방지를 위한 문서헤더 출력
	' +----------------------------------------------------------------------------------------------+*/
	function printAjaxHeader($charset = null) {
		if(headers_sent()) return false;

		if(!$charset) $charset = _BASE_CHARSET_;
		header('Content-type:text/html; charset='.$charset);

		return true;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  boolean makeThumb(string 원본경로, string 사본결로, int 가로, Int 세로) - imageMagick 으로 섬네일 생성(PHP4용)
	' +----------------------------------------------------------------------------------------------+*/
	function makeThumb($source, $target, $w, $h) { // php4 imageMagick
		global $cfg;

		if(class_exists('Imagick')) {
			$thumb = new Imagick();
			try {
				$thumb->readImage($source);
				$frames = $thumb->coalesceImages();
				if(count($frames) > 1) { // AniGIF
					foreach($frames as $frame) {
						$frame->resizeImage($w, $h, imagick::FILTER_LANCZOS, 1, true);
					}
					$thumb = $frames->deconstructImages();
					$thumb->writeImages($target, true);
				} else {
					$thumb->resizeImage($w, $h, imagick::FILTER_LANCZOS, 1, true);
					$thumb->writeImage($target);
				}
				$thumb->clear();
				$thumb->destroy();
			} catch (\ImagickException $e) {
				ErrorReport::__report(
					'ImagickException',
					$e->getMessage(),
					$e->getCode(),
					$e->getTrace()
				);				
				msg('이미지 생성 중 오류가 발생하였습니다.');
			}

			$img = getimagesize($target);
			return array('width' => $img[0], 'height' => $img[1]);
		} else {
			// Imagick 없을 경우 GD 로 처리
			$imginfo = getimagesize($source);
			$ow = $imginfo[0];
			$oh = $imginfo[1];
			$meta = $imginfo['mime'];

			switch($meta) {
				case 'image/jpeg' :
					$gd_src = imagecreatefromjpeg($source);
				break;
				case 'image/png' :
					$gd_src = imagecreatefrompng($source);
				break;
				case 'image/gif' :
					$gd_src = imagecreatefromgif($source);
				break;
				default :
					return false;
				break;
			}

			$img = setImageSize($ow, $oh, $w, $h);
			$gd_dst = imagecreatetruecolor($img[0], $img[1]);
			imagecopyresampled($gd_dst, $gd_src, 0, 0, 0, 0, $img[0], $img[1], $ow, $oh);

			switch($meta) {
				case 'image/jpeg' :
					$res = imagejpeg($gd_dst, $target, 100);
				break;
				case 'image/png' :
					$res = imagepng($gd_dst, $target);
				break;
				case 'image/gif' :
					$res = imagegif($gd_dst, $target);
				break;
			}
			if($res) return array('width' => $img[0], 'height' => $img[1]);
		}

		return false;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void fsConFolder(string 업로드경로) - 업로드경로에 해당하는 추가서버 반환
	' +----------------------------------------------------------------------------------------------+*/
	function fsConFolder($up_dir=""){
		global $dir,$cfg, $_use,$file_server,$matched_server,$file_server_num,$root_dir, $root_url;

		$matched_server = array();

		$dir_part = preg_replace("@^(($root_dir)|($root_url))?/?@", "", trim($up_dir));
		$dir_part = preg_replace("/\+/", "/", $dir_part);
		$dir_part = preg_replace("@https?://[^.]+\.[^.]+(\.[^./]+)?/?@", "", $dir_part);
		$dir_part = explode('/', $dir_part);

		$updir_web = array(
				'board_common', 'cate_common', 'compare',
				'config', 'conut_log', 'coordi_category', 'coupon', 'gift',
				'mail', 'prd_common', '_include_bak', '_template_bak'
		);
		$sysdir_web = array(
				'_config', 'w3c', '_template'. '_include', '_config', '_skin'
		);

		if(in_array($dir_part[0], $sysdir_web) || in_array($dir_part[1], $updir_web)) $conCate = 'loadbalance';
		if($dir_part[0] == 'board' || $dir_part[1] == 'editor_attach') $conCate = 'mari_board';
		if($dir_part[0] == 'board' && $dir_part[1] == '_skin') $conCate = 'loadbalance'; // 게시판 스킨 저장
		if($dir_part[0] == '_image' || $dir_part[1] == '_image') $conCate = 'image_ftp';
		if($dir_part[0] == '_data' && $dir_part[1] == 'review') $conCate = 'review';
		if($dir_part[0] == '_data' && $dir_part[1] == 'qna') $conCate = 'qna';
		if($dir_part[0] == '_data' && ($dir_part[1] == 'product' || $dir_part[1] == 'erp_storage')) $conCate = 'product';
		if($dir_part[0] == '_data' && $dir_part[1] == 'icon') $conCate = 'product';
		if($dir_part[0] == '_data' && $dir_part[1] == 'prd_option') $conCate = 'product';
		if($dir_part[0] == '_data' && $dir_part[1] == 'attach') $conCate = 'attach';
		if($dir_part[0] == '_data' && $dir_part[1] == 'popup') $conCate = 'popup';
		if($dir_part[0] == '_data' && strchr($dir_part[1], 'skin_')) $conCate = 'loadbalance'; // 스킨 백업관련
		if($dir_part[0] == '_template' && $dir_part[1] == 'content') $conCate = 'loadbalance'; // 스킨 백업관련
		if($dir_part[0] == '_data' && $dir_part[1] == 'intra_board') $conCate = 'mari_board'; // 인트라넷
		if($dir_part[0] == '_data' && $dir_part[1] == 'banner') $conCate = 'banner'; // 디자인 배너
		if($dir_part[0] == '_data' && $dir_part[1] == 'internal_banner') $conCate = 'banner'; // 내장 배너
		if($dir_part[0] == '_data' && $dir_part[1] == 'promotion') $conCate = 'banner'; // 프로모션 기획전 배너
		if($dir_part[0] == '_skin' && $dir_part[2] == 'img') $conCate = 'image_ftp';
		if($dir_part[0] == '__manage__') $conCate = 'product';
        if($dir_part[0] == '_data' && $dir_part[1] == 'seo') $conCate = 'banner';
        if($dir_part[0] == '_data' && $dir_part[1] == 'member') $conCate = 'mari_board';
        if ($dir_part[0] == '_data' && $dir_part[1] == 'favicon') $conCate = 'banner';

		if(function_exists('fsConcateUser')) {
			$conCate = fsConcateUser($dir_part, $conCate);
		}

		if($cfg['file_server_option'] < 2) return;
		if(!$_use['file_server'] || !$up_dir) return;

		for($i = 1; $i <= $cfg['file_server_ea'] ; $i++) {
			if(is_array($file_server[$i]['file_type'])) {
				if(in_array($conCate, $file_server[$i]['file_type'])) {
					$matched_server[] = $i;
					$jj++;
				}
			}
		}

		// 같은 역할의 파일서버 중 랜덤으로 서버 번호를 가져옴
        if(count($matched_server) > 0) {
            $rand = array_rand($matched_server);
            $file_server_num = $matched_server[$rand];
        } else {
            $file_server_num = $matched_server[1];
        }

		return $jj;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void srver_sync(string 파일명[, string 강제파일명변경]) - 웹서버 로드밸런싱 파일 싱크로
	' +----------------------------------------------------------------------------------------------+*/
	function server_sync($file_path, $force_name = '') {
		global $root_dir;

		if(fsConFolder(dirname($file_path))) {
			$tmp_file = $root_dir."/_data/$GLOBALS[now].".getExt($file_path);
			copy($file_path, $tmp_file);

			$file[name]=($force_name) ? $force_name : basename($tmp_file);
			$file[tmp_name]=$tmp_file;
			$file[size]=filesize($tmp_file);

			$GLOBALS[ext_unlimit] = "Y"; // PHP 확장자 업로드 가능
			$filename = preg_replace("/\.[^.]+$/", "", basename($file_path));
			$updir = preg_replace("@^$root_dir@", "", dirname($file_path));

			uploadFile($file, $filename, $updir);
			unlink($tmp_file);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getFileDir(string 업로드위치) - 업로드 위치로 해당 파일이 있는 웹서버 URL 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getFileDir($updir, $file = 1) {
		global $cfg, $_use, $file_server, $matched_server, $root_url;

		if($_use['file_server'] == "Y" && $file && fsConFolder($updir)) {
			if(!is_array($matched_server) || count($matched_server) < 1) return $root_url;

			$rand = array_rand($matched_server);
			$file_server_num = $matched_server[$rand];
			$file_dir=$file_server[$file_server_num]['url'];
		}
		else $file_dir=$root_url;

		if($cfg['ssl_type'] == 'Y') {
			$file_dir = preg_replace('@^[a-z]+://@', 'https://', $file_dir);
		}

		return $file_dir;
	}

	// 임대형 이용시 프리이미지 파일명 구하기
	function getListImgURL($updir, $upfile) {
		global $engine_dir, $tbl, $cfg, $dir, $pdo;

		if (!$upfile) return null;
        $img = getFileDir($updir).'/'.$updir.'/'.$upfile;

		return $img;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array checkAgent(string userAgent) - 접속자의 브라우저 정보 분석
	' +----------------------------------------------------------------------------------------------+*/
	function checkAgent($user_agent = null) {
		if(!$user_agent) $user_agent = $_SERVER['HTTP_USER_AGENT'];
		$user_agent = str_replace('rv:11.0', 'MSIE rv:11.0', $user_agent);

		$uagent	= preg_replace("/(\(.*)\(.*?\)/", "$1", $user_agent);

		$browser_list	= array("MSIE", "Opera", "KHTML", "Chrome", "Safari", "Netscape", "Firefox", "Konqueror", "PLAYSTATION", "PSP");
		$os_list			= array(
			"Windows 3.0" => "Windows 3.0", "Windows 3.1" => "Windows 3.1", "Windows 95" => "Windows 95", "Windows 98" => "Windows 98", "Windows CE" => "Windows CE", // Windows
			"Windows NT 4.0" => "Windows NT", "Windows NT 5.0" => "Windows 2000", "Windows NT 5.1" => "Windows XP", "Windows NT 5.2" => "Windows 2003", "Windows NT 6.0" => "Windows Vista", "Windows NT 7.0" => "Windows 7", // Windows NT ver
			"Windows NT" => "Windows NT", "Windows 2000" => "Windows 2000", "Windows XP" => "Windows XP", "Windows 2003" => "Windows 2003", "Windows Vista" => "Windows Vista", "Windows 7" => "Windows 7", // Windows NT
			"Linux" => "Linux", // Linux
			"PLAYSTATION" => "PLAYSTATION","PSP" => "PSP", // Console game machine
			"SymbianOS" => "SymbianOS", // Cellular
			"iPod" => "iPod Mac OS X", "iPhone" => "iPhone Mac OS X", "Mac OS X" => "Mac OS X", // Mac compatible
		);

		foreach($os_list as $key => $val) {
			if(preg_match("/".$key."/", $user_agent)) {
				$os_name = $val;
				break;
			}
		}

		foreach($browser_list as $val) {
			if(preg_match("/".$val."/", $user_agent)) {
				$br_name = $val;
				if($br_name == "MSIE") { // IE 일때만 버전 확인
					$br_name = preg_replace("/(;|-).*$/", "", substr($uagent, strpos($uagent, $br_name)));
					$br_name = preg_replace("/\/.*$/", "", $br_name);
				}
				break;
			}
		}

		if(!$os_name) $os_name = "UNKNOWN";
		if(!$br_name) $br_name = "UNKNOWN";

		return array($os_name, $br_name, $user_agent);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int seVerChk() - 설치된 스마트에디터 버전 체크
	' +----------------------------------------------------------------------------------------------+*/
	function seVerChk() {
		global $root_dir, $engine_dir;

		$engine_version=@file_get_contents($engine_dir."/../weagleEye/_smartEditor/version.php");
		$app_version=@file_get_contents($root_dir."/_data/_smartEditor/version.php");

		if($engine_version != $app_version) { // 버전이 현재와 다를경우 copy

			if(!is_dir($root_dir."/_data/smartEditor")) @mkdir($root_dir."/_data/smartEditor");

			$op_dir=@opendir($engine_dir."/../weagleEye/_smartEditor/");
			while($rd_file=@readdir($op_dir)){
				if($rd_file == "." || $rd_file == "..") continue;
				@copy($engine_dir."/../weagleEye/_smartEditor/".$rd_file, $root_dir."/_data/smartEditor/".$rd_file);
			}
			@closedir($op_dir);
		}

		return $engine_version;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array getCategoriesCache(int 타입) - 카테고리명 캐싱
	' +----------------------------------------------------------------------------------------------+*/
	function getCategoriesCache($type = null) {
		global $tbl, $pdo;

		$_cname_cache = array();

		if($type > 0) $w = " and ctype='$type'";
		$res = $pdo->iterator("select no, name from {$tbl['category']} where 1 ".$w);
        foreach ($res as $cdata) {
			$_cname_cache[$cdata['no']] = stripslashes($cdata['name']);
		}

		return $_cname_cache;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  array getPrdAllCates(array 상품) 바로가기를 포함한 상품의 모든 사용 카테고리코드를 배열로 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getPrdAllCates($prd) {
		global $tbl, $cfg, $pdo;

		$add_field = '';
		if($cfg['max_cate_depth'] >= 4) {
			$add_field .= ", depth4";
		}

		$check_cate = array(
			$prd['big'], $prd['mid'], $prd['small'], $prd['depth4'],
			$prd['xbig'], $prd['xmid'], $prd['xsmall'], $prd['xdepth4'],
			$prd['ybig'], $prd['ymid'], $prd['ysmall'], $prd['ydepth4']
		);
		if($prd['ebig']) $check_cate = array_merge($check_cate, explode('@', preg_replace('/^@|@$/', '', $prd['ebig'])));
		$scres = $pdo->iterator("select big, mid, small $add_field from {$tbl['product']} where wm_sc='{$prd['pno']}' and stat < 5"); // 바로가기 포함
        foreach ($scres as $sc) {
			$check_cate[] = $sc['big'];
			$check_cate[] = $sc['mid'];
			$check_cate[] = $sc['small'];
			$check_cate[] = $sc['depth4'];
		}

		foreach($check_cate as $key => $val) {
			if(!$val) unset($check_cate[$key]);
		}

		return array_unique($check_cate);
	}

	function fputcsvtmp($array) {
		if(!is_array($array)) return;
		if(_BASE_CHARSET_ != 'euc-kr') {
			foreach($array as $key => $val) {
				$array[$key] = mb_convert_encoding($val, 'euc-kr', _BASE_CHARSET_);
			}
		}

		$fp = fopen('php://output', 'r+');
		fputcsv($fp, $array);
		$csv = fgets($fp);
		fclose($fp);

		return $csv;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  Order Payment
	' +----------------------------------------------------------------------------------------------+*/
	function createPayment($data, $ext_stat = null) {
		global $tbl, $cfg, $admin, $now, $erpListener, $_order_sales, $pdo, $scfg;

		if(!$data['pno']) $data['pno'] = array();
		if(!$data['pno2']) $data['pno2'] = array();

		$ono = $data['ono'];
		$pno = '@'.implode('@', $data['pno']).'@'; // 정상상품
		$pno2 = '@'.implode('@', $data['pno2']).'@'; // 취소상품
		$amount = numberOnly($data['amount'], true);
		if(isset($data['type'])) $type = $data['type'];
		else $type = ($amount+$milage_prc+$emoney_prc) > 0 ? 2 : 1;
		$reason = $data['reason'];
		$comment = addslashes(trim($data['comment']));
		$pay_type =$data['pay_type'];
		$dlv_prc = numberOnly($data['dlv_prc'], true);
		$ex_dlv_type = numberOnly($data['ex_dlv_type']);
		$ex_dlv_prc = (int) numberOnly($data['ex_dlv_prc'], true);
		$add_dlv_prc = (int) numberOnly($data['add_dlv_prc'], true);
		$total_add_dlv_prc = $ex_dlv_prc+$add_dlv_prc;
		$repay_emoney = $data['repay_emoney'];
		$repay_milage = $data['repay_milage'];
		$emoney_prc = $data['emoney_prc'];
		$milage_prc = $data['milage_prc'];
		$cpn_no = numberOnly($data['cpn_no']);

		if($pay_type == '2' || $pay_type == '4') {
			$bank = addslashes(trim($data['bank']));
			$bank_account = addslashes(trim($data['bank_account']));
			$bank_name = addslashes(trim($data['bank_name']));

            // 계좌번호 암호화
            if ($scfg->comp('use_account_enc', 'Y') == true) {
                $bank_account = aes128_encode($bank_account);
                $bank_name = aes128_encode($bank_name);
            }
		}

		if($ext_stat) {
			$stat = $ext_stat;
		} else {
			$stat = 1;
			if(($amount+$milage_prc+$emoney_prc) == 0) $type = 3;
			if($pay_type == 3 || $pay_type == 6 || $pay_type == 11) $stat = 2;
			if($amount == 0) $stat = 2;
		}

		$ord = $pdo->assoc("select pay_prc, dlv_prc, repay_prc, milage_down, stat from {$tbl['order']} where ono=:ono", array(
            ':ono' => $ono
        ));
        if ($ord['stat'] == '41') return ;

		if($cpn_no > 0) {
			if($data['cpn_type'] == 'use') {
				$pdo->query("update {$tbl['coupon_download']} set ono='$ono', use_date='$now' where no='$cpn_no' and ono=''");
			} else if($data['cpn_type'] == 'cancel') {
				$is_type = $pdo->row("select is_type from {$tbl['coupon_download']} where no = '$cpn_no'");
				if($is_type == 'A') {
					$pdo->query("update {$tbl['coupon_download']} set ono='', use_date='0' where no='$cpn_no'");
				} else {
					$pdo->query("delete from $tbl[coupon_download] where no='$cpn_no'");
					if(is_object($erpListener)) {
						$erpListener->removeCoupon($cpn_no);
					}
				}
			}
			if(is_object($erpListener)) {
				$erpListener->setCoupon($cpn_no);
			}
		}

		if($type > 0 && $dlv_prc != 0) {
			ordStatLogw($ono, 100, null, null, '기본배송비 변경 ('.parsePrice($dlv_prc, true).'원)');
		}

		if($add_dlv_prc > 0) {
			ordStatLogw($ono, 100, null, null, '부분취소로 인한 추가 배송비 '.number_format($add_dlv_prc).' 원 생성');
		}
		if($add_dlv_prc < 0) {
			ordStatLogw($ono, 100, null, null, '추가/변경으로 인한 추가 배송비 '.number_format(abs($add_dlv_prc)).' 원 취소/환불');
		}

		if($ex_dlv_prc > 0) {
			ordStatLogw($ono, 100, null, null, '반품/교환 '.$_order_payment_dlv[$ex_dlv_type].' 배송비 '.number_format($total_add_dlv_prc).' 원 추가');
		}

		/*if($type == 1 && $dlv_prc) {
			$pdo->query("update $tbl[order] set dlv_prc=dlv_prc-dlv_prc where ono='$ono'");
		}*/

		$pdo->query("
			insert into {$tbl['order_payment']}
			(ono, pno, pno2, pay_type, amount, dlv_prc, ex_dlv_type, ex_dlv_prc, add_dlv_prc, repay_emoney, repay_milage, emoney_prc, milage_prc, cpn_no, type, stat, reason, comment, bank, bank_account, bank_name, reg_id, reg_date)
			values
			('$ono', '$pno', '$pno2', '$pay_type', '$amount', '$dlv_prc', '$ex_dlv_type', '$ex_dlv_prc', '$add_dlv_prc', '$repay_emoney', '$repay_milage', '$emoney_prc', '$milage_prc', '$cpn_no', '$type', '$stat', '$reason', '$comment', '$bank', '$bank_account', '$bank_name', '$admin[admin_id]', '$now')
		");
		$payment_no = $pdo->lastInsertId();

		if($type > 0 && $stat == 2 && $pay_type > 0) {
			$pno = str_replace('@', ',', preg_replace('/^@|@$/', '', $pno));
            if ($pno) {
                $rows = $pdo->row("select count(*) from {$tbl['order_product']} where no in ($pno) and stat=1");
                if($rows > 0) {
                    $pdo->query("update {$tbl['order_product']} set stat=2 where no in ($pno) and stat=1");
                    ordStatLogw($ono, 100, null, null, "추가결제 승인 처리로 {$_order_stat[1]} 상품이 {$_order_stat[2]}상태로 변경($pno)");
                }
            }
		}

		// 주문서 적용
		$rsql = getOrderSalesField(null, '-');
		$repay_prc = $pdo->row("select sum(total_prc -$rsql) from {$tbl['order_product']} where ono='$ono' and stat in (13, 15, 17, 19)");

		if($type == 1 && $dlv_prc != 0) { // 2017-06-29 배송비 취소금액을 총 환불 금액에 추가
			$repay_prc += abs($dlv_prc);
		}
		if($type == 1 && $total_add_dlv_prc) { // 무료배송 해제로 인한 배송비 및 반품교환배송비를 총 환불 금액에서 제외
			$repay_prc -= $total_add_dlv_prc;
		}

		$pay_prc = $pdo->row("select sum(amount) from {$tbl['order_payment']} where ono='$ono' and amount!=0");
		$prd_prc = $pdo->row("select sum(total_prc) from {$tbl['order_product']} where ono='$ono'");
		$dlv_prc = $pdo->row("select sum(dlv_prc+ex_dlv_prc+add_dlv_prc) from {$tbl['order_payment']} where ono='$ono'");

		$osql = '';
		if($ord['milage_down'] != 'Y' && $type != 0) {
			$milage = $pdo->assoc("select sum(total_milage) as mp, sum(member_milage) as mmp from {$tbl['order_product']} where ono='$ono' and stat!=11");
			$repay_milage = $pdo->row("select sum(total_milage) from {$tbl['order_product']} where ono='$ono' and stat > 11");
			$osql .= " , total_milage='{$milage['mp']}', member_milage='{$milage['mmp']}', repay_milage='$repay_milage'";
		}
		if($pay_type == '11'){
			$total_prc = $prd_prc;
			if($data['rm_dlv_prc']) $osql .= ", dlv_prc=dlv_prc-{$data['rm_dlv_prc']}";
			if($data['rm_tax']) $osql .= ", tax=tax-{$data['rm_tax']}";
		}else{
			$total_prc = ($pay_prc == 0 && in_array($ord['stat'], array(13, 15, 17, 19))) ? abs($ord['repay_prc']) : ($prd_prc + $dlv_prc);
			$osql .= ", dlv_prc='$dlv_prc'";
		}
		if($cfg['use_prd_dlvprc'] == 'Y') { // 개별 배송비 재계산
			$osql .= ", prd_dlv_prc=(select sum(prd_dlv_prc-repay_prd_dlv_prc) from {$tbl['order_product']} where ono='$ono')";
		}
		$pdo->query("update {$tbl['order']} set pay_prc='$pay_prc', repay_prc='$repay_prc', prd_prc='$prd_prc', total_prc='$total_prc' $osql where ono='$ono'");

		if($data['copytomemo'] == 'Y') {
			$pdo->query("insert into {$tbl['order_memo']} (ono, content, type, admin_no, admin_id, reg_date) values ('$ono', '$comment', '1', '$admin[no]', '$admin[admin_id]', '$now')");
			$pdo->query("update {$tbl['order']} set memo_cnt=memo_cnt+1 where ono='$ono'");
		}

		return $payment_no;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  ordChgPart 부분 주문상태 저장
	' +----------------------------------------------------------------------------------------------+*/
	function ordChgPart($ono, $date_chg = true) {
		global $tbl, $cfg, $now, $pdo;

		$ord = $pdo->assoc("select date1, date2, date3, date4, date5, stat from {$tbl['order']} where ono='$ono'");

		$stat2 = $pdo->row("select group_concat(stat) from {$tbl['order_product']} where ono='$ono'");
		$stat2 = explode(',', $stat2);
		$stat = min($stat2);
		$stat2 = '@'.implode('@', $stat2).'@';

		if($ord['stat'] != $stat && $stat <= 5) {
			for($i = 2; $i <= $stat; $i++) {
				if(!$ord['date'.$i] || ($i == $stat && $date_chg == true)) {
					$asql .= ", date$i='$now'";
				}
			}
		}
		$pdo->query("update {$tbl['order']} set stat='$stat', stat2='$stat2' $asql where ono='$ono'");

		return $stat;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  ordChgHold 주문 보류 현황 저장
	' +----------------------------------------------------------------------------------------------+*/
	function ordChgHold($ono) {
		global $tbl, $pdo;

		$data = $pdo->assoc("select sum(if(dlv_hold='Y',1, 0)) as hold_Y, sum(if(dlv_hold!='Y',1, 0)) as hold_N from {$tbl['order_product']} where ono='$ono' and stat in (1, 2, 3, 11, 20)");

		$postpone_yn = '';
		if($data['hold_Y'] > 0 && $data['hold_N'] > 0) $postpone_yn = 'B';
		else if($data['hold_Y'] > 0 && $data['hold_N'] == 0) $postpone_yn = 'Y';
		else if($data['hold_Y'] == 0 && $data['hold_N'] > 0) $postpone_yn = 'N';

		$pdo->query("update {$tbl['order']} set postpone_yn='$postpone_yn' where ono='$ono'");

		return $postpone_yn;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  환율 currencyconverterapi 가져오기 money_type  = USD_CNY(1달러=위안)
	' +----------------------------------------------------------------------------------------------+*/
	function getExchangeRate($money_type){
		global $now;

        $wec = new weagleEyeClient($GLOBALS['_we'], 'Etc');
        $rate = $wec->call('getExchangeRate', array('money_type' => $money_type));

        $exchangeRate = "";
		$exchangeDate = "";

		$rate = json_decode($rate,true);

		return array($rate['rate'], $rate['get_date']);
	}
	/* +----------------------------------------------------------------------------------------------+
	' |  환율 계산 약 금액
	' +----------------------------------------------------------------------------------------------+*/
	function showExchangeFee($fee,$br=false){
		global $cfg, $tbl, $pdo;
		$return = "";
		$fee = str_replace(',','',$fee);
		$r_currency_type = $cfg['r_currency_type'];
		if($cfg['r_currency_type'] == '원') $r_currency_type = 'KRW';

		if(trim($r_currency_type) && $fee > 0){
			$exchange = $pdo->row("select value from wm_default where code='{$r_currency_type}Rate'");
			$fee = $fee*$exchange;
			if(0) $return = "<br/>";
			$return .= number_format($fee,$cfg['r_currency_decimal']);
		}else{
			$return = 0;
		}
		return $return;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void insertTrashBox(array 삭제데이터, array 요약정보) - 데이터를 삭제하고 휴지통에 넣는다.
	' +----------------------------------------------------------------------------------------------+*/
	function insertTrashBox($data, $opt) {
		global $engine_dir, $tbl, $now, $pdo;

		if($opt['tbl'] == $tbl['review']) { // 후기 삭제시 상품평 적립금 취소
			if($data['milage_date'] > 0) {
				if(!function_exists('reviewMilage')) {
					include_once $engine_dir.'/_engine/include/mmilage/lib.php';
				}
				reviewMilage($data['no'], true);
				$data['milage_date'] = 0;
				$data['milage'] = 0;
			}
		}

		if(!isTable($tbl['common_trashbox'])) return false;
		$sdata = addslashes(serialize($data));
		$opt['title'] = addslashes($opt['title']);
		$opt['name'] = addslashes($opt['name']);
        if(isset($opt['db']) == false) $opt['db'] = '';
		$r = $pdo->query("
			insert into {$tbl['common_trashbox']}
				(tblname, db, title, name, data, reg_date, del_date)
				values
				(:tbl, :db, :title, :name, :sdata, :opt, :now)
		", array(
            ':tbl' => $opt['tbl'],
            ':db' => $opt['db'],
            ':title' => $opt['title'],
            ':name' => $opt['name'],
            ':sdata' => $sdata,
            ':opt' => $opt['reg_date'],
            ':now' => $now
        ));
		if($r) {
			$r = $pdo->query($opt['del_qry']);
		}
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  order / order_product 의 총 할인 금액 합산
	' +----------------------------------------------------------------------------------------------+*/
	function getOrderTotalSalePrc($data, $comma = false) {
		global $_order_sales;

		$total_sale_prc = 0;
		foreach($_order_sales as $fn => $fv) {
			if(is_array($data)) $sale = $data[$fn];
			elseif(is_object($data)) $sale = $data->{$fn};

			$total_sale_prc += $sale;
		}
		return parsePrice($total_sale_prc, $comma);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  쿼리를 위한 할인 필드명 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getOrderSalesField($prefix = '', $separator = ', ', $explain = array()) {
		global $tbl, $cfg, $_order_sales;

		if(!fieldExist($tbl['order_product'], 'sale7')) unset($_order_sales['sale7']);

		$fd = array();
		foreach($_order_sales as $sk => $sn) {
			if(in_array($sk, $explain)) continue;

			if($prefix) $sk = $prefix.'.'.$sk;
			$fd[] = $sk;
		}

		return implode($separator, $fd);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  simpleXML 사용시 객체 확인 후 toString 처리
	' +----------------------------------------------------------------------------------------------+*/
	function simplexmlToString($object) {
		if(is_object($object)) {
			$object = $object->__toString();
		}
		return $object;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  특별회원 그룹 추가 속성
	' +----------------------------------------------------------------------------------------------+*/
	function getMemberAttr($member) {
		global $tbl, $pdo;

		$_mchecker = array();
		foreach($member as $_mkey => $_mval) {
			if($_mval == 'Y' && preg_match('/^checker_([0-9]+)$/', $_mkey, $_mtmp)) {
				$_mchecker[] = $_mtmp[1];
			}
		}
		if(count($_mchecker) > 0) {
			$_mchecker = implode(',', $_mchecker);
			$_mchecker = $pdo->iterator("select * from {$tbl['member_checker']} where no in ($_mchecker)");
			foreach ($_mchecker as $_mdata) {
				if($_mdata['no_milage'] == 'Y') $member['attr_no_milage'] = 'Y';
				if($_mdata['no_sale'] == 'Y') $member['attr_no_sale'] = 'Y';
				if ($_mdata['no_discount'] == 'Y') $member['attr_no_discount'] = 'Y';
				if ($_mdata['no_coupon'] == 'Y') $member['attr_no_coupon'] = 'Y';
				if($_mdata['no_pg'] == 'Y') $member['attr_no_pg'] = 'Y';
				if($_mdata['deny'] == 'Y') $member['attr_deny'] = 'Y';
				if($_mdata['homepage']) $member['attr_homepage'] = stripslashes($_mdata['homepage']);
				if($_mdata['login_msg']) $member['attr_login_msg'] = stripslashes($_mdata['login_msg']);
			}
		}
		return $member;
	}

	// 페이징용 GET 쿼리스트링 생성
	function makeQueryString() {
		$args = func_get_args();
		if(gettype($args[0]) == 'boolean') {
			$qry_start = array_shift($args);
		} else {
			$qry_start = false;
		}

		$queryString = '';
		foreach($_GET as $key => $val) {
			if(in_array($key, $args)) continue;

			if(is_array($val)) {
				foreach($val as $key2 => $val2) {
					if(strlen($val2) > 0) $queryString .= '&'.$key.'['.$key2.']='.urlencode(strip_tags($val2));
				}
			} else {
				if(strlen($val) > 0) $queryString .= '&'.$key.'='.urlencode(strip_tags($val));
			}
		}
		if($qry_start == true) {
			$queryString = '?'.substr($queryString, 1);
		}

		return $queryString;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  기존 get 형태로 전달되던 listURL 대체
	' +----------------------------------------------------------------------------------------------+*/
	function setListURL($body) {
		if(is_array($_SESSION['listURL']) == false) $_SESSION['listURL'] = array();
		$_SESSION['listURL'][$body] = getURL();
	}

	function getListURL($body) {
		if(is_array($_SESSION['listURL'])) {
			return $_SESSION['listURL'][$body];
		}
		return '';
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  안정화 이후 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function extractParam() {
		if(defined('__extracted__')) return;

		foreach($_GET as $key => $val) {
			$GLOBALS[$key] = $val;
		}
		foreach($_POST as $key => $val) {
			$GLOBALS[$key] = $val;
		}
		define('__extracted__', true);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  재입고 알림 문자 발송처리
	' +----------------------------------------------------------------------------------------------+*/
	function sendNotifyRestockSMS($complex_no) {
		global $cfg, $tbl, $engine_dir, $sms_replace, $now, $pdo;

		// 재입고 알림 미사용시 리턴
		if ($cfg['notify_restock_use'] != "Y") return false;

		$_result = $pdo->assoc("SELECT force_soldout, is_soldout, qty, curr_stock('$complex_no') as cqty FROM erp_complex_option WHERE complex_no='$complex_no'");
		$qty = $_result['qty'];
		$soldout_type = $_result['force_soldout'];

		$notify_restock_sms_handler = false; // 재고상태에 따른 알림처리 핸들러
		switch ($soldout_type) {
			case "Y": // 강제품절
				// 강제 품절되어있는경우 별도의 알림 안함
				break;
			case "N": // 무제한, 알림처리
				$notify_restock_sms_handler = true;
				break;
			case "L": // 한정, 재고가 입고알림 기준 수량 이상인경우 알림처리
				if ($qty >= $cfg['notify_restock_min_qty']) $notify_restock_sms_handler = true;
				break;
		}

		if ($notify_restock_sms_handler) {
			$_sms_case = 25;
			include_once $engine_dir . "/_engine/sms/sms_module.php";

			switch ($cfg['notify_restock_target']) {
				case "1":
					$notify_restock_target_sql = "";
					break; // 전체
				case "2":
					$notify_restock_target_sql = " AND nr.`member_no` > 0 ";
					break; // 회원
				case "3":
					$notify_restock_target_sql = " AND nr.`member_no` = 0 ";
					break; // 비회원
			}

			$notify_restock_sql = "SELECT
										  nr.*
										, m.name as member_name
										, p.name as product_name
									FROM
										$tbl[notify_restock] nr
										LEFT JOIN `erp_complex_option` co ON nr.`complex_no`=co.`complex_no`
										LEFT JOIN $tbl[member] m ON nr.`member_no`=m.`no`
										LEFT JOIN $tbl[product] p ON nr.`pno`=p.`no`
									WHERE
										nr.`del_stat`='N'
										AND nr.`stat`=1
										AND nr.`complex_no` = '$complex_no'
										AND co.`del_yn` = 'N'
										$notify_restock_target_sql
									";
			$notify_restock_result = $pdo->query($notify_restock_sql);
            foreach ($notify_restock_result as $notify_restock_data) {
				// 상품명
				$_product_name = "";
				$_product_name = $notify_restock_data['product_name'];

				// 상품옵션
				$_options_str = $notify_restock_data['option'];

				// 신청자
				$_member_name = "";
				if ($notify_restock_data['member_no']) {
					$_member_name = $notify_restock_data['member_name'];
				} else {
					$_member_name = "고객";
				}

				// 문자내용 한글변수 처리
				$sms_replace['name'] = $_member_name;
				$sms_replace['notify_restock_prd'] = $_product_name;
				$sms_replace['notify_restock_opt'] = $_options_str;

				// 받는사람 전화번호
				$_buyer_cell = $notify_restock_data['buyer_cell'];

				// 문자발송
				SMS_send_case($_sms_case, $_buyer_cell);

				// 재입고요청 상태 알림완료(2)로 변환
				$update_sql = "UPDATE {$tbl['notify_restock']} SET stat=2, update_date='$now', send_date='$now' WHERE `no`={$notify_restock_data['no']}";
				$pdo->query($update_sql);
			}
		}
	}

	function isSmartApp() {
		if(strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP') > -1 && strpos($_SERVER['HTTP_USER_AGENT'],'IOS') > -1) return 'IOS';
		if(strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP') > -1) return 'Android';
		return false;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  스마트 스토어 사용 여부 체크
	' +----------------------------------------------------------------------------------------------+*/
	function getSmartStoreState() {
		global $cfg;

		if(empty($cfg['n_smart_store']) == true) return false;
		if(empty($cfg['n_store_app_id']) == true) return false;
		if(empty($cfg['n_store_app_secret']) == true) return false;
		if($cfg['n_smart_store'] != 'Y') return false;

		return true;
	}

	// 네이버 스마트스토어 사용 여부 / 기본값 설정
	if(getSmartStoreState() == false) {
		$cfg['n_smart_store'] = 'N';
	}

	function couponList() {
		global $couponRes,$tbl,$now,$root_url,$prd,$member, $cfg, $pdo;

		addField($tbl['coupon'], 'explain', 'text not null');

		if(!$couponRes) {
			$_prd = $prd;
			if ($_prd['no'] != $_prd['parent']) {
				if ($cfg['use_partner_shop'] == 'Y') {
					$afield .= ", partner_no";
				}
				if ($cfg['max_cate_depth'] >= 4) {
					$afield .= ", depth4";
				}
				$_prd = $pdo->assoc("select big, mid, small, xbig, xmid, xsmall, ybig, ymid, ysmall, ebig $afield from {$tbl['product']} where no = {$prd['parent']}");
				$_prd['parent'] = $prd['parent'];
			}
			$nowYmd = date("Y-m-d",$now);
			$ckcates = array_merge(array($_prd['big'], $_prd['mid'], $_prd['small'], $_prd['xbig'], $_prd['xmid'], $_prd['xsmall'], $_prd['ybig'], $_prd['ymid'], $_prd['ysmall']), explode('@', preg_replace('/^@|@$/', '', $_prd['ebig'])));

			$w .= " and (rdate_type=1 or (rdate_type=2 and rstart_date<='$nowYmd' and rfinish_date>='$nowYmd'))"; // 다운로드 날짜
			$w .= " and (down_type='A' or (down_type='B' and ((down_grade='$member[level]' and down_gradeonly='Y') or (`down_grade`>='$member[level]' and `down_gradeonly`='N'))))";

			if($cfg['use_partner_shop'] == 'Y' && $_prd) {
				if($_prd['partner_no'] > 0) { // 파트너
					$w .= " and (partner_type=0 or (partner_type in (2, 3) and partner_no='$_prd[partner_no]'))";
				} else { // 본사
					$w .= " and partner_type!=2";
				}
			}

			if(fieldExist($tbl['coupon'], 'is_birth')) {
				$w .= " and is_birth!='Y'";
			}

			if($_SESSION['is_wisaapp'] == true) {
				$w .= " and device in ('', 'app', 'mobile_all')";
			} else if($_SESSION['browser_type'] == 'mobile') {
				$w .= " and device in ('', 'mobile', 'mobile_all')";
			} else {
				$w .= " and device in ('', 'pc')";
			}

			$cpns = array();
			$res = $pdo->iterator("select * from {$tbl['coupon']} where is_type='A' $w");
            foreach ($res as $data) {
				if ($_SERVER['SCRIPT_NAME'] != '/shop/detail.php' && $_REQUEST['exec_file'] != 'shop/quickDetail.inc.php') {
					$data['attachtype'] = 0;
				}
				switch($data['attachtype']) {
					case '' :
					case '0' :
						$cpns[] = $data['no'];
					case '1' :
						foreach($ckcates as $val) {
							if($val > 0) {
								if(strpos($data['attach_items'], "[$val]") > -1) $cpns[] = $data['no'];
							}
						}
					break;
					case '2' :
						if(strpos($data['attach_items'], "[$_prd[parent]]") > -1) $cpns[] = $data['no'];
					break;
					case '3' :
						foreach($ckcates as $val) {
							if($val > 0) {
								if(strpos($data['attach_items'], "[$val]") > -1) continue;
							}
						}
						$cpns[] = $data['no'];
					break;
					case '4' :
						if(strpos($data['attach_items'], "[$_prd[parent]]") === false) {
							$cpns[] = $data['no'];
						}
					break;
				}
			}

			$cpns = array_unique($cpns);
			if(count($cpns)) $w = " and no in (".implode(',', $cpns).")";
			else $w = ' and 0';

			$couponRes = $pdo->iterator("select * from {$tbl['coupon']} where is_type='A' $w order by no desc");
		}

		$data = $couponRes->current();
        $couponRes->next();
		if($data == false) {
			return;
		}

		$file_dir = getFileDir($data['updir']);
		$data['img']="$file_dir/$data[updir]/$data[upfile1]";
		$data['name']=stripslashes($data['name']);
		if($data['auto_cpn']=='Y') {
			$data['link']="javascript:;\" onClick=\"alert('".__lang_cpn_info_autoCpn__."');return false;";
		}
		else {
			$data['link']="javascript:downLoadCoupon($data[no])";
		}
		if($member['no']) {
			if($data['download_limit'] == 2) {
				$coupon_reuse = $pdo->assoc("select no, ono from {$tbl['coupon_download']} where member_no = '{$member['no']}' and cno = '{$data['no']}' order by no desc");
				$coupon_yn = (!$coupon_reuse['no'] || ($coupon_reuse['no'] && $coupon_reuse['ono'] != '')) ? "" : "Y";
			}
			$data['coupon_yn'] = ($coupon_yn) ? "" : "Y";
			$data['coupon_ny'] = ($coupon_yn) ? "Y" : "";
			if($data['download_limit'] == 1) {
			    $data['coupon_yn'] = "Y";
				$data['coupon_ny'] = "";
			}
			if($data['download_limit'] == 3) {
				$dl2=$pdo->row("select count(*) from {$tbl['coupon_download']} where `cno`='$data[no]' and `member_no`='{$member['no']}' and `member_id`='{$member['member_id']}'");
				if($dl2>=$data['download_limit_ea']) {
					$data['coupon_yn'] = "";
					$data['coupon_ny'] = "Y";
				} else {
				    $data['coupon_yn'] = "Y";
					$data['coupon_ny'] = "";
				}
			}
		}
		$data['sale_type'] = ($data['sale_type'] == 'm') ? parsePrice($data['sale_prc'], "true")."원" : $data['sale_prc']."%";
		if($data['stype'] == 3) {
			$data['sale_type'] = __lang_shop_free_shipping__;
		}

		$data['prc_limit'] = parsePrice($data['prc_limit'], "true");
		return $data;
	}

    /**
     * aes128
     **/
    function aes128_encode($string)
    {
        if (function_exists('openssl_encrypt') == false || $string == '') {
            return $string;
        }

        $key = $GLOBALS['_site_key_file_info'][2];
        $enc = openssl_encrypt($string, 'AES128', $key, OPENSSL_RAW_DATA);
        return base64_encode($enc);
    }

    function aes128_decode($string)
    {
        if (function_exists('openssl_decrypt') == false) {
            return $string;
        }

        $key = $GLOBALS['_site_key_file_info'][2];
        return openssl_decrypt(base64_decode($string), 'AES128', $key, OPENSSL_RAW_DATA);
    }

    /**
     * jsonReturn - json으로 결과 출력
     *
     * @array $arr json으로 변환할 배열
     **/
    function jsonReturn($arr)
    {
        header('Content-type:application/json');
        exit(json_encode_pretty($arr));
    }

    /**
     * json_encode53 - PHP 5.3 이하 호환성 처리
     *
     * @array $arr json으로 변환할 배열
     **/
    function json_encode_pretty($array)
    {
        if (defined('JSON_PRETTY_PRINT') == null) {
            define('JSON_PRETTY_PRINT', null);
        }
        if (defined('JSON_UNESCAPED_UNICODE') == null) {
            define('JSON_UNESCAPED_UNICODE', null);
        }
        return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * sms 인증번호 생성 후 리턴
     **/
    function smsCertificateNum()
    {
        global $scfg;

        // 인증번호 자릿수(member_confirm_sms_cnt)가 정상이면 (숫자, 3~10)
        $member_confirm_sms_cnt = (int) $scfg->get('member_confirm_sms_cnt');
        if ($member_confirm_sms_cnt >= 3 && $member_confirm_sms_cnt <= 10) {
            // 인증번호 생성
            $randStartNum = str_pad('1', $member_confirm_sms_cnt, '0');
            $randEndNum = str_pad('9', $member_confirm_sms_cnt, '9');
            $reg_code = rand($randStartNum, $randEndNum);

            // 구분문자(member_confirm_sms_str)가 정상 & 사용안함이 아니면 (숫자, 1:사용안함 2:- 3:공백)
            $member_confirm_sms_str = (int) $scfg->get('member_confirm_sms_str');
            if ($member_confirm_sms_str == 2 || $member_confirm_sms_str == 3) { // 1사용안함은 패스
                $member_confirm_sms_strplace = (int) $scfg->get('member_confirm_sms_strplace'); // N자리마다 (숫자, 3~5)
                if ($member_confirm_sms_strplace >= 3 && $member_confirm_sms_strplace <= 5) {
                    $reg_code = str_split($reg_code, $member_confirm_sms_strplace);
                    $implode_str = ($member_confirm_sms_str == '2') ? '-' : ' ';
                    $reg_code = implode($implode_str, $reg_code);
                }
            }
        } else { // 인증번호 기본 6자리로 생성
            $randStartNum = str_pad('1', 6, '0');
            $randEndNum = str_pad('9', 6, '9');
            $reg_code = rand($randStartNum, $randEndNum);
        }

        return $reg_code;
    }

    /**
     * 구글리캡챠 인증확인
     * @param string $captcha_response 리캡챠 응답코드
     * @param int $ver 리캡챠버전 (기본 : 2)
     * @return bool 인증성공여부
     */
    function recaptchaVerify($response, $ver=2)
    {
        global $cfg;

        if ($ver == 2) {
            $ret = comm("https://www.google.com/recaptcha/api/siteverify?secret=".$cfg['captcha_secret_key']."&response=".$response."&remoteip=".$_SERVER['REMOTE_ADDR']);
            $retkey = json_decode($ret, true);
            return ($retkey['success'] == 1);
        }
    }

    /**
     * CSV 파일 문자열 확인 후 인코딩
     *
     * @param array $f CSV 파일 $_FILES['csv']
     * @param string $toEncoding 바꿀 인코딩 (기본 : _BASE_CHARSET_)
     *
     * @return array CSV 파일 인코딩 후 리턴
     */
    function csvFileEncoding($f, $toEncoding = _BASE_CHARSET_) {
        $csvContent = file_get_contents($f['tmp_name']);
        $now_encoding = mb_detect_encoding($csvContent, array('UTF-8','EUC-KR','CP949'));

        if (strtoupper($now_encoding) != strtoupper($toEncoding)) {
            $convertFile = mb_convert_encoding($csvContent, $toEncoding, $now_encoding);
            file_put_contents($f['tmp_name'], $convertFile);
        }

        return $f;
    }

    /**
     * sms 인증번호 구분자 설정 체크
     */
    function smsCertificateNumCheckstr() {
        global $scfg;
        $implode_str = '';
        // 구분문자(member_confirm_sms_str)가 정상 & 사용안함이 아니면 (숫자, 1:사용안함 2:- 3:공백)
        $member_confirm_sms_str = (int) $scfg->get('member_confirm_sms_str');
        if ($member_confirm_sms_str == 2 || $member_confirm_sms_str == 3) { // 1사용안함은 패스
            $implode_str = ($member_confirm_sms_str == '2') ? '-' : ' ';
        }
        return $implode_str;
    }
    
    /**
     * 쿠폰 반환 시 중복 쿠폰 체크
     * @param $cpn_no : 반환쿠폰번호
     * @param $prd_cp : 개별상품쿠폰여부
     * @return array
     */

    function coupon_dup_check($cpn_no, $prd_cp = false)
    {
        global $tbl, $pdo;
        $data = array();
        $data['cpn_name'] = $data['rtn_cpn'] = array();
        $cpn_info = $pdo->iterator("select a.no, a.member_no, a.cno, a.name, a.udate_type, a.ufinish_date, b.rdate_type, b.rstart_date, b.rfinish_date, b.udate_limit, b.download_limit from $tbl[coupon_download] a join $tbl[coupon] b on a.cno = b.`no` where a.`no` in ($cpn_no)");
        foreach ($cpn_info as $val) {
            if ($val['download_limit'] == '2') {
                $cpn_where = $prd_cp ? "and stype = '5' " : "and stype != '5' ";
                if ($val['udate_type'] == '2') $cpn_where .= "and CURDATE() between ustart_date and ufinish_date ";
                if ($val['udate_type'] == '3') $cpn_where .= "and ufinish_date >= CURDATE() ";
                $cpn_chk = $pdo->row("select count(1) as cnt from $tbl[coupon_download] where member_no = '{$val['member_no']}' and cno = '{$val['cno']}' AND use_date = 0 ".$cpn_where);
                if ($cpn_chk && $cpn_chk > 0) $data['cpn_name'][] = addslashes($val['name']);
                else $data['rtn_cpn'][] = $val['no'];
            }
        }
        return $data;
    }

    function replaceEntities($text = '', $spc = false)
    {
        if (!trim($text)) return $text;
        $text = stripslashes($text);
        if ($spc) $text = htmlentities($text);
        return $text;
    }

	include_once $engine_dir.'/_engine/include/design.lib.php';
	include_once $engine_dir."/_engine/include/popup.lib.php";

?>