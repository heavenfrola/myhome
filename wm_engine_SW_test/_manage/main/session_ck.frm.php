<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  세션 분석
	' +----------------------------------------------------------------------------------------------+*/
	$v_count = array();
	$v_cate = array("main" => "메인", "shop" => "카테고리", "cart" => "장바구니", "detail" => "상품상세", "order" => "주문서", "bbs" => "게시판" ,"mypage" => "마이쇼핑", "manage" => "관리자", "etc" => "기타");
	$v_array_shop = array("shop/big_section.php", "coordi/coordi_list.php", "coordi/coordi_view.php");
	$v_array_detail = array("shop/detail.php", "coordi/coordi_view.php");
	$v_array_cart = array("shop/cart.php");
	$v_array_order = array("shop/order.php");
	$v_array_bbs = array("shop/product_review_list.php", "shop/product_review.php", "shop/product_qna_list.php", "shop/product_qna.php");
	$v_array_manage = array("_manage");

	$sw.=" and left(`remote_addr`, 12) != '121.254.156.' and left(`remote_addr`, 12) != '121.254.159.' and left(`remote_addr`, 8) != '27.1.44.' ";

	$session_time = $now - 300;
	$v_count_total = $pdo->row("select count(*) from wm_session where accesstime >= '$session_time' $sw");
	$res = $pdo->iterator("select member_no, page, data from wm_session where accesstime >= '$session_time' $sw");
    foreach ($res as $session) {
		$v_count++;

		$member_no = $session['member_no'];
		$script_name = preg_replace('/^\/|\?.*$/', '', $session['page']);
		list($v_dir, $v_file) = explode("/", $script_name);

		$v_code = '';
		if($v_dir == 'main' || $v_dir == '') $v_code = 'main';
		if($v_dir == 'mypage') $v_code = 'mypage';
		if($v_dir == 'board' || in_array($script_name, $v_array_bbs)) $v_code = 'bbs';
		if(in_array($script_name, $v_array_detail)) $v_code = 'detail';
		if(in_array($script_name, $v_array_order)) $v_code = 'order';
		if(in_array($script_name, $v_array_shop) || (!$v_code && $v_dir == 'shop')) $v_code = 'shop';
		if(preg_match('/^_manage/', $script_name)) $v_code = 'manage';
		if(!$v_code) $v_code = 'etc';

		$v_count[$v_code]++;
	}

	$v_max = @max($v_count);
	foreach($v_cate as $ccode => $cname){
		$val = $v_count[$ccode];
		$per[$ccode] = @floor(($val / $v_max) * 100);
		if($per[$ccode] == 0) $per[$ccode] = 1;
	}

?>