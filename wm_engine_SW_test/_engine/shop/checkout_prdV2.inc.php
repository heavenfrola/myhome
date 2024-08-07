<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버페이 2.1 상품 정보 연동
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\Checkout;
	use Wing\common\SimpleXMLExtended;

	include_once $engine_dir.'/_engine/include/cart.class.php';

	$checkout = new Checkout;

	$xml = new SimpleXMLExtended("<?xml version=\"1.0\" encoding=\"utf-8\" ?><products/>");

	// cdn 및 외부 파일서버 이용시 상품 이미지 주소 변경
	if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) {
		$file_url = $cfg['cdn_url'];
	} else {
		$file_url = getFileDir('_data/product');
	}

	$products = $_GET['product'];

	if(is_array($products)) {
		foreach($products as $key => $product) {
			$pno = numberOnly($product['id']);
            $merchantProductId = (int) $product['merchantProductId'];
			if(!$pno) continue;

			// 상품 기본 정보
			$prd = $pdo->assoc("select * from $tbl[product] where no='$pno' and stat > 1");
			$img = getListImgURL($prd['updir'], $prd['upfile3']);
            if (isset($_use['npay_no_https']) == true && $_use['npay_no_https'] == true) {
                $img = str_replace('https://', 'http://', $img);
            }
			$prd['buy_ea'] = 1;
			$prd['free_dlv'] = $prd['free_delivery'];
			$tax_type = ($prd['tex_free'] == 'Y') ? 'TAX_FREE' : 'TAX';
			$tot_stock = ($prd['ea_type'] == 2) ? 9999 : $pdo->row("select sum(if(force_soldout='L',qty,9999)) from erp_complex_option where pno='$pno' and del_yn='N' and force_soldout in ('N','L') and complex_no=$merchantProductId");
			$status = $checkout->getPrdStatus($prd['stat']);
			if($prd['free_delivery'] == 'Y' && $cfg['delivery_type'] == 3 && $cfg['delivery_prd_free'] == 'Y') { // 무료배송일 경우 상품명 맞춤
				$prd['name'] .= " [".__lang_shop_info_freedlv__."]";
			}
			$xmlprd = $xml->addChild('product');
			$xmlprd->addChild('id', $prd['no']);
			$xmlprd->addChild('ecMallProductId', $prd['hash']);
			$xmlprd->name = null;
			$xmlprd->name->addCdata(cutstr(strip_tags(stripslashes($prd['name'])), 100));
			$xmlprd->addChild('basePrice', parsePrice($prd['sell_prc']));
			$xmlprd->addChild('taxType', $tax_type);
			$xmlprd->addChild('infoUrl', $root_url.'/shop/detail.php?pno='.$prd['hash']);
			$xmlprd->imageUrl = null;
			$xmlprd->imageUrl->addCdata($img);
			$xmlprd->addChild('stockQuantity', $tot_stock);
			$xmlprd->addChild('status', $status);

			// 상품 옵션 정보
            $complex_no = 0;
			$txt_options = $opt_ness = array();
			$has_option = ($pdo->row("select count(*) from $tbl[product_option_set] where pno='$pno' and necessary!='P'") > 0) ? 'true' : 'false';
			$xmlprd->addChild('optionSupport', $has_option);
			if($has_option == 'true') {
				// 옵션 리스트 나열
				$osql = '';
				$xmlopt = $xmlprd->addChild('option');
				$optres = $pdo->iterator("select no, name, necessary, otype from $tbl[product_option_set] where pno='$pno' and necessary!='P' order by sort asc");
                foreach ($optres as $odata) {
					$otype = ($odata['otype'] == '4B') ? 'INPUT' : 'SELECT';
					$xmloptitem = $xmlopt->addChild('optionItem');
					$xmloptitem->addChild('type', $otype);
					$xmloptitem->name = null;
					$xmloptitem->name->addCdata(stripslashes($odata['name']));

					if($otype == 'INPUT') {
						$txt_options[$odata['no']] = $odata['name'];
					} else {
						if($odata['necessary'] == 'N') { // 필수옵션이 아닐 경우 미선택 옵션 별도로 생성
							$xmloptval = $xmloptitem->addChild('value');
							$xmloptval->addChild('id', $odata['no'].'_0');
							$xmloptval->addChild('text', '옵션미선택');

							$opt_ness[] = $odata;
						}

						$ovalres = $pdo->iterator("select no, iname from $tbl[product_option_item] where pno='$pno' and opno='$odata[no]' $osql order by sort asc");
                        foreach ($ovalres as $item) {
							$xmloptval = $xmloptitem->addChild('value');
							$xmloptval->addChild('id', $item['no']);
							$xmloptval->text = null;
							$xmloptval->text->addCdata(stripslashes($item['iname']));
						}
					}
				}

				$optionManageCodes = explode(',', $product['optionManageCodes']);
				foreach($optionManageCodes as $_optionManageCodes) {
					list($manageCode, $option_prc) = explode('+', $_optionManageCodes);

					// 복합옵션(combination) 정보 구성
					$xmlcomp = $xmlopt->addChild('combination');
					if($prd['ea_type'] == 1 && count($opt_ness) == 0) { // 윙포스
						$osearch = ($manageCode) ? " and complex_no in ($manageCode)" : "";
						$compres = $pdo->iterator("select complex_no, opts, force_soldout, qty from erp_complex_option where pno='$pno' and del_yn='N' $osearch");
                        foreach ($compres as $comp) {
							$opts = str_replace('_', ',', trim($comp['opts'], '_'));
							$qty = $comp['qty'];
							$soldout = false;
							if($comp['force_soldout'] == 'Y') $soldout = true;
							if($comp['force_soldout'] == 'L' && $qty < 1) $soldout = true;

                            if (!$opts) {
                                $xmlcompitem = $xmlcomp->addChild('options');
                                $xmlcompitem->name = null;
                                $xmlcompitem->name->addCdata(stripslashes('옵션없음'));
                                $xmlcompitem->addChild('id', $comp['complex_no']);
                            } else {
                                $compopts = $pdo->iterator("select i.no, s.name, i.iname, i.add_price from $tbl[product_option_item] i inner join $tbl[product_option_set] s on i.opno=s.no where i.pno='$pno' and i.no in ($opts) and s.necessary!='P' order by s.sort asc");
                                foreach ($compopts as $item) {
                                    $xmlcompitem = $xmlcomp->addChild('options');
                                    $xmlcompitem->name = null;
                                    $xmlcompitem->name->addCdata(stripslashes($item['name']));
                                    $xmlcompitem->addChild('id', $item['no']);
                                }
                            }

							$manage_code = $complex_no = $comp['complex_no'];
							if($option_prc !== '') $manage_code .= '+'.$option_prc;

							$xmlcomp->addChild('manageCode', $manage_code);
							$xmlcomp->addChild('price', $option_prc);
							if($comp['force_soldout'] != 'N') $xmlcomp->addChild('stockQuantity', $qty);
							if($soldout == true) $xmlcomp->addChild('status', 'false');
						}
					} else { // 비 윙포스 일반 옵션
                        if ($prd['ea_type'] == 1) {
                            $complex_no = $product['merchantProductId'];
                        }
						$osearch = '';
						$necessary_n = array(); // 선택 옵션 체크
						if($_REQUEST['optionSearch'] == 'true') { // 특정 옵션만 검색시
							$opts = '';
							$_manageCode = explode(',', $manageCode);
							foreach($_manageCode as $_cd) {
								$tmp = explode('/', $_cd);
								foreach($tmp as $key => $val) {
									$val = explode('|', $val);
									if(strlen($opts) > 0) $opts .= ",";
									if(preg_match('/^([0-9]+)_0$/', $val[1], $_tmp)) { // 비필수옵션 미선택시 예외처리
										$val[1] = 0;
										$necessary_n[] = $_tmp[1];
									}
									$opts .= $val[1];
								}
							}
							if($opts) $osearch = " and no in ($opts)";
						}

						// 구성 가능한 복합 옵션 정보 생성
						$set_name = array();
						$opt_data = array();
						$_temp = array();
						$ores = $pdo->iterator("select no, name, necessary from $tbl[product_option_set] where pno='$pno' and necessary!='P' order by sort asc");
                        foreach ($ores as $oset) {
							$set_name[$oset['no']] = stripslashes($oset['name']);

							$res2 = $pdo->iterator("select no, iname from $tbl[product_option_item] where pno='$pno' and opno='$oset[no]' $osearch order by sort asc");
                            foreach ($res2 as $odata) {
								$iname = stripslashes($odata['iname']);
								if(count($opt_data) == 0) {
									$_temp[$odata['no']] = $iname;
								} else {
									foreach($opt_data as $key => $val) {
										$_temp[$key.','.$odata['no']] = $val.'<ss>'.$iname;
										unset($_temp[$key]);
									}
								}
							}
                            unset($odata);

							if(in_array($oset['no'], $necessary_n)) { // 미선택 옵션이 있을 경우
								if(count($opt_data) == 0) {
									$_temp[$oset['no'].'_0'] = '옵션미선택';
								} else {
									foreach($opt_data as $key => $val) {
										if(!$odata['no']) $odata['no'] = $oset['no'].'_0';
										$_temp[$key.','.$odata['no']] = $val.'<ss>'.'옵션미선택';
										unset($_temp[$key]);
									}
								}
							}

							$opt_data = $_temp;
						}
						if(count($opt_data) > 0) {
							foreach($opt_data as $key => $val) {
								$_manageCode = '';
								$add_price = 0;

								// 선택된 옵션
								$selected_opno = array();
								$_key = preg_replace('/[0-9]+_0/', '0', $key);
								$res = $pdo->iterator("select i.no, i.opno, i.add_price, s.otype, s.necessary from $tbl[product_option_item] i inner join $tbl[product_option_set] s on s.no=i.opno where i.no in ($_key) order by s.sort asc");
                                foreach ($res as $odata) {
									if($_REQUEST['optionSearch'] == 'true' && $odata['necessary'] == 'N') { // 주문 상품 최종 확인시 비필수 옵션 제거
										if(in_array($odata['opno'], $necessary_n) == true) continue;
									}

									$xmlcompitem = $xmlcomp->addChild('options');
									$xmlcompitem->name = null;
									$xmlcompitem->name->addCdata($set_name[$odata['opno']]);
									$xmlcompitem->addChild('id', ($odata['otype'] == '4B') ? 'T'.$odata['opno'] : $odata['no']);

									$add_price += $odata['add_price'];
									if($_manageCode) $_manageCode .= "/";
									$_manageCode .= "$odata[opno]|$odata[no]";
									$selected_opno[] = $odata['opno'];
								}
								// 네이버페이용 미선택 옵션 강제 생성
								$_key = explode(',', $key);
								foreach($_key as $v) {
									if(preg_match('/^(.*)_0$/', $v, $k) == true) {
										$xmlcompitem = $xmlcomp->addChild('options');
										$xmlcompitem->name = null;
										$xmlcompitem->name->addCdata($set_name[$k[1]]);
										$xmlcompitem->addChild('id', $k[1].'_0');

										if($_manageCode) $_manageCode .= "/";
										$_manageCode .= $k[1].'|'.$v;
									}
								}
								if($option_prc) $add_price = $option_prc;
								$xmlcomp->addChild('manageCode', $_optionManageCodes);
								$xmlcomp->addChild('price', $add_price);
							}
						}
					}
				} // foreach optionManageeCode
			} else { // has option
                if ($prd['ea_type'] == '1') {
                    $complex_no = $pdo->row("select complex_no from erp_complex_option where pno='{$prd['no']}' and del_yn='N' and opts=''");
                }
            }

			$ptnOrd = new OrderCart();
			$ptnOrd->addCart($prd);
			$ptnOrd->complete();
			$_cart = $ptnOrd->loopCart();
			$_pcart = $_cart->parent;
			$_pconf = $_pcart->getData('conf');
			$is_freedlv = $_pcart->getData('is_freedlv');

			// 배송비 정책
			$dlv_feeType = 'CHARGE';
			if($is_freedlv == 'Y') $dlv_feeType = 'FREE';
			else if($_pconf['delivery_type'] == 3 && $_pconf['delivery_free_limit'] > 0) $dlv_feeType = 'CONDITIONAL_FREE';

			// 배송비 지불 방법
			$dlv_feePayType = 'PREPAYED';
			if($is_freedlv == 'Y') $dlv_feePayType = 'FREE';
			if($_pcart->getData('is_cod') == 'Y') $dlv_feePayType = 'CASH_ON_DELIVERY';

			$xmldlv = $xmlprd->addChild('shippingPolicy');
			$xmldlv->addChild('groupId', $_pcart->getData('partner_no'));
			$xmlprd->addChild('merchantProductId', $complex_no);
			$xmldlv->addChild('feeType', $dlv_feeType);
			$xmldlv->addChild('feePayType', $dlv_feePayType);
			if($dlv_feePayType == 'CASH_ON_DELIVERY') {
				$xmldlv->addChild('feePrice', parsePrice($_cart->parent->cod_prc));
			} else {
				$xmldlv->addChild('feePrice', parsePrice($_pconf['delivery_fee']));
			}
			if($is_freedlv != 'Y' && $dlv_feeType == 'CONDITIONAL_FREE') {
				$tmp = $xmldlv->addChild('conditionalFree');
				$tmp->addChild('basePrice', parsePrice($_pconf['delivery_free_limit']));
			}

			$tmp = $xmldlv->addChild('surchargeByArea');
			$tmp->addChild('apiSupport', 'true');

			// returnInfo
            if($scfg->comp('use_partner_npayr', 'Y') == true && $prd['partner_no'] > 0) {
                $ptn = sql_assoc("select zipcode, addr1, addr2, corporate_name, cell from $tbl[partner_shop] where no='{$prd['partner_no']}'");
                if($ptn['zipcode'] && $ptn['addr1'] && $ptn['addr2'] && $ptn['cell']) {
                    $xmlreturn = $xmlprd->addChild('returnInfo');
                    $xmlreturn->addChild('zipcode', $ptn['zipcode']);
                    $xmlreturn->addChild('address1', stripslashes($ptn['addr1']));
                    $xmlreturn->addChild('address2', stripslashes($ptn['addr2']));
                    $xmlreturn->addChild('sellername', stripslashes($ptn['corporate_name']));
                    $xmlreturn->addChild('contact1', $ptn['cell']);
                }
            }
		} // products
	}

	// debug
	$fp = @fopen($GLOBALS['root_dir'].'/_data/cache/checkout_prd.txt', 'w');
	if($fp) {
		fwrite($fp, print_r($_SERVER, true));
		fclose($fp);
		$xml->asXML($GLOBALS['root_dir'].'/_data/cache/checkout_prd.xml');
	}

	header('Content-type:application/xml; charset=utf8');
	echo $xml->asXml();

?>