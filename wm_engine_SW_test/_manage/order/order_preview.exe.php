<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품 미리보기
	' +----------------------------------------------------------------------------------------------+*/

	$ono = addslashes(trim($_GET['ono']));
	$sbscr = ($_GET['sbscr']=='Y') ? 'Y':'N';
	if($sbscr=='Y') {
		$tbl_order_product = $tbl['sbscr_product'];
		$tbl_where = " and sbono='$ono'";
	}else {
		$tbl_order_product = $tbl['order_product'];
		$tbl_where = " and ono='$ono'";
	}
	if($admin['level'] == 4) {
		$tbl_where .= " and p.partner_no='{$admin['partner_no']}'";
	}
	$res = $pdo->iterator("
		select p.name, p.updir, p.upfile3, op.*
		from $tbl_order_product op inner join $tbl[product] p on op.pno=p.no
		where 1 $tbl_where order by op.stat asc, op.no asc
	");

    $set_ids = array();
    $set_id = 0;

	function parseProduct($res, $w, $h) {
		global $_order_sales, $set_ids, $set_id;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['name'] = strip_tags(stripslashes($data['name']));
		$img = prdImg(3, $data, $w, $h);
		$data['img'] = $img[0];
		$data['option'] = strip_tags(parseOrderOption(stripslashes($data['option'])));
        $data['canceled'] = (in_array($data['stat'], array(13, 15, 17, 19)) == true) ? 'canceled' : '';

        if ($data['set_idx']) {
            if (in_array($data['set_idx'], $set_ids) == false) {
                $set_ids[] = $data['set_idx'];
                $set_id++;
            }
            $data['set_id'] = $set_id;
        }

		foreach($_order_sales as $key => $val) { // 할인가 반영
			$data['total_prc'] -= $data[$key];
		}

		return $data;
	}

?>
<ul class="productPreview">
	<?while($data = parseProduct($res, 50, 50)) {?>
	<li>
		<a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank" class="img">
            <img src="<?=$data['img']?>">
            <?php if ($data['set_id']) { ?>
            <div class="set">SET<?=$data['set_id']?></div>
            <?php } ?>
        </a>
		<div class="info">
			<p class="name"><strong><?=$data['name']?></strong></p>
			<p class="opt"><?=$data['option']?></p>
			<p class="price <?=$data['canceled']?>"><?=parsePrice($data['total_prc'], true)?> <?=$cfg['currency']?> (<?=$data['buy_ea']?>)</p>
			<p style="color:<?=$_order_color[$data['stat']]?>"><?=$_order_stat[$data['stat']]?></p>
		</div>
	</li>
	<?}?>
</ul>