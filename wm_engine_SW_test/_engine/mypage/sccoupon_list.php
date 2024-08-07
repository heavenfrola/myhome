<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 사용리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	memberOnly(1,"");

	$sql="select *, (select `name` from `$tbl[sccoupon]` where `no`=a.`scno`) as `scname`, (select `name` from `$tbl[coupon]` where `no`=a.`cno`) as `cname` from `$tbl[sccoupon_use]` a where `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `no` desc";
	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;
	$QueryString="";

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[sccoupon_use]` where `member_no`='$member[no]' and `member_id`='$member[member_id]'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$cpRes=$pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	function sccouponDownList() {
        global $cpRes;

		$data = $cpRes->current();
        $cpRes->next();
		if($data == false) return false;

		$data['cname']=stripslashes($data['cname']);
		$data['scname']=stripslashes($data['scname']);
		$data['milage_prc']=number_format($data['milage_prc']);
		$data['reg_date']=date("Y-m-d", $data['reg_date']);

		$GLOBALS['idx']--;
		return $data;
	}

	common_header();

	include_once $engine_dir."/_engine/common/skin_index.php";

?>