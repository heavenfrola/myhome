<?php

/**
 * 네이버 커머스 API
 */

namespace Wing\API\Naver;

require_once __ENGINE_DIR__ . '/_engine/include/cart.class.php';

// PHP 7 미만
if (!defined('DATE_RFC3339_EXTENDED')) {
    define('DATE_RFC3339_EXTENDED', 'Y-m-d\TH:i:s.uP');
}

Class CommerceAPI {

    use Variables; // 각종 사유 코드

    private $pdo;
    private $tbl;
    private $cfg;
    private $host;
    private $use;
    private $id;
    private $secret;
    private $timestamp;
    private $access_token;
    private $error;
    private $last_status;

    public function __construct()
    {
        global $pdo, $tbl, $scfg;

        $this->pdo = &$pdo;
        $this->tbl = &$tbl;
        $this->cfg = &$scfg;

        $this->host = 'https://api.commerce.naver.com/external';
        $this->use = $this->cfg->get('n_smart_store');
        $this->id = $this->cfg->get('n_store_app_id');
        $this->secret = $this->cfg->get('n_store_app_secret');

        if ($this->activated()) {
            $this->token();
        }
    }

    /**
     * 스마트스토어 세팅 완료 여부 체크
     * @return bool
     */
    public function activated()
    {
        return ($this->use == 'Y' && $this->id && $this->secret);
    }

    /**
     * 모델 조회
     * @param string $name 모델명
     * @param int $page 페이지
     * @return object
     */
    public function productModels($name, $page = 1)
    {
        $param = http_build_query(array(
            'name' => $name,
            'page' => $page,
            'size' => 100
        ));
        $result = $this->api('/v1/product-models?' . $param, 'GET');
        return $result;
    }

    /**
     * 모델 단건 조회
     * @param int $id 모델 ID
     * @return object
     */
    public function productModelsId($id)
    {
        $result = $this->api('/v1/product-models/' . $id, 'GET');
        return $result;
    }

    /**
     * 브랜드 조회
     * @param string $name 브랜드명
     * @return object
     */
    public function productBrands($name)
    {
        $param = http_build_query(array(
            'name' => $name
        ));
        $result = $this->api('/v1/product-brands?' . $param, 'GET');
        return $result;
    }

    /**
     * 카테고리별 상품 속성 항목 조회
     * @param int $categoryId 카테고리 번호
     * @return bool|object
     */
    public function productAttribute($categoryId)
    {
        $result = $this->api('/v1/product-attributes/attributes?categoryId=' . $categoryId, 'GET');
        return $result;
    }

    /**
     * 카테고리별 상품 속성 항목 값 조회
     * @param int $categoryId 카테고리 번호
     * @return bool|object
     */
    public function productAttributeValues($categoryId)
    {
        $result = $this->api('/v1/product-attributes/attribute-values?categoryId=' . $categoryId, 'GET');
        return $result;
    }

    /**
     * 상품 속성 단위 조회
     * @return bool|object
     */
    public function productAttributeUnits()
    {
        $result = $this->api('/v1/product-attributes/attribute-value-units', 'GET');
        return $result;
    }

    /**
     * 판매 상태 변경
     * @param int $originProductNo 원상품번호
     * @param string $stat 변경할 상태 값
     * @return object
     */
    public function productsChangeStatus($originProductNo, $stat)
    {
        $result = $this->api('/v1/products/origin-products/' . $originProductNo . '/change-status', 'PUT', array(
            'statusType' => $this->__toStat($stat)
        ));
        return $result;
    }

    /**
     * 전체 카테고리 조회
     * @param string|null $id 카테고리 id
     * @return object
     */
    public function categories($id = null)
    {
        $param = ($id) ? '/' . $id : '';
        $result = $this->api('/v1/categories' . $param, 'GET');
        return $result;
    }

    /**
     * 하위 카테고리 출력
     * @param string $id 카테고리 id
     * @return object
     */
    public function categoriesSubCategory($id)
    {
        $result = $this->api('/v1/categories/' . $id . '/sub-categories', 'GET');
        return $result;
    }

    /**
     * 원산지 코드 정보 전체 조회
     * @return object
     */
    public function productOriginAreas()
    {
        $result = $this->api('/v1/product-origin-areas', 'GET');
        return $result;
    }

    /**
     * 하위 원산지 코드 정보 전체 조회
     * @param string $code 부모 원산지 코드
     * @return false|object
     */
    public function subOriginAreas($code)
    {
        $result = $this->api('/v1/product-origin-areas/sub-origin-areas?code=' . $code, 'GET');
        return $result;
    }

    /**
     * 네이버페이 채널 상품 조회
     * @param int $channelProductNo 채널 상품 주문 번호
     * @return object
     */
    public function channelProducts($channelProductNo)
    {
        $ret = $this->api('/v2/products/channel-products/' . $channelProductNo, 'GET');
        return $ret;
    }

    /**
     * 상품 등록
     * @param int $pno 스마트윙 상품 주문 번호
     * @return object
     * @throws \Exception
     */
    public function products($pno)
    {
        // 상품 정보
		$afield = '';
		if ($this->cfg->comp('use_partner_shop', 'Y')) {
			$afield .= ', dlv_type, partner_no';
		}
        for ($i = 4; $i <= $this->cfg->get('add_prd_img'); $i++) {
            $afield .= ', upfile' . $i;
        }
        $data = $this->pdo->assoc(
            "
            select 
                no, name, code, stat, updir, upfile2, sell_prc, ea_type,
                min_ord, max_ord, max_ord_mem, content2, n_store_check $afield
            from {$this->tbl['product']} where no=? 
            ",
            array($pno)
        );
        $store = $this->pdo->assoc(
            "select * from {$this->tbl['product_nstore']} where pno=?",
            array($pno)
        );
        $extra_datas = json_decode($store['extra_datas']);
        if ($data['dlv_type'] == '1') { // 본사 배송
            $data['partner_no'] = '0';
        }

        if ($data['ea_type'] != '1') {
            throw new \Exception('스마트스토어 연동을 위해 재고관리를 "사용함"으로 설정하셔야 합니다.');
        }

        // 배송비 시뮬레이션
        $cart = new \OrderCart();
        $cart->addCart($data);
        $cart->complete();
        $ptns = $cart->getData('ptns');
        $ptn = array_shift($ptns);
        $pconf = $ptn->getData('conf');

        // 상품 이미지 업로드
        if ($data['upfile2']) {
            $image = $this->productImagesUpload(array(
                getListImgURL($data['updir'], $data['upfile2'])
            ));
        } else {
            $image = array();
        }

        // 추가 이미지
        $optionalImages = array();
        for ($i = 4; $i <= $this->cfg->get('add_prd_img'); $i++) {
            if ($data['upfile' . $i]) {
                $optionalImages[] = getListImgURL($data['updir'], $data['upfile' . $i]);
            }
        }
        if (count($optionalImages)) {
            $images = $this->productImagesUpload($optionalImages);
            $optionalImages = $images->images;
        }

        // 리프 카테고리 아이디
        if ($store['n_category_depth']) $leafCategoryId = $store['n_category_depth'];
        else if ($store['n_category_small']) $leafCategoryId = $store['n_category_small'];
        else if ($store['n_category_mid']) $leafCategoryId = $store['n_category_mid'];
        else if ($store['n_category_big']) $leafCategoryId = $store['n_category_big'];

        // 배송비 정책
        $deliveryFeeType = 'CHARGE';
        if ($ptn->getData('is_freedlv') == 'Y') $deliveryFeeType = 'FREE';
        else if($pconf['delivery_type'] == '3' && intval($pconf['delivery_free_limit']) > 0) $deliveryFeeType = 'CONDITIONAL_FREE';
        $dlv_prc = intval($pconf['delivery_fee']);
        if ($deliveryFeeType == 'FREE') {
            $dlv_prc = 0;
        }

        // 배송비 결제 방식
        $deliveryFeePayType = 'PREPAID'; // 선불
        if ($ptn->getData('is_freedlv') == 'Y') $deliveryFeePayType = 'FREE'; // 무료
        if ($ptn->getData('is_cod') == 'Y') $deliveryFeePayType = 'COLLECT'; // 착불
        if ($deliveryFeePayType == 'COLLECT') {
            $dlv_prc = intval($pconf['cod_prc']);
        }

        // 원산지 코드
        if ($store['n_origin_small']) $originAreaCode = $store['n_origin_small'];
        elseif ($store['n_origin_mid']) $originAreaCode = $store['n_origin_mid'];
        elseif ($store['n_origin_big']) $originAreaCode = $store['n_origin_big'];

        // 최소 구매 수량
        $_purchaseQuantityInfo = array();
        if ($data['min_ord'] > 1) {
            $_purchaseQuantityInfo['minPurchaseQuantity'] = intval($data['min_ord']);
        }
        if ($data['max_ord'] > 1) {
            $_purchaseQuantityInfo['maxPurchaseQuantityPerOrder'] = intval($data['max_ord']);
        }
        if ($data['max_ord_mem'] > 1 && $data['max_ord_mem'] > $data['max_ord']) {
            $_purchaseQuantityInfo['maxPurchaseQuantityPerId'] =  intval($data['max_ord_mem']);
        }
        if (count($_purchaseQuantityInfo)) {
            $purchaseQuantityInfo = $_purchaseQuantityInfo;
        }

        // 조합형 옵션
        $optionCombinationGroupNames = $optionSort = array();
        // 옵션 세트
        $res = $this->pdo->iterator(
            "select no, name from {$this->tbl['product_option_set']} where pno=? order by sort asc",
            array($pno)
        );
        foreach ($res as $key => $option) {
            $optionSort[$option['no']] = ($key + 1);
            $optionCombinationGroupNames['optionGroupName' . ($key + 1)] = stripslashes($option['name']);
        }
        if (count($optionCombinationGroupNames) > 4) {
            throw new \Exception('옵션은 최대 4개 까지만 지원합니다.');
        }
        // 옵션 아이템
        $qty = 0;
        $res = $this->pdo->iterator(
            "select complex_no, opts, qty, force_soldout from erp_complex_option where pno=? and del_yn='N' order by complex_no asc",
            array($pno)
        );
        $optionCombinations = array();
        foreach ($res as $complex) {
            switch ($complex['force_soldout']) {
                case 'Y' :
                    $complex['qty'] = 0;
                    break;
                case 'N' :
                    if ($complex['qty'] < 1) $complex['qty'] = 999;
                    break;
            }
            if (!$complex['opts']) { // 옵션 없음
                $data['complex_no'] = $complex['complex_no'];
            } else {
                $opts = str_replace('_', ',', trim($complex['opts'], '_'));
                $options = $this->pdo->iterator(
                    "select no, opno, iname, add_price from {$this->tbl['product_option_item']} where no in ($opts)"
                );
                $array = array(
                    'stockQuantity' => $complex['qty'],
                    'price' => 0,
                    'sellerManagerCode' => $complex['complex_no'],
                );
                foreach ($options as $option) {
                    $key = $optionSort[$option['opno']];
                    $array['optionName' . $key] = $option['iname'];
                    $array['price'] += intval($option['add_price']);
                }
                $optionCombinations[] = $array;
            }

            $qty += intval($complex['qty']);
        }

        // 묶음 배송 그룹
        $deliveryBundleGroupId = 0;
        if ($data['partner_no'] > 0) {
            $ret = $this->deliveryBundleGroupSet($data['partner_no']);
            $deliveryBundleGroupId = $ret->groupId;
        }

        // 개별 본문
        if ($this->cfg->comp('n_smart_content2', 'Y') && $store['n_content']) {
            $data['content2'] = $store['n_content'];
        }

        // 상품 태그
        if ($extra_datas->tags) {
            $tags = explode(',', $extra_datas->tags);
            foreach ($tags as $key => $tag) {
                $tags[$key] = array(
                    'text' => $tag
                );
            }
        }

        // 상품 등록
        $json = array(
            'originProduct' => array(
                'statusType' => ($data['n_store_check'] != 'Y') ? 'SUSPENSION' : $this->__toStat($data['stat']),
                'saleType' => 'NEW',
                'leafCategoryId' => $leafCategoryId,
                'name' => strip_tags($data['name']),
                'detailContent' => $data['content2'],
                'images' => array(
                    'representativeImage' => array(
                        'url' => $image->images[0]->url
                    ),
                    'optionalImages' => $optionalImages
                ),
                'salePrice' => (int) $data['sell_prc'],
                'stockQuantity' => $qty,
                'deliveryInfo' => array(
                    'deliveryType' => 'DELIVERY',
                    'deliveryAttributeType' => 'NORMAL',
                    'deliveryCompany' => $store['n_delivery_company'],
                    'deliveryBundleGroupUsable' => true,
                    'deliveryBundleGroupId' => ($data['partner_no'] > 0) ? $deliveryBundleGroupId : null,
                    'deliveryFee' => array(
                        'deliveryFeeType' => $deliveryFeeType,
                        'baseFee' => $dlv_prc,
                        'freeConditionalAmount' => intval($pconf['delivery_free_limit']),
                        'deliveryFeePayType' => $deliveryFeePayType,
                        'deliveryFeeByArea' => array(
                            'deliveryAreaType' => 'AREA_3',
                            'area2extraFee' => (int) $this->cfg->get('nstore_area2extraFee'),
                            'area3extraFee' => (int) $this->cfg->get('nstore_area3extraFee')
                        )
                    ),
                    'claimDeliveryInfo' => array(
                        'returnDeliveryFee' => intval($store['n_delivery_return_prc']),
                        'exchangeDeliveryFee' => intval($store['n_delivery_change_prc']),
                        'returnAddressId' => (int) $store['n_delivery_parcel'],
                    )
                ),
                'detailAttribute' => array(
                    'naverShoppingSearchInfo' => array(
                        'modelId' => ($extra_datas->n_model) ? $extra_datas->n_model : ''
                    ),
                    'afterServiceInfo' => array(
                        'afterServiceTelephoneNumber' => $store['n_as_tel'],
                        'afterServiceGuideContent' => $store['n_as_comment']
                    ),
                    'purchaseQuantityInfo' => $purchaseQuantityInfo,
                    'originAreaInfo' => array(
                        'originAreaCode' => $originAreaCode,
                        'importer' => $store['n_importer'],
                        'content' => $extra_datas->origin_content
                    ),
                    'sellerCodeInfo' => array(
                        'sellerManagementCode' => $data['complex_no'],
                        'sellerCustomCode1' => $data['no'],
                        'sellerCustomCode2' => $data['code']
                    ),
                    'minorPurchasable' => ($store['n_infant'] == 'Y'),
                    'customProductYn' => ($store['n_custom_made'] == 'Y'),
                    'seoInfo' => array(
                        'sellerTags' => $tags
                    )
                )
            ),
            'smartstoreChannelProduct' => array(
                'channelProductName' => ($extra_datas->channelProductName) ? $extra_datas->channelProductName : '',
                'naverShoppingRegistration' => true,
                'channelProductDisplayStatusType' => ($data['stat'] == '4' || $data['n_store_check'] != 'Y') ? 'SUSPENSION' : 'ON'
            )
        );

		// 모델명
		if (!$extra_datas->n_model) {
			$json['originProduct']['detailAttribute']['naverShoppingSearchInfo']['modelName'] = strip_tags($data['name']);
		}

        // 옵션
        if (count($optionCombinations)) {
            $json['originProduct']['detailAttribute']['optionInfo'] = array(
                'optionCombinationGroupNames' => $optionCombinationGroupNames,
                'optionCombinations' => $optionCombinations
            );
        }

        // 속성
        if ($extra_datas->attr) {
            $attr = array();
            foreach ($extra_datas->attr as $attributeSeq => $attributeValueSeqs) {
                foreach ($attributeValueSeqs as $attributeValueSeq) {
                    if ($attributeValueSeq) {
                        $attributeRealValue = ($extra_datas->attr_v->{$attributeSeq}) ? $extra_datas->attr_v->{$attributeSeq} : '';
                        $attributeRealValueUnitCode = ($extra_datas->attr_u->{$attributeSeq}) ? $extra_datas->attr_u->{$attributeSeq} : '';
                        $attr[] = array(
                            'attributeSeq' => $attributeSeq,
                            'attributeValueSeq' => $attributeValueSeq,
                            'attributeRealValue' => $attributeRealValue,
                            'attributeRealValueUnitCode' => $attributeRealValueUnitCode
                        );
                    }
                }
            }
            $json['originProduct']['detailAttribute']['productAttributes'] = $attr;
        }

        // 정보고시
        $summary = $this->pdo->assoc(
            "select a.datas, a.category, b.summary 
                from 
                    {$this->tbl['store_summary']} a 
                    inner join {$this->tbl['store_summary_type']} b on a.category=b.no 
                where a.no=?",
            array($store['n_summary_no'])
        );
        $productInfoProvidedNoticeType = preg_split('/(?=[A-Z])/', $summary['summary'], -1, PREG_SPLIT_NO_EMPTY);
        $productInfoProvidedNoticeType = implode('_', array_map('strtoupper', $productInfoProvidedNoticeType));
        if ($productInfoProvidedNoticeType) {
            $summary['datas'] = str_replace('/', '\\', $summary['datas']);
            $temp = json_decode($summary['datas']);
            $productInfoProvidedNotice = array();
            foreach ($temp as $k => $v) {
				if (!strlen($v)) continue;
                if ($v == 'Y') $v = true;
                else if ($v == 'N') $v = false;

                $productInfoProvidedNotice[lcfirst($k)] = $v;
            }
            $json['originProduct']['detailAttribute']['productInfoProvidedNotice'] =
                array(
                    'productInfoProvidedNoticeType' => $productInfoProvidedNoticeType,
                    lcfirst($summary['summary']) => $productInfoProvidedNotice
                );
        }

        // 세금 유형
        if ($store['n_taxtype']) {
            $json['originProduct']['detailAttribute']['taxType'] = $store['n_taxtype'];
        }

        // 도서 정보
        if ($this->cfg->comp('use_navershopping_book', 'Y')) {
            $book = $this->pdo->assoc("select * from {$this->tbl['product_book']} where no=?", array($pno));
            if ($book['isbn']) {
                $json['originProduct']['detailAttribute']['isbnInfo'] = array(
                    'isbn13' => $book['isbn']
                );
                $json['originProduct']['detailAttribute']['bookInfo'] = array(
                    'publishDay' => $book['publish_day'],
                    'publisher' => array(
                        'text' => $book['publisher']
                    ),
                    'authors' => array(
                        'text' => $book['author']
                    ),
                );
            }
        }

        // 인증
        if ($extra_datas->certificationInfoId) {
            $json['originProduct']['detailAttribute']['productCertificationInfos'] = array(
                'certificationInfoId' => intval($extra_datas->certificationInfoId),
                'certificationKindType' => $extra_datas->certificationKindType,
                'name' => $extra_datas->certificationInfoName,
                'certificationNumber' => $extra_datas->certificationNumber,
                'companyName' => '',
                'certificationDate' => ''
            );
        }
        if ($extra_datas->certificationTargetExclude == 'Y') {
            $json['originProduct']['detailAttribute']['certificationTargetExcludeContent'] = array(
                'childCertifiedProductExclusionYn' => true,
                'kcCertifiedProductExclusionYn' => 'TRUE',
                'greenCertifiedProductExclusionYn' => true
            );
        }

        $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        fwriteTo('_data/smartstore_product_log.txt', $json, 'w');

        if ($store['product_id']) {
            $result = $this->api('/v2/products/channel-products/' . $store['product_id'], 'PUT', $json);
        } else {
            $result = $this->api('/v2/products', 'POST', $json);
        }

        if ($result->message) {
            $message = '[스마트스토어] ' . $result->message;
            foreach ($result->invalidInputs as $invalid) {
                $message .= "\n- " . $invalid->message;
                if ($invalid->name) {
                    $message .= "\n   (" . $invalid->name . ")";
                }
            }
            throw new \Exception($message);
        }

        // 채널 상품 주문 번호 업데이트
        $this->pdo->query("update {$this->tbl['product_nstore']} set product_id=? where pno=?", array(
            $result->smartstoreChannelProductNo,
            $pno
        ));
        $this->pdo->query("update {$this->tbl['product']} set nstoreId=?, n_store_check='Y' where no=?", array(
            $result->smartstoreChannelProductNo,
            $pno
        ));

        return $result;
    }

    /**
     *
     * @param array $urls URL 배열
     * @return object|boolean
     * @throws \Exception
     */
    public function productImagesUpload(array $urls)
    {
        $boundary = uniqid();
        $postFields = '';
        foreach ($urls as $key => $url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ));
            $content = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if ($info['http_code'] != 200) continue;
            if (!$content) continue;

            $postFields .=
                "--" . $boundary . "\r\n" .
                "Content-Disposition: form-data; name=\"imageFiles[$key]\"; filename=\"" . basename($url) . "\"\r\n" .
                "Content-Type: " . $info['content_type'] . "\r\n\r\n" .
                $content . "\r\n";
        }
        if (!$postFields) {
            return false;
        }
        $postFields .= "--" . $boundary . "--";

        $ret = $this->api(
            '/v1/product-images/upload',
            'POST',
            $postFields,
            'multipart/form-data; boundary=' . $boundary
        );
        return $ret;
    }

    /**
     * 반품 택배사 다건 조회
     * @return object
     */
    public function productReturnDeliveryCompanies()
    {
        $ret = $this->api('/v2/product-delivery-info/return-delivery-companies', 'GET');
        return $ret;
    }

    /**
     * 판매자 주소록 목록 조회
     * @return object
     */
    public function sellerAddressbooksForPage()
    {
        $ret = $this->api('/v1/seller/addressbooks-for-page', 'GET');
        return $ret;
    }

    /**
     * 판매자 주소록 단건 조회
     * @param int $addressBookNo 주소록 번호
     * @return object
     */
    public function sellerAddressbooks($addressBookNo)
    {
        $ret = $this->api('/v1/seller/addressbooks/' . $addressBookNo, 'GET');
        return $ret;
    }

    /**
     * 상품 카테고리 정보를 읽어 카테고리 구조 출력
     * @param array|null $data 카테고리 데이터
     * @return array
     */
    public function makeCategoryData($data)
    {
        global $_cate_colname;

        $ret = array(
            'big' => array(),
            'mid' => array(),
            'small' => array(),
            'depth4' => array()
        );

        // 대분류
        $res = $this->categories();
        foreach ($res as $cate) {
            if (strpos($cate->wholeCategoryName, '>')) {
                continue;
            }
            $ret['big'][$cate->id] = $cate->name;
        }
        // 하위 분류
        for ($i = 2; $i <= 4; $i++) {
            $_parent = $_cate_colname[1][($i - 1)];
            $_self = $_cate_colname[1][$i];

            if ($data['n_category_' . $_parent]) {
                $res = $this->categoriesSubCategory($data['n_category_' . $_parent]);
                foreach ($res as $cate) {
                    $ret[$_self][$cate->id] = $cate->name;
                }
            }
        }

        return $ret;
    }

    /**
     * 상품 원산지 정보를 읽어 원산지 구조 출력
     * @param array|null $data 원산지 데이터
     * @return array
     */
    public function makeOriginData($data)
    {
        global $_cate_colname;

        $ret = array(
            'big' => array(),
            'mid' => array(),
            'small' => array()
        );

        // 대분류
        $res = $this->productOriginAreas();
        foreach ($res->originAreaCodeNames as $origin) {
            if (preg_match('/[>:]/', $origin->name)) {
                continue;
            }
            $ret['big'][$origin->code] = $origin->name;
        }
        // 하위 분류
        for ($i = 2; $i <= 3; $i++) {
            $_parent = $_cate_colname[1][($i - 1)];
            $_self = $_cate_colname[1][$i];

            if ($data['n_origin_' . $_parent]) {
                $res = $this->subOriginAreas($data['n_origin_' . $_parent]);
                foreach ($res->subOriginAreaCodeNames as $origin) {
                    $ret[$_self][$origin->code] = preg_replace('/.*(:>)/', '', $origin->name);
                }
            }
        }

        return $ret;
    }

    /**
     * 묶음 배송 그룹 등록 (입점사별로 묶음 기능으로 사용)
     * @param int $partner_no
     * @return object
     */
    public function deliveryBundleGroupSet($partner_no)
    {
        if (!$partner_no) return (object) array(
            'groupId' => 0
        );

        // 수정 여부
        $ret = $this->deliveryBundleGroupGet($partner_no);
        $param = ($ret->totalElements == 1) ? '/' . $ret->contents[0]->id : '';
        $methods = ($ret->totalElements == 1) ? 'PUT' : 'POST';

        $ret = $this->api('/v1/product-delivery-info/bundle-groups' . $param, $methods, json_encode(array(
            'deliveryBundleGroup' => array(
                'id' => ($ret->totalElements == 1) ? $ret->contents[0]->id : 0,
                'name' => 'partner_' . $partner_no . '_group',
                'baseGroup' => false,
                'usable' => true,
                'deliveryFeeChargeMethodType' => 'MAX',
                'deliveryFeeByArea' => array(
                    'deliveryAreaType' => 'AREA_3',
                    'area2extraFee' => (int) $this->cfg->get('nstore_area2extraFee'),
                    'area3extraFee' => (int) $this->cfg->get('nstore_area3extraFee')
                )
            )
        )));
        return $ret;
    }

    /**
     * 묶음 배송 그룹 검색
     * @param int $partner_no 입점사 번호
     * @return object
     */
    public function deliveryBundleGroupGet($partner_no)
    {
        $name = 'partner_' . $partner_no . '_group';
        $ret = $this->api('/v1/product-delivery-info/bundle-groups?name=' . $name, 'GET');
        return $ret;
    }

    /**
     * 사전 정보 등록
     * @param array $data $_POST로 전송된 상품 저장 데이터
     * @return void
     * @throws \Exception
     */
    public function saveProduct(array $data)
    {
        $store = $this->pdo->assoc("select no from {$this->tbl['product_nstore']} where pno=?", array(
            $data['pno']
        ));
        if ($store['extra_datas']) {
            $extra_datas = json_decode($store->extra_datas);
        } else {
            $extra_datas = (object) array();
        }
        $extra_datas->n_model = $data['n_model'];

        // 인증 방법
        $cert = explode('@', $data['n_certificationInfoId']);
        $extra_datas->certificationKindType = $cert[0];
        $extra_datas->certificationInfoId = $cert[1];
        $extra_datas->certificationInfoName = $data['n_certificationInfoName'];
        $extra_datas->certificationNumber = $data['n_certificationNumber'];
        $extra_datas->certificationTargetExclude = $data['n_certificationTargetExclude'];
        $extra_datas->channelProductName = $data['n_channelProductName'];
        $extra_datas->tags = trim($data['n_tags'], ',');
        $extra_datas->attr = $data['n_attr'];
        $extra_datas->attr_v = $data['n_attr_v'];
        $extra_datas->attr_u = $data['n_attr_u'];

        // 원산지 직접 입력
        if ($data['n_origin_big'] == '04') {
            $extra_datas->origin_content = $data['n_origin_content'];
        } else {
            unset($extra_datas->origin_content);
        }

        $update = array(
            'pno' => $data['pno'],
            'n_name' => $data['name'],
            'n_statustype' => $this->__toStat($data['stat']),
            'n_custom_made' => $data['n_custom_made'],
            'n_category_big' => $data['n_category_big'],
            'n_category_mid' => $data['n_category_mid'],
            'n_category_small' => $data['n_category_small'],
            'n_category_depth' => $data['n_category_depth4'],
            'n_origin_big' => $data['n_origin_big'],
            'n_origin_mid' => $data['n_origin_mid'],
            'n_origin_small' => $data['n_origin_small'],
            'n_importer' => ($data['n_importer']) ? $data['n_importer'] : '',
            'n_taxtype' => $data['n_taxtype'],
            'n_infant' => $data['n_infant'],
            'n_delivery_company' => $data['n_delivery_company'],
            'n_as_tel' => $data['n_as_tel'],
            'n_as_comment' => $data['n_as_comment'],
            'n_summary_no' => $data['n_summary_no'],
            'n_delivery_parcel' => $data['n_delivery_parcel'],
            'n_delivery_return_prc' => $data['n_delivery_return_prc'],
            'n_delivery_change_prc' => $data['n_delivery_change_prc'],
            'n_content' => $data['n_content'],
            'extra_datas' => json_encode($extra_datas, JSON_UNESCAPED_UNICODE)
        );

        if ($store) {
            $query = '';
            $update['update_date'] = time();
            $update['update_id'] = $GLOBALS['admin']['admin_id'];
            $update['update_ip'] = $_SERVER['REMOTE_ADDR'];
            foreach ($update as $k => $v) {
                $query .= ", `$k` = '" . addslashes($v) . "'";
            }
            $query = trim($query, ',');
            $r = $this->pdo->query(
                "update {$this->tbl['product_nstore']} set $query where no='{$store['no']}'"
            );
        } else {
            $update['insert_date'] = time();
            $update['insert_id'] = $GLOBALS['admin']['admin_id'];
            $update['insert_ip'] = $_SERVER['REMOTE_ADDR'];
            $keys = $values = '';
            foreach ($update as $k => $v) {
                $keys .= ", `$k`";
                $values .= ", '" . addslashes($v) . "'";
            }
            $keys = trim($keys, ',');
            $values = ltrim($values, ',');
            $r = $this->pdo->query(
                "insert into {$this->tbl['product_nstore']} ($keys) values ($values)"
            );
        }
        if (!$r) {
            throw new \Exception('스마트 스토어 상품 정보 저장 오류');
        }
    }

    /**
     * 상품 문의 조회
     * @param string $fromDate 조회 일시 시작
     * @param string $toDate 조회 일시 종료
     * @param int $page 페이지 번호
     * @return bool|object
     * @throws \Exception
     */
    public function contentsQnas($fromDate, $toDate, $page)
    {
        $fromDate = new \DateTime($fromDate);
        $toDate = new \DateTime($toDate);

        $params = http_build_query(array(
            'page' => $page,
            'size' => 100,
            'fromDate' => $fromDate->format(DATE_RFC3339_EXTENDED),
            'toDate' => $toDate->format(DATE_RFC3339_EXTENDED)
        ));
        $ret = $this->api('/v1/contents/qnas?' . $params, 'GET');
        return $ret;
    }

    /**
     * 상품 문의 답변 등록/수정
     * @param string $questionId 상품 문의 ID
     * @param string $content
     * @return bool|object 상품 문의 답변 내용
     */
    public function contentsQnasPut($questionId, $content) {
        $ret = $this->api('/v1/contents/qnas/' . $questionId, 'PUT', json_encode(array(
            'commentContent' => $content
        )));
        return $ret;
    }

    /**
     * 변경 주문 정보 조회
     * @param string $from 검색 시작일
     * @param string $to 검색 종료일
     * @return bool|object
     */
    public function ordersChanged($from, $to = '', $moreSequence = null)
    {
        $iso_from = str_replace('+', '.0+', date('c', strtotime($from)));
        $iso_to = ($to) ? str_replace('+', '.999+', date('c', strtotime($to))) : null;

        $params = http_build_query(array(
            'lastChangedFrom' => $iso_from,
            'lastChangedTo' => $iso_to,
            'moreSequence' => $moreSequence,
            'limitCount' => 300
        ));
        $res = $this->api('/v1/pay-order/seller/product-orders/last-changed-statuses?' . $params, 'GET');
        return $res;
    }

    /**
     * 주문 상세 정보
     * @param string|array $productOrderId 스마트스토어 상품 주문 번호
     * @return bool|object
     */
    public function ordersQuery($productOrderId)
    {
        global $ordersQuery_count;

        // 주문 상품 조회 API 가 초당 2회만 허용 되므로 1회 이상 부터
        if (!isset($ordersQuery_count)) $ordersQuery_count = 0;
        else $ordersQuery_count++;
        if ($ordersQuery_count > 0) {
            usleep(550000);
        }

        if (is_array($productOrderId)) {
            $productOrderIds = $productOrderId;
        } else {
            $productOrderIds = array(
                $productOrderId
            );
        }

        $ret = $this->api('/v1/pay-order/seller/product-orders/query', 'POST', json_encode(array(
            'productOrderIds' => $productOrderIds
        )));

        return $ret;
    }

    /**
     * 스마트스토어 서버에 있는 현재 주문 상태 체크
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return false|int|string
     */
    public function getCurrentStat($productOrderId)
    {
        $ret = $this->ordersQuery($productOrderId);
        if (!$ret->data) {
            return false;
        }
        return $this->__orderStat($ret->data[0]->productOrder);
    }

    /**
     * 주문 번호를 받아 갱신
     * @param array $orders 변경 주문번호 목록
     * @return array
     */
    public function orderSave(array $orders)
    {
		global $engine_dir, $pdo, $cfg, $tbl, $_pay_type, $sms_replace, $erpListener;

        // 변경된 주문 번호
        $changed = array();

        // 입점사 배송비 정산 정보
        $_dlv_prc = array();

		// 추가된 주문번호
		$insert_ono = array();

        // 주문서 갱신
        $ords = array();
        foreach ($orders as $loop_idx => $ord) {
            $ords[] = $ord->productOrderId;
        }
        $ords_chunk = array_chunk($ords, 100); // 최대 한번에 200개까지 조회 가능

        foreach ($ords_chunk as $ords) {
            $ords = $this->ordersQuery($ords);
            if (!$ords->data) continue;

            foreach ($ords->data as $data) {
                // 주문서
                $ono = $data->order->orderId;
                $date1 = strtotime($data->order->orderDate);
                $date2 = ($data->order->paymentDate) ? strtotime($data->order->paymentDate) : 0;
                $date3 = ($data->productOrder->placeOrderDate) ? strtotime($data->productOrder->placeOrderDate) : 0;
                $date4 = ($data->delivery->sendDate) ? strtotime($data->delivery->sendDate) : 0;
                $date5 = ($data->delivery->deliveredDate) ? strtotime($data->delivery->deliveredDate) : 0;
                $pay_type = $this->__orderPayType($data->order->paymentMeans);
                $dlv_prc = $data->productOrder->deliveryFeeAmount;
                if ($data->order->generalPaymentAmount + $data->order->chargeAmountPaymentAmount + $data->order->naverMileagePaymentAmount == 0) {
                    $dlv_prc = 0;
                }
                $pay_prc = $data->order->generalPaymentAmount;
                $mobile = ($data->order->payLocationType == 'PC') ? 'N' : 'Y';
                $memo = $data->productOrder->shippingMemo;
                // 주문자 정보
                $o_name = $data->order->ordererName;
                $o_cell = $data->order->ordererTel;
                // 배송 정보
                $r_zip = $data->productOrder->shippingAddress->zipCode;
                $r_addr1 = $data->productOrder->shippingAddress->baseAddress;
                $r_addr2 = $data->productOrder->shippingAddress->detailedAddress;
                $r_cell = str_replace('-', '', $data->productOrder->shippingAddress->tel1);
                $r_name = $data->productOrder->shippingAddress->name;
                $dlv_name = $data->delivery->deliveryCompany;
                $dlv_code = $data->delivery->trackingNumber;
                $dlv_no = ($data->delivery->deliveryCompany) ? $this->__toDlvNo($data->delivery->deliveryCompany) : 0;
                // 주문 상품
                $pno = $data->productOrder->sellerCustomCode1;
                if (!$pno) {
                    $pno = $data->productOrder->sellerProductCode;
                }
                $productOrderId = $data->productOrder->productOrderId;
                $name = $data->productOrder->productName;
                $stat = $this->__orderStat($data->productOrder);
                $buy_ea = $data->productOrder->quantity;
                $option = ($data->productOrder->productOption) ? $data->productOrder->productOption : '';
                $complex_no = ($data->productOrder->optionManageCode) ? $data->productOrder->optionManageCode : $data->productOrder->sellerProductCode;
                $prd_prc = $data->productOrder->unitPrice + $data->productOrder->optionPrice;
                $option_prc = $data->productOrder->optionPrice;
                $total_prc = $data->productOrder->totalProductAmount;
                if ($data->productOrder->productDiscountAmount > 0) {
                    $total_prc = $data->productOrder->totalPaymentAmount;
                }
                $sell_prc = floor($total_prc / $buy_ea);

                // 일부 구버전 데이터 상품 코드 보정
                if ($data->productOrder->productId) {
                    $_pno = $this->pdo->row("select pno from {$this->tbl['product_nstore']} where product_id='{$data->productOrder->productId}'");
                    if ($_pno != $pno && $_pno == $complex_no) {
                        $complex_no = $pno;
                        $pno = $_pno;
                    }
                }

                // 입점사
                $dlv_partner_no = 0;
                if ($this->cfg->comp('use_partner_shop', 'Y')) {
                    $prd = $this->pdo->assoc(
                        "select partner_no, dlv_type, partner_rate from {$this->tbl['product']} where no=?",
                        array($pno)
                    );
                    $dlv_partner_no = ($prd->dlv_type == '1' || !$this->cfg->comp('use_partner_delivery', 'Y'))
                        ? 0 : $prd['partner_no'];
                    $partner_no = $prd['partner_no'];
                }
                $_dlv_prc[$ono][$dlv_partner_no] = $dlv_prc;

                // 재고 감산
                $oprd = $this->pdo->assoc("select no, stat from {$this->tbl['order_product']} where ono=? and smartstore_ono=?", array(
                    $ono, $productOrderId
                ));
                if ($oprd['stat'] == '20' && $stat == '2') $stat = '20';

                // 저장 데이터
                $key = array(
                    'pno', 'ono', 'name', 'sell_prc', 'buy_ea', 'total_prc',
                    'option', 'option_prc', 'complex_no', 'stat',
                    'dlv_code', 'dlv_no', 'r_name', 'r_zip', 'r_addr1', 'r_addr2', 'r_cell',
                    'smartstore_ono'
                );
                $val = array(
                    $pno, $ono, $name, $sell_prc, $buy_ea, $total_prc,
                    $option, $option_prc, $complex_no, $stat,
                    $dlv_code, $dlv_no, $r_name, $r_zip, $r_addr1, $r_addr2, $r_cell,
                    $productOrderId
                );

                // 중복 체크
                $order_product_no = $oprd['no'];
                if ($order_product_no) {
                    // 쿼리 작성
                    $usql = '';
                    foreach ($key as $v) {
                        $usql .= ", `$v`=?";
                    }
                    $usql = substr($usql, 1);
                    array_push($val, $ono, $productOrderId);

                    // 쿼리 실행
                    $this->pdo->query("
                        update {$this->tbl['order_product']}
                            set $usql
                        where ono=? and smartstore_ono=?
                    ", $val);

                    // 주문서 실 결제금액 업데이트
                    $this->pdo->query("
                        update {$this->tbl['order']} set pay_prc=? where ono=?
                    ", array(
                        $pay_prc, $ono
                    ));
                } else {
                    // 입점사 수수료
                    if ($this->cfg->comp('use_partner_shop', 'Y')) {
                        $fee_prc = getPercentage($total_prc, $prd['partner_rate']);
                        array_push($key, 'partner_no', 'fee_rate', 'fee_prc', 'dlv_type');
                        array_push($val, $partner_no, $prd['partner_rate'], $fee_prc, $prd['dlv_type']);
                    }

                    // 쿼리 작성
                    $keys = $holder = '';
                    foreach ($key as $v) {
                        $keys .= ", `$v`";
                        $holder .= ", ?";
                    }
                    $keys = substr($keys, 1);
                    $holder = substr($holder, 1);
                    array_push($val, $productOrderId);

                    // 쿼리 실행
                    $this->pdo->query("
                        INSERT INTO {$this->tbl['order_product']} 
                            ($keys)
                            SELECT $holder FROM DUAL
                            WHERE NOT EXISTS 
                                (SELECT smartstore_ono FROM {$this->tbl['order_product']} WHERE smartstore_ono=?)
                    ", $val);

                    // 주문서 생성
                    $this->pdo->query("
                        insert into {$this->tbl['order']}
                            (
                                ono, date1, date2, date3, date4, date5, 
                                buyer_name, buyer_cell, buyer_phone, 
                                pay_type, total_prc, prd_prc, dlv_prc, pay_prc,
                                addressee_addr1, addressee_addr2, addressee_zip, addressee_name, addressee_cell, addressee_phone,
                                dlv_no, dlv_code, dlv_memo, mobile, 
                                smartstore 
                            ) 
                            SELECT
                                ?, ?, ?, ?, ?, ?, 
                                ?, ?, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                ?
                            FROM DUAL
                            WHERE NOT EXISTS
                                (SELECT ono FROM {$this->tbl['order']} WHERE ono=?)
                    ", array(
                        $ono, $date1, $date2, $date3, $date4, $date5,
                        $o_name, $o_cell, $o_cell,
                        $pay_type, 0, 0, $dlv_prc, $pay_prc,
                        $r_addr1, $r_addr2, $r_zip, $r_name, $r_cell, $r_cell,
                        $dlv_no, $dlv_code, $memo, $mobile, 'Y', $ono
                    ));
                    $insert_ono[] = $ono;

                    $oprd = $this->pdo->assoc("select no, stat from {$this->tbl['order_product']} where ono=? and smartstore_ono=?", array(
                        $ono, $productOrderId
                    ));
                    $oprd['stat'] = 0;
                }

                // 재고 처리
                ob_start();
                $err = orderStock($ono, $oprd['stat'], $stat, array($oprd['no']));
                if ($err) {
                    $this->pdo->query("update {$this->tbl['order_product']} set stat=20 where no=?", array($oprd['no']));
                }
                ob_end_clean();
                if ($err == 20 && $stat <= 2) $stat = 20;

                $changed[] = $ono;
            }
        }

        // 주문서 전체 계산
        $changed = array_unique($changed);
        foreach ($changed as $ono) {
            $sum = $this->pdo->assoc("
                select 
                    sum(if (stat < 10, sell_prc, 0)) as prd_prc,
                    sum(if (stat < 10, total_prc, 0)) as total_prc,
                    name as title,
                    count(*) as count
                from {$this->tbl['order_product']}
                where ono=?
            ", array(
                $ono
            ));
            $title = $sum['title'];
            if ($sum['count'] > 1) {
                $title .= ' 外 ' . ($sum['count'] - 1);
            }

            // 배송비
            $dlv_prc = array_sum($_dlv_prc[$ono]);
            foreach ($_dlv_prc[$ono] as $partner_no => $prc) {
                // 입점사 배송비 정산
                if ($partner_no > 0) {
                    $exists = $this->pdo->row(
                        "select no from {$this->tbl['order_dlv_prc']} where ono=? and partner_no=?",
                        array($ono, $partner_no)
                    );
                    if ($exists) {
                        $this->pdo->query(
                            "update {$this->tbl['order_dlv_prc']} set dlv_prc=? where ono=? and partner_no=?",
                            array($prc, $ono, $partner_no)
                        );
                    } else {
                        $this->pdo->query("
                            insert into {$this->tbl['order_dlv_prc']}
                                (ono, partner_no, dlv_prc, first_prc) values (?, ?, ?, ?)
                        ", array(
                            $ono, $partner_no, $prc, $prc
                        ));
                    }
                }
            }

            $this->pdo->query("
                update {$this->tbl['order']} set
                    title=?,
                    total_prc=?, prd_prc=?, dlv_prc=?
                where ono=?
            ", array(
                $title,
                ($sum['total_prc'] + $dlv_prc), $sum['prd_prc'], $dlv_prc,
                $ono
            ));

            // 주문 상태 변경 및 로그
            $ori_stat = $this->pdo->row("select stat from {$this->tbl['order']} where ono=?", array($ono));
            $stat = ordChgPart($ono, false);
            if ($ori_stat != $stat) {
                $GLOBALS['data'] = array(
                    'stat' => $ori_stat
                );
                ordStatLogw($ono, $stat, 'Y');   
            }

            // 외부 ERP 연동
            if (is_object($erpListener)) {
                $erpListener->setOrder($ono);
            }
        }

		// 상태변경 문자 및 메일 발송
		$insert_ono = array_unique($insert_ono);
		foreach ($insert_ono as $ono) {
            if (!$ono) continue;
			$ord = $this->pdo->assoc("select * from {$this->tbl['order']} where ono='$ono'");
			if (!$ord) continue;

			// 페이스북 픽셀 구매전환
			if ($this->cfg->comp('use_fb_npay', 'Y') == true && $this->cfg->comp('use_fb_conversion', 'Y') == true) {
				require_once __ENGINE_DIR__ . '/_engine/include/facebook.lib.php';
				fbPurchase($ord);
			}

			include_once __ENGINE_DIR__ . '/_engine/sms/sms_module.php';
			$sms_replace['ono'] = $ono;
			$sms_replace['title'] = $ord['title'];
			$sms_replace['buyer_name'] = stripslashes($ord['buyer_name']);
			$sms_replace['pay_prc'] = parsePrice($ord['pay_prc']);
			$sms_replace['pay_type'] = $_pay_type[$ord['pay_type']];
			$sms_replace['address'] = stripslashes($ord['addressee_addr1'] . ' ' . $ord['addressee_addr2']);
			if ($ord['pay_type'] == '4') {
				$sms_replace['account'] = '-';
			} elseif ($pay_type == 2) {
				$sms_replace['account'] = '-';
			} else {
				$sms_replace['account'] = '결제완료';
			}
			SMS_send_case(12);

			if (strchr($this->cfg->get('email_checked'), '@2') && strchr($this->cfg->get('email_checked'), '@0')) {
				$mail_case = 2;
				$admin_email = ($this->cfg->get('email_admin')) ? $this->cfg->get('email_admin') : $this->cfg->get('admin_email');

				include __ENGINE_DIR__ . '/_engine/include/mail.lib.php';
			}
		}

        return $changed;
    }

    /**
     * 발주 확인 처리
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return object
     */
    public function ordersConfirm($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/confirm',
            'POST',
            json_encode(array(
                'productOrderIds' => array($productOrderId)
            ))
        );
        return $ret;
    }

    /**
     * 발송 처리
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $deliveryCompanyCode 택배사 코드
     * @param string $trackingNumber 송장 번호
     * @return bool|object
     */
    public function ordersDispatch($productOrderId, $deliveryCompanyCode, $trackingNumber)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/dispatch',
            'POST',
            json_encode(array(
                'dispatchProductOrders' => array(
                    array(
                        'productOrderId' => $productOrderId,
                        'deliveryMethod' => 'DELIVERY',
                        'deliveryCompanyCode' => $this->__toDlvCode($deliveryCompanyCode),
                        'trackingNumber' => $trackingNumber,
                        'dispatchDate' => date('c')
                    )
                )
            ))
        );
        return $ret;
    }

    /**
     * 취소 요청 승인
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return bool|object
     */
    public function cancelApprove($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/cancel/approve',
            'POST'
        );
        return $ret;
    }

    /**
     * 취소 처리
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $cancelReason 취소 사유
     * @return bool|object
     */
    public function cancel($productOrderId, $cancelReason)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/cancel/request',
            'POST',
            json_encode(array('cancelReason' => $cancelReason))
        );
        return $ret;
    }

    /**
     * 반품 요청
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $returnReason 반품 요청 사유
     * @param string|null $collectDeliveryCompany 반품 택배사
     * @param string|null $collectTrackingNumber 반품 송장번호
     * @return object
     */
    public function returnRequest($productOrderId, $returnReason, $collectDeliveryCompany = '', $collectTrackingNumber = '')
    {
        $request = array(
            'returnReason' => $returnReason,
            'collectDeliveryMethod' => 'RETURN_INDIVIDUAL'
        );
        if ($collectDeliveryCompany) {
            $request['collectDeliveryCompany'] = $this->__toDlvCode($collectDeliveryCompany);
        }
        if ($collectTrackingNumber) {
            $request['collectTrackingNumber'] = $collectTrackingNumber;
        }

        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/request',
            'POST',
            json_encode($request)
        );
        return $ret;
    }

    /**
     * 반품 승인
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return object
     */
    public function returnApprove($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/approve',
            'POST'
        );
        return $ret;
    }

    /**
     * 반품 거부 사유
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $rejectReturnReason 반품 거부 사유
     * @return object
     */
    public function returnReject($productOrderId, $rejectReturnReason)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/reject',
            'POST',
            json_encode(array(
                'rejectReturnReason' => $rejectReturnReason
            ))
        );
        return $ret;
    }

    /**
     * 반품 보류
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $holdbackClassType 반품 보류 사유
     * @param string $holdbackReturnDetailReason 상세 사유
     * @param int|null $extraReturnFeeAmount 기타 반품 비용
     * @return object
     */
    public function returnHoldback($productOrderId, $holdbackClassType, $holdbackReturnDetailReason, $extraReturnFeeAmount = 0)
    {
        $request = array(
            'holdbackClassType' => $holdbackClassType,
            'holdbackReturnDetailReason' => $holdbackReturnDetailReason,

        );
        if ($extraReturnFeeAmount > 0) {
            $request['extraReturnFeeAmount'] = $extraReturnFeeAmount;
        }

        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/holdback',
            'POST',
            json_encode($request)
        );
        return $ret;
    }

    /**
     * 반품보류 해제
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return bool|object
     */
    public function returnHoldbackRelease($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/holdback/release',
            'POST'
        );
        return $ret;
    }

    /**
     * 교환 수거 완료
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return bool|object
     */
    public function exchangeCollectApprove($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/collect/approve',
            'POST'
        );
        return $ret;

    }

    /**
     * 교환 재배송
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string|null $reDeliveryCompany 재배송 택배사 코드
     * @param string|null $reDeliveryTrackingNumber 재배송 송장번호
     * @return object
     */
    public function exchangeDispatch($productOrderId, $reDeliveryCompany, $reDeliveryTrackingNumber)
    {
        $request = array(
            'reDeliveryMethod' => 'RETURN_INDIVIDUAL'
        );
        if ($reDeliveryCompany) {
            $request['reDeliveryCompany'] = $this->__toDlvCode($reDeliveryCompany);
        }
        if ($reDeliveryTrackingNumber) {
            $request['reDeliveryTrackingNumber'] = $reDeliveryTrackingNumber;
        }
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/dispatch',
            'POST',
            json_encode($request)
        );
        return $ret;
    }

    /**
     * 교환 거부 (철회)
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $rejectExchangeReason 겨환 거부 사유
     * @return bool|object
     */
    public function exchangeReject($productOrderId, $rejectExchangeReason)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/reject',
            'POST',
            json_encode(array(
                'rejectExchangeReason' => $rejectExchangeReason
            ))
        );
        return $ret;
    }

    /**
     * 교환 보류
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string $holdbackClassType 교환 보류 사유
     * @param string $holdbackExchangeDetailReason 상세 사유
     * @param int|null $extraExchangeFeeAmount 기타 반품 비용
     * @return object
     */
    public function exchangeHoldback($productOrderId, $holdbackClassType, $holdbackExchangeDetailReason, $extraExchangeFeeAmount = 0)
    {
        $request = array(
            'holdbackClassType' => $holdbackClassType,
            'holdbackExchangeDetailReason' => $holdbackExchangeDetailReason,

        );
        if ($extraExchangeFeeAmount > 0) {
            $request['extraExchangeFeeAmount'] = $extraExchangeFeeAmount;
        }

        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/holdback',
            'POST',
            json_encode($request)
        );
        return $ret;
    }

    /**
     * 교환보류 해제
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @return object
     */
    public function exchangeHoldbackRelease($productOrderId)
    {
        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId .  '/claim/exchange/holdback/release',
            'POST'
        );
        return $ret;
    }

    /**
     * 발송 지연
     * @param string $productOrderId 스마트스토어 상품 주문 번호
     * @param string|null $dispatchDueDate 발송 기한 (KST)
     * @param string|null $delayedDispatchReason 발송 지연 사유 코드
     * @param string|null $dispatchDelayedDetailedReason 발송 지연 상세 사유
     * @return object
     */
    public function orderDelay($productOrderId, $dispatchDueDate, $delayedDispatchReason, $dispatchDelayedDetailedReason)
    {
        $request = array();
        if ($dispatchDueDate) {
            $request['dispatchDueDate'] = date('c', strtotime($dispatchDueDate));
        }
        if ($delayedDispatchReason) {
            $request['delayedDispatchReason'] = $delayedDispatchReason;
        }
        if ($dispatchDelayedDetailedReason) {
            $request['dispatchDelayedDetailedReason'] = $dispatchDelayedDetailedReason;
        }

        $ret = $this->api(
            '/v1/pay-order/seller/product-orders/' . $productOrderId . '/delay',
            'POST',
            json_encode($request)
        );
        return $ret;
    }

    /**
     * 스마트스토어 상품고시 정보
     */
    public static function getProductSummeryTypes()
    {
        $summeryList = comm('http://smapi.wisa.ne.kr/ProductSummery.json');
        return json_decode($summeryList);
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 클레임 코드를 한글명으로 변경
     * @param string $code 클레임 코드
     * @return string
     */
    public function claimTypeName($code)
    {
        $variable = $this->vClaimType();
        return $variable[$code];
    }

    /**
     * 클레임 사유 출력
     * @param object $data 주문 상품 정보
     * @return string
     */
    public function claimReason($data)
    {
        return $this->{strtolower($data->productOrder->claimType) . 'ClaimReason'}($data);
    }

    /**
     * 취소 클레임 사유 출력
     * @param object $data 주문 상품 정보
     * @return string
     */
    public function cancelClaimReason($data)
    {
        $variable = $this->vCancelReason();
        return $variable[$data->cancel->cancelReason];
    }

    /**
     * 반품 클레임 사유 출력
     * @param object $data 주문 상품 정보
     * @return string|null
     */
    public function returnClaimReason($data)
    {
        $variable = $this->vReturnReason();
        return $variable[$data->return->returnReason];
    }

    /**
     * 교환 클레임 사유 출력
     * @param object $data 주문 상품 정보
     * @return string|null
     */
    public function exchangeClaimReason($data)
    {
        $variable = $this->vExchangeReason();
        return $variable[$data->return->returnReason];
    }

    /**
     * 발송 지연 사유 출력
     * @param object $data 주문 상품 정보
     * @return string|null
     */
    public function delayedDispatchReason($data)
    {
        $variable = $this->vdelayedDispatchReason();
        return $variable[$data->productOrder->delayedDispatchReason];
    }

    /**
     * 스마트윙의 상품 상태 코드를 커머스API 상품 상태 코드로 변환
     * @param string $stat 스마트윙 상품 상태 코드
     * @return string
     */
    private function __toStat($stat)
    {
        switch ($stat) {
            case '2' :
                return 'SALE' ;
            case '3' :
                return 'SUSPENSION';
            case '4' :
                return 'SUSPENSION';
            default :
                return '';
        }
    }

    /**
     * 주문 상태 변환
     * @param $data
     * @return int
     */
    public function __orderStat($data)
    {
        switch ($data->productOrderStatus) {
            case 'PAYMENT_WAITING' :
                $stat = '1';
                break;
            case 'PAYED' :
                $stat = '2';
                if ($data->placeOrderStatus == 'OK') {
                    $stat = '3';
                }
                break;
            case 'DELIVERING' :
                $stat = '4';
                break;
            case 'DELIVERED' :
            case 'PURCHASE_DECIDED' :
                $stat = '5';
                break;
        }
        if($data->claimType == 'CANCEL') {
            switch ($data->claimStatus) {
                case 'CANCEL_REQUEST' :
                case 'CANCELING' :
                    $stat = 12;
                    break;
                case 'CANCEL_DONE' : $stat = 13; break;
            }
        }
        else if($data->claimType == 'RETURN') {
            switch ($data->claimStatus) {
                case 'RETURN_REQUEST' : $stat = 16; break;      // 반품요청
                case 'COLLECTING' : $stat = 22; break;          // 수거처리중
                case 'COLLECT_DONE' : $stat = 23; break;        // 수거완료
                case 'RETURN_DONE' : $stat = 17; break;         // 반품완료
            }
        }
        else if($data->claimType == 'EXCHANGE') {
            switch ($data->claimStatus) {
                case 'EXCHANGE_REQUEST' : $stat = 18; break;
                case 'COLLECTING' : $stat = 24; break;          // 수거처리중
                case 'COLLECT_DONE' : $stat = 25; break;        // 수거완료
                case 'EXCHANGE_REDELIVERING' : $stat = 4; break;// 교환재배송중
                case 'EXCHANGE_DONE' : $stat = 5; break;        // 교환완료
                case 'EXCHANGE_REJECT' : break;                 // 교환철회
            }
        }
        else if($data->claimType == 'ADMIN_CANCEL') {
            switch ($data->claimStatus) {
                case 'ADMIN_CANCELING' : $stat = 12; break;
                case 'ADMIN_CANCEL_DONE' : $stat = 13; break;
            }
        }
        return (int) $stat;
    }

    /**
     * 결제방식 변환
     * @param string $paymentMeans 네이버 결제 방식 코드
     * @return int|string
     */
    private function __orderPayType($paymentMeans) {
        $paymentMeans = str_replace(' 간편결제', '', $paymentMeans);
        switch ($paymentMeans) {
            case '신용카드' : $pay_type = 1; break;
            case '무통장입금' : $pay_type = 2; break;
            case '실시간계좌이체' : $pay_type = 5; break;
            case '계좌' : $pay_type = 5; break;
            case '휴대폰결제' : $pay_type = 7; break;
            case '휴대폰' : $pay_type = 7; break;
            case '포인트결제' : $pay_type = 3; break;
            case '네이버 캐쉬' : $pay_type = 'C'; break;
            case '나중에결제' : $pay_type = 24; break;
            case '후불결제' : $pay_type = 26; break;
        }
        return $pay_type;
    }

    private function __toDlvCode($deliveryNo)
    {
        $deliveryName = $this->pdo->row(
            "select name from {$this->tbl['delivery_url']} where no=?",
            array($deliveryNo)
        );
        switch ($deliveryName) {
            case 'CJ대한통운' : $code = 'CJGLS'; break;
            case '대한통운' : $code = 'CJGLS'; break;
            case 'CJGLS' : $code = 'CJGLS'; break;
            case 'SC로지스': $code = 'SAGAWA'; break;
            case '옐로우캡 택배' : $code = 'YELLOW'; break;
            case '로젠' : $code = 'KGB'; break;
            case '동부익스프레스' : $code = 'DONGBU'; break;
            case 'KG로지스' : $code = 'DONGBU'; break;
            case '드림택배' : $code = 'DONGBU'; break;
            case '우체국택배' : $code = 'EPOST'; break;
            case '한진택배' : $code = 'HANJIN'; break;
            case '현대택배' : $code = 'HYUNDAI'; break;
            case '롯데택배' : $code = 'HYUNDAI'; break;
            case 'KGB택배' : $code = 'KGBLS'; break;
            case '하나로로지스' : $code = 'HANARO'; break;
            case '경동택배' : $code = 'KDEXP'; break;
            case '천일택배' : $code = 'CHUNIL'; break;
            case '대신택배' : $code = 'DAESIN'; break;
            case '편의점택배' : $code = 'CVSNET'; break;
            case '롯데글로벌로지스' : $code = 'HLCGLOBAL'; break;
            case '건영택배' : $code = 'KUNYOUNG'; break;
            case 'CU 편의점택배' : $code = 'CUPARCEL'; break;
            default : $code = 'CH1'; break;
        }

        return $code;
    }

    /**
     * 택배사 코드 반환
     * @param string $deliveryCompany 네이버 택배사 코드
     * @return string
     */
    private function __toDlvNo($deliveryCompany)
    {
        switch($deliveryCompany) {
            case 'CJGLS' :
                $dlv_name = array('CJGLS', 'CJ대한통운');
                break;
            case 'SAGAWA':
                $dlv_name = array('SC로지스');
                break;
            case 'YELLOW' :
                $dlv_name = array('옐로우캡 택배');
                break;
            case 'KGB' :
                $dlv_name = array('로젠');
                break;
            case 'DONGBU' :
                $dlv_name = array('동부익스프레스', 'KG로지스', '드림택배');
                break;
            case 'EPOST' :
                $dlv_name = array('우체국택배');
                break;
            case 'HANJIN' :
                $dlv_name = array('한진택배');
                break;
            case 'HYUNDAI' :
                $dlv_name = array('롯데택배');
                break;
            case 'KGBLS' :
                $dlv_name = array('KGB 택배');
                break;
            case 'HANARO' :
                $dlv_name = array('하나로 로지스');
                break;
            case 'KDEXP' :
                $dlv_name = array('경동택배');
                break;
            case 'CHUNIL' :
                $dlv_name = array('천일택배');
                break;
            case 'CVSNET' :
                $dlv_name = array('편의점택배');
                break;
            case 'HLCGLOBAL' :
                $dlv_name = array('롯데글로벌로지스');
                break;
            case 'KUNYOUNG' :
                $dlv_name = array('건영택배');
                break;
            case 'CUPARCEL' :
                $dlv_name = array('CU 편의점택배');
                break;
        }

        if (is_array($dlv_name)) {
            $dlv_names = '';
            foreach($dlv_name as $key => $val) {
                $dlv_names .= ",'$val'";
            }
            $dlv_names = substr($dlv_names, 1);
            $dlv_no = $this->pdo->row("
                select no from {$this->tbl['delivery_url']} where 
                       replace(name, ' ', '') in ($dlv_names) order by no desc limit 1
           ");
        }

        return $dlv_no;
    }

    /**
     * 인증 토큰 발급 요청
     * @return bool
     */
    private function token()
    {
        // 타임스탬프. 네이버 시계가 느린 경우가 있을 수 있어 과거로 보정
        $this->timestamp = (int) (microtime(true) * 1000) - 10000;

        try {
            $result = $this->api('/v1/oauth2/token', 'POST', array(
                'client_id' => $this->id,
                'timestamp' => $this->timestamp,
                'client_secret_sign' => $this->signature(),
                'grant_type' => 'client_credentials',
                'type' => 'SELF'
            ), '');
            if ($result->access_token) {
                $this->access_token = $result->access_token;
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * 전자 서명 생성
     * @return string
     */
    private function signature()
    {
        $hashed = crypt($this->id . '_' . $this->timestamp, $this->secret);
        return base64_encode($hashed);
    }

    /**
     * 커머스 API 통신
     * @param string $url 주소
     * @param string $method 메소드 타입
     * @param array $body POST body
     * @return object|bool
     */
    private function api($url, $method, $body = null, $contentType = 'application/json')
    {
        $this->setError('');

        if ($url != '/v1/oauth2/token' && !$this->access_token) {
            $this->error = '스마트스토어 인증을 진행해주세요.';
            return false;
        }

        // 헤더
        $headers = array();
        if ($contentType) {
            array_push(
                $headers,
                'content-type: ' . $contentType
            );
        }
        if ($this->access_token) {
            array_push(
                $headers,
                'Authorization: Bearer ' . $this->access_token
            );
        }

        // API 요청
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->host . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        $result = json_decode($result);

        if ($info['http_code'] != 200) {
            if ($result->message) {
                $this->setError($result->message);
            } else {
                $this->setError($info['http_code'] . 'error');
                return false;
            }
        }

        return $result;
    }

}