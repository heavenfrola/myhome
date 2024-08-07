<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  위시리스트 통계
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/shop.lib.php";

	$list_tab_qry = makeQueryString(true, 'page', 'prd_stat');

	$_search_type = array(prd_name=>'상품명',member_id=>'회원아이디',member_name=>'회원명');
	$search_str = addslashes(trim($_GET['search_str']));
	$search_type = addslashes($_GET['search_type']);
	$search_on = addslashes($_GET['search_on']);
	$prd_stat = numberOnly($_GET['prd_stat']);

	if($search_type && $search_str) {
		switch($search_type) {
			case "prd_name" : $_where_qry[] = "p.name like '%$search_str%'"; break;
			case "member_id" : $_where_qry[] = "m.member_id like '%$search_str%'"; break;
			case "member_name" : $_where_qry[] = "m.name like '%$search_str%'"; break;
		}
	}
	if($prd_stat){
		switch($prd_stat) {
			case "2" : $_where_qry2 = " and p.`stat` = 2"; break;
			case "3" : $_where_qry2 = " and p.`stat` = 3"; break;
		}
	}

	$_where_qry[] = "p.`no`=w.`pno` and w.`member_no`=m.`no` and p.`stat`!='4'";
	$where_qry = count($_where_qry)?" where ".implode(" and ",$_where_qry):"";

	$sql = "select p.`no`, p.`hash`, p.`name` as prd_name, p.`stat`, p.`sell_prc` , p.`ea_type` , p.`updir` , p.`upfile3`, p.`w3`, p.`h3` , w.`no` as wno, w.`pno`, w.`member_no` , w.`reg_date`, m.`member_id` as member_id, m.`name` as member_name, m.`email`, m.`cell`, m.`blacklist` from `".$tbl['product']."` p inner join  `".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` ".$where_qry.$_where_qry2." group by  w.`no` order by w.`no` desc";
	$sql2 = "select count(*) cnt from (select w.`pno` from`".$tbl['product']."` p inner join`".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no`".$where_qry.$_where_qry2." group by w.`no`)  cnt ";

	$total = $pdo->row("select count(*) from `".$tbl['wish']."`w inner join `".$tbl['product']."` p on w.`pno`=p.`no` left join `".$tbl['member']."` m on w.member_no=m.no ".$where_qry);
	$normal = $pdo->row("select count(*) cnt from (select w.`pno` from`".$tbl['product']."` p inner join`".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` left join `erp_complex_option` e on p.`no` = e.`pno`".$where_qry."  and p.`stat` =2 group by w.`no`)  cnt ");
	$soldout = $total - $normal;

	if($search_on == "Y"){
		include $engine_dir."/_engine/include/paging.php";
		$page = numberOnly($_GET['page']);
		$row = numberOnly($_GET['row']);
		if($page <= 1) $page = 1;
		if($row < 1 || $row > 1000) $row = 20;
		$block = 10;
		$NumTotalRec = $pdo->row($sql2);
		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString(makeQueryString('page'));
		$PagingResult = $PagingInstance->result($pg_dsn);
		if($body != 'log@wish_list_excel.exe') {
			$sql.=$PagingResult['LimitQuery'];
		}
		$pg_res = $PagingResult['PageLink'];
		$res = $pdo->iterator($sql);
		$idx = $NumTotalRec-($row*($page-1));
		$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

		function wishList($imgn=3,$w=10,$h=10) {
			global $root_url,$tbl,$root_dir, $prd_stat, $cfg;

			$data = $GLOBALS['res']->current();
            $GLOBALS['res']->next();
			if($data == false) return;
			$data = shortCut($data);
			$data['name'] = stripslashes($data['prd_name']);
			$data['sell_prc_str'] = number_format($data['sell_prc']);
			$data['link'] = $root_url."/shop/detail.php?pno=".$data[hash];
			$data['soldout'] = "품절";
			if($data['stat'] != 3) {
				$data['soldout'] = "정상";
			}
			$data['class'] = ($data['soldout'] == "품절") ? 'soldout' : '';

			$file_dir = getFileDir($data['updir']);
			if($data['upfile3']) {
				$data['imgstr'] = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' class='prdimgs' width='41' height='50'>";
			} else {
				$data['imgstr'] = "<img src='$file_dir/$cfg[noimg3_mng]' class='prdimgs' width='41' height='50'>";
			}
			return $data;
		}
	}

	$sch_prd_stat = $prd_stat;
	if(!$sch_prd_stat) $sch_prd_stat = '1';
	${'list_tab_active'.$sch_prd_stat} = 'class="active"';

	$xls_query = makeQueryString('body');
	if($body == 'log@wish_list_excel.exe') {
		return;
	}

?>
<form name="searchFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="search_on" value="Y">
	<div class="box_title first">
		<h2 class="title">위시리스트 통계</h2>
	</div>

	<div id="search">
		<div class="box_search box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"::선택::",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
			<table class="tbl_row">
			<caption class="hidden">위시리스트 통계</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<?if($search_on == "Y"){?>
<form name="prdFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="atbl" value="<?=$tbl[wish]?>">
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active1?>>전체<span><?=$total?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&prd_stat=2" <?=$list_tab_active2?>>정상<span><?=$normal?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&prd_stat=3" <?=$list_tab_active3?>>품절<span><?=$soldout?></span></a></li>
			<span class="box_btn_s btns icon excel"><a href="./?body=log@wish_list_excel.exe<?=$xls_query?>">엑셀다운</a></span>
		</ul>
	</div>
	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="20" <?=checked($row,20,1)?>>20</option>
					<option value="50" <?=checked($row,50,1)?>>50</option>
					<option value="100" <?=checked($row,100,1)?>>100</option>
				</select>
			</dd>
		</dl>
	</div>
	<!-- //정렬 -->
	<table class="tbl_col">
		<caption class="hidden">위시리스트 통계 리스트</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:60px">
			<col style="width:80px">
			<col>
			<col style="width:120px">
			<col style="width:120px">
			<col style="width:120px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col" colspan="2">상품명</th>
				<th scope="col">품절여부</th>
				<th scope="col">상품가격</th>
				<th scope="col">회원명</th>
				<th scope="col">등록일</th>
			</tr>
		</thead>
		<tbody>
			<?while($data=wishList(3,50,50)){?>
				<tr class =<?=$data['class']?>>
					<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['wno']?>"></td>
					<td><?=$idx?></td>
					<td class="nobd"><a href="<?=$data[link]?>" target="_blank"><?=$data['imgstr']?></a></td>
					<td class="left"><a href="<?=$data[link]?>" target="_blank"><?=$data['name']?></a></td>
					<td><?=$data['soldout']?></td>
					<td><?=$data['sell_prc_str']?> 원</td>
					<td><a href="javascript:;" onclick="viewMember('<?=$data['member_no']?>')"><?=$data['member_name']?> <?=blackIconPrint($data['blacklist'])?></br>(<?=$data['member_id']?>)</a></td>
					<td><?=date("Y/m/d",$data[reg_date])?></td>
				</tr>
				<?
					$idx--;
			}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn gray left_area"><input type="button" value="선택 삭제" onclick="deleteWish(document.prdFrm)"></span>
		<?=$pg_res?>
	</div>
</form>
<?} else {?>
<div class="box_full p_color2 center">전체 데이터량이 많을 경우 조회시간이 길어질 수 있습니다. ※</div>
<?}?>

<script type="text/javascript">
	function deleteWish(f){
		if(!checkCB(f.check_pno,"삭제할 Wish상품을")) return;
		if (!confirm('선택하신 Wish상품을 삭제하시겠습니까?')) return;
		f.body.value="log@cart.exe";
		f.exec.value="deleteWish";
		f.submit();
	}
</script>