<?PHP

	printAjaxHeader();

	$orderby = numberOnly($_GET['orderby']);

	if($_GET['pgrp_no']) {
		$pgrp_no = numberOnly($_GET['pgrp_no']);
		$res = $pdo->iterator("select * from $tbl[promotion_pgrp_link] where pgrp_no='$pgrp_no' order by `sort` asc");
        foreach ($res as $data) {
			$pno[] = $data['pno'];
		}
		$NumTotalRec = $pdo->row("select * from $tbl[promotion_pgrp_link] where pgrp_no='$pgrp_no' order by `sort` asc");
	}else if($_GET['pno']) {
		$pno = numberOnly($_GET['pno']);
	}
	if($pno) {
		if($orderby) {
			$os = " order by ".$_prd_by[$orderby];
		}else {
			$os = " order by field(no,".implode(',', $pno).")";
		}
		$ws = " and no in (".implode(',', $pno).")";
		$gsql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord from $tbl[product] where wm_sc = 0 $ws $os";
		$NumTotalRec = $pdo->row("select count(*) from $tbl[product] where wm_sc = 0 $ws");
		$_old_pno = implode('|', $pno);
	}

	// 페이징
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 5;
	if($row > 30) $row = 30;
	$block=5;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, '', 'prm_sort_submit');
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result('ajax_admin');

	$gsql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$gres = $pdo->iterator($gsql);
    $idx = 0;
	$sort = ($row*($page-1))+1;

?>
<table class="tbl_col" id="prm_sort" name="prm_sort">
	<input type="hidden" id="pno" name="pno" value="<?=$_old_pno?>">
	<caption class="hidden">상품검색</caption>
	<colgroup>
		<col style="width:50px">
		<col>
		<col style="width:80px">
		<col style="width:80px">
		<col style="width:80px">
		<col style="width:80px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순번</th>
			<th scope="col">상품</th>
			<th scope="col">가격</th>
			<th scope="col">적립금</th>
			<th scope="col">상태</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
    <?php
		if($NumTotalRec>0) {
            foreach ($gres as $prd) {
				$prd['name'] = strip_tags(stripslashes($prd['name']));
				$prd['sell_prc'] = parsePrice($prd['sell_prc'], true);
				$prd['milage'] = parsePrice($prd['milage'], true);

				if($prd['upfile3']) {
					$file_dir = getFileDir($prd['updir']);
					$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='40' height='40'>";
				}

?>
				<tr id="<?=$prd['no']?>">
					<td><?=++$idx?></td>
					<td class="left">
						<div class="box_setup">
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
					<td><span class="box_btn_s"><input type="button" value="삭제" onclick="pgsearch.pcan(<?=$prd['no']?>)"></span></td>
				</tr>
<?php
		}
	}

?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>
<script type="text/javascript">
var srt3 = null;
$(function() {
	srt3 = new Sorttbl('prm_sort');
});
</script>