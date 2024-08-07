<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Acecounter / PC
	' +----------------------------------------------------------------------------------------------+*/

	switch($GLOBALS['_file_name']) {
		case 'shop_detail.php' :
			$prd = $GLOBALS['prd'];;
			$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['name'])));
			$_cate = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($pdo->row("select name from `{$GLOBALS['tbl']['category']}` where no='$prd[big]'"))));
			$_prc = numberOnly($prd['sell_prc']);

			$subjs = 'product_detail.js';
			$subdata = "
			<script type='text/javascript'>
			_pd =_RP('$_pname');
			_ct =_RP('$_cate');
			_amt = _RP('$_prc',1);

			_A_amt[_ace_countvar]=$_prc;
			_A_nl[_ace_countvar]=1;
			_A_pl[_ace_countvar]='$prd[no]';
			_A_pn[_ace_countvar]='$_pname';
			_A_ct[_ace_countvar]='$_cate';
			_ace_countvar++;
			</script>
			";
		break;
		case 'shop_cart.php' :
			$subjs = 'cart_inout.js';
			while($cart = cartList()) {
				$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($cart['name'])));
				$_cate = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($pdo->row("select name from `{$GLOBALS['tbl']['category']}` where no='$cart[big]'"))));
				$_total_prc = $cart['sell_prc']*$cart['buy_ea'];

				$subdata .= "
				<script type='text/javascript'>
				_A_amt[_ace_countvar]='$_total_prc';
				_A_nl[_ace_countvar]='$cart[buy_ea]';
				_A_pl[_ace_countvar]='$cart[pno]';
				_A_pn[_ace_countvar]='$_pname';
				_A_ct[_ace_countvar]='$_cate';
				_ace_countvar++;
				</script>
				";
			}
		break;
		case 'shop_order_finish.php' :
			$subjs = 'order_finish.js';

			$ono = $_SESSION['last_order'];
			$res = $pdo->iterator("select p.name, p.pno, p.total_prc, p.buy_ea, c.name as cname from {$GLOBALS[tbl][order_product]} p inner join `{$GLOBALS[tbl][product]}` p2 on p.pno=p2.no inner join `{$GLOBALS[tbl][category]}` c on p2.big=c.no where p.ono='$ono'");
            foreach ($res as $prd) {
				$_pname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['name'])));
				$_cname = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($prd['cname'])));

				$subdata .= "
				<script type='text/javascript'>
					_A_amt[_ace_countvar]='$prd[total_prc]';
					_A_nl[_ace_countvar]='$prd[buy_ea]';
					_A_pl[_ace_countvar]='$prd[pno]';
					_A_pn[_ace_countvar]='$_pname';
					_A_ct[_ace_countvar]='$_cname';
					_ace_countvar++;
				</script>
				";
			}

			$ord = $GLOBALS['ord'];

			$subdata .= "
			<script type='text/javascript'>
			var _amt = '$ord[total_prc]' ;
			var _orderno = '$ord[ono]' ;
			if(typeof AEC_B_L=='function') AEC_B_L();
			</script>
			";
		break;
		case 'shop_search_result.php' :
			$_search_key = preg_replace('/\'|"|\n|\r/', '', strip_tags(stripslashes($_GET['search_str'])));
			$subdata = "
			<script language='javascript'>
			   var _skey='$_search_key';
			</script>
			";
		break;
	}

	$subdata = str_replace("\t", '', $subdata);

?>
<script language='javascript'> var ACE_CODE="<?=$cfg['ace_counter_gcode']?>"; </script>

<?if($subjs) echo "<script type='text/javascript' src='$GLOBALS[engine_url]/_engine/log/acecounter/$subjs'></script>";?>
<?=$subdata?>

<script language='javascript'>
if( typeof ACE_CODE == 'undefined' ){ var ACE_CODE = '' ;} if( ACE_CODE.length > 9  && ( typeof CMK_GUL == 'undefined')){
	var ACE_IDX=(parseInt(ACE_CODE.substring(10))%20)+1; var CMK_GUL = 'mgs'+ACE_IDX+'.acecounter.com';
	var _AceGID=(function(){var Inf=[CMK_GUL,'80',ACE_CODE,'CW','0','NaPm,Ncisy','ALL','0']; var _CI=(!_AceGID)?[]:_AceGID.val;var _N=0;var _T=new Image(0,0);if(_CI.join('.').indexOf(Inf[3])<0){ _T.src =( location.protocol=="https:"?"https://"+Inf[0]:"http://"+Inf[0]+":"+Inf[1]) +'/?cookie'; _CI.push(Inf);  _N=_CI.length; } return {o: _N,val:_CI}; })();
	var _AceCounter=(function(){var G=_AceGID;if(G.o!=0){var _A=G.val[G.o-1];var _G=( _A[0]).substr(0,_A[0].indexOf('.'));var _C=(_A[7]!='0')?(_A[2]):_A[3];	var _U=( _A[5]).replace(/\,/g,'_');var _S=((['<scr','ipt','type="text/javascr','ipt"></scr','ipt>']).join('')).replace('tt','t src="'+location.protocol+ '//cr.acecounter.com/Web/AceCounter_'+_C+'.js?gc='+_A[2]+'&py='+_A[4]+'&gd='+_G+'&gp='+_A[1]+'&up='+_U+'&rd='+(new Date().getTime())+'" t');document.writeln(_S); return _S;} })();
}
</script>