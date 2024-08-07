<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카카오페이 결제완료 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$wm_paydata = comm('https://pghub.wisa.co.kr/kakaopay.api.php?wm_paykey='.$_GET['wm_paykey']);
	$wm_paydata = json_decode($wm_paydata);
	$pg_token = $wm_paydata->pg_token;
	$ono = $wm_paydata->ono;

	$card_tbl = $tbl['card'];
	$card = $pdo->assoc("select * from `$card_tbl` where wm_ono='$ono'");

	$pay_tid = $card['tno'];
	$pay_ono = $card['wm_ono'];

	$card_price = parsePrice($card['wm_price']);

	if(!$member['no']) {
		$partner_user_id = $_SESSION['guest_no'];
	}else {
		$partner_user_id = $member['member_id'];
	}

	makePGLog($pay_ono, 'kakaopay start');

	//2번 중복으로 올 경우 리턴
	if($card['stat']==2) {
		makePGLog($pay_ono, 'kakaopay card stat 정상');
		exit;
	}
	$adminkey  = $cfg['kaka_admin_key']; // admin 키
	$cid       = $cfg['kakao_cid']; // cid

	$req_auth   = 'Authorization: KakaoAK '.$adminkey;
	$req_cont   = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

	$kakao_header = array($req_auth, $req_cont);

	$kakao_params = array(
		'cid'               => $cid,								// 가맹점코드 10자
		'tid'               => $pay_tid,							// 결제 고유번호. 결제준비 API의 응답에서 얻음
		'partner_order_id'  => $pay_ono,							// 주문번호. 결제준비 API에서 요청한 값과 일치해야 함
		'partner_user_id'   => $partner_user_id,                // 회원 id. 결제준비 API에서 요청한 값과 일치해야 함
		'pg_token'          => $pg_token							// 결제승인 요청을 인증하는 토큰.
	);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://kapi.kakao.com/v1/payment/approve');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($kakao_params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $kakao_header);
    $result_json = json_decode(curl_exec($ch), true);
    $result_info = curl_getinfo($ch);
    curl_close($ch);

    // 결제 실패
    if ($result_info['http_code'] != '200') {
        $layer1 = 'order1';
        $layer2 = 'order2';
        $layer3 = 'order3';
        msg("결제가 실패하였습니다.\n{$result_json['msg']}");
    }

	$ono = $result_json['partner_order_id'];//주문번호
	$tid = $result_json['tid']; // kakao 거래 고유 번호
	$cid = $result_json['cid']; // 아이디
	$aid = $result_json['aid']; // Request 고유 번호
	$goodsName = $result_json['item_name'];// 상품명
	$amt = $result_json['amount']['total']; // 결제금액
	$purchase_corp = $result_json['card_info']['kakaopay_purchase_corp']; // 매입카드사 한글명
	$purchase_corp_code = $result_json['card_info']['kakaopay_purchase_corp_code'];// 매입카드사 코드
	$payMethod = $result_json['payment_method_type']; //결제 방법 ( CARD, MONEY )
	$AuthDate = $result_json['approved_at']; //승인시간
	$approved_id = $result_json['card_info']['approved_id']; //신용카드 승인번호
	$cardInterest = $result_json['card_info']['interest_free_install']; // 신용카드 무이자 여부 ( Y: 무이자,  N : 일반)
	$cardQuota = $result_json['card_info']['install_month'];        // 신용카드 할부개월
	$cardBin = $result_json['card_info']['bin'];         // 신용카드 번호

	//검증
	if($result_json['aid'] && $result_json['amount']['total']==$card_price) {
		$new_kakao_pay = 'Y';

		$pdo->query("update `$card_tbl` set `stat`='2' ,`card_cd`='$purchase_corp_code' ,`card_name`='$purchase_corp' ,`app_time`='$AuthDate' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$cardQuota' ,`res_cd`='0000' ,`res_msg`='success' ,`ordr_idxx`='$ono' ,`tno`='$tid' ,`good_mny`='$amt' ,`good_name`='$goodsName' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$payMethod', `pg_version`='$cfg[kakao_version]'  where `wm_ono`='$ono'");

		makePGLog($ono, 'kakaopay success');

		$card_pay_ok=true;
		include_once $engine_dir."/_engine/order/order2.exe.php";

		if($_SESSION['browser_type'] == 'mobile') {
		?>
		<script type="text/javascript">
			parent.location.replace("<?=$root_url?>/shop/order_finish.php");
			//self.close();
		</script>
		<?
		}else {
?>
		<script type="text/javascript">
			parent.location.replace("<?=$root_url?>/shop/order_finish.php");
		</script>
<?
		}
		exit;
	}else{
		if($result_json['amount']['total']!=$card_price) {
		   // 결제 실패시 DB처리 하세요.
			$pdo->query("update `$card_tbl` set `card_cd`='$purchase_corp_code' ,`card_name`='$purchase_corp' ,`app_time`='$AuthDate' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$cardQuota' ,`res_cd`='$resultCode' ,`res_msg`='verify fail' ,`ordr_idxx`='$pay_ono' ,`tno`='$tid' ,`good_mny`='$amt' ,`good_name`='$goodsName' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$payMethod', `pg_version`='$cfg[kakao_version]'  where `wm_ono`='$pay_ono'");

			// 카드결제 실패 처리
			$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$pay_ono' and stat != 2");
			$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$pay_ono' and stat != 2");

			makePGLog($pay_ono, 'kakaopay verify failed');

			//결제 취소 처리
			$card = get_info($tbl[card], "no", $card['no']);

			$adminkey  = $cfg['kaka_admin_key']; // admin 키
			$cid       = $cfg['kakao_cid']; // cid

			$_chk_price = parsePrice($card['wm_price'], false);

			$req_auth   = 'Authorization: KakaoAK '.$adminkey;
			$req_cont   = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

			$kakao_header = array($req_auth, $req_cont);

			$kakao_params = array(
				'cid'						=> $cid,                            // 가맹점코드 10자
				'tid'						=> $card['tno'],         // 결제 고유번호. 결제준비 API의 응답에서 얻을 수 있음
				'cancel_amount'				=> $_chk_price,    // 가맹점 주문번호. 결제준비 API에서 요청한 값과 일치해야 함
				'cancel_tax_free_amount'    => '0'		//취소 비과세 금액
			);

			$Result = comm('https://kapi.kakao.com/v1/payment/cancel', http_build_query($kakao_params), '', $kakao_header);
			$result_json = json_decode($Result, true);

			$respcode = $result_json['code'];
			$respmsg = $result_json['msg'];
			$stat = 1;
			$rtn_etc = array();

			if($result_json['aid']){//취소성공
				$pdo->query("update `$card_tbl` set `stat`='3' where `no`='$card[no]'");
				$stat=2;
			}

			$pdo->query("
				insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `price`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
				values ('$card[no]', '$stat', '$card[wm_ono]', '$card[tno]', '$_chk_price', '$respcode', '$respmsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
			");

			if($_SESSION['browser_type'] == 'mobile') {
				msg('검증에 실패하였습니다 \n\n 다시 결제하기를 클릭하세요\n', '/shop/order.php');
			}else {
?>
				<script type="text/javascript">
					window.alert("\n 검증에 실패하였습니다 \n\n 다시 결제하기를 클릭하세요\n");
                    parent.removeDimmed();
                    parent.layTgl3('order1', 'Y');
                    parent.layTgl3('order2', 'N');
                    parent.layTgl3('order3', 'Y');
				</script>
<?
			}
			exit;
		}else {
			$resultMsg = $result_json['method_result_message'];
			$resultCode = $result_json['code'];

			// 카드결제 실패 처리
			$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$pay_ono' and stat != 2");
			$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$pay_ono' and stat != 2");

			makePGLog($pay_ono, 'kakaopay failed');

			if($_SESSION['browser_type'] == 'mobile') {
				msg('결제가 실패하였습니다.\n 다시 결제하기를 클릭하세요\n', '/shop/order.php');
			}else {
?>
				<script type="text/javascript">
					window.alert("\n 결제가 실패하였습니다 : <?=$resultCode?> ( <?=$resultMsg?> )\n\n 다시 결제하기를 클릭하세요\n");
                    parent.removeDimmed();
                    parent.layTgl3('order1', 'Y');
                    parent.layTgl3('order2', 'N');
                    parent.layTgl3('order3', 'Y');
				</script>
<?
			}
			exit;
		}
	}
?>