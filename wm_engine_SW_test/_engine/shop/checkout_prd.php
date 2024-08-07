<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  체크아웃 상품정보 제공
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if($cfg['npay_ver'] == 2) {
		include 'checkout_prdV2.inc.php';
		return;
	}

	$qry = explode('&', $_SERVER['QUERY_STRING']);
	$file_url = getFiledir('_data/product');
	$cdepth = array(1 => 'first', 2 => 'secode', 3 => 'third');


	//상품정보 구성
	foreach($qry as $arg) {
		list($key, $val) = explode('=', $arg);
		if($key == 'ITEM_ID') {
			$pno = preg_replace('/_.*$/', '', $val);
			$pno = numberOnly($pno);
			$qry = "select p.* from `$tbl[product]` p where p.`no` = '$pno'";
			$prd = $pdo->assoc($qry);
			$prd = shortCut($prd);
			if(!$prd['no']) continue;

			$prd['name'] = iconv(_BASE_CHARSET_, 'utf-8', stripslashes(str_replace('&', '&amp;', $prd['name'])));
			$prd['content1'] = iconv(_BASE_CHARSET_, 'utf-8', stripslashes(str_replace('&', '&amp;', $prd['content1'])));
			$prd['ea'] = $prd['ea_stat'] == 3 ? $prd['ea'] : 999;
			$prd[content2] = htmlspecialchars(stripslashes($prd[content2]));

			$items .= "<item id='$val'>\r\n";
			$items .= "	<name><![CDATA[$prd[name]]]></name>\r\n";
			$items .= "	<url>$root_url/shop/detail.php?pno=$prd[hash]</url>\r\n";
			$items .= "	<description><![CDATA[$prd[name]]]></description>\r\n";
			$items .= "	<thumb>$file_url/$prd[updir]/$prd[upfile3]</thumb>\r\n";
			$items .= "	<image>$file_url/$prd[updir]/$prd[upfile2]</image>\r\n";
			$items .= "	<price>$prd[sell_prc]</price>\r\n";
			$items .= "	<quantity>$prd[ea]</quantity>\r\n";

			# 상품 옵션
			$opt = $pdo->iterator("select * from `$tbl[product_option_set]` where `pno`='$prd[no]' order by `sort` asc");
            foreach ($opt as $option) {
				$oname = iconv(_BASE_CHARSET_, 'utf-8', stripslashes(str_replace('&', '&amp;', $option['name'])));
				$oitem = explode('@', $option['items']);
				$items .= "	<option name='$oname'>\r\n";
				foreach($oitem as $key2 => $val2) {
					if(!$val2) continue;
					$val2 = preg_replace('/:.*$/', '', $val2);
					$iname = iconv(_BASE_CHARSET_, 'utf-8', stripslashes(str_replace('&', '&amp;', $val2)));
					$items .= "		<select><![CDATA[$iname]]></select>\r\n";
				}
				$items .= "	</option>\r\n";
			}

			# 상품 카테고리
			$items .= "	<category>\r\n";
			$cate = $pdo->iterator("select `name`, `level` from `$tbl[category]` where `no` in ($prd[big],$prd[mid],$prd[small]) order by `level` asc");
            foreach ($cate as $cname) {
				$cname['name'] = iconv(_BASE_CHARSET_, 'utf-8', stripslashes(str_replace('&', '&amp;', $cname['name'])));
				$depth = $cdepth[$cname['level']];
				$items .= "		<$depth><![CDATA[$cname[name]]]></$depth>\r\n";
			}
			$items .= "	</category>\r\n";

			$items .= "</item>\r\n";
		}

	}


	// XML 출력
	header("Content-Type: application/xml; charset=UTF-8");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
?>
<response>
	<?=$items?>
</response>