<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 리스트 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$cart_sum_price = 0;

	$rURL = getListURL('big_section');
	if(!$rURL) $rURL = $root_url;

	$sbscr = ($_GET['sbscr']=='Y')? 'Y':'N';
	if($sbscr=='Y') {
		include_once $engine_dir."/_engine/cart/cart_sbscr.php";
		return;
	}

	$quickcart = numberOnly($_REQUEST['quickcart']);
	if($quickcart == 1 || $quickcart == 2) {
		$_tmp_file_name = 'shop_cart_layer'.$quickcart.'.php';
		$striplayout = $_GET['striplayout'] = 1;
		$cfg['checkout_id'] = ''; // 체크아웃 스크립트 충돌로 제외

		include_once $engine_dir."/_engine/common/skin_index.php";
		return;
	}


	common_header();

	// 에이스 카운터
	if($cfg['ace_counter_gcode'] && $cfg['ace_counter_Ver'] != '2') {
		?>
		<!-- AceCounter eCommerce (Cart_Inout) v3.0 Start -->
		<script type='text/javascript'>
		var EU_URL='http://'+'dgc1.acecounter.com:5454/';var EL_CODE='<?=$cfg['ace_counter_gcode']?>';if( document.URL.substring(0,8) == 'https://' ){ EU_URL = 'https://dgc1.acecounter.com/logecgather' ;};
		if(typeof AEC_iob =='undefined') var AEC_iob = new Image() ;if(typeof AEC_iob0 =='undefined') var AEC_iob0 = new Image();if(typeof AEC_iob1 =='undefined') var AEC_iob1 = new Image();if(typeof AEC_iob2 =='undefined') var AEC_iob2 = new Image();if(typeof AEC_iob3 =='undefined') var AEC_iob3 = new Image();if(typeof AEC_iob4 =='undefined') var AEC_iob4 = new Image();
		function AEC_REPL(s,m){if(typeof s=='string'){if(m==1){return s.replace(/[#&^@,]/g,'');}else{return s.replace(/[#&^@]/g,'');}}else{return s;} };
		function AEC_F_D(prodid,mode,cnum){ var i = 0 , prod_amt = 0 , prod_num = 0 ; var prod_cate = '' ,prod_name = '' ; prod_num = cnum ;if(mode == 'I' ) mode = 'i' ;if(mode == 'O' ) mode = 'o' ;if(mode == 'B' ) mode = 'b' ; if( mode == 'b' || mode == 'i' || mode == 'o' ){ for( i = 0 ; i < _AEC_prodidlist.length ; i ++ ){ if( _AEC_prodidlist[i] == prodid ){ prod_name = AEC_REPL(_AEC_prodname[i]); prod_amt = ( parseInt(AEC_REPL(_AEC_amtlist[i],1)) / parseInt(AEC_REPL(_AEC_numlist[i],1)) ) * prod_num ; prod_cate = AEC_REPL(_AEC_category[i]); _AEC_argcart = EU_URL+'?cuid='+EL_CODE; _AEC_argcart += '&md='+mode+'&ll='+escape(prod_cate+'@'+prod_name+'@'+prod_amt+'@'+prod_num+'^&'); break;};};if(_AEC_argcart.length > 0 ) AEC_iob.src = _AEC_argcart;setTimeout("",2000);};};
		function AEC_D_A(){ var i = 0,_AEC_str= ''; var ind = 0; for( i = 0 ; i < _AEC_prodidlist.length ; i ++ ){ _AEC_str += AEC_REPL(_AEC_category[i])+'@'+AEC_REPL(_AEC_prodname[i])+'@'+AEC_REPL(_AEC_amtlist[i],1)+'@'+AEC_REPL(_AEC_numlist[i],1)+'^'; if(  escape(_AEC_str).length > 800 ){ if(ind > 4) ind = 0; _AEC_str = escape(_AEC_str)+'&cmd=on' ; AEC_S_F(_AEC_str , 'o', ind) ; _AEC_str = '' ; ind++; }; }; if( _AEC_str.length > 0 ){ if(ind+1 > 4) ind = 0; AEC_S_F(escape(_AEC_str) , 'o', ind+1) ; }; };
		function AEC_B_A(){var i=0,_AEC_str='',_AEC_argcart='';var ind = 0;_AEC_argcart = EU_URL+'?cuid='+EL_CODE+'&md=b';for( i = 0 ; i < _AEC_prodidlist.length ; i ++ ){_AEC_str += ACE_REPL(_AEC_category[i])+'@'+ACE_REPL(_AEC_prodname[i])+'@'+ACE_REPL(_AEC_amtlist[i],1)+'@'+ACE_REPL(_AEC_numlist[i],1)+'^';if(escape(_AEC_str).length > 800 ){if(ind > 4) ind = 0;_AEC_str = escape(_AEC_str)+'&cmd=on';AEC_S_F(_AEC_str,'b',ind); _AEC_str = '' ;ind++;};}; if( _AEC_str.length > 0 ){if(ind+1 > 4) ind = 0; AEC_S_F(escape(_AEC_str),'b',ind+1);};};
		function AEC_U_V(prodid,bnum){ var d_cnt = 0 ; var A_amt = 0 ; var A_md = 'n' ;var _AEC_str = '' ; for( j = 0 ; j < _AEC_prodidlist.length; j ++ ){ if( _AEC_prodidlist[j] == prodid ){ d_cnt = 0; if( _AEC_numlist[j] != bnum ){ d_cnt = bnum - parseInt(AEC_REPL(_AEC_numlist[j],1)) ; A_amt = Math.round( parseInt(AEC_REPL(_AEC_amtlist[j],1)) / parseInt(AEC_REPL(_AEC_numlist[j],1))); if( d_cnt > 0 ){ A_md = 'i' ; }else{ A_md = 'o' ;};_AEC_amtlist[j] = A_amt*Math.abs(d_cnt) ; _AEC_numlist[j] = Math.abs(d_cnt);_AEC_str += AEC_REPL(_AEC_category[j])+'@'+AEC_REPL(_AEC_prodname[j])+'@'+AEC_REPL(_AEC_amtlist[j],1)+'@'+AEC_REPL(_AEC_numlist[j],1)+'^';}}};if( _AEC_str.length > 0 ){ AEC_S_F(escape(_AEC_str) ,A_md, j);};};
		function AEC_S_F(str,mode,idx){ var i = 0,_AEC_argcart = ''; var k = eval('AEC_iob'+idx); if(mode == 'I' ) mode = 'i' ; if(mode == 'O' ) mode = 'o' ; if(mode == 'B' ) mode = 'b' ; if( mode == 'b' || mode == 'i' || mode == 'o'){ _AEC_argcart = EU_URL+'?cuid='+EL_CODE ; _AEC_argcart += '&md='+mode+'&ll='+(str)+'&'; k.src = _AEC_argcart;window.setTimeout('',2000);};};

			if( typeof _AEC_prodidlist == 'undefined' ) var _AEC_prodidlist = Array(1) ;
			if( typeof _AEC_numlist == 'undefined' ) var _AEC_numlist = Array(1) ;
			if( typeof _AEC_category == 'undefined' ) var _AEC_category = Array(1) ;
			if( typeof _AEC_prodname == 'undefined' ) var _AEC_prodname = Array(1) ;
			if( typeof _AEC_amtlist == 'undefined' ) var _AEC_amtlist = Array(1) ;
		</script>
		<!-- AceCounter eCommerce (Cart_InOut) v3.0 Start -->
		<?php
		$_ace_counter_cart=1;
	}

	if(!$usable_emoney) $usable_emoney = 0;

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js?20200630"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.prdcpn.js?20200630"></script>
<script type="text/javascript">
	var ace_counter_cart = '<?=$_ace_counter_cart?>';
	var order_cpn_paytype='<?=$cfg['order_cpn_paytype']?>';
	var order_milage_paytype='<?=$cfg['order_milage_paytype']?>';
	var order_cpn_milage='<?=$cfg['order_cpn_milage']?>';
	var usable_emoney=<?=$usable_emoney?>;
	var prdprc_sale=0;
	var delivery_type = '<?=$cfg['delivery_type']?>';
	var exchangeRate = '<?=$exchangeRate?>';
</script>
<script type="text/javascript">
$(document).ready(function(){
	if($('form[name=cartFrm]').find(":checkbox").length>0) {
		$('form[name=cartFrm]').find(":checkbox").attrprop('checked', true);
	}
	$('form[name=cartFrm]').find(":checkbox").change(function() {
		cartLiveCalc(this.form);
	});
});
</script>
<?php if($nvcpa) { ?>
<!-- 네이버 CPA 스크립트 -->
    <script type='text/javascript'>
        if (!wcs_add) var wcs_add={};
        wcs_add["wa"] = "<?=trim($cfg['ncc_AccountId'])?>";
        var _nasa={};
        if (window.wcs) {
            _nasa["cnv"] = wcs.cnv("3", "1");
            wcs_do(_nasa);
        }
    </script>
<?php } ?>
<?php

include_once $engine_dir.'/_engine/common/skin_index.php';

?>