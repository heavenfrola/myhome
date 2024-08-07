<script type="text/javascript" src="https://tx.allatpay.com/common/AllatPayM.js"></script>
<script type="text/javascript">
	// 결제페이지 호출
	function approval(sendFm) {

		try {
			$('form[name=ordFrm]').hide();
			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
		} catch(ex) {
		}

		Allat_Mobile_Approval(sendFm,0,0); /* 포지션 지정 (결제창 크기, 320*360) */
	}

	// 결과값 반환( receive 페이지에서 호출 )
	function approval_submit(result_cd,result_msg,enc_data) {
		Allat_Mobile_Close();

		if( result_cd != '0000' ){
			$('form[name=ordFrm]').show();
			alert(result_cd + " : " + result_msg);
		} else {
			sendFm.allat_enc_data.value = enc_data;

			sendFm.action = "<?=$root_url?>/main/exec.php?exec_file=card.allat/mobile/card_pay.exe.php";
			sendFm.method = "post";
			sendFm.target = "_self";
			sendFm.submit();
		}
	}
</script>

<form id='sendFm' name="sendFm" method="post">
	<input type='hidden' name="allat_shop_id" value=""> <!-- 상점 아이디 -->
	<input type='hidden' name="allat_order_no" value=""> <!-- 주문번호 -->
	<input type='hidden' name="allat_amt" value=""> <!-- 승인금액 -->
	<input type='hidden' name="allat_pmember_id" value=""> <!-- 회원ID -->
	<input type='hidden' name="allat_product_cd" value=""> <!-- 상품코드 -->
	<input type='hidden' name="allat_product_nm" value=""> <!-- 상품명 -->
	<input type='hidden' name="allat_buyer_nm" value=""> <!-- 결제자성명 -->
	<input type='hidden' name="allat_recp_nm" value=""> <!-- 수취인성명 -->
	<input type='hidden' name="allat_recp_addr" value=""> <!-- 수취인주소 -->
	<input type='hidden' name="shop_receive_url" value="<?=$root_url?>/main/exec.php?exec_file=card.allat/mobile/receive.php"> <!-- 인증정보수신URL -->
	<input type="hidden" name="allat_enc_data"> <!-- 주문정보암호화필드 -->
	<?php if(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') > -1 && strpos($_SERVER['HTTP_USER_AGENT'],'Safari') === false) { ?>
		<input type="hidden" name="allat_app_scheme" value="wisa<?=str_replace('-','',$_we['wm_key_code'])?>://"> <!-- 앱스키마 -->
	<?php } else if(strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP') > -1) { ?>
		<input type="hidden" name="allat_app_scheme" value="ANDROID"> <!-- 앱스키마 -->
	<?php } ?>
	<input type='hidden' name="allat_card_yn" value=""> <!-- 신용카드 결제 사용여부 -->
	<input type='hidden' name="allat_abank_yn" value=""> <!-- 계좌이체 결제 사용여부 -->
	<input type='hidden' name="allat_vbank_yn" value=""> <!-- 가상계좌 결제 사용여부 -->
	<input type='hidden' name="allat_hp_yn" value=""> <!-- 휴대폰 결제 사용여부 -->
	<input type='hidden' name="allat_ticket_yn" value="">
	<input type='hidden' name="allat_tax_yn" value=""> <!-- 과세여부 -->
	<input type='hidden' name="allat_sell_yn" value=""> <!-- 할부 사용여부 -->
	<input type='hidden' name="allat_zerofee_yn" value=""> <!-- 일반/무이자할부 사용여부 -->
	<input type='hidden' name="allat_bonus_yn" value="">
	<input type='hidden' name="allat_cash_yn" value="y">
	<input type='hidden' name="allat_email_addr" value=""> <!-- 결제정보 수신 E-Mail -->
	<input type='hidden' name="allat_test_yn" value=""> <!-- 테스트여부 -->
	<input type='hidden' name="allat_real_yn" value="y"> <!-- 상품 실물 여부-->
	<input type='hidden' name="allat_cardes_yn" value="n"> <!-- 신용카드 에스크로 적용여부 -->
	<input type='hidden' name="allat_abankes_yn" value="y"> <!-- 계좌이체 에스크로 적용여부 -->
	<input type='hidden' name="allat_vbankes_yn" value="n"> <!-- 가상계좌 에스크로 적용여부 -->
	<input type='hidden' name="allat_hpes_yn" value="n"> <!-- 휴대폰 에스크로 적용여부 -->
	<input type='hidden' name="allat_ticketes_yn" value=""> <!-- 상품권 에스크로 적용여부 -->
	<input type='hidden' name="allat_gender" value=""> <!-- 성별 -->
	<input type='hidden' name="allat_birth_ymd" value=""> <!-- 생년월일 -->
	<input type="hidden" name="allat_encode_type" value="U">
</form>