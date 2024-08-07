<?PHP

    ini_set('memory_limit', -1);
	include_once $engine_dir."/_engine/include/file.lib.php";

    function getPrivateCate($cate1, $cate2 = null, $cate3 = null) {
        global $tbl, $pdo;

        $cate = preg_replace('/^,+|,+$/', '', $cate1.','.$cate2.','.$cate3);
        if(!$cate) return;
        $private = $pdo->row("select count(*) from `$tbl[category]` where `no` in ($cate) and `private` = 'Y'");

        return $private;
    }

	function setEp($title, $value = null) {
		return "<<<$title>>>".stripslashes($value)."\n";
	}

	function getDeliv2($data) {
		global $cfg;

		switch ($cfg['delivery_type']) {
			case 1:
				$deliv = 0;   //무료배송
			break;
			case 2:
				$deliv = -1; // 착불 - 유료
			break;
			case 3:
				$prdCart = new OrderCart();
				$prdCart->addCart($data);
				$prdCart->complete();

				$deliv = $prdCart->getData('dlv_prc');
			break;
			default:
				$deliv = 0;
		};
		return $deliv;
	}

	$imgurl = getFileDir('_data/product');
	if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) $imgurl = $cfg['cdn_url'];

	$cres = $pdo->iterator("select no, name from $tbl[category] where ctype=1");
    foreach ($cres as $cdata) {
		$_cname[$cdata['no']] = stripslashes($cdata['name']);
	}

	$today = strtotime(date('Y-m-d 00:00:00'));

    $w = '';
    if ($scfg->comp('compare_explain', 'Y') == true) {
        $w .= " and no_ep!='Y'";
    }

	$cnt = 0;
	$ep_all = $ep_sum = '';
	$res = $pdo->iterator("
		select no, hash, sell_prc, normal_prc, name, edt_date, reg_date, updir, upfile1, upfile2, upfile3, big, mid, small, rev_avg, rev_cnt, stat, milage, big, mid, small
		from $tbl[product] where stat in (2, 3) and wm_sc=0 and (upfile2 != '' or upfile3 !='') $w order by no asc
	");
    foreach ($res as $sdata) {
		if(!$sdata['edt_date']) $sdata['edt_date'] = $sdata['reg_date'];
		if(!$sdata['upfile1'] && !$sdata['upfile2'] && !$sdata['upfile3']) continue;

		if(getPrivateCate($sdata['big'], $sdata['mid'], $sdata['small'])) continue;

		// 요약 EP
		if($sdata['reg_date'] >= $today || $sdata['edt_date'] >= $today) {
			$classv = 'I';
			if($sdata['reg_date'] < $sdata['edt_date']) $classv = 'U';
			if($sdata['stat'] == 3) $classv = 'D';
			$ep_sum .= setEp('begin');
			$ep_sum .= setEp('mapid', $sdata['no']);
			$ep_sum .= setEp('class', $classv);
			$ep_sum .= setEp('utime', date('Ymdhis', $sdata['edt_date']));
			$ep_sum .= setEp('pname', $sdata['name']);
			$ep_sum .= setEp('rating', $sdata['rev_avg'].'/5');
			$ep_sum .= setEp('price', parsePrice($sdata['sell_prc']));
			if($classv == 'I') {
				$ep_sum .= setEp('pgurl', $root_url.'/shop/detail.php?pno='.$sdata['hash']);
				$ep_sum .= setEp('cate1', $_cname[$sdata['big']]);
				$ep_sum .= setEp('caid1', $sdata['big']);
				if($_cname[$sdata['mid']]) {
					$ep_sum .= setEp('cate2', $_cname[$sdata['mid']]);
					$ep_sum .= setEp('caid2', $sdata['mid']);
				}
				if($_cname[$sdata['small']]) {
					$ep_sum .= setEp('cate3', $_cname[$sdata['small']]);
					$ep_sum .= setEp('caid3', $sdata['small']);
				}
				$ep_sum .= setEp('deliv', getDeliv2($sdata));
			}
			$ep_sum .= setEp('ftend');
		}

		if($sdata['stat'] == 3) continue;

		// 전체 EP
		$ep_all .= setEp('begin');
		$ep_all .= setEp('mapid', $sdata['no']);
		if($sdata['normal_prc'] > 0 && $sdata['sell_prc'] > $sdata['normal_prc']) $ep_all .= setEp('lprice', parsePrice($sdata['normal_prc']));
		$ep_all .= setEp('price', parsePrice($sdata['sell_prc']));
		//$ep_all .= setEp('mpric', parsePrice($sdata['sell_prc']));
		$ep_all .= setEp('pname', $sdata['name']);
		$ep_all .= setEp('pgurl', $root_url.'/shop/detail.php?pno='.$sdata['hash'].'&ref2=daum_howshop');
		if($sdata['upfile'.$cfg['show_image_no']]) {
			$ep_all .= setEp('igurl', $imgurl.'/'.$sdata['updir'].'/'.$sdata['upfile'.$cfg['show_image_no']]);
		}
		if(!$sdata['upfile'.$cfg['show_image_no']]) {
			for($i = 1; $i <= 3; $i++) {
				if($sdata['upfile'.$i]) {
					$ep_all .= setEp('igurl', $imgurl.'/'.$sdata['updir'].'/'.$sdata['upfile'.$i]);
				}
			}
		}
		$ep_all .= setEp('cate1', $_cname[$sdata['big']]);
		$ep_all .= setEp('caid1', $sdata['big']);
		if($_cname[$sdata['mid']]) {
			$ep_all .= setEp('cate2', $_cname[$sdata['mid']]);
			$ep_all .= setEp('caid2', $sdata['mid']);
		}
		if($_cname[$sdata['small']]) {
			$ep_all .= setEp('cate3', $_cname[$sdata['small']]);
			$ep_all .= setEp('caid3', $sdata['small']);
		}
		$ep_all .= setEp('point', parsePrice($sdata['milage']));
		$ep_all .= setEp('deliv', getDeliv2($sdata));
		$ep_all .= setEp('rating', $sdata['rev_avg'].'/5');
		$ep_all .= setEp('revct', $sdata['rev_cnt']);
		$ep_all .= setEp('ftend');

		$cnt++;
	}
	$ep_all = setEp('tocnt', $cnt).$ep_all;

	// 파일생성
	makeFullDir('_data/compare/daumDB/sh');

	$fp = fopen($root_dir.'/_data/compare/daumDB/sh/show_new.txt', 'w');
	fwrite($fp, $ep_all);
	fclose($fp);

	$fp = fopen($root_dir.'/_data/compare/daumDB/sh/show_sum.txt', 'w');
	fwrite($fp, $ep_sum);
	fclose($fp);

	if($body == 'openmarket@show.exe') {
		msg('', 'reload', 'parent');
	}
