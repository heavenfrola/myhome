<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 지식쇼핑 3.0 TSV 상품 카탈로그
	' +----------------------------------------------------------------------------------------------+*/

	$urlfix = 'Y';
	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/cart.class.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	set_time_limit(0);
	header('Content-type: text/plain;charset='._BASE_CHARSET_);

    if ($feedtype == '3' || $feedtype == '4') { // 도서 EP
        require 'navershop_feed_book.jsonl.php';
        return;
    }

	function fputtsvfeed($array) {
		if(!is_array($array)) return;

		$csv = '';
		foreach($array as $key => $val) {
			if($csv) $csv .= "\t";
			$csv .= str_replace("\t", "", $val);
		}
		$csv .= "\n";

		return $csv;
	}

	$ep_today = date('Ymd');
	$no_qcheck = true;
	unset($member); // 회원 할인/이벤트 가격 반영 금지

	// 예외 카테고리
	$exists_cno = $pdo->row("select group_concat(no) from $tbl[category] where private='Y' or hidden='Y'");
	if($exists_cno) {
		$add_qry .= " and (";
		$add_qry .= " big not in ($exists_cno) and mid not in ($exists_cno) and small not in ($exists_cno)";
		if($cfg['max_cate_depth'] >= 4) {
			$add_qry .= " and depth4 not in ($exists_cno)";
		}
		$add_qry .= ")";
	}

	// 카테고리명 캐시
	$ccache = array();
	$res = $pdo->iterator("select no, name from $tbl[category] where ctype=1");
    foreach ($res as $data) {
		$ccache[$data['no']] = stripslashes($data['name']);
	}

	// 브랜드명 캐시
	if($cfg['compare_brand'] == 'xbig' || $cfg['compare_brand'] == 'ybig') {
		$ctype = ($cfg['compare_brand'] == 'xbig') ? 4 : 5;
		$brandcache = getCategoriesCache($ctype);
	}

	// 상품 이미지 경로
	$_imgurl = getFileDir('_data/product');
	if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) {
		$_imgurl = $cfg['cdn_url'];
	} else {
		if(is_array($asvcs) == false) {
			$weca = new weagleEyeClient($_we, 'account');
			$asvcs = $weca->call('getSvcs',array('key_code'=>$GLOBALS['wec']->config['wm_key_code'], 'use_cdn'=>$cfg['use_cdn']));
		}
	}

	// 피드 구조
	$feed_struct = array(
		'id',
		'title',
		'price_pc',
		'link',
		'image_link',
		'category_name1',
		'category_name2',
		'category_name3',
        'search_tag',
		'manufacture_define_number',
		'brand',
		'point',
		'review_count',
		'shipping',
		'class',
		'update_time',
		'mobile_link',
		'import_flag',
		'shipping_settings',
	);

	if($feedtype == 2) {
		$add_qry .= " and edt_date>'".strtotime(date('Y-m-d 00:00:00'))."'";
		$search_stat = '2,3';
	} else {
		$search_stat = '2';
	}
    if ($scfg->comp('compare_explain', 'Y') == true) {
        $add_qry .= " and no_ep!='Y'";
    }

    if ($scfg->comp('use_navershopping_book', 'Y') == true) {
        $asql .= " and is_book='N'";
    }

	$add_field = '';
	if($cfg['use_partner_shop'] == 'Y') {
		$add_field .= ", partner_no";
	}
	if($cfg['compare_image_no'] === '0') {
		$add_field .= ", upfile0";
	}
	if($cfg['compare_brand']) {
		$add_field .= ", xbig, ybig";
	}
	if($cfg['import_flag_use'] == 'Y') {
		$add_field .= ", import_flag";
	}
	if($cfg['compare_today_start_use'] == 'Y') {
		$add_field .= ", compare_today_start";
	}
    if ($cfg['use_prd_dlvprc'] == 'Y') {
        $add_field .= ", delivery_set";
    }

    if ($cfg['checkout_id'] && ($cfg['npay_review_general'] == 'Y' || $cfg['npay_review_premium'] == 'Y')) {
		$add_field .= ", (SELECT COUNT(*) FROM wm_review WHERE pno=p.no and npay!='Y') AS rev_cnt";
    }

	echo fputtsvfeed($feed_struct);
	$res = $pdo->iterator("select no, hash, name, keyword, code, updir, upfile1, upfile2, upfile3, big, mid, small, sell_prc, rev_cnt, edt_date, reg_date, stat, free_delivery as free_dlv $add_field
	from $tbl[product] p where stat in ($search_stat) and wm_sc=0 $add_qry");

    foreach ($res as $data) {
		$prdCart = new OrderCart();
		$prdCart->addCart($data);
		$prdCart->complete();

		if(!$data['edt_date']) $data['edt_date'] = $data['reg_date'];

		$feed['id'] = $data['hash'];
		switch($cfg['compare_goods_name']) {
			case '1' : 	$feed['title'] = stripslashes($data['name']); break;
			case '2' : 	$feed['title'] = stripslashes($data['keyword']); break;
			case '3' : 	$feed['title'] = stripslashes($data['name'].' '.$data['keyword']); break;
		}
		if(!$feed['title']) $feed['title'] = stripslashes($data['name']);

		if(!$data['upfile'.$cfg['compare_image_no']]) {
			for($i = 1; $i <= 3; $i++) {
				if($data['upfile'.$i]) {
					$data['upfile'.$cfg['compare_image_no']] = $data['upfile'.$i];
					break;
				}
			}
		}

		if($cfg['compare_brand']) {
			switch($cfg['compare_brand']) {
				case 'xbig' : $brand = $brandcache[$data['xbig']]; break;
				case 'ybig' : $brand = $brandcache[$data['ybig']]; break;
				default :
					list($dummy, $fno) = explode('@', $cfg['compare_brand']);
					$brand = $pdo->row("select value from $tbl[product_filed] where fno='$fno' and pno='$data[no]'");
					$brand = stripslashes($brand);
				break;
			}
		}

		$feed['title'] = strip_tags(stripslashes($feed['title']));
		$feed['price_pc'] = parsePrice($prdCart->pay_prc-$prdCart->dlv_prc);
		$feed['image_link'] = $_imgurl.'/'.$data['updir'].'/'.$data['upfile'.$cfg['compare_image_no']];
		if(defined('__use_free_img__') == true) {
			$feed['image_link'] = str_replace('_data/product/', '', $feed['image_link']);
		} else {
			if($cfg['ssl_type'] == 'Y') { // 네이버쇼핑 강제로 http 로 전송
				$feed['image_link'] = str_replace('https://', 'http://', $feed['image_link']);
			}
		}

		$feed['link'] = $root_url.'/shop/detail.php?pno='.$data['hash'].'&ref=naver_open';
		$feed['category_name1'] = $ccache[$data['big']];
		$feed['category_name2'] = $ccache[$data['mid']];
		$feed['category_name3'] = $ccache[$data['small']];
		$feed['manufacture_define_number'] = $data['code'];
        $feed['search_tag'] = $data['keyword'];
		$feed['brand'] = stripslashes($brand);
		$feed['point'] = ($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) ? ($data['sell_prc']/100)*$cfg['milage_type_per'] : $data['milage'];
		$feed['review_count'] = $data['rev_cnt'];
		$feed['shipping'] = ($prdCart->cod_prc > 0 && $prdCart->dlv_prc == 0) ? -1 : $prdCart->dlv_prc;
		$feed['class'] = ($ep_today == date('Ymd')) ? 'I' : 'U';
		if($data['stat'] == 3) $feed['class'] = 'D';
		$feed['update_time'] = date('Y-m-d H:i:s', $data['edt_date']);
		$feed['mobile_link'] = $m_root_url.'/shop/detail.php?pno='.$data['hash'].'&ref=naver_open';
		if(!$feed['manufacture_define_number']) $feed['manufacture_define_number'] = $data['no'];
		if(!$feed['point']) $feed['point'] = '0';
		$feed['import_flag'] = $data['import_flag'];
		if($data['compare_today_start'] == 'Y') {
			$feed['shipping_settings'] = "오늘출발^".$cfg['compare_today_time'].":00^^^^^^^^";
		}

		$line = array();
		foreach($feed_struct as $key) {
			$line[$key] = $feed[$key];
		}

		echo fputtsvfeed($line);
	}

?>