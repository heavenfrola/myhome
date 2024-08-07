<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  옵션별 부가이미지 업로드 폼
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$data = $pdo->assoc("select * from `$tbl[product_option_img]` where `opno`='$opno' and `idx`='$idx'");
	if($data) {
		$file_url = getFileDir($data['updir']);
		$size1 = setimagesize($data['w1'], $data['h1'], 100, 50);
		$size2 = setimagesize($data['w2'], $data['h2'], 100, 50);
	}

?>
<input type="hidden" name="body" value="product@product_option.exe">
<input type="hidden" name="exec" value="attach">
<input type="hidden" name="pno" value="<?=$pno?>">
<input type="hidden" name="opno" value="<?=$opno?>">
<input type="hidden" name="idx" value="<?=$idx?>">
<table class="tbl_row">
	<caption>부가이미지</caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<td colspan="2">
			<ul class="list_msg">
				<li>상품의 <?=($idx+1)?> 번째 옵션에 사용할 부가이미지를 업로드 해 주세요.</li>
				<li>옵션을 삭제하실 경우 업로드 된 이미지도 같이 삭제됩니다.</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th>부가이미지 1</th>
		<td><input type="file" name="upfile1" class="input" size="15"></td>
	</tr>
	<tr>
		<th>부가이미지 2</th>
		<td><input type="file" name="upfile2" class="input" size="15"></td>
	</tr>
</table>

<?if($data['upfile1'] || $data['upfile2']) {?>
<table cellpadding="0" cellspacing="0" class="register" style="width: 100%">
	<col width="50%"><col width="50%">
	<tr>
		<td>
			<?if($data['upfile1']){?>
			<a href="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile1']?>" target="_blank"><img src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile1']?>" <?=$size1[2]?>></a>
			<input type="checkbox" name="delete1" value="ok"> 이미지 삭제
			<?}?>
		</td>
		<td>
			<?if($data['upfile2']){?>
			<a href="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile2']?>" target="_blank"><img src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile2']?>" <?=$size2[2]?>></a>
			<input type="checkbox" name="delete2" value="ok"> 이미지 삭제
			<?}?>
		</td>
	</tr>
</table>
<?}?>

<div class="pop_bottom">
	<span class="box_btn blue"><input type="submit" value="부가사진 업로드"></span>
	<span class="box_btn gray"><input type="button" value="닫기" onclick="optAttach()"></span>
</div>