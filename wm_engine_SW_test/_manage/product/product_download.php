<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  일괄 업로드용 상품 엑셀 다운로드
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_manage/product/product_search.inc.php";

	foreach($_GET as $key=>$val) {
		if($key!="page") $qs.="&".$key."=".$val;
	}

	$xls_query = preg_replace('/&body=[^&]+/','',$qs);
	$xls_query = str_replace("&body=$pgCode","",$xls_query);
	$xls_query = preg_replace("/&?exec=[^&]+/","",$xls_query);

	// 카테고리 검색
	for ($i = 0; $i <= 2; $i++ ) {
		switch ($i) {
			case "0" : $cm = ""; $ct = "1"; break;
			case "1" : $cm = "x"; $ct = "4"; break;
			case "2" : $cm = "y"; $ct = "5"; break;
		}
		if ( $i == 0 || $_use[$cm."big"] == "Y" ) {
			if ( $_GET[$cm."big"] ) $cw .= " or (`level`= '2' and `big` = '".$_GET[$cm."big"]."')";
			if ( $_GET[$cm."mid"] ) $cw .= " or (`level`= '3' and `mid` = '".$_GET[$cm."mid"]."')";

			$sql = $pdo->iterator("select `no`,`name`,`ctype`,`level` from `$tbl[category]` where `ctype`= '$ct' and (`level` = '1' $cw )order by `level`,`sort`");
            foreach ($sql as $cate) {
				switch ($cate[level]) {
					case "1" : $cl = $cm."big"; break;
					case "2" : $cl = $cm."mid"; break;
					case "3" : $cl = $cm."small"; break;
				}
				$sel = ( $_GET[$cl] == $cate[no] ) ? "selected" : "";
				${"item_".$cate['ctype']."_".$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
			}
		}
	}


	// 기획전 검색
	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype`='2' order by `level`,`sort`");
    foreach ($sql as $data) {
		$sel = $data['no'] == $_GET['cno'] ? 'selected' : '';
		$item_2 .= "\n\t<option value='$data[no]' $sel>".stripslashes($data['name'])."</option>";
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[product] p $prd_join where stat > 1 $w");

	foreach($_GET as $key => $val) {
		if($key == 'body' || !$val) continue;
		$xls_query .= "&$key=".urlencode($val);
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<div class="box_title first">
	<h2 class="title">엑셀양식 다운로드</h2>
</div>
<!-- 검색폼 -->
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<input type="hidden" name="cpn_mode" value="<?=$cpn_mode?>">
	<input type="hidden" name="detail_search" value="<?=$detail_search?>">
	<input type="hidden" name="icons" value="<?=$icons?>">
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn('prdSearchFrm', $_COOKIE['prd_detail_search_on'])?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'prd_detail_search_on');" <?=checked($_COOKIE['prd_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<?if($cpn_mode==2){?>
			<tr>
				<th scope="row">쿠폰</th>
				<td scope="row" colspan="3">
				<select name="cpn">
					<option value="">::쿠폰 선택::</option>
					<?php
					$sql = "select * from `$tbl[coupon]` where `stype`='2' order by `no` desc";
					$res = $pdo->iterator($sql);
					$idx = $res->rowCount();
                    foreach ($res as $cpns) {
					?>
					<option value="<?=$cpns['no']?>" <?=checked($cpns['no'], $cpn, 1)?>><?=inputText($cpns['name'])?></option>
					<?}?>
				</select>
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row"class="divider">매장분류</th>
				<td scope="row"class="divider" colspan="3">
					<select name="big" onchange="chgCate(this,'mid','small')" style="width: 150px">
						<option value="">::대분류::</option>
						<?=$item_1_1?>
					</select>
					<select name="mid" onchange="chgCate(this,'small')" style="width: 150px">
						<option value="">::중분류::</option>
						<?=$item_1_2?>
					</select>
					<select name="small" style="width: 150px">
						<option value="">::소분류::</option>
						<?=$item_1_3?>
					</select>
					<label class="p_cursor"><input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외</label>
				</td>
			</tr>
			<?if($cfg['xbig_mng'] == "Y"){?>
			<tr>
				<th scope="row"><?=$cfg['xbig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="xbig" onchange="chgCate(this,'xmid','xsmall')" style="width: 150px">
						<option value="">::대분류::</option>
						<?=$item_4_1?>
					</select>
					<select name="xmid" onchange="chgCate(this,'xsmall')" style="width: 150px">
						<option value="">::중분류::</option>
						<?=$item_4_2?>
					</select>
					<select name="xsmall" style="width: 150px">
						<option value="">::소분류::</option>
						<?=$item_4_3?>
					</select>
				</td>
			</tr>
			<?}?>
			<?if($cfg['ybig_mng'] == "Y"){?>
			<tr>
				<th scope="row"><?=$cfg['ybig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="ybig" onchange="chgCate(this,'ymid','ysmall')" style="width: 150px">
						<option value="">::대분류::</option>
						<?=$item_5_1?>
					</select>
					<select name="ymid" onchange="chgCate(this,'ysmall')" style="width: 150px">
						<option value="">::중분류::</option>
						<?=$item_5_2?>
					</select>
					<select name="ysmall" style="width: 150px">
						<option value="">::소분류::</option>
						<?=$item_5_3?>
					</select>
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">등록기간별</th>
				<td>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~
					<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				</td>
				<th scope="row"><?=$_ctitle[2]?></th>
				<td>
					<select name="cno" style="width: 150px">
						<option value="">:: 기획전 :;</option>
						<?=$item_2?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?=$cfg['product_sell_price_name']?>별</th>
				<td>
					<input type="text" name="start_prc" value="<?=$start_prc?>" class="input" size="10"> ~
					<input type="text" name="finish_prc" value="<?=$finish_prc?>" class="input" size="10">
				</td>
				<th scope="row">판매설정별</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="event_sale" value="Y" <?=checked($event_sale,"Y")?>> 이벤트</label>
					<label class="p_cursor"><input type="checkbox" name="member_sale" value="Y" <?=checked($member_sale,"Y")?>> 회원할인</label>
					<label class="p_cursor"><input type="checkbox" name="free_delivery" value="Y" <?=checked($free_delivery,"Y")?>> 무료배송 적용 상품</label>
					<label class="p_cursor"><input type="checkbox" name="dlv_alone" value="Y" <?=checked($dlv_alone,"Y")?>> 무이자</label>
					<label class="p_cursor"><input type="checkbox" name="checkout" value="Y" <?=checked($checkout,"Y")?>> 네이버 페이</label>
				</td>
			</tr>
			<tr>
				<th scope="row">적립금별</th>
				<td>
					<input type="text" name="start_milage" value="<?=$start_milage?>" class="input" size="10"> ~
					<input type="text" name="finish_milage" value="<?=$finish_milage?>" class="input" size="10">
				</td>
				<th scope="row">상태별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="2" <?=checked($prd_stat, 2)?> > 정상</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="3" <?=checked($prd_stat, 3)?>> 품절</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="4" <?=checked($prd_stat, 4)?> > 숨김</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
		</div>
	</div>
</form>
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 상품이 검색되었습니다.
	<div class="btns">
		<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="downloadPrdExcel()"></span>
		<span class="box_btn_s icon excel"><input type="button" value="추가항목엑셀다운" onclick="downloadPrdExcel('field')"></span>
	</div>
</div>

<?if($NumTotalRec) {?>
<table class="tbl_row">
	<tr>
		<td class="center p_color3">검색된 상품을 엑셀다운로드 해주세요.</td>
	</tr>
</table>
<?}?>
<!-- //검색 총합 -->

<script type="text/javascript">
function downloadPrdExcel(type) {
	var f = document.getElementById('search');
	f.body.value = (!type) ? 'product@product_download.exe' : 'product@product_download_field.exe' ;
	f.target = hid_frame;
	f.submit();

	f.body.value = 'product@product_download';
	f.target = '';
}
</script>