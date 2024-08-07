<?php

use Wing\common\Xml;
use Wing\HTTP\CurlConnection;

Class weagleEyeClient {

	var $config;
	var $queue_list;
	var $db_connect;
	var $server;
	var $service;
	var $result;
	var $error;

	function __construct($config, $service = null) {
		$this->config = $config;
		$this->server = 'http://weo.wisa.ne.kr';
		$this->service = $service;
	}

	function queue($action) { // 큐 작성
		$args = func_get_args();

		$param  = "service=".$this->service;
		$param .= "&action=".$action;
		$param .= "&keycode=".$this->config['wm_key_code'];
		$param .= "&apikey=".$this->config['api_key'];

		foreach($args as $key => $val) {
			if($key == 0) continue;
			if(is_array($val)) $val = implode('@', $val);
			if(_BASE_CHARSET_ != 'euc-kr') $val = iconv('UTF-8', 'CP949//IGNORE', $val);
			$val = urlencode($val);
			$param .= '&args'.$key.'='.$val;
		}

		return $this->send($param);
	}

	function call($action) {
		$args = func_get_args();
		if(is_array($args[1])) {
			foreach($args[1] as $key => $val) {
				if($key == 'service' || $key == 'action') continue;
				$add_args .= "&$key=$val";
			}
		}

		$param  = "service=".$this->service;
		$param .= "&action=".$action;
		$param .= "&keycode=".$this->config['wm_key_code'];
		$param .= "&apikey=".$this->config['api_key'];

		return $this->send($param.$add_args);
	}

	function send_clean() {

	}

	function send($param) {
		$this->result = $this->comm($this->server, $param);
		$this->result = trim($this->result);

		if(preg_match('/^#ERROR/', $this->result)) {
			$this->result = preg_replace('/^#ERROR/', '', $this->result);
			$this->error = 1;
		} else {
			if (!$this->result) $this->result = 'OK';
		}

		if(preg_match('/^<\?xml/', $this->result)) $this->result = $this->parseXmlInfo($this->result); // 결과가 XML일 경우
		return $this->result;
	}

	function comm($url, $post_args = null, $protocol = null) {
		$post_args = preg_replace('/&+/', '&', 'account_idx='.$this->config['account_idx'].'&'.$post_args);

        $curl = new CurlConnection($url, (is_null($post_args) ? 'GET' : 'POST'), $post_args);
        $curl->exec();
        $result = $curl->getResult();

		if($result === true || strpos($result, '<title>404 Not Found</title>') > 0) $result = '';
		$result = trim($result);

		return $result;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string get(string $rcode, string $args)
	' +----------------------------------------------------------------------------------------------+*/
	function get($rcode, $args = null, $xml_parse = false) {
		$param  = "rcode=".$rcode;
		$param .= "&keycode=".$this->config['wm_key_code'];
		$param .= "&apikey=".$this->config['api_key'];
		$param .= '&'.$args;

		$result = $this->comm($this->server, $param);
		if($xml_parse == true) $result = $this->parseXmlInfo($result);

		return $result;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  array parseXmlInfo(string $xml);
	' +----------------------------------------------------------------------------------------------+*/
	function parseXmlInfo($xmldata) {
		$xml = new Xml();
		$xml->xmlData($xmldata);
		if(is_null($xml->arr->weXML)) {
            return simplexml_load_string($xmldata, null, LIBXML_NOERROR);
		}

		$data = $xml->arr->weXML[0]->data[0]->info;
		return $data;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void error(string $msg) - 에러 발생시 내용을 출력한다
	' +----------------------------------------------------------------------------------------------+*/
	function error($msg){
		echo "<script type='text/javascript'>window.alert('{$msg}')</script>";
		exit;
	}

}

?>