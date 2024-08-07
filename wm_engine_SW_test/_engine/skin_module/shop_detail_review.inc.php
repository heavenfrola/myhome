<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 상세 및 상품별 후기 페이지 공통 후기 데이터
	' +----------------------------------------------------------------------------------------------+*/

    $rev_where = '';
    if ($prd['prd_type'] == '1') {
        $rev_where = " and pno='{$prd['parent']}'";
    } else {
        $setsubs = $pdo->row("select group_concat(refpno) from {$tbl['product_refprd']} where pno='{$prd['parent']}' and `group`=99");
        if ($setsubs) {
            $rev_where = " and pno in ($setsubs)";
        } else {
            $rev_where = " and 0";
        }
    }

	$_line = getModuleContent($_rev_pts_module_name);
	$_tmp_star = array();
	$_tmp_cnt = $_tmp_sum = $rev_cnt = 0;
	$_tmp = '';
	$tmp_res = $pdo->iterator("select rev_pt, count(*) as cnt from {$tbl['review']} where stat>1 $rev_where group by rev_pt");
    foreach ($tmp_res as $tmp_data) {
		$_tmp_star[$tmp_data['rev_pt']] = $tmp_data['cnt'];
		$_tmp_cnt += $tmp_data['cnt'];
        $_tmp_sum += ($tmp_data['rev_pt']*$tmp_data['cnt']);
	}
    $_rev_avg = ($_tmp_cnt == 0) ? 0 : round($_tmp_sum/$_tmp_cnt, 1);
	if(count($_tmp_star) > 0) {
		$_tmp_max = max($_tmp_star);
		$_tmp_min = min($_tmp_star);
	}
	if($_skin['review_pts_revert'] == 'Y') {
		for($i = 5; $i >= 1; $i--) {
			$_tmp .= lineValues($_rev_pts_module_name, $_line, array(
				'rev_pts' => $i,
				'counts' => number_format($_tmp_star[$i]),
				'percent' => round($_tmp_cnt > 0 ? ($_tmp_star[$i]/$_tmp_cnt)*100 : 0, 1),
				'is_best' => ($_tmp_max == $_tmp_star[$i]) ? 'max' : '',
				'is_worst' => ($_tmp_min == $_tmp_star[$i]) ? 'min' : '',
			));
		}
	} else {
		for($i = 1; $i <= 5; $i++) {
			$_tmp .= lineValues($_rev_pts_module_name, $_line, array(
				'rev_pts' => $i,
				'counts' => number_format($_tmp_star[$i]),
				'percent' => round($_tmp_cnt > 0 ? ($_tmp_star[$i]/$_tmp_cnt)*100 : 0, 1),
				'is_best' => ($_tmp_max == $_tmp_star[$i]) ? 'max' : '',
				'is_worst' => ($_tmp_min == $_tmp_star[$i]) ? 'min' : '',
			));
		}
	}
	$_replace_code[$_file_name][$_rev_pts_module_name] = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['detail_total_review'] = $_tmp_cnt;
	$_replace_code[$_file_name]['detail_review_avg'] = str_replace('.0', '', $_rev_avg);
	$_replace_code[$_file_name]['detail_review_per'] = round($_rev_avg/5, 2)*100;
	unset($_line, $_tmp_star, $_tmp, $_tmp_res, $tmp_data);

?>