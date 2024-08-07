<?php

/**
 * 네이버페이 결제형 : 결제창 오픈
 **/

startOrderLog($ono, 'card_pay.php');

// 결제창 옵션
$merchantUserKey = 'm'.sprintf('%06d',$member['no']);
$mode = 'normal';
$return_url = $root_url.'/main/exec.php?exec_file=card.naverSimplePay/card_pay.exe.php&sesskey='.session_id();

// 카드 로그
checkAgent();
$os = trim("$os_name $os_version");
$browser = trim("$br_name $br_version");
$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

$card_tbl = $tbl['card'];
cardDataInsert($card_tbl, 'nsp');

if ($pay_type == '27') { // 정기결제
    $mode = 'recurrent';
    $return_url = $root_url.'/main/exec.php?exec_file=card.naverSimplePay/subscription.exe.php&ono='.$ono.'&sesskey='.session_id();
    $representativ_product_code .= '_'.$now;
    $pay_prc = $tax_amount = parsePrice($ptnOrd->getData('sbscr_firsttime_pay_prc'));
    $taxfree_amount = 0;
} else { // 일괄 결제
    // 비과세 설정
    if ($taxfree_amount_sbscr) {
        $taxfree_amount = $taxfree_amount_sbscr;
    }
    if ($scfg->comp('nsp_use_tax', 'N') == true) {
        $tax_amount = $pay_prc;
        $taxfree_amount = 0;
    }
    $tax_amount = parsePrice($pay_prc-$taxfree_amount);
    $pdo->query("update $card_tbl set wm_free_price='$taxfree_amount' where wm_ono='$ono'");
}

if ($pay_prc < 100) {
    msg('최소 결제금액 100원 미만으로는 결제가 불가능합니다.');
}

// 상품 배열
$productItems = array();
$productCount = 0;
if (is_object($ptnOrd) == true) {
    while($obj = $ptnOrd->loopCart()) {
        $cart = $obj->data;
		$option = str_replace('<split_big>', '/', $cart['option']);
		$option = str_replace('<split_small>', ':', $option);

        $productItems[] = array(
            'categoryType' => 'PRODUCT',
            'categoryId' => 'GENERAL',
            'uid' => $cart['hash'],
            'name' => mb_strcut(strip_tags(trim($cart['name'].' '.$cart['option'])), 0, 128, _BASE_CHARSET_),
            'count' => (int) $cart['buy_ea'],
        );
        $productCount += $cart['buy_ea'];
    }
}
$productName = mb_strcut(strip_tags($representativ_product_name), 0, 128, _BASE_CHARSET_);

// openType
$nsp_openType = ($_SESSION['browser_type'] == 'pc') ? $scfg->get('nsp_openType') : 'page';


$pdo->query("update $card_tbl set good_name=? where wm_ono=?", array(
    $productName, $ono
));

$_SESSION['nsp_sbscr'] = $sbscr;
$_SESSION['nsp_cart_selected'] = $_POST['cart_selected'];

// 결제창 파라메터
$open = array();
if ($pay_type == '27') {
    $cfg['nsp_clientId'] = $cfg['nsp_sub_clientId'];
    $open['actionType'] = 'NEW';
    $open['productCode'] = $representativ_product_code;
} elseif ($pay_type == '25') {
    if ($sbscr == 'Y') { // 일괄결제용
        $cfg['nsp_clientId'] = $cfg['nsp_sub_clientId2'];
        $_SESSION['nsp_sbscr'] = 'A';
    }
    if ($member['no'] > 0) {
        $open['merchantUserKey'] = $merchantUserKey;
    }
    $open['merchantPayKey'] = $ono;
    $open['productItems'] = $productItems;
    $open['productCount'] = (int) $productCount;
    $open['taxScopeAmount'] = (int) parsePrice($tax_amount);
    $open['taxExScopeAmount'] = (int) parsePrice($taxfree_amount);
}
$open['productName'] = $productName;
$open['totalPayAmount'] = (int) parsePrice($pay_prc);
$open['returnUrl'] = $return_url;
$open = json_encode($open);

$log_instance->writeln($open, '[API] open');

?>
<script>
<?php if ($nsp_openType == 'popup') { ?>
parent.printFLoading();
<?php } ?>

var oPay = parent.Naver.Pay.create({
    "mode" : "production", // production
    "clientId": "<?=$cfg['nsp_clientId']?>",
    <?php if ($scfg->comp('nsp_chainId') == true) { ?>
    "chainId": "<?=$cfg['nsp_chainId']?>",
    <?php } ?>
    "payType": "<?=$mode?>",
    "openType": "<?=$nsp_openType?>",
    "useNaverAppLogin": true,
    "onAuthorize": function(r) {
        if (r.resultCode != 'Success') {
            switch(r.resultMessage) {
                case 'userCancel' :
                    r.resultMessage = '결제를 취소하셨습니다.주문 내용 확인 후 다시 결제해주세요.';
                    break;
                case 'OwnerAuthFail' :
                    r.resultMessage = '타인 명의 카드는 결제가 불가능합니다.회원 본인 명의의 카드로 결제해주세요.';
                    break;
                case 'paymentTimeExpire' :
                    r.resultMessage = '결제 가능한 시간이 지났습니다.주문 내용 확인 후 다시 결제해주세요.';
                    break;
                case 'webhookFail' :
                    r.resultMessage = '호출 응답 실패';
                    break;
            }
            window.alert(r.resultMessage);
            parent.removeFLoading();
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
        } else {
            var param = '';
            for(key in r) {
                if (key == 'returnUrl') continue;
                param += '&'+key+'='+r[key];
            }
            location.href = '<?=$return_url?>'+param;
        }
    }
});

oPay.open(<?=$open?>);
</script>