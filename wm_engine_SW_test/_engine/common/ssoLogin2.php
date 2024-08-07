<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자모드 로그인 처리
	' +----------------------------------------------------------------------------------------------+*/

	@define("_wisa_manage_edit_", true);
	include_once $engine_dir.'/_manage/manage.lib.php';
	include_once $engine_dir.'/_manage/manage2.lib.php';

	printAjaxHeader();

    if ($_GET['target']) {
        $_POST = $_GET;
    }

    $check_url1 = parse_url($this_root_url);
    $_manage_url = parse_url($manage_url);
	if(!$_GET['skey'] && count($_POST) == 0 && $check_url1['host'] != $_manage_url['host']) {
		msg('', str_replace($check_url1['host'], $_manage_url['host'], getURL()));
	}

	$access_pass = "N";
	//독립몰,최고관리자 pass
	if((file_exists($engine_dir.'/_engine/include/account/setHosting.inc.php') && $_use['direct_login']!="Y") || $cfg['staffs_access_limit']!="Y" || $admin['level']==1 || $admin['level']==2 || preg_match('/^118\.129\.243\./', $_SERVER['REMOTE_ADDR']) == true) {
		$access_pass = "Y";
	}

	addField($tbl['mng'], "access_lock", "enum('Y','N') not null default 'N'");
	addField($tbl['mng'], "access_count", "int(2) not null default 0");

	$admin_id = addslashes($_POST['admin_id']);
	$admin_pwd = addslashes($_POST['admin_pwd']);
	$login_type = addslashes($_POST['login_type']);
	$site_code = addslashes($_POST['site_code']);
	$query_string = addslashes($_POST['query_string']);

	$err = 0;

	// 로그인 체크
    if ($_GET['exec'] == 'getToken') {
        // 사이트 소유자 체크
        $wec = new weagleEyeClient($_we, 'Etc');
        $ret = $wec->call('compareSiteOwner', array(
            'key1' => $_GET['site_key'],
            'key2' => trim($_site_key_file_info[2])
        ));
        if ($ret == 'OK') {
            $r = comm(
                $_GET['ret_url'].'/main/exec.php?exec_file=api/sso/ssoOpener.exe.php'.
                '&admin_no='.$_GET['admin_no'].
                '&skey='.$_GET['skey']
            );
            $r = json_decode($r);
            if ($r->sess_id) {
                $result = 'success';
                $scfg->import(array(
                    'ssoKey' => md5(time())
                ));
            } else {
                $result = 'faild';
            }
        } else {
            $result = 'Site owner is different.';
        }

        header('Content-type:application/json;');
        exit(json_encode(array('result' => $result, 'ssoKey' => $cfg['ssoKey'])));
    }
    else if($_GET['skey']) {
        $wec = new weagleEyeClient($_we, 'Etc');
        $ret = $wec->call('compareSiteOwner', array(
            'key1' => $_REQUEST['site_key'],
            'key2' => trim($_site_key_file_info[2])
        ));
        if ($ret != 'OK') {
            if ($_REQUEST['contentType'] == 'json') {
                header('Content-type: application/json');
                exit(json_encode(array(
                    'status' => 'N',
                    'hash' => 'Site owner is diffrent',
                )));
            }
            msg('사이트 소유자가 일치하지 않습니다.', 'back');
        }

		$ret_url = $_GET['ret_url'];
		$ano = $_GET['admin_no'];
		$skey = $_GET['skey'];
		$_body = $_GET['body'];
		$r = comm($ret_url.'/main/exec.php?exec_file=api/sso/ssoOpener.exe.php&admin_no='.$ano.'&skey='.$skey.'&urlfix=Y');

		if(!$r) msg('정상적인 접근이 아닙니다.', 'back');

		$data = json_decode($r);
		$admin_id = $data->admin_id;
		$ssoKey = $data->ssoKey;

		$mng = $pdo->assoc("select * from {$tbl['mng']} where admin_id='$admin_id'");
		if(!$mng['no']) msg('자동 로그인 하는 사이트에 동일한 아이디가 있어야합니다.', 'back');

		if($data->sess_id != $skey || $data->admin_no != $ano) {
			msg('정상적인 접근이 아닙니다.', 'back');
		}

        if (defined('__WEAGLEEYE_OUTSIDE__') == false) { // 임대형
            $ret = $wec->call('getMyAccountStat', array(
                'key_code' => trim($_site_key_file_info[2])
            ));
            if ($ret != 'OK') {
                msg(mb_convert_encoding($ret, 'utf8', 'euckr'), 'back');
            }
        }

		$result = 'true';
	} else {
		if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) {
			include_once $engine_dir.'/_engine/include/account/ssoLogin.inc.php';
		} else {

			$mng = $pdo->assoc("select * from {$tbl['mng']} where admin_id='$admin_id'");
			if($mng['pwd'] != sql_password($admin_pwd)) $err=1;
			$result = 'true';
		}
	}

	if($result != 'true') $err = 1;
	if (!$mng['no']) $err++; //2
	if($access_pass!="Y" && $err==1) { //비밀번호 틀림
		$lock_chk = "Y";
		$pdo->query("update {$tbl['mng']} set access_count=access_count+1 where no='$mng[no]'");
		$lock_cnt = $mng['access_count']+1;
	}else if($access_pass!="Y" && $err==2) { //아이디 없음
		$lock_chk = "Y";
		$err = 9;
		$mng['admin_id'] = $admin_id;
		$lock_cnt = $pdo->row("select count(*) from {$tbl['mng_log']} where member_id='$admin_id' and login_result='9'");
		$lock_cnt = $lock_cnt+1;
	}

	mngLoginLog($mng['admin_id'], $err);

	$err_msg = "";
	$login_type = preg_replace('/[^a-z0-9_]/', '', $_POST['login_type']);
	if($lock_chk=="Y") {
		if($lock_cnt<$cfg['access_warning']) {
			$err_msg = "로그인 인증이 실패되었습니다";
		}else if($lock_cnt>=$cfg['access_warning'] && $lock_cnt<$cfg['access_lock']) {
			$err_msg = "아이디 또는 비밀번호 오류가 ".$lock_cnt."회 발생하였습니다. \\n".$cfg['access_lock']."회 실패 시 사용이 중지되니 주의바랍니다.";
		}else if($lock_cnt>=$cfg['access_lock']) {
			if($err==1) {
				$pdo->query("update {$tbl['mng']} set access_lock='Y' where admin_id='{$mng['admin_id']}'");
			}
			$err_msg = "아이디 또는 비밀번호 오류가 ".$cfg['access_lock']."회 발생하여 사용이 중지되었습니다. \\n로그인 후 인증절차를 통해 해제가 가능합니다.";
		}
	}else if($err>0) {
		$err_msg = "로그인 인증이 실패되었습니다";
	}
	if($err_msg) {
		echo "
		<script type='text/javascript'>
			window.alert('".$err_msg."');

			if('$login_type' == 'direct') {
				history.back();
			} else {
				if(navigator.appVersion.indexOf('MSIE 7.0') >= 0 || navigator.appVersion.indexOf('MSIE 8.0') >= 0) {
					parent.window.open('about:blank','_self').close();
				} else {
					parent.window.opener = self;
					parent.self.close();
				}
			}
		</script>
		";
		exit;
	}else {
		if($mng['access_lock']=="Y") {
			$_SESSION['access_admin_no'] = $mng['no'];
            msg('', $manage_url.'/_manage/?body=intra@access_limit.frm', '');
            exit;
		} elseif ($scfg->comp('intra_2factor_use', 'Y') == true) {
            $_SESSION['access_admin_no'] = $mng['no'];
            msg('', $root_url.'/_manage/?body=intra@intra_factor.frm', '');
            exit;
        } elseif ($scfg->comp('mng_pass_expire') == true && $body != 'intra@password_expire.exe' && strtotime($mng['expire_pwd']) < $now) { // 비밀번호 유효기간 설정
            $_SESSION['access_admin_no'] = $mng['no'];
            msg('', $manage_url.'/_manage/?body=intra@password_expire.frm', '');
        }
	}

	if(!$mng['ssoKey'] && $_SESSION['ssokey'] && $_SESSION['ssoId']) {
		$pdo->query("update $tbl[mng] set ssoKey='$_SESSION[ssokey]' where admin_id='$_SESSION[ssoId]'");
		unset($_SESSION['ssokey'], $_SESSION['ssoId']);
	}

	$_SESSION['admin_no'] = $mng['no'];

	if($_POST['target']) {
		$_body = 'support@'.$_POST['target'];
	}

	if($mng['access_count']>0) { //로그인 성공시 초기화
		$pdo->query("update {$tbl['mng']} set access_count='0' where no='$mng[no]'");
	}

	if($_REQUEST['contentType'] == 'json') {
		exit(json_encode(array(
			'status' => 'Y',
			'hash' => session_id(),
		)));
	}

?>
<html>
<body>
<form name="makeCookieFrm" method="post" action="/main/exec.php">
<input type="hidden" name="exec_file" value="common/makeCookie.php" />
<input type="hidden" name="session_id" value="<?=session_id()?>" />
<input type="hidden" name="param" value="<?=$_body?>" />
<input type="hidden" name="query_string" value="<?=$query_string?>" />
<input type="hidden" name="urlfix" value="Y" />
</form>
<script type="text/javascript">
	document.makeCookieFrm.submit();
</script>
</body>
</html>