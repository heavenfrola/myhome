<?php

/**
 * 카카오 톡스토어 연동 클래스
 */

	namespace Wing\API\Kakao;

	use Wing\Order\OrderCart;

	include_once __ENGINE_DIR__.'/_engine/include/wingPos.lib.php';

	class KakaoTalkStore {
		private $seller_app_key;
        private $pdo;

		public function __construct() {
			$seller_app_key = $GLOBALS['cfg']['kakaoTalkStore_key'];
			if(!$seller_app_key) {
				exit('KakaoTalkStore seller_app_key not found!');
			}
			$this->seller_app_key = $seller_app_key;
            $this->pdo = $GLOBALS['pdo'];
		}

		// seller_app_key 발급 후 최초 한번 admin_key 와 seller_app_key 연결
		public function storeRegister() {
			return $this->api('storeRegister');
		}

		// 상품 정보 수집
		public function getProduct($pno) {
			global $tbl;

			return $this->getProductByProductId(
				$this->pdo->row("select productId from $tbl[product_talkstore] where pno='$pno'")
			);
		}

		public function getProductByProductId($productId) {
			return $this->api('getProduct', $productId);
		}

		// 상품 등록
		public function productRegister($pno) {
			global $tbl, $cfg, $_we;

			$prd = $this->pdo->assoc("select * from $tbl[product] where no='$pno'");
			$kko = $this->pdo->assoc("select * from $tbl[product_talkstore] where pno='$prd[no]'");

			if(!$kko['no']) return false;
			$prd = array_map('stripslashes', $prd);
			$kko = array_map('stripslashes', $kko);

			// 카카오톡 스토어 상품아이디가 존재하는지 재검증
			if($kko['productId']) {
				$ret = $this->getproductByProductId($kko['productId']);
				$ret = json_decode($ret);
				if(!$ret->productId) {
					unset($kko['productId']);
				}
			}

			// 상품 금액 계산
			if($cfg['kakaoTalkStore_msale'] == 'Y') { // 회원할인 반영 여부
				$GLOBALS['member']['level'] = 9;
			} else {
				define('__DISABLE_ORDERCART_MSALE__', true);
			}
			if($cfg['kakaoTalkStore_esale'] != 'Y') { // 이벤트할인 반영 여부
				define('__DISABLE_ORDERCART_EVENT__', true);
			}
			$prdCart = new \OrderCart();
			$prdCart->addCart($prd);
			$prdCart->complete();

			// 즉시할인 (회원할인과 이벤트할인은 합산하여 계산) 타임세일 적용시 동시 적용 불가
			$conf = $prdCart->getData('conf');
			$sale2 = $prdCart->getData('sale2');
			$sale3 = $prdCart->getData('sale3');
			$sale4 = $prdCart->getData('sale4');

			$total_sale_prc = $prdCart->getData('total_sale');
			$useDiscount = 'false';
			if($total_sale_prc > 0) {
				$useDiscount = 'true';
				$discountType = 'RATE';
				$discountValue = 0;
                if ($sale3 > 0) {
                    $discountType = ($prd['ts_saletype'] == 'price') ? 'PRICE' : 'RATE';
                    $discountValue = $prd['ts_saleprc'];
                } else if($sale2 > 0 && $conf['esale']['sale_prc'] > 0) {
					$discountType = 'PRICE';
					$discountValue = $total_sale_prc;
				} else {
					if($sale2 > 0) $discountValue += $conf['esale']['sale_per'];
					if($sale4 > 0) $discountValue += $conf['msale']['sale_per'];
				}
			}

			// 상품정보고시
			if($kko['announcementType']) {
				$ann = $this->pdo->assoc("select type, datas from $tbl[product_talkstore_announce] where idx='$kko[announcementType]'");
			}

			// 배송비
			$ptn = $prdCart->getData('ptns');
			$ptn = current($ptn);
			$ptn_conf = $ptn->getData('conf');
			$is_freedlv = $ptn->getData('is_freedlv'); // 무료배송여부
			$delivery_type = $ptn_conf['delivery_type']; // 배송방식
			$delivery_free_limit = $ptn_conf['delivery_free_limit']; // 무료배송 기준
			$delivery_fee2 = $ptn_conf['dlv_fee2']; // 착불배송비
			if($is_freedlv == 'Y') $deliveryFeeType = 'FREE';
			else if($delivery_type == 3 && $delivery_free_limit > 0) $deliveryFeeType = 'CONDITIONAL_FREE';
			else if($delivery_type == 2 && $delivery_fee2 > 0) $deliveryFeeType = 'PAID';
			$deliveryFeePaymentType = ($delivery_type == 2) ? 'COLLECT' : 'PREPAID';
			$deliverybaseFee = $ptn->getDlvBaseFee();
			$freeConditionalAmount = ($delivery_type == 3) ? $delivery_free_limit : 0;

			// 상품옵션
			$option = $this->makeProductOption($prd);
			if($option['type'] == 'COMBINATION' || $option['type'] == 'COMBINATION_CUSTOM') {
				$stockQuantity = null;
			} else {
				$stockQuantity = ($prd['ea_type'] == 1) ? $this->pdo->row("select sum(if(force_soldout='L',qty,9999)) from erp_complex_option where pno='$prd[no]' and force_soldout!='Y' and del_yn='N'") : 9999;
			}

			// 상품 이미지
			if(!$kko['productId'] || $GLOBALS['_img_changed'][$cfg['kakaoTalkStore_imgno']] == true) {
				$file_url = getFileDir($prd['updir']);
				if(is_array($asvcs) == false) {
					$weca = new \weagleEyeClient($_we, 'account');
					$asvcs = $weca->call('getSvcs',array('key_code'=>$GLOBALS['wec']->config['wm_key_code'], 'use_cdn'=>$cfg['use_cdn']));
				}
				if($asvcs[0]->mall_goods_idx[0] >= 4 && $asvcs[0]->mall_goods_idx[0] <= 6) {
					$file_url = 'http://wimg.mywisa.com/freeimg/'.$asvcs[0]->account_id[0];
					$prd['updir'] = str_replace('_data/product', '', $prd['updir']);
				}
				$img = $file_url.'/'.$prd['updir'].'/'.$prd['upfile'.$cfg['kakaoTalkStore_imgno']];
				$img = $this->uploadProductImage($img);
				$img = json_decode($img);
				$img = $img->url;
				$_try = 0;
				while(1) { // 비동기 이미지 업로드 체크
					if($_try > 10) {
						msg('카카오톡 스토어 이미지 업로드가 실패되었습니다.');
					}
					$_try++;

					$ret = $this->checkProductImage($img);
					$ret = json_decode($ret);

					usleep(2000);
					if($ret->code == 'SUCCESS') break;
				}
			} else {
				$img = $kko['representImage'];
			}

			// 기타 데이터
			if($prd['min_ord'] < 2) $prd['min_ord'] = null;
			if(!$prd['max_ord']) $prd['max_ord'] = 999;
			if(!$kko['productId']) $kko['productId'] = null;
			if($kko['talkstore_prc'] > 0) $prd['sell_prc'] = $kko['talkstore_prc'];

			// 인증 정보
			$cert = array(array(
				'certType' => $kko['certType'],
				'certCode' => $kko['certCode']
			));

			$param = array(
				'productId' => $kko['productId'],
				'categoryId' => $kko['categoryId'],
				'name' => $prd['name'],
				'productDetailDescription' => $prd['content2'],
				'taxType' => $kko['taxType'],
				'salePrice' => parsePrice($prd['sell_prc']),
				'productCondition' => $kko['productCondition'],
				'plusFriendSubscriberExclusive' => 'false',
				'minPurchaseQuantity' => $prd['min_ord'],
				'maxPurchaseQuantity' => $prd['max_ord'],
				'storeManagementCode' => $prd['no'],
				'displayStatus' => (($prd['stat'] == 4 || $kko['useYn'] == 'N' || $kko['displayStatus'] == 'HIDDEN') ? 'HIDDEN' : 'OPEN'),
				'shoppingHowDisplayable' => 'true',
				'productOriginAreaInfo' => array(
					'originAreaContent' => $kko['originAreaContent'],
					'originAreaCode' => $kko['originAreaCode'],
					'originAreaType' => $kko['originAreaType'],
					'registerWithOtherOriginArea' => 'false'
				),
				'productImage' => array(
					'imageRatio' => $cfg['kakaoTalkStore_ratio'],
					'representImage' => array(
						'url' => $img
					),
					/*
					'optionalImages' => array( // 추가 이미지 최대 3개까지 (없어지면 삭제 됨)
						array('url' => $img),
						array('url' => $img),
					),
					*/
				),
				'announcementInfo' => array(
					'announcementType' => $ann['type'],
					'announcement' => json_decode($ann['datas'])
				),
				'discount' => array(
					'type' => $discountType,
					'value' => $discountValue,
					'useDiscount' => $useDiscount,
				),
				'delivery' => array(
					'deliveryMethodType' => $kko['deliveryMethodType'],
					'bundleGroupAvailable' => 'true',
					'deliveryFeeType' => $deliveryFeeType,
					'baseFee' => $deliverybaseFee,
					'freeConditionalAmount' => $freeConditionalAmount,
					'deliveryFeePaymentType' => $deliveryFeePaymentType,
					'returnDeliveryFee' => $deliverybaseFee,
					'exchangeDeliveryFee' => $deliverybaseFee,
					'shippingAddressId' => $kko['shippingAddressId'],
					'returnAddressId' => $kko['returnAddressId'],
					'asPhoneNumber' => $kko['asPhoneNumber'],
					'asGuideWords' => $kko['asGuideWords'],
				),
				'option' => $option,
				'stockQuantity' => $stockQuantity,
				'certs' => $cert
			);

			$this->pdo->query("update $tbl[product_talkstore] set representImage='$img' where no='$kko[no]'");

			if($kko['productId']) {
				return $this->api('productUpdate', $param);
			} else {
				$ret = $this->api('productRegister', $param);

				$json = json_decode($ret);
				$this->pdo->query("update $tbl[product_talkstore] set productId='$json->productId' where no='$kko[no]'");
				return $ret;
			}
		}

		// 상품에 사용할 카테고리 코드 구하기
		public function getSubCategories($categoryId) {
			return $this->api('getSubCategories', $categoryId);
		}

		// 비동기 이미지 업로드 요청
		public function uploadProductImage($url) {
			return $this->api('uploadProductImage', array(
				'url' => $url,
				'ratio' => $GLOBALS['cfg']['kakaoTalkStore_ratio']
			));
		}

		// 비동기 이미지 업로드가 완료되었는지 확인
		private function checkProductImage($url) {
			return $this->api('checkProductImage', $url);
		}

		// productRegister 에서 사용할 옵션 정보 생성
		private function makeProductOption($prd) {
			global $tbl;

			$options = $combinationAttributes = Array();

			$set_cnt = $this->pdo->assoc("select sum(if(otype!='4B', 1, 0)) as S, sum(if(otype='4B', 1, 0)) as T from $tbl[product_option_set] where pno='$prd[no]' and necessary!='P' and otype!='4A'");
			$set_cnt_s = $set_cnt['S'];
			$set_cnt_t = $set_cnt['T'];
			$set_cnt =  $set_cnt_s+$set_cnt_t;

			// option types
			if($set_cnt < 1) $options['type'] = 'NONE';
			/* SIMPLE 은 재고 미반영 되는 옵션으로 사용하지 않음
			if($set_cnt == 1 && $set_cnt_s == 1) $options['type'] = 'SIMPLE';
			if($set_cnt_s == 1 && $set_cnt_t == 1) $options['type'] = 'SIMPLE_CUSTOM';
			*/
			if($set_cnt == 1 && $set_cnt_t == 1) $options['type'] = 'CUSTOM';
			if($set_cnt_s > 0 && $set_cnt_t == 0) $options['type'] = 'COMBINATION';
			if($set_cnt_s > 0 && $set_cnt_t > 0) $options['type'] = 'COMBINATION_CUSTOM';
			if(!$options['type']) return $options;

			// 상품에 사용할 재고데이터 캐싱
			$complex_cache = array();
			if($set_cnt_s > 0 && $prd['ea_type'] == 1) {
				$res = $this->pdo->iterator("select complex_no, opts, force_soldout, if(force_soldout='N', 9999, curr_stock(complex_no)) as stock from erp_complex_option where pno='$prd[no]' and del_yn='N'");
                foreach ($res as $erp) {
					if($erp['force_soldout'] == 'Y') $erp['stock'] = null;
					$complex_cache[$erp['opts']] = array(
						'complex_no' => $erp['complex_no'],
						'stockQuantity' => $erp['stock']
					);
				}
			}

			// 일반 옵션 구성
			if($set_cnt_s > 0) {
				$combinations = array();
				$set_res = $this->pdo->iterator("select no, name from $tbl[product_option_set] where pno='$prd[no]' and necessary in ('Y', 'C') and otype not in ('4A', '4B') order by sort desc");
                foreach ($set_res as $set_data) {
					$set_data['name'] = stripslashes($set_data['name']);

					$_temp = array();
					$item_res = $this->pdo->iterator("select * from $tbl[product_option_item] where pno='$prd[no]' and opno='$set_data[no]' and hidden='N' order by sort asc");
                    foreach ($item_res as $item_data) {
						$item_data['iname'] = stripslashes($item_data['iname']);

						if($set_cnt_s > 0) { // 복합 옵션일 경우
							$item = array(
								'key' => $set_data['name'],
								'value' => $item_data['iname'],
								'no' => $item_data['no']
							);

							if(count($combinations) == 0) {
								$erp = $complex_cache[makeComplexKey($item_data['no'])];
								if(is_null($erp['stockQuantity']) == true) $erp['stockQuantity'] = 0;
								$_temp[] = array(
									'name' => array($item),
									'price' => parsePrice($item_data['add_price']),
									'stockQuantity' => $erp['stockQuantity'],
									'managedCode' => $erp['complex_no'],
									'usable' => 'true'
								);
							} else {
								foreach($combinations as $key => $val) {
									$opts = array($item_data['no']);
									foreach($combinations[$key]['name'] as $val) {
										$opts[] = $val['no'];
									}
									$erp = $complex_cache[makeComplexKey($opts)];
									$idx = count($_temp);
									$_temp[$idx]['name'] = array_merge(array($item), $combinations[$key]['name']);
									$_temp[$idx]['price'] = $combinations[$key]['price'] + $item_data['add_price'];
									$_temp[$idx]['stockQuantity'] = $erp['stockQuantity'];
									$_temp[$idx]['managedCode'] = $erp['complex_no'];
									$_temp[$idx]['usable'] = 'true';
								}
							}
							// 복합 옵션 구성요소
							$combinationAttributes[] = array(
								'name' => $set_data['name'],
								'value' => $item_data['iname']
							);
						} else { // 단일 옵션일 경우
							$options['simples'][] = array(
								'name' => $set_data['name'],
								'value' => $item_data['iname'],
								'usable' => 'true'
							);
						}
					}
					$combinations = $_temp;
				}
			}
			if($set_cnt_s > 0) {
				/*
				foreach($combinations as $key => $val) {
					if($val['stockQuantity'] < 1 || is_null($val['stockQuantity']) == true) { // 품절 및 재고 미입력 옵션 제거
						unset($combinations[$key]);
					}
				}
				*/
				$options['combinations'] = $combinations;
				$options['combinationAttributes'] = $combinationAttributes;
			}

			// 텍스트옵션 구성
			if($set_cnt_t > 0) {
				$options['customs'] = array();
				$set_res = $this->pdo->iterator("select no, name from $tbl[product_option_set] where pno='$prd[no]' and otype='4B' order by sort desc");
                foreach ($set_res as $set_data) {
					$options['customs'][] = array(
						'name' => $set_data['name'],
						'usable' => 'true'
					);
				}
			}

			return $options;
		}

		// 카카오톡 스토어의 원산지 코드 호출
		public function getOriginArea() {
			return $this->api('getOriginArea');
		}

		// 카카오톡 스토어 판매자센터에 등록한 주소지 정보 호출
		public function getAddressed() {
			return $this->api('getAddressed');
		}

		// 주문번호 기준으로 주문서 갱신
		public function getOrder($order_id) {
			$ret = $this->api('getOrder', $order_id);
			$ret = json_decode($ret);

			$this->parseOrder($ret);

			return $ret;
		}

		// 기간 내 변경된 주문번호 호출
		public function getOrders($start_date, $finish_date, $page = 1) {
            global $tbl;

			$param  = 'order_modified_at_start='.$start_date;
			$param .= '&order_modified_at_end='.$finish_date;
			$param .= '&page='.$page;
			$ret = $this->api('getOrders', urlencode($param));
			$ret = json_decode($ret);

			if(is_array($ret->content)) {
				foreach($ret->content as $data) {
					$ord = $this->pdo->assoc("select * from $tbl[order] where ono='$data->orderId'");
					if($ord['talkstore_last'] >= $data->modifiedAt) continue;

					$this->getOrder($data->orderId);
				}
			}

			if($ret->totalPages > $page) {
				$this->getOrders($start_date, $finish_date, $page+1);
			}
			return;
		}

		// 읽어들인 주문데이터를 스마트윙 DB에 반영
		public function parseOrder($ord) {
			global $tbl;

			$ono = $ord->orderBase->paymentId;
			if(!$ono) return false;

			$dlv_prc = array();
			$prd_prc = $discount_prc = 0;

			// 주소정보
			if($ord->orderDeliveryRequest) {
				$addr = $ord->orderDeliveryRequest;
				$r_name = addslashes($addr->receiverName);
				$r_phone = $addr->receiverPhoneNumber;
				$r_cell = $addr->receiverMobileNumber;
				$r_zip = $addr->zipcode;
				$r_addr1 = addslashes($addr->receiverAddress);
				$r_message = addslashes($addr->requirement);
			}

			// 송장정보
			if($ord->orderDelivery) {
				$dlv = $ord->orderDelivery;
				$dlv_code = $dlv->invoiceNumber;
                $dlv_no = $this->getDlvNo($dlv->deliveryCompanyCode);
			}

			// 주문상품
			$val = $ord->orderProduct;
			$talkstore_ono = $ord->id;
			$name = addslashes($val->name);
			$pno = $val->sellerItemNo;
			$stat = $this->getOrdStat($ord->orderBase->status);

			$option = addslashes($val->optionContent);
			$option_prc = ($val->optionPrice/$val->quantity);
			$complex_no = $val->sellerItemOptionCode;
			$buy_ea = $val->quantity;
			$sell_prc = ($val->productPrice/$val->quantity)+($val->optionPrice/$val->quantity);
			$total_prc = ($val->productPrice+$val->optionPrice);
			$pay_prc = $val->settlementBasicPrice;
			$sale4 = $val->sellerDiscountPrice;
			$deliveryAmountOriginId = $val->deliveryAmountOriginId;
			$dlv_prc = $val->deliveryAmount;

			$cnt = $this->pdo->row("select count(*) from $tbl[order_product] where talkstore_ono='$talkstore_ono'");
			$exec = ($cnt == 0) ? 'insert' : 'update';

			if($exec == 'insert') {
				$this->pdo->query("
					insert into $tbl[order_product]
						(ono, pno, name, sell_prc, buy_ea, total_prc, `option`, option_prc, complex_no, stat, talkstore_ono, r_name, r_phone, r_cell, r_zip, r_addr1, r_message, sale4, talkstore_deliveryAmount, talkstore_deliveryId, dlv_code, dlv_no)
						values
						('$ono', '$pno', '$name', '$sell_prc', '$buy_ea', '$total_prc', '$option', '$option_prc', '$complex_no', '$stat', '$talkstore_ono', '$r_name', '$r_phone', '$r_cell', '$r_zip', '$r_addr1', '$r_message', '$sale4', '$dlv_prc', '$deliveryAmountOriginId', '$dlv_code', '$dlv_no')
				");
				$new_opno = $this->pdo->lastInsertId();
                if ($stat <= 10) {
                    orderStock($ono, 0.99, $stat, $new_opno);
                }
			} else {
				$oprd = $this->pdo->assoc("select no, stat from {$tbl['order_product']} where ono='$ono' and talkstore_ono='$talkstore_ono'");
                if ($oprd['stat'] != $stat) {
                    orderStock($ono, $oprd['stat'], $stat, $oprd['no']);
                }

				$this->pdo->query("
					update $tbl[order_product] set
						stat='$stat', r_name='$r_name', r_phone='$r_phone', r_cell='$r_cell', r_zip='$r_zip', r_addr1='$r_addr1', r_message='$r_message', sale4='$sale4', talkstore_deliveryAmount='$dlv_prc', talkstore_deliveryId='$deliveryAmountOriginId',
                        dlv_code='$dlv_code', dlv_no='$dlv_no'
					where ono='$ono' and talkstore_ono='$talkstore_ono'
				");
			}

			// 주문서
			$date1 = strtotime($ord->orderBase->createdAt);
			$date2 = strtotime($ord->orderBase->paidAt);
			$date3 = ($ord->orderDelivery->confirmedAt) ? strtotime($ord->orderDelivery->confirmedAt) : 0;
			$date4 = ($ord->orderDelivery->deliveredAt) ? strtotime($ord->orderDelivery->deliveredAt) : 0;
			$date5 = ($ord->orderBase->decidedAt) ? strtotime($ord->orderBase->decidedAt) : 0;
			$repay_date = ($ord->orderClaimReturn->createdAt) ? strtotime($ord->orderClaimReturn->createdAt) : 0;

			$buyer_cell = $ord->orderer->phoneNumber;
			$sum = $this->pdo->assoc("select sum(total_prc) as total_prc, sum(sale4) as discount_prc from $tbl[order_product] where ono='$ono' and stat<10");
			$prd_prc = $sum['total_prc'];
			$discount_prc = $sum['discount_prc'];
			$dlv_prc= ($prd_prc > 0) ? $this->pdo->row("select sum(talkstore_deliveryAmount) from (select distinct talkstore_deliveryId, talkstore_deliveryAmount from wm_order_product where ono='$ono') t") : 0;
			$pay_prc = $prd_prc-$discount_prc+$dlv_prc;
			$total_prc = $prd_prc+$dlv_prc;
			$talkstore_last = strtotime($ord->orderBase->modifiedAt);

			$ord = $this->pdo->assoc("select no, stat from {$tbl['order']} where ono='$ono'");
			$exec = ($ord == false) ? 'insert' : 'update';
			if($exec == 'insert') {
				$this->pdo->query("
					insert into $tbl[order]
						(ono, mobile, date1, date2, date3, date4, date5, repay_date, total_prc, pay_prc, prd_prc, dlv_prc, talkstore, talkstore_last, addressee_name, addressee_phone, addressee_cell, addressee_zip, addressee_addr1, dlv_memo, dlv_code, dlv_no)
						values
						('$ono', 'Y', '$date1', '$date2', '$date3', '$date4', '$date5', '$repay_date', '$total_prc', '$pay_prc', '$prd_prc', '$dlv_prc', 'Y', '$talkstore_last', '$r_name', '$r_phone', '$r_cell', '$r_zip', '$r_addr1', '$r_message', '$dlv_code', '$dlv_no')
				");
				$this->pdo->query("update $tbl[order] set x_order_id='talkstore' where ono='$ono'");
			} else {
				$this->pdo->query("
					update $tbl[order] set
						prd_prc='$prd_prc', total_prc='$total_prc', dlv_prc='$dlv_prc', pay_prc='$pay_prc',
						stat='$stat', date2='$date2', date3='$date3', date4='$date4', date5='$date5', repay_date='$repay_date', talkstore_last='$talkstore_last',
						addressee_name='$r_name', addressee_phone='$r_phone', addressee_cell='$r_cell', addressee_zip='$r_zip', addressee_addr1='$r_addr1', dlv_memo='$r_message',
                        dlv_code='$dlv_code', dlv_no='$dlv_no'
					where ono='$ono'
				");
			}
			$stat = ordChgPart($ono, false);
			if($stat != $ord['stat']) {
				ordStatLogw($ono, $stat, 'Y');
			}

			return $ord;
		}

		// 스마트윙 주문번호 기준으로 주문상태 갱신(수정 전용)
		public function updateOrderByPaymentId($paymentId) {
			global $tbl;

			$res = $this->pdo->iterator("select talkstore_ono from $tbl[order_product] where ono='$paymentId'");
            foreach ($res as $data) {
				$this->getOrder($data['talkstore_ono']);
			}
		}

		// 주문서를 상품준비중 상태로 변경
		public function setShippingWait($orderId) {
			$ret = $this->api('setShippingWait', json_encode(array('orderIds' => array($orderId))));
			if($ret == 'OK') {
				$this->getOrder($orderId);
			}
			return $ret;
		}

		// 주문서를 배송중 상태로 변경
		public function setShipping($dlv_name, $dlv_code, $orderId) {
			$dlv_no = $this->getDlvCode($dlv_name);
			if(!$dlv_no) {
				msg('카카오톡 스토어에서 지원하지 않는 택배사이거나 택배사 이름이 정확하지 않습니다.');
			}
			return $this->api('setShipping', json_encode(array(
				array(
					'deliveryInvoiceInfo' => array(
						'deliveryCompanyCode' => $dlv_no,
						'invoiceNumber' => $dlv_code
					),
					'orderId' => $orderId,
					'shippingMethod' => 'SHIPPING'
				)
			)));
		}

		// 주문서를 품절 취소
		public function orderCancel($orderId) {
			return $this->api('orderCancel', $orderId);
		}

		// 카카오톡 스토어에서 지원하는 택배사 코드 수집
		public function getDeliveryCompanies() {
			return $this->api('getDeliveryCompanies');
		}

		// 배송지연 처리
		public function setDeliveryDealy($delayCausation, $delayCausationCode, $deliveryExpectedAt, $orderIds) {
			return $this->api('setDeliveryDealy', json_encode(array(
				'delayCausation' => $delayCausation, // 배송지연 안내 사유
				'delayCausationCode' => $delayCausationCode, // 배송지연사유 코드
				'deliveryExpectedAt' => $deliveryExpectedAt, // 배송예정일
				'orderIds' => $orderIds // 주문번호(배열)
			)));
		}

		// 배송지연 사유
		public function getDeliveryCausationCode() {
			return array(
				'OUT_OF_STOCK' => '상품준비중(재고부족)',
				'ORDER_MADE' => '주문제작상품',
				'RESERVED' => '예약발송',
				'ADDITIONAL_COST' => '도서산간비 추가입금',
				'ETC' => '기타',
			);
		}

		// 스마트윙 택배사명과 카카오톡 스토어 택배사코드 매칭
		private function getDlvCode($str) {
			switch(str_replace(' ', '', strtoupper($str))) {
				case 'CJ대한통운' : $code = 'CJGLS'; break;
				case '대한통운' : $code = 'CJGLS'; break;
				case 'CJGLS' : $code = 'CJGLS'; break;
				case '로젠' : $code = 'KGB'; break;
				case '우체국택배' : $code = 'EPOST'; break;
				case '한진택배' : $code = 'HANJIN'; break;
				case '현대택배' : $code = 'HYUNDAI'; break;
				case '롯데택배' : $code = 'HYUNDAI'; break;
				case '대신택배' : $code = 'DAESIN'; break;
				case '일양로지스' : $code = 'ILYANG'; break;
				case '경동택배' : $code = 'KDEXP'; break;
				case '합동택배' : $code = 'HDEXP'; break;
				case '천일택배' : $code = 'CHUNIL'; break;
				case '편의점택배' : $code = 'CVSNET'; break;
				case '건영택배' : $code = 'KUNYOUNG'; break;
				case '호남택배' : $code = 'HONAM'; break;
				case '용마로지스' : $code = 'YONGMA'; break;
				case 'KGB택배' : $code = 'KGBPS'; break;
				case 'HI택배' : $code = 'HILOGIS'; break;
				case 'SLX택배' : $code = 'SLX'; break;
			}
			return $code;
		}

		private function getDlvNo($str) {
            global $tbl, $cfg;

            $dlv_no = 0;

            $asql = ($cfg['use_partner_delivery'] == 'Y') ? " and partner_no='$partner_no'" : '';
			switch($str) {
                case 'CJGLS' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('CJ대한통운', '대한통운', 'CJGLS') $asql");
                    break;
				case 'KGB' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('로젠') $asql");
                    break;
				case 'EPOST' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('우체국', '우체국택배') $asql");
                    break;
				case 'HANJIN' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('한진택배') $asql");
                    break;
				case 'HYUNDAI' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('현대택배', '롯데택배') $asql");
                    break;
				case 'DAESIN' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('대신택배') $asql");
                    break;
				case 'ILYANG' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('일양로지스') $asql");
                    break;
				case 'KDEXP' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('경동택배') $asql");
                    break;
				case 'HDEXP' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('합동택배') $asql");
                    break;
				case 'CHUNIL' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('천일택배') $asql");
                    break;
				case 'CVSNET' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('편의점택배') $asql");
                    break;
				case 'KUNYOUNG' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('건영택배') $asql");
                    break;
				case 'HONAM' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('호남택배') $asql");
                    break;
				case 'YONGMA' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('용마로지스') $asql");
                    break;
				case 'KGBPS' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('KGB택배') $asql");
                    break;
				case 'HILOGIS' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('HI택배') $asql");
                    break;
				case 'SLX' :
                    $dlv_no = $this->pdo->row("select no from {$tbl['delivery_url']} where name in ('SLX택배') $asql");
                    break;
			}
			return $dlv_no;
		}

		// 카카오톡 스토어 주문상태를 스마트윙 주문상태로 매칭
		private function getOrdStat($status) {
			switch($status) {
				case 'Created' : $stat = 11; break; // 주문 생성
				case 'PayRequest' : $stat = 11; break; // 결제 요청
				case 'PayWaiting' : $stat = 11; break; // 결제 대기
				case 'PayChecking' : $stat = 11; break; // 입금 확인 중
				case 'PayFailed' : $stat = 31; break; // 결제 실패
				case 'PayComplete' : $stat = 2; break; // 결제 완료
				case 'ShippingRequest' : $stat = 2; break; // 배송 요청
				case 'PayCancelRequest' : $stat = 15; break; // 결제 취소 예정
				case 'PayCancelComplete' : $stat = 15; break; // 결제 취소 완료
				case 'RefundRequest' : $stat = 14; break; // 환불 요청 대기
				case 'RefundWaiting' : $stat = 15; break; // 환불 예정
				case 'RefundFailed' : $stat = 14; break; // 환불 재요청 대기
				case 'UnpaidCancelComplete' : $stat = 13; break; // 미입금 취소 완료
				case 'ShippingWaiting' : $stat = 3; break; // 배송 준비 중
				case 'ShippingCancelRequest' : $stat = 15; break; // 결제 취소 예정
				case 'ShippingCancelComplete' : $stat = 15; break; // 결제 취소 완료
				case 'ShippingProgress' : $stat = 4; break; // 배송 중
				case 'ShippingComplete' : $stat = 4; break; // 배송 완료
				case 'ShippingRefundRequest' : $stat = 15; break; // 환불 요청 대기
				case 'ShippingRefundWaiting' : $stat = 15; break; // 환불 예정
				case 'ShippingRefundFailed' : $stat = 15; break; // 환불 재요청 대기
				case 'ShippingRefundComplete' : $stat = 15; break; // 환불 완료
				case 'ShippingCancelRequestBuyer' : $stat = 14; break; // 구매자 배송 취소 요청
				case 'ShippingCancelRequestSeller' : $stat = 15; break; // 판매자 배송 취소 요청
				case 'ShippingCancelRejected' : $stat = null; break; // 취소 불가(기발송)
				case 'ShippingCancelRequestRetract' : $stat = null; break; // 취소 요청 철회
				case 'ExchangeRequest' : $stat = 16; break; // 교환 요청
				case 'ExchangeApproved' : $stat = 17; break; // 교환 승인
				case 'ExchangePending' : $stat = 27; break; // 교환 보류
				case 'ExchangeReturning' : $stat = 24; break; // 교환 반송 중
				case 'ExchangeReturnComplete' : $stat = 25; break; // 교환 반송 완료
				case 'ExchangeShippingProgress' : $stat = 26; break; // 교환 재배송 중
				case 'ExchangeShippingComplete' : $stat = 17; break; // 교환 완료
				case 'ExchangeRequestRetract' : $stat = null; break; // 교환 요청 철회
				case 'ExchangeRejected' : $stat = null; break; // 교환 불가
				case 'ReturnRequest' : $stat = 16; break; // 반품 요청
				case 'ReturnApproved' : $stat = 17; break; // 반품 승인
				case 'ReturnPending' : $stat = null; break; // 반품 보류
				case 'ReturnShippingProgress' : $stat = 22; break; // 반품 반송 중
				case 'ReturnShippingComplete' : $stat = 23; break; // 반품 반송 완료
				case 'ReturnCancelRequest' : $stat = 23; break; // 반품 결제 취소 예정
				case 'ReturnCancelComplete' : $stat = 17; break; // 반품 결제 취소 완료
				case 'ReturnRefundRequest' : $stat = 14; break; // 환불 요청 대기
				case 'ReturnRefundWaiting' : $stat = 15; break; // 반품 환불 예정
				case 'ReturnRefundFailed' : $stat = 14; break; // 환불 재요청 대기
				case 'ReturnRefundComplete' : $stat = 15; break; // 반품 환불 완료
				case 'ReturnRequestRetract' : $stat = null; break; // 반품 요청 철회
				case 'ReturnRejected' : $stat = 27; null; // 반품 불가
				case 'BuyDecision' : $stat = 5; break; // 구매 결정
			}
			return $stat;
		}

		// 최근 1페이지의 상품문의 수집
		public function getStoreQna() {
			global $tbl;

			$ret = $this->api('getStoreQna');
			$ret = json_decode($ret);
			foreach($ret->contents as $data) {
				$qnaId = $data->qnaId;
				if($data->productId) {
					$pno = $this->pdo->row("select a.no from $tbl[product] a inner join $tbl[product_talkstore] b on a.no=b.pno where b.productId='$data->productId'");
				}
				$ono = $data->orderId;
				$title = addslashes("[".$this->getQnaType($data->type).'문의] '.$data->productName);
				$content = nl2br($data->content);
				if(is_array($data->imageUrls)) {
					foreach($data->imageUrls as $url) {
						$content .= "\n<p style='margin-top: 10px;'><img src='$url'></p>";
					}
				}
				$content = addslashes($content);
				$answer_ok = ($data->answered == true) ? 'Y' : 'N';
				$answer_date = strtotime($data->answerCreatedAt);
				$answer = addslashes($data->answerContent);
				$secret = ($data->secret == true) ? 'Y' : 'N';
				$pwd = sql_password($data->orderId);
				$reg_date = strtotime($data->createdAt);

				$no = $this->pdo->row("select no from $tbl[qna] where talkstore_qnaId='$qnaId'");
				if($no > 0) {
					$this->pdo->query("update $tbl[qna] set content='$content', answer_ok='$answer_ok', answer_date='$answer_date', answer='$answer' where no='$no'");
				} else {
					$this->pdo->query("
						insert into $tbl[qna]
						(pno, name, title, content, reg_date, secret, pwd, answer_ok, answer_date, answer, talkstore_qnaId)
						values ('$pno', '비회원', '$title', '$content', '$reg_date', '$secret', '$pwd', '$answer_ok', '$answer_date', '$answer', '$qnaId')
					");
				}
			}
		}

		// 상품문의에 답변 작성
		public function setStoreQnaAnswer($qnaId, $answer) {
			$this->api('setStoreQnaAnswer', json_encode(array(
				'qnaId' => $qnaId,
				'answer' => $answer
			)));
		}

		// 상품문의 카테고리 코드 매칭
		private function getQnaType($type) {
			switch($type) {
				case 'PRODUCT' : return '상품'; return;
				case 'DELIVERY' : return '배송'; return;
				case 'RETURN' : return '반품'; return;
				case 'EXCHANGE' : return '교환'; return;
				case 'CANCEL' : return '취소 및 환불'; return;
				case 'ETC' : return '기타'; return;
			}
			return false;
		}

		// 인증 타입 종류 출력
		public function getCertType() {
			return array(
				'NOT_APPLICABLE' => '해당없음(인증대상아님)',
				'DETAIL_REF' => '상품상세설명 참조',
				'KC_1' => '[생활용품] 안전인증',
				'KC_2' => '[생활용품] 안전확인',
				'KC_3' => '[생활용품] 어린이보호포장',
				'KC_4' => '[생활용품] 공급자적합성확인',
				'KC_5' => '[전기용품] 안전인증',
				'KC_6' => '[전기용품] 안전확인',
				'KC_7' => '[전기용품] 공급자적합성확인',
				'KC_8' => '[어린이제품] 안전인증',
				'KC_9' => '[어린이제품] 안전확인',
				'KC_10' => '[어린이제품] 공급자적합성확인',
				'RRA_1' => '방송통신기자재 적합성평가',
				'FOOD_1' => '[친환경농산물인증] 무농약농산물',
				'FOOD_2' => '[친환경농산물인증] 유기축산물',
				'FOOD_3' => '[친환경농산물인증] 유기농산물',
				'FOOD_4' => '[친환경농산물인증] 저농약농산물',
				'FOOD_5' => '[친환경농산물인증] 무항생제축산물',
				'FOOD_6' => '친환경 수산물 품질인증',
				'FOOD_7' => '위해요소 중점관리 (HACCP)',
				'FOOD_8' => '농산물 우수관리인증 (GAP)',
				'FOOD_9' => '가공식품 표준화인증 (KS)',
				'FOOD_10' => '유기가공식품 인증',
				'FOOD_11' => '수산물 품질인증',
				'FOOD_12' => '수산특산물 품질인증',
				'FOOD_13' => '수산전통식품 품질인증',
				'FOOD_14' => '건강기능식품 광고심의',
				'FOOD_15' => '이력추적관리농산물',
				'ECOLIFE_1' => '생활화학/살생물제품',
			);
		}

		// 실제 API 전송
		private function api($api, $param = null) {
			global $tbl;

			if(is_array($param)) $param = json_encode($param);

			$wec = new \weagleEyeClient($GLOBALS['_we'], 'KakaoTalkStore');
			$ret = $wec->call($api,
				array(
					'srv'=>'sandbox',
					'seller_app_key' => $this->seller_app_key,
					'param' => urlencode($param)
				)
			);

			// log
			/*
			$log_api = array(
				'productUpdate', 'productRegister', 'setStoreQnaAnswer',
				'getOrder', 'getOrders',
				'setShippingWait', 'setShipping', 'orderCancel', 'setDeliveryDealy',
			);
			if(in_array($api, $log_api)) {
				$_param = addslashes($param);
				$_ret = addslashes($ret);
				$this->pdo->query("insert into $tbl[talkstore_api_log] (api, param, ret, reg_date) values ('$api', '$_param', '$_ret', now())");
			}
			*/

			return $ret;
		}

		public function setCron($useYn) {
			return $this->api('setCron', $useYn);
		}
	}

?>