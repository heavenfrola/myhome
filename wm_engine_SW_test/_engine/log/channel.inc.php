<?PHP

	if($GLOBALS['_this_pop_up']) return;

	$amember = $GLOBALS['member'];
	$m_id = $amember['member_id'];
	$m_name = stripslashes($amember['name']);
	$m_cell = $amember['cell'];
	$m_total_ord = (int) $amember['total_ord'];
	$m_total_prc = (int) $amember['total_prc'];
	$m_group = stripslashes($pdo->row("select name from {$GLOBALS['tbl']['member_group']} where no='$amember[level]'"));
	$m_milage = (int) $amember['milage'];
	$m_emoney = (int) $amember['emoney'];
    $m_cpns = ($amember['no'] > 0) ?
        $pdo->row("
            select count(*) from {$tbl['coupon_download']}
            where
                member_no='{$amember['no']}'
                and member_id='{$amember['member_id']}'
                and use_date=0 and (ufinish_date >= '".date('Y-m-d')."' || ufinish_date = '')
        ") : 0;
    $m_cart_rows = ($amember['no'] > 0) ? $pdo->row("select count(*) from {$tbl['cart']} where member_no='{$amember['no']}'") : 0;
    $m_wish_rows = ($amember['no'] > 0) ? $pdo->row("select count(*) from {$tbl['wish']} where member_no='{$amember['no']}'") : 0;

    // 장바구니 데이터
    $cartdata = '';
    $_cartres = $pdo->iterator(
        "select p.name, c.buy_ea
            from {$tbl['cart']} c inner join {$tbl['product']} p on c.pno=p.no
            where 1 ".mwhere()."
            order by c.no desc limit 3
    ");
    foreach ($_cartres as $data) {
        if ($cartdata) $cartdata .= ', ';
        $cartdata .= str_replace('"', '', stripslashes(strip_tags($data['name'])));
        if ($data['buy_ea'] > 1) $cartdata .= '('.$data['buy_ea'].')';
    }

    // 최근 주문 상품
    $orderdata1 = $orderdata2 = '';
    $tidx = 1;
    if ($member['no'] > 0) {
        $_ordres = $pdo->iterator("select title, date1, pay_prc from {$tbl['order']} where member_no='{$member['no']}' order by no desc limit 2");
        foreach ($_ordres as $data) {
            $tempdata  = date('Y-m-d', $data['date1']);
            $tempdata .= ' | '.str_replace('"', '', stripslashes(strip_tags($data['title'])));
            $tempdata .= ' | '.parsePrice($data['pay_prc'], true).' '.$cfg['currency'];

            ${'orderdata'.$tidx} = $tempdata;
            $tidx++;
        }
    }
    $unsubscribeEmail = (isset($amember['mailing']) == 'Y') ? $amember['mailing'] : 'N';
    $unsubscribeSms = (isset($amember['sms']) == 'Y') ? $amember['sms'] : 'N';
    $lastCheckoutCompleteAt = '0000-00-00 00:00:00';
    if ($amember['no']) {
        $__date = $pdo->row("select max(date1) from {$tbl['order']} where member_no='{$amember['no']}' and member_id='{$amember['member_id']}' and stat < 10");
        if ($__date) {
            $lastCheckoutCompleteAt = date('Y-m-d H:i:s', $__date);
        }
    }

?>
<!-- Channel Plugin Scripts -->
<script>
	window.channelPluginSettings = {
		"plugin_id": "<?=$cfg['channel_plugin_id']?>"
	};
	window.channelPluginSettings.user = {
		"id": "<?=$m_id?>",
		"name": "<?=$m_name?>",
		"mobileNumber": "<?=$m_cell?>",
		"meta": {
            <?php if (empty($amember['nick']) == false) { ?>
            "닉네임": "<?=$amember['nick']?>",
            <?php } ?>
            "cart_list": "<?=$cartdata?>",
            "recent_ord1": "<?=$orderdata1?>",
            "recent_ord2": "<?=$orderdata2?>",
			"order_count": "<?=$m_total_ord?>",
			"order_amount": "<?=$m_total_prc?>",
			"login_count": "<?=$amember['total_con']?>",
			"member_group": "<?=$m_group?>",
			"마일리지": "<?=$m_milage?> <?=$cfg['currency_type']?>",
			"예치금": "<?=$m_emoney?> <?=$cfg['currency_type']?>",
            "coupon_count": "<?=$m_cpns?>",
            "cart_count": "<?=$m_cart_rows?>",
            "wish_count": "<?=$m_wish_rows?>",
            "unsubscribeEmail": "<?=$unsubscribeEmail?>",
            "unsubscribeSms": "<?=$unsubscribeSms?>",
            "lastCheckoutCompleteAt": "<?=$lastCheckoutCompleteAt?>"
		}
	};

	(function() {
		$('body').append("<div id='ch-plugin'></div>");
		var async_load = function() {
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = '//cdn.channel.io/plugin/ch-plugin-web.js';
			s.charset = 'UTF-8';
			var x = document.getElementsByTagName('script')[0];
			x.parentNode.insertBefore(s, x);
		};
		if (window.attachEvent) {
			window.attachEvent('onload', async_load);
		} else {
			window.addEventListener('load', async_load, false);
		}
	})();
</script>
<!-- End Channel Plugin -->