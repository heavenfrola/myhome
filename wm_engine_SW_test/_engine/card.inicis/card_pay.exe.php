<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 결제 결과 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	extract($_POST);

	$card_array = array(
					"01"=>"외환", "03"=>"롯데", "04"=>"현대", "06"=>"국민", "11"=>"BC", "12"=>"삼성", "13"=>"LG",
					"14"=>"신한", "21"=>"해외비자", "22"=>"해외마스터", "23"=>"JCB", "24"=>"해외아멕스", "25"=>"해외다이너스"
					);

	$bank_array = array(
					"03" => "기업은행", "04" => "국민은행", "05" => "외환은행", "07" => "수협중앙회", "11" => "농협중앙회", "20" => "우리은행", "23" => "SC제일은행",
					"31" => "대구은행", "32" => "부산은행", "34" => "광주은행", "37" => "전북은행", "39" => "경남은행", "53" => "한국씨티은행", "71" => "우체국",
					"81" => "하나은행", "88" => "통합 신한은행 (신한,조흥은행)"
					);

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	checkBasic();

	// 라이브러리 인클루드
	require $engine_dir."/_engine/card.inicis/INIpay41Lib.php";

	function hconv($str) {
		return mb_convert_encoding($str, 'euc-kr', _BASE_CHARSET_);
	}

	define('__pg_card_pay.exe__', $_POST['oid']);
	makePGLog($_POST['oid'], 'INIPay Start');

	$ono = addslashes($_POST['oid']);
	$ord = $pdo->assoc("select * from $card_tbl where wm_ono='$ono'");
	$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ono'");

	// INIpay41 클래스의 인스턴스 생성
	$inipay = new INIpay41;

	// 지불 정보 설정
	$inipay->m_inipayHome	= file_exists($root_dir.'/_data/INIpay41') ? $root_dir.'/_data/INIpay41' : $root_dir.'/INIpay41';
	$inipay->m_type			= "securepay";					// 고정 (절대 수정 불가)
	$inipay->m_pgId			= "INIpay".$pgid;				// 고정 (절대 수정 불가)
	$inipay->m_subPgIp		= "203.238.3.10";				// 고정 (절대 수정 불가)
	$inipay->m_keyPw		= $cfg['card_key_password'];	// 키패스워드(상점아이디에 따라 변경)
	$inipay->m_debug		= "true";						// 로그모드("true"로 설정하면 상세로그가 생성됨.)
	$inipay->m_mid			= $mid;							// 상점아이디
	$inipay->m_uid			= $uid;							// INIpay User ID (절대 수정 불가)
	$inipay->m_uip			= getenv("REMOTE_ADDR");		// 고정 (절대 수정 불가)
	$inipay->m_goodName		= hconv($goodname);				// 상품명
	$inipay->m_currency		= $currency;					// 화폐단위
	$inipay->m_price		= $price;						// 결제금액
	$inipay->m_buyerName	= hconv($buyername);			// 구매자 명
	$inipay->m_buyerTel		= $buyertel;					// 구매자 연락처(휴대폰 번호 또는 유선전화번호)
	$inipay->m_buyerEmail	= $buyeremail;					// 구매자 이메일 주소
	$inipay->m_payMethod	= $paymethod;					// 지불방법 (절대 수정 불가)
	$inipay->m_encrypted	= $encrypted;					// 암호문
	$inipay->m_sessionKey	= $sessionkey;					// 암호문
	$inipay->m_url			= $cfg['card_site_url'];		// 실제 서비스되는 상점 SITE URL로 변경할것
	$inipay->m_cardcode		= $cardcode;					// 카드코드 리턴
	$inipay->m_ParentEmail	= $parentemail;					// 보호자 이메일 주소(핸드폰 , 전화결제시에 14세 미만의 고객이 결제하면  부모 이메일로 결제 내용통보 의무, 다른결제 수단 사용시에 삭제 가능)


	// 수취인 정보
	$inipay->m_recvName = hconv($recvname);						// 수취인 명
	$inipay->m_recvTel = $recvtel;								// 수취인 연락처
	$inipay->m_recvAddr = hconv($recvaddr);						// 수취인 주소
	$inipay->m_recvPostNum = $recvpostnum;						// 수취인 우편번호
	$inipay->m_recvMsg = hconv($recvmsg);						// 전달 메세지


	// 가상계좌입금내역수신모듈 설정
	$inipay->m_returntype = ""; // URL 통보 방식 : U , java 데몬 수신: J , window 데몬 수신 :W
	$inipay->m_returnurl = "";  // URL 로 전달 받기 위한 상점 수신 URL
	$inipay->m_returnip = "";   // tcp/ip 통신을 통한 전달을 받기 위한 상점 IP
	$inipay->m_returnport = "";  // tcp/ip 통신을 통한 전달을 받기 위한 상점 port

	// 비과세 설정
	$ini_tax = parsePrice(floor(($card['wm_price']-$card['wm_free_price'])/11));
	$ini_tax_free_prc = parsePrice($card['wm_free_price']);
	$inipay->m_merchantReserved1 = "Tax={$ini_tax}&TaxFree={$ini_tax_free_prc}";

	// 지불 요청
	$inipay->startAction();

	foreach($inipay as $key => $val) {
		$inipaylog .= "[$key] $val\n;";
	}
	makePGLog($_POST['oid'], 'INIpay rcv completed', $inipaylog);

	// 결제  결과                                                    						*
	$ono = $inipay->m_moid;
	$ordr_idxx	=$ono;

	$card_cd	= $inipay->m_cardCode;
	if($card_array[$inipay->m_cardCode]) $card_name = $card_array[$inipay->m_cardCode]."카드";
	$quota		= $inipay->m_cardQuota;

	$app_no		= $inipay->m_authCode;
	$app_time	= $inipay->m_pgAuthDate.$inipay->m_pgAuthTime;
	$noinf		= $inipay->m_quotaInterest;

	$res_cd		= $inipay->m_resultCode;
	$res_msg	= mb_convert_encoding($inipay->m_resultMsg, 'utf-8', 'euc-kr');
	$tno		= $inipay->m_tid;
	$good_mny	= $inipay->m_resultprice;

	$use_pay_method = $inipay->m_payMethod;

	$rcode=$inipay->m_resultCode;

	$res_msg=addslashes($res_msg);

	if($rcode!="00") {
		$pdo->query("update `$card_tbl` set `card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono'");

		// 카드결제 실패 처리
		$pdo->query("update `$tbl[order]` set `stat`=31 where stat=11 and `ono`='$ono'");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where stat=11 and `ono`='$ono'");

		makePGLog($ono, 'INIpay failed');

		?><script type="text/javascript">
			window.alert("\n 결제가 실패하였습니다 : <?=$res_cd?> ( <?=$res_msg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
			cancelf = parent.document.pay_cFrm;
			cancelf.ono.value = '<?=$ono?>';
			cancelf.mode.value = 'fail';
			cancelf.submit();
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		</script><?
		exit;
	} else {
		if($use_pay_method == "VBank"){ // 2007-08-23 : 가상계좌 - Han
			$bankname=$bank_array[$inipay->m_vcdbank];
			$account=$inipay->m_vacct;
			$depositor=hconv($inipay->m_nminput);
			$bank_code=$inipay->m_vcdbank;
			$bank="$bankname $account $depositor";
			$pdo->query("update `$card_tbl` set `stat`='2' ,`bankname`='$bankname' ,`account`='$account',  `depositor`='$depositor' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method', `bank_code`='$bank_code' where `wm_ono`='$ono'");

			?><script type="text/javascript">
			parent.document.getElementById('bank_list_span').innerHTML='<input type="hidden" name="bank" value="<?=$bank?>">';
			</script><?
		}else{
			$pdo->query("update `$card_tbl` set `stat`='2' ,`card_cd`='$card_cd' ,`card_name`='$card_name' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$quota' ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ordr_idxx' ,`tno`='$tno' ,`good_mny`='$good_mny' ,`good_name`='$goodname' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method'  where `wm_ono`='$ono'");
		}

		makePGLog($ono, 'INIpay success');
	}

	$card_pay_ok=true;
	include_once $engine_dir."/_engine/order/order2.exe.php";
	exit;
?>
<script type="text/javascript">
	var of=parent.document.ordFrm;
	of.ono.value='<?=$ono?>';
	window.onload=new function(){
		of.submit();
	}
</script>