<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품리스트 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$cno1 = numberOnly($_GET['cno1']);
	$cno2 = numberOnly($_GET['cno2']);
	$sort = numberOnly($_GET['sort']);

	if(!$cno1) msg(__lang_common_error_required__.'(cno)', $root_url, 'parent');

	$_cno1=getCateInfo($cno1);
	$rows=$_cno1[cols]*$_cno1[rows];
	if($cno2) $_cno2=getCateInfo($cno2);

	if($_cno1[ctype]==1) {
		$mcate_where=" and `ctype`='4'";
		$normal_cate=$_cno1;
	}
	else {
		$mcate_where=" and `ctype`='1' and `level`='1'";
		if($_cno2[no]) $normal_cate=$_cno2;
	}
	$midx=0; // 상단 중분류 목록

	$sort_idx=$total_sort=0;

	$_sort=get_info($tbl['product_sort'],"no",$sort);
	if(!$sort || !$_sort['no'] || $_sort['use']=="N" || $_sort['real_use']=="N") {
		if($_cno1[ctype]=="2") {
			$prdOrder="sort".$cno1;
		}
		else {
			$_sort = $pdo->assoc("select * from $tbl[product_sort] where `no`='$cfg[prd_sort_def]'");
			$sort=$_sort[no];
		}
	}
	if(!$prdOrder) $prdOrder=$_sort[query];

	$prdWhere=""; // 상품 조건
	$prdWhere.=" and (`stat`=2 or `stat`=3)";

	$prdWhere.=prdWhereByCate($_cno1);
	$prdWhere.=prdWhereByCate($_cno2);


	$nidx=0;
	if($_cno1[cols]<1) $_cno1[cols]=4;

	$prdTopWhere=$prdWhere." and `top_prd`='Y'";

	// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";


	// 상품 정렬 앞 주소 - 이부분도 함수로 만들것
	$sort_list_query="";
	if($cno1) $sort_list_query.="&cno1=$cno1";
	if($cno2) $sort_list_query.="&cno2=$cno2";
	if($search_str) $sort_list_query.="&search_str=$search_str";

	$QueryString=$sort_list_query."&sort=$sort";

	common_header();
?>
<script type='text/javascript' type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>