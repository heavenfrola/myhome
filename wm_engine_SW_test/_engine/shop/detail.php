<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$ano = numberOnly($_GET['ano']);
	$pno = addslashes($_GET['pno']);
	$cno1 = numberOnly($_GET['cno1']);
	if($ano) {
		getTsPrd(" and `no`='$ano'");
		$prd = checkPrd($pdo->row("select hash from $tbl[product] where no='$ano'"));
	}
	else {
		getTsPrd(" and `hash`='$pno'");
		$prd = checkPrd($pno);
	}
	$prd['pno'] = $prd['no'];
	$_prd_cache[$prd['no']]=$prd; // qna,review 연동

	// 성인 인증 필요 상품
	if ($scfg->comp('use_kcb', 'Y') && $prd['adult'] == 'Y') {
		memberOnly();

		require_once __ENGINE_DIR__ . '/_engine/member/kcb/lib.php';
		$is_adult = is_adult();
		if (is_null($is_adult)) {
			msg('성인인증 이후 이용할 수 있습니다.', 'back');
		} else if (!$is_adult) {
			msg('미성년자는 접근할 수 없는 상품입니다.', 'back');
		}
	}

	// 노출 위치
	if(isset($admin['level']) == false || $admin['level'] < 1 || $admin['level'] > 3) {
		if($cfg['use_prd_perm'] == 'Y' && $prd['perm_dtl'] != 'Y') {
			$check_url1 = parse_url($_SERVER['HTTP_REFERER']);
			$check_url2 = parse_url(getURL());
			if($check_url1['host'] != $check_url2['host']) {
				$rURL = $root_url;
			} else {
				$rURL = 'back';
			}
            javac("
                if(typeof parent.removequickDetailPopup != 'undefined') parent.removequickDetailPopup();
            ");
            if ($_GET['ref'] == 'set') {
    			msg('선택하신 상품은 별도 구매가 불가능합니다', 'close');
            } else {
    			msg(__lang_shop_info_denyPrd__, $rURL, 'back');
            }
		}
	}

	if($cno1) {
		$_cno1 = getCateInfo($cno1);
	}

	getPrdMyLevel(1); // 상품 회원권한별 접근

	if($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) {
		$prd['milage'] = $prd['sell_prc'] * ($cfg['milage_type_per']/100);
	}

	$prd['name']=stripslashes($prd['name']);
	$prd['milage']=parsePrice($prd['milage'], true);
	$prd['sell_prc_str']=parsePrice($prd['sell_prc'], true);
	$prd['normal_prc']=parsePrice($prd['normal_prc'], true);

	$cart_cnt = $pdo->row("select count(*) from `{$tbl['cart']}` where 1 ".mwhere());
	if($cfg['use_sbscr'] == 'Y') {
		$sbscr_cart_cnt = $pdo->row("select count(*) from `{$tbl['sbscr_cart']}` where 1 ".mwhere());
	}

	if($cfg['use_prc_consult'] != 'Y') {
		$prd['sell_prc_consultation'] = '';
		$prd['detail_sell_prc_consultation'] = '';
	}

	if(!$prd['min_ord'] || $prd['min_ord']<1) $prd['min_ord']=1;
	$opt_no=0;

	// 배송비
	$prd['free_dlv'] = $prd['free_delivery'];
	$prdCart = new OrderCart(array(
		'is_detail' => true
	));
	$prdCart->addCart($prd);
	$prdCart->complete();
	$prdCart->pay_prc -= $prdCart->dlv_prc;
	$objCart = $prdCart->loopCart();

	$prd['delivery_prc'] = $prdCart->dlv_prc;
	$free_delivery = ($prdCart->free_dlv_prc > 0) ? 'Y' : 'N';
    $prdCart->sale2 = ($prdCart->sale2-$prdCart->sale2_dlv); // 무료배송 이벤트금액 제외

	$prd['all_milage'] = $prdCart->getData('total_milage'); // 총 적립금
	$prd['member_milage'] = $prdCart->getData('member_milage'); // 회원 적립금
	$prd['event_milage'] = $prdCart->getData('event_milage'); // 이벤트 적립금
	$prd['prd_milage'] = $prd['all_milage'] - $prd['member_milage'] - $prd['event_milage']; // 할인 적용 이후의 상품 적립금

	// 재고 있음
	if($prd['stat']==2) $prd['stack_ok']=1;

	// 상품평 권한
	$ra=reviewAuth('product_review_auth');

	// 상품 질문 권한
	$qa=reviewAuth('product_qna_auth');

	// 조회
	if(empty($_SESSION["cmp_hitted"]) == true || strpos($_SESSION["cmp_hitted"], '_'.$prd['no']) === false) {
		$_SESSION["cmp_hitted"]=$_SESSION["cmp_hitted"]."_".$prd['parent'];
		ctrlPrdHit($prd['parent'],"hit_view","+1");

		if($cfg['today_click_use'] == 'Y' && !$_GET['startup']) {
            $_click_prd = explode('_', trim($_COOKIE['click_prd'], '_'));
            if (in_array($prd['parent'], $_click_prd) == false) {
                array_push($_click_prd, $prd['parent']);
				$_skin = getSkinCfg();
				$count = ($_skin['recent_view_total'] < 10) ? $_skin['recent_view_total'] : 10;
                if (count($_click_prd) > $count) {
                    array_shift($_click_prd);
                }
            }

            $_new_click = implode('_', $_click_prd);
			setcookie('click_prd', $_new_click, 0, '/');

			$_click_prd = getClickPrd($_new_click);
        }
	}
	if($prd['partner_no']>0) {
		$content_number = "_".$prd['partner_no'];
		$prd_content_use = getWMDefault(array("ptn_content_use".$content_number));
		if($prd_content_use["ptn_content_use".$content_number]=="Y") {
			$_prd_content = getWMDefault(array("content3".$content_number, "content4".$content_number, "content5".$content_number));
			if(count($_prd_content)>0) {
				$prd_content['content3'] = $_prd_content["content3".$content_number];
				$prd_content['content4'] = $_prd_content["content4".$content_number];
				$prd_content['content5'] = $_prd_content["content5".$content_number];
			}
		}else {
			$prd_content = getWMDefault(array("content3","content4","content5"));
		}
	}else {
		$prd_content = getWMDefault(array("content3","content4","content5"));
	}

	$_cp[103]=$prd['small'];
	$_cp[102]=$prd['mid'];
	$_cp[101]=$prd['big'];

	if (!$prd['prd_type']) $prd['prd_type'] = '1';

	// 이전 다음 상품 2006-09-11
	function prdNextPrev($next=1,$rtype="") {
		global $prd,$tbl,$cfg,$_cp,$_cate_colname,$root_url, $_cno1, $pdo;

		$ctype = $_cno1['ctype'];
		if(!$_cno1['no']) return;
		if(!$ctype) $ctype = 1;

		if($ctype == 2) {
			$w .= " and ebig like '%@$_cno1[no]%'";
		} elseif($ctype == 4 || $ctype == 5) {
			$w .= " and `".$_cate_colname[$ctype][$_cno1['level']]."`='$_cno1[no]'";
			$w .= " and wm_sc=0";
		} else {
			for($x = 1; $x < 4; $x++) {
				$ii = $x+100;
				if($_cate_colname[$ctype][$x]) {
					$w .= " and `".$_cate_colname[$ctype][$x]."`='".$_cp[$ii]."'";
				}
				if($_cp[$ii]==$_GET['cno1']) break;
			}
		}

		$sort_key = 'edt_date';
		$oby = $next == 1 ? 'asc' : 'desc';
		$bh = $next == 1 ? '>=' : '<=';

		if($ctype) {
			if($ctype == '2') $sort_key = 'sort'.$_cno1['no'];
			if($ctype == '4' || $ctype == '5') {
				$oby = ($oby == 'asc') ? 'desc' : 'asc';
				$bh  = ($bh  == '>=') ? '<=' : '>=';
			} else {
				$_sort = $pdo->assoc("select `query` from `$tbl[product_sort]` where `no`='$cfg[prd_sort_def]'");
				if(!$_sort['query']) {
					$_sort['query'] = 'reg_date desc';
				}
				list($sort_key, $sort_dir) = explode(' ', $_sort['query']);
				$sort_key = str_replace('`', '', $sort_key);
				$sort_dir = trim($sort_dir) == '' ? 'asc' : trim($sort_dir);

				if($sort_key == 'edt_date' && $sort_dir == 'desc') {
					$sort_key = 'sort'.$_cate_colname[1][$_cno1['level']];
				}
				$oby = ($oby == 'asc') ? 'desc' : 'asc';
				$bh  = ($bh  == '>=') ? '<=' : '>=';
			}
		}

		$stat = '2,3';
		if(empty($cfg['prd_sort_soldout']) || $cfg['prd_sort_soldout'] == 'N') $stat = '2';

		if(($ctype == 1 || $ctype == 4 || $ctype == 5) && $sort_key == 'edt_date') {
			$w .= " and edt_date $bh '$prd[edt_date]'";
			//$sort2 = ", edt_date $oby";
		}

        $_sort_key = preg_replace('/binary\((.*)\)/', '$1', $sort_key);
		$data = $pdo->assoc("select * from `$tbl[product]` where no!='$prd[no]' and `stat` in ($stat) and `$_sort_key` $bh '$prd[$_sort_key]' $w order by $sort1 `$_sort_key` $oby $sort2 limit 0,1");
		$detail_url = $_GET['type'] ? "$root_url/main/exec.php?exec_file=shop/quickDetail.exe.php&type=$_GET[type]&frameno=$_GET[frameno]&" : "$root_url/shop/detail.php?";

		if($rtype) $r = $data;
		else {
			if($data['hash']) {
				if($_GET['exec_file'] == 'shop/quickDetail.inc.php') $r = "<a href='#' onclick=\"parent.quickDetailPopup(this, $data[no], '$_cno1[no]'); return false;\">";
				else $r="<a href=\"{$detail_url}&pno=$data[hash]&cno1=".$_GET['cno1']."&ctype=".$_GET['ctype']."\">";
			}
			else $r="<wisamall2006 ";
		}
		return $r;
	}

	if($_use['fast_sql'] == "Y") {
		if(!$prd['sc']) $prd['rev_cnt'] = $pdo->row("select count(*) from `$tbl[review]` where stat in (2,3) and `pno`='$prd[no]'");
	}
	else {
		$parent_no=($prd['parent'])? $prd['parent']:$prd['no'];
		$prd['rev_cnt'] = $pdo->row("select count(*) from `wm_product` p inner join `$tbl[review]` r on p.`no`=r.`pno` where p.`stat` in (2,3) and p.`no` = '$parent_no' and r.`stat` in (2,3)");
		$prd['qna_cnt'] = $pdo->row("select count(*) from `wm_product` p inner join `$tbl[qna]` q on p.`no`=q.`pno` where p.`stat` in (2,3) and p.`no` = '$parent_no'");
	}

	if($_GET['single_module']) {
		include_once $engine_dir."/_engine/common/skin_index.php";
		return;
	}

	$totalOptNum=$pdo->row("select count(*) from `$tbl[product_option_set]` where `stat`=2 and `pno`='$prd[parent]'"); // 2007-03-20 : 옵션갯수 - Han
	common_header();

	if(!$cfg['product_review_con_strlen']) $cfg['product_review_con_strlen'] = 0;

	// 에이스카운터(구버전)
	if($cfg['ace_counter_gcode'] && $cfg['ace_counter_Ver'] != '2'){
		$prd['cate_name']=$pdo->row("select `name` from `$tbl[category]` where `no`='$prd[big]' limit 1");
?>
<!-- AceCounter eCommerce (Product_Detail) v3.0 Start -->
<script type='text/javascript'>
var EU_URL='http://'+'dgc1.acecounter.com:5454/';var EL_CODE='<?=$cfg[ace_counter_gcode]?>';if( document.URL.substring(0,8) == 'https://' ){ EU_URL = 'https://dgc1.acecounter.com/logecgather' ;};
if(typeof AEC_iob =='undefined') var AEC_iob = new Image() ;if(typeof AEC_iob0 =='undefined') var AEC_iob0 = new Image();if(typeof AEC_iob1 =='undefined') var AEC_iob1 = new Image();if(typeof AEC_iob2 =='undefined') var AEC_iob2 = new Image();if(typeof AEC_iob3 =='undefined') var AEC_iob3 = new Image();if(typeof AEC_iob4 =='undefined') var AEC_iob4 = new Image();
function AEC_REPL(s,m){if(typeof s=='string'){if(m==1){return s.replace(/[#&^@,]/g,'');}else{return s.replace(/[#&^@]/g,'');}}else{return s;} };
function AEC_F_D(prodid,mode,cnum){ var i = 0 , prod_amt = 0 , prod_num = 0 ; var prod_cate = '' ,prod_name = '' ; prod_num = cnum ;if(mode == 'I' ) mode = 'i' ;if(mode == 'O' ) mode = 'o' ;if(mode == 'B' ) mode = 'b' ; if( mode == 'b' || mode == 'i' || mode == 'o' ){ for( i = 0 ; i < _AEC_prodidlist.length ; i ++ ){ if( _AEC_prodidlist[i] == prodid ){ prod_name = AEC_REPL(_AEC_prodname[i]); prod_amt = ( parseInt(AEC_REPL(_AEC_amtlist[i],1)) / parseInt(AEC_REPL(_AEC_numlist[i],1)) ) * prod_num ; prod_cate =  AEC_REPL(_AEC_category[i]); _AEC_argcart = EU_URL+'?cuid='+EL_CODE; _AEC_argcart += '&md='+mode+'&ll='+escape(prod_cate+'@'+prod_name+'@'+prod_amt+'@'+prod_num+'^&'); break;};};if(_AEC_argcart.length > 0 ) AEC_iob.src = _AEC_argcart;setTimeout("",2000);};};

	if( typeof _AEC_prodidlist == 'undefined' ) var _AEC_prodidlist = Array(1) ;
	if( typeof _AEC_numlist == 'undefined' ) var _AEC_numlist = Array(1) ;
	if( typeof _AEC_category == 'undefined' ) var _AEC_category = Array(1) ;
	if( typeof _AEC_prodname == 'undefined' ) var _AEC_prodname = Array(1) ;
	if( typeof _AEC_amtlist == 'undefined' ) var _AEC_amtlist = Array(1) ;

if( typeof EL_pd == 'undefined' ) var EL_pd = '' ;
if( typeof EL_ct == 'undefined' ) var EL_ct = '' ;
if( typeof EL_amt == 'undefined' ) var EL_amt = '' ;

EL_pd ="<?=addslashes($prd[name])?>";
EL_ct ="<?=addslashes($prd[cate_name])?>";
EL_amt = "<?=$prd[sell_prc]?>";

_AEC_amtlist=Array('<?=$prd[sell_prc]?>');
_AEC_numlist=Array('1');
_AEC_prodidlist=Array('<?=$prd[parent]?>');
_AEC_prodname=Array('<?=addslashes($prd[name])?>');
_AEC_category=Array('<?=addslashes($prd[cate_name])?>');
</script>
<!-- AceCounter eCommerce (Product_detail) v3.0 Start -->
<?php
		$_ace_counter=1;
	}

    // 페이스북 전환 API
    if ($scfg->comp('use_fb_conversion', 'Y') == true && $scfg->comp('fb_pixel_id') == true) {
        require_once __ENGINE_DIR__.'/_engine/promotion/fd_conversion_view.inc.php';
    }

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js?20220111"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.prdcpn.js?20200630"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/clipboard.min.js"></script>
<?php
if($cfg['use_sbscr']=='Y') {
	$sbscr_set_no = $pdo->row("select no from $tbl[sbscr_set_product] where pno='$prd[no]'");
	if(($cfg['sbscr_type']=='P' && $sbscr_set_no) || ($cfg['sbscr_type']=='A')) {
?>
	<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js?20220624"></script>
<?php }
}
?>
<script type='text/javascript'>
<!--
var ra='<?=$ra?>';
var qa='<?=$qa?>';
var review_strlen=<?=$cfg['product_review_strlen']?>;
var review_con_strlen=<?=$cfg['product_review_con_strlen']?>;
var qna_strlen='<?=$cfg['product_qna_strlen']?>';
var ace_counter='<?=$_ace_counter?>';
var cart_direct_order='<?=$cfg['cart_direct_order']?>';
var cart_cnt='<?=$cart_cnt?>';
var sbscr_cart_cnt='<?=$sbscr_cart_cnt?>';
var sbscr_cart_type='<?=$cfg['sbscr_cart_type']?>';
var hid_now='<?=$now?>';
var pg_type='detail_';
<?php if($Anx == "Y"){ ?>
function anxCart(type){
	f=document.prdAnxFrm;
	if(!checkCB(f["ck[]"], "<?=$AnxN?>을(를)")) return;
	f.type.value=type;
	f.submit();
}
<?php } ?>
function refPrdCart(refkey, type) {
	var f = byName('refFrm'+refkey);
	if(!f) return;
    f.next.value=type;
    f.submit();
}

var defaultOpt='';
window.onload=function (){
	if(document.all.multiOpt){
		defaultOpt=document.all.multiOpt.innerHTML;
	}
}
//-->
</script>
<span class="detail_url" data-clipboard-text="<?=$root_url?>/shop/detail.php?pno=<?=$pno?>"></span>
<?php if ($_GET['d_preview'] == 'Y') { ?>
<script src="<?=$engine_url?>/_manage/detail_preview.js?<?=date('YmdHi')?>"></script>
<?php } ?>
<?PHP
	include_once $engine_dir."/_engine/common/skin_index.php";
?>