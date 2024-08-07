<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  옵션 세트 추가
	' +----------------------------------------------------------------------------------------------+*/
	$stat = '5';
	include $engine_dir.'/_manage/product/product_option.frm.php';

?>
<form>
	<div class="box_title">
		<h2 class="title">옵션세트 관리</h2>
	</div>
	<iframe name="optFrame" src="./?body=product@product_option_list.frm&stat=<?=$stat?>" style="width:100%" scrolling="no" frameborder="0"></iframe></td>
</form>