<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	define('_LOAD_AJAX_PAGE_', true);

	$rno = numberOnly($_GET['rno']);
	$data = $pdo->assoc("select * from {$tbl['review']} where no='$rno'");

	if(!$rno || !$data['no']) {
		header('Content-type: application/json;');
		exit(json_encode(array(
			'status' => 'error',
			'message' => __lang_common_error_nodata__
		)));
	}

	$data = reviewOneData($data);

	// 기본 첨부파일
	$attach_list = array();
	for($i = 1; $i <= 2; $i++) {
		if($data['upfile'.$i]) $attach_list[] = getFileDir($data['updir']).'/'.$data['updir'].'/'.$data['upfile'.$i];
	}

	// 본문 삽입 첨부이미지를 일반 첨부 이미지로 분리
	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	@$dom->loadHTML(
		'<meta http-equiv="Content-Type" content="text/html; charset='._BASE_CHARSET_.'">'.$data['content']
	);
	$img = $dom->getElementsByTagName('img');
	$rm = array();
	foreach($img as $el) {
		$rm[] = $el;
	}
	foreach($rm as $key => $val) {
		$attach_list[] = $val->getAttribute('src');
		if($val->parentNode->childNodes->length == 1) {
			$val->parentNode->parentNode->removeChild($val->parentNode);
		} else {
			$val->parentNode->removeChild($val);
		}
	}
	$data['content'] = preg_replace('/.*<body>(.*)<\/body>.*/is', '$1', $dom->saveHTML());
	$data['content'] = str_replace('<p></p>', '', $data['content']);
	$data['content'] = preg_replace('/(<br>\s*)+/', '<br>', trim($data['content']));

	// 조회수 추가
	$pdo->query("update {$tbl['review']} set hit=hit+1 where no='$rno'");

	// 관련 상품
	if($data['pno']) {
		$prd = $pdo->assoc("select no, hash, updir, upfile3, name from {$tbl['product']} where no='{$data['pno']}' and stat in (2,3)");
		$prd['name'] = stripslashes($prd['name']);
		$prd['prd_img'] = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile3'];
		$prd['link'] = $root_url.'/shop/detail.php?pno='.$prd['hash'];
	}

	// 스킨 출력
	$_tmp_file_name = 'shop_product_review_detail.php';
	include_once $engine_dir."/_engine/common/skin_index.php";

?>