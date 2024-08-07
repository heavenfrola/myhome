<?PHP

	$skin_name = str_replace('/', '', $_GET['skinname']);
	if(empty($skin_name) == true || is_dir($root_dir.'/_skin/'.$skin_name) == false) {
		msg('삭제할 스킨을 선택해주세요.', 'close');
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">스킨 삭제</div>
	</div>
	<div id="popupContentArea">
		<form method="post" action="./index.php" onsubmit="return skinCopy(this);">
			<input type="hidden" name="body" value="design@design_config.exe">
			<input type="hidden" name="exec" value="skin_delete">
			<input type="hidden" name="selected_skin" value="<?=$skin_name?>">
			<input type="hidden" name="type" value="<?=$_GET['type']?>">
			<div class="box_middle">
				<p>선택하신 <span class="p_color"><?=$skin_name?></span> 스킨을 삭제하시겠습니까?</p>
                <?php if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) { ?>
                <p>삭제한 스킨은 복구가 불가능하며, 쇼핑몰을 소유한 계정의 비밀번호 입력 후 삭제할 수 있습니다.</p>
                <?php } else { ?>
				<p>삭제한 스킨은 복구가 불가능하며, 최고 관리자 비밀번호 입력 후 삭제됩니다.</p>
                <?php } ?>
			</div>
			<table class="tbl_row">
				<caption class="hidden">스킨 복사</caption>
				<colgroup>
					<col style="width:30%">
					<col>
				</colgroup>
				<tr>
					<th scope="row">최고 관리자 비밀번호</th>
					<td><input type="password" name="pwd" class="input" size="20"></td>
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
	if(confirm('<?=$skin_name?> 스킨을 삭제하시겠습니까?') == true) {
		f.target = hid_frame;
		printLoading();
		$('#stpBtn').hide();
		return true;
	}
	return false;
}
//$('input[name=pwd]').focus();
</script>