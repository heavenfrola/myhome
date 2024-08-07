<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	class shopLinkerOrder extends shopLinker {
		private $api_url;
		private $xml_url;
		private $delivery_pno;
        private $pdo;

		public function __construct() {
			parent::__construct();

            $this->pdo = $GLOBALS['pdo'];
		}

		public function orderImport() {
			global $tbl;

			// 주문 접수
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Order/orderlist.php';
			$return = $this->api(
					$this->api_url,
					'orderImport',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=order&method=orderImport&stype=1')
			);
			$xml = simplexml_load_string($return);

			$onos = array();
			if(count($xml->order) > 0) {
				foreach($xml->order as $val) {
					$onos[] = $this->insertOrder($val);
				}
				$this->makePayments($onos);
			}

			// 클레임(취소, 환불, 반품, 교환) 접수
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Clame/Clame_Xml.php';
			$return = $this->api(
					$this->api_url,
					'orderImport',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=order&method=orderImport&stype=2')
			);
			$xml = simplexml_load_string($return);
			if(count($xml->Clame) > 0) {
				foreach($xml->Clame as $val) {
					$onos[] = $this->insertClaim($val);
				}
			}

			$onos = array_unique($onos);
			foreach($onos as $ono) {
				ordChgPart($ono, true);

				$stat = $this->pdo->row("select stat from $tbl[order] where ono='$ono'");
				ordStatLogw($ono, $stat, 'Y');
			}

			return $onos;
		}

		public function orderImportXML() {
			$data = array(
				'customer_id' => $this->customer_cd,
				'shoplinker_id' => $this->customer_id,
				'st_date' => date('Ymd', strtotime('-3 days')),
				'ed_date' => date('Ymd'),
			);

			$type = ($_GET['stype'] == 2) ? 'Clame' : 'Order';

			$this->makeXml($type, array($data));
		}

		private function insertOrder($val) {
			global $tbl, $cfg, $now;

			$mall_id = $val->mall_id->__toString();
			$mall_ono = $val->mall_order_id->__toString();
			$ono = $val->mall_order_id->__toString();
			$mall_name = $val->mall_name->__toString();
			$buyer_name = trim($val->order_name->__toString());
			$buyer_tel = $val->order_tel->__toString();
			$buyer_cell = $val->order_cel->__toString();
			$buyer_email = $val->order_email->__toString();
			$addressee_name = $val->receive->__toString();
			$addressee_tel = $val->receive_tel->__toString();
			$addressee_cell = $val->receive_cel->__toString();
			$addressee_zip = $val->receive_zipcode->__toString();
			$addressee_addr = explode(' ', $val->receive_addr->__toString());
			foreach($addressee_addr as $key => $_addr) {
				$_key = ($key <= 3) ? 1 : 2;
				${'addressee_addr'.$_key} .= ' '.$_addr;
			}
			$addressee_addr1 = trim($addressee_addr1);
			$addressee_addr2 = trim($addressee_addr2);
			$dlv_memo = $val->delivery_msg->__toString();
			$date1 = $this->parseDate($val->order_reg_date->__toString());
			if($cfg['openmarket_scrap_order'] == 2) {
				$date2 = $date1;
			}

			// 배송비
			$stat = $cfg['openmarket_scrap_order'];
			$dlv_type = $val->baesong_type->__toString();
			$dlv_prc = $val->baesong_bi->__toString();

			// 주문상품
			$pno = $val->order_product_id->__toString();
			$shoplinker_pno = $val->shoplinker_product_id->__toString();
			$name = $val->product_name->__toString();
			$buy_ea = $val->quantity->__toString();
			$sell_prc = $val->sale_price->__toString();
			$total_prc = $sell_prc*$buy_ea;
			$sku = $val->sku->__toString();
			$only_sku = $val->only_sku->__toString();
			$add_sku = $val->add_sku->__toString();
			$sku_match_code = $val->sku_match_code->__toString();
			$sku_barcode = $val->sku_barcode->__toString();
			$hash = md5($pno.$sku);
			$shoplinker_ono = $val->shoplinker_order_id->__toString();
			$option = preg_replace('/_[0-9]+_/', '', $sku);
			$complex_no = numberOnly(preg_replace('/.*_([0-9]+)_.*/', '$1', $sku));
			if($sku_barcode) {
				$_tmp_complex_no = $this->pdo->row("select complex_no from erp_complex_option where barcode='$sku_barcode'");
				if($_tmp_complex_no > 0) $complex_no = $_tmp_complex_no;
			}

			// 주문정보 인서트
			$ono = $this->pdo->row("select ono from $tbl[order] where openmarket_id='$mall_id' and openmarket_ono='$mall_ono'");
			if(!$ono) {
				$ono = date('Ymd', $date1).'-'.numberOnly($mall_id).'-'.$val->shoplinker_order_id->__toString();

				$this->pdo->query("
					insert into $tbl[order]
						(ono, stat, buyer_name, buyer_phone, buyer_cell, buyer_email, addressee_name, addressee_phone, addressee_cell, addressee_zip, addressee_addr1, addressee_addr2, dlv_prc, dlv_memo, date1, openmarket_id, openmarket_ono, pay_type, prd_prc, total_prc, pay_prc)
						values
						('$ono', '$stat', '$buyer_name', '$buyer_tel', '$buyer_cell', '$buyer_email', '$addressee_name', '$addressee_tel', '$addressee_cell', '$addressee_zip', '$addressee_addr1', '$addressee_addr2', '$dlv_prc', '$dlv_memo', '$date1', '$mall_id', '$mall_ono', '9', '0', '$dlv_prc', '$dlv_prc')
				");
				$this->pdo->query("insert into $tbl[order_memo] (admin_id, ono, content, type, reg_date) values ('system', '$ono', '[$mall_name] $dlv_type', '1', $now)");
			}

			// 주문상품정보 인서트
			$exists = $this->pdo->row("select no from $tbl[order_product] where ono='$ono' and openmarket_hash='$hash'");
			if(!$exists) {

				// 입점사 정산 데이터 저장
				$pasql1 = $pasql2 = '';
				if($cfg['use_partner_shop'] == 'Y') {
					$prd = $this->pdo->assoc("select no, partner_no, partner_rate, dlv_type from $tbl[product] where no='$pno'");
					if($prd['no']) {
						$prd['fee_prc'] = getPercentage($total_prc, $prd['partner_rate']);
						$pasql1 = ", partner_no, fee_rate, fee_prc, dlv_type";
						$pasql2 = ", '$prd[partner_no]', '$prd[partner_rate]', '$fee_prc', '$prd[dlv_type]'";
					}
				}

				$this->pdo->query("
					insert into $tbl[order_product]
						(ono, pno, name, sell_prc, buy_ea, total_prc, `option`, option_prc, complex_no, stat, openmarket_id, openmarket_ono, openmarket_hash $pasql1)
						values
						('$ono', '$pno', '$name', '$sell_prc', '$buy_ea', '$total_prc', '$option', '$option_prc', '$complex_no', '$stat', '$mall_id', '$shoplinker_ono', '$hash' $pasql2)
				");
				orderStock($ono, 0.99, $stat, $this->pdo->lastInsertId());

				$this->pdo->query("update $tbl[order] set prd_prc=prd_prc+'$total_prc', total_prc=total_prc+'$total_prc', pay_prc=pay_prc+'$total_prc' where ono='$ono'");

				return $ono;
			}

			return false;
		}

		private function makePayments($onos) {
			global $tbl;

			$onos = array_unique($onos);
			foreach($onos as $ono) {
				$ord = $this->pdo->assoc("select pay_prc, dlv_prc, (select group_concat(no) from $tbl[order_product] where ono='$ono') as pno from $tbl[order] where ono='$ono'");

				$payment_no = createPayment(array(
					'type' => 0,
					'ono' => $ono,
					'pno' => explode(',', $ord['pno']),
					'pay_type' => 9,
					'amount' => $ord['pay_prc'],
					'dlv_prc' => $ord['dlv_prc'],
					'reason' => '샵링커 주문수집',
				), 2);
				ordChgPart($ono, true);
			}
		}

		public function getDeliveryListXml() {
			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Order/delivery_list.php';

			$xml = $this->api(
				$this->api_url,
				null,
				'customer_id='.$this->customer_cd
			);
			$xml = @simplexml_load_string($xml);
			$xml = $xml->DeliveryList->DeliveryNm;

			$data = array();
			foreach($xml as $key => $val) {
				$data[] = trim($val->__toString());
			}

			return $data;
		}

		public function setDeilvery($pno, $dlv_no, $dlv_code) {
			global $cfg;

			if($cfg['openmarket_dlv'] != 'Y') return;

			$pno = preg_replace('/[^0-9,]/', '', $pno);

			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Order/delivery.php';
			$return  = $this->api(
					$this->api_url,
					'setDeilvery',
					'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=order&method=setDeilvery&pno='.$pno.'&dlv_no='.$dlv_no.'&dlv_code='.$dlv_code)
			);

			return $this->setDeliveryLinkage($pno, $dlv_no, $dlv_code);
		}

		public function setDeilveryXML() {
			global $tbl;

			$pno = preg_replace('/[^0-9,]/', '', $_GET['pno']);
			$dlv_no = numberOnly($_GET['dlv_no']);
			$dlv_code = addslashes($_GET['dlv_code']);
			$dlv_name = stripslashes($this->pdo->row("select name from $tbl[delivery_url] where no='$dlv_no'"));

			$datas = array();
			$res = $this->pdo->iterator("select openmarket_ono from $tbl[order_product] where no in ($pno)");
			foreach ($res as $ord) {
				$datas[] = array(
					'order_id' => $ord['openmarket_ono'],
					'delivery_name' => $dlv_name,
					'delivery_invoice' => $dlv_code,
				);
			}

			$this->makeXml(array('OrderInfo', 'Delivery'), $datas);
		}

		public function setDeliveryLinkage($pno, $dlv_no, $dlv_code) {
			global $tbl;

			$this->api_url = 'http://apiweb.shoplinker.co.kr/ShoplinkerApi/Order/DeliveryLinkage.php';
			$return  = $this->api(
				$this->api_url,
				'setDeliveryLink',
				'iteminfo_url='.urlencode($this->root_url.'/main/exec.php?exec_file=api/shopLinker/xml.exe.php&type=order&method=setDeliveryLinkage&pno='.$pno.'&dlv_no='.$dlv_no.'&dlv_code='.$dlv_code)
			);

			$return = mb_convert_encoding($return, 'euckr', 'utf8');
			$xml = simplexml_load_string($return);
			$result = $xml->ResultMessage->result->__toString();
			$result = ($result == 'true') ? true: false;

			return $result;
		}

		function setDeliveryLinkageXML() {
			global $tbl;

			$pno = preg_replace('/[^0-9,]/', '', $_GET['pno']);
			$dlv_no = numberOnly($_GET['dlv_no']);
			$dlv_code = addslashes($_GET['dlv_code']);
			$dlv_name = stripslashes($this->pdo->row("select name from $tbl[delivery_url] where no='$dlv_no'"));

			$datas = array();
			$res = $this->pdo->iterator("
				select a.openmarket_ono, a.openmarket_id, c.account_id
				from $tbl[order_product] a
				inner join $tbl[openmarket_cfg] c on c.api_code=a.openmarket_id
				where a.no in ($pno)
			");
            foreach ($res as $ord) {
				$account_id = $ord['account_id'];
				$openmarket_id = $ord['openmarket_id'];

				$datas[] = array(
					'order_id' => $ord['openmarket_ono'],
					'user_id' => $ord['account_id'],
					'delivery_name' => $dlv_name,
					'delivery_invoice' => $dlv_code,
				);
			}

			$this->mall_id = $openmarket_id;
			$this->master_id = $account_id;

			$this->makeXml(array('OrderInfo', 'Delivery'), $datas, array('user_id', 'delivery_name', 'delivery_invoice'));
		}

		private function insertClaim($val) {
			global $tbl;

			$openmarket_id = $val->mall_order_id->__toString();
			$openmarket_ono = $val->shoplinker_order_id->__toString();
			$openmarket_hash = md5($val->order_product_id->__toString().$val->sku->__toString());

			$data = $this->pdo->assoc("select no, ono, stat from $tbl[order_product] where openmarket_ono='$openmarket_ono' and openmarket_hash='$openmarket_hash'");
			if(!$data['no']) return;

			switch($val->clame_type->__toString()) {
				case '001' : $stat = '13'; break;
				case '002' : $stat = '19'; break;
				case '003' : $stat = '17'; break;
			}
			if($stat == $data['stat']) return;

			$mall_name = addslashes($val->mall_name->__toString());
			$claim_date = $this->parseDate($val->clame_date->__toString());
			$claim_memo = addslashes($val->clame_memo->__toString());

			orderStock($ono, $data['stat'], $stat, $data['no']);

			$this->pdo->query("update $tbl[order_product] set stat='$stat' where no='$data[no]'");
			$this->pdo->query("insert into $tbl[order_memo] (admin_id, ono, content, type, reg_date) values ('system', '$data[ono]', '[$mall_name] $claim_memo', '1', $claim_date)");

			return $data['ono'];
		}

	}

?>