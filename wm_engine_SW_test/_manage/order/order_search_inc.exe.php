<?PHP

	if($_POST['exec'] == 'prd' || $body == 'order@order_list' || $body == 'order@order_trash' || $body == 'order@sbscr_list') {
		printAjaxHeader();

		$pno = numberOnly($_REQUEST['pno']);
		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$pno'");
		$prd = shortCut($prd);
		$prd['name'] = stripslashes($prd['name']);
		$prd['thumb'] = getFileDir($prd['updir'])."/$prd[updir]/$prd[upfile3]";

		$total_buy_ea = $pdo->row("select sum(buy_ea) from $tbl[order_product] where pno='$pno' and stat between 1 and 5");
		?>
		<input type="hidden" name="pno" value="<?=$prd['no']?>">
		<div class="box_setup">
			<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><img src='<?=$prd['thumb']?>' height='80px;'></a></div>
			<dl>
				<dt class="title"><a href='?body=product@product_register&pno=<?=$prd['no']?>' target='_blank'><?=$prd['name']?></a></dt>
				<dd class="cstr"><?=$prd['origin_name']?></dd>
				<dd><?=number_format($prd['sell_prc'])?> 원</dd>
				<dd>총 주문수량 <strong><?=number_format($total_buy_ea)?></strong> 개 (취소주문 제외)</dd>
				<dd style="padding-top:5px;"><input type="text" name="optstr" size="20" class="input" placeholder="옵션 검색" value="<?=$optstr?>"></dd>
			</dl>
		</div>
		<?
	}

?>