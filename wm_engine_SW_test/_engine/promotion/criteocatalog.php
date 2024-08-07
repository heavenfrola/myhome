<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  크리테오 상품카탈로그
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	$document = "xml";
	include_once $engine_dir."/_engine/include/common.lib.php";

	if($GLOBALS[cfg]['criteo_use'] != '1') exit('크리테오 사용계정이 아닙니다.');

	// 개인결제창 카테고리
	function getPrivateCate($cate1, $cate2 = null, $cate3 = null) {
		global $tbl,$comname,$filetype, $pdo;

		$cate = preg_replace("/^,+|,+$/", "", $cate1.",".$cate2.",".$cate3);
		if (!$cate) return;
		$private = $pdo->row("select count(*) from `$tbl[category]` where `no` in ($cate) and `private` = 'Y'");

		return $private;
	}

	$file_url = getFiledir('_data/product');
	if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) $file_url = $cfg['cdn_url'];

    $add_qry = '';
    if ($scfg->comp('compare_explain', 'Y') == true) {
        $add_qry .= " and no_ep!='Y'";
    }

	header("Content-Type: text/xml; charset=utf-8");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
	echo "<products>\n";
	$res = $pdo->iterator("
        select name, hash, big, mid, small, updir, upfile2, upfile3, sell_prc, content1
        from {$tbl['product']}
        where prd_type='1' and stat='2' and wm_sc='0'  $add_qry order by no asc
    ");
    foreach ($res as $data) {
		if(getPrivateCate($data['big'], $data['mid'], $data['small'])) continue;
		$data['name'] = strip_tags($data['name']);
		$data['name'] = $data['name'];

		if(!$criteo_cache[$data['big']]) {
			$criteo_cache[$data['big']] = stripslashes($pdo->row("select name from $tbl[category] where no='$data[big]'"));
		}
		$category1 = $criteo_cache[$data['big']];

	echo "<product id=\"".$data['hash']."\">\n";
	echo "<name><![CDATA[".$data['name']."]]></name>\n";
	echo "<smallimage><![CDATA[".getListImgURL($data['updir'], $data['upfile3'])."]]></smallimage>\n";
	echo "<bigimage><![CDATA[".getListImgURL($data['updir'], $data['upfile2'])."]]></bigimage>\n";
	echo "<producturl><![CDATA[".$root_url."/shop/detail.php?pno=".$data['hash']."&ref=criteo]]></producturl>\n";
	echo "<price>".$data['sell_prc']."</price>\n";
	echo "<description><![CDATA[".$data['content1']."]]></description>\n";
	echo "<categoryid1><![CDATA[$category1]]></categoryid1>";
	echo "</product>\n";
	}
	echo "</products>";

?>