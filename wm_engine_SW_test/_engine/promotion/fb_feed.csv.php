<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이스북 상품 Feed
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	header('Content-type: application/vnd.ms-excel;charset='._BASE_CHARSET_);
	header('Content-Disposition: attachment; filename=product.csv');
	header('Content-Description: Wisamall Excel Data');

	function fputcsvfeed($array) {
		if(!is_array($array)) return;

		$fp = fopen('php://output', 'r+');
		fputcsv($fp, $array);
		$csv = fgets($fp);
		fclose($fp);

		return $csv;
	}

	// 예외 카테고리
    $add_qry = '';
	$exists_cno = $pdo->row("select group_concat(no) from $tbl[category] where private='Y' or hidden='Y'");
	if($exists_cno) {
		$add_qry .= " and (big not in ($exists_cno) and mid not in ($exists_cno) and small not in ($exists_cno))";
	}
    if ($scfg->comp('compare_explain', 'Y') == true) {
        $add_qry .= " and no_ep!='Y'";
    }

	// 카테고리명 캐시
	$ccache = array();
	$res = $pdo->iterator("select no, name from $tbl[category] where ctype=1");
    foreach ($res as $data) {
		$ccache[$data['no']] = stripslashes($data['name']);
	}

	// 화폐단위
	$currency = 'KRW';
	if($cfg['currency_type'] != '원') $currency = $cfg['currency_type'];

	// 피드 구조
	$feed_struct = array(
		'id',
		'availability',
		'condition',
		'description',
		'image_link',
		'link',
		'title',
		'price',
		'mpn',
		'additional_image_link',
		'product_type',
	);

	// 피드 출력
	echo fputcsvfeed($feed_struct);

	$res = $pdo->iterator("select no, hash, name, keyword, ea_type, content1, content2, updir, upfile1, upfile2, upfile3, big, mid, small, sell_prc from $tbl[product] where stat in (2,3) and wm_sc=0 $add_qry");
    foreach ($res as $data) {
		$feed['id'] = $data['hash'];

		if($data['ea_type'] == 1) {
			if(isWingposStock($data['no']) == true) {
				$feed['availability'] = 'in stock';
			} else {
				$feed['availability'] = 'out of stock';
			}
		} else {
			$feed['availability'] = 'in stock';
		}
		$feed['condition'] = 'new';
		$feed['description'] = ($data['content1']) ? $data['content1'] : stripslashes($data['content2']);
		$feed['description'] = strip_tags($feed['description']);
		if(!$feed['description']) $feed['description'] = $data['name'];
		$feed['image_link'] = getFileDir($data['updir']).'/'.$data['updir'].'/'.$data['upfile'.$cfg['fb_ad_image_no']];
		$feed['link'] = $root_url.'/shop/detail.php?pno='.$data['hash'];
		$feed['price'] = parsePrice($data['sell_prc']).' '.$currency;
		switch($cfg['fb_ad_goods_name']) {
			case '1' : 	$feed['title'] = stripslashes($data['name']); break;
			case '2' : 	$feed['title'] = stripslashes($data['keyword']); break;
			case '3' : 	$feed['title'] = stripslashes($data['name'].' '.$data['keyword']); break;
		}
		$feed['mpn'] = $data['no'];
		$feed['product_type'] = $ccache[$data['big']];
		if($data['mid'] > 0) $feed['product_type'] .= ' > '.$ccache[$data['mid']];
		if($data['small'] > 0) $feed['product_type'] .= ' > '.$ccache[$data['small']];

		$feed['additional_image_link'] = '';
		$img = $pdo->iterator("select updir, filename from $tbl[product_image] where pno='$data[no]' and filetype in (2, 8)");
        foreach ($img as $key => $idata) {
			if($feed['additional_image_link']) $feed['additional_image_link'] .= ',';
			$feed['additional_image_link'] .= getFileDir($idata['updir']).'/'.$idata['updir'].'/'.$idata['filename'];
		}

		$line = array();
		foreach($feed_struct as $key) {
			$line[$key] = $feed[$key];
		}

		echo fputcsvfeed($line);
	}

?>