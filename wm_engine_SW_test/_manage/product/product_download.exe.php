<?PHP

ini_set('memory_limit', '-1');

	function getCateNameCached($cno) {
		global $tbl, $cname_cache, $pdo;

		if(!is_array($cname_cache)) {
			$res = $pdo->iterator("select no, code, name from $tbl[category]");
            foreach ($res as $data) {
				$cname_cache[$data['no']] = stripslashes($data['name']);
			}
		}

		return $cname_cache[$cno];
	}

	include_once $engine_dir."/_manage/product/product_search.inc.php";
	$prd_csv_fd = array(
		'no' => '시스템ID',
		'code' => '상품코드',
		'name' => '상품명',
		'name_referer' => '참고상품명',
		'keyword' => '키워드',
		'code' => '상품코드',
		'normal_prc' => '소비자가',
		'm_normal_prc' => '소비자가(관리)',
		'origin_prc' => '사입원가',
		'sell_prc' => '판매가',
		'm_sell_prc' => '판매가(관리)',
		'milage' => '적립금',
		'ea_type' => '재고방식',
		'force_soldout' => 'SKU한정여부(L/N)',
		'weight' => '무게',
		'hs_code' => 'HS 코드',
		'seller' => '사입처명',
		'origin_name' => '장기명',
		'stat' => '상품상태',
		'big' => '대분류', 'mid' => '중분류', 'small' => '소분류', 'depth4' => '세분류',
		'xbig' => $cfg['xbig_name'].' 대분류', 'xmid' => $cfg['xbig_name'].' 중분류', 'xsmall' => $cfg['xbig_name'].' 소분류', 'xdepth4' => $cfg['xbig_name'].' 세분류',
		'ybig' => $cfg['ybig_name'].' 대분류', 'ymid' => $cfg['ybig_name'].' 중분류', 'ysmall' => $cfg['ybig_name'].' 소분류', 'ydepth4' => $cfg['ybig_name'].' 세분류',
		'dlv_alone' => '단독배송',
		'event_sale' => '이벤트',
		'member_sale' => '회원혜택',
		'free_delivery' => '무료배송',
		'checkout' => '네이버페이',
		'min_ord' => '최소주문한도',
		'max_ord' => '최대주문한도',
		'updir' => '이미지경로',
		'org_upfile' => '원본이미지',
		'upfile1' => '대이미지', 'w1' => '대이미지 가로사이즈', 'h1' => '대이미지 세로사이즈',
		'upfile2' => '중이미지', 'w2' => '중이미지 가로사이즈', 'h2' => '중이미지 세로사이즈',
		'upfile3' => '소이미지', 'w3' => '소이미지 가로사이즈', 'h3' => '소이미지 세로사이즈',
		'content2' => '상세설명',
		'content1' => '요약설명',
		'opt_info1' => '상품옵션1',
		'opt_info2' => '상품옵션2',
		'opt_info3' => '상품옵션3',
		'opt_info4' => '상품옵션4',
		'opt_info5' => '상품옵션5',
		'add_img1' => '부가이미지1',
		'add_img2' => '부가이미지2',
		'add_img3' => '부가이미지3',
		'add_img4' => '부가이미지4',
		'add_img5' => '부가이미지5',
		'content3' => '공통정보1(기본)',
		'content4' => '공통정보2(기본)',
		'content5' => '공통정보3(기본)',
    );

    $exceptionColType = array(
        'normal_prc' => 'price',
        'm_normal_prc' => 'price',
        'origin_prc' => 'price',
        'sell_prc' => 'price',
        'm_sell_prc' => 'price',
        'milage' => 'price',
    );

	$prd_csv_fd['etc1'] = (!empty($cfg['prd_etc1'])) ? $cfg['prd_etc1'] : '추가항목1';
	$prd_csv_fd['etc2'] = (!empty($cfg['prd_etc2'])) ? $cfg['prd_etc2'] : '추가항목2';
	$prd_csv_fd['etc3'] = (!empty($cfg['prd_etc3'])) ? $cfg['prd_etc3'] : '추가항목3';
	$prd_csv_fd['add_prd_img'] = '추가이미지(^기호로 구분)';
	$prd_csv_fd['add_prd_w'] = '추가이미지 가로(^기호로 구분)';
	$prd_csv_fd['add_prd_h'] = '추가이미지 세로(^기호로 구분)';

	for($i = 2; $i <= 9; $i++) {
		if($cfg['group_price'.$i] == 'Y') {
			$grp = stripslashes($pdo->row("select name from $tbl[member_group] where no='$i'"));
			$prd_csv_fd['sell_prc'.$i] = $grp.'가격';
		} else {
			$prd_csv_fd['x'.$i] = '미사용'.($i-1);
		}
	}
	$prd_csv_fd['partner_name'] = '입점사명';
	$prd_csv_fd['partner_rate'] = '입점수수료';
	$prd_csv_fd['m_content'] = '모바일 상세설명';
	$prd_csv_fd['compare_today_start'] = '오늘출발';
	$prd_csv_fd['delivery_set'] = '개별배송비 코드';

	$_otype = array(
		'2A' => '콤보박스',
		'3A' => '라디오버튼',
		'3B' => '라디오버튼+줄바꿈',
		'5A' => '컬러칩',
		'5B' => '텍스트칩',
		'4B' => '텍스트입력',
	);

	if($cfg['use_partner_shop'] == 'Y') {
		$_partner_names = array();
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop]");
        foreach ($pres as $pdata) {
			$_partner_names[$pdata['no']] = stripslashes($pdata['corporate_name']);
		}
	}

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'name' => 50,
        'name_referer' => 50,
        'keyword' => 50,
        'updir' => 30,
        'org_upfile' => 50,
        'upfile1' => 50,
        'upfile2' => 50,
        'upfile3' => 50,
        'content2' => 50,
        'content1' => 50,
    );

	foreach($prd_csv_fd as $key => $val){
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
	}
    $file_name = '상품양식엑셀';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator("select p.* from $tbl[product] p $prd_join where p.stat in (2,3,4) $w order by no asc");
    foreach ($res as $data) {
		$data['stat'] = $_prd_stat[$data['stat']];
		$data['ea_type'] = $_prd_ea_type[$data['ea_type']];
		$data['big'] = getCateNameCached($data['big']);
		$data['mid'] = getCateNameCached($data['mid']);
		$data['small'] = getCateNameCached($data['small']);
		$data['depth4'] = getCateNameCached($data['depth4']);
		$data['xbig'] = getCateNameCached($data['xbig']);
		$data['xmid'] = getCateNameCached($data['xmid']);
		$data['xsmall'] = getCateNameCached($data['xsmall']);
		$data['xdepth4'] = getCateNameCached($data['xdepth4']);
		$data['ybig'] = getCateNameCached($data['ybig']);
		$data['ymid'] = getCateNameCached($data['ymid']);
		$data['ysmall'] = getCateNameCached($data['ysmall']);
		$data['ydepth4'] = getCateNameCached($data['ydepth4']);
		if($data['content3'] == 'wisamall_default') $data['content3'] = '기본';
		if($data['content4'] == 'wisamall_default') $data['content4'] = '기본';
		if($data['content5'] == 'wisamall_default') $data['content5'] = '기본';

		// 상품 추가이미지
		$data['add_prd_img'] = $data['add_prd_w'] = $data['add_prd_h'] = '';
		if($cfg['mng_add_prd_img'] > 0) {
			for($i = 1; $i <= $cfg['mng_add_prd_img']; $i++) {
				if($data['upfile'.($i+3)]) {
					$data['add_prd_img'] .= "^".$data['upfile'.($i+3)];
					$data['add_prd_w'] .= "^".$data['w'.($i+3)];
					$data['add_prd_h'] .= "^".$data['h'.($i+3)];
				} else {
                    $data['add_prd_img'] .= "^";
                    $data['add_prd_w'] .= "^";
                    $data['add_prd_h'] .= "^";
                }
			}
            $tmpAddPrdImg = str_replace('^', '', $data['add_prd_img']);
            $tmpAddPrdW = str_replace('^', '', $data['add_prd_w']);
            $tmpAddPrdH = str_replace('^', '', $data['add_prd_h']);
            if ($tmpAddPrdImg == '') $data['add_prd_img'] = '';
            if ($tmpAddPrdW == '') $data['add_prd_w'] = '';
            if ($tmpAddPrdH == '') $data['add_prd_h'] = '';
			$data['add_prd_img'] = substr($data['add_prd_img'], 1);
			$data['add_prd_w'] = substr($data['add_prd_w'], 1);
			$data['add_prd_h'] = substr($data['add_prd_h'], 1);
		}

		// 상품옵션
		$okey = 0;
		$ores = $pdo->iterator("select no, name, otype from $tbl[product_option_set] where pno='$data[no]' order by sort asc");
        foreach ($ores as $odata) {
			$okey++;
			$data['opt_info'.$okey]  = stripslashes($odata['name']);
			$data['opt_info'.$okey] .= '::'.$_otype[$odata['otype']];
			$ires = $pdo->iterator("select iname, add_price from $tbl[product_option_item] where pno='$data[no]' and opno='$odata[no]' order by sort asc");
            foreach ($ires as $item) {
				$data['opt_info'.$okey] .= '::'.stripslashes($item['iname']);
				if($item['add_price'] != 0) {
					$data['opt_info'.$okey] .= '$$'.parsePrice($item['add_price']);
				}
			}
		}

		// 부가이미지
		$add_img_cnt = 0;
		if($cfg['up_aimg_sort'] == 'Y') {
			$prd_img_qry = "select updir, filename, sort from $tbl[product_image] where pno='$data[no]' and (filetype=2 or filetype=8) order by sort asc";
		} else {
			$prd_img_qry = "select updir, filename from $tbl[product_image] where pno='$data[no]' and (filetype=2 or filetype=8) order by no asc";
		}
		$fres = $pdo->iterator($prd_img_qry);
        foreach ($fres as $fdata) {
			$add_img_cnt++;
			if($fdata['updir']) $fdata['filename'] = $fdata['updir'].'/'.$fdata['filename'];
			if($add_img_cnt > 5) {
				$data['add_img5'] .= '^'.$fdata['filename'];
			} else {
				$data['add_img'.$add_img_cnt] = $fdata['filename'];
			}
		}

		// 입점사정보
		if($data['partner_no'] > 0) $data['partner_name'] = $_partner_names[$data['partner_no']];

        $row = array();
		foreach($prd_csv_fd as $key => $val) {
            $row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);

	}

    $ExcelWriter->writeFile();
