<?PHP

	$fbpage = $_SERVER['SCRIPT_NAME'];
	$currency = $cfg['currency_type'];
	if($currency == '원') $currency = 'KRW';

	if($fbpage == '/shop/detail.php' && $GLOBALS['prd']) {
		$pno = $GLOBALS['prd']['hash'];
		if($GLOBALS['prd']['no'] != $GLOBALS['prd']['parent']) {
			$_parent = numberOnly($GLOBALS['prd']['parent']);
			$pno = $pdo->row("select hash from $tbl[product] where no='$_parent'");
		}
		$sell_prc = parsePrice($GLOBALS['prd']['sell_prc']);
	}

	if($fbpage == '/shop/search_result.php') {
		$search_str = trim(addslashes($_GET['search_str']));
	}

	if($fbpage == '/shop/order_finish.php' && $GLOBALS['ord']) {
		$pay_prc = parsePrice($GLOBALS['ord']['pay_prc']);
		$ono = addslashes($GLOBALS['ord']['ono']);
		$order_pnos = '';
		$res = $pdo->iterator("select p.hash from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no where o.ono='$ono'");
        foreach ($res as $tmp) {
			if($order_pnos) $order_pnos .= ',';
			$order_pnos .= "'$tmp[hash]'";
		}
	}

    if($fbpage == '/shop/order.php' || ($fbpage == '/main/exec.php' && $_REQUEST['exec_file'] == 'cart/cart.exe.php')) {
		$ptnOrd = new OrderCart();
		$order_pnos = '';
		while($cart = cartList()) {
			$ptnOrd->addCart($cart);
			if($order_pnos) $order_pnos .= ',';
			$order_pnos .= "'$cart[hash]'";
		}
		$ptnOrd->complete();
        unset($GLOBALS['cartRes']);

		$pay_prc = $ptnOrd->getData('pay_prc');
		$ono = 'npay_'.date('ymdHis').'_'.sprintf('%04d', rand(0,9999)); // 가상 주문번호
	}

?>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', "<?=$cfg['fb_pixel_id']?>");
fbq('track', 'PageView');

<?php if($fbpage == '/shop/detail.php') { ?>
fbq('track', 'ViewContent', {content_ids:'<?=$pno?>', content_type:'product', value:<?=$sell_prc?>, currency:'<?=$currency?>'});
<?php } ?>

<?php if($fbpage == '/shop/search_result.php') { ?>
fbq('track', 'Search', {search_string:'<?=$search_str?>', content_type:'product'});
<?php } ?>

<?php if($fbpage == '/shop/order.php') { ?>
fbq('track', 'InitiateCheckout', {content_ids:[<?=$order_pnos?>]});
<?php } ?>

<?php if($fbpage == '/shop/order_finish.php' || ($fbpage == '/main/exec.php' && $_REQUEST['exec_file'] == 'cart/cart.exe.php')) { ?>
fbq('track', 'Purchase', {content_ids:[<?=$order_pnos?>], content_type:'product', value:<?=$pay_prc?>, currency:'<?=$currency?>'});
<?php } ?>
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?=$cfg['fb_pixel_id']?>&ev=PageView&noscript=1"
/></noscript>
<!-- DO NOT MODIFY -->
<!-- End Facebook Pixel Code -->