<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  MY 후기 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	memberOnly(1,"");

	include $engine_dir."/_engine/include/paging.php";

	// 정렬
	$rev_order = ' r.`reg_date` desc';
	switch($_GET['rev_sort']) {
		case '2' : $rev_order = 'r.rev_pt desc, r.reg_date desc'; break;
		case '3' : $rev_order = 'r.rev_pt asc, r.reg_date desc'; break;
		case '4' :
			if(isTable($GLOBALS['tbl']['review_recommend']) == true) {
				$rev_order = 'r.recommend_Y desc, r.recommend_N asc, r.reg_date desc';
			}
		break;
	}

	$addq = "and `member_no`='".$member['no']."'";
	$sql = "select * from `".$tbl['review']."` r where 1 ".$addq." order by $rev_order";
	$NumTotalRec = $review_idx = $pdo->row("select count(*) from `$tbl[review]` where 1 $addq");

	if($_SESSION['browser_type'] == 'mobile') {
		/*
		if($page <= 1) $page=1;
		if(!$row) $row = 10;
		if(!$block) $block=10;
		$QueryString="";

		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString($QueryString);
		$PagingResult=$PagingInstance->result($pg_dsn);
		$sql.=$PagingResult[LimitQuery];

		$pageRes=$PagingResult[PageLink];
		$csRes=$pdo->query($sql);
		$review_idx=$NumTotalRec-($row*($page-1));
		*/
	}

	$contentRes = $pdo->iterator($sql);
	$GLOBALS['total_content'] = $NumTotalRec;

	function contentList($w = 50, $h = 50) {
		global $tbl,$review_idx, $contentRes;

		$data = $contentRes->current();
        $contentRes->next();
		if($data == false) return false;

		$data=reviewOneData($data, null, $w, $h, null, null, null, 3);
		$data[rev_idx]=$review_idx;

		$data['prd_img'] = $data['prd_name'] ? "<a href='{$data['link1']}'><img src='{$data['img']}' {$data[imgstr]}></a>" : "";
		$data['prd_name'] = "<a href='{$data['link1']}'>{$data['prd_name']}</a>";

		$review_idx--;
		return $data;
	}

	if(empty($cfg['product_review_strlen'])) $cfg['product_review_strlen'] = 0;
	if(empty($cfg['product_review_con_strlen'])) $cfg['product_review_con_strlen'] = 0;

	if(!$_GET['single_module']) {
	common_header();
?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js?20190822"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<script type="text/javascript">
var ra='<?=reviewAuth('product_review_auth')?>';
var review_strlen=<?=$cfg['product_review_strlen']?>;
var review_con_strlen=<?=$cfg['product_review_con_strlen']?>;
</script>
<form name="reviewDelFrm" method="post" action="<?=$root_url?>/main/exec.php" target="hidden">
<input type="hidden" name="exec_file" value="">
<input type="hidden" name="no" value="">
<input type="hidden" name="exec" value="delete">
</form>
<?
	}

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>