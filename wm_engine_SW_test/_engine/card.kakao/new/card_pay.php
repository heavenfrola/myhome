<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 카카오페이 결제정보 전송
	' +----------------------------------------------------------------------------------------------+*/


	include_once $engine_dir."/_engine/include/common.lib.php";

	if(empty($cfg['kakao_cid'])) msg('카카오페이 설정이 잘못되었습니다 - 관리자게에 문의하세요.');

	$card_tbl = $tbl['card'];

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	if(!$member['no']) {
		$partner_user_id = $_SESSION['guest_no'];
	}else {
		$partner_user_id = $member['member_id'];
	}

	$oid = $ono;	//주문번호
	$amount = parsePrice($pay_prc, false);	//결제금액
	$good_name = strip_tags(preg_replace("/(\"|'|&)/", " ", $title));

	$adminkey  = $cfg['kaka_admin_key']; // admin 키
	$cid       = $cfg['kakao_cid']; // cid

	$req_auth = 'Authorization: KakaoAK '.$adminkey;
	$req_cont = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

	$kakao_header = array($req_auth, $req_cont);

	$approval_url = 'https://pghub.wisa.co.kr/kakaopay.php?type=appoval&ono='.$ono.'&root_url='.urlencode($root_url);
	$cancel_url = 'https://pghub.wisa.co.kr/kakaopay.php?type=cancel&ono='.$ono.'&root_url='.urlencode($root_url);
	$fail_url = 'https://pghub.wisa.co.kr/kakaopay.php?type=fail&ono='.$ono.'&root_url='.urlencode($root_url);

	$kakao_params = array(
		'cid'               => $cid,                             // 가맹점코드 10자
		'partner_order_id'  => $oid,						     // 주문번호
		'partner_user_id'   => $partner_user_id,             // id
		'item_name'         => $good_name,                       // 상품명
		'quantity'          => $buy_ea,                          // 상품 수량
		'total_amount'      => $amount,                          // 상품 총액
		'tax_free_amount'   => '0',                              // 상품 비과세 금액
		'approval_url'      => $approval_url,                    // 결제성공시 콜백url
		'cancel_url'        => $cancel_url,						// 결제취소시 콜백url
		'fail_url'          => $fail_url,						// 결제실패시 콜백url
	);

	$Result = comm('https://kapi.kakao.com/v1/payment/ready', http_build_query($kakao_params), '', $kakao_header);
	$result_json = json_decode($Result, true);

	if(!$result_json['tid']) {
		$resultCode = $result_json['code'];
		$resultMsg = $result_json['msg'];
?>
		<script type="text/javascript">
			window.alert("\n 카카오페이 인증에 실패하였습니다. : <?=$resultCode?> ( <?=$resultMsg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		</script>
<?
		exit;
	}

	cardDataInsert($card_tbl, 'kakaopay');

	$pdo->query("update `$card_tbl` set `tno`='$result_json[tid]' where `wm_ono`='$ono'");

	// app : next_redirect_app_url
	if($_SESSION['browser_type'] == 'mobile') {
		$result_url = $result_json['next_redirect_mobile_url'];
?>
		<script type="text/javascript">
			parent.location.href = '<?=$result_url?>';
		</script>
<?
	}else {
		$result_url = $result_json['next_redirect_pc_url'];
?>
		<script type="text/javascript">
			var target_url = '<?=$result_url?>';
			var scrollTop = parent.document.documentElement.scrollTop ? parent.document.documentElement.scrollTop+300 : parent.document.body.scrollTop+300;
			parent.setDimmed();
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		</script>
<?
	}
?>