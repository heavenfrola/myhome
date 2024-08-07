<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 메인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/ext.lib.php";

	$rURL = ($_REQUEST['rURL']) ? $_REQUEST['rURL'] : urlencode($this_url);

	$sql="select p.`no`, p.`hash`, p.`name`, p.`stat`, p.`sell_prc` , p.`milage` , w.`no` as wno, w.`pno`, w.`member_no` , w.`reg_date` from `".$tbl['product']."` p inner join `$tbl[wish]` w on p.no=w.pno where w.`member_no`='$member[no]' and p.`stat`!='4' order by w.`no` desc";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	include_once $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[product]` p inner join `$tbl[wish]` w on p.`no`=w.`pno` where w.`member_no`='$member[no]' and p.`stat`!='4'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$wishRes = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	$GLOBALS[total_wish]=$NumTotalRec;

	if (!function_exists("wishList")) {
		function wishList() {
            global $wishRes;

			$data = $wishRes->current();
            $wishRes->next();
			if($data == false) return false;

			$data[name]=stripslashes($data[name]);
			$data[sell_prc_str]=number_format($data[sell_prc]);
			$data[milage_str]=number_format($data[milage]);
			$data[link]=$GLOBLAS[root_url]."/shop/detail.php?pno=".$data[hash];
			$GLOBALS[widx]++;
			return $data;
		}
	}

    $w = "member_no='{$member['no']}' and member_id='{$member['member_id']}'";
	$sql="select * from {$tbl['milage']} where $w order by `no` desc";

	include_once $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;
	$QueryString="";

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['milage']} where $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$resMilage = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	include_once $engine_dir."/_engine/include/milage.lib.php";


	common_header();

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>