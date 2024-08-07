var _UD='undefined';var _UN='unknown';
var _ace_countvar = 0;
function _IDV(a){return (typeof a!=_UD)?1:0}
if(!_IDV(_A_pl)) var _A_pl = Array(1) ;
if(!_IDV(_A_nl)) var _A_nl = Array(1) ;
if(!_IDV(_A_ct)) var _A_ct = Array(1) ;
if(!_IDV(_A_pn)) var _A_pn = Array(1) ;
if(!_IDV(_A_amt)) var _A_amt = Array(1) ;
if( ACE_CODE.length > 9  && ( typeof MK_CRL == 'undefined')){
var ACE_IDX=(parseInt(ACE_CODE.substring(10))%20)+1;
if(typeof MK_CRL == 'undefined') var MK_CRL='http://mgs'+ACE_IDX+'.acecounter.com:80/'; var MK_GCD=ACE_CODE;
if( document.URL.substring(0,8) == 'https://' ){ MK_CRL = 'https://mgs'+ACE_IDX+'.acecounter.com/logecgather/' ;};
if(!_IDV(_A_i)) var _A_i = new Image() ;if(!_IDV(_A_i0)) var _A_i0 = new Image() ;if(!_IDV(_A_i1)) var _A_i1 = new Image() ;if(!_IDV(_A_i2)) var _A_i2 = new Image() ;if(!_IDV(_A_i3)) var _A_i3 = new Image() ;if(!_IDV(_A_i4)) var _A_i4 = new Image() ;
function _RP(s,m){if(typeof s=='string'){if(m==1){return s.replace(/[#&^@,]/g,'');}else{return s.replace(/[#&^@]/g,'');}}else{return s;} };
if(!_IDV(_ll)) var _ll='';
function AEC_B_L(){var i=0;_ll=''; for( i = 0 ; i < _A_pl.length ; i ++ ){_ll += _RP(_A_ct[i])+'@'+_RP(_A_pn[i])+'@'+_A_pl[i]+'@'+_RP(_A_amt[i],1)+'@'+_RP(_A_nl[i],1)+'^';}; };
function AEC_S_F(str,md,idx){ var i = 0,_A_cart = ''; var k = eval('_A_i'+idx); md=md.toLowerCase(); if( md == 'b' || md == 'i' || md == 'o'){ _A_cart = MK_CRL+'?cuid='+MK_GCD ; _A_cart += '&md='+md+'&ll='+(str)+'&'; k.src = _A_cart;window.setTimeout('',2000);};};
}