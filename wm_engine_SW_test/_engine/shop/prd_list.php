<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품리스트 쿼리 작성
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\Design\DesignCache;

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir."/_engine/include/paging.php";

	setListURL('big_section');

	$cno1 = numberOnly($_GET['cno1']);
	$cno2 = numberOnly($_GET['cno2']);
	$sort = numberOnly($_GET['sort']);
	$search_str = trim($_GET['search_str']);

	if($_SERVER['SCRIPT_NAME'] == '/shop/big_section.php' && $scfg->comp('cache_prdlist_use', 'Y')) {
		new DesignCache('prdlist', array($cno1, $cno2));
	}

    if ($_SERVER['SCRIPT_NAME'] == '/shop/search_result.php') {
        $list_mode = 3;
    }

	if(!$list_mode) {
		if(isset($_GET['search_str']) == true) $list_mode=3; // 검색
		elseif($cno1!="") $list_mode=2;
		else $list_mode=1;
	}

	$prdWhere=" and `stat` in (2,3)"; // 정상판매 및 품절 // 2007-09-18 or => in

	loadPlugin('product_list_search_pre');

	$fno = numberOnly($_GET['fno']);
	$fvalue = addslashes(trim($_GET['fvalue']));
	if($fno && $fvalue) {
		$prdWhere.=" and `no` in (select `pno` from `$tbl[product_filed]` where `fno`='$fno' and `value`='$fvalue')";
	}

	// 가격별
	$start_price = numberOnly($_GET['start_price'], true);
	$finish_price = numberOnly($_GET['finish_price'], true);
	if($start_price || $finish_price) {
		if($start_price && $finish_price && $start_price>$finish_price) {
			msg(__lang_shop_error_searchPrice__, 'back');
		}

		if($start_price) $prdWhere.=" and `sell_prc`>=$start_price";
		if($finish_price) $prdWhere.=" and `sell_prc`<=$finish_price";
	}

	if($list_mode==3) { // 검색
		include $engine_dir."/_engine/shop/search_result.php";

		$old_search_str = addslashes(trim($_GET['old_search_str']));
		if($old_search_str) $prdWhere.=" and (`name` like '%$old_search_str%' or `keyword` like '%$old_search_str%' or `code` like '%$old_search_str%')"; // 결과내 재검색

		// 검색어 존재시
		$search_str = $_GET['search_str'];
		if($search_str) {
			putSearchLog($search_str);
			// 검색어
			$search1 = $search_str; // 일반 표출용
			$search2 = inputText($search_str); // 텍스트박스용

			$_search_str = str_replace('\\', '', addslashes(str_replace(' ', '', $search_str)));
			$_search_str = parseParam($_search_str);

			$prdWhere.=" and (replace(replace(p.name, '\\\', ''), ' ', '') like '%$_search_str%' or replace(replace(p.keyword, '\\\', ''), ' ', '') like '%$_search_str%' or p.code like '%$_search_str%')";
		}

		$prdWhere.=" and `wm_sc`='0'";
		$prdWhere.=getPrdMyLevel(); // 2007-06-15 : 상품 회원권한별 접근 - Han

		$prdWhere1=$prdWhere;

		$cate = numberOnly($_GET['cate']);
		$xbig = numberOnly($_GET['xbig']);
		$ybig = numberOnly($_GET['ybig']);
        $ctype = preg_replace('/[\'"\/]/', '', trim($_GET['ctype'])); //따옴표,역슬래시 삭제

		if($ctype && $cate) $prdWhere.=" and `$ctype`='$cate'";
		if($xbig > 0) $prdWhere .= " and `xbig`='$xbig'";
		if($ybig > 0) $prdWhere .= " and `ybig`='$ybig'";
	} elseif($list_mode==2) { // 일반 카테고리
		$_cno1=$_cp=getCateInfo($cno1);

		if($_cno1['access_member'] && strpos($_cno1['access_member'], 'buy') === false) { // 접근권한 체크
			if(!$admin['no'] && $member['level'] != 1 && !strchr($_cno1['access_member'],"@".$member['level']."@")) {
				if($_cno1['no_access_page']) {
					msg($_cno1['no_access_msg'], $_cno1[no_access_page]);
				}
				else {
					$msg = $_cno1['no_access_msg'] ? $_cno1['no_access_msg'] : __lang_shop_error_cateDenyDefault__;
					msg($msg, "back");
				}
			}
		}
		$prdWhere.=prdWhereByCate($_cno1);
		$rows=$_cno1['cols']*$_cno1['rows'];

		if($cno2) {
			$_cno2=getCateInfo($cno2);
			$rows2=$_cno2['cols']*$_cno2['rows'];
			$prdWhere.=prdWhereByCate($_cno2);
		}
		if($_cno1['ctype']==1) {
			$mcate_where=" and `ctype`='4'";
			$normal_cate=$_cno1;
		}
		else {
			$mcate_where=" and `ctype`='1' and `level`='1'";
			if($_cno2['no']) $normal_cate=$_cno2;
		}
		$midx=0;

		if($_cate_colname[$_cno1['ctype']][$_cno1['level']] == 'xbig') $prdWhere.=" and `wm_sc`=0";
	} elseif($list_mode==4) { // 최근 클릭상품 리스트
        if(function_exists('getClickPrd') ==false) {
            msg('최근 본 상품 기능을 사용하고 있지 않습니다.', 'back');
        }
		if(!$_click_prd) $_click_prd=getClickPrd();

		if(is_array($_click_prd) && count($_click_prd)>0) {
			$click_where="";
			foreach($_click_prd as $key=>$val) {
				$click_where.=" or `no`='$val'";
			}
			$click_where=substr($click_where,4);
			$prdWhere.=" and ($click_where)";
		}else {
			$prdWhere.=" and 0";
		}
		if(is_array($_click_prd)) reset($_click_prd);
	}

	// 노출 위치
	if($list_mode == 3) {
		if($cfg['use_prd_perm'] == 'Y') {
			$prdWhere .= " and perm_sch='Y'";
		}
	} else {
		if($cfg['use_prd_perm'] == 'Y') {
			$prdWhere .= " and perm_lst='Y'";
		}
	}

	$prdTopWhere=$prdWhere." and `top_prd`='Y'";

	if($_cno1['ctype'] == 4 || $_cno1['ctype'] == 5) {
		$prdWhere .= " and wm_sc=0";
	}

	switch($_GET['prd_stat']) {
		case 'sale' :
			$prdWhere .= " and stat=2";
		break;
		case 'soldout' :
			$prdWhere .= " and stat=3";
		break;
	}

	$sort_list_query = makeQueryString('page', 'sort', 'striplayout', 'stripheader', 'makecache');
	$QueryString = ($sort) ? $sort_list_query."&sort=$sort" : $sort_list_query;

	// 정렬 정보
	if($sort) $_sort=get_info($tbl['product_sort'],"no",$sort);

	// 정렬값이 없을 경우
	if(!$sort || !$_sort['no'] || $_sort['use']=="N" || $_sort['real_use']=="N") {
		$swhere=($cfg['prd_sort_def']) ? "`no`='{$cfg['prd_sort_def']}'" : "`use`='Y' and `real_use`='Y' order by `sort` limit 1"; // + 2007-09-18 기본값 없을 경우
		$_sort = $pdo->assoc("select * from $tbl[product_sort] where $swhere");
		$sort=$_sort['no'];
		$prdOrder=$_sort['query'];
	} else {
		$prdOrder=$_sort[query];
	}
	if(!$prdOrder) $prdOrder="`edt_date` desc";

	if(trim($prdOrder) == '`edt_date` desc') {
		if($_cno1['ctype'] == 1) {
			if($_cno1['level'] == 1) $prdOrder="`sortbig` desc, ".$prdOrder;
			elseif($_cno1['level'] == 2) $prdOrder="`sortmid` desc, ".$prdOrder;
			elseif($_cno1['level'] == 3) $prdOrder="`sortsmall` desc, ".$prdOrder;
			elseif($_cno1['level'] == 4) $prdOrder="`sortdepth4` desc, ".$prdOrder;
		} elseif($_cno1['ctype'] == 2 || $_cno1['ctype'] == 6) {
			$prdOrder = " l.sort_big asc";
		} elseif($cfg['use_new_sortxy'] == 'Y' && ($_cno1['ctype'] == 4 || $_cno1['ctype'] == 5)) {
			$prdOrder = 'sort_'.$_cate_colname[1][$_cno1['level']].' asc, '.$prdOrder;
		}
	}
	if($cfg['prd_sort_soldout'] == 'Y') $prdOrder = "`stat`,".$prdOrder;
	elseif($cfg['prd_sort_soldout'] == 'H') $prdWhere .= " and stat!=3";


	$nidx=0;
	if($_cno1['cols']<1) $_cno1['cols']=4;

	if($single_module) return;

	common_header();

	// 2008-10-15 : 상품검색시 에이스카운터
	if($list_mode == 3 && $cfg[ace_counter_gcode] && $search_str){
?>
<!-- AceCounter eCommerce (Prouct_Search) v3.0 Start -->
<script type='text/javascript'>
   var EL_skey='<?=$search_str?>';
</script>
<!-- AceCounter eCommerce (Prouct_Search) v3.0 Start -->
<?php
	}

?>
<script type='text/javascript' type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?php
	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>