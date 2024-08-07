<?php

/**
 * 카카오페이 구매 연동 클래스
 */

namespace Wing\API\Kakao;

use Wing\Order\OrderCart;
use Wing\HTTP\CurlConnection;

include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';
include_once __ENGINE_DIR__.'/_engine/include/ftp.lib.php';

class KakaoTalkPay {

    const __AUTH_TYPE__ = 'API_PUBLIC_KAPI';
    const __REST_API_KEY__ = '418067a1912f1bee5831044ab52b69c1';

    const __API_URL__ = 'https://api.pay.kakao.com';
    const __BUTTON_URL__ = 'https://t1.kakaocdn.net/checkout/pay/sdk.js';

    /*
    const __API_URL__ = 'https://testbox-api.pay.kakao.com';
    const __BUTTON_URL__ = 'https://t1.kakaocdn.net/checkout/pay-testtool/sdk.js';
    */

    public function __construct(&$scfg)
    {
        $this->pdo = &$GLOBALS['pdo'];
        $this->use_yn = $scfg->get('use_talkpay');
        $this->button_auth_key = $scfg->get('talkpay_authkey');
        $this->shopKey = $scfg->get('talkpay_ShopKey');
        $this->shopID = $scfg->get('talkpay_ShopID');
        $this->button_type_pc = $scfg->get('talkpay_btn_type');
        $this->button_type_mobile = $scfg->get('talkpay_btn_type_m');
        $this->scfg = $scfg;
    }

    /**
     * 상품 상세 구매버튼 출력
     **/
    public function printButton($prd)
    {
        global $tbl, $scfg, $member;

        // 버튼 스타일
        list($type, $darkMode) = explode('_', $this->{'button_type_'.$_SESSION['browser_type']});
        if ($_SESSION['browser_type'] == 'mobile') {
            $type .= 'M';
        }

        // 버튼 활성화
        if (gettype($prd) == 'integer') {
            $btn_enable = ($prd > 0) ? 'true' : 'false';
            $prd = array('hash' => 'false');
        } else {
    		$btn_enable = ($prd['stat'] == 2 && $prd['use_talkpay'] == 'Y' && $prd['sell_prc'] > 0) ? 'true' : 'false';
            if ($prd['no'] != $prd['parent']) {
                $prd['hash'] = $this->pdo->row("select hash from {$tbl['product']} where no='{$prd['parent']}'");
            }
            $prd['hash'] = "\"{$prd['hash']}\"";
        }

        $is_login = ($member['no'] > 0) ? 'true' : 'false';
        $usePayOrder = ($scfg->comp('use_kakaopay', 'Y') == true) ? 'true' : 'false';
        $snackMode = ($_SESSION['browser_type'] == 'mobile' && $scfg->comp('talkpay_btn_snack_mb', 'Y')) ? 'true' : 'false';

        // 버튼 출력
        return "
        <div id='kakao_talkpay_buttons'>
            ​<script type=\"text/javascript\" src=\"".self::__BUTTON_URL__."\"></script>
            <script>
            kakaoCheckout.createButton({
                authKey: \"{$this->button_auth_key}\",
                shopProductId: {$prd['hash']},
                buttonType: \"$type\",
                darkMode: $darkMode,
                containerId: \"kakao_talkpay_buttons\",
                showWishButton: true,
                isLogin: $is_login,
                usePayOrder: $usePayOrder,
                enable: $btn_enable,
                snackMode: $snackMode,
                onOrder: function() {
                    if ('{$prd['hash']}' == 'false') {
                        return order_talkpay;
                    } else {
                        if (addCart(document.prdFrm, 'talkpay') == 'success') {
                            return buy_talkpay;
                        }
                    }
                },
                onPayOrder: function() {
                    if ('{$prd['hash']}' == 'false') {
                        var url = root_url+'/shop/order.php?pay_type=kakaopay';
                        var cart_selected = '';
                        $(':checked[name=\"cno[]\"]').each(function() {
                            if (cart_selected) cart_selected += ',';
                            cart_selected += this.value;
                        });
                        if (cart_selected) {
                            url += '&cart_selected='+cart_selected;
                        }
                        location.href = url;
                    } else {
                        if (addCart(document.prdFrm, 'talkpay') == 'success') {
                            var f = document.prdFrm;
                            f.next.value = 'talkpay_direct';
                            $.post(f.action, $(f).serialize(), function(r) {
                                if (r.redirect_url) location.href = r.redirect_url;
                                else window.alert(r);
                            });
                        }
                    }
                },
                onWish: function(err) {
                    //
                }
            });
            </script>
        </div>
        ";
    }

    /**
     * 판매점 정보 연결
     **/
    public function mapping()
    {
        return $this->api('/v1/shops/mapping', array(
            'storeKey' => $this->shopID
        ));
    }

    /**
     * 판매점 상태 활성화
     **/
    public function serviceOn()
    {
        return $this->api('/v1/shops/service-on', array(
            'storeKey' => $this->shopID
        ), 'PUT');
    }

    /**
     * 판매점 상태 일시 중지
     **/
    public function serviceOff()
    {
        return $this->api('/v1/shops/service-off', array(
            'storeKey' => $this->shopID
        ), 'PUT');
    }

    /**
     * 판매점 상태 조회
     **/
    public function shopStatus()
    {
        return $this->api('/v1/shops/status');
    }

    /**
     * 주문서 생성
     **/
    public function order($direct_no = '')
    {
        global $tbl, $root_url;

        $GLOBALS['member']['level'] = 10; // 카카오페이 구매는 회원혜택 받을수 없도록 비회원 처리

        $order = array(
            'products' => array(),
            'continuousUrl' => $root_url,
            /* 문화비 여부 */
            'mcstCultureBenefit' => false,
            /* customData1 최대 300자를 넘을 수 없으므로 개발시 주의 */
            'customData1' => json_encode(array(
                'browser_type' => $_SESSION['browser_type']
            )),
            /* 광고 유입 코드 */
            'salesCode' => $_SESSION['conversion']
        );

        // 장바구니
        if($direct_no) {
            $GLOBALS['cart_selected'] = $direct_no;
        }
		$ptnOrd = new \OrderCart();
		while($cart = cartList('/', ':', '', '', 0, 0, 4)) {
			$ptnOrd->addCart($cart);
		}
		$ptnOrd->complete();

		$prds = 0;
        $cart_no = array();
		while($cart = cartList('/', ':', '', '', 0, 0, 4)) {
            // 개별배송 및 재고미사용 상품 사용 불가
            if ($cart['delivery_set'] > 0 || $cart['ea_type'] != '1') {
                msg('카카오페이 구매가 불가능한 상품입니다.');
            }

            if ($cart['set_idx']) {
                msg('세트상품은 카카오페이구매로 구매할수 없습니다.');
            }

            // 배송비 및 결제금액 계산
			$ptnOrd = new \OrderCart();
			$ptnOrd->addCart($cart);
			$ptnOrd->complete('Y');
			$obj = $ptnOrd->loopCart();
            $pconf = $obj->parent->getData('conf'); // 배송설정

            $base_prc = $obj->getData('sum_sell_prc') / (int) $obj->getData('buy_ea');
            $option_prc = 0;

            // 전체 상품 금액 중 옵션의 비율(옵션 추가금액에 할인율 감안)
            settype($cart['option_prc'], 'integer');
            if ($cart['option_prc'] !== 0) {
                $opt_per = $cart['option_prc']/$cart['sell_prc'];
                $option_prc = floor($base_prc*$opt_per);
                $base_prc -= $option_prc;
            }

            $product = array(
                'id' => $cart['hash'],
                'name' => stripslashes(strip_tags($cart['name'])),
                'basePrice' => $base_prc,
                'taxType' => ($cart['tax_free'] == 'Y') ? 'TAX_FREE' : 'TAX',
                'informationUrl' => $root_url.'/shop/detail.php?pno='.$cart['hash'],
                'imageUrl' => getListImgURL($cart['updir'], $cart['upfile3']),
                'shippingPolicy' => array()
            );

            // 옵션 정보
            $option_no = array(); // 옵션 아이템 코드(윙포스 미사용시 manage_code)
            $select_cnt = 0; // 선택형 옵션의 수
            $selectItems = array(); // 선택한 옵션 아이템 목록
            $manage_code_n = array(); // 선택옵션에 의한 managecode 중복 방지용
            if ($cart['option_idx']) {
                $_oidx_big = explode('<split_big>', $cart['option_idx']);
                $_oval_big = explode('<split_big>', $cart['option']);
                foreach ($_oidx_big as $okey => $_oidx_small) {
                    list($opno, $ino) = explode('<split_small>', $_oidx_small);
                    list($oname, $ovalue) = explode('<split_small>', $_oval_big[$okey]);
                    $optdata = $this->pdo->assoc("select otype, name from {$tbl['product_option_set']} where no='$opno' and necessary!='P'");

                    if ($optdata['otype'] != '4B') {
                        $selectItems++;
                    }

                    $option_no[] = $ino;
                    $selectItems[] = array(
                        'type' => ($optdata['otype'] == '4B') ? 'INPUT' : 'SELECT',
                        'name' => stripslashes($optdata['name']),
                        'id' => $ino,
                        'text' => $ovalue
                    );

                    if (count($_oidx_big) == 1 && $option_prc > 0 && $optdata['otype'] == '4B') {
                        header('Content-type:application/json');
                        exit(json_encode(array('result' => 'false', 'message' => '입력 방식 옵션과 옵션 추가금액을 같이 이용할 수 없습니다.')));
                    }

                    $manage_code_n[] = $ino;
                }
            }
            if (count($option_no) > 0) {
                sort($manage_code_n);
                $manage_code_n = implode('x', $manage_code_n);
                $manage_code = $cart['complex_no'].'x'.$manage_code_n;
                $product['options'][] = array(
                    'quantity' => $cart['buy_ea'],
                    'manageCode' => $manage_code,
                    'price' => $option_prc,
                    'selectItems' => $selectItems
                );
            } else {
                $product['singleItemQuantity'] = $cart['buy_ea'];
            }

            // 배송정보
            $group_id = $obj->getData('dlv_partner_no');
			$dlv_feeType = 'CHARGE';
			if ($obj->parent->getData('is_freedlv') == 'Y') {
                $dlv_feeType = 'FREE';
            }
			else if ($pconf['delivery_type'] == 3 && $pconf['delivery_free_limit'] > 0) {
                $dlv_feeType = 'CONDITIONAL_FREE';
            }
			$dlv_feePayType = 'PREPAID'; // 선불
			if ($obj->parent->getData('is_freedlv') == 'Y') $dlv_feePayType = 'FREE'; // 무료
			if ($obj->parent->getData('is_cod') == 'Y') $dlv_feePayType = 'POSTPAID'; // 착불
			if ($dlv_feeType == 'FREE') {
				$dlv_prc = 0;
			}
            $baseFee = 0;
			if($dlv_feePayType == 'POSTPAID') {
				$baseFee = $obj->parent->getData('cod_prc'); // 선불
			} else {
				$baseFee = parsePrice($pconf['delivery_fee']);
			}

            $product['shippingPolicy'] = array(
                'groupId' => $group_id,
                'feeType' => $dlv_feeType,
                'feePayType' => $dlv_feePayType,
            );
            if ($dlv_feeType != 'FREE') {
                $product['shippingPolicy']['baseFee'] = parsePrice($baseFee);
                if ($pconf['delivery_free_limit'] > 0) {
                    $product['shippingPolicy']['conditionalFree'] = array(
                        'basePrice' => parsePrice($pconf['delivery_free_limit']),
                    );
                }
            }

            $order['products'][] = $product;
            unset($product);

            $cart_no[] = $cart['cno'];
        }

        fwriteTo('_data/cache/talkpay.json', print_r($order, true), 'w');

        $ret = $this->api('/v1/order-sheets', $order);

        if ($ret->orderSheetId) {
            if ($this->scfg->comp('talkpay_truncate_cart', 'Y') == true) {
                $cart_no = implode(',', $cart_no);
                $this->pdo->query("delete from {$tbl['cart']} where no in ($cart_no)");
            }
        }

        return $ret;
    }

    /**
     * 주문상품 목록 조회
     **/
    public function getChangedOrder($startDateTime = null, $endDateTime = null, $continuationToken = null)
    {
        if (is_null($startDateTime) == true) {
            $startDateTime = strtotime('-2 hours');
            $startDateTime = $this->convertDateFormat($startDateTime);
        }

        return $this->api('/v1/order-products.simple?'.http_build_query(array(
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'continuationToken' => $continuationToken
        )));
    }

    /**
     * 주문번호 조회
     **/
    public function getOrderById($orderProductIds)
    {
        return $this->api('/v1/order-products.simple?'.http_build_query(array(
            'orderProductIds' => $orderProductIds,
        )));
    }

    /**
     * 주문 상품 상세 조회
     **/
    public function getOrderProduct($orderProductIds)
    {
        return $this->api('/v1/order-products?'.http_build_query(array(
            'orderProductIds' => $orderProductIds
        )));
    }

    /**
     * 상품준비중 처리
     **/
    public function confirm($orderProductId, $dlv_no = null, $invoiceNo = null)
    {
        $logisticsCode = null;
        if ($dlv_no && $invoiceNo) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:confirm', array(
            'orderProductId' => $orderProductId,
            'logisticsCode' => $logisticsCode,
            'invoiceNo' => $invoiceNo
        ));

        if ($ret->orderProductId) {
            return 'OK';
        }

        return $ret->message;
    }

    /**
     * 배송중 처리
     **/
    public function delivery($orderProductId, $dlv_no = 0, $invoiceNo = 0)
    {
        if (!$dlv_no) $dlv_no = 0;
        if (!$invoiceNo) $invoiceNo = 0;

        if ($dlv_no > 0) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:delivery', array(
            'orderProductId' => $orderProductId,
            'logisticsCode' => $logisticsCode,
            'invoiceNo' => $invoiceNo
        ));

        if ($ret->orderProductId) {
            return 'OK';
        }

        return $ret->message;
    }

    /**
     * 택배사 코드 조회
     **/
    public function getLogistics()
    {
        return $this->api('/v1/logistics-companies');
    }

    /**
     * 배송 지연 처리
     **/
    public function delay($orderProductId, $estimatedDeliveryDate, $reasonType, $message)
    {
        $ret = $this->api('/v1/order-products:delay', array(
            'orderProductId' => $orderProductId,
            'estimatedDeliveryDate' => $estimatedDeliveryDate,
            'reasonType' => $reasonType,
            'message' => $message,
        ));

        if ($ret->orderProductId) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 주문 취소 승인
     **/
    public function cancelApprove($orderId, $orderProductIds)
    {
        $ret = $this->api('/v1/order-products:cancel-approve', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 구매 취소
     **/
    public function cancelForce($orderId, $orderProductIds, $requestType, $requestReason)
    {
        $ret = $this->api('/v2/order-products:cancel-force', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'requestType' => $requestType,
            'requestReason' => $requestReason
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 취소 불가 발송 처리
     **/
    public function cancelReject($orderId, $orderProductIds, $deliveryMethod, $dlv_no = '', $dlv_code = '')
    {
        if ($dlv_no > 0) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:cancel-reject', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'deliveryMethod' => $deliveryMethod,
            'invoiceNo' => $dlv_code,
            'logisticsCode' => $logisticsCode
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 반품 요청
     **/
    public function returnRequest($orderId, $orderProductIds, $collectMethodType, $requestType, $requestReason, $dlv_no = '', $dlv_code = '')
    {
        if ($dlv_no > 0) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:return-request', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'collectMethodType' => $collectMethodType,
            'requestType' => $requestType,
            'requestReason' => $requestReason,
            'invoiceNo' => $dlv_code,
            'logisticsCode' => $logisticsCode
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 반품 승인
     **/
    public function returnApprove($orderId, $orderProductIds)
    {
        $ret = $this->api('/v1/order-products:return-approve', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 반품 거부
     **/
    public function returnReject($orderId, $orderProductIds, $reason)
    {
        $ret = $this->api('/v1/order-products:return-reject', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'reason' => $reason
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 반품 보류
     **/
    public function returnHoldback($orderId, $orderProductIds, $holdbackReason, $extraFee)
    {
        $ret = $this->api('/v1/order-products:return-holdback', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'holdbackReason' => $holdbackReason,
            'extraFee' => $extraFee
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 교환 요청
     **/
    public function exchangeRequest($orderId, $orderProductIds, $collectMethodType, $requestType, $requestReason, $dlv_no = '', $dlv_code = '')
    {
        if ($dlv_no > 0) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:exchange-request', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'collectMethodType' => $collectMethodType,
            'requestType' => $requestType,
            'requestReason' => $requestReason,
            'invoiceNo' => $dlv_code,
            'logisticsCode' => $logisticsCode
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }


    /**
     * 교환 재배송
     **/
    public function exchangeRedelivery($orderId, $orderProductIds, $deliveryMethod, $dlv_no = '', $dlv_code = '')
    {
        if ($dlv_no > 0) {
            $logisticsCode = $this->getLogisticsCode($dlv_no);
            if (is_null($logisticsCode) == true) {
                return '택배사코드를 확인할수 없습니다.';
            }
        }

        $ret = $this->api('/v1/order-products:exchange-redelivery', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'deliveryMethod' => $deliveryMethod,
            'invoiceNo' => $dlv_code,
            'logisticsCode' => $logisticsCode
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 교환 거부
     **/
    public function exchangeReject($orderId, $orderProductIds, $reason)
    {
        $ret = $this->api('/v1/order-products:exchange-reject', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'reason' => $reason
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 교환 보류
     **/
    public function exchangeHoldback($orderId, $orderProductIds, $holdbackReason, $extraFee)
    {
        $ret = $this->api('/v1/order-products:exchange-holdback', array(
            'orderId' => $orderId,
            'orderProductIds' => $orderProductIds,
            'holdbackReason' => $holdbackReason,
            'extraFee' => $extraFee
        ));

        if ($ret->claimIds) {
            return true;
        }

        return $ret->message;
    }

    /**
     * 상품 문의 수집
     **/
    public function getQuestions($startDateTime, $endDateTime = null, $size = 100, $continuationToken = null)
    {
        if (is_null($startDateTime) == true) {
            $startDateTime = $this->convertDateFormat(
                strtotime('-2 hours')
            );
        }

        return $this->api('/v1/questions?'.http_build_query(array(
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'continuationToken' => $continuationToken,
            'size' => $size
        )));
    }

    /**
     * 상품 문의 답변
     **/
    public function answer($contents, $questionId, $answerId = null)
    {
        $questionId = preg_replace('/^talkpay/', '', $questionId);

        $req = array(
            'contents' => $contents,
            'questionId' => $questionId,
        );
        if ($answerId > 0) {
            $req['answerId'] = $answerId;
        }

        return $this->api('/v1/questions:answer', $req);
    }

    /**
     * 상품 후기 수집
     **/
    public function getReviews($startDateTime, $endDateTime, $size = 100, $continuationToken = null)
    {
        if (is_null($startDateTime) == true) {
            $startDateTime = $this->convertDateFormat(
                strtotime('-2 hours')
            );
        }

        return $this->api('/v1/reviews?'.http_build_query(array(
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'continuationToken' => $continuationToken,
            'size' => $size
        )));
    }

    /**
     * 상품 정보 즉시 동기화
     **/
    public function syncProduct(array $productIds)
    {
        $ret = $this->api('/v1/product:sync', array(
            'productIds' => $productIds
        ));
        if (isset($ret->result) == false) {
            return false;
        }
        return $ret->result;
    }

    /**
     * API 전송
     **/
    private function api($url, $param = null, $method = null)
    {
        if ($param) {
            $param = json_encode(
                $param,
                (defined('JSON_PRETTY_PRINT') == true) ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES : null
            );
        }
        if (!$method) {
            $method = ($param) ? 'POST' : 'GET';
        }
        $url = self::__API_URL__.$url;

        $curl = new CurlConnection($url, $method, $param);
        $curl->setHeader(array(
            'Content-Type:application/json',
            'Access-Token: '.self::__REST_API_KEY__,
            'checkout-shop-key: '.$this->shopKey
        ));
        $curl->exec();
        $result = $curl->getResult(true);

        // 배송처리 로그
        if (preg_match('/\/v1\/order-products:/', $url) == true) {
            global $log_instance;

            $param = json_decode($param);
            startOrderLog($param->orderId, 'kakaopay구매');
            $log_instance->writeln($url, 'url');
            $log_instance->writeln(print_r($param, true), 'param');
            $log_instance->writeln(print_r($result, true), 'result');
        }

        return json_decode($result);
    }

    /**
     * 날짜 데이터 변경
     **/
    public function parseDateFormat($time, $set_timestamp = false)
    {
        if (!$time) return null;

        $time = str_replace('T', ' ', $time);
        $time = preg_replace('/\..*$/', '', $time);

        if ($set_timestamp == true) {
            $time = strtotime($time);
        }

        return $time;
    }

    /**
     * 타임존 포함된 날짜 데이터로 변경
     **/
    public function convertDateFormat($datetime)
    {
        if (preg_match('/^[0-9]{10}$/', $datetime) == false) {
            $datetime = strtotime($datetime);
        }
        return date('Y-m-d', $datetime).'T'.date('H:i:s', $datetime).'.000';
    }

    /**
     * 전화번호 데이터 변경
     **/
    public function parsePhoneFormat($num)
    {
        return preg_replace('/^8210/' , '010', $num);
    }

    /**
     * 상태 값 매칭
     **/
    public function parseStatusType($product)
    {
        if (count($product->claim) > 0) {
            switch($product->claim->claimProcessType) {
                case 'CANCEL_REQUEST' :
                    if ($product->status == 'PAID' || $product->status == 'PREPARING_DELIVERY') {
                        return 14;
                    }
                    return 12;
                case 'CANCEL_DONE' :
                    return 15;
                case 'CANCEL_REJECT' :
                    break;
                case 'CANCEL_REFUND_FAILED' :
                    break;
                case 'CANCEL_WAIT_REFUND_SAVING' :
                    return 12;
                case 'CANCEL_REFUND_BANK_ACCOUNT' :
                    return 12;
                case 'RETURN_REQUEST' :
                    return 16;
                case 'RETURN_BACKWARD_DELIVERY' :
                    return 22;
                case 'RETURN_BACKWARD_DELIVERY_DONE' :
                    return 23;
                case 'RETURN_DONE' :
                    return 17;
                case 'RETURN_REFUND_FAILED' :
                    return 16;
                case 'RETURN_WAIT_REFUND_SAVING' :
                    return 16;
                case 'RETURN_HOLDBACK' :
                    return 28;
                case 'RETURN_REJECT' :
                    break;
                case 'RETURN_BACKWARD_DELIVERY_CHECKING' :
                    return 14;
                case 'EXCHANGE_REQUEST' :
                    return 18;
                case 'EXCHANGE_BACKWARD_DELIVERY' :
                    return 24;
                case 'EXCHANGE_BACKWARD_DELIVERY_DONE' :
                    return 25;
                case 'EXCHANGE_FORWARD_DELIVERY' :
                    return 26;
                case 'EXCHANGE_REFUND_FAILED' :
                    return 18;
                case 'EXCHANGE_WAIT_REFUND_SAVING' :
                    return 18;
                case 'EXCHANGE_HOLDBACK' :
                    return 27;
                case 'EXCHANGE_DONE' :
                    return 19;
                case 'EXCHANGE_REJECT' :
                    break;
            }
        }
        switch($product->status) {
            case 'WAIT_DEPOSIT' :
                return 1;
            case 'PAID' :
                return 2;
            case 'RETURNED' :
                return 17;
            case 'EXCHANGED' :
                return 19;
            case 'DEPOSIT_CANCELED' :
                return 13;
            case 'PREPARING_DELIVERY' :
                return 3;
            case 'IN_DELIVERY' :
                return 4;
            case 'DELIVERY_COMPLETED' :
                return 5;
            case 'PURCHASE_DECISION' :
                return 5;
            case 'CANCELED' :
                return 15;
        }
    }

    /**
     * 주문의 클레임 미 적용시 상태 값
     **/
    public function getCurrentStatus($orderProductId)
    {
        $pdata = $this->getOrderProduct($orderProductId);

        if (count($pdata->orderProducts) == 1) {
            $order_prd = $pdata->orderProducts[0];
            unset($order_prd->claim);

            return $this->parseStatusType($order_prd);
        }
        return false;
    }

    /**
     * 결제 수단 매칭
     **/
    public function parsePaymentMethodType($type)
    {
        switch($type) {
            case 'VIRTUAL_ACCOUNT' :
                return 4;
            case 'CREDIT_CARD';
                return 2;
            case 'KAKAOPAY_CARD' :
                return 12;
            case 'KAKAOPAY_MONEY' :
                return 12;
            case 'MOBILE' :
                return 7;
        }
    }

    /**
     * 택배사 코드 매칭
     **/
    public function MatchLogistics($code, $partner_no)
    {
        global $scfg, $tbl, $talkpay_logistis;

        // 택배사 매칭 캐시 생성
        if (isset($talkpay_logistis[$partner_no]) == false) {
            $logistis = $this->getLogistics();

            if (is_array($talkpay_logistis) == false) {
                $talkpay_logistis = array();
            }
            $asql = ($scfg->comp('use_partner_delivery', 'Y') == true) ? " and partner_no='$partner_no'" : '';
            $res = $this->pdo->iterator("select * from {$tbl['delivery_url']} where 1 $asql");
            foreach ($res as $data) {
                foreach ($logistis as $val) {
                    $val->name = str_replace(' ', '', strtolower($val->name));
                    $data['name'] = str_replace(' ', '', strtolower($data['name']));
                    if ($data['name'] == 'cjgls') {
                        $data['name'] = 'CJ대한통운';
                    }
                    if ($data['name'] == '로젠') {
                        $data['name'] = '로젠택배';
                    }
                    if ($val->name == $data['name']) {
                        if (strlen($data['partner_no']) == 0) {
                            $data['partner_no'] = 0;
                        }
                        $talkpay_logistis[$data['partner_no']][$val->code] = $data['no'];
                        break;
                    }
                }
            }
        }

        if (strlen($partner_no) == 0) {
            $partner_no = 0;
        }
        return $talkpay_logistis[$partner_no][$code];
    }

    /**
     * 스마트윙 택배사 번호로 카카오페이 구매 택배사 코드 리턴
     **/
    public function getLogisticsCode($dlv_no)
    {
        global $tbl;

        $dlv_name = $this->pdo->row("select name from {$tbl['delivery_url']} where no='$dlv_no'");
        $dlv_name = str_replace(' ', '', strtolower($dlv_name));
        if (empty($dlv_name) == true) {
            return false;
        }
        if ($dlv_name == 'cjgls') $dlv_name = 'cj대한통운';
        if ($dlv_name == '로젠') $dlv_name = '로젠택배';

        $logistics = $this->getLogistics();
        foreach ($logistics as $val) {
            $val->name = preg_replace('/,.*/', '', $val->name);
            $name = str_replace(' ', '', strtolower($val->name));
            if (strcmp($name, $dlv_name) === 0) {
                return $val->code;
            }
        }
        return null;
    }

    /**
     * 택배사 선택 select 출력
     **/
    public function getLogisticsSelect($partner_no)
    {
        global $tbl, $scfg;

        $psql = '';
		if($scfg->comp('use_partner_shop', 'Y') == true) {
			$psql = (!$partner_no) ? " and partner_no in (0, '')" : " and partner_no='$partner_no'";
		}

        $dlv_url = array();
        $dres = $this->pdo->iterator("select * from {$tbl['delivery_url']} where 1 $psql order by sort asc");
        foreach ($dres as $dlvdata) {
            $dlv_url[$dlvdata['no']] = stripslashes($dlvdata['name']);
        }

        return selectArray($dlv_url, 'dlv_no', false, ':: 택배사 선택 ::');
    }

    /**
     * 배송 지연 사유
     **/
    public function getDelayReasonType()
    {
        return array(
            'OUT_OF_STOCK' => '단기 재고 부족',
            'TOO_MUCH_ORDER' => '주문폭주로 인한 배송지연',
            'CUSTOM_MADE' => '주문 제작 상품',
            'REQUEST_PURCHASER' => '고객 요청',
            'ETC' => '기타',
        );
    }

    /**
     * 상품문의 카테고리
     **/
    public function getQuestionCategory($category)
    {
        switch($category) {
            case 'PRODUCT' : return '상품';
            case 'DELIVERY' : return '배송';
            case 'RETURN' : return '반품';
            case 'EXCHANGE' : return '교환';
            case 'CANCEL' : return '취소';
            case 'REFUND' : return '환불';
        }
        return '';
    }

    /**
     * API 사용 여부 체크
     **/
    public function compareShopKey($shopKey)
    {
        /*
        if ($this->use_yn != 'Y') {
            exit(json_encode(array('result' => 'false', 'message' => '카카오페이 구매를 사용중이 아닙니다.')));
        }
        */

        if (empty($this->shopKey) == true) {
            exit(json_encode(array('result' => 'false', 'message' => 'ShopKey가 설정되어있지 않습니다.')));
        }

        if (strcmp($this->shopKey, $shopKey) !== 0) {
            exit(json_encode(array('result' => 'false', 'message' => 'ShopKey가 일치하지 않습니다.')));
        }
    }

    /**
     * 첨부파일 가져오기
     **/
    public function getAddOnFile($file, $updir)
    {
        global $root_dir;

        $curl = new CurlConnection($file->url);
        $curl->exec();

        $up_filename = md5($file->url);
        $ext = getExt($file->name);
        $path = $root_dir.'/_data/'.$up_filename.'.'.$ext;

        // 임시 경로 쓰기 가능여부 체크
        if (is_writable(dirname($path)) == false) {
            return false;
        }

        $r = file_put_contents($path, $curl->getResult());
        if ($r == false) {
            return false;
        }

        $ret = \uploadFile(
            array(
                'tmp_name' => $path,
                'name' => $file->name,
                'size' => filesize($path)
            ),
            $up_filename,
            $updir
        );
        unlink($path);

        return $ret;
    }

    /**
     * 상품 정보 고시 출력
     **/
    public function getAnnoucement($pno)
    {
        global $tbl, $_talkstore_announce;

        if (isTable($tbl['kakaopaybuy_info']) == false || isTable($tbl['product_talkstore_announce']) == false) {
            return false;
        }

        $announcement = $this->pdo->assoc("
            select b.type, b.datas
            from {$tbl['kakaopaybuy_info']} a inner join {$tbl['product_talkstore_announce']} b on a.annoucement_idx=b.idx
            where pno='$pno' and a.annoucement_idx > 0
        ");

        if (is_array($announcement) == false) {
            return false;
        }

        if (is_array($_talkstore_announce) == false) {
            require_once __ENGINE_DIR__.'/_config/set.talkStore.php';
        }

        $items = array();
        $__fname = $_talkstore_announce[$announcement['type']]['fields'];
        $__fvalue = json_decode($announcement['datas']);
        foreach ($__fvalue as $key => $val) {
            if (!$__fname[$key] || !$val) continue;
            $items[] = array(
                'name' => $__fname[$key],
                'value' => $val
            );
        }
        return array(
            'name' => $_talkstore_announce[$announcement['type']]['name'],
            'items' => $items
        );
    }

    /**
     * 장품 정보고시 지정
     **/
    public function setAnnoucement($pno, $idx)
    {
        global $tbl;

        if (!$idx) return false;

        $kakao_info_idx = $this->pdo->row("select idx from {$tbl['kakaopaybuy_info']} where pno=?", array($pno));
        if ($kakao_info_idx == false) {
            return $this->pdo->query(
                "insert into {$tbl['kakaopaybuy_info']} (pno, annoucement_idx) values (?, ?)",
                array($pno, $idx)
            );
        } else {
            return $this->pdo->query(
                "update {$tbl['kakaopaybuy_info']} set annoucement_idx=? where idx=?",
                array($idx, $kakao_info_idx)
            );
        }
    }

}
