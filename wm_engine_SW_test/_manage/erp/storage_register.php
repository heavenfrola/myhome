<?PHP

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from $tbl[erp_storage] where no='$no'");
		$data = array_map('stripslashes', $data);
	}

	$w = '';
	if($data['big']) {
		$w .= " or (level='2' and big='".$data['big']."')";
	}
	if($data['mid']) {
		$w .= " or (level='3' and mid='".$data['mid']."')";
	}
	if($data['small']) {
		$w .= " or (level='4' and small='".$data['small']."')";
	}

	$res = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='9' and (level='1' $w ) order by ctype, level, sort");
    foreach ($res as $cate) {
		$sel = ($cate['no'] == $data[$_cate_colname[1][$cate['level']]]) ? "selected" : "";
		${'item_'.$cate['ctype'].'_'.$cate['level']} .= "\n<option value='".stripslashes($cate['no'])."' $sel>".stripslashes($cate['name'])."</option>";
	}

?>
<form method="post" enctype="multipart/form-data" action='./index.php' target='hidden<?=$now?>'>
	<input type='hidden' name='body' value='erp@storage.exe'>
	<input type='hidden' name='no' value='<?=$data['no']?>'>

	<table class="tbl_row">
		<caption>창고정보 입력</caption>
		<colgroup>
			<col style="width:200px;">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">창고위치</th>
				<td>
					<select name="sbig" onchange="chgCateInfinite(this, 2, 's');">
						<option value="">::대분류::</option>
						<?=$item_9_1?>
					</select>
					<select name="smid" onchange="chgCateInfinite(this, 3, 's');">
						<option value="">::중분류::</option>
						<?=$item_9_2?>
					</select>
					<select name="ssmall" onchange="chgCateInfinite(this, 4, 's');">
						<option value="">::소분류::</option>
						<?=$item_9_3?>
					</select>
					<select name="sdepth4">
						<option value="">::세분류::</option>
						<?=$item_9_4?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">창고별명</th>
				<td><input type="text" name="name" class="input input_full" value="<?=$data['name']?>"></td>
			</tr>
			<tr>
				<th scope="row">담당자</th>
				<td><input type="text" name="dam" class="input" value="<?=$data['dam']?>"></td>
			</tr>
			<tr>
				<th scope="row">사진1</th>
				<td>
					<input type="file" name="upfile1" class="input">
					<?=delImgStr($data, 1)?>
				</td>
			</tr>
			<tr>
				<th scope="row">사진2</th>
				<td>
					<input type="file" name="upfile2" class="input">
					<?=delImgStr($data, 2)?>
				</td>
			</tr>
			<tr>
				<th scope="row">설명</th>
				<td>
					<textarea name="content" class="txta"><?=$data['content']?></textarea>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="document.location.href='<?=getListURL('?body=erp@storage')?>'"></span>
	</div>
</form>