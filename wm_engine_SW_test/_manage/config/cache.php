<?PHP

	use Wing\Design\DesignCache;

	$_cache_mins = array(
		5 => '5분',
		10 => '10분',
		30 => '30분',
		60 => '1시간',
	);

	$cache_storage = $root_dir.'/'.DesignCache::STORAGE;
	$writable = is_writable($cache_storage);

	if(empty($cfg['cache_main_use']) == true) $cfg['cache_main_use'] = 'N';
	if(empty($cfg['cache_prdlist_use']) == true) $cfg['cache_prdlist_use'] = 'N';
	if(empty($cfg['cache_main_interval']) == true) $cfg['cache_main_interval'] = '10';
	if(empty($cfg['cache_prdlist_interval']) == true) $cfg['cache_prdlist_interval'] = '10';
	if(empty($cfg['cache_main_type']) == true) $cfg['cache_main_type'] = 'Y';
	if(empty($cfg['cache_prdlist_type']) == true) $cfg['cache_prdlist_type'] = 'Y';

?>
<?php if ($writable == false) { ?>
<div class="msg_topbar sub quad warning">
	캐시 저장소의 권한이 없습니다. <?=$cache_storage?> 경로의 쓰기 권한을 부여해주시기 바랍니다.
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>

<form method="POST" action="?" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">

	<div class="box_title first">
		<h2 class="title">페이지캐시 설정</h2>
		<div class="btns">
			<span class="box_btn_s icon copy2"><input type="button" value="캐시 갱신" onclick="resetCache()"></span>
		</div>
	</div>
	<table class="tbl_row">
		<caption class="hidden">페이지캐시 설정</caption>
		<colgroup>
			<col style="width:9%">
			<col style="width:6%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th rowspan="3" class="line_r">메인</th>
				<th>사용여부</th>
				<td>
					<label><input type="radio" name="cache_main_use" value="Y" <?=checked($cfg['cache_main_use'], 'Y')?>>사용함</label>
					<label><input type="radio" name="cache_main_use" value="N" <?=checked($cfg['cache_main_use'], 'N')?>>사용안함</label>
				</td>
			</tr>
			<tr>
				<th>갱신주기</th>
				<td>
					<?php foreach($_cache_mins as $key => $val) { ?>
					<label><input type="radio" name="cache_main_interval" value="<?=$key?>" <?=checked($cfg['cache_main_interval'], $key)?>><?=$val?></label>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>대상</th>
				<td>
					<label><input type="radio" name="cache_main_type" value="Y" <?=checked($cfg['cache_main_type'], 'Y')?>>전체 회원</label>
					<label><input type="radio" name="cache_main_type" value="N" <?=checked($cfg['cache_main_type'], 'N')?>>비회원만</label>
				</td>
			</tr>
			<tr>
				<th rowspan="3" class="line_r">상품리스트</th>
				<th>사용여부</th>
				<td>
					<label><input type="radio" name="cache_prdlist_use" value="Y" <?=checked($cfg['cache_prdlist_use'], 'Y')?>>사용함</label>
					<label><input type="radio" name="cache_prdlist_use" value="N" <?=checked($cfg['cache_prdlist_use'], 'N')?>>사용안함</label>
				</td>
			</tr>
			<tr>
				<th>갱신주기</th>
				<td>
					<?php foreach($_cache_mins as $key => $val) { ?>
					<label><input type="radio" name="cache_prdlist_interval" value="<?=$key?>" <?=checked($cfg['cache_prdlist_interval'], $key)?>><?=$val?></label>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>대상</th>
				<td>
					<label><input type="radio" name="cache_prdlist_type" value="Y" <?=checked($cfg['cache_prdlist_type'], 'Y')?>>전체 회원</label>
					<label><input type="radio" name="cache_prdlist_type" value="N" <?=checked($cfg['cache_prdlist_type'], 'N')?>>비회원만</label>
				</td>
			</tr>
		</body>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>회원 로그인 시 등급에 따라 표현되는 정보가 다른 경우, 비회원만 캐시를 이용하도록 설정해주세요.</li>
			<li>캐싱 된 페이지 내의 사용자배너 또는 상품이미지가 변경된 경우 이미지가 미노출될 수 있습니다. 갱신주기를 기다리시거나, 캐시 갱신을 클릭해주세요.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
function resetCache() {
	if(confirm('생성된 모든 캐시를 삭제하고 초기화 하시겠습니까?')) {
		$.post('?body=config@cache.exe', {'exec':'reset'}, function(r) {
			location.reload();
		});
	}
}
</script>