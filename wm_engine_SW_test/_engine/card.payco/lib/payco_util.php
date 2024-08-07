<?PHP
	//------------------------------------------------------------------------------
    // PAYCO 기능 함수  ( PHP EASYPAY / PAY1 )
	// payco_util.php version 1.1
	// 2016-02-02	PAYCO기술지원 <dl_payco_ts@nhnent.com>
	//-------------------------------------------------------------------------------
	include("include/httpcurl.php");

	//-------------------------------------------------------------------------------
	// 주문 예약 API 호출 함수
	// 사용 방법 : payco_reserve($mData)
	// $mData - JSON 데이터
	//-------------------------------------------------------------------------------
	function payco_reserve($mData){

		GLOBAL $URL_reserve;
		$Result = Call_API($URL_reserve, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "주문 예약 API 호출 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// PAYCO 주문 취소 가능 여부 API 호출 함수
	// 사용 방법 : payco_cancel_check($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_cancel_check($mData){

		GLOBAL $URL_cancel_check;
		$Result = Call_API($URL_cancel_check, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "주문 결제 취소 가능 여부 조회 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// PAYCO 주문 취소 API 호출 함수
	// 사용 방법 : payco_cancel($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_cancel($mData){

		GLOBAL $URL_cancel;
		$Result = Call_API($URL_cancel, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "주문 결제 취소 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// 주문 상태 변경 API 호출 함수
	// 사용 방법 : payco_upstatus($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_upstatus($mData){

		GLOBAL $URL_upstatus;
		$Result = Call_API($URL_upstatus, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "주문 상태 변경 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// 마일리지 적립 취소 API 호출 함수
	// 사용 방법 : payco_cancelmileage($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_cancelmileage($mData){

		GLOBAL $URL_cancelMileage;
		$Result = Call_API($URL_cancelMileage, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "마일리지 적립 취소 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// 가맹점별 연동키 유효성 체크 API 호출 함수
	// 사용 방법 : payco_keycheck($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_keycheck($mData){

		GLOBAL $URL_checkUsability;
		$Result = Call_API($URL_checkUsability, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "가맹점별 연동키 유효성 체크 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//---------------------------------------------------------------------------------
	// 결제상세 조회(검증용) API 호출 함수
	// 사용 방법 : payco_detailForVerify($mData)
	// $mData - JSON 데이터
	//---------------------------------------------------------------------------------
	function payco_detailForVerify($mData){

		GLOBAL $URL_detailForVerify;
		$Result = Call_API($URL_detailForVerify, "json", $mData);

		if($Result[0] == 200){
			return $Result[1];
		} else {
			$Error_Return = array();
			$Error_Return["result"]		= "결제상세 조회 도중 오류가 발생하였습니다.";
			$Error_Return["message"]	= $Result[1];
			$Error_Return["code"]		= $Result[0];
			return $Error_Return;
		}
	}

	//-----------------------------------------------------------------------------
	// 로그 기록 함수 ( 디버그용 )
	// 사용 방법	: Call Write_Log(Log_String)
	// Log_String	: 로그 파일에 기록할 내용
	//-----------------------------------------------------------------------------
	function Write_Log($Input_String) {

		global $LogUse, $Write_LogFile;

		if($LogUse){

			$oTextStream = fopen($Write_LogFile, "a");
			$today = date("Y-m-d H:i:s");

			//-----------------------------------------------------------------------------
			// 내용 기록
			//-----------------------------------------------------------------------------
			fwrite($oTextStream,  $today." ".$Input_String."\n");

			//-----------------------------------------------------------------------------
			// 리소스 해제
			//-----------------------------------------------------------------------------
			fclose($oTextStream);
		}
	}
	//-----------------------------------------------------------------------------
	// API 호출 함수( POST 전용 - PAYCO 연동은 모든 API 호출에 POST만을 사용합니다. )
	// 사용 방법	: Call_API(SiteURL, App_Mode, Param)
	// SiteURL		: 호출할 API 주소
	// App_Mode		: 데이터 전송 형태 ( 예: json, x-www-form-urlencoded 등 )
	// Param		: 전송할 POST 데이터
	//-----------------------------------------------------------------------------
	function Call_API($SiteURL, $App_Mode, $Param) {

		//-----------------------------------------------------------------------------
		// API 전송 정보를 로그 파일에 저장
		//-----------------------------------------------------------------------------
		Write_Log("Call API   $SiteURL Mode : $App_Mode");
		Write_Log("Call API   $SiteURL Data : $Param");

		try {
			//-----------------------------------------------------------------------------
			// WinHttpRequest 선언
			//-----------------------------------------------------------------------------
			$http = new HTTPCURL();
			$http->Post($SiteURL, $App_Mode, $Param, "false");
			$returnValue = $http->getResult();
			$http->Close();

			//-----------------------------------------------------------------------------
			// API 전송 결과를 로그 파일에 저장
			//-----------------------------------------------------------------------------
			Write_Log("API Result $SiteURL Status : ".$http->response['http_code']);
			Write_Log("API Result $SiteURL ResponseText : ".$returnValue);

		} catch (RequestException $e) {
			Write_Log("Call API Function Error : Number - ".$e->getRequest().", Description - ".$e->getResponse());
		}

		$Result = array();
		array_push($Result, $http->response['http_code'] ,$returnValue);
		return $Result;
	}
?>