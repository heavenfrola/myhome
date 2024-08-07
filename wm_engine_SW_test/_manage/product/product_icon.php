<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품아이콘 관리
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_manage/product/product_icon.inc.php';
	$imode = 1;
	$_itype[2] = '이벤트';
	$_itype[5] = '무료배송';
	$_itype[7] = '단독배송';
	$_itype[6] = '오늘출발';
	$_itype[4] = '품절';

?>
<div class="box_title first">
	<h2 class="title">자동 아이콘</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">자동 아이콘</caption>
	<colgroup>
		<col style="width:15%">
		<col>
	</colgroup>
	<?
		foreach($_itype as $key=>$val) {
			$data = get_info($tbl['product_icon'], 'itype', $key);
	?>
	<tr>
		<th scope="row"><?=$val?></th>
		<td>
			<form method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkBlank(this.upfile,'찾아보기를 눌러 업로드할 아이콘을 입력해주세요.')">
				<input type="hidden" name="body" value="product@product_icon.exe">
				<input type="hidden" name="itype" value="<?=$key?>">
				<input type="hidden" name="imode" value="<?=$imode?>">
				<input type="hidden" name="ino" value="<?=$data[no]?>">
				<input type="hidden" name="exec" value="delete">
				<input type="file" name="upfile" class="input input_full">
				<span class="box_btn_s"><input type="submit" value="등록"></span>
				<?=getIconTag($data)?>
				<?if($data[no]){?>
				<span class="box_btn_s gray"><input type="button" value="삭제" onclick="delPrdIcon('<?=$data[no]?>')"></span>
				<?}?>
			</form>
		</td>
	</tr>
	<?}?>
</table>
<?include $engine_dir.'/_manage/product/product_icon_list.php';?>