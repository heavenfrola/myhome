<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 타이틀/DTD/viewport
	' +----------------------------------------------------------------------------------------------+*/
	if(!$cfg['viewportAll']) {
		$viewport = explode('maximum-scale=', $cfg['viewport']);
		$all_viewport = explode(',', $viewport[1]);
		$all_viewport2 = explode('user-scalable=', $all_viewport[2]);
		$cfg['viewportAll'] = $all_viewport[0];
		if($all_viewport2[1] == 'no') $cfg['viewportAll'] = '1.0';
	}
	if(!$cfg['viewportDetail']) $cfg['viewportDetail'] = '2.0';
	if(!$cfg['viewportBoard']) $cfg['viewportBoard'] = '2.0';
?>
<form id="cfgfrm" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="dtd">
	<div class="box_title first">
		<h2 class="title">DTD/확대 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">DTD/확대 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">DTD<br>(모바일전용)</th>
			<td>
				<input type="text" name="mfrontDTD" value="<?=inputText(stripslashes(stripslashes($cfg['mfrontDTD'])))?>" class="input" size="100">
				<p>
					<label class="p_cursor"><input type="checkbox" name="mDTDuse" value="N" onclick="disabledDTD(this)"> DTD사용하지 않음 (DTD를 브라우저가 자동으로 선택하도록 합니다.)</label>
				</p>
				<ul class="list_info tp">
					<li>내용을 입력하지 않을 경우 <span class="p_color2">&lt;!DOCTYPE html&gt;</span>로 적용됩니다.</li>
					<li>DTD 변경 시 디자인이 틀려지거나 자바스크립트에 오류가 발생할 수 있습니다. 변경 후 각 페이지를 테스트해주시기 바랍니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="3">모바일 확대 설정<br>(VIEWPORT)</th>
			<td>
				<ul class="list_common4">
					<li>
						전체페이지 : <select name="viewportAll">
							<option value="1.0">미설정</option>
							<?for($i = 1.5; $i <= 5; $i+=0.5){?>
							<option value="<?=$i?>" <?=checked($i, $cfg['viewportAll'], true)?>><?=sprintf('%1.1f', $i)?> 배</option>
							<?}?>
						</select>
					</li>
					<li>
						상세페이지 : <select name="viewportDetail">
							<option value="1.0">미설정</option>
							<?for($i = 1.5; $i <= 5; $i+=0.5){?>
							<option value="<?=$i?>" <?=checked($i, $cfg['viewportDetail'], true)?>><?=sprintf('%1.1f', $i)?> 배</option>
							<?}?>
						</select>
					</li>
					<li>
						일반게시판 : <select name="viewportBoard">
							<option value="1.0">미설정</option>
							<?for($i = 1.5; $i <= 5; $i+=0.5){?>
							<option value="<?=$i?>" <?=checked($i, $cfg['viewportBoard'], true)?>><?=sprintf('%1.1f', $i)?> 배</option>
							<?}?>
						</select>
					</li>
				</ul>
				<ul class="list_info tp">
					<li>상세페이지 및 게시판 확대가 미설정일 경우 전체페이지 확대 설정이 적용됩니다.</li>
					<li>상세페이지 및 게시판 확대를 사용할 경우 전체페이지 확대 설정보다 우선 적용됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function disabledDTD(ckbtn) {
		var f = document.getElementById('cfgfrm');
		if(!ckbtn || ckbtn.checked == true) {
			f.mfrontDTD.value = ' ';
			f.mfrontDTD.style.backgroundColor = '#f2f2f2';
			f.mfrontDTD.readOnly = true;
			f.DTDuse.checked = true;
		} else {
			f.mfrontDTD.value = '';
			f.mfrontDTD.style.backgroundColor = '';
			f.mfrontDTD.readOnly = false;
		}
	}

	<?if($cfg['mfrontDTD'] == ' '){?>
	disabledDTD();
	<?}?>
</script>