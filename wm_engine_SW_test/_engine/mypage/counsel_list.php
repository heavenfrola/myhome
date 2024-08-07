<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  1:1 고객센터 작성 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	$_skin = getSkinCfg();

	if($_GET['ono']) $ono = addslashes(trim($_GET['ono']));
	if($_GET['sbono']) $sbono = addslashes(trim($_GET['sbono']));
	if($ono) {
		$sql="select * from `$tbl[cs]` where `ono`='$ono' order by `no` desc";
	} elseif($sbono) {
		$sql="select * from `$tbl[cs]` where `ono`='$sbono' order by `no` desc";
	}
	else {
		memberOnly(1,"");
		$sql="select * from `$tbl[cs]` where `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `no` desc";
	}

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row = ($_SESSION['browser_type'] == 'mobile') ? 10 : 20;
	if(!$block) $block=10;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[cs]` where `member_no`='$member[no]' and `member_id`='$member[member_id]'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$csRes = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;
	$widx = ($row*($page-1));

	function counselLoop() {
		$data = $GLOBALS['csRes']->current();
        $GLOBALS['csRes']->next();
		if($data == false) return false;

		$data[title]=stripslashes($data[title]);
		$data[date]=date("Y/m/d",$data[reg_date]);
		$data[link]="javascript:csView('$data[no]','$data[reply_date]')";

		$_lang_cust_cate = eval(__lang_cust_cate);
		$data[cate]=$_lang_cust_cate[$data[cate1]][$data[cate2]];
		if(!$data[cate]) $GLOBALS['_cust_cate'][$data[cate1]][$data[cate2]];

		$data['content'] = stripslashes($data['content']);
		if(strip_tags($data['content']) == $data['content']) {
			$data['content'] = nl2br($data['content']);
		}
		if($data[reply]) {
			$data[reply]=nl2br(stripslashes($data[reply]));
		}
		else {
			$data[reply] = __lang_mypage_info_answerStandby__;
		}
		$GLOBALS[widx]++;
		return $data;
	}

	if(!$_GET['single_module']) {
	common_header();

?>
<script language="JavaScript" src="<?=$engine_url?>/_engine/common/shop.js?200228"></script>
<?PHP

	}

	if($counsel_list_include) return;

	include_once $engine_dir."/_engine/common/skin_index.php";

?>