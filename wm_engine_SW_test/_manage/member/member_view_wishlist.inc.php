<?php

	$prd_stat = numberOnly($_GET['prd_stat']);
	$list_tab_qry = makeQueryString(true, 'page', 'prd_stat');
	if(!$prd_stat) $prd_stat = '1';
	${'list_tab_active'.$prd_stat} = 'class="active"';

	$total = $pdo->row("select count(*) from `".$tbl['wish']."`w inner join `".$tbl['product']."` p on w.`pno`=p.`no` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` where  p.`stat` != '4' and w.`member_no` = '".$mno."' ");
	$normal = $pdo->row("select count(*) cnt from (select w.`pno` from`".$tbl['product']."` p inner join`".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` where w.`member_no` = '".$mno."' and  p.`stat` = 2) cnt");
	$soldout = $total - $normal;

	if($prd_stat){
		switch($prd_stat) {
			case "2" : $_where_qry = "p.`stat` = 2 and"; break;
			case "3" : $_where_qry = "p.`stat` = 3 and"; break;
		}
	}
	$sql="select p.`no`, p.`hash`, p.`name` as prd_name, p.`stat`, p.`sell_prc` , p.`ea_type` , p.`updir` , p.`upfile3`, p.`w3`, p.`h3` , w.`no` as wno, w.`pno`, w.`member_no` , w.`reg_date`, m.`member_id`, m.`name`as member_name, m.`cell`, m.`email` from `".$tbl['product']."` p inner join  `".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` where ".$_where_qry." m.`no` = '".$mno."' and p.`stat` !=4 group by  w.`no` order by w.`no` desc";
	$sql2="select count(*) cnt from (select w.`pno` from`".$tbl['product']."` p inner join`".$tbl['wish']."` w on p.`no` = w.`pno` inner join `".$tbl['member']."` m on w.`member_no` = m.`no` where ".$_where_qry." m.`no` = '".$mno."' and p.`stat` !=4  group by w.`no`)  cnt ";

	include_once $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	$row = 20;
	$block = 10;
	$NumTotalRec = $pdo->row($sql2);

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	if($body != 'log@wish_list_excel.exe') {
		$sql .= $PagingResult['LimitQuery'];
	}
	$pageRes = $PagingResult['PageLink'];

	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page-1));

	function wishList($imgn=3,$w=10,$h=10) {
		global $root_url, $tbl, $cfg;
		$cnt = 0;
		$data = $GLOBALS['res']->current();
        $GLOBALS['res']->next();
		if($data == false) return false;
		$cnt++;
		$data = shortCut($data);
		$data['name'] = stripslashes($data['prd_name']);
		$data['sell_prc_str'] = number_format($data['sell_prc']);
		$data['link'] = $root_url."/shop/detail.php?pno=".$data['hash'];
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

	$xls_query = makeQueryString('body');
	if($body == 'log@wish_list_excel.exe') {
		return;
	}
?>
<table class="tbl_col tbl_col_bottom">
	<div class="box_tab first">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active1?>>전체<span><?=$total?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&prd_stat=2" <?=$list_tab_active2?>>정상<span><?=$normal?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&prd_stat=3" <?=$list_tab_active3?>>품절<span><?=$soldout?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=log@wish_list_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
	<div class="box_sort">
	</div>
	<caption class="hidden">위시리스트</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:80px;">
		<col>
		<col style="width:90px;">
		<col style="width:90px;">
		<col style="width:90px;">
	</colgroup>
	<?PHP
	?>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col" colspan="2">상품명</th>
			<th scope="col">품절여부</th>
			<th scope="col">상품가격</th>
			<th scope="col">등록일</th>
		</tr>
	</thead>
	<tbody>
		<?while($data=wishList(3,50,50)){?>
			<?if($data['no']) {?>
				<tr class =<?=$data['class']?>>
					<td><?=$idx?></td>
					<td class="nobd"><a href="<?=$data['link']?>" target="_blank"><?=$data['imgstr']?></a></td>
					<td class="left"><a href="<?=$data['link']?>" target="_blank"><?=$data['name']?></a></td>
					<td><?=$data['soldout']?></td>
					<td><?=$data['sell_prc_str']?> 원</td>
					<td><?=date("Y/m/d",$data['reg_date'])?></td>
				</tr>
			<?
				$idx--;
			}
		}?>
	</tbody>
</table>
<div class="box_bottom"><?=$pageRes?></div>