<?php

/**
 * Google Analytics 4 Ecommerce
 **/

$currency = $cfg['currency'];
if ($currency == '원') $currency = 'KRW';

switch($_SERVER['SCRIPT_NAME']) {
    case '/shop/detail.php' :
        $data = $pdo->assoc("select hash, name, sell_prc, big from {$GLOBALS['tbl']['product']} where hash=?", array(
            $_GET['pno']
        ));

        $json = json_encode(array(
            'currency' => $currency,
            'value' => parsePrice($data['sell_prc']),
            'items' => array(array(
                'item_id' => $data['hash'],
                'item_name' => $data['name'],
                'currency' => $currency,
                'item_category' => getCateName($data['big']),
                'price' => parsePrice($data['sell_prc']),
                'quantity' => 1
            ))
        ));
        $gtag = "gtag('event', 'view_item', $json);";
    break;
    case '/shop/order.php' :
        unset($GLOBALS['cartRes']);
        $items = array();
        while ($cart = cartList()) {
            $items[] = array(
                'item_id' => $cart['hash'],
                'item_name' => $cart['name'],
                'price' => parsePrice($cart['sell_prc']),
                'quantity' => $cart['buy_ea']
            );
            $total_sell_prc += $cart['sell_prc'];
        }
        $json = json_encode(array(
            'currency' => $currency,
            'value' => parsePrice($total_sell_prc),
            'items' => $items
        ));

        $gtag = "gtag('event', 'begin_checkout', $json);";
        unset($GLOBALS['cartRes']);
    break;
    case '/shop/order_finish.php' :
        $ord = $GLOBALS['ord'];

        $items = array();
        $pres = $pdo->iterator("
            select
                p.hash, p.name, op.*
            from {$tbl['order_product']} op inner join {$tbl['product']} p on op.pno=p.no
            where ono=?
        ", array(
            $ord['ono']
        ));
        foreach ($pres as $data) {
            $items[] = array(
                'item_id' => $data['hash'],
                'item_name' => $data['name'],
                'price' => parsePrice($data['sell_prc']),
                'discount' => parsePrice(getOrderTotalSalePrc($data)),
                'quantity' => $data['buy_ea']
            );
        }

        $json = json_encode(array(
            'transaction_id' => $ord['ono'],
            'value' => parsePrice($ord['pay_prc']),
            'currency' => $currency,
            'items' => $items
        ));

        $gtag = "gtag('event', 'purchase', $json);";
        unset($GLOBALS['cartRes']);
    break;
}

?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=$cfg['ga_code']?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
	<?php if($cfg['use_ga_UserID'] == 'Y') { ?>window.dataLayer.push({'userId': '<?=$GLOBALS['member']['member_id']?>'});<?php } ?>

    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?=$cfg['ga_code']?>');

    <?=$gtag?>
</script>