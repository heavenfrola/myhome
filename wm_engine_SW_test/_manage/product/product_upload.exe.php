<?PHP

    /**
     * 상품 엑셀 일괄 업로드
     **/

	set_time_limit(0);
	ini_set('memory_limit', -1);

    use Wing\common\WorkLog;

	include_once $engine_dir.'/_engine/include/classes/SpreadsheetExcelReader.class.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$use_file_server = fsConFolder('_data/product');

	$_var_ea_type = array(
		'ERP' => 1,
		'무제한' => 2,
		'' => 2,
	);

	$_var_prd_stat = array(
		'정상판매' => 2,
		'정상' => 2,
		'품절' => 3,
		'숨김' => 4,
		'' => 4
	);

	$_var_otype = array(
		'콤보박스' => '2A',
		'라디오버튼' => '3A',
		'라디오버튼+줄바꿈' => '3B',
		'컬러칩' => '5A',
		'텍스트칩' => '5B',
		'텍스트입력' => '4B',
	);

	function getSellerIdx($name) {
		global $tbl, $_seller_idx_cache, $pdo;

		$name = trim($name);
		if(!$name) return;

		if(!is_array($_seller_idx_cache)) {
			$res = $pdo->iterator("select no, provider from $tbl[provider]");
            foreach ($res as $data) {
				$_seller_idx_cache[stripslashes($data['provider'])] = $data['no'];
			}
		}

		return $_seller_idx_cache[$name];
	}

	function getCateNo($cnames, $ctype) {
		global $tbl, $_cate_colname, $pdo;

		$asql = '';
		$cate = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
		for($level = 1; $level <= 4; $level++) {
			$cate_nm = addslashes($cnames[$level-1]);
			if(!$cate_nm) continue;
			$no_sql = preg_match('/^[0-9]+$/', $cate_nm) ? " or no='$cate_nm'" : '';
			$cno = $pdo->row("select no from $tbl[category] where ctype='$ctype' and level='$level' $asql and (name='$cate_nm' $no_sql)");
			if($cno > 0) {
				$cate[$level] = $cno;
				$colname = $_cate_colname[1][$level];
				$asql .= " and $colname='$cno'";
			}
		}

		return array(
			1 => $cate[1],
			2 => $cate[2],
			3 => $cate[3],
			4 => $cate[4],
		);
	}

    function rebuildCategoryLink($no, $prd)
    {
        global $tbl, $scfg, $_cate_colname, $pdo;

        foreach (array(4, 5) as $ctype) {
            $where = '';
            for($lv = 1; $lv <= $scfg->get('max_cate_depth'); $lv++) {
                $fd = $_cate_colname[$ctype][$lv];
                $ld = $_cate_colname[1][$lv];

                if (empty($prd[$fd]) == false) {
                    $where .= " and n{$ld}='{$prd[$fd]}'";
                }
                ${$ld} = $prd[$fd];
            }

            $exists = $pdo->row("select idx from {$tbl['product_link']} where pno='$no' and ctype='$ctype' $where");
            if (!$exists) {
                $pdo->query("delete from {$tbl['product_link']} where pno='$no' and ctype='$ctype'");
                $pdo->query("
                    insert into {$tbl['product_link']} (ctype, nbig, nmid, nsmall, ndepth4, pno)
                    values ('$ctype', '$big', '$mid', '$small', '$depth4', '$no')
                ");
            }
        }
    }

	if($_FILES['csv']['size'] < 1) msg('정상적인 파일을 업로드 해주세요.');

	if($cfg['product_upload_debug'] == 'Y') {
		if(getExt($_FILES['csv']['name']) != 'xls') {
			msg('파일 확장자명이 xls 가 아닙니다.');
		}

		makeFullDir('_data/productUploadExcel');
		copy($_FILES['csv']['tmp_name'], $root_dir.'/_data/productUploadExcel/'.date('Ymd_his').'.txt');
	}

    $log = new WorkLog();

    $checked_pno = array(); // 네이버 스마트스토어, 카카오톡스토어 연동중인 상품
    $external = array();
    if ($cfg['use_kakaoTalkStore'] == 'Y') $external[] = 'use_talkstore';
    if ($cfg['n_smart_store'] == 'Y') $external[] = 'n_store_check';
    $external = implode(',', $external);

	$excel = new Spreadsheet_Excel_Reader();
	$excel->setUTFEncoder('mb');
	$excel->setOutputEncoding(_BASE_CHARSET_);
	$excel->read($_FILES['csv']['tmp_name']);

	if($excel->sheets[0]['numRows'] < 2) msg('처리 가능한 데이터가 없습니다.');

	for($i = 2; $i <= $excel->sheets[0]['numRows']; $i++) {
		$data = $excel->sheets[0]['cells'][$i];

		if(!trim($data[3])) continue;

        $pdo->query('START TRANSACTION');

		$qry = $qry1 = $qry2 = '';
		$qryset = array();
		$qryset['no'] = $no = numberOnly($data[1]);
		$qryset['code'] = addslashes(trim($data[2]));
		$qryset['name'] = addslashes(trim($data[3]));
		$qryset['name_referer'] = addslashes($data[4]);
		$qryset['keyword'] = addslashes($data[5]);
		$qryset['normal_prc'] = $data[6];
		$qryset['m_normal_prc'] = $data[7];
		$qryset['origin_prc'] = $data[8];
		$qryset['sell_prc'] = $data[9];
		$qryset['m_sell_prc'] = $data[10];
		$qryset['milage'] = parsePrice($data[11]);
		$qryset['ea_type'] = $ea_type = $_var_ea_type[$data[12]];
		$force_soldout = $data[13];
		if(!$force_soldout) $force_soldout = 'N';
		$qryset['weight'] = $data[14];
		$qryset['hs_code'] = $data[15];
		$qryset['seller'] = addslashes($data[16]);
		$qryset['seller_idx'] = getSellerIdx($data[16]);
		$qryset['origin_name'] = addslashes($data[17]);
		$qryset['stat'] = $_var_prd_stat[$data[18]];
		$qryset['dlv_alone'] = ($data[31] == 'Y') ? 'Y' : 'N';
		$qryset['event_sale'] = ($data[32] == 'Y') ? 'Y' : 'N';
		$qryset['member_sale'] = ($data[33] == 'Y') ? 'Y' : 'N';
		$qryset['free_delivery'] = ($data[34] == 'Y') ? 'Y' : 'N';
		$qryset['checkout'] = ($data[35] == 'Y') ? 'Y' : 'N';
		$qryset['min_ord'] = numberOnly($data[36]);
		$qryset['max_ord'] = numberOnly($data[37]);
		$qryset['updir'] = $updir = $data[38];
		$qryset['content3'] = ($data[61] == '기본') ? 'wisamall_default' : $data[61];
		$qryset['content4'] = ($data[62] == '기본') ? 'wisamall_default' : $data[62];
		$qryset['content5'] = ($data[63] == '기본') ? 'wisamall_default' : $data[63];
		if($cfg['use_prd_etc1'] == 'Y') $qryset['etc1'] = addslashes($data[64]);
		if($cfg['use_prd_etc2'] == 'Y') $qryset['etc2'] = addslashes($data[65]);
		if($cfg['use_prd_etc3'] == 'Y') $qryset['etc3'] = addslashes($data[66]);
		if($cfg['use_partner_shop'] == 'Y') {
			$tmp = addslashes(trim($data[78]));
			$qryset['partner_no'] = $pdo->row("select no from $tbl[partner_shop] where corporate_name='$tmp' and stat!=5");
			$qryset['partner_rate'] = numberOnly($data[79], true);
		}

		if($cfg['use_icb_storage'] == 'Y') {
			$qryset['upurl'] = $cfg['current_icb_upurl'];
		}

		if($qryset['free_delivery'] == 'Y' && $cfg['use_prd_dlvprc'] == 'Y') { // 상품 개별 배송
			$qryset['delivery_set'] = 0;
		}

		// 카테고리
		$qryset['big'] = $qryset['mid'] = $qryset['small'] = $qryset['xbig'] = $qryset['xmid'] = $qryset['xsmall'] = $qryset['ybig'] = $qryset['ymid'] = $qryset['ysmall'] = 0;
		if($cfg['max_cate_depth'] > 3) {
			$qryset['xdepth4'] = $qryset['ydepth4'] = $qryset['depth4'] = 0;
		}
		for($x = 19; $x <= 27; $x+=4) {
			if($x == 19) $ctype = 1;
			if($x == 23) $ctype = 4;
			if($x == 27) $ctype = 5;

			$cate = getCateNo(array($data[$x], $data[$x+1], $data[$x+2], $data[$x+3]), $ctype);
			if($cate[1] > 0) $qryset[$_cate_colname[$ctype][1]] = $cate[1];
			if($cate[2] > 0) $qryset[$_cate_colname[$ctype][2]] = $cate[2];
			if($cate[3] > 0) $qryset[$_cate_colname[$ctype][3]] = $cate[3];
			if($cate[4] > 0) $qryset[$_cate_colname[$ctype][4]] = $cate[4];
		}

		// 회원 그룹별 상품 금액
		for($x = 2; $x <= 9; $x++) {
			if($cfg['group_price'.$x] == 'Y') {
				$qryset['sell_prc'.$x] = numberOnly($data[68+$x], true);
			}
		}

		if($data[39]) { // 섬네일 자동 생성
			if(!$updir) {
				if($cfg['use_icb_storage'] == 'Y') {
					$dir['upload'] = $cfg['current_icb_updir'];
				}
				$updir = $dir['upload'].'/'.$dir['product'].'/'.date('Ym/d');
				$qryset['updir'] = $updir;
			}
			makeFullDir($updir);
			$upfile1_name = md5($data[39].'1'.$i.rand(0,99999));
			$upfile2_name = md5($data[39].'2'.$i.rand(0,99999));
			$upfile3_name = md5($data[39].'3'.$i.rand(0,99999));
			$upfile1 = $upfile1_name.'.'.getExt($data[39]);
			$upfile2 = $upfile2_name.'.png';
			$upfile3 = $upfile3_name.'.png';
			$org = '_data/auto_thumb/'.$data[39];

			if(file_exists($root_dir.'/'.$org)) {
				list($w1, $h1) = getImagesize($root_dir.'/'.$org);
				$thumb2 = makeThumb($root_dir.'/'.$org, $root_dir.'/_data/auto_thumb/'.$upfile2, $cfg['thumb2_w_mng'], $cfg['thumb2_h_mng']);
				$thumb3 = makeThumb($root_dir.'/'.$org, $root_dir.'/_data/auto_thumb/'.$upfile3, $cfg['thumb3_w_mng'], $cfg['thumb3_h_mng']);
				$w2 = $thumb2['width'];
				$h2 = $thumb2['height'];
				$w3 = $thumb3['width'];
				$h3 = $thumb3['height'];

				$files1 = array('name' => $upfile1, 'tmp_name' => $root_dir.'/_data/auto_thumb/'.$data[39], 'size' => filesize($root_dir.'/'.$org));
				$files2 = array('name' => $upfile2, 'tmp_name' => $root_dir.'/_data/auto_thumb/'.$upfile2, 'size' => filesize($root_dir.'/_data/auto_thumb/'.$upfile2));
				$files3 = array('name' => $upfile3, 'tmp_name' => $root_dir.'/_data/auto_thumb/'.$upfile3, 'size' => filesize($root_dir.'/_data/auto_thumb/'.$upfile3));

				uploadFile($files1, $upfile1_name, $updir);
				uploadFile($files2, $upfile2_name, $updir);
				uploadFile($files3, $upfile3_name, $updir);

				if($use_file_server) {
					unlink($files1['tmp_name']);
					unlink($files2['tmp_name']);
					unlink($files3['tmp_name']);
				}
				unlink($root_dir.'/'.$org);

				$qryset['upfile1'] = $upfile1;
				$qryset['upfile2'] = $upfile2;
				$qryset['upfile3'] = $upfile3;
				$qryset['w1'] = $w1;
				$qryset['w2'] = $w2;
				$qryset['w3'] = $w3;
				$qryset['h1'] = $h1;
				$qryset['h2'] = $h2;
				$qryset['h3'] = $h3;

			}
		} else {
			if($updir) {
				$qryset['upfile1'] = $data[40];
				$qryset['upfile2'] = $data[43];
				$qryset['upfile3'] = $data[46];
				$qryset['w1'] = $data[41];
				$qryset['w2'] = $data[44];
				$qryset['w3'] = $data[47];
				$qryset['h1'] = $data[42];
				$qryset['h2'] = $data[45];
				$qryset['h3'] = $data[48];
			}
		}
		// 추가이미지
		if($cfg['mng_add_prd_img'] > 0) {
			$add_prd_img = explode('^', $data[67]);
			$add_prd_w = explode('^', $data[68]);
			$add_prd_h = explode('^', $data[69]);
			foreach($add_prd_img as $tmp_no => $tmp) {
				if($tmp_no < $cfg['mng_add_prd_img']) {
					$imgno = $tmp_no+4;
					$qryset['upfile'.$imgno] = $tmp;
					$qryset['w'.$imgno] = $add_prd_w[$tmp_no];
					$qryset['h'.$imgno] = $add_prd_h[$tmp_no];
				}
			}
		}

		$qryset['content2'] = addslashes($data[49]);
		$qryset['content1'] = addslashes($data[50]);
		if($cfg['use_m_content_product'] == 'Y') {
			$qryset['m_content'] = addslashes($data[80]);
		}

		if($cfg['compare_today_start_use'] == 'Y') {
			$qryset['compare_today_start'] = addslashes($data[81]);
		}

		if($cfg['use_prd_dlvprc'] == 'Y') {
			$delivery_set = numberOnly($data[82]);
			$perm_check = $pdo->row("select count(*) from {$tbl['product_delivery_set']} where no='$delivery_set' and partner_no='{$qryset['partner_no']}'");

			if($perm_check) {
				$qryset['delivery_set'] = $delivery_set;
				if($qryset['delivery_set'] > 0) {
					$qryset['free_delivery']='N';
					$qryset['checkout']='N';
				}
			}
		}

		// 상품상세태그 자동 생성
		if(preg_match('/^\(.*\)$/', $data[49])) {
			$qryset['content2'] = '';
			$tmp = explode(',', substr($data[49], 1, strlen($data[49])-2));
			foreach($tmp as $val) {
				$qryset['content2'] .= "<p><img src='$val'></p>\n";
			}
			$qryset['content2'] = addslashes($qryset['content2']);
		}

		// 실 저장
		if($no) {
            // 세트 상품 본체의 가격 정보 수정 불가
            $__prd = $pdo->assoc("select * from {$tbl['product']} where no='$no'");
            if ($__prd['prd_type'] != '1') {
                unset(
                    $qryset['ea_type'],
                    $qryset['normal_prc'],
                    $qryset['m_normal_prc'],
                    $qryset['origin_prc'],
                    $qryset['sell_prc'],
                    $qryset['m_sell_prc'],
                    $qryset['milage'],
                    $qryset['ea_type'],
                    $qryset['weight'],
                    $qryset['hs_code'],
                    $qryset['origin_name'],
                    $qryset['seller_idx'],
                    $qryset['seller_idx'],
                    $qryset['dlv_alone'], $qryset['event_sale'], $qryset['member_sale'], $qryset['free_delivery'], $qryset['checkout'],
            		$qryset['min_ord'], $qryset['max_ord'],
                    $qryset['compare_today_start'],
                    $data[51], $data[52], $data[53], $data[54], $data[55],
                    $data[82]
                );
            }

			foreach($qryset as $key => $val) {
				if($key == 'no') continue;
				$qry .= ",$key='$val'";
			}
			$qry = substr($qry, 1);

			$pdo->query("update $tbl[product] set $qry where no='$no'");
			$pdo->query("update $tbl[product] set stat='$qryset[stat]' where wm_sc='$no' and stat!=5");

            $log->createLog(
                $tbl['product'],
                (int) $no,
                'name',
                $__prd,
                $pdo->assoc("select * from {$tbl['product']} where no=?", array($no))
            );

            if ($external) {
                $external_svc = $pdo->assoc("select $external from {$tbl['product']} where no='$no'");
                if ($external_svc['use_talkstore'] == 'Y' || $external_svc['n_store_check'] == 'Y') {
                    $checked_pno[] = $no;
                }
            }
		} else {
			$qryset['reg_date'] = $qryset['edt_date'] = $now;

			foreach($qryset as $key => $val) {
				if($key == 'no') continue;
				$qry1 .= ",$key";
				$qry2 .= ",'$val'";
			}
			$pdo->query("insert into $tbl[product] (".substr($qry1, 1).") values (".substr($qry2, 1).")");
			$no = $pdo->lastInsertId();
			$hash = strtoupper(md5($no));
			if(!$_sortbig) $_sort_info = $pdo->assoc("select max(`sortbig`) as sortbig, max(`sortmid`) as sortmid, max(`sortsmall`) as sortsmall from `$tbl[product]`");
			$_sortbig = (!$_sortbig && $_sort_info['sortbig']) ? $_sort_info['sortbig']+1 : $_sortbig+1;
			$_sortmid = (!$_sortmid && $_sort_info['sortmid']) ? $_sort_info['sortmid']+1 : $_sortmid+1;
			$_sortsmall = ($_sortsmall && $_sort_info['sortsmall']) ? $_sort_info['sortsmall']+1 : $_sortsmall+1;
			$sort_sql = ", `sortbig`='$_sortbig', `sortmid`='$_sortmid', `sortsmall`='$_sortsmall'";
			$pdo->query("update $tbl[product] set hash='$hash' $sort_sql where no='$no'");
		}

        // 분류 설정
        rebuildCategoryLink($no, $qryset);

		$pnos[] = $no;

		// 옵션
		for($o = 51; $o <= 55; $o++) {
			if(!$data[$o]) continue;
			$opt = explode('::', addslashes($data[$o]));
			$necessary = 'Y';
			$otype = $_var_otype[$opt[1]];
            if ($data[$o] && !$otype) {
                $pdo->query('ROLLBACK');
                msg("옵션이 잘못 입력되어 [$i]번째 라인 이후로 업로드가 중단되었습니다.");
            }
			$opno = $pdo->row("select no from $tbl[product_option_set] where pno='$no' and name='$opt[0]'");
			$sort = $o-50;
			if($opno) { // 수정
				$pdo->query("update $tbl[product_option_set] set otype='$otype', sort='$sort' where no='$opno'");
			} else { // 신규
				$pdo->query("insert into $tbl[product_option_set] (name, necessary, otype, how_cal, pno, stat, sort, reg_date) values ('$opt[0]','$necessary','$otype','1','$no','2', '$sort', '$now')");
				$opno = $pdo->lastInsertId();
			}

			$item_names = $item5A = $item5B = array();
			for($oi = 2; $oi < count($opt); $oi++) {
				list($item_name, $item_prc) = explode('$$', $opt[$oi]);
				$osql1 = $osql2 = '';
				if($otype == '5A') {
					$chip_idx = $pdo->row("select no from $tbl[product_option_colorchip] where name='$item_name'");
					$osql1 = ", chip_idx";
					$osql2 = ", '$chip_idx'";
					$item5A[] = $item_name;
				}
				if($otype == '5B') {
					$item5B[] = $item_name;
				}
				$item_names[] = "'$item_name'";
				$oitem = $pdo->row("select no from $tbl[product_option_item] where pno='$no' and opno='$opno' and iname='$item_name'");
				if(!$oitem) {
					$pdo->query("insert into $tbl[product_option_item] (pno, opno, iname, add_price, sort, reg_date $osql1) values ('$no', '$opno', '$item_name', '$item_prc', '$oi', '$now' $osql2)");
				} else {
					$pdo->query("update $tbl[product_option_item] set add_price='$item_prc' where no='$oitem'");
				}
			}

			if(count($item5A) > 0) {
				$item5A = '@'.implode('@', $item5A).'@';
				$pdo->query("update $tbl[product] set chip1='$item5A' where no='$no'");
			}
			if(count($item5B) > 0) {
				$item5B = '@'.implode('@', $item5B).'@';
				$pdo->query("update $tbl[product] set chip2='$item5B' where no='$no'");
			}

			if(count($item_names)) { // 없어진 옵션 아이템 삭제
				$item_names = implode(',', $item_names);
				$tmp = $pdo->iterator("select no from $tbl[product_option_item] where pno='$no' and opno='$opno' and iname not in ($item_names)");
                foreach ($tmp as $tmpdata) {
					$pdo->query("delete from $tbl[product_option_item] where no='$tmpdata[no]'");
					$pdo->query("update erp_complex_option set del_yn='Y' where pno='$no' and opts like '%#_$tmpdata[no]#_%' ESCAPE '#'");
				}
			}
		}

		// 윙포스 옵션 생성
		if($ea_type == 1) {
			createTmpComplexNo($no, $force_soldout);
		}

		// 부가이미지
		$add_imgs = array();
		for($o = 56; $o <= 60; $o++) {
			if(!$data[$o]) continue;
			$tmp = explode('^', $data[$o]);
			foreach($tmp as $add_img_tmp) {
				$add_imgs[] = $add_img_tmp;
			}
		}
		$filetype = ($cfg['use_cdn'] == 'Y' && $_SESSION['mall_goods_idx'] == 3) ? "8" : "2";
		foreach($add_imgs as $sort => $imgdata) {
			$_updir = dirname($imgdata);
			if($_updir == '.') $_updir = '';
			$_filename = basename($imgdata);
			$sort = $sort+1;

			$imgno = $pdo->assoc("select no, updir, filename from $tbl[product_image] where pno='$no' and filename='$_filename'");
			if($imgno['filename'] != $_filename || $_updir != $imgno['updir']) {
				if($use_file_server) {
					fsFileDown($_updir, $_filename, $root_dir.'/_data/');
					list($w, $h) = getimagesize($root_dir.'/_data/'.$_filename);
					unlink($root_dir.'/_data/'.$_filename);
				} else {
					list($w, $h) = getimagesize($root_dir.'/'.$_updir.'/'.$_filename);
				}
			}

			if($imgno['no']) {
				if($imgno['filename'] != $_filename || $_updir != $imgno['updir']) {
					$pdo->query("update $tbl[product_image] set updir='$_updir', filename='$_filename', width='$w', height='$h' where no='$imgno[no]'");
				}
			} else {
				$pdo->query("
					insert into $tbl[product_image] (pno, filetype, updir, filename, ofilename, stat, width, height, reg_date, sort)
						values ('$no', '$filetype', '$_updir', '$_filename', '$_filename', '2', '$w', '$h', '$now', '$sort')
				");
			}
		}

        $pdo->query('COMMIT');

		$total++;
	}

	if($cfg['opmk_api']) {
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'.class.php';
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'Product.class.php';

		$apiname = $cfg['opmk_api'].'Product';
		$openmarket = new $apiname();
		$openmarket->productExport($pnos);
	}

    // 카카오톡스토어, 네이버스마트스토어 연계상품인 경우 다음 페이지로
    $rURL = $target = null;
    if (count($checked_pno) > 0) {
        $rURL = '?body=product@product_upload_external&pno='.urlencode(base64_encode(gzcompress(implode(',', $checked_pno))));
        $target = 'parent';
    }

	msg($total.' 건의 상품 일괄 등록이 완료되었습니다.', $rURL, $target);

?>