<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  다운로드 쿠폰 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	$_skin = getSkinCfg();

	memberOnly(1,"");
	$rURL=urlencode($this_url);
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	$sql="select * from `$tbl[coupon_download]` where `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `no` desc";
	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=$_SESSION['browser_type'] == 'mobile' ? 10 : 20;
	if(!$block) $block=10;
	$QueryString="";

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[coupon_download]` where `member_no`='$member[no]' and `member_id`='$member[member_id]'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$cpRes = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;


	function couponDownList($deco=null){
		global $rURL, $cpRes;
		if(!$deco) $deco = __currency__;
		$data = $cpRes->current();
        $cpRes->next();
		if($data == false) return false;

		$data[name]=stripslashes($data[name]);
		$data[sale_prc]=$data[sale_prc];
		$data[prc_limit]=number_format($data[prc_limit]).$deco;
		$data[sale_limit]=number_format($data[sale_limit]).$deco;
        if ($data['is_type'] != 'B'){
            $data['down_date'] = date('Y-m-d', $data['down_date']);
        } else {
            $data['down_date'] = date('Y-m-d', $data['use_date']);
        }
		if($data[ono]){
			$data[use_date]="<a href=\"".$GLOBLAS[root_url]."/mypage/order_detail.php?ono=$data[ono]&rURL=$rURL\">".date("Y-m-d",$data[use_date])."</a>";
		} else {
			$data[use_date]= __lang_cpn_info_notUsed__;
		}

		if($data['udate_type'] == 1) {
			$data['ustart_date'] = '';
			$data['ufinish_date'] = __lang_cpn_info_unlimited__;
		}

		$GLOBALS[idx]--;
		return $data;
	}

	if(!$single_module) {

	common_header();
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?PHP

	}

	include_once $engine_dir."/_engine/common/skin_index.php";

?>