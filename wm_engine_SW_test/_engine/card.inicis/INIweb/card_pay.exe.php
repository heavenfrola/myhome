<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIweb 결제결과 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	$card_array = array(
					"01"=>"외환", "03"=>"롯데", "04"=>"현대", "06"=>"국민", "11"=>"BC", "12"=>"삼성", "13"=>"LG",
					"14"=>"신한", "15"=>"한미", "16"=>"NH", "17"=>"하나SK", "21"=>"해외비자", "22"=>"해외마스터", "23"=>"JCB", "24"=>"해외아멕스", "25"=>"해외다이너스"
					);

	$bank_array = array(
					"03" => "기업은행", "04" => "국민은행", "05" => "외환은행", "07" => "수협중앙회", "11" => "농협중앙회", "20" => "우리은행", "23" => "SC제일은행",
					"31" => "대구은행", "32" => "부산은행", "34" => "광주은행", "37" => "전북은행", "39" => "경남은행", "53" => "한국씨티은행", "71" => "우체국",
					"81" => "하나은행", "88" => "통합 신한은행 (신한,조흥은행)", "89" => "케이뱅크"
					);

	function objToArr($data) {
		if(is_object($data)) {
			foreach (get_object_vars($data) as $key => $val) {
				$ret[$key] = objToArr($val);
			}
			return $ret;
		}elseif(is_array($data)) {
			foreach ($data as $key => $val) {
				$ret[$key] = objToArr($val);
			}
			return $ret;
		}else{
			return $data;
		}
	}

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	// 라이브러리 인클루드 *
	require_once($engine_dir."/_engine/card.inicis/INIweb/libs/INIStdPayUtil.php");
	require_once($engine_dir."/_engine/card.inicis/INIweb/libs/HttpClient.php");

    if (isset($_REQUEST['orderNumber']) == true) {
        startOrderLog($_REQUEST['orderNumber'], 'card_pay.exe.php');
    }

	$util = new INIStdPayUtil();

	 if (strcmp("0000", $_REQUEST["resultCode"]) == 0) {//성공

		//############################################
		// 1.전문 필드 값 설정(***가맹점 개발수정***)
		//############################################

		$mid = $_REQUEST["mid"];     // 가맹점 ID 수신 받은 데이터로 설정

		$signKey = $cfg['card_inicis_key']; // 가맹점에 제공된 키(이니라이트키) (가맹점 수정후 고정) !!!절대!! 전문 데이터로 설정금지

		$timestamp = $util->getTimestamp();   // util에 의해서 자동생성

		$charset = "UTF-8";        // 리턴형식[UTF-8,EUC-KR](가맹점 수정후 고정)

		$format = "JSON";        // 리턴형식[XML,JSON,NVP](가맹점 수정후 고정)
		// 추가적 noti가 필요한 경우(필수아님, 공백일 경우 미발송, 승인은 성공시, 실패시 모두 Noti발송됨) 미사용
		//String notiUrl	= "";

		$authToken = $_REQUEST["authToken"];   // 취소 요청 tid에 따라서 유동적(가맹점 수정후 고정)

		$authUrl = $_REQUEST["authUrl"];    // 승인요청 API url(수신 받은 값으로 설정, 임의 세팅 금지)

		$netCancel = $_REQUEST["netCancelUrl"];   // 망취소 API url(수신 받은f값으로 설정, 임의 세팅 금지)

		///$mKey = $util->makeHash(signKey, "sha256"); // 가맹점 확인을 위한 signKey를 해시값으로 변경 (SHA-256방식 사용)
		$mKey = hash("sha256", $signKey);

		//#####################
		// 2.signature 생성
		//#####################
		$signParam["authToken"] = $authToken;  // 필수
		$signParam["timestamp"] = $timestamp;  // 필수
		// signature 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
		$signature = $util->makeSignature($signParam);


		//#####################
		// 3.API 요청 전문 생성
		//#####################
		$authMap["mid"] = $mid;   // 필수
		$authMap["authToken"] = $authToken; // 필수
		$authMap["signature"] = $signature; // 필수
		$authMap["timestamp"] = $timestamp; // 필수
		$authMap["charset"] = $charset;  // default=UTF-8
		$authMap["format"] = $format;  // default=XML


		try {

			$httpUtil = new HttpClient();

			//#####################
			// 4.API 통신 시작
			//#####################

			$authResultString = "";
			if ($httpUtil->processHTTP($authUrl, $authMap)) {
				$authResultString = $httpUtil->body;
			} else {
				//echo "Http Connect Error\n";
				msg($httpUtil->errormsg);

				throw new Exception("Http Connect Error");
			}

			//############################################################
			//5.API 통신결과 처리(***가맹점 개발수정***)
			//############################################################

			$resultMap = json_decode($authResultString, true);

			if (strcmp("0000", $resultMap["resultCode"]) == 0) {
				/*                         * ***************************************************************************
				 * 여기에 가맹점 내부 DB에 결제 결과를 반영하는 관련 프로그램 코드를 구현한다.

				  [중요!] 승인내용에 이상이 없음을 확인한 뒤 가맹점 DB에 해당건이 정상처리 되었음을 반영함
				  처리중 에러 발생시 망취소를 한다.
				 * **************************************************************************** */

				// 수신결과를 파싱후 resultCode가 "0000"이면 승인성공 이외 실패
				// 가맹점에서 스스로 파싱후 내부 DB 처리 후 화면에 결과 표시
				// payViewType을 popup으로 해서 결제를 하셨을 경우
				// 내부처리후 스크립트를 이용해 opener의 화면 전환처리를 하세요
				//throw new Exception("강제 Exception");

				$data = objToArr($resultMap);

                if (is_object($log_instance) == true) {
                    $log_instance->writeln(print_r($data, true), 'inicis data');
                }

				$ono = $data['MOID'];
				$ordr_idxx	=$ono;

				// 공통정보
				$app_time = strtotime($data['applDate'].$data['applTime']); // 결제승인일시
				$tno = $data['tid'];
				$pay_prc = $data['TotPrice']; // 총 결제금액
				$good_name = $data['goodsName']; // 상품명
				$res_msg = $data['resultMsg']; // 결제 처리 메시지
				$res_cd = $data['resultCode']; // 결제 처리 코드
				$wm_res_cd = $data['resultCode']; // 쇼핑몰용 결제 처리 코드
				$buyr_name = $data['buyerName']; // 구매자명
				$buyr_tel1 = $data['buyerTel']; // 수령자 전화번호
				$use_pay_method = $data['payMethod']; // 결제코드

				// 카드정보
				$card_cd = $data['CARD_Num']; // 카드 번호

				$card_name = $card_array[$data['CARD_Code']]."카드";// 카드명

				$noinf = ($data['CARD_Interest']==0?1:2); // 무이자 여부 1 : 무이자, 2 : 일반
				$quota = $data['CARD_Quota']; // 할부개월
				$app_no = $data['applNum']; // 결제기관 승인번호

				$hpp_corp = $data['HPP_Corp']; // 휴대폰 통신사

				$use_pay_method = $data['payMethod'];
				$card_tbl=($use_pay_method == "VBank") ? $tbl[vbank] : $tbl[card];

				if($use_pay_method == "VBank") {
					$depositor = $data['ACT_Name']; // 예금주
					$bank_code = $data['VACT_BankCode']; // 은행코드
					$bankname = $data['vactBankName']; // 은행명
					$account = $data['VACT_Num']; // 입금할 가상계좌
					$bank="$bankname $account $depositor";

					$pdo->query("update `$card_tbl` set `stat`='2' ,`bankname`='$bankname' ,`account`='$account',  `depositor`='$depositor' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method', `bank_code`='$bank_code' where `wm_ono`='$ono'");

					?>
					<script type="text/javascript">
					parent.document.getElementById('bank_list_span').innerHTML='<input type="hidden" name="bank" value="<?=$bank?>">';
					</script>
					<?
				} else {
					$pdo->query("update `$card_tbl` set `stat`='2' ,`card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono'");
				}

			}else {
				//#####################
				// 망취소 API
				//#####################
                /*
				$netcancelResultString = ""; // 망취소 요청 API url(고정, 임의 세팅 금지)
				if ($httpUtil->processHTTP($netCancel, $authMap)) {
					$netcancelResultString = $httpUtil->body;
				} else {
					//echo "Http Connect Error\n";
					msg($httpUtil->errormsg);

					throw new Exception("Http Connect Error");
				}

				//echo "## 망취소 API 결과 ##";

				$netcancelResultString = str_replace("<", "&lt;", $$netcancelResultString);
				$netcancelResultString = str_replace(">", "&gt;", $$netcancelResultString);
                */

				$data = objToArr($resultMap);
                $netcancelResultString = $data['resultMsg'];

				$pdo->query("update `$card_tbl` set `card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$netcancelResultString' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono' and stat!=2");
				?>
				<script type="text/javascript">
					alert("\n 결제가 실패하였습니다 : <?=$res_cd?> ( <?=$res_msg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
					cancelf=parent.document.pay_cFrm;
					cancelf.ono.value='<?=$ono?>';
					cancelf.mode.value='fail';
					cancelf.submit();
                    parent.layTgl3('order1', 'Y');
                    parent.layTgl3('order2', 'N');
                    parent.layTgl3('order3', 'Y');
				</script>
				<?
				exit;
				// 취소 결과 확인
			}
		} catch (Exception $e) {
			//####################################
			// 실패시 처리(***가맹점 개발수정***)
			//####################################
			//---- db 저장 실패시 등 예외처리----//
			$s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
			$pdo->query("update `$card_tbl` set `card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$s' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono'");
			?>
			<script type="text/javascript">
				alert("\n 결제가 실패하였습니다 : <?=$res_cd?> ( <?=$res_msg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
				cancelf=parent.document.pay_cFrm;
				cancelf.ono.value='<?=$ono?>';
				cancelf.mode.value='fail';
				cancelf.submit();
                parent.layTgl3('order1', 'Y');
                parent.layTgl3('order2', 'N');
                parent.layTgl3('order3', 'Y');
			</script>
			<?
			exit;
		}


	}else {//실패
		if(!$res_cd && $_POST['resultCode']) $res_cd = $_POST['resultCode'];
		if(!$res_msg && $_POST['resultMsg']) $res_msg = $_POST['resultMsg'];

		$pdo->query("update `$card_tbl` set `card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono'");
		?>
		<script type="text/javascript">
			alert("\n 결제가 실패하였습니다 : <?=$res_cd?> ( <?=$res_msg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
			cancelf=parent.document.pay_cFrm;
			cancelf.ono.value='<?=$ono?>';
			cancelf.mode.value='fail';
			cancelf.submit();
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		</script>
		<?
		exit;

	}
	$card_pay_ok=true;
	include_once $engine_dir."/_engine/order/order2.exe.php";
	exit;

?>