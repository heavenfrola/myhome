<?PHP

	define('__PRODUCT_INC_CUSTOM__', true);
	$_GET['smartsotre'] = 'Y';

	require 'product_inc.exe.php';
	$pg_res = str_replace('psearch.', 'nPreset.', $pg_res);

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">스마트스토어 설정 복사</div>
	</div>
	<div id="popupContentArea">
		<form id="search" onsubmit="return nPreset.fsubmit(this);">
			<input type="hidden" name="body" value="<?=$_GET['body']?>">
			<input type="hidden" name="exparam" value="<?=$_GET['exparam']?>">
			<table class="tbl_row">
				<caption class="hidden">스마트스토어 설정 복사</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">매장분류</th>
					<td>
						<select name="pbig" style="width:24%;" onchange="nPreset.fsubmit(this.form);">
							<option value="">::대분류::</option>
							<?=$pcate_sel1?>
						</select>
						<select name="pmid" style="width:24%;" onchange="nPreset.fsubmit(this.form);">
							<option value="">::중분류::</option>
							<?=$pcate_sel2?>
						</select>
						<select name="psmall" style="width:24%;" onchange="nPreset.fsubmit(this.form);">
							<option value="">::소분류::</option>
							<?=$pcate_sel3?>
						</select>
						<?if($cfg['max_cate_depth'] >= 4) {?>
						<select name="pdepth4" style="width:24%;" onchange="nPreset.fsubmit(this.form);">
							<option value="">::세분류::</option>
							<?=$pcate_sel4?>
						</select>
						<?}?>
					</td>
				</tr>
			</table>
			<div class="box_bottom">
				<select name="search_key">
					<option value="name" <?=checked($search_key, 'name', 1)?>>상품명</option>
					<option value="keyword" <?=checked($search_key, 'keyword', 1)?>>검색키워드</option>
					<option value="code" <?=checked($search_key, 'code', 1)?>>상품코드</option>
					<option value="origin_name" <?=checked($search_key, 'origin_name', 1)?>>장기명</option>
					<option value="seller" <?=checked($search_key, 'seller', 1)?>>사입처명</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=$search_str?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</div>
		</form>
		<table class="tbl_col">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col>
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
				<col style="width:15%">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">상품</th>
					<th scope="col">가격</th>
					<th scope="col">적립금</th>
					<th scope="col">상태</th>
					<th scope="col">선택</th>
				</tr>
			</thead>
			<tbody>
				<?PHP
                    foreach ($res as $prd) {
						$prd['parent'] = $prd['no'];
						$prd['name'] = inputText(strip_tags($prd['name']));

						if($prd['upfile3']) {
							$file_dir = getFileDir($prd['updir']);
							$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' style='height:50px;'>";
						}

						switch($prd['stat']) {
							case '2' : $prd['stat'] = '정상'; break;
							case '3' : $prd['stat'] = '품절'; break;
							case '4' : $prd['stat'] = '숨김'; break;
						}

				?>
				<tr>
					<td class="left">
						<div class="box_setup">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
							<dl style="height:30px;">
								<dt class="title"><?=$prd['name']?></dt>
							</dl>
						</div>
					</td>
					<td><?=parsePrice($prd['sell_prc'],true)?></td>
					<td><?=parsePrice($prd['milage'], true)?></td>
					<td><?=$prd['stat']?></td>
					<td>
						<span class="box_btn_s blue"><input type="button" value="선택" onclick="nPreset.sel(<?=$prd['parent']?>)"></span>
					</td>
				</tr>
				<?}?>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="nPreset.close()"></span>
	</div>
</div>