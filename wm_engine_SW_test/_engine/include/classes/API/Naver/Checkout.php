<?php

namespace Wing\API\Naver;

use Wing\common\SimpleXMLExtended;

/*
 *  네이버 페이 연동 클래스
 */
include_once $GLOBALS['engine_dir'].'/_engine/include/cart.class.php';

Class Checkout {

	public $api_url;
	public $shop_id;
	public $certi_key;
	public $prd_price;
	public $total_price;
	public $shipping_price;
	public $shipping_type;
	public $method;
	public $testplug;
    public $is_mobile;
    private $pdo;

	public function __construct() {
		//$this->testplug = 'test-';

        $this->is_mobile = ($GLOBALS['mobile_browser'] == 'mobile' || $_SESSION['browser_type'] == 'mobile') ? 'mobile' : 'pc';
		if ($_GET['is_mobile'] == 'true') {
			$this->is_mobile = 'mobile';
		}
		switch($this->is_mobile) {
			case 'mobile' : $this->page_url = "https://{$this->testplug}m.checkout.naver.com/mobile/customer"; break;
			default : $this->page_url = "https://{$this->testplug}checkout.naver.com/customer"; break;
		}
		$this->api_url = "https://{$this->testplug}checkout.naver.com/customer";

		$this->shop_id = $GLOBALS['cfg']['checkout_id'];
		$this->certi_key = $GLOBALS['cfg']['checkout_key'];
		$this->rurl = $_SERVER['HTTP_REFERER'];

		if(!$this->shop_id || !$this->certi_key) {
			$this->error(__lang_shop_naverpay_notset__);
		}

        $this->pdo = &$GLOBALS['pdo'];
        $this->scfg = &$GLOBALS['scfg'];
	}

	public function order($direct_no = null) {
		global $cfg;

		if($cfg['npay_ver'] == '2') {
			return $this->orderV2($direct_no);
		}

		if($direct_no) $GLOBALS['cart_selected'] = $direct_no;
		$this->method = '';

		// 주문 등록
		$this->setMethod('SHOP_ID', $this->shop_id);
		$this->setMethod('CERTI_KEY', $this->certi_key);

		$ptnOrd = new \OrderCart();
		while($cart = cartList('/', ':', '', '', 0, 0, 3)) {
			$ptnOrd->addCart($cart);
		}
		$ptnOrd->complete();

		$prds = 0;
		while($obj = $ptnOrd->loopCart()) {
			$cart = $obj->data;
			$option_idx = str_replace('<split_big>', '@', str_replace('<split_small>', ':', $cart['option_idx']));

			$tprice = $obj->getData('sum_sell_prc');
			$uprice = ($tprice/$cart['buy_ea']);
			$option = str_replace('<split_small>', ':', $cart['option']);
			$option = str_replace('<split_big>', '/', $option);
			//$option .= ($cart['complex_no']) ? '_optionCode:'.$cart['complex_no'] : '_optionCode:'.$option_idx;

			$option_list = explode('<split_big>', $cart['option']);

			if($cart['sell_prc'] < 1) $this->error(__lang_shop_naverpay_error1__);

			$this->setMethod('ITEM_ID', $cart['pno']);
			$this->setMethod('ITEM_NAME', strip_tags($cart['name']));
			$this->setMethod('ITEM_COUNT', $cart['buy_ea']);
			$this->setMethod('ITEM_UPRICE', $uprice);
			$this->setMethod('ITEM_TPRICE', $tprice);
			$this->setMethod('ITEM_OPTION', $option);
			$this->setMethod('ITEM_OPTION_CODE', $cart['complex_no'] ? $cart['complex_no'] : $cart['option_idx']);
			$this->setMethod('MALL_MANAGE_CODE', $cart['complex_no'] ? $cart['complex_no'] : $cart['option_idx']);
			if($GLOBALS['cfg']['compare_use'] == 'Y') { // 지식쇼핑 mapid 공유
				$this->setMethod('EC_MALL_PID', strtoupper(md5($cart['pno'])));
			}

			$prds++;
		}

		if(!$prds) $this->error(__lang_shop_naverpay_error2__);

		$this->prd_price = $ptnOrd->getData('sum_prd_prc');
		$this->total_price = $ptnOrd->getData('pay_prc');
		$this->shipping_type = $this->getShippingType($ptnOrd->getData('dlv_prc'));

		$this->setMethod('SHIPPING_PRICE', $ptnOrd->getData('dlv_prc'));
		$this->setMethod('SHIPPING_TYPE', $this->shipping_type);
		$this->setMethod('TOTAL_PRICE', $this->total_price);
		$this->setMethod('BACK_URL', $this->rurl);
		if($_SESSION['NVADID']) {
			$this->setMethod('SA_CLICK_ID', $_SESSION['NVADID']);
		}
		if($_COOKIE["CPAValidator"]) {
			$this->setMethod('CPA_INFLOW_CODE', $_COOKIE["CPAValidator"]);
		}
		if($_COOKIE["NA_CO"]) {
			$this->setMethod('NAVER_INFLOW_CODE', $_COOKIE["NA_CO"]);
		}
		if($GLOBALS['cfg']['milage_api_id'] && $GLOBALS['cfg']['milage_api_key'] && $_COOKIE['NA_MI']) {
			$this->setMethod('NMILEAGE_INFLOW_CODE', urldecode(base64_decode($_COOKIE["NA_MI"])));
		}

		// 결제창 연동
		$order_id = comm($this->api_url.'/api/order.nhn', $this->method);
		$order_id = trim($order_id);

		if(preg_match('/^[0-9]+$/', $order_id)) {
			$target = ($cfg['secutiry_url']==2) ? 'parent.parent' : 'parent';
			msg('', $this->page_url.'/order.nhn?ORDER_ID='.$order_id.'&SHOP_ID='.$this->shop_id.'&TOTAL_PRICE='.$this->total_price, $target);
		} else {
			$fp = fopen($GLOBALS[root_dir].'/_data/checkout.txt', 'w');
			fwrite($fp, $order_id);
			fclose($fp);
			$this->error(__lang_shop_naverpay_error3__);
		}

		return $order_id;
	}

	public function orderV2($direct_no = null) {
		global $tbl, $cfg, $root_url, $engine_dir, $total_dlv_alone_rows, $cart_rows, $member;

		if($direct_no) $GLOBALS['cart_selected'] = $direct_no;
		$this->method = '';
		$cart_cnos = array();

		// 주문 등록
		$xml = new SimpleXMLExtended("<?xml version=\"1.0\" encoding=\"utf-8\" ?><order/>");
		$xml->addChild('merchantId', $this->shop_id);
		$xml->addChild('certiKey', $this->certi_key);
		$xml->backUrl = null;
		if($_POST['pno']) {
			$xml->backUrl->addCdata($root_url.'/shop/detail.php?pno='.$_POST['pno']);
		} else {
			$xml->backUrl->addCdata(preg_replace('/&NaPm=[^&]+/', '', $_SERVER['HTTP_REFERER']));
		}

		if($cfg['use_cdn'] == 'Y' && $cfg['cdn_url']) {
			$file_url = $cfg['cdn_url'];
		} else {
			$file_url = getFileDir('_data/product');
		}

        // 가격 미리 계산
        $member['level'] = 10;
        $ptnOrd = new \OrderCart(array(
            'guest' => true
        ));
        while($cart = cartList('/', ':', '', '', 0, 0, (isset($_POST['set_pno']) == true) ? '' : 3)) {
            if ($cart['checkout'] != 'Y') {
                msg('세트 구성 중 네이버페이로 구매할 수 없는 상품이 있습니다. ');
            }
            $ptnOrd->addCart($cart);
        }
        $ptnOrd->complete();
        // 실제 상품 처리
		$prds = $none_set_idx = 0;
        $set_idx = array();
        $merchantCustomCode1 = $merchantCustomCode2 = array();
        while($obj = $ptnOrd->loopCart()) {
            $cart = $obj->data;

			if($cart['sell_prc'] == 0) continue; // 0원 상품은 네이버 페이 구매 불가
            if (isset($cart['prdcpn_no']) == true) $cart['prdcpn_no'] = 0; // 네이버페이에 개별상품쿠폰 사용 불가

			$cart = $obj->data;
			$hash = strtoupper(md5($cart['pno']));
			$cart_cnos[] = $cart['cno'];
			$pconf = $obj->parent->getData('conf');
			if(!$cart['partner_no']) $cart['partner_no'] = 0;

			$img=getListImgURL($cart['updir'], $cart['upfile3']);

            $base_prc = $obj->getData('sum_sell_prc') / (int) $obj->getData('buy_ea');
            $option_prc = 0;

            // 전체 상품 금액 중 옵션의 비율(옵션 추가금액에 할인율 감안)
            settype($cart['option_prc'], 'integer');
            if ($cart['option_prc'] !== 0) {
                $opt_per = $cart['option_prc']/$cart['sell_prc'];
                $option_prc = floor(bcmul($base_prc, $opt_per));
                $base_prc -= $option_prc;
            }

            if (isset($xml->product) == false && $cart['set_idx']) {
                $merchantCustomCode1['set_idx'] = $cart['set_idx'];
                $merchantCustomCode1['set_pno'] = $cart['set_pno'];

            }
            if (isset($cart['set_idx']) == true || empty($cart['set_idx']) == false) $set_idx[] = $cart['set_idx'];
            else $none_set_idx++;

			$xmlprd = $xml->addChild('product');
			$xmlprd->addChild('id', $cart['pno']);
			$xmlprd->addChild('merchantProductId', $cart['complex_no']); // 옵션 없는 재고관리 상품
			$xmlprd->addChild('ecMallProductId', $hash);
			$xmlprd->name = null;
			$xmlprd->name->addCdata(strip_tags(stripslashes($cart['name'])));
			$xmlprd->addChild('basePrice', $base_prc);
			$xmlprd->addChild('infoUrl', $root_url.'/shop/detail.php?pno='.$hash);
			$xmlprd->imageUrl = null;
			$xmlprd->imageUrl->addCdata($img);
            $xmlprd->addChild('taxType', ($cart['tax_free'] == 'Y') ? 'TAX_FREE' : 'TAX');

			// option
			$option = $option_idx = null;
			$manageCode = str_replace('<split_big>', '/', $cart['option_idx']);
			$manageCode = str_replace('<split_small>', '|', $manageCode);
			if($cart['option']) {
				$option = explode('<split_big>', $cart['option']);
				$option_idx = explode('<split_big>', $cart['option_idx']);
			}

			$manageCode_a = explode('/', $manageCode);
			$manageCode = '';
			foreach($manageCode_a as $key => $val) {
				$manageCode_str = strpos($val, '|0');
				if($manageCode_str !== false) {
					$val = '';
				}
				if($val !='') {
					$manageCode .= $val.'/';
				}
			}
			$manageCode =  substr($manageCode, 0, -1);

			// 선택형 옵션 유무 체크후 네이버페이에 선택안함으로 전달
			$selected_os = array();
			if(is_array($option_idx)) {
				foreach($option_idx as $key => $val) {
					list($os, $oi) = explode('<split_small>', $val);
					$selected_os[] = $os;
				}
			}
			$selected_os = implode(',', $selected_os);
			if(!$selected_os) $selected_os = '0';
			$nessN = $this->pdo->iterator("select no, name from $tbl[product_option_set] where pno='$cart[pno]' and necessary='N' and no not in ($selected_os) order by sort asc");
            foreach ($nessN as $oset) {
				if(is_array($option) == false) {
					$option = $option_idx = array();
				}
				$option[] = stripslashes($oset['name']).'<split_small>옵션미선택';
				$option_idx[] = stripslashes($oset['no']).'<split_small>0';

				if($manageCode) $manageCode .= '/';
				$manageCode .= $oset['no'].'|'.$oset['no'].'_0';
			}
			$is_ness_n = $this->pdo->row("select count(*) from {$tbl['product_option_set']} where pno='{$cart['pno']}' and necessary='N'");

			if(count($option) > 0) {
				if($cart['complex_no'] && $is_ness_n == 0) $manageCode = $cart['complex_no'];
				$manageCode .= "+".parsePrice($option_prc);
				if($cart['complex_no'] && $is_ness_n > 0) $manageCode .= '/'.$cart['complex_no'];

				$xmlprdopt = $xmlprd->addChild('option');
				$xmlprdopt->addChild('quantity', $cart['buy_ea']);
				$xmlprdopt->addChild('price', $option_prc);
				$xmlprdopt->addChild('manageCode', $manageCode);

				foreach($option as $key => $val) {
					if(!$val) continue;
					$val1 = explode('<split_small>', $val);
					$val2 = explode('<split_small>', $option_idx[$key]);
                    $item_no = $val2[1];
					if($val2[1] == '0') $val2[1] = $val2[0].'_'.$item_no;

					$otype = 'SELECT';
					$optdata = $this->pdo->assoc("select otype from $tbl[product_option_set] where no='$val2[0]'");
					if($optdata['otype'] == '4B') {
						$otype = 'INPUT';
						$val2[1] = 'T'.$val2[0];
					}

                    if ($item_no > 0) { // 숨김 옵션, 삭제된 옵션 체크
                        $optionitem = $this->pdo->assoc("select no, hidden from {$tbl['product_option_item']} where no=?", array($item_no));
                        if ($optionitem['hidden'] == 'Y') {
                            msg(__lang_shop_error_soldoutOption__."\\n".$cart['name']."\\n".$val1[0]." : ".$val1[1], 'back');
                        }
                        if ($optionitem == false) {
                            msg(__lang_shop_error_unregistOption__, 'back');
                        }
                    }

					$tmp = $xmlprdopt->addChild('selectedItem');
					$tmp->addChild('name', str_replace('&', '&amp;', $val1[0]));
					$tmp->addChild('type', $otype);
					$tmp2 = $tmp->addChild('value');
					$tmp2->addChild('id', $val2[1]);
					$tmp2->text = null;
					$tmp2->text->addCdata(strip_tags($val1[1]));
				}
			} else {
				$xmlsingle = $xmlprdopt = $xmlprd->addChild('single');
				$xmlsingle->addChild('quantity', $cart['buy_ea']);
			}

			// shipping
			$dlv_feeType = 'CHARGE';
			if($obj->parent->getData('is_freedlv') == 'Y') $dlv_feeType = 'FREE';
			else if($pconf['delivery_type'] == 3 && $pconf['delivery_free_limit'] > 0) $dlv_feeType = 'CONDITIONAL_FREE';

			$dlv_feePayType = 'PREPAYED'; // 선불
			if($obj->parent->getData('is_freedlv') == 'Y') $dlv_feePayType = 'FREE'; // 무료
			if($obj->parent->getData('is_cod') == 'Y') $dlv_feePayType = 'CASH_ON_DELIVERY'; // 착불
            $dlv_prc = $obj->parent->getData('dlv_prc');
			if($dlv_feeType == 'FREE') {
				$dlv_prc = 0;
			}

			$xmlprddlv = $xmlprd->addChild('shippingPolicy');
			$xmlprddlv->addChild('groupId', $obj->getData('dlv_partner_no'));
			$xmlprddlv->addChild('feeType', $dlv_feeType);
			$xmlprddlv->addChild('feePayType', $dlv_feePayType);
			if($dlv_feePayType == 'CASH_ON_DELIVERY') {
				$xmlprddlv->addChild('feePrice', parsePrice($obj->parent->getData('cod_prc')));
			} else {
				$xmlprddlv->addChild('feePrice', parsePrice($pconf['delivery_fee']));
			}

            if ($dlv_feeType == 'CONDITIONAL_FREE') {
                if($obj->parent->getData('is_freedlv') != 'Y') {
                    $tmp = $xmlprddlv->addChild('conditionalFree');
                    $tmp->addChild('basePrice', parsePrice($pconf['delivery_free_limit']));
                }
            }
			$tmp = $xmlprddlv->addChild('surchargeByArea');
			$tmp->addChild('apiSupport', 'true');

			// returnInfo
            /*
            if($this->scfg->comp('use_partner_npayr', 'Y') == true && $cart['partner_no'] > 0) {
                $ptn = sql_assoc("select zipcode, addr1, addr2, corporate_name, cell from $tbl[partner_shop] where no='{$cart['partner_no']}'");
                if($ptn['zipcode'] && $ptn['addr1'] && $ptn['addr2'] && $ptn['cell']) {
                    $xmlreturn = $xmlprd->addChild('returnInfo');
                    $xmlreturn->addChild('zipcode', $ptn['zipcode']);
                    $xmlreturn->addChild('address1', stripslashes($ptn['addr1']));
                    $xmlreturn->addChild('address2', stripslashes($ptn['addr2']));
                    $xmlreturn->addChild('sellername', stripslashes($ptn['corporate_name']));
                    $xmlreturn->addChild('contact1', $ptn['cell']);
                }
            }
            */
		}
        $set_idx = count(array_unique($set_idx));
        if ($none_set_idx > 0 && $set_idx > 0) {
            msg('네이버페이에서 세트상품과 일반상품을 동시에 구매할 수 없습니다.', 'back');
        }
        if ($set_idx > 1) {
            msg('세트 구매 시 다른 상품 및 세트는 같이 구매할 수 없습니다.', 'back');
        }

		// interface
		$xmlifc = $xml->addChild('interface');
		$xmlifc->addChild('salesCode', $_SESSION['conversion']);
		$xmlifc->addChild('naverInflowCode', $_COOKIE["NA_CO"]);
		$xmlifc->addChild('saClickId', $_COOKIE['NVADID']);
		$xmlifc->addChild('cpaInflowCode', $_COOKIE["CPAValidator"]);
        if (count($merchantCustomCode1) > 0) {
            $xmlifc->addChild('merchantCustomCode1', json_encode($merchantCustomCode1));
        }
        if (count($merchantCustomCode2) > 0) {
            $xmlifc->addChild('merchantCustomCode2', json_encode($merchantCustomCode2));
        }

		$xml->asXML($GLOBALS['root_dir'].'/_data/cache/checkout.xml'); // debug
		$xml = $xml->asXML();

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset=utf-8'));
		curl_setopt($ci, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ci, CURLOPT_URL, 'https://'.$this->testplug.'api.pay.naver.com/o/customer/api/order/v20/register');
		curl_setopt($ci, CURLOPT_POST, TRUE);
		curl_setopt($ci, CURLOPT_TIMEOUT, 10);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $xml);
		$response = trim(curl_exec($ci));
		curl_close($ci);

		list($resp_cd, $resp_msg, $resp_shopid) = explode(':', $response);

		if($resp_cd == 'SUCCESS') {
			if($cfg['npay_truncate_cart'] != 'N') {
				$this->pdo->query("delete from $tbl[cart] where no in (".implode(',', $cart_cnos).")");
				if($cfg['npay_target'] == 'blank' && $_POST['exec'] == 'checkout') {
					javac("opener.location.reload();");
				}
			}
			javac("top.document.location.href='https://{$this->testplug}order.pay.naver.com/customer/buy/$resp_msg/$resp_shopid'");
		} else {
			alert(php2java($resp_msg));
		}
		exit;
	}

	public function wishlist($pno) {
		global $tbl, $root_url, $p_root_url, $_use;

		$this->method = '';

		// 위시리스트 등록
		$prd = $this->pdo->assoc("select * from `$tbl[product]` where `hash`='$pno' and `stat` > 1");
		if($prd['wm_sc'] > 0) $prd = $this->pdo->assoc("select * from `$tbl[product]` where `no`='$prd[wm_sc]' and `stat` > 1"); // 바로가기 처리

		$file_url = getFileDir($prd['updir']);

		if(!$prd['no']) $this->error(__lang_common_error_noprd__);
		if($prd['sell_prc'] == 0) $this->error(__lang_shop_naverpay_error1__);
		if(!$prd['upfile2']) $prd['upfile2'] = $prd['upfile3'];

		$this->setMethod('SHOP_ID', $this->shop_id);
		$this->setMethod('CERTI_KEY', $this->certi_key);
		$this->setMethod('ITEM_ID', $prd['no']);
		$this->setMethod('ITEM_NAME', strip_tags($prd['name']));
		$this->setMethod('ITEM_DESC', $prd['content1']);
		$this->setMethod('ITEM_UPRICE', parsePrice($prd['sell_prc']));
        $img2=getListImgURL($prd['updir'], $prd['upfile2']);
        $img3=getListImgURL($prd['updir'], $prd['upfile3']);
        if (isset($_use['npay_no_https']) == true && $_use['npay_no_https'] == true) {
            $img2 = str_replace('https://', 'http://', $img2);
            $img3 = str_replace('https://', 'http://', $img3);
        }
		$this->setMethod('ITEM_IMAGE', $img2);
		$this->setMethod('ITEM_THUMB', $img3);
		$this->setMethod('ITEM_URL', "$p_root_url/shop/detail.php?pno=$prd[hash]");
		if($GLOBALS['cfg']['compare_use'] == 'Y') { // 지식쇼핑 mapid 공유
			$this->setMethod('EC_MALL_PID', strtoupper(md5($cart['pno'])));
		}

		$item_id = comm($this->api_url.'/api/wishlist.nhn', $this->method);

		if(preg_match('/^[0-9a-z]+$/i', $item_id)) {
			$this->method = '';
			$this->setMethod('SHOP_ID', $this->shop_id);
			$this->setMethod('ITEM_ID', $item_id);

            if ($_REQUEST['from_ajax'] == 'true') {
                if($this->is_mobile == 'mobile') {
                    $url = $this->page_url.'/wishList.nhn?'.$this->method;
                } else {
                    $url = $this->page_url.'/wishlistPopup.nhn?'.$this->method;
                }
                exit(json_encode(array(
                    'status' => 'on',
                    'type' => 'checkout',
                    'url' => $url
                )));
            } else if($this->is_mobile == 'mobile') {
				$url = $this->page_url.'/wishList.nhn?'.$this->method;
				if($_POST['pagetype'] == 'mobilecheckout') exit($url);
			} else {
				$url = $this->page_url.'/wishlistPopup.nhn?'.$this->method;
				javac("
					var ckWin;
					ckWin = parent.window.open('$url', 'checkoutWish', 'status=no, width=200px, height=200px');
					if(!ckWin) window.alert(\"".__lang_shop_naverpay_error4__."\");
				");
			}
		} else {
			$this->error(__lang_shop_naverpay_error5__);
		}
	}

	private function setMethod($key, $value) {
		if(!$this->method) $this->method = '';

		$this->method .= ($this->method == '') ? '' : '&';
		$this->method .= $key.'='.urlencode(trim(stripslashes($value)));
	}

	private function getShippingType($dlv_prc = 0) {
		$dtype = $GLOBALS['cfg']['delivery_type'];
		if(!$dtype) $dtype = 1;

		switch($dtype) {
			case 1 :
				$type = 'FREE';
				$this->shipping_price = 0;
			break;
			case 2 :
				$type = 'ONDELIVERY';
				$dlv_fee2 = numberOnly($GLOBALS['cfg']['dlv_fee2']);
				if(!$dlv_fee2) msg(__lang_shop_naverpay_error6__);
				$this->shipping_price = $dlv_fee2;
			break;
			case 3 :
				$type = 'PAYED';
				$this->shipping_price = $dlv_prc;
			break;
		}

		if($this->shipping_price == 0) $type = 'FREE';

		return $type;
	}

	public function getPrdStatus($stat) {
		switch($stat) {
			case '2' : $status = 'ON_SALE'; break;
			case '3' : $status = 'SOLD_OUT'; break;
			default  : $status = 'NOT_SALE';
		}
		return $status;
	}

	public function error($msg) {
		msg($msg);
	}

}

?>