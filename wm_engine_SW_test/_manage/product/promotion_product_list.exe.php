<?PHP

	printAjaxHeader();

	$_search_type['name'] = '상품명';
	$_search_type['keyword'] = '검색 키워드';
	$_search_type['code'] = '상품 코드';
	$_search_type['origin_name'] = '장기명';
	$_search_type['seller'] = '사입처';

	if($_GET['depth4']) $w .= " and `depth4`='{$_GET['depth4']}'";
	elseif($_GET['small']) $w .= " and `small`='{$_GET['small']}'";
	elseif($_GET['mid']) $w .= " and `mid`='{$_GET['mid']}'";
	elseif($_GET['big']) $w .= " and `big`='{$_GET['big']}'";

	if($_GET['xdepth4']) $w .= " and `xdepth4`='{$_GET['xdepth4']}'";
	elseif($_GET['xsmall']) $w .= " and `xsmall`='{$_GET['xsmall']}'";
	elseif($_GET['xmid']) $w .= " and `xmid`='{$_GET['xmid']}'";
	elseif($_GET['xbig']) $w .= " and `xbig`='{$_GET['xbig']}'";

	if($_GET['ydepth4']) $w .= " and `ydepth4`='{$_GET['ydepth4']}'";
	elseif($_GET['ysmall']) $w .= " and `ysmall`='{$_GET['ysmall']}'";
	elseif($_GET['ymid']) $w .= " and `ymid`='{$_GET['ymid']}'";
	elseif($_GET['ybig']) $w .= " and `ybig`='{$_GET['ybig']}'";

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$start_prc = numberOnly($_GET['prd_prc_s']);
	if($start_prc > 0) $w .= " and `sell_prc`>=$start_prc";
	$finish_prc = numberOnly($_GET['prd_prc_f']);
	if($finish_prc > 0) $w .= " and `sell_prc`<=$finish_prc";

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str != "") {
		$_tmp = explode(',', $search_str);
		if($search_type == 'code' && count($_tmp) > 1) {
			$_search_str = implode(',', array_map(function($val) {
				return "'$val'";
			}, $_tmp));
			$w .= " and code in ($_search_str)";
		} else {
			$w .= " and `$search_type` like '%$search_str%'";
		}
	}
	
	if(!$_GET['stat']) $w .= " and `stat` != '1'";

	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	$start_date = preg_replace('/[^0-9-]/', '', $_GET['start_date']);
	$finish_date = preg_replace('/[^0-9-]/', '', $_GET['finish_date']);
	if(!$start_date || !$finish_date) $all_date = "Y";
	if(!$start_date && !$finish_date) {
		$start_date =  date('Y-m-d', strtotime('-15 days'));
		$finish_date = date("Y-m-d", $now);
	}
	if(!$all_date) {
		$w .= " and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') >= '$start_date'";
		$w .= " and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') <= '$finish_date'";
	}

	$stat = $_GET['stat'];
	if(!$stat) $w .= " and `stat` != '1' and `stat` != 5";
	else $w .= " and `stat`='$stat'";

	$review = $_GET['review'];
	if($review=="Y") $ow = " order by rev_cnt desc";
	else $ow = " order by reg_date desc, no asc";

	$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord from $tbl[product] where wm_sc = 0 $w $ow";

	// 페이징
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 5;
	if($row > 30) $row = 30;
	$block=5;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[product] where wm_sc = 0 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, '', 'prm_prd_submit');
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result('ajax_admin');

	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));
	$sort = ($row*($page-1))+1;

?>

<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 상품이 검색되었습니다.
	<div class="total">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="prm_prd_submit(this.value);" style="position:absolute;top:10px;right:5px">
					<option value="5" <?=checked($row,5,1)?>>5</option>
					<option value="10" <?=checked($row,10,1)?>>10</option>
					<option value="20" <?=checked($row,20,1)?>>20</option>
					<option value="30" <?=checked($row,30,1)?>>30</option>
				</select>
			</dd>
		</dl>
	</div>
</div>
<div class="limit_box">
	<table class="tbl_col" id="prm_product_search">
		<caption class="hidden">상품 리스트</caption>
		<colgroup>
            <col style="width:50px">
			<col style="width:80px">
            <col style="width:150px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:80px">
            <col style="width:80px">
		</colgroup>
		<thead>
			<tr>
                <th scope="col"><input type="checkbox" onclick="checkAll($('.cb_prd_search'),this.checked)"></th>
                <!-- 상품검색리스트 전체체크 체크박스 생성 -->
				<th scope="col">순번</th>
				<th scope="col">상품</th>
				<th scope="col">가격</th>
				<th scope="col">적립금</th>
				<th scope="col">상태</th>
				<th scope="col">선택</th>
			</tr>
		</thead>
		<?php
            foreach ($res as $prd) {
				$prd['name'] = strip_tags(stripslashes($prd['name']));
				$prd['sell_prc'] = parsePrice($prd['sell_prc'], true);
				$prd['milage'] = parsePrice($prd['milage'], true);

				if($prd['upfile3']) {
					$file_dir = getFileDir($prd['updir']);
					$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='30' height='30'>";
				}

				$prd['name'] = cutStr($prd['name'], 35, '');
		?>
				<tr id="<?=$prd[no]?>">
                    <td><input type="checkbox" class="cb_prd_search" value="<?=$prd[no]?>>"></td>
                    <!-- 상품검색리스트 개별체크 체크박스 생성 -->
					<td><?=$idx?></td>
					<td class="left">
                        <div class="box_setup"  style="padding: 0px;">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
							<dl style="height:30px;">
								<dt class="title"><?=$prd['name']?></dt>
								<dd><a href="./?body=product@product_register&pno=<?=$prd['no']?>" class="p_color" target="_blank">수정</a></dd>
							</dl>
						</div>
					</td>
					<td><?=$prd['sell_prc']?></td>
					<td><?=$prd['milage']?></td>
					<td><?=$_prd_stat[$prd['stat']]?></td>
					<td><span class="box_btn_s gray"><input type="button" value="추가" onclick="prdsearch.psel(<?=$prd['no']?>)"></span></td>
				</tr>
		<?PHP

			$idx--;
			}

		?>
	</table>
</div>
<div class="box_bottom">
	<?=$pg_res?>
</div>