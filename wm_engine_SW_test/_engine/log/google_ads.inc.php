<?php

/**
 * 구글 애드워즈 공통 스크립트
 **/

$google_ads_id = $scfg->get('google_ads_id');
$google_ads_currency = $scfg->get('currency_type');
if ($google_ads_currency == '원') $google_ads_currency = 'KRW';

switch($_SERVER['SCRIPT_NAME']) {
    case '/shop/order_finish.php' :
        $ord = $GLOBALS['ord'];

        $google_snipet_id = $scfg->get('google_ads_conv_id');
        $google_ads_value = parsePrice($ord['pay_prc']);
        $google_ads_transaction_id = $ord['ono'];
       break;
    case '/member/join_step3.php' :
        $member = $GLOBALS['member'];

        $google_snipet_id = $scfg->get('google_ads_join_id');
        $google_ads_value = '1';
        $google_ads_transaction_id = $member['member_id'];
       break;
}

?>
<!-- Global site tag (gtag.js) - Google Ads -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=$google_ads_id?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '<?=$google_ads_id?>');

    <?php if ($google_snipet_id) { ?>
    gtag('event', 'conversion', {
        'send_to': '<?=$google_snipet_id?>',
        'value': '<?=$google_ads_value?>',
        'currency': '<?=$google_ads_currency?>',
        'transaction_id': '<?=$google_ads_transaction_id?>'
    });
    <?php } ?>

    var use_google_ads = true;
    var google_ads_cart_id = '<?=$scfg->get('google_ads_cart_id')?>';
</script>