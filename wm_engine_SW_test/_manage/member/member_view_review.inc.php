<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">상품리뷰</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:80px">
		<col style="width:200px;">
		<col>
		<col style="width:50px;">
		<col style="width:120px;">
	</colgroup>
	<?PHP

		$sql = "select * from `".$tbl['review']."` where `member_no` = '".$mno."' $id_where order by `reg_date` desc ".$limitq;
		if(!$limitq) {
			include_once $engine_dir.'/_engine/include/paging.php';

			$page = numberOnly($_GET['page']);
			if($page <= 1) $page = 1;
			$row = 20;
			$block = 10;

			$NumTotalRec = $pdo->row("select count(*) from `".$tbl['review']."` where `member_no` = '".$mno."' ".$id_where);
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString(makeQueryString('page'));
			$PagingResult = $PagingInstance->result($pg_dsn);
			$sql .= $PagingResult['LimitQuery'];

			$pageRes = $PagingResult['PageLink'];
		} else {
			$NumTotalRec = $pdo->row("select count(*) from ($sql) a");
		}
		$res = $pdo->iterator($sql);
		$idx = $NumTotalRec - ($row * ($page-1));

	?>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col" colspan="2">상품명</th>
			<th scope="col">제목</th>
			<th scope="col">점수</th>
			<th scope="col">등록일</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$prd = $pdo->assoc("select no, hash, name, updir, upfile3, w3, h3 from `$tbl[product]` where no='$data[pno]'");
				$data['pname'] = cutStr(stripslashes($prd['name']), 25);
				$data['hash'] = $prd['hash'];
				$data['rno'] = $data['no'];
				$data['rreg_date'] = date("Y/m/d",$data['reg_date']);
				$img = prdImg(3, $prd, 50, 50);
		?>
		<tr>
			<td>
				<input type="hidden" name="pno[]" value="<?=$data['no']?>">
				<?=$idx?>
			</td>
			<td class="nobd">
				<?if($prd['no'] > 0) {?><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><img src="<?=$img[0]?>" <?=$img[1]?>></a><?}?>
			</td>
			<td class="left">
				<?if($prd['no'] > 0) {?><a href="?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><?=$data['pname']?></a><?}?>
			</td>
			<td class="left"><a href="javascript:;" onClick="wisaOpen('./pop.php?body=member@product_review_view.frm&no=<?=$data['rno']?>','mng_review','yes')"><?=cutStr(stripslashes($data['title']),50)?></a></td>
			<td><?=$data['rev_pt']?></td>
			<td title="<?=$data['rreg_date']?>"><?=$data['rreg_date']?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pageRes?></div>