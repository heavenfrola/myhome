<?PHP

	$menu = numberOnly($_GET['MENU']);
	$cellid = str_replace('-', '', $amember['cell']);

?>
<iframe
	src='http://203.239.176.82/solution/widoo/?corp=<?=$erpListener->getCorpCd()?>&MENU=<?=$menu?>&member_id=<?=$erpListener->setMemberPrefix($mid)?>&cell=<?=$cellid?>'
	frameborder='0'
	style='width:810px; height: 680px;'
>
</iframe>
<script type="text/javascript">
	$('body').css('overflow','hidden');
</script>