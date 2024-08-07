<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	$db = addslashes(trim($_REQUEST['db']));
	$cate = numberOnly($_REQUEST['cate']);
	$rURL =  preg_replace('/\'|"/', '', $_REQUEST['rURL']);
	$mari_mode = trim($_REQUEST['mari_mode'], '@');
	$page = numberOnly($_REQUEST['page']);

	$mari_path=$engine_dir."/board/";
	$mari_url=$root_url."/board/";

// 라이브러리
	if(is_file($mari_path."include/lib.php")) {
		include_once $mari_path."include/lib.php";
	}
	else {
		exit("파일을 찾을 수 없습니다 - lib.php");
	}

	if($install_mode) return;

// 메인 파일 & 권한 체크
	if($mari_mode && !preg_match('/^[a-z_@]+$/i', trim($mari_mode))) {
		$mari_mode = 'view@list';
	}

	checkDB($db);

	if(!$mari_mode) {
		switch($config['start_mode']) {
			case '2' :
				$where = ($member['level'] == 1) ? "" : " and hidden!='Y'";
				$no = $_GET['no'] = $pdo->row("select no from `$mari_set[mari_board]`where db='$db' $where ORDER BY no DESC limit 1");
				$mari_mode = 'view@view';
			break;
			case '3' :
				$mari_mode = 'write@write';
			break;
			default :
				$mari_mode='view@list';
		}
	}

	// 버튼 링크
	$link['write'] = "<a style='display:none;'>";
	if(getAuth('write') >= 0) {
		$link['write'] = "<a href=\"#\" onclick=\"javascript:mariExec('write@write','')\">";
	}

	if(!$mari_mode) $mari_mode="view@list";
	$mode=explode("@",$mari_mode);
	$main_file=$mode[0]."/".$mode[1].".php";
	if($ext_include) { // 엔진외의 파일 사용시 2006-11-23 Jin
		$main_file=$root_dir."/board/".$main_file;
	}
	else {
		$main_file=$mari_path.$main_file;
	}
	if(preg_match('/_exec/',$mode[1])) $no_master=1;
	$exec=$mode[2];
	if(!is_file($main_file)) msg("존재 하지 않는 구성 파일입니다","/","parent");


// 보드명 권한 체크
	checkDB($db);
	getAuth($_auth[$mari_mode],1);

	if($_REQUEST[db]) {
		$db_que1="&db=$db";
		$db_que2="?db=$db";
	}

	// 스킨 경로
		if($_SESSION['browser_type'] == 'mobile' && $config['mskin']) {
			$config['skin'] = $config['mskin'];
			$config['gallery_cols'] = 0;
		}

		$skin_path=$root_dir."/board/_skin/$config[skin]/";
		$skin_url=$root_url."/board/_skin/$config[skin]/";

	if($_REQUEST[db]) $hidden_db="<input type=\"hidden\" name=\"db\" value=\"$db\">";

	if($bs_module) return;

	// 기간설정 처리
	if($cfg['use_board_timer'] == 'Y') {
		$now_time = date('Y-m-d H:i:s');
		$pdo->query("update {$mari_set['mari_board']} set hidden='Y' where end_date>'0000-00-00' and end_date<'$now_time' and n_status='Hidden'");
		$pdo->query("update {$mari_set['mari_board']} set cate=n_cate where end_date>'0000-00-00' and end_date<'$now_time' and n_status='Category'");
	}

	if($no_master) {
		include $main_file;
	}
	else {
		$title_img=$title_num=$title_no=$db;
		if($_REQUEST[db]) $hidden_db="<input type=\"hidden\" name=\"db\" value=\"$db\">";

		common_header();
		$stylecss=$skin_url."style.css";

		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$stylecss\">";

		// V3 디자인
		include_once $engine_dir."/_engine/common/skin_index.php";

	// 보드 설정 헤더
		$config[inc_header]=str_replace("..",$root_dir,$config[inc_header]);
		if(is_file($config[inc_header])) include $config[inc_header];

	// 상단 이미지
		if ($config['top_use'] == 'Y' && $config['top_content'])
			include $mari_path."include/top_design.php";

	// 기본 파일
		include $main_file;

	// 기본 헤더
		include_once $mari_path."include/header.php";

	// 보드 설정 푸터
		$config[inc_header]=str_replace("..",$root_dir,$config[inc_header]);
		if(is_file($config[inc_footer])) include $config[inc_footer];
	}
?>