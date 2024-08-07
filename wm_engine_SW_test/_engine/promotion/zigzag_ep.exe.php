<?PHP

    ini_set("log_errors", '0');
    ini_set('memory_limit', -1);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/paging.php';

	if($_REQUEST['apiKey'] != $cfg['zigzag_apikey']) {
		exit(json_encode(array(
			'error' => 'wrong apiKey'
		)));
	}

	unset($member); // 비회원 기준 가격 전달

	$w = ' and prd_type=1 ';

	// 상품 번호로 검색
	$product_no = trim(addslashes($_REQUEST['product_no']));
	if($product_no) {
		$product_no = explode(',', $product_no);
		$product_no = implode('\',\'', $product_no);
		$w .= " and hash in ('$product_no')";
	}

	// 수정 일자로 검색
	$edtdate_s = trim($_REQUEST['edtdate_s']);
	$edtdate_e = trim($_REQUEST['edtdate_e']);
	if($edtdate_s && $edtdate_e) {
		$edtdate_s = strtotime($edtdate_s);
		$edtdate_e = strtotime($edtdate_e);
		$w .= " and edt_date > 0 and edt_date between '$edtdate_s' and '$edtdate_e'";
	}

	// 카테고리로 검색
	$level = trim($_REQUEST['level']);
	$categoryId = numberOnly($_REQUEST['categoryId']);
	if($level > 0 && $categoryId > 0) {
		$cname = $_cate_colname[1][$level];
		$w .= " and $cname='$categoryId'";
	}

	// 예외 카테고리
	$exists_cno = $pdo->row("select group_concat(no) from $tbl[category] where private='Y'");
	if($exists_cno) {
		$add_qry .= " and (";
		$add_qry .= " big not in ($exists_cno) and mid not in ($exists_cno) and small not in ($exists_cno)";
		if($cfg['max_cate_depth'] >= 4) {
			$add_qry .= " and depth4 not in ($exists_cno)";
		}
		$add_qry .= ")";

		$w .= $add_qry;
	}

    // 예외 상품
    if ($scfg->comp('compare_explain', 'Y') == true) {
        $w .= " and no_ep!='Y'";
    }

	// 페이징
	$page = numberOnly($_REQUEST['page']);
	if($page < 1) $page=1;
	$page_size = numberOnly($_REQUEST['page_size']);
	if($page_size < 1) $page_size = 100;
	$block = 10;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[product] where stat in (2, 3) and wm_sc=0 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $page_size, $block);
	$PagingResult = $PagingInstance->result($pg_dsn);

    // 상품 정보고시 카테고리 캐시
    $definition = array();
    $res = $pdo->iterator("select no, code from {$tbl['category']} where ctype=3");
    foreach ($res as $data) {
        $definition[$data['no']] = stripslashes($data['code']);
    }

	// 상품 정보 출력
	$product = array();
	$res = $pdo->iterator("select * from {$tbl['product']} where prd_type='1' and stat in (2, 3) and wm_sc=0 $w order by no desc ".$PagingResult['LimitQuery']);
    foreach ($res as $data) {
		// 상품 가격
		$data['free_dlv'] = $data['free_delivery'];
		$prdCart = new OrderCart(array(
			'is_detail' => 'true'
		));
		$prdCart->addCart($data);
		$prdCart->complete();

        $dlv_prc = $prdCart->dlv_prc;
        $dlv_prc -= (int) $prdCart->getData('sale2_dlv');
        $dlv_prc -= (int) $prdCart->getData('sale4_dlv');

		$is_zpay = 'true';

		// 카테고리
		$category_no_list = array();
		if($data['big']) $category_no_list[] = $data['big'];
		if($data['mid']) $category_no_list[] = $data['mid'];
		if($data['small']) $category_no_list[] = $data['small'];
		if($data['depth4']) $category_no_list[] = $data['depth4'];

		$options = $variants = array();
		$check_option = $pdo->row("select count(*) from {$tbl['product_option_set']} where pno='{$data['no']}' and (necessary not in ('Y', 'C') or otype in ('4A', '4B')) order by sort asc");

        if($check_option > 0) continue;

		if($data['ea_type'] == 1) {
			$ores = $pdo->iterator("select no, name, necessary from $tbl[product_option_set] where pno='$data[no]' and necessary in ('Y', 'C') order by sort asc");
            foreach ($ores as $odata) {
				$o = array();
				$ires = $pdo->iterator("select no, iname, add_price from $tbl[product_option_item] where opno='$odata[no]' and hidden!='Y'");
                foreach ($ires as $idata) {
					$o[] = stripslashes($idata['iname']);
				}
				$options[] = array(
					'name' => stripslashes($odata['name']),
					'value' => $o,
				);
			}

			// variants
			if(count($options) > 0) {
				$sale_ing = 0;
				$ores = $pdo->iterator("select complex_no, opts, qty, force_soldout from erp_complex_option where pno='{$data['no']}' and del_yn='N'");
                foreach ($ores as $odata) {
					$o = array();
					$add_price = 0;
					$opts = str_replace('_', ',', trim($odata['opts'], '_'));
					if($opts) {
						$compopts = $pdo->iterator("select i.no, s.name, i.iname, i.add_price from {$tbl['product_option_item']} i inner join {$tbl['product_option_set']} s on i.opno=s.no where i.pno='{$data['no']}' and i.no in ($opts) and s.necessary!='P' order by s.sort asc");
                        foreach ($compopts as $item) {
							$add_price += $item['add_price'];
							$o[] = array(
								'name' => stripslashes($item['name']),
								'value' => stripslashes($item['iname']),
							);
						}
					}
                    $is_sold_out = ($odata['force_soldout'] == 'Y' ||  ($odata['force_soldout'] == 'L' && $odata['qty'] < 1));
					$variants[] = array(
						'code' => $odata['complex_no'],
						'options' => $o,
						'is_displayed' => 'true',
						'is_selling' => 'true',
						'is_sold_out' => $is_sold_out ? 'true' : 'false',
						'additional_amount' => parsePrice($add_price)
					);
					unset($otmp);

                    if($is_sold_out == false) $sale_ing++;
				}
                if($sale_ing == 0) $data['stat'] = 3;
			} else {
                if(isWingposStock ($data['no']) == 0) $data['stat'] = 3;
            }
		} else {
			$is_zpay = false;
		}

		if(!$data['upfile'.$cfg['zigzag_image_no']]) {
			for($i = 1; $i <= 3; $i++) {
				if(empty($data['upfile'.$i]) == false) {
					$data['upfile'.$cfg['zigzag_image_no']] = $data['upfile'.$i];
					break;
				}
			}
		}

        // 상품정보고시
        $essential = null;
        if ($data['fieldset']) {
            $defn = $pdo->iterator("
                select s.name, s.default_value, s.category, f.value
                from {$tbl['product_field_set']} s inner join {$tbl['product_field']} f on s.no=f.fno
                where f.pno='{$data['no']}' and s.category='{$data['fieldset']}'
            ");
            foreach ($defn as $fd) {
                if (count($essential) == 0) {
                    $essential = array();
                    $essential['품목정보'] = $definition[$fd['category']];
                }
                $tmp = trim(stripslashes(($fd['value']) ? $fd['value'] : $fd['default_value']));
                if ($tmp) {
                    $essential[stripslashes($fd['name'])] = $tmp;
                }
            }
        }

        $original_price = $data['sell_prc'];
        if ($data['normal_prc'] > 0 && $data['normal_prc'] > $data['sell_prc']) {
            $original_price = $data['normal_prc'];
        }

		$line = array(
			'product_no' => $data['hash'],
            'product_code' => $data['code'],
			'url' => $root_url.'/shop/detail.php?pno='.$data['hash'].'&ref=wsmk_zigzag',
			'image' => getListImgURL($data['updir'], $data['upfile'.$cfg['zigzag_image_no']]),
            'small_image' => getListImgURL($data['updir'], $data['upfile3']),
            'medium_image' => getListImgURL($data['updir'], $data['upfile2']),
            'big_image' => ($data['upfile1']) ? getListImgURL($data['updir'], $data['upfile1']) : '',
			'title' => stripslashes($data['name']),
			'price' => parsePrice($prdCart->pay_prc-$dlv_prc),
			'original_price' => parsePrice($original_price),
			'shipping_fee' => parsePrice($dlv_prc),
			'is_cod' => 'false',
			'is_deleted' => 'false',
			'is_sold_out' => ($data['stat'] == 2) ? 'false' : 'true',
			'is_selling' => ($data['stat'] == 2) ? 'true' : 'false',
			'is_displayed' => ($data['perm_lst'] == 'Y') ? 'true' : 'false',
			'is_zpay' => $is_zpay,
            'description' => $data['content2'],
			'date_created' => date('Y-m-d H:i:s', $data['reg_date']),
			'date_updated' => date('Y-m-d H:i:s', $data['edt_date']),
			'category_no_list' => $category_no_list,
			'minimum_quantity' => $data['min_ord'],
			'variants' => $variants,
            'essential' => $essential
		);
		if($data['max_ord']) $line['maximum_quantity'] = $data['max_ord'];
		if(count($options) > 0) {
			$line['option_type'] = 'MIXED';
			$line['options'] = $options;
		}

		$product[] = $line;
	}

	$json_options = null;
	if(defined('JSON_PRETTY_PRINT')) {
		$json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
	}

	exit(json_encode(array(
		'total_page' => $PagingInstance->end,
		'page' => $page,
		'page_size' => $page_size,
		'total_rows' => $NumTotalRec,
		'products' => $product
	), $json_options));

?>