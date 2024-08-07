<?php
/**
 * 크리마 API 연동 클래스
 * @박연경 <pyk87@wisa.co.kr>
 * @date 2016-01-20
 */
class cremaAPI extends restAPI{
	private $tbl, $cfg;
	private $matching_tbl = "crema_matching";
	private $oAuth;
	public  $responseArr;
    private $pdo;

	function __construct() {
		global $tbl, $cfg;

		$this->tbl = array(
            'category' => $tbl['category'],
            'member' => $tbl['member'],
            'member_group' => $tbl['member_group'],
            'order' => $tbl['order'],
            'order_product' => $tbl['order_product'],
            'delivery_url' => $tbl['delivery_url']
        );

		$this->cfg = array(
            'crema_app_id' => $cfg['crema_app_id'],
            'crema_secret' => $cfg['crema_secret'],
        );

		$this->file_url = getFileDir('_data/product');
        $this->pdo = $GLOBALS['pdo'];

		$this->url = "https://api.cre.ma";
		$this->httpHeader =  array("Accept : application/x-www-form-urlencoded;charset=UTF-8");
		//$this->debugMode = 2;
		if(!$cfg['crema_image_no']) {
			$cfg['crema_image_no'] = 2;
		}

		$this->getOAuth();
	}

	// 인증키 발급
	function getOAuth() {
		$this->method = "POST";
		$this->resource = "/oauth/token";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['grant_type']	= "client_credentials";
		$this->requestData['client_id']		= $this->cfg['crema_app_id'];
		$this->requestData['client_secret']	= $this->cfg['crema_secret'];

		$this->execute();

		$jdata = json_decode($this->responseBody);
		$this->oAuth = $jdata->access_token;

	}

	// 카테고리 생성
	function createCategory($cno) {

		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$cno."' AND type = 'c'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateCategory($cno);
			return;
		}

		$sql = "SELECT * FROM ".$this->tbl['category']." WHERE no = '".$cno."'";
		$cate = $this->pdo->assoc($sql);

		if($cate['level']=='1') {
			$paretn_id = '';
		}else if($cate['level']=='2') {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cate['big']."' AND type = 'c'";
			$paretn_id = $this->pdo->row($sql);
			$paretn_id = ($paretn_id ? $paretn_id : '');
		}else if($cate['level']=='3') {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cate['mid']."' AND type = 'c'";
			$paretn_id = $this->pdo->row($sql);
			$paretn_id = ($paretn_id ? $paretn_id : '');
		}

		$this->method = "POST";
		$this->resource = "/v1/categories";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['code']					= $cate['no'];
		$this->requestData['name']					= strip_tags($cate['name']);
		if($paretn_id) {
			$this->requestData['parent_category_id']	= $paretn_id;
		}

		$this->exe();

		$this->exeMatchingDB("c", "INSERT", $cno);
	}

	// 카테고리 수정
	function updateCategory($cno) {
		$sql = "SELECT * FROM ".$this->tbl['category']." WHERE no = '".$cno."'";
		$cate = $this->pdo->assoc($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cno."' AND type = 'c'";
		$id = $this->pdo->row($sql);

		if($cate['level']=='1') {
			$paretn_id = '';
		}else if($cate['level']=='2') {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cate['big']."' AND type = 'c'";
			$paretn_id = $this->pdo->row($sql);
			$paretn_id = ($paretn_id ? $paretn_id : '');
		}else if($cate['level']=='3') {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cate['mid']."' AND type = 'c'";
			$paretn_id = $this->pdo->row($sql);
			$paretn_id = ($paretn_id ? $paretn_id : '');
		}

		$this->method = "PUT";
		$this->resource = "/v1/categories/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['code']					= $cate['no'];
		$this->requestData['name']					= strip_tags($cate['name']);
		if($paretn_id) {
			$this->requestData['parent_category_id']	= $paretn_id;
		}

		$this->exe();
	}

	// 카테고리 정보 가져오기
	function getCategory($cno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cno."' AND type = 'c'";
		$id = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/categories/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['limit']		    = '100';
		$this->requestData['page']					= 1;

		$this->exe();
	}

	// 카테고리 정보 삭제
	function deleteCategory($cno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$cno."' AND type = 'c'";
		$id = $this->pdo->row($sql);

		$this->method = "DELETE";
		$this->resource = "/v1/categories/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();

		$this->exeMatchingDB("c", "DELETE", $cno);
	}

	// 회원등급 정보 가져오기
	function getGrade($gno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$gno."' AND type = 'g'";
		$id = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/user_grades/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['limit']		    = '100';
		$this->requestData['page']			= 1;
		$this->requestData['id']			= $gno;

		$this->exe();
	}

	// 회원등급 생성
	function createGrade($gno) {

		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$gno."' AND type = 'g'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateGrade($gno);
			return;
		}

		$sql = "SELECT * FROM ".$this->tbl['member_group']." WHERE no = '".$gno."'";
		$grade = $this->pdo->assoc($sql);

		$this->method = "POST";
		$this->resource = "/v1/user_grades";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['name']					= strip_tags($grade['name']);
		$this->categoryData							= "";
		$this->optionData							= "";

		$this->exe();

		$this->exeMatchingDB("g", "INSERT", $gno);
	}

	// 회원등급 수정
	function updateGrade($gno) {
		$sql = "SELECT * FROM ".$this->tbl['member_group']." WHERE no = '".$gno."'";
		$grade = $this->pdo->assoc($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$gno."' AND type = 'g'";
		$id = $this->pdo->row($sql);

		$this->method = "PUT";
		$this->resource = "/v1/user_grades/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['name']					= strip_tags($grade['name']);
		$this->categoryData							= "";
		$this->optionData							= "";

		$this->exe();
	}

	// 회원등급 정보 삭제
	function deleteGrade($gno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$gno."' AND type = 'g'";
		$id = $this->pdo->row($sql);

		$this->method = "DELETE";
		$this->resource = "/v1/user_grades/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();

		$this->exeMatchingDB("g", "DELETE", $gno);
	}

	// 상품 생성
	function createProduct($no) {
		global $cfg;

		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$no."' AND type = 'p'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateProduct($no);
			return;
		}

		$sql = "SELECT * FROM wm_product WHERE no = '".$no."' AND wm_sc=0";
		$prd = $this->pdo->assoc($sql);

		$tmp_cate = array();
		if($prd['big']>0) $tmp_cate[] = $prd['big'];
		if($prd['mid']>0) $tmp_cate[] = $prd['mid'];
		if($prd['small']>0) $tmp_cate[] = $prd['small'];
		if($prd['obig']>0) $tmp_cate[] = $prd['obig'];
		if($prd['omid']>0) $tmp_cate[] = $prd['omid'];
		if($prd['xbig']>0) $tmp_cate[] = $prd['xbig'];
		if($prd['xmid']>0) $tmp_cate[] = $prd['xmid'];
		if($prd['xsmall']>0) $tmp_cate[] = $prd['xsmall'];
		if($prd['ybig']>0) $tmp_cate[] = $prd['ybig'];
		if($prd['ymid']>0) $tmp_cate[] = $prd['ymid'];
		if($prd['ysmall']>0) $tmp_cate[] = $prd['ysmall'];

		if($prd['ebig']) {
			$ebig = explode("@",$prd['ebig']);
			foreach($ebig as $key=>$val) {
				if($val) {
					$tmp_cate[] = $val;
				}
			}
		}

		$_cate = array();
		for($i=0;$i<=count($tmp_cate);$i++){
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$tmp_cate[$i]."' AND type = 'c'";
			$matcing_cate_id = $this->pdo->row($sql);
			if($matcing_cate_id) $_cate[] = $matcing_cate_id;
		}
		$_cate = implode("&category_ids[]=", $_cate);

		//이미지
		if($prd['upfile'.$cfg['crema_image_no']]) {
			$img=getListImgURL($prd['updir'], $prd['upfile'.$cfg['crema_image_no']]);
			$prd['img_url']=$img;
		}else {
			$prd['img_url']="";
		}

		$stock_count = ($prd['stat']==3) ? 0 : 1;

		$this->method = "POST";
		$this->resource = "/v1/products";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']		= $this->oAuth;
		$this->requestData['code']				= $no;
		$this->requestData['name']				= strip_tags($prd['name']);
		$this->requestData['url']				= $GLOBALS['root_url']."/shop/detail.php?pno=".$prd['hash'];
		$this->requestData['org_price']			= parsePrice($prd['normal_prc']);
		$this->requestData['final_price']		= parsePrice($prd['sell_prc']);
		$this->categoryData						= $_cate;
		$this->requestData['display']			= (in_array($prd['stat'], array(2, 3)) ? 1 : 0);
		if($prd['img_url']) {
			$this->requestData['image_url']			= $prd['img_url'];
		}
		$this->requestData['stock_count']		= $stock_count;
        $this->requestData['product_options']   = $this->getProductOptions($prd['no']);

		$this->exe();

		$this->exeMatchingDB("p", "INSERT", $no);
	}

	// 상품 수정
	function updateProduct($no) {
		global $cfg;

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$no."' AND type = 'p'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT * FROM wm_product WHERE no = '".$no."' AND wm_sc=0";
		$prd = $this->pdo->assoc($sql);

		$tmp_cate = array();
		if($prd['big']>0) $tmp_cate[] = $prd['big'];
		if($prd['mid']>0) $tmp_cate[] = $prd['mid'];
		if($prd['small']>0) $tmp_cate[] = $prd['small'];
		if($prd['obig']>0) $tmp_cate[] = $prd['obig'];
		if($prd['omid']>0) $tmp_cate[] = $prd['omid'];
		if($prd['xbig']>0) $tmp_cate[] = $prd['xbig'];
		if($prd['xmid']>0) $tmp_cate[] = $prd['xmid'];
		if($prd['xsmall']>0) $tmp_cate[] = $prd['xsmall'];
		if($prd['ybig']>0) $tmp_cate[] = $prd['ybig'];
		if($prd['ymid']>0) $tmp_cate[] = $prd['ymid'];
		if($prd['ysmall']>0) $tmp_cate[] = $prd['ysmall'];

		if($prd['ebig']) {
			$ebig = explode("@",$prd['ebig']);
			foreach($ebig as $key=>$val) {
				if($val) {
					$tmp_cate[] = $val;
				}
			}
		}

		$_cate = array();
		for($i=0;$i<=count($tmp_cate);$i++){
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$tmp_cate[$i]."' AND type = 'c'";
			$matcing_cate_id = $this->pdo->row($sql);
			if($matcing_cate_id) $_cate[] = $matcing_cate_id;
		}
		$_cate = implode("&category_ids[]=", $_cate);

		//이미지
		if($prd['upfile'.$cfg['crema_image_no']]) {
			$img=getListImgURL($prd['updir'], $prd['upfile'.$cfg['crema_image_no']]);
			$prd['img_url']=$img;
		}else {
			$prd['img_url']="";
		}

		$stock_count = ($prd['stat']==3) ? 0 : 1;

		$this->method = "PUT";
		$this->resource = "/v1/products/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']		= $this->oAuth;
		$this->requestData['code']				= $no;
		$this->requestData['name']				= strip_tags($prd['name']);
		$this->requestData['url']				= $GLOBALS['root_url']."/shop/detail.php?pno=".$prd['hash'];
		$this->requestData['org_price']			= parsePrice($prd['normal_prc']);
		$this->requestData['final_price']		= parsePrice($prd['sell_prc']);
		$this->categoryData						= $_cate;
		$this->requestData['display']			= (in_array($prd['stat'], array(2, 3)) ? 1 : 0);
		if($prd['img_url']) {
			$this->requestData['image_url']			= $prd['img_url'];
		}
		$this->requestData['stock_count']		= $stock_count;
        $this->requestData['product_options']   = $this->getProductOptions($prd['no']);

		$this->exe();

	}

    private function getProductOptions($pno)
    {
        global $tbl;

        $options = array();
        $res = $this->pdo->iterator("select no, name from {$tbl['product_option_set']} where pno='$pno' and necessary in ('Y','N') order by sort asc");
        foreach ($res as $oset) {
            $values = array();
            $res2 = $this->pdo->iterator("select iname from {$tbl['product_option_item']} where opno='{$oset['no']}' order by sort asc");
            foreach ($res2 as $oitem) {
                $values[] = stripslashes($oitem['iname']);
            }
            $options[] = array(
                'name' => stripslashes($oset['name']),
                'values' => $values
            );
        }
        return $options;
    }

	// 상품 정보 가져오기
	function getProduct($code) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$code."' AND type = 'p'";
		$id = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/products/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']			= $this->oAuth;

		$this->exe();
	}

	// 상품 리스트 가져오기
	function getProductList($limit=30,$page=1,$start_date,$end_date,$date_type) {
		$this->method = "GET";
		$this->resource = "/v1/products/";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['limit']					= $limit;
		$this->requestData['page']					= $page;
		$this->requestData['start_date']			= $start_date;
		$this->requestData['end_date']				= $end_date;
		$this->requestData['date_type']				= $date_type;
		$this->exe();
	}

	// 상품 정보 삭제
	function deleteProduct($code) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$code."' AND type = 'p'";
		$id = $this->pdo->row($sql);

		$this->method = "DELETE";
		$this->resource = "/v1/products/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();

		$this->exeMatchingDB("p", "DELETE", $code);
	}

	// 주문 생성
	function createOrder($ono) {
		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$ono."' AND type = 'o'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateOrder($ono);
			return;
		}

		$sql = "SELECT ono, date1, date2, total_prc, member_id, buyer_name, buyer_cell, buyer_email, mobile FROM ".$this->tbl['order']." WHERE ono = '".$ono."'";
		$ord = $this->pdo->assoc($sql);

		$sql = "SELECT level FROM ".$this->tbl['member']." where member_id='$ord[member_id]'";
		$member_level = $this->pdo->row($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$member_level."' AND type = 'g'";
		$member_glevel = $this->pdo->row($sql);

		$reg_date = ($ord['date2']>0) ? $ord['date2']:$ord['date1'];

		$this->method = "POST";
		$this->resource = "/v1/orders";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->categoryData					= "";
		$this->optionData					= "";
		$this->reviewImgData							= "";
		$this->requestData['code']			= $ord['ono'];
		$this->requestData['created_at']	= date("Y-m-d H:i:s", $reg_date);
		$this->requestData['total_price']	= parsePrice($ord['total_prc']);
		$this->requestData['user_code']		= $ord['member_id'];
		if($member_glevel) $this->requestData['user_grade_id']	= $member_glevel;
		$this->requestData['user_name']		= $ord['buyer_name'];
		$this->requestData['user_phone']	= $ord['buyer_cell'];
		$this->requestData['user_email']	= $ord['buyer_email'];
		$this->requestData['order_device']	= ($ord['mobile']=='Y')?"mobile":"pc";

		$this->exe();

		$this->exeMatchingDB("o", "INSERT", $ono);
	}

	// 주문 수정
	function updateOrder($ono) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$ono."' AND type = 'o'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT ono, date1, date2, total_prc, member_id, buyer_name, buyer_cell, buyer_email, mobile FROM ".$this->tbl['order']." WHERE ono = '".$ono."'";
		$ord = $this->pdo->assoc($sql);

		$sql = "SELECT level FROM ".$this->tbl['member']." where member_id='$ord[member_id]'";
		$member_level = $this->pdo->row($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$member_level."' AND type = 'g'";
		$member_glevel = $this->pdo->row($sql);

		$reg_date = ($ord['date2']>0) ? $ord['date2']:$ord['date1'];

		$this->method = "PUT";
		$this->resource = "/v1/orders/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->categoryData					= "";
		$this->optionData					= "";
		$this->reviewImgData							= "";
		$this->requestData['code']			= $ord['ono'];
		$this->requestData['created_at']	= date("Y-m-d H:i:s", $reg_date);
		$this->requestData['total_price']	= parsePrice($ord['total_prc']);
		$this->requestData['user_code']		= $ord['member_id'];
		if($member_glevel) $this->requestData['user_grade_id']	= $member_glevel;
		$this->requestData['user_name']		= $ord['buyer_name'];
		$this->requestData['user_phone']	= $ord['buyer_cell'];
		$this->requestData['user_email']	= $ord['buyer_email'];
		$this->requestData['order_device']	= ($ord['mobile']=='Y')?"mobile":"pc";

		$this->exe();
	}

	// 주문 리스트 가져오기
	function getOrderList($limit=30,$page=1,$start_date,$end_date,$date_type) {
		$this->method = "GET";
		$this->resource = "/v1/orders/";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['limit']					= $limit;
		$this->requestData['page']					= $page;
		$this->requestData['start_date']			= $start_date;
		$this->requestData['end_date']				= $end_date;
		$this->requestData['date_type']				= $date_type;
		$this->exe();
	}

	// 주문 정보 가져오기
	function getOrder($ono) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$ono."' AND type = 'o'";
		$id = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/orders/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;
		$this->exe();
	}

	// 주문 삭제
	function deleteOrder($ono) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$ono."' AND type = 'o'";
		$id = $this->pdo->row($sql);

		$this->method = "DELETE";
		$this->resource = "/v1/orders/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();

		$this->exeMatchingDB("o", "DELETE", $ono);
	}

	// 주문 상품 생성
	function createOrderProduct($opno) {
		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$opno."' AND type = 'op'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateOrderProduct($opno);
			return;
		}

		$sql = "SELECT no, ono, pno, sell_prc, buy_ea, `option`, stat, dlv_code, dlv_no FROM ".$this->tbl['order_product']." WHERE no = '".$opno."'";
		$op = $this->pdo->assoc($sql);

		$sql = "SELECT date4, date5 FROM ".$this->tbl['order']." WHERE ono = '".$op['ono']."'";
		$ord = $this->pdo->assoc($sql);

		$dlv = $this->pdo->assoc("SELECT name FROM ".$this->tbl['delivery_url']." WHERE no = '".$op['dlv_no']."'");

		// 크리마 플래그와 V4 set.common.php 택배사와 매칭 (없는것은 제외)
		/*
		hyundai_logistics|cj_gls|korea_express|dhl|
		epost|logen|dongbu_express|ilyang_logis|
		ems|ups|fedex|hanjin|
		kgb_logis|twenty_four_quick|hanjin_b2b|kyoungdong_express|
		usps|innogis|daesin_parcel_service|gtx
		*/

		if($dlv['name']) {
			switch($dlv['name']) {
				case '대한통운' : $d_code = 'korea_express'; break;
				case 'CJ대한통운' : $d_code = 'korea_express'; break;
				case 'CJ gls' : $d_code = 'cj_gls'; break;
				case 'CJGLS' : $d_code = 'cj_gls'; break;
				case '로젠' : $d_code = 'logen'; break;
				case '동부 익스프레스' : $d_code = 'dongbu_express'; break;
				case 'KG로지스' : $d_code = 'dongbu_express'; break;
				case '우체국택배' : $d_code = 'epost'; break;
				case '한진택배' : $d_code = 'hanjin'; break;
				case '현대택배' : $d_code = 'hyundai_logistics'; break;
				case '롯데택배' : $d_code = 'hyundai_logistics'; break;
				case 'KGB 택배' : $d_code = 'kgb_logis'; break;
				case '드림택배' : $d_code = 'dream_logis'; break;
			}
		}

		$op['delivery_service'] = $d_code;

		$_option_str = "";
		if($op['option']) {
			$op['option']=str_replace("/","",$op['option']);
			$op['option']=str_replace("&","",$op['option']);
			if(strpos($op['option'], "<split_small>")==true) {
				$_option_str=str_replace("<split_big>","/",$op['option']);
				$_option_str=str_replace("<split_small>",":",$_option_str);
			}else {
				$_option_str=str_replace("<split_big>",":",$op['option']);
			}
		}

		$product_options = "";
		//옵션이 한개인 경우
		if(strpos($_option_str, "/")==false) {
			$product_options = $_option_str;
		}else {//옵션이 한개이상인 경우
			$_option = explode("/", $_option_str);

			foreach($_option as $key=>$val) {
				if($key>0) $product_options .="&product_options[]=";
				$product_options .= $val;
			}
		}

		$product_options = str_replace('%', '', $product_options);

		//$op['product_options'] = implode("&product_options[]=", $op['product_options']);

		switch($op['stat']) {
			case "1" :
				$op['status'] = "not_paid";
				break;
			case "2" :
				$op['status'] = "paid";
				break;
			case "3" :
				$op['status'] = "delivery_preparing";
				break;
			case "4" :
				$op['status'] = "delivery_started";
				break;
			case "5" :
				$op['status'] = "delivery_finished";
				break;
			case "13" :
				$op['status'] = "returned";
				break;
			case "15" :
				$op['status'] = "returned";
				break;
			case "17" :
				$op['status'] = "returned";
				break;
			default :
				$op['status'] = "paid";
				break;
		}

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$op['ono']."' AND type = 'o'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$op['pno']."' AND type = 'p'";
		$product_id = $this->pdo->row($sql);

		if($ord['date4']) {
			$delivery_started_at = date("Y-m-d H:i:s", $ord['date4']);
		}else {
			$delivery_started_at = 0;
		}
		if($ord['date5'] && $op['stat']==5) {
			$delivered_at = date("Y-m-d H:i:s", $ord['date5']);
		}else {
			$delivered_at = 0;
		}

		$this->method = "POST";
		$this->resource = "/v1/orders/".$id."/sub_orders";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['product_id']			= $product_id;
		$this->requestData['price']					= parsePrice($op['sell_prc']);
		$this->requestData['product_count']			= $op['buy_ea'];
		$this->requestData['code']		        	= $op['no'];
		$this->categoryData							= "";
		$this->optionData							= $product_options;
		$this->reviewImgData							= "";
		$this->requestData['status']				= $op['status'];
		if($op['stat'] == 4 || $op['stat'] == 5) {
			$this->requestData['delivery_started_at']	= $delivery_started_at;
			$this->requestData['delivered_at']			= $delivered_at;
			$this->requestData['invoice']				= $op['dlv_code'];
			$this->requestData['delivery_service']		= $op['delivery_service'];
		}

		$this->exe();

		$this->exeMatchingDB("op", "INSERT", $op['no']);
	}

	// 주문 상품 수정
	function updateOrderProduct($opno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$opno."' AND type = 'op'";
		$opid = $this->pdo->row($sql);

		$sql = "SELECT no, ono, pno, sell_prc, buy_ea, `option`, stat, dlv_code, dlv_no FROM ".$this->tbl['order_product']." WHERE no = '".$opno."'";
		$op = $this->pdo->assoc($sql);

		$sql = "SELECT date4, date5 FROM ".$this->tbl['order']." WHERE ono = '".$op['ono']."'";
		$ord = $this->pdo->assoc($sql);

		$dlv = $this->pdo->assoc("SELECT name FROM ".$this->tbl['delivery_url']." WHERE no = '".$op['dlv_no']."'");

		if($dlv['name']) {
			switch($dlv['name']) {
				case '대한통운' : $d_code = 'korea_express'; break;
				case 'CJ대한통운' : $d_code = 'korea_express'; break;
				case 'CJ gls' : $d_code = 'cj_gls'; break;
				case 'CJGLS' : $d_code = 'cj_gls'; break;
				case '로젠' : $d_code = 'logen'; break;
				case '동부 익스프레스' : $d_code = 'dongbu_express'; break;
				case 'KG로지스' : $d_code = 'dongbu_express'; break;
				case '우체국택배' : $d_code = 'epost'; break;
				case '한진택배' : $d_code = 'hanjin'; break;
				case '현대택배' : $d_code = 'hyundai_logistics'; break;
				case '롯데택배' : $d_code = 'hyundai_logistics'; break;
				case 'KGB 택배' : $d_code = 'kgb_logis'; break;
				case '드림택배' : $d_code = 'dream_logis'; break;
			}
		}

		$op['delivery_service'] = $d_code;

		$_option_str = "";
		if($op['option']) {
			$op['option']=str_replace("/","",$op['option']);
			$op['option']=str_replace("&","",$op['option']);
			if(strpos($op['option'], "<split_small>")==true) {
				$_option_str=str_replace("<split_big>","/",$op['option']);
				$_option_str=str_replace("<split_small>",":",$_option_str);
			}else {
				$_option_str=str_replace("<split_big>",":",$op['option']);
			}
		}

		$product_options = "";
		//옵션이 한개인 경우
		if(strpos($_option_str, "/")==false) {
			$product_options = $_option_str;
		}else {//옵션이 한개이상인 경우
			$_option = explode("/", $_option_str);

			foreach($_option as $key=>$val) {
				if($key>0) $product_options .="&product_options[]=";
				$product_options .= $val;
			}
		}

		$product_options = str_replace('%', '', $product_options);

		//$op['product_options'] = implode("&product_options[]=", $op['product_options']);

		switch($op['stat']) {
			case "1" :
				$op['status'] = "not_paid";
				break;
			case "2" :
				$op['status'] = "paid";
				break;
			case "3" :
				$op['status'] = "delivery_preparing";
				break;
			case "4" :
				$op['status'] = "delivery_started";
				break;
			case "5" :
				$op['status'] = "delivery_finished";
				break;
			case "13" :
				$op['status'] = "returned";
				break;
			case "15" :
				$op['status'] = "returned";
				break;
			case "17" :
				$op['status'] = "returned";
				break;
			default :
				$op['status'] = "paid";
				break;
		}

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$op['ono']."' AND type = 'o'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$op['pno']."' AND type = 'p'";
		$product_id = $this->pdo->row($sql);

		if($ord['date4']) {
			$delivery_started_at = date("Y-m-d H:i:s", $ord['date4']);
		}else {
			$delivery_started_at = 0;
		}
		if($ord['date5'] && $op['stat']==5) {
			$delivered_at = date("Y-m-d H:i:s", $ord['date5']);
		}else {
			$delivered_at = 0;
		}

		$this->method = "PUT";
		$this->resource = "/v1/orders/".$id."/sub_orders/".$opid;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['product_id']			= $product_id;
		$this->requestData['price']					= parsePrice($op['sell_prc']);
		$this->requestData['product_count']			= $op['buy_ea'];
        $this->requestData['code']		        	= $op['no'];
		$this->categoryData							= "";
		$this->optionData							= $product_options;
		$this->reviewImgData							= "";
		$this->requestData['status']				= $op['status'];
		if($op['dlv_code'] && ($op['stat'] == 4 || $op['stat'] == 5)) {
			$this->requestData['delivery_started_at']	= $delivery_started_at;
			$this->requestData['delivered_at']			= $delivered_at;
			$this->requestData['invoice']				= $op['dlv_code'];
			$this->requestData['delivery_service']		= $op['delivery_service'];
		}

		$this->exe();
	}


	// 주문 상품 정보 가져오기
	function getOrderProduct($opno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$opno."' AND type = 'op'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT mt.crema_key FROM ".$this->tbl['order_product']." AS op JOIN ".$this->matching_tbl." AS mt ON op.ono=mt.w_key WHERE op.no = '".$opno."'";
		$oid = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/orders/".$oid."/sub_orders/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;
		//$this->requestData['limit']		    = '100';
		//$this->requestData['page']					= 300;
		$this->exe();
	}

	// 주문 상품 삭제
	function deleteOrderProduct($opno) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$opno."' AND type = 'op'";
		$id = $this->pdo->row($sql);

		$sql = "SELECT mt.crema_key FROM ".$this->tbl['order_product']." AS op JOIN ".$this->matching_tbl." AS mt ON op.ono=mt.w_key WHERE op.no = '".$opno."'";
		$oid = $this->pdo->row($sql);

		$this->method = "DELETE";
		$this->resource = "/v1/orders/".$oid."/sub_orders/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();

		$this->exeMatchingDB("op", "DELETE", $opno);
	}

	// 리뷰 생성
	function createReview($no) {
		$sql = "SELECT COUNT(*) FROM ".$this->matching_tbl." WHERE w_key = '".$no."' AND type = 'r'";
		$use = $this->pdo->row($sql);
		if($use) {
			$this->updateReview($no);
			return;
		}

		$sql = "SELECT * FROM wm_review WHERE no  = '".$no."'";
		$review = $this->pdo->assoc($sql);

		$r_data = array();

		if($review['updir']) {
			if($review['upfile1']) {
				$r_data[] = $this->file_url."/".$review['updir']."/".$review['upfile1'];
			}

			if($review['upfile2']) {
				$r_data[] = $this->file_url."/".$review['updir']."/".$review['upfile2'];
			}
		}

		$review['content'] = str_replace("&nbsp;", "", $review['content']);
		$review_content = addslashes($review['content']);

		$files = array();
		preg_match_all('/https?:\/\/([^"&?]+)\.(gif|png|jpg|jpeg)/is', $review_content, $attach);
		foreach($attach[0] as $key => $val) {
			$files[] =$val;
		}

		foreach($files as $key => $val) {
			$r_data[] = $val;
		}

		$review['title'] = strip_tags($review['title']);
		// 네이버페이 단문 후기의 제목 제거
		if($review['npay'] == 'Y' && strpos(
			trim(strip_tags($review['content'])),
			str_replace('...', '', $review['title'])
		) === 0) {
			$review['title'] = '';
		}

		if($review['pno']) {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$review['pno']."' AND type = 'p'";
			$review_pno = $this->pdo->row($sql);
		} else {
			$review_pno = '-1';
		}

		$img_data = "";
		if(count($r_data)>0) {
			foreach($r_data as $key=>$val) {
				if($key>3) continue;
				if($key>0) $img_data .="&image_urls[]=";
				$img_data .= $val;
			}
		}

		//평점 없을 경우 무조건 5로 크리마에서 요청함 by pyk 2016 12 26
		if(!$review['rev_pt'] || $review['rev_pt']==0) {
			$rev_pt = 5;
		}else {
			$rev_pt = $review['rev_pt'];
		}

		$this->method = "POST";
		$this->resource = "/v1/reviews";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['code']			= $review['no'];
		$this->requestData['product_id']	= $review_pno;
		$this->requestData['created_at']	= date("Y-m-d H:i:s", $review['reg_date']);
		$this->requestData['message']		= $review['title'].strip_tags($review['content']);
		$this->requestData['score']			= $rev_pt;
		if($review['member_id']) $this->requestData['user_code']		= $review['member_id'];
		$this->requestData['user_name']		= $review['name'];
		$this->categoryData					= "";
		$this->optionData					= "";
		$this->reviewImgData				= $img_data;

		$this->exe();

		$this->exeMatchingDB("r", "INSERT", $no);
	}
	// 리뷰 수정
	function updateReview($no) {
		$sql = "SELECT * FROM wm_review WHERE no  = '".$no."'";
		$review = $this->pdo->assoc($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$no."' AND type = 'r'";
		$id = $this->pdo->row($sql);

		$r_data = array();

		if($review['updir']) {
			if($review['upfile1']) {
				$r_data[] = $this->file_url."/".$review['updir']."/".$review['upfile1'];
			}

			if($review['upfile2']) {
				$r_data[] = $this->file_url."/".$review['updir']."/".$review['upfile2'];
			}
		}

		$review['content'] = str_replace("&nbsp;", "", $review['content']);
		$review_content = addslashes($review['content']);

		$files = array();
		preg_match_all('/https?:\/\/([^"&?]+)\.(gif|png|jpg|jpeg)/is', $review_content, $attach);
		foreach($attach[0] as $key => $val) {
			$files[] =$val;
		}

		foreach($files as $key => $val) {
			$r_data[] = $val;
		}

		$review['title'] = strip_tags($review['title']);
		// 네이버페이 단문 후기의 제목 제거
		if($review['npay'] == 'Y' && strpos(
			trim(strip_tags($review['content'])),
			str_replace('...', '', $review['title'])
		) === 0) {
			$review['title'] = '';
		}

		if($review['pno']) {
			$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$review['pno']."' AND type = 'p'";
			$review_pno = $this->pdo->row($sql);
		} else {
			$review_pno = '-1';
		}

		$img_data = "";
		if(count($r_data)>0) {
			foreach($r_data as $key=>$val) {
				if($key>3) continue;
				if($key>0) $img_data .="&image_urls[]=";
				$img_data .= $val;
			}
		}

		//평점 없을 경우 무조건 5로 크리마에서 요청함 by pyk 2016 12 26
		if(!$review['rev_pt'] || $review['rev_pt']==0) {
			$rev_pt = 5;
		}else {
			$rev_pt = $review['rev_pt'];
		}

		$this->method = "PUT";
		$this->resource = "/v1/reviews/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['code']			= $review['no'];
		$this->requestData['product_id']	= $review_pno;
		$this->requestData['created_at']	= date("Y-m-d H:i:s", $review['reg_date']);
		$this->requestData['message']		= $review['title'].strip_tags($review['content']);
		$this->requestData['score']			= $rev_pt;
		if($review['member_id']) $this->requestData['user_code']		= $review['member_id'];
		$this->requestData['user_name']		= $review['name'];
		$this->categoryData					= "";
		$this->optionData					= "";
		$this->reviewImgData				= $img_data;

		$this->exe();
	}

	// 리뷰 정보 가져오기
	function getReview($no) {
		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$no."' AND type = 'r'";
		$id = $this->pdo->row($sql);

		$this->method = "GET";
		$this->resource = "/v1/reviews/".$id;
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']	= $this->oAuth;

		$this->exe();
	}

	// 주문 리스트 가져오기
	function getReviewList($limit=30,$page=1) {
		$this->method = "GET";
		$this->resource = "/v1/reviews/";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData['access_token']			= $this->oAuth;
		$this->requestData['limit']					= $limit;
		$this->requestData['page']					= $page;
		$this->exe();
	}

	// 리뷰 댓글 생성
	function createRComment($no) {
		$sql = "SELECT * FROM wm_review_comment WHERE no  = '".$no."'";
		$comment = $this->pdo->assoc($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$comment['ref']."' AND type = 'r'";
		$id = $this->pdo->row($sql);

		$this->method = "POST";
		$this->resource = "/v1/reviews/".$id."/comments";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['code']			= $comment['no'];
		$this->requestData['created_at']	= date("Y-m-d H:i:s", $comment['reg_date']);
		$this->requestData['message']		= $comment['content'];
		$this->requestData['user_code']		= $comment['member_id'];
		$this->requestData['user_name']		= $comment['name'];
		$this->categoryData							= "";
		$this->optionData							= "";
		$this->reviewImgData							= "";

		$this->exe();

		$this->exeMatchingDB("rc", "INSERT", $no);
	}

	// 장바구니 아이템 업데이트
	function createCartItem($cart, $id) {
		$this->method = "POST";
		$this->resource = "/v1/users/".$id."/cart_items";
		$this->restUrl = $this->url.$this->resource;

		$pno = $this->pdo->row("select crema_key from crema_matching where type='p' and w_key='$cart[pno]'");

		$this->requestData = array();

		$this->requestData['access_token']		= $this->oAuth;
		$this->requestData['product_id']		= $pno;
		$this->requestData['code']				= $cart['no'];
		$this->requestData['added_to_cart_at']	= date("Y-m-d H:i:s", $cart['reg_date']);

		$this->exe();
		return $this->responseArr;
	}

	// 회원 업데이트
	function createUser($mem) {
		if(!$mem['member_id']) return false;

		$mem = array_map('stripslashes', $mem);

		$sql = "SELECT level FROM ".$this->tbl['member']." where member_id='$mem[member_id]'";
		$member_level = $this->pdo->row($sql);

		$sql = "SELECT crema_key FROM ".$this->matching_tbl." WHERE w_key = '".$member_level."' AND type = 'g'";
		$member_glevel = $this->pdo->row($sql);

		$this->method = "POST";
		$this->resource = "/v1/users";
		$this->restUrl = $this->url.$this->resource;

		$this->requestData = array();

		$this->requestData['access_token']	= $this->oAuth;
		$this->requestData['user_id']		= $mem['member_id'];
		$this->requestData['user_name']			= $mem['name'];
		$this->requestData['created_at']	= date('Y-m-d H:i:s', $mem['reg_date']);
		$this->requestData['user_phone']	= $mem['cell'];
		$this->requestData['allow_sms']		= ($mem['sms'] == 'Y') ? 1 : 0;
		$this->requestData['user_email']	= $mem['email'];
		$this->requestData['allow_email']	= ($mem['mailing'] == 'Y') ? 1 : 0;
		$this->requestData['user_grade_id']	= $member_glevel;

		$this->exe();

		$this->exeMatchingDB("m", "INSERT", $mem['no']);

		return $this->responseArr;
	}

	function exeMatchingDB($type, $method, $key) {
        global $pdo;

		if($method=="INSERT" && !$this->responseArr['id']) return; //정상적인 api response 가 없으면 INSERT 안함

		switch($method) {
			case "INSERT" :
				$sql = "INSERT INTO ".$this->matching_tbl."
							(type, w_key, crema_key, reg_date)
						VALUES
							('".$type."', '".$key."', '".$this->responseArr['id']."', ".time().")";
				break;
			case "DELETE" :
				$sql = "DELETE FROM ".$this->matching_tbl." WHERE w_key = '".$key."' AND type = '".$type."'";
				break;
		}

		$pdo->query($sql);
	}

	function exe() {
		$this->execute();

		$this->decodeResult();
	}

	function decodeResult() {
		$jdata = json_decode($this->responseBody);
		$this->responseArr = $jdata;
		$this->responseArr = $this->objToArr($this->responseArr); // include common.lib.php
	}

	function objToArr($data) {
		if(is_object($data)) {
			foreach (get_object_vars($data) as $key => $val) {
				$ret[$key] = $this->objToArr($val);
			}
			return $ret;
		}elseif(is_array($data)) {
			foreach ($data as $key => $val) {
				$ret[$key] = $this->objToArr($val);
			}
			return $ret;
		}else{
			return $data;
		}
	}
}
?>