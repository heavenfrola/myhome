<?PHP

	$skin_name = str_replace('/', '', $_GET['skinname']);
	if(empty($skin_name) == true || is_dir($root_dir.'/_skin/'.$skin_name) == false) {
		msg('복사할 스킨을 선택해주세요.', 'close');
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">스킨 복사</div>
	</div>
	<div id="popupContentArea">
		<form method="post" action="./index.php" onsubmit="return skinCopy(this);">
			<input type="hidden" name="body" value="design@design_config.exe">
			<input type="hidden" name="exec" value="skin_copy">
			<input type="hidden" name="selected_skin" value="<?=$skin_name?>">
			<input type="hidden" name="type" value="<?=$_GET['type']?>">

			<table class="tbl_row">
				<caption class="hidden">스킨 복사</caption>
				<colgroup>
					<col style="width:20%">
					<col>
				</colgroup>
				<tr>
					<th scope="row">원본 스킨명</th>
					<td>
						<strong><?=$skin_name?></strong>
					</td>
				</tr>
				<tr>
					<th scope="row">복사 스킨명</th>
					<td>
						<input type="text" name="skin_name" value="<?=$skin_name?>_1" class="input" size="50">
						<ul class="list_info">
							<li>복사한 스킨 명은 영문, 숫자, 일부 특수문자( _ - ) 만 입력할 수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">스킨 설명글</th>
					<td>
						<input type="text" name="skin_comment" class="input" size="50">
					</td>
				</tr>
			</table>
			<div id="stpBtn" class="pop_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
				<span class="box_btn gray"><input type="button" value="취소" onclick="skinDialog.close();"></span>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
function skinCopy(f) {
	if(confirm('<?=$skin_name?> 스킨을 복사하시겠습니까?') == true) {
		f.target = hid_frame;
		printLoading();
		$('#stpBtn').hide();
		return true;
	}
	return false;
}
$('input[name=skin_name]').select();
</script>