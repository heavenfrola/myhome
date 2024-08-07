<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Google 애널리틱스 수집 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	global $tbl, $pdo;

	$ord = $GLOBALS['ord'];
	$amember = $GLOBALS['member'];
	$currency = ($cfg['currency_type'] == '원') ? 'KRW' : $cfg['currency_type'];
	if($cfg['use_ga_ecommerce'] == 'Y' && $ord['ono']) {
		$ga_ono = $ord['ono'];
		$ga_affiliation = str_replace("'", '', $cfg['company_mall_name']);
		$ga_revenue = parsePrice($ord['pay_prc']);
		$ga_shipping = parsePrice(numberOnly($ord['dlv_prc'], true));

		$ga_prd = array();
		$pres = $pdo->iterator("select op.*, c.name as big from $tbl[order_product] op inner join $tbl[product] p on op.pno=p.no inner join $tbl[category] c on p.big=c.no where op.ono='$ord[ono]' and op.stat < 10");
        foreach ($pres as $data) {
			$ga_prd[] = array(
				'id' => $data['ono'],
				'name' => str_replace("'", '', stripslashes($data['name'])),
				'sku' => (($data['complex_no'] > 0) ? 'c'.$data['complex_no'] : $data['pno']),
				'category' => str_replace("'", '', stripslashes($data['big'])),
				'price' => numberOnly($data['sell_prc']),
				'quantity' => $data['buy_ea'],
			);
		}
	}

?>
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	ga('create', '<?=$cfg['ga_code']?>', 'auto');
	<?if($cfg['use_ga_UserID'] == 'Y') {?>
	ga('set', 'userId', '<?=$amember['member_id']?>');
	<?}?>
	ga('send', 'pageview');

	<?if($ga_ono && count($ga_prd) > 0) {?>
	// ecommerce
	ga('require', 'ecommerce', 'ecommerce.js');

	ga('ecommerce:addTransaction', {
	  'id': '<?=$ga_ono?>',
	  'affiliation': '<?=$ga_affiliation?>',
	  'revenue': '<?=$ga_revenue?>',
	  'shipping': '<?=$ga_shipping?>',
      'tax':'',
	  'currency':'<?=$currency?>',
	});

	<?foreach($ga_prd as $_prd) {?>
	ga('ecommerce:addItem', <?=json_encode($_prd)?>);
	<?}?>
	ga('ecommerce:send');
	<?}?>
</script>