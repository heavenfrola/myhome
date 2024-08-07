<?PHP

	if($cfg['use_ga'] != 'Y' && $cfg['use_ga'] != '4') $cfg['use_ga'] = 'N';
	if($cfg['use_ga_enhanced_ec'] != 'Y') $cfg['use_ga_enhanced_ec'] = 'N';

?>
<form name="googleFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="ga">
	<div class="box_title first">
		<h2 class="title">
			구글 애널리틱스 연동&nbsp;
			<a href="https://www.google.com/analytics/" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
		</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">구글 애널리틱스 연동</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용 여부</th>
			<td>
				<label><input type="radio" name="use_ga" value="Y" <?=checked($cfg['use_ga'], 'Y')?>> 유니버설(UA)</label>
				<label><input type="radio" name="use_ga" value="4" <?=checked($cfg['use_ga'], '4')?>> 구글 애널리틱스 4</label>
				<label><input type="radio" name="use_ga" value="N" <?=checked($cfg['use_ga'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">측정 ID</th>
			<td>
				<input type="text" name="ga_code" class="input" size="30" value="<?=$cfg['ga_code']?>">
				<ul class="list_msg">
					<li>구글 애널리틱스 메뉴 하단의 관리 > 속성 > 데이터 스트림 메뉴에서 측정ID를 확인할 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">부가기능</th>
				<td>
					<label><input type="checkbox" name="use_ga_UserID" value="Y" <?=checked($cfg['use_ga_UserID'], 'Y')?>> User-ID 연동</label>
					<ul class="list_msg">
						<li>구글 애널리틱스 메뉴 하단의 관리 > 속성 > 추적 정보 > User-ID 메뉴에서 User-ID 설정 기능을 활성화 해주세요.</li>
					</ul>
					<label class="ec"><input type="checkbox" name="use_ga_ecommerce" value="Y" <?=checked($cfg['use_ga_ecommerce'], 'Y')?> onclick='checkGoogle(this.form)'> 전자상거래 추적</label>
					<div class="ec">
						<p style="margin-left:16px;margin-top:10px">
							└ <label class="p_cursor"><input type="checkbox" name="use_ga_enhanced_ec" value="Y" <?=checked($cfg['use_ga_enhanced_ec'],'Y')?>>
							향상된 전자상거래 사용</label>
						</p>
					</div>
					<ul class="list_msg">
						<li>구글 애널리틱스 메뉴 하단의 관리 > 보기 > 전자상거래 설정 > 전자상거래 사용 기능을 활성화 해주세요.</li>
					</ul>
				</td>
			</th>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>이미 스킨에 수동으로 입력하신 구글 애널리틱스 코드가 있을 경우 2중으로 수집되거나 충돌이 일어날수 있습니다.</li>
			<li>수동으로 입력하신 코드가 있을 경우에는 삭제 후 이용해주세요.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script>
checkGoogle(document.googleFrm);
function checkGoogle(f) {
	if(f.use_ga_ecommerce.checked==false) {
		$('[name=use_ga_enhanced_ec]').prop('disabled', true);
	}else {
		$('[name=use_ga_enhanced_ec]').prop('disabled', false);
	}
}

(ga_ver = function() {
    if ($(':checked[name=use_ga]').val() == 'Y') {
        $('.ec').show();
    } else {
        $('.ec').hide();
    }
})();
$(':radio[name=use_ga]').on('click', ga_ver);
</script>