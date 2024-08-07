<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Google 향상된 전자상거래 애널리틱스 수집 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	switch($_SERVER['SCRIPT_NAME']) {
		case '/shop/detail.php' :
			$detail_hash = addslashes($_GET['pno']);
			$detail_data = $pdo->assoc("select big, name from {$GLOBALS['tbl']['product']} where `hash`='$detail_hash'");
			if($detail_data['big']) {
				$big_name = getCateName($detail_data['big']);
				$big_name = str_replace("'", '', $big_name);
			}
			$detail_data['name'] = str_replace("'", '', $detail_data['name']);
			$gscript = "
			ga('create', '$cfg[ga_code]');
			ga('require', 'ec');
			ga('ec:addProduct', {
			  'id': '$detail_hash',
			  'name': '$detail_data[name]',
			  'category': '$big_name',
			  'position': 1,
			});
			ga('ec:setAction', 'detail');
			ga('send', 'pageview');";
		break;
		case "/shop/order.php" :
			$gscript = "
			ga('create', '$cfg[ga_code]');
			ga('require', 'ec');
			";
			while($cart = cartList()) {
				$cart['name'] = str_replace("'", '', $cart['name']);
				$gscript .= "
					ga('ec:addProduct', {
					  'id': '$cart[hash]',
					  'name': '$cart[name]',
					  'price': '$cart[sell_prc]',
					  'quantity': '$cart[buy_ea]'
					});
				";
			}
            unset($GLOBALS['cartRes']);
			$gscript .= "
				ga('ec:setAction', 'checkout', {
				  'step': 1
				});
				ga('send', 'pageview');
			";
		break;
		case '/shop/order_finish.php' :
			$gscript = "
			ga('create', '$cfg[ga_code]');
			ga('require', 'ec');
			";
			$ord = $GLOBALS['ord'];
			if($ord['ono']) {
				$ga_ono = $ord['ono'];
				$ga_affiliation = str_replace("'", '', $cfg['company_mall_name']);
				$ga_revenue = parsePrice($ord['pay_prc']);
				$ga_shipping = parsePrice($ord['dlv_prc']);
				$currency = ($cfg['currency_type'] == '원') ? 'KRW' : $cfg['currency_type'];

				$ga_prd = array();
				$pres = $pdo->iterator("select op.*, c.name as big, p.name as pname, p.hash from {$GLOBALS[tbl][order_product]} op inner join {$GLOBALS[tbl][product]} p on op.pno=p.no inner join {$GLOBALS[tbl][category]} c on p.big=c.no where op.ono='$ga_ono' and op.stat < 10");
                foreach ($pres as $data) {
					$total_prc = parsePrice($data['sell_prc']);
					$data['pname'] = str_replace("'", '', $data['pname']);
					$gscript .= "
						ga('ec:addProduct', {
						  'id': '$data[hash]',
						  'name': '$data[pname]',
						  'category': '$data[big]',
						  'price': '$total_prc',
						  'quantity': '$data[buy_ea]'
						});
					";
				}
				$gscript .= "
				ga('ec:setAction', 'purchase', {
				  'id': '$ga_ono',
				  'affiliation': '$ga_affiliation',
				  'revenue': '$ga_revenue',
				  'shipping': '$ga_shipping',
				  'tax':''
				});
				ga('send', 'pageview');
				";
			}
		break;
		default :
			$gscript = "
				ga('create', '$cfg[ga_code]', 'auto');
				ga('send', 'pageview');
			";
		break;
	}
?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
<?=$gscript?>

var use_google_enhenced_ecommerce = true;
var google_alanytics_id = '<?=$cfg['ga_code']?>';
</script>