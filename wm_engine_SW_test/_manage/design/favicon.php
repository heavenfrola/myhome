<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  파비콘 설정
' +----------------------------------------------------------------------------------------------+*/

$favicon = '';
if ($cfg['favicon'] == 'Y') {
    $favicon = $root_url.'/favicon.ico';
} else if ($cfg['favicon']) {
    $favicon = getListImgURL('_data/favicon', $cfg['favicon']);
}

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@favicon.exe">
	<div class="box_title first">
		<h2 class="title">파비콘 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">파비콘 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">아이콘 파일</th>
			<td>
				<input type="file" name="icon" class="input input_full">
                <?php if ($favicon) { ?>
                <img src='<?=$favicon?>' style="max-width: 16px">
                <?php } ?>
				<p class="explain">ico, png 이미지만 업로드할 수 있습니다.</p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>