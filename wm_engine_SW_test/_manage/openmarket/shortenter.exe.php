<?PHP

	$exec = addslashes(trim($_POST['exec']));
	$bitly_token = addslashes(trim($_POST['bitly_token']));
	$title = addslashes(trim($_POST['title']));
	$longUrl = addslashes(trim($_POST['longUrl']));
	
	if($exec) {
		checkBlank($bitly_token, 'ACCESS TOKEN 값을 입력해주세요.');
	}
	checkBlank($title, '페이지명을 입력해주세요.');
	checkBlank($longUrl, '단축 대상 URL을 입력해주세요.');
	
	if(!$exec) {
		$google_shortEnter_apikey = $_POST['google_shortEnter_apikey'];
		include_once $engine_dir.'/_engine/include/classes/urlShortEnter.class.php';

		$goo_gl = new urlShortEnter($google_shortEnter_apikey);
		$result = $goo_gl->shorten($longUrl);

		if(!$result) msg('단축 URL 생성이 실패되었습니다.');

		sql_query("insert into $tbl[urlshortenter] (title, shortUrl, longUrl, reg_date, admin_id) values ('$title', '$result', '$longUrl', '$now', '$admin[admin_id]')");

		// 설정 저장
		unset($_POST);
		$no_reload_config = true;
		$_POST['google_shortEnter_apikey'] = $google_shortEnter_apikey;
		require $engine_dir.'/_manage/config/config.exe.php';
	}else {//bitly
		$bitly_token = $_POST['bitly_token'];

        $postdata = json_encode(array('long_url' => $longUrl));
        $header = array("Authorization: Bearer $bitly_token", "Content-Type: application/json");

        $ret = comm("https://api-ssl.bit.ly/v4/shorten", $postdata, 0, $header);
		$result = json_decode($ret, true);

		if(!$result['link']) msg('단축 URL 생성이 실패되었습니다.');
		$shorturl = $result['link'];

		sql_query("insert into $tbl[bitly_shortenter] (title, shortUrl, longUrl, reg_date, admin_id) values ('$title', '$shorturl', '$longUrl', '$now', '$admin[admin_id]')");

		// 설정 저장
		unset($_POST);
		$no_reload_config = true;
		$_POST['bitly_token'] = $bitly_token;
		require $engine_dir.'/_manage/config/config.exe.php';
	}
	msg('단축 URL 생성이 완료되었습니다.', 'reload', 'parent');

?>