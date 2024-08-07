<?PHP

	if($cfg['instagram_interval'] < 3600) $cfg['instagram_interval'] = 3600;
    $scfg->def('instagram_get_mov', 'N');

?>
<?php if($cfg['instagram_access_token']) { ?>
<form method="post" class='register' action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<table class="tbl_row">
		<caption>데이터 수집 설정</caption>
		<colgroup>
			<col width="180px">
			<col>
		</colgroup>
		<tr>
			<th scope="row" rowspan="2">갱신 주기</th>
			<td>
				<input type="radio" name="instagram_interval" value="3600"   <?=checked($cfg['instagram_interval'], 3600)?>> 1시간
				<input type="radio" name="instagram_interval" value="21600"  <?=checked($cfg['instagram_interval'], 21600)?>> 6시간
				<input type="radio" name="instagram_interval" value="86400" <?=checked($cfg['instagram_interval'], 86400)?>> 1일
			</td>
		</tr>
		<tr>
			<td>
				<span class="box_btn_s"><input type="button" value="즉시 새로고침" onclick="mediaRefresh();"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">동영상 수집</th>
			<td>
                <label><input type="radio" name="instagram_get_mov" value="Y" <?=checked($scfg->get('instagram_get_mov'), 'Y')?>> 사용함</label>
                <label><input type="radio" name="instagram_get_mov" value="N" <?=checked($scfg->get('instagram_get_mov'), 'N')?>> 사용안함</label>
                <ul class="list_info">
                    <li>동영상 수집 시 스킨 수정이 필요할 수 있습니다.</li>
                    <li>한 페이지에 많은 동영상이 로딩될 경우 사이트 로딩이 늦어지거나 고객PC가 느려질수 있습니다.</li>
                </ul>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>인스타그램 업데이트 빈도에 따라 갱신주기를 조정하여 사이트 체감 속도에 지장이 없도록 해 주시기 바랍니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br>
<?php } ?>

<form method="post" class="register" action="./index.php" target="hidden<?=$now?>" onsubmit="openLinkWindow(this);">
	<input type="hidden" name="body" value="design@instagram.exe">
	<input type="hidden" name="exec" value="<?=($cfg['instagram_access_token'] ? 'unlink' : 'valid')?>">
	<table class="tbl_row">
		<caption>인스타그램 API 연결</caption>
		<colgroup>
			<col width="180px">
			<col>
		</colgroup>
		<tr>
			<th scope="row">계정 연결</th>
			<td>
				<?php if($cfg['instagram_access_token']) { ?>
				<span class="box_btn_s gray"><input type="submit" value="연결 해제"></span>
				<?php } else { ?>
				<span class="box_btn_s"><input type="submit" value="인스타그램 연결"></span>
				<?php } ?>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
function openLinkWindow(f) {
	if(f.exec.value == 'valid') {
		f.target = 'instagram';
		window.open('', 'instagram', 'status=no, width=450px, height=600px, top=100px, left=100px');
	}
	return true;
}

function mediaRefresh() {
	$.post('./?body=design@instagram.exe', {'exec':'refresh'}, function(r) {
		window.alert(r.count+'개의 데이터가 수집되었습니다.');
		location.reload();
	});
}
</script>