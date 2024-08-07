<?php
/**
 * rest API 범용 클래스
 * @박연경 <pyk87@wisa.co.kr>
 * @date 2016-01-25
 */


class restAPI{

	var $url; // url
	var $restUrl; // access url + directory
	var $requestData; // array or string request data
	var $optionData; //옵션데이터 배열
	var $categoryData; //카테고리 데이터 배열
	var $reviewImgData; //리뷰이미지 데이터 배열
	var $httpHeader;
	var $method;
	var $debugMode;
	var $responseBody; // callback data
	var $responseInfo; // callback info

	function __construct() {
		$this->httpHeader =  array("Accept : application/x-www-form-urlencoded;charset=UTF-8");
	}

	function execute() {
		$ch = curl_init();

		switch(strtoupper($this->method)) {
			case 'GET' :
				$this->executeGet($ch);
				break;
			case 'POST' :
				$this->executePost($ch);
				break;
			case 'PUT' :
				$this->executePut($ch);
				break;
			case 'DELETE' :
				$this->executeDelete($ch);
				break;
		}
	}

	function executeGet($ch) {
		if(!is_string($this->requestData)) {
			$this->makeData();
		}

		$this->restUrl = $this->restUrl."?".$this->requestData;

		$this->doExecute($ch);
	}

	function executePost($ch) {
		if(!is_string($this->requestData)) {
			$this->makeData();
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestData);
		curl_setopt($ch, CURLOPT_POST, 1);

		$this->doExecute($ch);
	}

	function executePut($ch) {
		if(!is_string($this->requestData)) {
			$this->makeData();
		}

		/*
		//파일 업로드 방식 사용시
		$this->requestLength = strlen($this->requestData);

		$fh = fopen('php://memory', 'rw');
		fwrite($fh, $this->requestData);
		rewind($fh);

		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
		curl_setopt($ch, CURLOPT_PUT, true);
		*/
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestData);
		$this->doExecute($ch);

		//fclose($fh);
	}

	function executeDelete($ch) {
		if(!is_string($this->requestData)) {
			$this->makeData();
		}

		$this->restUrl = $this->restUrl."?".$this->requestData;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		$this->doExecute($ch);
	}

	function doExecute(&$curlHandle) {
		$this->setCurlOpts($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		$this->responseInfo	= curl_getinfo($curlHandle);

		curl_close($curlHandle);

		if($this->debugMode) $this->deBug();

		$this->requestData = "";
	}

	function setCurlOpts(&$curlHandle) {
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
		curl_setopt($curlHandle, CURLOPT_URL, $this->restUrl);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);

		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $this->httpHeader);
	}

	function makeData($data = null) {
		$data = ($data !== null) ? $data : $this->requestData;

		$data = $this->makeQueryString($data);

		if($this->optionData) {
			$data .= "&product_options[]=".$this->optionData;
		}
		if($this->categoryData) {
			$data .= "&category_ids[]=".$this->categoryData;
		}
		if($this->reviewImgData) {
			$data .= "&image_urls[]=".$this->reviewImgData;
		}
        $data = preg_replace('/product_options\[[0-9]+\]/', 'product_options[]', $data);
        $data = preg_replace('/product_options\[\]\[values\]\[[0-9]+\]/', 'product_options[][values][]', $data);

		$this->requestData = $data;
	}

	function deBug() {
		if($this->debugMode==1) {
			if( is_array($this->responseBody) ){
				echo "<pre>";
				print_r($this->responseBody);
				echo "</pre>";
			}

		}
		elseif($this->debugMode==2) {
			if( is_array($this->responseBody) ){
				echo "<pre>";
				print_r($this->responseBody);
				echo "</pre>";
			}
			if( is_array($this->responseInfo) ){
				echo "<pre>";
				print_r($this->responseInfo);
				echo "</pre>";
			}
		}
	}

	function makeQueryString($data, $key = '') {
		$ret = array();
		if(is_array($data)) {
			$sep = ini_get("arg_separator.output");
			foreach ($data as $k => $v) {
				$k = urlencode($k);

				if (!empty($key)) {
					$k = $key . "[" . $k . "]";
				}

				if (is_array($v) || is_object($v)) {
					array_push($ret, $this->makeQueryString($v, $k));
				} else {
					array_push($ret, $k . "=" . urlencode($v));
				}
			}
		}
		return implode($sep, $ret);
	}
}
?>