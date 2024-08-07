<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Acecounter / mobile
	' +----------------------------------------------------------------------------------------------+*/

	switch($GLOBALS['_file_name']) {
		case 'shop_detail.php' :
			$prd = $GLOBALS['prd'];;
			$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['name'])));
			$_cate = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($pdo->row("select name from `{$GLOBALS['tbl']['category']}` where no='$prd[big]'"))));
			$_prc = numberOnly($prd['sell_prc']);

			$subjs = 'product_detail.js';
			$subdata = "
			<script language='javascript'>
			var m_pd ='$_pname';
			var m_ct ='$_cate';
			var m_amt='$_prc';
			</script>
			";
		break;
		case 'shop_cart.php' :
			$idx = 0;
			$subdata = "<script type='text/javascript'>var cart_pno = new Array()</script>";
			while($cart = cartList()) {
				$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($cart['name'])));
				$_cate = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($pdo->row("select name from `{$GLOBALS['tbl']['category']}` where no='$cart[big]'"))));
				$_total_prc = $cart['sell_prc']*$cart['buy_ea'];

				$subdata .= "
				<script type='text/javascript'>
				cart_pno[$idx] = $cart[pno];
				var AM_Cart=(function(){
					var c={pd:'$cart[pno]',pn:'$_pname',am:'$_total_prc',qy:'$cart[buy_ea]',ct:'$_cate'};
					var u=(!AM_Cart)?[]:AM_Cart; u[c.pd]=c;return u;
				})();
				</script>
				";
				$idx++;
			}
		break;
		case 'shop_order_finish.php' :
			$ono = $_SESSION['last_order'];
			if($_SESSION['ace_order_finish_'.$ono] != true) {
				$_SESSION['ace_order_finish_'.$ono] = true;
				$res = $pdo->iterator("select p.name, p.pno, p.total_prc, p.buy_ea, c.name as cname from {$GLOBALS[tbl][order_product]} p inner join `{$GLOBALS[tbl][product]}` p2 on p.pno=p2.no inner join `{$GLOBALS[tbl][category]}` c on p2.big=c.no where p.ono='$ono'");
                foreach ($res as $prd) {
					$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['name'])));
					$_cname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['cname'])));

					$subdata .= "
					<script type='text/javascript'>
					var AM_Cart=(function(){
						var c={pd:'$prd[pno]',pn:'$_pname',am:'$prd[total_prc]',qy:'$prd[buy_ea]',ct:'$_cname'};
						var u=(!AM_Cart)?[]:AM_Cart; u[c.pd]=c;return u;
					})();
					</script>
					";
				}

				$subdata .= "
				<script type='text/javascript'>
				var m_order_code='$ono';
				var m_buy='finish';
				</script>
				";
			}
		break;
	}

	$subdata = str_replace("\t", '', $subdata);

?>
<script language='javascript'> var ACE_MCODE="<?=$cfg['ace_counter_gcode_m']?>"; </script>

<?if($subjs) echo "<script type='text/javascript' src='$GLOBALS[engine_url]/_engine/log/acecounter/$subjs'></script>";?>
<?=$subdata?>

<script language='javascript'>
	if( typeof ACE_MCODE != 'undefined' ){ var _ACC_CD=String(location.hostname);var _ACC_P=location.port;var _ACC_UD='';if(_ACC_P!=''){_ACC_UD=_ACC_CD+":"+_ACC_P}else{_ACC_UD=_ACC_CD}
	var _AceGID=(function(){var Inf=[_ACC_CD,_ACC_UD,ACE_MCODE,'CM','0','NaPm,Ncisy','ALL','0']; var _CI=(!_AceGID)?[]:_AceGID.val;var _N=0;if(_CI.join('.').indexOf(Inf[3])<0){ _CI.push(Inf);  _N=_CI.length; } return {o: _N,val:_CI}; })();
	var _AceCounter=(function(){var G=_AceGID;if(G.o!=0){var _A=G.val[G.o-1];var _G=( _A[0]).substr(0,_A[0].indexOf('.'));var _C=(_A[7]!='0')?(_A[2]):_A[3];	var _U=( _A[5]).replace(/\,/g,'_');var _S=((['<scr','ipt','type="text/javascr','ipt"></scr','ipt>']).join('')).replace('tt','t src="'+location.protocol+ '//cr.acecounter.com/Mobile/AceCounter_'+_C+'.js?gc='+_A[2]+'&py='+_A[4]+'&up='+_U+'&rd='+(new Date().getTime())+'" t');document.writeln(_S); return _S;} })();	}
</script>