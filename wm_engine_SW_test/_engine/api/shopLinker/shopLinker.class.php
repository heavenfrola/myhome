<?PHP

	class shopLinker {
		protected $root_url;
		protected $customer_id;
		protected $customer_cd;
        private $pdo;

		public function __construct() {
			global $cfg;

			$this->root_url = $GLOBALS['root_url'];
			$this->customer_id = $cfg['shoplinker_id'];
			$this->customer_cd = $cfg['shoplinker_cd'];
			$this->origin = '대한민국';
            $this->pdo = $GLOBALS['pdo'];
		}

		public function __destruct() {}

		// xml 양식 생성
		public function makeXml($type, $data = array(), $cdata = array(), $msg_top = array()) {
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8' ?><openMarket></openMarket>");

			if(is_array($type)) {
				$type1 = $type[0];
				$type2 = $type[1];
			} else {
				$type1 = $type.'Info';
				$type2 = $type;
			}

			$header = $xml->addChild('MessageHeader');
			$header->addChild('sendID', 1);
			$header->addChild('senddate', date('Ymd'));
			$header->addChild('customer_id', $this->customer_cd);
			if($this->mall_id) {
				$section = $header->addChild('mall_id');
				$dom = dom_import_simplexml($section);
				$dom->appendChild($dom->ownerDocument->createCDATASection($this->mall_id));
			}
			if($this->master_id) {
				$section = $header->addChild('master_id');
				$dom = dom_import_simplexml($section);
				$dom->appendChild($dom->ownerDocument->createCDATASection($this->master_id));
			}

			$dom = dom_import_simplexml($xml);
			$body = $xml->addChild($type1);

			foreach($msg_top as $tag => $value) {
				if(in_array($tag, $cdata)) {
					$section = $body->addChild($tag);
					$dom = dom_import_simplexml($section);
					$dom->appendChild($dom->ownerDocument->createCDATASection($value));
				} else {
					$body->addChild($tag, $value);
				}
			}

			foreach($data as $val) {
				$item = $body->addChild($type2);
				foreach($val as $tag => $value) {
					list($tag, $attr) = explode(':', $tag);
					if(in_array($tag, $cdata)) {
						$section = $item->addChild($tag);
						$dom = dom_import_simplexml($section);
						$dom->appendChild($dom->ownerDocument->createCDATASection($value));
					} else {
						$section = $item->addChild($tag, $value);
					}
					if($attr) {
						$tmp = explode('=', $attr);
						$section->addAttribute($tmp[0], $tmp[1]);
					}
				}
			}

			$fp = fopen($GLOBALS['root_dir'].'/_data/openmarket_'.time().'.xml', 'w');
			fwrite($fp, $xml->asXML());
			fclose($fp);

			$this->printXML($xml->asXML());
			exit;
		}

		public function printXML($xml) {
			header('content-type:text/xml; charset=utf8;');
			echo $xml;
		}

		function api($api_url, $method, $param) {
			global $tbl, $now;

			$return = trim(comm($api_url, $param));
			$return = mb_convert_encoding(trim($return), _BASE_CHARSET_, array('euckr', 'utf8'));
			$return = str_replace('EUC-KR', 'UTF-8', $return);

			$req = addslashes($api_url.'?'.$param);
			$ret = addslashes($return);
			$this->pdo->query("insert into $tbl[openmarket_api_log] (method, req_data, ret_data, reg_date) values ('$method', '$req', '$ret', '$now')");

			return $return;
		}

		function parseDate($str) {
			$date = strtotime($str);
			return $date;
		}
	}

?>