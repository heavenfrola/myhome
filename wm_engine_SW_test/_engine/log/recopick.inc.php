<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  recopick(레코픽)
	' +----------------------------------------------------------------------------------------------+*/

	if($_SESSION['browser_type']=='pc') {
		$recopick_show_url = $cfg['recopick_url'];
	}else {
		$recopick_show_url = $cfg['m_recopick_url'];
	}

	switch($GLOBALS['_file_name']) {
		case 'main_index.php' :
			$script = "recoPick('sendLog', 'visit');";
		break;
		case 'shop_detail.php' :
			$detail_hash = addslashes($_GET['pno']);
			$reco_data = $pdo->assoc("select big, mid, small from {$GLOBALS[tbl][product]} where `hash`='$detail_hash'");
			if($reco_data['big']) {
				$big_name = $pdo->row("select name from {$GLOBALS[tbl][category]} where no='$reco_data[big]'");
			}
			if($reco_data['mid']) {
				$mid_name = $pdo->row("select name from {$GLOBALS[tbl][category]} where no='$reco_data[mid]'");
			}
			if($reco_data['small']) {
				$small_name = $pdo->row("select name from {$GLOBALS[tbl][category]} where no='$reco_data[small]'");
			}
			$script = "
			recoPick('sendLog', 'view', {id:'{$detail_hash}', c1:'{$big_name}', c2:'{$mid_name}', c3:'{$small_name}'});
			recoPick('widget', '{$cfg[recopick_id]}');";
		break;
		case "shop_cart.php" :
			while($cart = cartList()) {
				$cart_hash = $pdo->row("select `hash` from {$GLOBALS[tbl][product]} where no='$cart[pno]'");
				$data  .= ($data  == "" ? "{id: '$cart_hash', count: $cart[buy_ea]}" : ", {id: '$cart_hash', count: $cart[buy_ea]}");
			}
			$script = "recoPick('sendLog', 'basket', {$data});";
		break;
		case 'shop_order_finish.php' :
			$ono = $_SESSION['last_order'];
			$res = $pdo->iterator("select pno, total_prc, buy_ea from {$GLOBALS[tbl][order_product]} where ono='$ono'");
            foreach ($res as $prd) {
				$ord_hash = $pdo->row("select `hash` from {$GLOBALS[tbl][product]} where no='$prd[pno]'");
				$prd['total_prc'] = parsePrice($prd['total_prc']);
				$data .= ($data == "" ? "{id: '$ord_hash', count: $prd[buy_ea], total_sales: $prd[total_prc]}" : ", {id: '$ord_hash', count: $prd[buy_ea], total_sales: $prd[total_prc]}");
			}
			$script = "recoPick('sendLog', 'order', {$data});";
		break;
		case 'shop_search_result.php' :
			$search_str = str_replace("'", '', $_GET['search_str']);
			$search_str = str_replace('"', '', $_GET['search_str']);
			$script = "recoPick('sendLog', 'search', '{$search_str}');";
		break;
		default :
			$script = "recoPick('sendLog', 'visit');";
		break;
	}

	if(!$script) return;
?>
<script type='text/javascript'>
(function(w,d,n,s,e,o) {
	w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)};
	e=d.createElement(s);e.async=1;e.charset='utf-8';e.src='//static.recopick.com/dist/production.min.js';
	o=d.getElementsByTagName(s)[0];o.parentNode.insertBefore(e,o);
})(window, document, 'recoPick', 'script');
recoPick('service', '<?=$recopick_show_url?>');
<?=$script?>
</script>