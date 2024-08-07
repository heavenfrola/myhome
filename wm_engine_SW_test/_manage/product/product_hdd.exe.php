<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기본디스크 및 윙디스크 남은 용량 표시
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$filetype = numberOnly($_GET['filetype']);

	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code'], 'use_cdn'=>$cfg['use_cdn']));
	$mall_goods_idx = $asvcs[0]->mall_goods_idx[0];
	$cdn_use = ($asvcs[0]->cdn_use[0] == 'Y') ? 'Y' : 'N';

	$wdisk = $wec->get(140, false, true);
	$_basic_img_size_limit = $_SESSION['h_spec']['img_limit'] ? floor($_SESSION['h_spec']['img_limit'])." MB" : "무제한";
	if($mall_goods_idx == '3') {
		$__basic_img_size_used = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where `filetype` in (2, 3, 6)");
	} else {
		$__basic_img_size_used = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where 1");
	}

	if($cdn_use == 'Y') {
		if($mall_goods_idx == '4') {
			$__basic_img_size_limit = $asvcs[0]->cdn_limit[0]*1000;
			$__basic_img_size_used = $asvcs[0]->cdn_used[0]*=1024;
		}
	}
	$_basic_img_size_used = filesizeStr($__basic_img_size_used);


	if($wdisk[0]->img_finish_date[0] < $now) $wdisk[0]->img_limit[0] = 0;
	$_wdisk_limit = number_format($wdisk[0]->img_limit[0]).'MB';
	$_wdisk_used = filesizeStr($wdisk[0]->img_used[0], 1, 2);

	$left = filesizeStr(($wdisk[0]->img_limit[0]*1024*1024)-$wdisk[0]->img_used[0], 1, 2);

	if($_GET['viewper'] == 'true') {
		if($filetype == 3 || $filetype == 6) {
            if ($__basic_img_size_used > 0) {
    			$per = @round(($__basic_img_size_used / ($_SESSION['h_spec']['img_limit'] * 1024 * 1024))*98,2);
            } else {
                $per = 0;
            }

			echo "<img src='$engine_url/_manage/image/file_graph_bg.gif'> $_basic_img_size_used / $_basic_img_size_limit ($per%)";
			if($per > 100) $per = 100;
			echo "\n".(($per*3)+6);
		}

		if($filetype == 9 || $filetype == 7) {
			$per = @@round(($wdisk[0]->img_used[0] / ($wdisk[0]->img_limit[0] * 1024 * 1024))*98,2);

			echo "<img src='$engine_url/_manage/image/file_graph_bg.gif'> $_wdisk_used / $_wdisk_limit ($per% / $left)";
			if($per > 100) $per = 100;
			echo "\n".(($per*3)+6);
			exit;

		}
	}

?>