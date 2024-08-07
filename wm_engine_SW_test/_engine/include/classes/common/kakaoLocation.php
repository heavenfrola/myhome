<?php

namespace Wing\common;

class kakaoLocation {

	public function __construct() {
		global
		$tbl_schema,
		$pdo,
		$tbl,
		$cfg;

		$this->pdo = $pdo;
		$this->tbl_schema = $tbl_schema;
		$this->tbl = $tbl;
		$this->cfg = $cfg;
	}

	//카카오 rest api 함수
	function kakaoRestApi($type, $parameter=array(), $content_type='json') {
		$host = "https://dapi.kakao.com";
		$api_key = $this->cfg['use_kakao_location_rest_key'];
		$headers = array("Authorization: KakaoAK {$api_key}");

		$query = http_build_query($parameter); //url 형식으로 변환
		$path = "";

		switch ($type) {
			// 좌표로 주소변환
			case "coord2address" :
				$path = "/v2/local/geo/coord2address";
				break;

			// 주소 검색
			case "address" :
				$path = "/v2/local/search/address";
				break;

			// 좌표로 행정구역정보 받기
			case "coord2regioncode" :
				$path = "/v2/local/geo/coord2regioncode";
				break;

			default :
				// 올바른 API 구분이 아닌 경우 return;
				return array('api_result' => false, 'err_msg' => 'API 구분을 올바르게 입력해 주세요.');
		}

		$opts = array(
			CURLOPT_URL => $host . $path . '.' . $content_type . '?' . $query,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSLVERSION => 1,
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => $headers
		);

		$curl_session = curl_init();
		curl_setopt_array($curl_session, $opts);
		$return_data = curl_exec($curl_session);

		// response 정보
		$decode = json_decode($return_data, true);

		if (curl_errno($curl_session)) {
			throw new Exception(curl_error($curl_session));
			$decode['api_result'] = false;
		} else {
			curl_close($curl_session);
			$decode['api_result'] = true;
		}

		return $decode;
	}

	//배송지 리스트
	function getStoreAddr($target, $SI_NM = null, $SI_GM = null) {
		global $cfg, $_sido_mapping, $_we;

		switch($target) {
			case 'sido' :
				break;
			case 'gugun' :
				$parent_nm = $SI_NM;
				break;
		}

		$wec = new \weagleEyeClient($_we, 'etc');
		$res = $wec->call('getSubAddress', array(
			'target' => $target,
			'SI_NM' => $SI_NM,
		));

		$res = json_decode($res);

		$result = array();
		$sel = "";
		foreach($res as $key => $val) {
			if($SI_NM != '')
			{
				$sel = ($val == $SI_GM) ? "selected":"";
				$option .= "<option value=\"$val\" $sel>$val</option>";
			} else {
				$result[$val]= $val;
			}
		}
		if($SI_NM) $result= $option;

		return $result;
	}
	// 카카오 시도명칭 변환
	function convSidoFromDaum($sido) {
		switch($sido) {
			case '서울' :
				return '서울특별시';
			case '부산' :
				return '부산광역시';
			case '광주' :
				return '광주광역시';
			case '대구' :
				return '대구광역시';
			case '대전' :
				return '대전광역시';
			case '인천' :
				return '인천광역시';
			case '울산' :
				return '울산광역시';
			case '강원' :
				return '강원도';
			case '경기' :
				return '경기도';
			case '경남' :
				return '경상남도';
			case '경북' :
				return '경상북도';
			case '전남' :
				return '전라남도';
			case '전북' :
				return '전라북도';
			case '충남' :
				return '충청남도';
			case '충북' :
				return '충청북도';
			default :
				return $sido;
		}
	}
}
?>