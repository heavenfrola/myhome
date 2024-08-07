<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir.'/_engine/include/design.lib.php';
	include_once $engine_dir."/_engine/include/paging.php";

	if($cfg['design_version'] == "V3") {
		$_skin = getSkinCfg();

		if($_skin['pageres_design_use'] == 'Y' && $dmode != 'admin') {
			$_use['qna_year_split'] = $_skin['qna_year_split'];
		}
	}

	$_qnaSearchCol=array("","name","member_id","title","content");
	$cate = numberOnly($_GET['cate']);

	$page = numberOnly($_GET['page']);

	function qnaAllList($title_cut=0,$prd_cut=0,$row=20,$block=10,$re_title="",$date_form="Y/m/d",$notice="") {
		global $search_column,$_qnaSearchCol,$admin,$cfg,$cate,$_use, $pdo, $PagingInstance;
		if(!$GLOBALS['revRes']) {
			if($GLOBALS[page]<1) $GLOBALS[page]=1;

			$sc=$_qnaSearchCol[$_GET['search_column']];
			if(isset($_GET['rsearch_str']) == false && $_GET['search_str']) {
				$_GET['rsearch_str'] = $_GET['search_str'];
			}
			$rsearch_str = addslashes(trim($_GET['rsearch_str']));
			if($rsearch_str && $sc) {
				$sc = str_replace('`', '', addslashes($sc));
				$ss=parseParam($rsearch_str);
				$w.=" and `$sc` like '%$ss%'";
			}

			if($GLOBALS[rno]) {
				$w.=" and `no`='$GLOBALS[rno]'";
			}

			if($notice) {
				$w.=" and `notice`='$notice'";
			}

			if($cate && $cfg[product_qna_cate]){
				$_cate_c=explode(",", $cfg[product_qna_cate]);
				$_cate=$_cate_c[$cate-1];
				if($prd[no]) $w .= " and q.`cate`='$_cate'";
				else $w .= " and `cate`='$_cate'";
			}

			$use_index = 'notice';
			if($_use['qna_year_split'] == 'Y') {
				$year = numberOnly($_GET['year']);
				if(!$year) $year = date('Y');
				$date1 = strtotime($year.'-01-01 00:00:00');
				$date2 = strtotime(($year+1).'-01-01 00:00:00')-1;
				if($notice != 'Y' && !$rsearch_str) {
					$use_index = 'reg_date';
					$w .= " and reg_date between $date1 and $date2";
				}
			}

			$sql="select * from `".$GLOBALS[tbl][qna]."` use index($use_index) where 1 $w order by  `reg_date` desc";

			if($notice!="Y") {
				$NumTotalRec=$pdo->row("select count(*) from `".$GLOBALS[tbl][qna]."` use index(reg_date) where 1 $w");
				$PagingInstance=new Paging($NumTotalRec, $GLOBALS[page], $row, $block);
				$PagingInstance->addQueryString(makeQueryString('page'));

				if($_use['qna_year_split'] == 'Y' && !$rsearch_str) {
					$qna_year = $pdo->assoc("select min(reg_date), max(reg_date) from {$GLOBALS['tbl']['qna']}");
					$min_year = date('Y', $qna_year[0]);
					$max_year = date('Y', $qna_year[1]);

					if($qna_year[0] > 0 && $max_year >= ($year+1)) $PagingInstance->year_next = ($year+1);
					if($qna_year[1] > 0 && $min_year <= ($year-1)) $PagingInstance->year_prev = ($year-1);
				}

				$PagingResult=$PagingInstance->result($GLOBALS[pg_dsn]); //
				$sql.=$PagingResult[LimitQuery];
				$GLOBALS[pageRes]=$PagingResult[PageLink];
			}

			$GLOBALS['revRes']=$pdo->iterator($sql);

			$GLOBALS[qna_idx]=$NumTotalRec-($row*($GLOBALS[page]-1))+1;
		}
		$data = $GLOBALS['revRes']->current();
        $GLOBALS['revRes']->next();

		if($data == false) {
			return false;
		}

		unset($prd);
		$data=qnaOneData($data,$title_cut,$prd_cut,$re_title);

		$GLOBALS[qna_idx]--;
		return $data;
	}

	function qnaReset() {
		$GLOBALS[qna_idx]=0;
		unset($GLOBALS['revRes']);
	}

	// 권한
	$qa=reviewAuth('product_qna_auth');
	$all_qna=1;
	common_header();

	if(!$single_module) {
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript">
<!--
var qa='<?=$qa?>';
var qna_strlen='<?=$cfg[product_qna_strlen]?>';
//-->
</script>
<?
	}

	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>