<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  크리테오
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['criteo_use']) $cfg['criteo_use'] = '2';

    // 네이버페이 세일즈 태그 사용
    $scfg->def('criteo_npay', 'N');
    $npay_disabled = ($scfg->get('checkout_id') == null) ? 'disabled' : '';

?>
<form name="criteoFrm" method="post" target="hidden<?=$now?>" onsubmit="return criFrm(this);">
	<input type="hidden" name="body" value="openmarket@criteo.exe">
	<input type="hidden" name="config_code" value="criteo_linkage">
	<div class="box_title first">크리테오 연동</div>
	<table class="tbl_row">
		<caption class="hidden">criteo</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<input type="radio" name="criteo_use" id="criteo_use_1" value="1" <?=checked($cfg['criteo_use'],1)?>> <label for="criteo_use_1" class="p_cursor">사용함</label>
				<input type="radio" name="criteo_use" id="criteo_use_2" value="2" <?=checked($cfg['criteo_use'],2)?>> <label for="criteo_use_2" class="p_cursor">사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Account</th>
			<td>
				<input type="text" name="criteo_P" value="<?=$cfg["criteo_P"]?>" class="input" maxlength="20" style="ime-mode:disabled;">
			</td>
		</tr>
		<tr>
			<th scope="row">네이버페이 세일즈태그 사용</th>
			<td>
				<label><input type="radio" name="criteo_npay" value="Y" <?=checked($cfg['criteo_npay'], 'Y')?> <?=$npay_disabled?>> 사용함</label>
				<label><input type="radio" name="criteo_npay" value="N" <?=checked($cfg['criteo_npay'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<?if($cfg['criteo_use'] == '1') {?>
		<tr>
			<th scope="row">caltalog</th>
			<td>
				<a href="<?=$root_url?>/_data/compare/criteo/criteocatalog.php" target="_blink"><?=$root_url?>/_data/compare/criteo/criteocatalog.php</a>
				<span class="box_btn_s"><input type="button" value="URL복사" onclick="javascript:tagCopy('<?=$root_url?>/_data/compare/criteo/criteocatalog.php')"></span>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function mmFrm(f, type){
		return true;
	}

	function mm_login(target, link, type) {
		var f = document.getElementById('loginFrm');
		f.target = target;
		f.action = link;
		if(type=='daum_show') f.method='post';
		f.submit();
	}

	function criFrm(f){
		if(f.criteo_use.value == '1') {
			if(!checkBlank(f['criteo_WI1'], 'CRITEO에서 전달 받은 WI값(메인/리스트/상품)을 입력해주세요.')) return false;
			if(!checkBlank(f['criteo_WI2'], 'CRITEO에서 전달 받은 WI값(장바구니/주문완료)을 입력해주세요.')) return false;
			if(!checkBlank(f['criteo_P'], 'CRITEO에서 전달 받은 P값을 입력해주세요.')) return false;
		}
		return true;
	}
</script>