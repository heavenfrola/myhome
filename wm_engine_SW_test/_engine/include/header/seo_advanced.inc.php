<?PHP

	function parseMetaTags($str, $replace_key) {
		foreach($replace_key as $key => $val) {
			$str = str_replace('{'.$key.'}', trim($val), $str);
		}
		$str = preg_replace('/{[^}]+}/', '', $str);
		$str = strip_tags(stripslashes(trim($str)));

		return $str;
	}

	// 현재 페이지 체크 및 페이지별 치환코드 세팅
	$replace_key = array(
		'쇼핑몰명' => $cfg['company_mall_name'],
	);
	switch($_SERVER['SCRIPT_NAME']) {
		case '/shop/big_section.php';
			$page = 'prdList';

			$replace_key['분류명'] = $GLOBALS['_cno1']['name'];
			$replace_key['상세분류명'] = strip_tags(getPrdPath());
			break;
		case '/shop/detail.php' :
			$page = 'prdDetail';

			$replace_key['상품명'] = $GLOBALS['prd']['name'];
			$replace_key['참고상품명'] = $GLOBALS['prd']['name_referer'];
			$replace_key['요약설명'] = $GLOBALS['prd']['content1'];
			$replace_key['검색키워드'] = $GLOBALS['prd']['keyword'];
			$replace_key['분류명'] = $GLOBALS['_cno1']['name'];
			break;
		case '/shop/product_qna_list.php' :
			$page = 'boardList';

			$replace_key['게시판명'] = __lang_common_bbs_qna__;
			break;
		case '/shop/product_review_list.php' :
			$page = 'boardList';

			$replace_key['게시판명'] = __lang_common_bbs_review__;
			break;
		case '/shop/product_qna.php' :
			$page = 'boardView';

			$rno = numberOnly($_GET['rno']);
			$board = $pdo->assoc("select title from $tbl[qna] where no='$rno'");
			$replace_key['게시판명'] = __lang_common_bbs_qna__;
			$replace_key['게시물제목'] = $board['title'];
			break;
		case '/shop/product_review.php' :
			$page = 'boardView';

			$rno = numberOnly($_GET['rno']);
			$board = $pdo->assoc("select title from $tbl[review] where no='$rno'");
			$replace_key['게시판명'] = __lang_common_bbs_review__;
			$replace_key['게시물제목'] = $board['title'];
			break;
		case '/board/index.php' :
			if(!$_REQUEST['mari_mode'] || $_REQUEST['mari_mode'] == 'view@list') $page = 'boardList';
			if($_REQUEST['mari_mode'] == 'view@view') {
				$page = 'boardView';

				$article_no = numberOnly($_GET['no']);
				$board = $pdo->assoc("select * from {$GLOBALS[mari_set][mari_board]} where no='$article_no'");
				$replace_key['게시물제목'] = $board['title'];
			}
			$replace_key['게시판명'] = $GLOBALS['config']['title'];
			break;
	}
	if(!$page) $page = 'common';

	// 기본 메타태그 생성
	$meta = $pdo->assoc("select * from $tbl[seo_config] where tag_type='meta' and page='$page'");
	if($meta['title']) $cfg['br_title'] = parseMetaTags($meta['title'], $replace_key);
	$cfg['meta_key'] = parseMetaTags($meta['keyword'], $replace_key);
	$cfg['meta_des'] = parseMetaTags($meta['description'], $replace_key);

	// Open Graph 태그 생성
	$og = $pdo->assoc("select * from $tbl[seo_config] where tag_type='og' and page='$page'");
	$cfg['og_title'] = parseMetaTags($og['title'], $replace_key);
	$cfg['og_description'] = parseMetaTags($og['description'], $replace_key);
	if($og['image_use'] == 'N' || !$og['image_use']) {
		$cfg['og_image'] = '';
	} else {
		if($og['image_use'] == 'Y') { // 업로드 한 이미지를 og:image에 이용 이용
			if($og['upfile1']) {
				$cfg['og_image'] = getListImgURL($og['updir'], $og['upfile1']);
			}
		} else {
			switch($page) {
				case 'prdList' :
					if($og['image_use'] == 'T') {
						$cfg['og_image'] = getListImgURL($GLOBALS['_cno1']['updir'], $GLOBALS['_cno1']['upfile1']);
					} else if($og['image_use'] == 'A') {
						if ($GLOBALS['_cno1']['ctype'] == '2') {
							$_prd_join = " inner join {$tbl['product_link']} l on p.no=l.pno";
						}
						$prd = $pdo->assoc("select p.updir, p.upfile3 from $tbl[product] p $_prd_join where 1 $GLOBALS[prdWhere] order by $GLOBALS[prdOrder] limit 1");
						$cfg['og_image'] = getListImgURL($prd['updir'], $prd['upfile3']);
					}
					break;
				case 'prdDetail' :
					$og['image_use'] = numberOnly($og['image_use']);
					if($og['image_use'] > 0 && $prd['upfile'.$og['image_use']]) {
						$cfg['og_image'] = getListImgURL($prd['updir'], $prd['upfile'.$og['image_use']]);
					}
					break;
				case 'boardView' :
					$og['image_use'] = numberOnly($og['image_use']);
					if($og['image_use'] > 0 && $board['upfile'.$og['image_use']]) {
						$cfg['og_image'] = getListImgURL('/board/'.$board['up_dir'], $board['upfile'.$og['image_use']]);
					}
					break;
			}
		}
	}

?>