<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	header('Content-type:application/json; charset=utf-8;');

	$word = addslashes($_REQUEST['word']);
	if(!$word) {
		exit(json_encode(array(
			'result' => 'faild'
		)));
	}

	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}

	$sql = "select a.no, a.seller_idx, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.big, a.mid, a.small, a.wm_sc, a.origin_prc" .
		 "      , b.complex_no, b.barcode, b.opts $add_field " .
		 "  from wm_product a inner join erp_complex_option b on a.no=b.pno" .
		 " where a.wm_sc=0 and a.stat in ('2','3','4')" .
		 "   and b.del_yn = 'N'" .
		 "   and (a.name like '%{$word}%' or b.barcode = '{$word}')";

	$res = $pdo->iterator($sql);
	$rows = $res->rowCount();
	$datas = array();
    foreach ($res as $data) {
		// 이미지 파일명
		if(!$file_dir) $file_dir = getFileDir($data['updir']);
		$is = setImageSize($data['w3'], $data['h3'], 50, 50);
		$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";

		// 상품 정보
		$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다' />" : $data['name'];
		$complex_option_name = getComplexOptionName($data['opts']);
		$category_name = makeCategoryName($data, 1);

		// 현재고 및 최종 입고가
		$data['in_price'] = $pdo->row("select in_price FROM erp_inout where complex_no='$data[complex_no]' and inout_kind='I' order by inout_no desc limit 1");
		$data['current_qty'] = $pdo->row("select curr_stock($data[complex_no])");
		if(!$data['in_price']) $data['in_price'] = $data['origin_prc'];
		$data['in_price'] = parsePrice($data['in_price']);

		$datas[] = array_merge(array(
			'imgstr' => $imgstr,
			'category_name' => $category_name,
			'productname' => $productname,
			'complex_option_name' => $complex_option_name,

		), $data);
	}

	exit(json_encode(array(
		'result' => 'success',
		'rows' => $rows,
		'datas' => $datas
	)));

?>