<?PHP
	$tmp_string = $_GET;
	$menu = $_GET['menu'];
	$popupMaxHeight = '400px';
	if ($menu == 'log' || $menu == 'keyword_log' || $menu == 'memAnalysis') $popupMaxHeight = '450px';
?>
<div id="popupContent" class="popupContent layerPop" style="width:600px; z-index:1001;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">단축검색 등록</div>
	</div>
	<div id="popupContentArea" style="max-height:<?=$popupMaxHeight?>; overflow: auto;">
		<form method="post" name="qsFrm" action="./" target="hidden<?=$now?>" onSubmit="return checkQuickSearch(this);">
			<input type="hidden" name="body" value="config@quicksearch.exe">
			<input type="hidden" name="menu" value="<?=$menu?>">
			<input type="hidden" name="string" value="<?=htmlspecialchars(serialize($tmp_string))?>">
			<div class="qs_setting">
				<p class="list_info2">단축검색 등록을 통해 자주 사용하는 검색 항목별 옵션을 손쉽게 관리하고, 편리하게 검색할 수 있습니다.</p>
				<table class="tbl_row">
					<caption class="hidden">단축검색등록</caption>
					<colgroup>
						<col style="width:28%;">
						<col>
					</colgroup>
					<tr>
						<th scope="row">단축검색명</th>
						<td><input type="text" name="title" value="" class="input" maxlength="20"> (최대 20자)</td>
					</tr>
					<tr>
						<th scope="row">요약설명</th>
						<td><textarea name="content" class="input" maxlength="30"></textarea> (최대 30자)</td>
					</tr>
					<?php if ($menu == 'log' || $menu == 'keyword_log' || $menu == 'memAnalysis') { ?>
					<tr>
						<th scope="row">설정</th>
						<td>
							<div>
								<input type="radio" name="setterm" value="term" id="term" checked>
								<label for="term">현재 검색일의 기간을 저장 (오늘 기준으로 해당 기간만큼 검색)</label>
							</div>
							<div>
								<input type="radio" name="setterm" value="date" id="date">
								<label for="date">현재 검색일을 그대로 저장</label>
							</div>
						</td>
					</tr>

					<input type="checkbox" name="limitconfig" value="Y" style="display:none;" checked>
					<?php } else { ?>
					<tr>
						<th scope="row">기간설정 포함</th>
						<td>
							<label><input type="checkbox" name="limitconfig" value="Y"> 저장 <span>(단축검색 등록 시 선택한 기간설정을 포함하여 저장합니다.)</span></label>
						</td>
					</tr>
					<?php } ?>
				</table>
				<div class="btn">
					<span class="box_btn blue qs_button"><input type="submit" value="확인"></span>
					<span class="box_btn"><a onclick="window.quicksearch.close(); removeDimmed();">취소</a></span>
				</div>
			</div>
		</form>
	</div>
</div>