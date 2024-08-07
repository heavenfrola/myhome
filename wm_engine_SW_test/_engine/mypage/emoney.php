<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  예치금 리스트
	' +----------------------------------------------------------------------------------------------+*/

	// 예치금 내역조회 - Han
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	$_skin = getSkinCfg();

	memberOnly(1,"");

	$sql="select * from `$tbl[emoney]` where `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `no` desc";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;

	$NumTotalRec=$pdo->row(str_replace("select * from","select count(*) from",$sql));
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$resEmoney=$pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	include $engine_dir."/_engine/include/milage.lib.php";
	common_header();

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>