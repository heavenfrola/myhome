<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir.'/_engine/include/design.lib.php';
	include_once $engine_dir."/_engine/include/paging.php";

	if($cfg['design_version'] == "V3") {
		$_skin = getSkinCfg();

		if($_skin['pageres_design_use'] == 'Y' && $dmode != 'admin') {
			$_use['rev_year_split'] = $_skin['rev_year_split'];
		}
	}

	$_reviewSearchCol = array("","name","member_id","title","content");

	$ra=reviewAuth('product_review_auth');
	$page = numberOnly($_GET['page']);
    $type = numberOnly($_GET['type']);
	if($_GET['rev_page']) $page = $_GET['rev_page'];

	// 제목줄임,상품명줄임,한페이지줄,페이지블럭,이미지가로,이미지세로,링크타입,공지여부,이미지번호
	function reviewAllList($title_cut=0,$prd_cut=0,$row=20,$block=10,$w="",$h="",$link_type="",$notice="",$img_no=1,$stat=0) {
		global $prd,$search_column,$_reviewSearchCol,$type,$_use, $page, $cfg, $member, $pdo, $PagingInstance, $_skin;

		if(!$GLOBALS['revRes']) {
			if($page <= 1) $page = 1;

			$sc = $_reviewSearchCol[$_GET['search_column']];
			if(isset($_GET['rsearch_str']) == false && $_GET['search_str']) {
				$_GET['rsearch_str'] = $_GET['search_str'];
			}
			$rsearch_str = addslashes(trim($_GET['rsearch_str']));
			if($rsearch_str && $sc) {
				$sc = str_replace('`', '', addslashes($sc));
				$ss = parseParam($rsearch_str);
				$where .= " and r.`$sc` like '%$ss%'";
			}

            if ($cfg['use_review_image_cnt'] == 'Y') {
                if ($type == '2') $where .= " and image_cnt>0";
                else if ($type == '3') $where .= " and image_cnt=0";
            } else {
                if ($type == '2') $where .= " and (r.upfile1!='' or r.upfile2!='')";
                else if ($type == '3') $where .= " and (r.upfile1='' and r.upfile2='')";
            }

			if($notice) {
				if($notice != 'Y') $notice = 'N';
				$where.=" and r.`notice`='$notice'";
			}

			$stat = numberOnly($_GET['stat']);
			if ($stat) $where .= " and r.`stat` = '$stat'";
			else {
				if($cfg['product_review_atype_detail'] == 'Y') $where .= ($member[no] > 0) ? " and (r.`stat` in (2,3,4) or r.member_no='$member[no]')":" and r.`stat` in (2,3,4)";
				else $where .= " and r.`stat` in (2,3,4)";
			}

			if($_use['rev_year_split'] == 'Y') {
				$year = numberOnly($_GET['year']);
				if(!$year) $year = date('Y');
				$date1 = strtotime($year.'-01-01 00:00:00');
				$date2 = strtotime(($year+1).'-01-01 00:00:00')-1;
				if($notice != 'Y' && !$rsearch_str) {
					$where .= " and r.reg_date between $date1 and $date2";
				}
			}

			if($notice != 'Y' && $_GET['is_mbr'] == 'true') {
				$join = " inner join {$GLOBALS['tbl']['member']} m on r.member_no=m.no ";
				foreach($_GET as $key => $val) {
					if(preg_match('/^mbr([0-9]+)$/', $key, $__tmp) == true) {
						$mbr_no = $__tmp[1];
						$vals = explode(',', preg_replace('/[^0-9,]/', '', $val));
						$tmp1 = $tmp2 = '';
						foreach($vals as $_val) {
							if($tmp1) $tmp1 .= ',';
							$tmp1 .= "'$_val'"; // radio button
							$tmp2 .= " or m.add_info{$mbr_no} like '%@$_val@%'"; // checkbox
						}
						$where .= " and (m.add_info{$mbr_no} in ($tmp1) $tmp2)";
					}
				}
			}

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

            if ($_skin['review_list_best_use'] == 'Y') {
                $rev_order = ' r.stat DESC, '.$rev_order;
            }

			$sql="select r.* from `".$GLOBALS['tbl']['review']."` r $join where 1 $where order by $rev_order";
			$_SESSION['rev_qry'] = $sql;

			if($notice != "Y") {
				$NumTotalRec = $pdo->row("select count(*) from `".$GLOBALS['tbl']['review']."` r $join where 1 $where");
				$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
				$PagingInstance->addQueryString(makeQueryString('page', 'striplayout', 'stripheader'));

				if($_use['rev_year_split'] == 'Y' && !$rsearch_str) {
					$qna_year = $pdo->assoc("select min(reg_date), max(reg_date) from {$GLOBALS['tbl']['review']}");
					$min_year = date('Y', $qna_year[0]);
					$max_year = date('Y', $qna_year[1]);

					if($qna_year[0] > 0 && $max_year >= ($year+1)) $PagingInstance->year_next = ($year+1);
					if($qna_year[1] > 0 && $min_year <= ($year-1)) $PagingInstance->year_prev = ($year-1);
				}

				$PagingResult = $PagingInstance->result($GLOBALS['pg_dsn']);
				$sql .= $PagingResult['LimitQuery'];

				$GLOBALS['pageRes'] = $PagingResult['PageLink'];
			}

			$GLOBALS['revRes'] = $pdo->iterator($sql);
			$GLOBALS['rev_idx'] = $NumTotalRec-($row*($page-1))+1;
		}
        $data = $GLOBALS['revRes']->current();
        $GLOBALS['revRes']->next();
		if($data == false) {
			return false;
		}

		$data['rev_idx'] = ($GLOBALS['rev_idx']-1);
		$data = reviewOneData($data,$title_cut,$w,$h,$def_img,$prd_cut,$link_type,$img_no);

		$GLOBALS['rev_idx']--;
		return $data;
	}

	function reviewReset() {
		$GLOBALS['qna_idx']=0;
		$GLOBALS['revRes']="";
	}

	if(!$_GET['single_module']) {
	common_header();

	if(!$cfg['product_review_con_strlen']) $cfg['product_review_con_strlen'] = 0;
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript">
<!--
var ra='<?=$ra?>';
var review_strlen=<?=$cfg['product_review_strlen']?>;
var review_con_strlen=<?=$cfg['product_review_con_strlen']?>;

function setMbrSearch() {
	var f = $('#mbr_search');
	var tmp = new Array();
	f.find(':checked').each(function() {
		var nm = this.name.replace('[]', '');
		if(!tmp[nm]) tmp[nm] = this.value;
		else tmp[nm] += ','+this.value;
	});

	var url = window.location.href.replace(/(\?|&)mbr[0-9]+=[0-9,]+/g, '').replace('&is_mbr=true', '');
	var param = '';
	for(var key in tmp) {
		param += (url.indexOf('?') < 0 && param.indexOf('?') < 0) ? '?' : '&';
		param += key+'='+tmp[key];
	}
	if(param) param += '&is_mbr=true&stripheader=1';
	$.get(url+param, function(r) {
		$('body').html(r);
	});
}

$(function() {
	$('.rev_mbr').click(function() {
		setMbrSearch();
	});
});
//-->
</script>
<?php
	}

    if ($type == '2') {
        if (file_exists($_skin['folder'].'/CORE/shop_product_preview_list.wsr') == true) {
            $_tmp_file_name = 'shop_product_preview_list.php';
        }
    }

	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>