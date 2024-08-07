<?PHP

	if($_POST['exec'] == 'restore') {
		$pdo->query("SET @is_price_rollback='Y';");

		$no = implode(',', numberOnly($_POST['no']));
		if(empty($no) == true) {
			msg('복구할 상품을 선택해주세요.');
		}

		$success = 0;
		$res = $pdo->iterator("select pno, ori_sell_prc from {$tbl['product_price_log']} where no in ($no)");
        foreach ($res as $data) {
			$r = $pdo->query("update {$tbl['product']} set sell_prc='{$data['ori_sell_prc']}' where no='{$data['pno']}'");
			if($r) {
				$success += $pdo->lastRowCount();
			}
		}

		if($success == 0) msg('변경된 상품이 없습니다.');
		else {
			msg(number_format($success).'개의 상품이 복구되었습니다.', 'reload', 'parent');
		}
	}

	$runid = numberOnly($_POST['runid']);

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_POST['page']);
	if($page <= 1) $page = 1;
	$row = 500;
	$block = 20;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['product_price_log']} l inner join {$tbl['product']} p on l.pno=p.no where l.runid='$runid' order by null");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, null, 'viewDetailPage');
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result('ajax_admin');
	$pg_res = $PagingResult['PageLink'];

	$res = $pdo->iterator("select l.no, l.pno, p.name, p.updir, p.upfile3, p.w3, p.h3, p.sell_prc, p.wm_sc, l.ori_sell_prc, l.new_sell_prc from {$tbl['product_price_log']} l inner join {$tbl['product']} p on l.pno=p.no where l.runid='$runid' order by p.name asc, p.no asc ".$PagingResult['LimitQuery']);

	function parseLog($res) {
		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$file_dir = getFileDir($data['updir']);
		$is = setImageSize($data['w3'], $data['h3'], 50, 50);
		$data['imgstr'] = "<img src='$file_dir/{$data['updir']}/{$data['upfile3']}' class='prdimgs' $is[2]>";
		$data['name'] = stripslashes($data['name']);

		$data['link'] = '?body=product@product_register&pno='.$data['pno'];
		$data['class'] = ($data['ori_sell_prc'] == $data['sell_prc']) ? 'p_color2' : '';

		return $data;
	}

?>
<form class="box_middle4" method="post" action="?" onsubmit="return restorePrice(this);">
	<input type="hidden" name="body" value="product@product_price_log.exe">
	<input type="hidden" name="exec" value="restore">

	<table class="tbl_inner line full">
		<colgroup>
			<col style="width:50px">
			<col style="width:60px">
			<col>
			<col style="width:110px">
			<col style="width:110px">
			<col style="width:110px">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" class="all_chkbox"></th>
				<th>이미지</th>
				<th>상품명</th>
				<th>변경 전 <?=$cfg['product_sell_price_name']?></th>
				<th>변경 후 <?=$cfg['product_sell_price_name']?></th>
				<th>현재 <?=$cfg['product_sell_price_name']?></th>
			</tr>
		</thead>
		<tbody>
			<?php while ($data = parseLog($res)) { ?>
			<tr>
				<td>
					<?php if (empty($data['class']) == true) { ?>
					<input type="checkbox" name="no[]" value="<?=$data['no']?>" class="sub_chkbox">
					<?php } ?>
				</td>
				<td>
                    <a href="<?=$data['link']?>" target="_blank"><?=$data['imgstr']?></a>
                    <?php if ($data['wm_sc'] > 0) { ?>
                    바로가기
                    <?php } ?>
                </td>
				<td class="left"><a href="<?=$data['link']?>" target="_blank"><?=$data['name']?></a></td>
				<td><?=parsePrice($data['ori_sell_prc'], true)?></td>
				<td><?=parsePrice($data['new_sell_prc'], true)?></td>
				<td><strong class="<?=$data['class']?>"><?=parsePrice($data['sell_prc'], true)?></strong></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span id="stpBtn" class="box_btn blue"><input type="submit" value="복구"></span>
		</div>
		<div class="right_area">
			<span class="box_btn"><input type="button" value="닫기" onclick="$('.details').remove();"></span>
		</div>
	</div>
</form>
<script type="text/javascript">
new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
</script>