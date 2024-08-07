<?PHP

	if(!$pdata['no']) return;

	if(trim(strip_tags($pdata['content'], '<img>')) == '') {
		$pdata['content'] = '';
	}
	if(trim(strip_tags($pdata['m_content'], '<img>')) == '') {
		$pdata['m_content'] = '';
	}
	$_replace_code[$_file_name]['promotion_content'] = $pdata['content'];
	$_replace_code[$_file_name]['promotion_m_content'] = $pdata['m_content'];


	// 프로모션 기획전 목록
	$pres = $pdo->iterator("
		select no, promotion_nm, date_start, date_end
		from $tbl[promotion_list]
		where use_yn='Y'
		order by sort asc
	");

	$_tmp = '';
	$_line = getModuleContent('promotion_list');
    foreach ($pres as $prm) {
		$prm['promotion_nm'] = stripslashes($prm['promotion_nm']);
		$prm['link'] = $root_url.'/shop/promotion.php?pno='.$prm['no'];
		if($prm['period_type'] == 'Y') {
			$prm['date_start_s'] = date('Y-m-d H:i', strtotime($prm['date_start']));
			$prm['date_end_s'] = date('Y-m-d H:i', strtotime($prm['date_end']));
		}
		$prm['is_active'] = ($pno == $prm['no']) ? 'active' : '';

		$_tmp .= lineValues('promotion_list', $_line, $prm);
	}
	$_replace_code[$_file_name]['promotion_list'] = listContentSetting($_tmp, $_line);;
	unset($_tmp);

	// 프로모션 상품그룹 목록
	$_line = getModuleContent('promotion_pgrp_list');
	$_lin2 = getModuleContent('promotion_product_list');
	if(!$pdata['status'] || $pdata['status'] == 'progress') { // 프로모션 기획전의 상태가 정상일때만 출력
		$pres = $pdo->iterator("
			select b.*
			from $tbl[promotion_link] a inner join $tbl[promotion_pgrp_list] b on a.pgrp_no=b.no
			where a.prm_no='$pno'
			order by a.sort asc
		");

		if(!$_skin['promotion_product_img_fd']) $_skin['promotion_product_img_fd'] = 3;
		if(!$_skin['promotion_product_img_w']) $_skin['promotion_product_img_w'] = $cfg['thumb3_w_mng'];
		if(!$_skin['promotion_product_img_h']) $_skin['promotion_product_img_h'] = $cfg['thumb3_h_mng'];
		if(!$_skin['promotion_product_namecut']) $_skin['promotion_product_namecut'] = 0;
		if(!$_skin['promotion_over_product_img_fd']) $_skin['promotion_over_product_img_fd'] = '';
		if($_skin['promotion_over_product_img_fd'] && $_skin['promotion_over_product_img_fd'] == $_skin['promotion_product_img_fd']) {
			$_skin['promotion_over_product_img_fd'] = '';
		}

		$_tmp = '';
		$imgurl = getFileDir('_data/promotion').'/_data/promotion/';
        foreach ($pres as $ppg) {
			$ppg['pgrp_no'] = $ppg['no'];
			$ppg['pgrp_nm'] = stripslashes($ppg['pgrp_nm']);
			$ppg['banner_text'] = ($ppg['banner_text']) ? stripslashes($ppg['banner_text']) : $ppg['pgrp_nm'];

            if ($ppg['link']) {
                switch($ppg['link_type']) {
                    case '2' :
                        $_pno = $pdo->row("select hash from {$tbl['product']} where no=?", array($ppg['link']));
                        $ppg['link'] = '/shop/detail.php?pno='.$_pno;
                    break;
                    case '3' :
                        $ppg['link'] = '/shop/big_section.php?cno1='.$ppg['link'];
                    break;
                }
            }
			for($i = 1; $i <= 2; $i++) {
				if($ppg['upfile'.$i]) {
					$ppg['upfile'.$i] = $imgurl.$ppg['upfile'.$i];
                    $ppg['banner'.$i] = sprintf('<img src="%s" alt="%s">', $ppg['upfile'.$i], $ppg['banner_text']);
                    if ($ppg['link']) {
                        $ppg['banner'.$i] = sprintf('<a href="%s" target="%s">%s</a>', $ppg['link'], $ppg['target'], $ppg['banner'.$i]);
                    }
				}
			}

			// 하위 상품 출력
			$tres = $pdo->iterator("
				select p.*
					from $tbl[promotion_pgrp_link] a inner join $tbl[product] p on a.pno=p.no
					where a.pgrp_no=$ppg[pgrp_no] and p.stat in (2, 3)
					order by a.sort asc
			");
			$_tmp2 = '';
			$_rollover = $_skin['promotion_over_product_img_fd'];
            foreach ($tres as $prd) {
				$prd = prdOneData(
						$prd,
						$_skin['promotion_product_img_w'],
						$_skin['promotion_product_img_h'],
						$_skin['promotion_product_img_fd'],
						$_skin['promotion_product_namecut']
				);
				$_tmp2 .= lineValues('promotion_product_list', $_lin2, $prd);
			}
			$ppg['product_list'] = listContentSetting($_tmp2, $_lin2);
			unset($_rollover);

			$_tmp .= lineValues('promotion_pgrp_list', $_line, $ppg);
		}
	}
	$_replace_code[$_file_name]['promotion_pgrp_list'] = listContentSetting($_tmp, $_line);

?>