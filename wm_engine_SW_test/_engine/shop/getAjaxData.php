<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Ajax 를 통해 상품 데이터 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/shop2.lib.php';
	include_once $engine_dir.'/_engine/include/cart.class.php';

	printAjaxHeader();

	switch($_REQUEST['exec']) {
		case 'getMultiOption' : // 상품 상세 멀티옵션 출력
			define('__do_not_print_skin__', true);

			if(is_array($_POST['data']) == false || count($_POST['data']) == 0) {
				header('Content-type:application/json; charset='._BASE_CHARSET_);
				exit(json_encode(array(
					'html' => '',
					'pay_prc' => 0
				)));
				return;
			}

			// 옵션 데이터 재구성
			foreach($_POST['data'] as $multi_idx => $val) {
				$multi_idx++;

				$tmp = explode('<split_option>', $val[0]);
				foreach($tmp as $option_no => $option) {
					$_POST['option'.($option_no+1)][$multi_idx] = $option;
				}
			}

			// 가상 장바구니 생성
			function mergeOption($prd, $checkedOption, $buy_ea) {
				$oprc = 0;
				$tmp = explode('<split_big>', $checkedOption['option_prc']);
				foreach($tmp as $key => $val) {
					$tmp2 = explode('<split_small>', $val);
					$oprc += parsePrice($tmp2[0]);
				}
				$prd['sell_prc'] += $oprc;
				$prd['buy_ea'] = $buy_ea;
				return array_merge($prd, $checkedOption);
			}
			$virCart = new OrderCart();
			$virCart->skip_dlv = 'Y';
			foreach($_POST['data'] as $multi_idx => $val) {
				$multi_idx++;
                $pno = addslashes($val[3]);
                $_prd = shortcut($pdo->assoc("select * from {$tbl['product']} where hash='$pno'"));
                $_prd['pno'] = $_prd['parent'];
                if ($_prd['min_ord'] > $val[1]) $val[1] = $_prd['min_ord'];

                $is_anx = explode('::', $val[0]);
                $_complex_no = (int) str_replace('cpx', '', $is_anx[4]);
				if ($_complex_no > 0) {
                    $_prd = $pdo->assoc("select p.*, e.opts from {$tbl['product']} p inner join erp_complex_option e on p.no=e.pno where e.complex_no='$_complex_no'");
                    $_prd = shortcut($_prd);
                    $_prd['pno'] = $_prd['parent'];

                    // 옵션 데이터 생성
                    $_opts = str_replace('_', ',', trim($_prd['opts'], '_'));
                    if ($_opts) {
                        $_ores = $pdo->iterator("select a.no, a.iname, a.add_price from {$tbl['product_option_item']} a inner join {$tbl['product_option_set']} b on a.opno=b.no where a.no in ($_opts) order by b.sort asc");

                        $_oidx = 0;
                        $_otmp = '';
                        foreach ($_ores as $_odata) {
                            $_oidx++;
                            $_POST['option'.$_oidx][$multi_idx] = $_odata['iname'].'::'.$_odata['add_price'].'::0::'.$_odata['no'].'::0';
                            $_otmp .= '<split_option>'.$_POST['option'.$_oidx][$multi_idx];
                        }
                    }
				}
				$tmp = prdCheckStock($_prd, $val[1], $multi_idx);

				$tmp['cno'] = $multi_idx;
				$tmp['prdcpn_no'] = $val[2];

				$virCart->addCart(mergeOption(
					$_prd, $tmp, $val[1]
				));
			}
			$virCart->complete();

			// 멀티옵션 출력
			$_skin = getSkinCfg();
			$spt1 = $_skin['mo_split_big'];
			$spt2 = $_skin['mo_split_small'];
			$_tmp_file_name = 'shop_detail.php';
			include_once $engine_dir."/_engine/common/skin_index.php";
			$line = getModuleContent('detail_multi_option_list');
			$_tmp = $html = '';
			$idx = 0;
			while($obj = $virCart->loopCart()) {
				$cart = $obj->data;
				$opts = array();
				$postdata = $_POST['data'][$idx][0];
                $_min_ord = $cart['min_ord'];
                if (!$_min_ord) $_min_ord = 1;

				// 옵션명
				$_oname = explode('<split_big>', $obj->data['option']);
				foreach($_oname as $val) {
					list($name, $iname) = explode('<split_small>', $val);

					if($opts['option_name']) $opts['option_name'] .= $spt1;
					$opts['option_name'] .= $name.$spt2.$iname; // 옵션명

					if($opts['option_name2']) $opts['option_name2'] .= $spt2;
					$opts['option_name2'] .= $iname; // 옵션명
				}

				// 중복 체크용 해쉬 생성, 및 부속 상품
				$_pno = $cart['hash'];
				$hash = '';
				$_oidx = explode('<split_option>', $postdata);

				// 세트상품 및 부속상품에 본상품명 추가
                if($_POST['pno'] != $cart['hash']) {
                    $opts['option_name'] = preg_replace('/ \(\)$/', '', "{$cart['name']} ({$opts['option_name']})");
                    $opts['option_name2'] = preg_replace('/ \(\)$/', '', "{$cart['name']} ({$opts['option_name2']})");
                }

				foreach($_oidx as $val) {
					$tmp = explode('::', $val);
					$hash .= (count($tmp) == 1) ? '_'.$val : '_'.$tmp[3];

					// 부속 상품
					$complex_no = (int)preg_replace('/^cpx([0-9]+)$/', '$1', $tmp[4]);
					if($complex_no > 0) {
						$_pno2 = $pdo->assoc("select p.hash, p.min_ord from {$tbl['product']} p inner join erp_complex_option e on p.no=e.pno where e.complex_no='$complex_no'");
						$_pno = $_pno2['hash'];
						$_min_ord = $_pno2['min_ord'];
						$opts['option_name'] = $opts['option_name2'] = $tmp[0];
					}
				}
                $hash = rtrim($hash, '_');

				// 장바구니 전송 데이터
				$opts['idx'] = $idx;
				$opts['buy_ea'] = $obj->getData('buy_ea');
				$opts['option']  = "<input type='hidden' name='multi_option_pno[$idx]' value='$_pno' class='multi_option_hash_{$pno}{$hash}' data-idx='$idx' />"; // cart로 전송할 상품코드
				$opts['option'] .= "<input type='hidden' name='multi_option_vals[$idx]' data-idx='$idx' data-buy_ea='{$opts['buy_ea']}' class='multi_option_vals' value=\"".inputText($postdata)."\" />"; // cart 로 전송할 옵션값
				$opts['option'] .= "<input type='hidden' name='multi_option_prdcpn_no[$idx]' value='{$cart['prdcpn_no']}' />"; // cart 로 전송할 쿠폰
				$opts['ea_prc'] = "<span class='multi_option_prc_$idx'>".parsePrice($obj->getData('sum_sell_prc'), true)."</span>"; // 옵션별 개별 실시간 가격
				$opts['r_ea_prc'] = "<span class='multi_option_r_prc_$idx'>".showExchangeFee($obj->getData('sum_sell_prc'), true)."</span>"; // 옵션별 개별 실시간 참조가격
				$opts['ea_plus'] = "multiChgEa($idx, +1, $_min_ord); return false;";
				$opts['ea_minus'] = "multiChgEa($idx, -1, $_min_ord); return false;";
				$opts['ea_remove'] = "multiChgRemove($idx); return false;";
                $_line = ($_POST['prd_type'] == '6' && trim($line[5])) ? $line[5] : $line;
				$_tmp .= lineValues('detail_multi_option_list', $_line, $opts);
				$idx++;
			}
			$html = preg_replace('/\{{2}([^}]+)\}{2}/', '', contentReset($_tmp, $_file_name));

			header('Content-type:application/json; charset='._BASE_CHARSET_);
			exit(json_encode(array(
				'html' => $html,
				'pay_prc' => parsePrice($virCart->getData('pay_prc'))
			)));
		break;
		case 'getAreaOptionPrc' : // 면적옵션 가격
			include_once $engine_dir.'/_engine/include/shop2.lib.php';

			$_no = explode('@', substr($_GET['no'], 1));
			$_val = explode('@', substr($_GET['val'], 1));
			$result = getAreaOptionData($_val, $_no);

			$result['errmsg'] = iconv('euc-kr', 'utf-8', $result['errmsg']);

			exit(json_encode($result));
		break;

		case 'getOptionStock' : // 하위옵션 재고 체크
			$result = '';
			$_item_no = explode('@', trim($_GET['item_no'], '@'));
			$item_no = numberOnly($_item_no[count($_item_no)-1]);

			$add_opts = '';
			foreach($_item_no as $val) {
				$add_opts .= " and opts like '%#_{$val}#_%' ESCAPE '#'";
			}

			$optset = $pdo->assoc("select a.pno, a.no, a.sort from $tbl[product_option_set] a inner join $tbl[product_option_item] b on a.no=b.opno where b.no='$item_no'");
			$prd = $pdo->assoc("select ea_type from $tbl[product] where no='$optset[pno]'");
			if($prd['ea_type'] != 1) exit;

			$nextopt = $pdo->row("select no from $tbl[product_option_set] where pno='$optset[pno]' and sort > '$optset[sort]' order by sort asc limit 1");
			$res = $pdo->iterator("select no from $tbl[product_option_item] where opno='$nextopt'");
            foreach ($res as $data) {
				if($cfg['erp_force_limit'] == 'Y') {
					$stock_sql2 = " and (limit_qty>-1 || qty>limit_qty)";
				}
				$stock = $pdo->assoc("select complex_no, force_soldout, qty, opts from erp_complex_option where pno='$optset[pno]' $add_opts and opts like '%#_$data[no]#_%'  ESCAPE '#' and del_yn='N' and ((force_soldout='N' $stock_sql2) or (force_soldout='L' and qty > 0))");
				if(!$stock['complex_no']) {
					if($result) $result .= '@';
					$result .= $data['no'];
				}
			}
			exit("$result");
		break;

		case 'getAddImgList' :
            if ($cfg['use_opt_addimg'] != 'Y') return;

			$opt_no = numberOnly($_GET['opt_no']);
			$ino = numberOnly($_GET['ino']);
			$hash = addslashes($_GET['hash']);
			$shortcut_cart = true;
			$prd = checkPrd($hash);
			$pno = $prd['no'];

			// 부가이미지 업로드한 옵션만 처리
			if($pdo->row("
				select count(*) from $tbl[product_option_set] a
								inner join $tbl[product_option_item] b on a.no=b.opno
								inner join $tbl[product_image] c on b.no=c.option_item_no
						where a.pno='$pno' and a.sort='$opt_no'
			") == 0) return;

			$filetype = '2, 8'; // 상품 기본 부가 이미지
			if($ino > 0) { // 옵션별 부가 이미지
				if($pdo->row("select count(*) from $tbl[product_image] where pno='$pno' and option_item_no='$ino'") > 0) {
					$w = " and option_item_no='$ino'";
					$filetype = 4;
				}
			}

			// 스킨모듈 세팅
			$_skin = getSkinCfg();
			$_file_name = 'shop_detail.php';
			include_once $engine_dir.'/_manage/skin_module/'.$_file_name;

			// 부가이미지 리스트 세팅
			$_tmp = '';
			$_line = getModuleContent('product_add_image_list');
			if($cfg['up_aimg_sort'] == 'Y' && fieldExist($tbl['product_image'], 'sort')) {
				$orderby = 'order by `sort` asc, `no` desc';
			}
			$_files = array();
			if($filetype != 4) { // 기본 중이미지
				$_replace_code[$_file_name]['detail_img2'] = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile2'].'#addimg';
				$_files[0] = $_replace_code[$_file_name]['detail_img2'];
			}
			$img_res = $pdo->iterator("select updir, filename, sort, width, height from `$tbl[product_image]` where `filetype` in ($filetype) and `pno`='$pno' $w $orderby");
            foreach ($img_res as $img_data) {
				$file_dir = getFileDir($img_data['updir']);
				$img_data['add_img'] = $file_dir.preg_replace('/\/+/', '/', '/'.$img_data['updir']."/".rawurlencode($img_data['filename'])).'#addimg';

				if(!$_files[0]) {
					$_replace_code[$_file_name]['detail_img2'] = $img_data['add_img'];
					$_files[0] = $img_data['add_img']." width=100";
				} else {
					$_files[] = $img_data['add_img']." width=100";
					$_tmp .= lineValues('product_add_image_list', $_line, $img_data);
				}
			}
			if(!$_tmp) $_tmp = '<span></span>';
			$_line[1] = contentReset($_line[1], $_file_name);
			$_tmp = preg_replace('/\{{2}([^}]+)\}{2}/', '', listContentSetting($_tmp, $_line));

			exit(json_encode(array(
				'main_img' => $_files[0], // 기본이미지
				'html' => $_tmp, // 부가이미지 리스트
				'files' => $_files // 부가이미지 파일 목록
			)));
		break;

		case 'getDetailPrice' : // 상품상세에서 선택한 상품+옵션 가격 계산
            $_REQUEST['ajax_return'] = true;

            if ($_GET['ano']) {
                $prd = $pdo->row("select hash from {$tbl['product']} where no=?", array($_GET['ano']));
                $_POST = $_GET;
                $_POST['pno'] = $prd;
            }

            if (is_array($_POST['pno']) == false) {
                $_POST['pno'] = array($_POST['pno']);
            }

            $set_pno = numberOnly($_POST['set_pno']); // 세트 상품 번호

            $prdCart = new OrderCart();
            $prdCart->skip_dlv = 'Y';
            foreach($_POST['pno'] as $key => $val) {
                if (!$val) continue;

                $prd = $pdo->assoc("select * from {$tbl['product']} where hash=?", array(
                    $val
                ));
                $prd = shortCut($prd);

                $prd['cno'] = $key;
                $prd['pno'] = $prd['no'];
                if ($_POST['set_pno'] > 0) {
                    $prd['set_idx'] = 1;
                    $prd['set_pno'] = $_POST['set_pno'];
                }

                if ($prd['prd_type'] != '1' && $prd['prd_type'] != '6') continue;

                $prd['buy_ea'] = ($_POST['buy_ea']) ? $_POST['buy_ea'] : 1;
                $chk = prdCheckStock($prd, $prd['buy_ea'], $key);

                if (isset($_POST['prdcpn_no'])) {
                    $prd['prdcpn_no'] = $_POST['prdcpn_no'];
                }

                $option_price_val = 0;
                foreach(explode('<split_big>', $chk['option_prc']) as $tmp) {
                    list($price, $how_cal) = explode('<split_small>', $tmp);
                    $prd['sell_prc'] += $price;
                }
                $prdCart->addCart($prd);

                if ($prd['prd_type'] == '6') break;
            }
            $prdCart->complete();

			header('Content-type:Application/json; charset='._BASE_CHARSET);
			exit(json_encode(array(
				'pay_prc' => $prdCart->getData('pay_prc'),
				'pay_prc_c' => parsePrice($prdCart->getData('pay_prc'), true),
				'pay_prc_one' => ($prdCart->getData('pay_prc')/$prd['buy_ea']),
				'prd_prc' => $prdCart->getData('sum_prd_prc'),
				'prdcpn_no' => str_replace(',', '@', $prdCart->getData('set_prdcpn_no')),
				'cpn_pay_type' => (int)$cpn_pay_type,
			)));
		break;

		// 재입고 알림 상품의 선택옵션의 하위옵션 품절 유무 체크
		case "getOptionSoldoutCheck":
			// 재입고 알림 미사용시 0리턴
			if($cfg['notify_restock_use'] != "Y") {
				echo json_encode(array("cnt" => 0));
				exit;
			}

			$pno_hash = $_GET['pno_hash'];
			$prd = checkPrd($pno_hash, false);

			// 품절방식 설정값에 따른 품절옵션만 나오게
			if(!$cfg['notify_restock_type_l']) $cfg['notify_restock_type_l'] = "Y";
			if(!$cfg['notify_restock_type_f']) $cfg['notify_restock_type_f'] = "Y";
			$soldout_where = "";
			if($cfg['notify_restock_type_l'] == "Y") {
				$soldout_where .= " (is_soldout='Y' OR (force_soldout='L' AND qty<1)) AND force_soldout!='Y' ";
			}
			if($cfg['notify_restock_type_f'] == "Y") {
				if($soldout_where != "") $soldout_where .= " OR ";
				$soldout_where .= " force_soldout='Y' ";
			}

			$opts = addslashes(trim($_GET['opts']));
			$sql = "SELECT opts FROM erp_complex_option WHERE pno='$prd[parent]' and del_yn='N' AND opts LIKE '%{$opts}%' AND ($soldout_where) ";
			$result = $pdo->iterator($sql);
			$return_array = array();
            foreach ($result as $row) {
				$_opts = $row['opts'];
				$opts_array = explode("_", $_opts);
				$opts_array = array_values(array_filter(array_map('trim', $opts_array)));
				$return_array = array_merge($return_array, $opts_array);
			}
			$return_array = array_unique($return_array);
			sort($return_array);

			echo json_encode($return_array);
			exit;
		break;

	}

?>