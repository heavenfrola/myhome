<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Criteo
	' +----------------------------------------------------------------------------------------------+*/

	$criteo_site_type = $_SESSION['browser_type'] == 'mobile' ? 'm' : 'd';

	switch($GLOBALS['_file_name']) {
		case 'main_index.php' :
			$tracker = array('event' => 'viewHome');
		break;
		case 'shop_big_section.php' :
		case 'shop_search_result.php' :
			$tracker = array(
				'event' => 'viewList',
				'item' => $GLOBALS['productIds']
			);
		break;
		case 'shop_detail.php' :
            $_prd = $GLOBALS['prd'];
            if ($_prd['no'] != $_prd['parent']) {
                $_prd = $pdo->assoc("select hash from {$tbl['product']} where no='{$_prd['parent']}'");
            }
			$tracker = array('event'=>'viewItem', 'item'=>$_prd['hash']);
            $criteo_npay = array(
                array('id' => $_prd['hash'], 'price' => $_prd['sell_prc'], 'quantity' => 1)
            );
		break;
		case "shop_cart.php" :
			$tracker = array('event' => 'viewBasket', 'item' => array());
			while($cart = cartList()) {
				$tracker['item'][] = array('id' => $cart['hash'], 'price' => $cart['sell_prc'], 'quantity' => $cart['buy_ea']);
			}
            $criteo_npay = $tracker['item'];
		break;
		case 'shop_order_finish.php' :
			$ono = $_SESSION['last_order'];
			$tracker = array('event' => 'trackTransaction', 'id' => $ono, 'item' => array());
			$res = $pdo->iterator("select o.pno, p.hash, o.sell_prc, o.buy_ea from {$GLOBALS['tbl']['order_product']} o inner join {$tbl['product']} p on o.pno=p.no where o.ono='$ono'");
            foreach ($res as $prd) {
				$tracker['item'][] = array('id' => $prd['hash'], 'price' => $prd['sell_prc'], 'quantity' => $prd['buy_ea']);
			}
		break;
	}

	if(is_array($tracker) == false || count($tracker) < 1) return;

	$member = $GLOBALS['member'];

?>
<script type='text/javascript' src='https://static.criteo.net/js/ld/ld.js' async='true'></script>
<script type='text/javascript'>
window.criteo_q = window.criteo_q || [];
window.criteo_q.push(
	{event:"setAccount",account:"<?=$cfg['criteo_P']?>"},
	{event:"setEmail",email:"<?=$member['email']?>"},
	{event:"setSiteType",type:"<?=$criteo_site_type?>"},
	<?=json_encode($tracker)?>
);

<?php if ($scfg->comp('criteo_npay', 'Y') == true && ($tracker['event'] == 'viewItem' || $tracker['event'] == 'viewBasket')) { ?>
<!-- Criteo 세일즈 태그 - 네이버페이 주문형 -->
window.criteo_q = window.criteo_q || [];
window.criteo_q.push({
    requiresDOM: "non-blocking", cb: function () {
        criteo_addEvent("npay_btn_pay", "click", function () {
            var item = <?=json_encode($criteo_npay)?>;
            <?php if ($tracker['event'] == 'viewItem') { ?>
            if (document.prdFrm) {
                if (document.prdFrm.buy_ea) {
                    item[0].quantity = document.prdFrm.buy_ea.value;
                }
                if ($(':input[name^=m_buy_ea]').length > 0) {
                    var quantity = 0;
                    $(':input[name^=m_buy_ea]').each(function() {
                        quantity += parseInt(this.value);
                    });
                    item[0].quantity = quantity;
                }
            }
            <?php } ?>
            if (typeof(Storage) !== "undefined") {
                criteoNpayEvent(
                    '<?=$cfg['criteo_P']?>',
                    item,
                    '<?=$member['email']?>',
                    '<?=$member['zip']?>',
                );
            }
        });
    }
});

function criteoNpayEvent(parterId, itemArray, email="", zipcode="") {
    var lastNpayTransaction = localStorage.lastNpayTransaction;
    var itemIdArray = [];
    for (item in itemArray)
    itemIdArray.push(itemArray[item].id);
    itemIdArray.sort();

    if (lastNpayTransaction !== undefined){
        var lastNpayTimestamp = lastNpayTransaction.split("||")[0];
        var lastNpayProductsArray = lastNpayTransaction.split("||")[1].split(",").sort();
        if ((Date.now() - lastNpayTimestamp)/1000/60 < 10 && criteo_arraysMatch(itemIdArray, lastNpayProductsArray))
        return;
    }

    var deviceType = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
    criteo_q.push(
        { event: "setAccount", account: parterId },
        { event: "setEmail", email: email },
        { event: "setZipcode", zipcode: zipcode },
        { event: "setSiteType", type: deviceType },
        { event: "trackTransaction", id: "npay" + Math.floor(Math.random()*99999999999), item: itemArray }
    );
    localStorage.lastNpayTransaction = Date.now() + "||" + itemIdArray.join();
}

function criteo_arraysMatch(arr1, arr2) {
    if (arr1.length !== arr2.length) return false;
    for (var i = 0; i < arr1.length; i++) {
        if (arr1[i] !== arr2[i]) return false;
    }
    return true;
};

function criteo_addEvent(className, evType, fn) {
    document.addEventListener(evType, function(e){
        if (e.target && e.target.classList.contains(className)) {
            fn();
            return true;
        }
    });
}
<!-- END Criteo 세일즈 태그 - 네이버페이 주문형 -->
<?php } ?>
</script>