<?PHP

	// 샵링커로 상품 보내기
	class shopLinkerProduct extends shopLinker {
		private $api_url;
        private $pdo;

		public function __construct() {
			parent::__construct();

            $this->pdo = $GLOBALS['pdo'];
		}

		// 상품 인서트
		public function productExport($no) {
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/xmlInsert.php';

			$return = $this->api(
					$this->api_url,
					'productExport',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=productExport&no='.implode(',', $no))
			);
		}

		// 상품 인서트 XML 생성
		public function productExportXML($no) {
			global $tbl, $cfg;

			$prds = array();
			$res = $this->pdo->iterator("select * from $tbl[product] where wm_sc=0 and no in ($no)");
            foreach ($res as $prd) {
				$prd = shortCut($prd);
				$prd = array_map('stripslashes', $prd);

				$stat = $this->getPrdStat($prd['stat']);
				$updir = getFileDir($prd['updir']).'/'.$prd['updir'];
				if($prd['upfile1']) $upfile1 .= $updir.'/'.$prd['upfile1'];
				if($prd['upfile2']) $upfile2 .= $updir.'/'.$prd['upfile2'];
				if($prd['upfile3']) $upfile3 .= $updir.'/'.$prd['upfile3'];
				$prd['origin_prc'] = parsePrice($prd['origin_prc']);
				$quantity = ($prd['ea_type'] == 2) ? 900 : $this->pdo->row("select sum(qty) from erp_complex_option where pno='$prd[parent]' and del_yn='N'");
				$opt_cnt = $this->pdo->row("select count(*) from $tbl[product_option_set] where pno='$prd[parent]'");
				if($opt_cnt < 1) $option_kind = '000';
				else {
					$option_kind = ($prd['ea_type'] == 1) ? '002' : '001';
				}

				$data = array(
					'customer_id' => $this->customer_cd,
					'partner_product_id' => ($prd['code']) ? $prd['code'] : $prd['parent'],
					'product_name' => $prd['name'],
					'sale_status' => $stat,
					'category_l' => '',
					'category_m' => '',
					'category_s' => '',
					'category_d' => '',
					'ccategory_l' => $prd['big'],
					'ccategory_m' => $prd['mid'],
					'ccategory_s' => $prd['small'],
					'ccategory_d' => $prd['detailsmall'],
					'maker' => $cfg['company_name'],
					'origin' => $this->origin,
					'image_url:num=1' => $upfile1,
					'start_price' => $prd['sell_prc'],
					'market_price' => ($prd['normal_prc'] > 0) ? $prd['normal_prc'] : $prd['sell_prc'],
					'sale_price' => $prd['sell_prc'],
					'supply_price' => $prd['origin_prc'],
					'market_price_p' => ($prd['normal_prc'] > 0) ? $prd['normal_prc'] : $prd['sell_prc'],
					'sale_price_p' => $prd['sell_prc'],
					'supply_price_p' => $prd['origin_prc'],
					'delivery_charge_type' => $this->getDlvType($prd),
					'delivery_charge' => $cfg['dlv_fee2'],
					'tax_yn' => '001',
					'detail_desc' => $prd['content2'],
					'new_desc_top' => $prd['content2'],
					'quantity' => $quantity,
					'keyword' => $prd['keyword'],
					'option_kind' => $option_kind,
					'trans_product_id' => $prd['shoplinker_idx'],
				);

				if($opt_cnt > 0) {
					$data = array_merge($data, $this->getOptionInfo($prd, $option_kind));
				}

				// 부가이미지
				$num = 6;
				$imgs = $this->pdo->iterator("select updir, filename from $tbl[product_image] where pno='$prd[no]' order by sort asc");
                foreach ($imgs as $img) {
					$data['image_url:num='.$num] = getFileDir($img['updir']).'/'.$img['updir'].'/'.$img['filename'];
					$num++;
				}

				$prds[] = $data;
			}

			$this->makeXml('product', $prds, array('product_name', 'image_url', 'detail_desc', 'new_desc_top', 'keyword', 'maker', 'opt_info'));
		}

		// 쇼핑몰별 상품 금액 수정 (최초등록시)
		public function productPriceInsert($no) {
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/mall_price_insert.html';

			$return = $this->api(
					$this->api_url,
					'productPriceInsert',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=productPriceInsert&no='.implode(',', $no))
			);
		}


		public function productPriceInsertXML($no) {
			global $tbl, $cfg;

			$no = addslashes($no);
			$prd = $this->pdo->assoc("select no, code, wm_sc, normal_prc, sell_prc from $tbl[product] where no='$no'");
			$prd = $prd = shortCut($prd);

			$header = array(
				'customer_id' => $this->customer_cd,
				'partner_product_id' => ($prd['code']) ? $prd['code'] : $prd['parent'],
			);

			$prds = array();
			$res = $this->pdo->iterator("select * from $tbl[product_openmarket] where pno='$prd[parent]'");
			foreach ($res as $data) {
				if($data['sell_prc'] == 0) $data['sell_prc'] = $prd['sell_prc'];
				$data['sell_prc'] = parsePrice($data['sell_prc']);

				$prds[] = array(
					'mall_id' => $data['api_code'],
					'street_price' => $prd['normal_price'],
					'sale_price' => $data['sell_prc'],
					'supply_price' => $data['origin_prc'],
				);
			}

			$this->makeXml(array('mall_info', 'mall_price_info'), $prds, array('customer_id', 'partner_product_id', 'mall_product_id'), $header);
		}

		// 쇼핑몰별 상품 금액 수정 (수정시)
		public function setProductPrice($no) {
			global $tbl;

			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/OpenMarket_soldout.html';

			foreach($no as $key => $val) {
				$return = $this->api(
						$this->api_url,
						'setProductPrice',
						'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=setProductPrice&no='.$val)
				);
			}
		}


		public function setProductPriceXML($no) {
			global $tbl, $cfg;

			$no = numberOnly($no);
			$prds = array();
			$res = $this->pdo->iterator("
				select a.*, b.sell_prc, b.api_code, b.mall_product_id, a.sell_prc as sell_prc_prd
				from $tbl[product] a
				inner join $tbl[product_openmarket] b on a.no=b.pno
				where b.no='$no'
			");
			foreach ($res as $prd) {
				$prd = shortCut($prd);

				if($prd['sell_prc'] == 0) $prd['sell_prc'] = $prd['sell_prc_prd'];
				$prd['sell_prc'] = parsePrice($prd['sell_prc']);
				$prd['normal_prc'] = parsePrice($prd['normal_prc']);
				$prd['origin_prc'] = parsePrice($prd['origin_prc']);

				$quantity = ($prd['ea_type'] == 2) ? 900 : $this->pdo->row("select sum(qty) from erp_complex_option where pno='$prd[parent]' and del_yn='N'");
				$opt_cnt = $this->pdo->row("select count(*) from $tbl[product_option_set] where pno='$prd[parent]'");
				if($opt_cnt < 1) $option_kind = '000';
				else {
					$option_kind = ($prd['ea_type'] == 1) ? '002' : '001';
				}

				$temp = array(
					'customer_id' => $this->customer_cd,
					'partner_product_id' => $prd['code'],
					'mall_product_id' => $prd['mall_product_id'],
					'sale_status' => $this->getPrdStat($prd['stat']),
					'market_price' => ($prd['normal_prc'] > 0) ? $prd['normal_prc'] : $prd['sell_prc'],
					'sale_price' => $prd['sell_prc'],
					'supply_price' => $prd['origin_prc'],
					'detail_desc' => $prd['content2'],
					'new_desc_top' => $prd['content2'],
					'quantity' => $quantity,
					'opt_info' => $this->getOptionInfo($prd, $option_kind)
				);
				if($opt_cnt > 0) {
					$temp = array_merge($temp, $this->getOptionInfo($prd, $option_kind));
				}

				$prds[] = $temp;
			}

			$this->makeXml('product', $prds, array('customer_id', 'partner_product_id', 'mall_product_id','detail_desc','new_desc_top' ));
		}

		public function setPriceData($pno, $api_code, $value) {
			global $tbl;

			$pno = numberOnly($pno);
			$api_code = addslashes($api_code);
			$price = numberOnly($price, true);

			if(is_array($value) == false) return;

			foreach($value as $key => $val) {
				$val = addslashes($val);
				$asql1 .= ", `$key`";
				$asql2 .= ", '$val'";
				$asql3 .= ", `$key`='$val'";
			}

			$data = $this->pdo->assoc("select * from $tbl[product_openmarket] where pno='$pno' and api_code='$api_code'");
			if($data['no']) {
				$this->pdo->query("update $tbl[product_openmarket] set api_code='$api_code' $asql3 where no='$data[no]'");
				if($this->pdo->lastRowCount() > 0) {
					return $data['no'];
				}
				if($data['mall_product_id']) return $data['no'];
			} else {
				$this->pdo->query("insert into $tbl[product_openmarket] (pno, api_code $asql1) values ('$pno', '$api_code' $asql2)");
				return $this->pdo->lastInsertId();
			}
			return false;
		}

		// 상품 옵션 xml 생성
		private function getOptionInfo($prd, $option_kind) {
			global $tbl;

			$oidx = 1;
			$data = $ocache = array();
			$ores = $this->pdo->iterator("select no, name, items from $tbl[product_option_set] where pno='$prd[parent]' order by sort asc");
            foreach ($ores as $odata) {
				$ocache[$odata['no']] = stripslashes($odata['name']);
				$data['option_name:num='.$oidx] = $ocache[$odata['no']];
				$data['option_value:num='.$oidx] = preg_replace('/::[0-9]+/', '', str_replace('@', ',', str_replace(',', '，', trim(stripslashes($odata['items']), '@'))));
				$oidx++;
			}

			// 옵션 상세 데이터 (재고/추가금액)
			/*
			if($option_kind == '002') {
				$ores = $this->pdo->iterator("select qty as qty, opts from erp_complex_option where pno='$prd[parent]' and del_yn='N'");
                foreach ($ores as $odata) {
					$_tmp = str_replace('_', ',', trim($odata['opts'], '_'));
					$_opts = $this->pdo->assoc("select group_concat(a.name separator '/') as name, group_concat(b.iname separator '/') as iname, sum(b.add_price) as price from $tbl[product_option_item] b inner join $tbl[product_option_set] a on b.opno=a.no where a.pno='$prd[parent]' and b.no in ($_tmp)");
					if(!$data['opt_info']) {
						$data['opt_info'] = $_opts['name'].'||';
					} else {
						$data['opt_info'] .= ',';
					}
					$data['opt_info'] .= $_opts['iname'].'^^'.$odata['qty'].'<**>'.parsePrice($_opts['price']);
				}
			}
			*/

			$set_name = '';
			$opt_data = array(); // 옵션 데이터
			$prc_data = array(); // 옵션 추가가격 데이터
			$ores = $this->pdo->iterator("select no, pno, name from $tbl[product_option_set] where pno='$prd[parent]' order by sort asc");
            foreach ($ores as $oset) {
				if($set_name) $set_name .= '-';
				$set_name .= stripslashes($oset['name']);

				$_temp = $opt_data;
				$res2 = $this->pdo->iterator("select * from $tbl[product_option_item] where pno='$oset[pno]' and opno='$oset[no]' order by sort asc");
                foreach ($res2 as $odata) {
					$iname = stripslashes($odata['iname']);
					$iname = preg_replace("@(\(|\)|/|^|,)@", '', $iname); // 샵링크 옵션 구분자를 옵션명에서 제거

					$add_price = $odata['add_price'];

					if(count($opt_data) == 0) {
						$_temp[$odata['no']] = $iname;
						$prc_data[$odata['no']] += $add_price;
					} else {
						foreach($opt_data as $key => $val) {
							$_temp[$key.'_'.$odata['no']] = $val.' '.$iname;
							$prc_data[$key.'_'.$odata['no']] = $prc_data[$key]+$add_price;
							unset($_temp[$key]);
						}
					}
				}
				$opt_data = $_temp;
			}

			if(count($opt_data) > 0) {
				foreach($opt_data as $key => $val) {
					if($option_kind == '002') {
						$opts = makeComplexKey($key);
						$complex = $this->pdo->assoc("select complex_no, qty, force_soldout from erp_complex_option where opts='$opts'");
						$qty = $complex['qty'];
						if($complex['force_soldout'] == 'Y') $qty = 0;
						if($complex['force_soldout'] == 'N') $qty = 500;
						$val .= ' _'.$complex['complex_no'].'_';
					} else {
						$qty = 500;
					}
					if(!$prc_data[$key]) $prc_data[$key] = 0;
					if($data['opt_info']) $data['opt_info'] .= ',';
					$data['opt_info'] .= $val.'^^'.$qty.'<**>'.parsePrice($prc_data[$key]);
				}
				$data['opt_info'] = $set_name.'||'.$data['opt_info'];
			}


			return $data;
		}

		// 상품상태값 변환
		public function getPrdStat($stat) {
			switch($stat) {
				case '1' : return '003'; break;
				case '2' : return '001'; break;
				case '3' : return '004'; break;
				case '4' : return '003'; break;
			}
			return '006';
		}

		// 배송상태값 변환
		public function getDlvType($prd) {
			global $cfg;

			$sell_prc = $prd['sell_prc'];
			switch($cfg['delivery_type']) {
				case 1: return '001';
				case 2: return '002';
				case 3:
					if($cfg['delivery_free_limit'] >= 100000) return '007';
					else if($cfg['delivery_free_limit'] >= 70000) return '006';
					else if($cfg['delivery_free_limit'] >= 50000) return '005';
					else if($cfg['delivery_free_limit'] >= 30000) return '004';
				break;
			};

			return '001';
		}

		public function getCategory() {
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/CategoryList.php';

			$return = $this->api(
					$this->api_url,
					'getCategory',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=getCategory')
			);
		}

		public function getCategoryXML() {
			$data = array(
				'customer_id' => $this->customer_cd,
				'cate_type' => '001',
			);

			$shoplinker = new shopLinkerProduct();
			$shoplinker->makeXml('category', array($data));
		}

		public function getMallProduct($no) {
			global $tbl;

			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/mall_product_list.php';

			$return = $this->api(
					$this->api_url,
					'getMallProduct',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=getMallProduct&no='.implode(',', $no))
			);

			$xml = @simplexml_load_string($return);
			if($xml->ResultMessage->result == 'false') {
				return;
			}
			$xml = $xml->ProductInfo->Product;

			foreach($xml as $val) {
				$api_code = $val->mall_id->__toString();
				$partner_product_id = $val->partner_product_id->__toString();
				$mall_product_id = $val->mall_product_id->__toString();

				if(!$pno) {
					$pno = $this->pdo->query("select no from $tbl[product] where code='$partner_product_id'");
				}

				$this->setPriceData($pno, $api_code, array('mall_product_id'=>$mall_product_id));
			}
		}

		public function getMallProductXML($no) {
			global $tbl;

			$prds = array();
			$res = $this->pdo->iterator("select no, code, wm_sc from $tbl[product] where wm_sc=0 and no in ($no)");
            foreach ($res as $prd) {
				$prd = shortCut($prd);
				$prd = array_map('stripslashes', $prd);

				$data = array(
					'customer_id' => $this->customer_cd,
					'st_date ' => '20000101',
					'ed_date' => date('Ymd'),
					'partner_product_id' => ($prd['code']) ? $prd['code'] : $prd['parent'],
					'partner_product_yn_view' => 'Y'
				);

				$prds[] = $data;
			}

			$this->makeXml('product', $prds, array('customer_id', 'partner_product_id', 'partner_product_yn_view'));
		}

		public function setProductInfo($no) {
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Product/goods_info_reg.php';

			$return = $this->api(
					$this->api_url,
					'setProductInfo',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=product&method=setProductInfo&no='.implode(',', $no))
			);
		}

		public function setProductInfoXML($no) {
			global $tbl;

			$prds = array();
			$res = $this->pdo->iterator("
				select a.code as pcode, c.shoplinker_cd, b.value
					from $tbl[product] a
					inner join $tbl[product_field] b on a.no=b.pno
					inner join $tbl[product_field_set] c on b.fno=c.no
				where a.no in ($no)
				");
            foreach ($res as $prd) {
				$pcode = $prd['pcode'];
				$lclass_id = 'i'.substr($prd['shoplinker_cd'],0, 2);
				$data = array(
					'item_seq' => $prd['shoplinker_cd'],
					'item_info' => $prd['value'],
				);

				$prds[] = $data;
			}

			$msg_top = array(
				'customer_id' => $this->customer_cd,
				'partner_product_id' => $pcode,
				'lclass_id' => $lclass_id,
			);

			$this->makeXml(array('goodsinfo', 'item'), $prds, array('item_info'), $msg_top);
		}

	}

?>