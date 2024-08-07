<?if($_SERVER['SCRIPT_NAME'] == '/shop/detail.php' && $prd['no'] > 0) {?>
<meta property="recopick:title" content="<?=strip_tags($prd['name'])?>">
<meta property="recopick:image" content="<?=getFileDir($prd['updir'])?>/<?=$prd['updir']?>/<?=$prd['upfile2']?>">
<meta property="recopick:price" content="<?=$prd['sell_prc']?>">
<meta property="recopick:price:currency" content="KRW">
<meta property="recopick:description" content="<?=strip_tags(htmlspecialchars(stripslashes($prd['content1'])))?>">
<?
if($prd['partner_no'] == 0) {
	$recopick_author = $cfg['company_mall_name'];
} else {
	$recopick_author = stripslashes($pdo->row("select corporate_name from $tbl[partner_shop] where no='$prd[partner_no]'"));
}
?>
<meta property="recopick:author" content="<?=$recopick_author?>">
<meta property="recopick:sale_price" content="<?=$prd['sell_prc']?>">
<meta property="recopick:sale_price:currency" content="KRW">
<?if($prd['stat']==3) {?>
<meta property="recopick:availability" content="oos">
<?}?>
<?}?>