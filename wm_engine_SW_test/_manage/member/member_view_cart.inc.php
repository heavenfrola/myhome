<?php

	$prd_stat = numberOnly($_GET['prd_stat']);
	$list_tab_qry = makeQueryString(true, 'page', 'prd_stat');
	if(!$prd_stat) $prd_stat = '1';
	${'list_tab_active'.$prd_stat} = 'class="active"';

	if($prd_stat){
		switch($prd_stat) {
			case "2" : $_where_qry[] = "(p.`stat` !=3 and (p.`ea_type`=2 or (o.`qty` >0 and `force_soldout`='L') or o.`force_soldout`='N'))"; break;
			case "3" : $_where_qry[] = "(p.`stat` =3 or (p.`ea_type`=1 and (o.`qty` <= 0 and `force_soldout`='L') or o.`force_soldout`='Y'))"; break;
		}
	}

	$_where_qry[] = "p.`no`=c.`pno` and p.`stat`!='4' and c.member_no = '".$mno."'";
	$where_qry = count($_where_qry)?" where ".implode(" and ",$_where_qry):"";

	$sql = "select  p.`no`, p.`hash`, p.`name` as prd_name, c.option, p.`stat`, p.`sell_prc` , p.`updir` , p.`upfile3` , p.`w3` , p.`h3` ,c.`no` as wno, c.`pno`, c.`member_no` , c.`reg_date`, c.buy_ea, o.`qty`, o.`force_soldout`  , p.`stat`, p.`ea_type`, m.`member_id`, m.`name`as member_name, m.`cell`, m.`email` ".$member_select." from `".$tbl['product']."` p inner join `".$tbl['cart']."` c on  p.no = c.pno  left join `erp_complex_option` o on c.complex_no = o.complex_no inner join `".$tbl['member']."` m on c.member_no = m.no".$where_qry." order by c.`no` desc";

	$sql2="select count(c.`pno`) from `".$tbl['product']."` p inner join `".$tbl['cart']."` c on p.no = c.pno left join `erp_complex_option` o on c.complex_no = o.complex_no inner join `".$tbl['member']."` m on c.member_no = m.no".$where_qry;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if($row < 1 || $row>1000) $row = 20;
	$block = 10;
	$NumTotalRec = $pdo->row($sql2);
	$total = $pdo->row("select count(*) from `".$tbl['cart']."`c inner join `".$tbl['product']."`p on  p.no = c.pno where c.`member_no` =  '".$mno."' and p.`stat` != 4");
	$soldout = $pdo->row("select count(*) from `".$tbl['product']."` p inner join `".$tbl['cart']."` c on  p.no = c.pno  left join `erp_complex_option` o on c.complex_no = o.complex_no inner join `".$tbl['member']."` m on c.member_no = m.no where `member_no` =  '".$mno."' and (p.`stat` =3 or (p.`ea_type`=1 and (o.`qty` <= 0 and `force_soldout`='L') or o.`force_soldout`='Y'))");
	$normal = $total - $soldout;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	if($body != 'log@cart_list_excel.exe') {
		$sql.=$PagingResult[LimitQuery];
	}

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	function cartListview($imgn=3,$w=10,$h=10) {
		global $root_dir, $cfg;
		$data = $GLOBALS['res']->current();
        $GLOBALS['res']->next();
		if($data == false) return false;
		$data = shortCut($data);
		$data['name'] = stripslashes($data['prd_name']);
		$data['sell_prc_str'] = number_format($data['sell_prc']);
		$data['link'] = $GLOBALS['root_url']."/shop/detail.php?pno=".$data['hash'];
		if($data['stat'] == 3 || ($data['ea_type'] == 1 && ($data['force_soldout'] ==  'Y' || ($data['qty']  < 1 && $data['force_soldout'] == 'L')))) {
			$data['soldout'] = "품절";
			$data['class'] =  'soldout';
		} else {
			$data['soldout'] = "정상";
			$data['class'] =  '';
		}

		$option = array();
		$tmp = explode('<split_big>', $data['option']);
		foreach($tmp as $val) {
			if($val) {
				list($name, $value) = explode('<split_small>', $val);
				$option[] = "<li><strong>$name</strong> : $value</li>";
			}
		}
		$data['option'] = "<ul class='desc1 square'>".implode($option)."</ul>";
		$file_dir = getFileDir($data['updir']);
		if($data['upfile3']) {
			$data['imgstr'] = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' class='prdimgs' width='41' height='50'>";
		} else {
			$data['imgstr'] = "<img src='$file_dir/$cfg[noimg3_mng]' class='prdimgs' width='41' height='50'>";
		}
		return $data;
	}

	$xls_query = makeQueryString('body');

	if($body == 'log@cart_list_excel.exe') {
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
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=log@cart_list_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
	<div class="box_sort">
	</div>
	<caption class="hidden">장바구니</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:80px;">
		<col>
		<col style="width:150px;">
		<col style="width:90px;">
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
			<th scope="col">옵션</th>
			<th scope="col">품절여부</th>
			<th scope="col">수량</th>
			<th scope="col">상품가격</th>
			<th scope="col">등록일</th>
		</tr>
	</thead>
	<tbody>
		<?while($data=cartListview(3,50,50)){	?>
			<tr class =<?=$data['class']?>>
				<td><?=$idx?></td>
				<td class="nobd"><a href="<?=$data['link']?>" target="_blank"><?=$data['imgstr']?></a></td>
				<td class="left"><a href="<?=$data['link']?>" target="_blank"><?=$data['name']?></a></td>
				<td class="left"><?=$data['option']?></td>
				<td><?=$data['soldout']?></td>
				<td><?=$data['buy_ea']?></td>
				<td><?=$data[sell_prc_str]?> 원</td>
				<td><?=date("Y/m/d",$data[reg_date])?></td>
			</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom"><?=$pageRes?></div>