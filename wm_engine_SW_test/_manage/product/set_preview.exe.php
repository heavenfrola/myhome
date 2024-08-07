<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  세트상품 리스트에서 미리보기
' +----------------------------------------------------------------------------------------------+*/

printAjaxHeader();

$pno = addslashes(trim($_GET['pno']));

if($cfg['max_cate_depth'] == '4') {
    $afield .= ", depth4";
}

function parseProduct($res, $w, $h) {
    global $_order_sales;

    $data = $res->current();
    $res->next();
    if($data == false) return false;

    $data['name'] = strip_tags(stripslashes($data['name']));
    $img = prdImg(3, $data, $w, $h);
    $data['img'] = $img[0];
    $data['option'] = strip_tags(parseOrderOption(stripslashes($data['option'])));

    foreach($_order_sales as $key => $val) { // 할인가 반영
        $data['total_prc'] -= $data[$key];
    }

    return $data;
}

if($body == 'product@set_preview_inc.exe') return;

$res = $pdo->iterator("
    select
        p.no, p.hash, p.name, p.updir, p.upfile3, p.sell_prc, p.stat, p.big, p.mid, p.small $afield
    from {$tbl['product_refprd']} r inner join {$tbl['product']} p on r.refpno=p.no
    where r.pno='$pno' and r.`group`=99 order by r.sort asc
");
if($res->rowCount() == 0) return;

?>
<ul class="productPreview">
	<?php while($data = parseProduct($res, 50, 50)) { ?>
	<li>
		<a class="img"><img src="<?=$data['img']?>"></a>
		<div class="info">
			<p class="name"><strong><?=$data['name']?></strong></p>
			<p class="price"><?=parsePrice($data['sell_prc'], true)?></p>
			<p><?=$_prd_stat[$data['stat']]?></p>
		</div>
	</li>
	<?php } ?>
</ul>