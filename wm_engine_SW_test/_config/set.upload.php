<?PHP

	$up_cfg = array();

	// 스킨디자인 이미지
	$up_cfg['skindesign']['filesize'] = 1024; // kb
	$up_cfg['skindesign']['ea'] = 0;

	// 상품기본사진
	$up_cfg['prdBasic']['filesize'] = $_SESSION['h_spec']['img_upload_limit'];
	$up_cfg['prdBasic']['ea'] = 0;

	// 상품상세 이미지
	switch($filetype) {
		case '2' : // 상품부가사진
			$up_cfg['prdContent']['filesize'] = $_SESSION['h_spec']['img_upload_limit'];
			$up_cfg['prdContent']['ea'] = 20;
			$up_cfg['prdContent']['totalSize'] = $_SESSION['h_spec']['img_limit']*1024; // 제공하는 총 업로드 공간
		break;
		case '3' : // 상품상세사진
        case '6' : // 상품상세사진 (모바일)
			$up_cfg['prdContent']['filesize'] = $_SESSION['h_spec']['content_img_upload_limit']*1000;
			$up_cfg['prdContent']['ea'] = 0;
			$up_cfg['prdContent']['totalSize'] = $_SESSION['h_spec']['img_limit']*1024; // 제공하는 총 업로드 공간
		break;
	}

	// 상품아이콘
	$up_cfg['prdIcon']['filesize'] = 100;
	$up_cfg['prdIcon']['ea'] = 0;

	// 공통정보
	$up_cfg['prdCommon']['filesize'] = 1024;
	$up_cfg['prdCommon']['ea'] = 5;

	// 옵션 아이콘
	$up_cfg['prdOption']['filesize'] = 1024;
	$up_cfg['prdOption']['ea'] = 0;

	// 세트리스트
	$up_cfg['setBasic']['filesize'] = $up_cfg['prdBasic']['filesize'];
	$up_cfg['setBasic']['ea'] = 1;

	// 세트 추가 이미지
	$up_cfg['setAdd']['filesize'] = $up_cfg['prdContent']['filesize'];
	$up_cfg['setAdd']['ea'] = 1;

	// 세트 본문삽입
	$up_cfg['setContent']['filesize'] = 1024;
	$up_cfg['setContent']['ea'] = 5;

	// 회원그룹 아이콘
	$up_cfg['memGroup']['filesize'] = 1024;
	$up_cfg['memGroup']['ea'] = 0;

	// 팝업첨부이미지
	$up_cfg['popup']['filesize'] = 3072;

	// 팝업스킨 첨부이미지
	$up_cfg['popupSkin']['filesize'] = 1024;

	// 이메일 첨부이미지
	$up_cfg['email']['filesize'] = 1024;
	$up_cfg['email']['ea'] = 5;

	// favicon
	$up_cfg['favicon']['filesize'] = 1024;
	$up_cfg['favicon']['ea'] = 1;

	// bgm
	$up_cfg['bgm']['filesize'] = 5120;
	$up_cfg['bgm']['ea'] = 1;

	// 이니시스 키파일
	$up_cfg['iniKey']['filesize'] = 1024;
	$up_cfg['iniKey']['ea'] = 1;

	// 쿠폰배너
	$up_cfg['coupon']['filesize'] = 1024;
	$up_cfg['coupon']['ea'] = 1;

	// 싸이로고
	$up_cfg['cyLogo']['filesize'] = 1024;
	$up_cfg['cyLogo']['ea'] = 1;

	// 인트라 게시판
	$up_cfg['intraCommu']['filesize'] = 1024;
	$up_cfg['intraCommu']['ea'] = 0;

	// [오프라인 매장] 썸네일 이미지
	$up_cfg['storeThum']['filesize'] = 1024;
	$up_cfg['storeThum']['ea'] = 0;

	// [오프라인 매장] 커버 이미지
	$up_cfg['storeMain']['filesize'] = 5120;
	$up_cfg['storeMain']['ea'] = 4;

    if (function_exists('wingUploadRule') == false) {
        function wingUploadRule($file, $title, $total_ea = 0, $totalsize = 0, $filetype = null) {
            global $engine_dir, $cfg;

            if(!$file) return;
            if(!file_exists($engine_dir.'/_engine/include/account/getHspec.inc.php')) {
                return;
            }
            if(defined('__USE_UNLIMITED_DISK__') == true) return;

            $config = $GLOBALS['up_cfg'][$title];
            if(isset($file['name']) && isset($file['size']) && isset($file['type'])) $file = array($file); // $_FILES 가 아니라 업로드 하나만 넘겨줄 경우

            # 파일갯수 체크
            $count = count($file);
            if($config['ea'] && $total_ea + $count > $config['ea']) {
                msg('최대 '.$config['ea'].'개 까지의 파일만 등록하실 수 있습니다');
            }

            # 단일 파일 용량 체크
            $uploadsize = 0;
            foreach($file as $resource) {
                $uploadsize += $resource['size'];
                if($config['filesize'] && $resource['size'] > $config['filesize']*1024) {
                    $limitsize = filesizeStr($config['filesize']*1024);
                    $filesize = filesizeStr($resource['size'], 2);
                    msg("다음 파일이 업로드 제한 용량 $limitsize 를 초과하였습니다.\n- $resource[name] ($filesize)");
                }
            }

            # 총 남은 용량 체크
            if($config['totalSize'] && $totalsize + $uploadsize > $config['totalSize']*1024) {
                $max_upload = filesizeStr($config['totalSize']*1024);
                $uploadsize = filesizeStr($uploadsize);
                $left = filesizeStr($config['totalSize']*1024 - $totalsize, 2);
                $totalsize = filesizeStr($totalsize, 2);
                msg("총 업로드 용량 $max_upload 를 초과하였습니다\\t\\n- 남은용량 : $left\\n- 업로드할 총 파일 용량 : $uploadsize");
            }
        }
    }

?>