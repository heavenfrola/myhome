<?
	require_once("JSONFunc.php");

	//개발서버
	define('AGSHOST','https://www.allthegate.com');
	//define('AGSHOST','http://hs.whoopersoft.com:8888');

	class AGSMobile {

		var $tracking_id = "";
		var $transaction = "";
		var $store_id = "";
		var $tracking_info = array();
		var $logging = false;
		var $logfile = null;
		var $ispCardNm = "";
		var $netCancelId = "";

		function __construct() {
			$args= func_get_args();
	        call_user_func_array
	        (
	            array(&$this, '__construct'),
	            $args
	        );


		}

		function __construct($store_id , $tracking_id , $transaction) {
			$this->store_id = $store_id;
			$this->tracking_id = $tracking_id;
			$this->transaction = $transaction;
			$this->tracking_info = $this->callApi(
						AGSHOST."/payment/mobilev2/transaction/tracking.jsp",
						array(
							"storeID"=>$this->store_id,
							"trackingID"=>$this->tracking_id
						)

			);

			$this->log($this->tracking_info);
			$this->tracking_info = json_decode($this->tracking_info,true);

		}

		function setLogging($b) {
			$this->logging = $b;
		}

		function log($str) {
			//로그 구현..
			//폴더 위치는 이 파일에서 ../log
			//폴더가 없으면 생성.
			//$this->logging 이 true일 경우만 파일에 기록

			$folder = dirname(__FILE__) . "/../log";
			if (!@file_exists($folder)) {
				@mkdir($folder);
			}
			if (!$this->logfile ) {
				$this->logfile = @fopen($folder."/".date("Y-m-d").".log","a");
				if (!$this->logfile) {
					die($folder."/".date("Y-m-d").".log 파일을 생성할 수 없습니다");
				}
			}

			if ($this->logfile && $this->logging) {
				$str = date("Y-m-d H:i:s")."==>".$str."\n";
				@fwrite($this->logfile,$str);
			}

		}

		function getTrackingInfo() {
			return $this->tracking_info;
		}

		function callApi($url , $params) {

			$query = "";
			foreach($params as $key => $value) {
				$query .= $key."=".$value;
				$query .= "&";
			}
			$nurl = $url . "?" . $query;
            $this->log($nurl);


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $nurl);
			curl_setopt($ch, CURLOPT_TIMEOUT, 90);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90 );
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'AGSMobile 2.0');
			$str = curl_exec($ch);
			curl_close($ch);


			//$str = file_get_contents($nurl);
			return $str;
		}




		function approve() {

			$ret = array(
				"status"=>"error",
				"message"=>"알 수 없는 에러"
			);

            $data = array(

            );



			switch($this->transaction) {

                /* virtual */
				case "virtual" : {
					$ret["paytype"]= "virtual";

                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/virtual.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "type"=>"approve"
                        )
                    );
                    $this->log($html);
                    if ($html) {
                        $json = json_decode($html,true);

                       	if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                       	if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

	                    if ($json['Success'] != "y") {
	                            $ret["status"]="error";
	                            $ret["message"]=$json['ResMsg'];
	                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";


                            $ret["data"]=array(

                            	//아래는 전 승인 공통..
                            	"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],
                                "NetCancelId" => $json['NetCancelId'],
                                "StoreId" => $json['StoreId'],
                                "OrdNo" => $json['OrdNo'],
                                "Amt" => $json['Amt'],
                                "EscrowYn"=> $json['EscrowYn'],
                                "NoInt" => $json['DeviId'] == "9000400002" ? "y" : "n",
                                "EscrowSendNo" => $json['EscrowSendNo'],

                                "VirtualNo" => $json['VirtualNum'],         // 입금계좌번호(가상계좌번호)
                                "BankCode" => $json['BankCode'],            // 입금은행코드
                                "SuccessTime" => $json['SuccessTime'],           // 승인일자
                                "DueDate" => $json['DueDate']          // 승인일자


                            );
                        }
	              }



				};break;



                /* hp */
				case "hp" : {
					$ret["paytype"]= "hp";
                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/phone.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "type"=>"approve"
                        )
                    );
                    $this->log($html);


                    if ($html) {
                        $json = json_decode($html,true);
                        if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                        if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

                        if ($json['Success'] != "y") {
                            $ret["status"]="error";
                            $ret["message"]=$json['ResMsg'];
                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";
                            $this->netCancelId = $json['NetCancelId'];
                            $ret["data"]=array(
                            	//아래는 전 승인 공통..
                            	"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],
                                "NetCancelId" => $json['NetCancelId'],
                                "StoreId" => $json['StoreId'],
                                "OrdNo" => $json['OrdNo'],
                                "Amt" => $json['Amt'],
                                "EscrowYn"=> $json['EscrowYn'],
                                "NoInt" => $json['DeviId'] == "9000400002" ? "y" : "n",


                                "AdmTID" => $json['HpTid'],           // 결제TID
                                "PhoneCompany" => $json['HpCompany'],      // 통신사
                                "Phone" => $json['HpNumber']               // 핸드폰 번호
                            );
                        }

                    }


				};break;



                /* kmpi */
				case "kmpi" :
				case "ansim" :
				case "xansim" :
				{
					$ret["paytype"]= "card";

                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/ansim.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "type"=>"approve"
                        )
                    );
                    $this->log($html);
                    if ($html) {
                        $json = json_decode($html,true);
                        if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                        if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

                        if ($json['Success'] != "y") {
                            $ret["status"]="error";
                            $ret["message"]=$json['FailReason'];
                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";
             				$this->netCancelId = $json['NetCancelId'];
                            $ret["data"]=array(

                            	//아래는 전 승인 공통..
                            	"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],

                                "NetCancelId" => $json['NetCancelId'],
                                "StoreId" => $json['StoreId'],
                                "OrdNo" => $json['OrdNo'],
                                "Amt" => $json['Amt'],
                                "EscrowYn"=> $json['EscrowYn'],
                                "NoInt" => $json['DeviId'] == "9000400002" ? "y" : "n",
                                "EscrowSendNo" => $json['EscrowSendNo'],

                                "BusiCd" => $json['Code'],     // 전문코드
                                "AdmNo" => $json['AdmNo'],     // 승인번호
                                "AdmTime" => $json['AdmTime'], // 승인시각
                                "CardCd" => $json['CardType'], // 카드사코드
                                "CardNm" => $json['CardName'], // 카드사명
                                "DealNo" => $json['SendNo'],    // 거래번호
                                "PartialMm" => $json["CardPartialMm"]
                            );
                        }

                    }

				};break;





                /* isp */
				case "isp" : {

					$ret["paytype"]= "card";


					$html = $this->callApi(
						AGSHOST."/payment/mobilev2/transaction/isp.jsp",
						array(
							"storeID"=>$this->store_id,
							"trackingID"=>$this->tracking_id,
							"type"=>"approve"
						)
					);
					$this->log($html);

					if ($html) {
						$json = json_decode($html,true);
						if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
						if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

						if ($json['Success'] != "y") {
							$ret["status"]="error";
							$ret["message"]=$json['FailReason'];
							$ret["data"]=null;
						}else {
							$ret["status"]="ok";
							$ret["message"]="ok";
							 /*
								 변수명에 맞게 json에서 뽑아와서 data에 세팅해야함..
								 이 때 들어갈 data는 12개의 인자를 리턴함
								 나머지 필요한 정보들은 tracking_info 에서 꺼내세요.
                             */
                            $this->netCancelId = $json['NetCancelId'];
							$ret["data"]=array(
								//아래는 전 승인 공통..
								"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],

                                "NetCancelId" => $json['NetCancelId'],
                                "StoreId" => $json['StoreId'],
                                "OrdNo" => $json['OrdNo'],
                                "Amt" => $json['Amt'],
                                "EscrowYn"=> $json['EscrowYn'],
                                "NoInt" => $json['DeviId'] == "9000400002" ? "y" : "n",
                                "EscrowSendNo" => $json['EscrowSendNo'],

                                "BusiCd" => $json['Code'],     // 전문코드
                                "AdmNo" => $json['AdmNo'],     // 승인번호
                                "AdmTime" => $json['AdmTime'], // 승인시각
                                "CardCd" => $json['CardType'], // 카드사코드
                                "CardNm" => $json['CardName'], // 카드사명
                                "DealNo" => $json['SendNo'],    // 거래번호
                                "PartialMm" => $json["CardPartialMm"]
							);
						}

					}



				}break;



                /* type default */
				default : {
					$this->log("결제 타입이 잘 못 되었습니다." . $this->transaction);
					$ret["message"] = "결제 타입 에러";
					$ret["status"] = "error";
				}break;
			}

			return $ret;

		}

		function forceCancel() {
			return $this->cancel("","","",$this->netCancelId);
		}

        function cancel($AdmNo, $AdmDt, $SendNo, $NetCancID = "") {

            $ret = array(
                "status"=>"error",
                "message"=>"알 수 없는 에러"
            );

            $data = array(

            );



            switch($this->transaction) {

                /* ansim, xansim, kmpi */
                case "ansim" :
                case "xansim" :
                case "kmpi" :
                {
                    $ret["paytype"]= "card";

                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/ansim.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "admNo"=>$AdmNo,
                            "sendNo"=>$SendNo,
                            "admDt"=>$AdmDt,
                            "NetCancelId"=>$NetCancID,
                            "type"=>"cancel"
                        )
                    );
                    $this->log($html);

                    if ($html) {
                        $json = json_decode($html,true);
                        if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                        if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

                        if ($json['Success'] != "y") {
                            $ret["status"]="error";
                            $ret["message"]=$json['FailReason'];
                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";

                            $ret["data"]=array(
                                "StoreId" => $json['StoreId'],  // 업체ID
                                "AdmNo" => $json['AdmNo'],     // 승인번호
                                "AdmTime" => $json['DealTime'], // 승인시각
                                "Code" => $json['Code']        // S000 : 성공,  S001 : 기처리(이미처리된건),  E999 : 기타오류.
                           );
                        }

                    }

                };break;

                /* isp */
                case "isp" : {

                    $ret["paytype"]= "card";

                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/isp.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "admNo"=>$AdmNo,
                            "sendNo"=>$SendNo,
                            "admDt"=>$AdmDt,
                            "NetCancelId"=>$NetCancID,
                            "type"=>"cancel"
                        )
                    );
                    $this->log($html);

                    if ($html) {
                        $json = json_decode($html,true);
                        if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                        if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

                        if ($json['Success'] != "y") {
                            $ret["status"]="error";
                            $ret["message"]=$json['FailReason'];
                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";

                            $ret["data"]=array(
                            	"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],
                                "StoreId" => $json['StoreId'],  // 업체ID
                                "AdmNo" => $json['AdmNo'],     // 승인번호
                                "AdmTime" => $json['DealTime'], // 승인시각
                                "Code" => $json['Code']        // S000 : 성공,  S001 : 기처리(이미처리된건),  E999 : 기타오류.

                            );
                        }

                    }



                }break;



                /* hp */
                case "hp" : {

                    $ret["paytype"]= "hp";

                    $html = $this->callApi(
                        AGSHOST."/payment/mobilev2/transaction/phone.jsp",
                        array(
                            "storeID"=>$this->store_id,
                            "trackingID"=>$this->tracking_id,
                            "NetCancelId"=>$NetCancID,
                            "type"=>"cancel"
                        )
                    );
                    $this->log($html);

                    if ($html) {
                        $json = json_decode($html,true);
                        if (!is_array($json) || !isset($json['code'])) {
                       		return $ret;
                       	}
                        if ($json['code'] == 400) {
                       		$ret["message"] = $json['message'];
	                        $ret["status"] = "error";
	                        return $ret;
                       	}

                       	$json = $json['data'];

                        if ($json['Success'] != "y") {
                            $ret["status"]="error";
                            $ret["message"]=$json['ResMsg'];
                            $ret["data"]=null;
                        }else {
                            $ret["status"]="ok";
                            $ret["message"]="ok";

                            $ret["data"]=array(
                            	"AuthTy" => $json['AuthTy'],
                                "SubTy" => $json['SubTy'],
                                "StoreId" => $json['StoreId'],  // 업체ID
                                "AdmTime" => $json['AdmTime'], // 승인시각
                               	"AdmTID" => $json['HpTid']           // 결제TID
                            );
                        }

                    }



                }break;



                /* type default */
                default : {
                    $this->log("취소 타입이 잘 못 되었습니다.");
                    $ret["message"] = "취소 타입 에러";
                    $ret["status"] = "error";
                }break;
            }

            return $ret;

        }

	}
?>