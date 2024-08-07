<?PHP

	$site_key = trim($_site_key_file_info[2]);

	// 인증창 열기
	if($_POST['exec'] == 'valid') {
		$url = parse_url(getURL());
		$url = $url['scheme'].'://'.$url['host'];
		header("Location: https://redirect.wisa.co.kr/instagram/index.php?redir=".urlencode($url).'&site_key='.$site_key);
		exit;
	}

	// 인스타그램 연결 해제
	if($_POST['exec'] == 'unlink') {
        $instagram_cache_path = $root_dir.'/_data/cache/instagram.cache.php';
        if (file_exists($instagram_cache_path) == true) {
    		unlink($instagram_cache_path);
        }
		$pdo->query("delete from {$tbl['config']} where name='instagram_access_token'");

		$wec = new weagleEyeClient($_we, 'Etc');
		$wec->call('unlinkInstagram');

		msg('', 'reload', 'parent');
	}

	if($_POST['exec'] == 'refresh') {
		define('__INSTAGRAM_FORCE__', true);
		require $engine_dir.'/_engine/api/instagram/get.inc.php';

        if (is_array($cfg['cache_domains'])) {
            foreach ($cfg['cache_domains'] as $domain) {
                comm($domain . '/main/exec.php', array(
                    'exec_file' => 'api/instagram/refresh.exe.php',
                    'refresh' => 'Y',
                    'urlfix' => 'Y'
                ));
            }
        }

		header('Content-type:application/json;');
		exit(json_encode(array(
			'count' => $cnt
		)));;
	}

	// 인증 결과 저장
	if($_GET['access_token']) {
		$cfg['instagram_access_token'] = $_POST['instagram_access_token'] = $_GET['access_token'];

		if($_GET['site_key'] != $site_key) {
			echo 'unregisterd sitekey';
			msg('unregisterd sitekey');
		}

		$no_reload_config = true;
		include $engine_dir.'/_manage/config/config.exe.php';

		// get Instagram media
		define('__INSTAGRAM_FORCE__', true);
		require $engine_dir.'/_engine/api/instagram/get.inc.php';

		if(isset($_GET['refresh']) == true) exit('OK');

		javac("
			parent.opener.location.reload();
			self.close();
		");

		exit;
	}

?>