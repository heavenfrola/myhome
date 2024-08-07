<script type="text/javascript">
var iconSearch = new layerWindow('product@product_icon_search_frm.exe');
var iconConfig = new layerWindow('product@product_icon_config_frm.exe');
</script>
<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품수정/관리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_manage/product/product_search.inc.php";

	$list_tab_qry = '';
	foreach($_GET as $key=>$val) {
		if(!$val && $key != "partner_no") continue;
		if($key!="page") $qs.="&".$key."=".urlencode($val);
		if($key != 'prd_stat' && $key != 'page') {
			$list_tab_qry .= ($list_tab_qry) ? '&' : '?';
			$list_tab_qry .= $key."=".urlencode($val);
		}
	}

	$xls_query = preg_replace('/&body=[^&]+/','',$qs);
	$xls_query = str_replace("&body=$pgCode","",$xls_query);
	$xls_query = preg_replace("/&?exec=[^&]+/","",$xls_query);

	// 카테고리 검색
	foreach(array(1, 4, 5, 9) as $ct) {
		if($ct == 1 || $ct == 9 || $_use[$_cate_colname[$ct][1]] == 'Y') {
			$cw = '';
			for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
				$val = numberOnly($_GET[$_cate_colname[$ct][$i]]);
				if($val) $cw .= " or (`level`='".($i+1)."' and {$_cate_colname[1][$i]}='$val')";
			}
			$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='$ct' and (level='1' $cw) order by level, sort");
            foreach ($sql as $cate) {
				$cl = $_cate_colname[$ct][$cate['level']];
				$sel = ($_GET[$cl] == $cate['no']) ? 'selected' : '';
				${'item_'.$cate['ctype'].'_'.$cate['level']} .= "\n<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
			}
		}
	}

	// 기획전 검색
	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype` in (2, 6) order by `level`,`sort`");
    foreach ($sql as $data) {
		$sel = ($data['no'] == $_GET['cno'] || $data['no'] == $_GET['mno']) ? 'selected' : '';
		${'item_'.$data['ctype']} .= "\n\t<option value='$data[no]' $sel>".stripslashes($data['name'])."</option>";
	}

	// 프로모션 검색
	$pgrp = '';
	if(isTable($tbl['promotion_pgrp_list'])) {
		$sql = $pdo->iterator("select * from `$tbl[promotion_pgrp_list]` order by `no`");
        foreach ($sql as $data) {
			$sel = ($data['no'] == $_GET['prno']) ? 'selected' : '';
			$pgrp .= "\n\t<option value='$data[no]' $sel>".stripslashes($data['pgrp_nm'])."</option>";
		}
	}

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$sort = numberOnly($_GET['sort']);
	if($sort === '') $sort = 2;
	for ($i = 1; $i <= 12; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	// 그룹별 가격일괄수정
	$memprcres=$pdo->iterator("select * from `$tbl[member_group]` where `use_group` = 'Y' order by `no` asc");
    foreach ($memprcres as $memprc_data) {
		if($cfg['group_price'.$memprc_data['no']] != 'Y') continue;
		$memprc .= "<option value=\"sell_prc{$memprc_data['no']}\">{$memprc_data['name']} 가격</option>\n";
	}
	$row = numberOnly($_GET['row']);
	if(!$row) $row = 20;

	include_once $engine_dir."/_manage/product/product_excel_config.php";
	foreach($_prd_excel_set as $key=>$val){
		$xls_sets .= "<option value='$key'>- $_prd_excel_set_name[$key]</option>\n";
	}

	$sch_prd_stat = numberOnly($_GET['prd_stat']);
	if(!$sch_prd_stat) $sch_prd_stat = '1';
	${'list_tab_active'.$sch_prd_stat} = 'class="active"';

    $QUERY_STRING = array();
    foreach ($_GET as $key => $val) {
        if ($val) {
            $QUERY_STRING[$key] = $val;
        }
    }
    $QUERY_STRING = http_build_query($QUERY_STRING);

    // 기본 정보 제공 고시
	$_annoucements = array();
	$cres = $pdo->iterator("select no, name from $tbl[category] where ctype='3' order by sort asc");
    foreach ($cres as $fdata) {
		$_annoucements[$fdata['no']] = stripslashes($fdata['name']);
	}

    // 카카오페이구매 정보 제공 고시
    if ($scfg->comp('use_talkpay', 'Y') == true) {
        $_kakao_annoucements = array();
        $tres = $pdo->iterator("select idx, title from {$tbl['product_talkstore_announce']} order by idx asc");
        foreach ($tres as $tdata) {
            $_kakao_annoucements[$tdata['idx']] = stripslashes($tdata['title']);
        }
    }

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?php if ($admin['level'] > 3) { ?>
<style type="text/css">
.sclink {
    display: none !important;
}
</style>
<?php } ?>
<!-- 검색 폼 -->
<form name="prdSearchFrm" id="prdSearchFrm">
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
					<div id="searchCtl" onclick="toggle_shadow()"><?php searchBoxBtn('prdSearchFrm', $_COOKIE['prd_detail_search_on']) ?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'prd_detail_search_on');" <?=checked($_COOKIE['prd_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
            <?php if($admin['level'] < 4) { ?>
			<ul class="quick_search">
				<?php
				$preset_menu = 'product';
				include_once $engine_dir."/_manage/config/quicksearch.inc.php";
				?>
			</ul>
            <?php } ?>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">매장분류</th>
				<td>
					<select name="big" onchange="chgCateInfinite(this, 2, '')">
						<option value="">::대분류::</option>
						<?=$item_1_1?>
					</select>
					<select name="mid" onchange="chgCateInfinite(this, 3, '')">
						<option value="">::중분류::</option>
						<?=$item_1_2?>
					</select>
					<select name="small" onchange="chgCateInfinite(this, 4, '')">
						<option value="">::소분류::</option>
						<?=$item_1_3?>
					</select>
					<?php if ($cfg['max_cate_depth'] >= 4) { ?>
					<select name="depth4">
						<option value="">::세분류::</option>
						<?=$item_1_4?>
					</select>
					<?php } ?>
					<label class="p_cursor" style="display:none;"><input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외</label>
				</td>
				<th scope="row"><?=$_ctitle[2]?></th>
				<td>
					<select name="cno">
						<option value="">:: 기획전 ::</option>
						<?=$item_2?>
					</select>
					<select name="mno">
						<option value="">:: 모바일기획전 ::</option>
						<?=$item_6?>
					</select>
					<select name="prno">
						<option value="">:: 프로모션 ::</option>
						<?=$pgrp?>
					</select>
				</td>
			</tr>
			<?php if ($cfg['xbig_mng'] == "Y") { ?>
			<tr>
				<th scope="row"><?=$cfg['xbig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="xbig" onchange="chgCateInfinite(this, 2, 'x')">
						<option value="">::대분류::</option>
						<?=$item_4_1?>
					</select>
					<select name="xmid" onchange="chgCateInfinite(this, 3, 'x')">
						<option value="">::중분류::</option>
						<?=$item_4_2?>
					</select>
					<select name="xsmall" onchange="chgCateInfinite(this, 4, 'x')">
						<option value="">::소분류::</option>
						<?=$item_4_3?>
					</select>
					<?php if ($cfg['max_cate_depth'] >= 4) { ?>
					<select name="xdepth4">
						<option value="">::세분류::</option>
						<?=$item_4_4?>
					</select>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<?php if ($cfg['ybig_mng'] == "Y") { ?>
			<tr>
				<th scope="row"><?=$cfg['ybig_name']?> 매장분류</th>
				<td colspan="3">
					<select name="ybig" onchange="chgCateInfinite(this, 2, 'y')">
						<option value="">::대분류::</option>
						<?=$item_5_1?>
					</select>
					<select name="ymid" onchange="chgCateInfinite(this, 3, 'y')">
						<option value="">::중분류::</option>
						<?=$item_5_2?>
					</select>
					<select name="ysmall" onchange="chgCateInfinite(this, 4, 'y')">
						<option value="">::소분류::</option>
						<?=$item_5_3?>
					</select>
					<?php if ($cfg['max_cate_depth'] >= 4) { ?>
					<select name="ydepth4">
						<option value="">::세분류::</option>
						<?=$item_5_4?>
					</select>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">상태</th>
				<td>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="2" <?=checked($prd_stat, 2)?>> 정상</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="3" <?=checked($prd_stat, 3)?>> 품절</label>
					<label class="p_cursor"><input type="radio" name="prd_stat" value="4" <?=checked($prd_stat, 4)?>> 숨김</label>
				</td>
				<th scope="row"><?=$cfg['product_sell_price_name']?></th>
				<td>
					<input type="text" name="start_prc" value="<?=$start_prc?>" class="input" size="10"> ~
					<input type="text" name="finish_prc" value="<?=$finish_prc?>" class="input" size="10">
				</td>
			</tr>
			<tr>
				<th scope="row">재고관리</th>
				<td>
					<label class="p_cursor"><input type="radio" name="ea_type" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="ea_type" value="1" <?=checked($ea_type, 1)?> > 사용함</label>
					<label class="p_cursor"><input type="radio" name="ea_type" value="2" <?=checked($ea_type, 2)?> > 사용안함</label>
				</td>
				<th scope="row">적립금</th>
				<td>
					<input type="text" name="start_milage" value="<?=$start_milage?>" class="input" size="10"> ~ <input type="text" name="finish_milage" value="<?=$finish_milage?>" class="input" size="10">
				</td>
			</tr>
			<tr>
				<th scope="row">상품군</th>
				<td>
					<label><input type="radio" name="prd_type" value="" <?=checked($prd_type, '')?>> 전체</label>
					<label><input type="radio" name="prd_type" value="1" <?=checked($prd_type, '1')?>> 일반상품</label>
					<label><input type="radio" name="prd_type" value="4" <?=checked($prd_type, '4')?>> 세트상품</label>
					<label><input type="radio" name="prd_type" value="6" <?=checked($prd_type, '6')?>> 골라담기</label>
                    <!--
					<label><input type="radio" name="prd_type" value="5" <?=checked($prd_type, '5')?>> 담을수록 할인</label>
                    -->
				</td>
				<th scope="row">판매채널</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="checkout" value="Y" <?=checked($checkout,"Y")?>> 네이버페이</label>
					<?php if ($cfg['use_talkpay'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="talkpay" value="Y" <?=checked($talkpay,"Y")?>> 톡체크아웃</label>
					<?php } ?>
					<?php if ($cfg['use_kakaoTalkStore'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="talkstore" value="Y" <?=checked($talkstore,"Y")?>> 카카오톡 스토어</label>
					<?php } ?>
					<?php if ($cfg['n_smart_store'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="smartstore" value="Y" <?=checked($smartstore,"Y")?>> 네이버 스마트스토어</label>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">판매설정</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="event_sale" value="Y" <?=checked($event_sale,"Y")?>> 이벤트</label>
					<label class="p_cursor"><input type="checkbox" name="member_sale" value="Y" <?=checked($member_sale,"Y")?>> 회원혜택</label>
					<label class="p_cursor"><input type="checkbox" name="free_delivery" value="Y" <?=checked($free_delivery,"Y")?>> 무료배송</label>
					<?php if(($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') && fieldExist($tbl['product'], 'oversea_free_delivery')) { ?>
					<label class="p_cursor"><input type="checkbox" name="oversea_free_delivery" value="Y" <?=checked($oversea_free_delivery,"Y")?>> 해외 무료배송(무게 차감)</label>
					<br/>
					<?php } ?>
					<label class="p_cursor"><input type="checkbox" name="dlv_alone" value="Y" <?=checked($dlv_alone,"Y")?>> 단독배송</label>
					<?php if ($cfg['import_flag_use'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="import_flag" value="Y" <?=checked($import_flag,"Y")?>> 해외구매대행</label>
					<?php } ?>
					<?php if ($cfg['compare_today_start_use'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="compare_today_start" value="Y" <?=checked($compare_today_start,"Y")?>> 오늘출발</label>
					<?php } ?>
					<?php if($cfg['ts_use'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="timesale" value="Y" <?=checked($timesale,"Y")?>> 타임세일</label>
					<?php } ?>
					<?php if ($cfg['use_sbscr'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="sbscr_product" value="Y" <?=checked($sbscr_product,"Y")?>> 정기배송</label>
					<?php } ?>
                    <?php
                    if(
                        $scfg->comp('cash_receipt_use', 'Y') == true
                        || $cfg['card_pg'] == 'dacom'
                        || ($cfg['card_pg'] == 'kcp' && $cfg['kcp_use_taxfree'] == 'Y')
                        || ($cfg['card_pg'] == 'nicepay' && $cfg['nice_use_taxfree'] == 'Y')
                        || $cfg['card_pg'] == 'inicis'
                    ) {
                    ?>
					<label class="p_cursor"><input type="checkbox" name="tax_free" value="Y" <?=checked($tax_free, 'Y')?>> 면세상품</label>
                    <a href="#" class="tooltip_trigger" data-child="tooltip_taxfree"></a>
                    <div class="info_tooltip tooltip_taxfree" >PG사 지원 또는 면세계약에 따라 면세처리가 되지 않을 수 있습니다.</div>
                    <?php } ?>
					<?php if($cfg['use_no_mile/cpn'] == 'Y') { ?>
					<label class="p_cursor" style="white-space:nowrap;"><input type="checkbox" name="no_milage" value="Y" <?=checked($no_milage,"Y")?>> 적립금사용불가</label>
					<label class="p_cursor" style="white-space:nowrap;"><input type="checkbox" name="no_cpn" value="Y" <?=checked($no_cpn,"Y")?>> 쿠폰사용불가</label>
					<?php } ?>
                    <?php if ($scfg->comp('use_navershopping_book', 'Y') == true) { ?>
					<label class="p_cursor" style="white-space:nowrap;"><input type="checkbox" name="is_book" value="Y" <?=checked($is_book,"Y")?>> 도서상품</label>
                    <?php } ?>
                    <?php if ($scfg->comp('compare_explain', 'Y') == true) { ?>
					<label class="p_cursor" style="white-space:nowrap;"><input type="checkbox" name="no_ep" value="N" <?=checked($no_ep, 'N')?>> 쇼핑 검색엔진 포함</label>
                    <?php } ?>
				</td>
				<th scope="row">등록일</th>
				<td>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				</td>
			</tr>
			<?php if (isset($cfg['use_prd_dlvprc']) == true && $cfg['use_prd_dlvprc'] == 'Y') { ?>
			<tr>
				<th scope="row">개별 배송비 설정</th>
				<td>
					<?=selectArray($_delivery_sets, 'dset', false, ':: 배송정책 :: ', $_GET['dset'])?>
				</td>
                <?php if ($admin['level'] < 4) { ?>
				<th scope="row">타임세일 세트</th>
				<td>
					<?=selectArray($_timesale_sets, 'ts_set', false, ':: 세트선택 :: ', $_GET['ts_set'])?>
				</td>
                <?php } ?>
			</tr>
			<?php } ?>
			<?php if ($cfg['use_m_content_product'] == 'Y') { ?>
			<tr>
				<th scope="row">모바일 상세설명</th>
				<td colspan="3">
					<label class="p_cursor"><input type="radio" name="is_mcontent" value="" <?=checked($is_mcontent, '')?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="is_mcontent" value="A"  <?=checked($is_mcontent, 'A')?>> 입력</label>
					<label class="p_cursor"><input type="radio" name="is_mcontent" value="B" <?=checked($is_mcontent, 'B')?>> 미입력</label>
				</td>
			</tr>
			<?php } ?>
			<?php if ($item_9_1 && $admin['level'] < 4) { ?>
			<tr>
				<th scope="row">창고위치</th>
				<td colspan="3">
					<select name="sbig" onchange="chgCateInfinite(this, 2, 's');">
						<option value="">::대분류::</option>
						<?=$item_9_1?>
					</select>
					<select name="smid" onchange="chgCateInfinite(this, 3, 's');">
						<option value="">::중분류::</option>
						<?=$item_9_2?>
					</select>
					<select name="ssmall" onchange="chgCateInfinite(this, 4, 's');">
						<option value="">::소분류::</option>
						<?=$item_9_3?>
					</select>
					<select name="sdepth4">
						<option value="">::세분류::</option>
						<?=$item_9_4?>
					</select>
				</td>
			</tr>
			<?php } ?>
			<?php if ($cfg['use_partner_shop'] == 'Y' && $admin['level'] < 4) { ?>
			<tr>
				<th scope="row">입점파트너</th>
				<td colspan="3">
					<?=selectArray($_partner_names, 'partner_no', 2, ':: 전체 ::', $partner_no)?>
					<span class="box_btn_s blue"><input type="button" value="입점사 검색" onclick="ptn_search.open();"></span>
				</td>
			</tr>
			<?php } ?>
			<tr>
                <?php if ($admin['level'] < 4) { ?>
				<th scope="row">바로가기</th>
				<td>
					<label class="p_cursor"><input type="radio" name="short_cut" value="A" <?=checked($short_cut, "A")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="short_cut" value=""  <?=checked($short_cut, "")?>> 바로가기 제외</label>
					<label class="p_cursor"><input type="radio" name="short_cut" value="B" <?=checked($short_cut, "B")?>> 바로가기만</label>
				</td>
                <?php } ?>
				<th scope="row">상품아이콘</th>
				<td>
					<span class="box_btn_s"><input type="button" value="상품아이콘선택" onclick="iconSearch.open('icons='+this.form.icons.value)"></span>
				</td>
			</tr>
			<?php if ($seller_chk) { ?>
			<tr>
				<th scope="row">사입처 미기재</th>
				<td colspan="3">
					<input type="radio" name="seller_chk" value="true" <?=checked($seller_chk, "true")?>> 사입체 미기재 업체 검색
					<input type="radio" name="seller_chk" value="wp" <?=checked($seller_chk, "wp")?>> 윙포스 재고 적용상품 중에서만 검색
				</td>
			</tr>
			<?php } ?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
            <?php if ($admin['level'] < 4) { ?>
			<span class="box_btn quicksearch"><a onclick="viewQuickSearch('prdSearchFrm', 'product');">#단축검색등록</a></span>
            <?php } ?>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_tab">
	<ul>
		<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active1?>>전체<span class="prd_stat_total">0</span></a></li>
		<li><a href="<?=$list_tab_qry?>&prd_stat=2" <?=$list_tab_active2?>>정상<span class="prd_stat_2">0</span></a></li>
		<li><a href="<?=$list_tab_qry?>&prd_stat=3" <?=$list_tab_active3?>>품절<span class="prd_stat_3">0</span></a></li>
		<li><a href="<?=$list_tab_qry?>&prd_stat=4" <?=$list_tab_active4?>>숨김<span class="prd_stat_4">0</span></a></li>
	</ul>
	<div class="btns">
		<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="showExcelBtn(event);"></span>
        <span class="box_btn_s icon setup btt"><input type="button" value="상품 관리 설정" onclick="oconfig.open();"></span>
	</div>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
		<dd><a href="<?=$sort1?>">수정일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></dd>
		<dd><a href="<?=$sort2?>">등록일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></dd>
	</dl>
	<div class="total">
		<span class="esale">이 : 이벤트</span>,
		<span class="msale">회 : 회원혜택</span>,
		<span class="fdlv">무 : 무료배송</span>,
		<?php if($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') {?>
			<span class="fdlv">무(해) : 해외 무료배송(무게차감)</span>,
		<?php } ?>
		<span class="noint">단 : 단독배송</span>,
		<span class="checkout">네 : 네이버 페이</span>
        <?php if ($scfg->comp('use_talkpay', 'Y') == true) { ?>
		<span class="talkpay">카 : 카카오 페이구매</span>
        <?php } ?>
		<?php if ($cfg['use_kakaoTalkStore'] == 'Y') { ?>
		,<span class="talkstore">카 : 카카오톡 스토어</span>
		<?php } ?>
		<?php if ($cfg['n_smart_store'] == 'Y') { ?>
		,<span class="smartstore">스 : 스마트스토어</span>
		<?php } ?>

		<a href="javascript:;" onclick="location.reload();" class="btt" tooltip="새로고침"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<!-- //정렬 -->
<!-- 엑셀 저장 레이어 -->
<form method="post" action="./?body=product@product_excel.exe<?=$xls_query?>" target="hidden<?=$now?>" id="excelLayer" class="popup_layer" style="display:none;">
	<input type="hidden" name="ckno" value="">
	<input type="hidden" name="checked" value="">
	<table class="tbl_mini">
		<tr>
			<th scope="row">엑셀양식</th>
			<td class="left">
				<select name="xls_set_temp" class="xls_set" onchange="change_xls_set(this)">
					<?=$xls_sets; unset($xls_sets);?>
				</select>
			</td>
		</tr>
	</table>
	<div class="btn_bottom">
		<span class="box_btn_s blue"><input type="button" value="엑셀다운" onclick="this.form.submit()"></span>
		<span class="box_btn_s"><input type="button" value="닫기" onclick="showExcelBtn(event);"></span>
	</div>
</form>
<!-- //엑셀 저장 레이어 -->

<!-- 등록버튼 -->
<div id="goto_register">
	<a href="./?body=product@product_register" class="btt"  tooltip="상품등록 바로가기"><img src="<?=$engine_url?>/_manage/image/product/register/add.png"></a>
</div>

<!-- 검색 테이블 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col tbl_col2">
		<caption class="hidden">상품수정/관리 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:40px">
			<col style="width:70px">
			<col>
			<?php if ($cfg['prd_reg_date'] == "Y") { ?>
			<col style="width:65px">
			<?php } ?>
			<col style="width:85px">
			<?php if($cfg['prd_normal_prc'] == 'Y') { ?>
			<col style="width:85px">
			<?php } ?>
			<col style="width:85px">
			<col style="width:70px">
			<col style="width:65px">

            <?php if($cfg['prd_origin_name'] === 'Y') { ?>
            <col style="width:85px">
            <?php } ?>
            <?php if($cfg['prd_seller'] === 'Y') { ?>
            <col style="width:85px">
            <?php } ?>
            <?php if($cfg['prd_origin_prc'] === 'Y') { ?>
            <col style="width:85px">
            <?php } ?>

			<col style="width:45px">
			<col style="width:45px">
			<col style="width:45px">
			<col style="width:45px">
			<col style="width:45px">
			<col style="width:45px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">이미지</th>
				<th scope="col"><a href="<?=$sort3?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<?php if ($cfg['prd_reg_date'] == "Y") { ?>
				<th scope="col">등록일</th>
				<?php } ?>
				<th scope="col"><a href="<?=$sort4?>"><?=$cfg['product_sell_price_name']?> <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<?php if ($cfg['prd_normal_prc'] == 'Y') { ?>
				<th scope="col"><a href="<?=$sort12?>"><?=$cfg['product_normal_price_name']?> <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir12?>.gif" class="arrow <?=$arrowcolor12?>"></a></th>
				<?php } ?>
				<th scope="col"><a href="<?=$sort5?>">적립금 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir5?>.gif" class="arrow <?=$arrowcolor5?>"></a></th>
				<th scope="col">상태</th>
				<th scope="col">판매설정</th>
                <?php if($cfg['prd_origin_name'] === 'Y') { ?>
                <th scope="col">장기명</th>
                <?php } ?>
                <?php if($cfg['prd_seller'] === 'Y') { ?>
                <th scope="col">사입처</th>
                <?php } ?>
                <?php if($cfg['prd_origin_prc'] === 'Y') { ?>
                <th scope="col">사입원가</th>
                <?php } ?>
				<th scope="col"><a href="<?=$sort6?>">조회 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir6?>.gif" class="arrow <?=$arrowcolor6?>"></a></th>
				<th scope="col"><a href="<?=$sort7?>">주문 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir7?>.gif" class="arrow <?=$arrowcolor7?>"></a></th>
				<th scope="col"><a href="<?=$sort8?>">판매 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir8?>.gif" class="arrow <?=$arrowcolor8?>"></a></th>
				<th scope="col"><a href="<?=$sort9?>">관심 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir9?>.gif" class="arrow <?=$arrowcolor9?>"></a></th>
				<th scope="col"><a href="<?=$sort10?>">담기 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir10?>.gif" class="arrow <?=$arrowcolor10?>"></a></th>
				<th scope="col">재고</th>
			</tr>
		</thead>
		<?php include $engine_dir."/_manage/product/product_list.inc.php"; ?>
	</table>
</form>
<!-- //검색 테이블 -->
<!-- 페이징 & 버튼 -->
<div class="box_bottom">
	<?=$pageRes?>
	<?php if ($admin['level'] < 4 || $cfg['partner_prd_accept'] == 'N') { ?>
	<div class="left_area">
		<span class="box_btn_s icon setup"><input type="button" value="가격/적립금/상태 수정" onclick="editPrd();"></span>
	</div>
	<?php if ($cpn_mode != 2) { ?>
	<div class="right_area">
		<span class="box_btn_s icon delete"><input type="button" value="선택삭제" onclick="deletePrd('<?=$cfg['use_trash_prd']?>');"></span>
	</div>
	<?php } ?>
	<?php } ?>
</div>
<!-- //페이징 & 버튼 -->
<!-- 하단 탭 메뉴 -->
<?php if ($cpn_mode != 2) { ?>
<div id="controlTab">
	<div class="tabs-wrap">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" class="selected">판매설정수정</li>
			<li id="ctab_13" onclick="tabSH(13)">상품정보고시일괄수정</li>
			<li id="ctab_2" onclick="tabSH(2)">가격일괄수정</li>
			<?php if ($admin['level'] < 4 && $cfg['milage_type'] == 1) { ?><li id="ctab_3" onclick="tabSH(3)">적립금일괄수정</li><?php } ?>
			<?php if ($admin['level'] < 4 && $cfg['ts_use'] == 'Y') { ?>
			<li id="ctab_12" onclick="tabSH(12)">타임세일일괄수정</li>
			<?php } ?>
			<?php if ($admin['level'] < 4 || $cfg['partner_prd_accept'] == 'N') {?><li id="ctab_4" onclick="tabSH(4)">상태/노출일괄수정</li><?php } ?>
			<li id="ctab_8" onclick="tabSH(8)">상품항목일괄수정</li>
			<?php if ($admin['level'] < 4) { ?>
				<li id="ctab_6" onclick="tabSH(6)">사입처일괄수정</li>
				<li id="ctab_9" onclick="iconConfig.open('NumTotalRec=<?=$NumTotalRec?>&query_string=<?=urlencode($QUERY_STRING)?>')">아이콘일괄수정</li>
				<!--
				<li id="ctab_7" onclick="tabSH(7)">윙Pos 적용</li>
				-->
				<li id="ctab_5" onclick="tabSH(5)">이동/바로가기</li>
				<li id="ctab_10" onclick="tabSH(10)">기획전적용</li>
				<?php if ($cfg['m_currency_type'] != 'N' && $cfg['currency_type'] != $cfg['m_currency_type']) { ?>
				<li id="ctab_11" onclick="tabSH(11)">가격환율반영일괄수정</li>
				<?php } ?>
			<?php } ?>
		</ul>
	</div>
	<div class="btns"><button type="button" class="scroll-left" data-element="#controlTab .tabs-wrap">&lt;</button><button type="button" class="scroll-right"  data-element="#controlTab .tabs-wrap">&gt;</button></div>
	<div class="context">
		<!-- 판매설정 수정 -->
		<form id="edt_layer_1" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="exec" value="event">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<?php if ($admin['level'] > 3 && $cfg['partner_prd_accept'] == 'Y') { ?>
			<input type="hidden" name="ori_no" value="<?=$data['ori_no']?>" />
			<table class="tbl_row_reg">
				<tbody>
					<tr>
						<td>
							<textarea name="partner_cmt" class="txta" placeholder="&#13;&#10; 변경 내용 및 사유를 입력해주세요."><?=stripslashes($partner_cmt)?></textarea>
							<div class="list_info tp">
								<p class="warning">변경된 상품은 일시적으로 판매가 중지되며, 본사 승인 후 변경된 설정으로 판매하실 수 있습니다.</p>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php } ?>
			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품의</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
			</div>
			<table class="tbl_row tbl_row2">
				<colgroup>
					<col style="width:15%;">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th scope="row">할인/적립 이벤트 <a href="./?body=promotion@event_list" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="event" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="event" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="event" value="N"> 해제</label>
						</td>
					</tr>
					<tr>
						<th scope="row">회원혜택 <a href="./?body=member@member_group" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="member_sale" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="member_sale" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="member_sale" value="N"> 해제</label>
						</td>
					</tr>
					<tr>
						<th scope="row">무료배송 <a href="./?body=config@delivery" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="free_delivery" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="free_delivery" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="free_delivery" value="N"> 해제</label>
						</td>
					</tr>
					<?php if(($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') && fieldExist($tbl['product'], 'oversea_free_delivery')) { ?>
					<tr>
						<th scope="row">해외 무료배송(무게차감)</th>
						<td>
							<label class="p_cursor"><input type="radio" name="oversea_free_delivery" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="oversea_free_delivery" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="oversea_free_delivery" value="N"> 해제</label>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<th scope="row">단독배송</th>
						<td>
							<label class="p_cursor"><input type="radio" name="dlv_alone" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="dlv_alone" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="dlv_alone" value="N"> 해제</label>
						</td>
					</tr>
					<tr>
						<th scope="row">네이버페이 <a href="./?body=config@easypay" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="checkout" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="checkout" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="checkout" value="N"> 해제</label>
						</td>
					</tr>
                    <?php if ($cfg['use_talkpay'] == 'Y') { ?>
					<tr>
						<th scope="row" rowspan="2">카카오 페이구매 <a href="./?body=config@easypay#talkpay" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="talkpay" class="talkpay_chg" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="talkpay" class="talkpay_chg" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="talkpay" class="talkpay_chg" value="N"> 해제</label>
						</td>
					</tr>
                    <tr>
                        <td class="kakao_annoucement" style="display: none">
                            카카오페이구매 정보제공고시 <?=selectArray($_kakao_annoucements, 'kakao_annoucement_idx', null, ':: 선택 ::')?>
                        </td>
                    </tr>
                    <?php } ?>
					<?php if ($cfg['import_flag_use'] == 'Y') { ?>
					<tr>
						<th scope="row">해외구매대행<a href="./?body=openmarket@compare_setup" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="import_flag" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="import_flag" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="import_flag" value="N"> 해제</label>
						</td>
					</tr>
					<?php } ?>
					<?php if ($cfg['compare_today_start_use'] == 'Y') { ?>
					<tr>
						<th scope="row">오늘출발<a href="./?body=openmarket@compare_setup" target="_blank" class="sclink">설정</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="compare_today_start" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="compare_today_start" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="compare_today_start" value="N"> 해제</label>
						</td>
					</tr>
					<?php } ?>
					<?php if ($cfg['use_no_mile/cpn'] == 'Y') { ?>
					<tr>
						<th scope="row">적립금 사용불가</a></th>
						<td>
							<label class="p_cursor"><input type="radio" name="no_milage" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="no_milage" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="no_milage" value="N"> 해제</label>
						</td>
					</tr>
					<tr>
						<th scope="row">쿠폰 사용불가</th>
						<td>
							<label class="p_cursor"><input type="radio" name="no_cpn" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="no_cpn" value="Y"> 적용</label>
							<label class="p_cursor"><input type="radio" name="no_cpn" value="N"> 해제</label>
						</td>
					</tr>
					<?php } ?>
                    <?php if ($scfg->comp('compare_explain', 'Y') == true) { ?>
					<tr>
						<th scope="row">쇼핑 검색엔진 포함</th>
						<td>
							<label class="p_cursor"><input type="radio" name="no_ep" value="" checked> 변화없음</label>
							<label class="p_cursor"><input type="radio" name="no_ep" value="N"> 적용</label>
							<label class="p_cursor"><input type="radio" name="no_ep" value="Y"> 해제</label>
						</td>
					</tr>
                    <?php } ?>
				</tbody>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //판매설정 수정 -->
		<!-- 가격 일괄수정 -->
		<form id="edt_layer_2" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_price.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="sell_prc">
			<?php if ($admin['level'] > 3 && $cfg['partner_prd_accept'] == 'Y') { ?>
			<input type="hidden" name="ori_no" value="<?=$data['ori_no']?>" />
			<table class="tbl_row_reg">
				<tbody>
					<tr>
						<td>
							<textarea name="partner_cmt" class="txta" placeholder="&#13;&#10; 변경 내용 및 사유를 입력해주세요."><?=stripslashes($partner_cmt)?></textarea>
							<div class="list_info tp">
								<p class="warning">변경된 상품은 일시적으로 판매가 중지되며, 본사 승인 후 변경된 설정으로 판매하실 수 있습니다.</p>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php } ?>
			<div class="box_middle2 left">
				<div style="margin-bottom:5px;">
					<label><input type="radio" name="prc_chg_type" value="1" checked> 할인 적용</label>
					<label><input type="radio" name="prc_chg_type" value="2"> 균일가 적용</label>
				</div>

				<select name="where">
					<option value="1">선택한 상품</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)</option>
				</select>

				<span class="prc_chg_type1">
					<select name="o1">
						<option value="normal_prc"><?=$cfg['product_normal_price_name']?></option>
						<option value="sell_prc"><?=$cfg['product_sell_price_name']?></option>
						<?=$memprc?>
					</select>
					의 <input type="text" name="p1" value="" class="input" size="10"> (소수점 가능)
					<select name="p2">
						<option value="1">%</option>
						<option value="2">원</option>
					</select> 을
					<select name="p3">
						<option value="-">할인</option>
						<option value="+">할증</option>
					</select> 된 가격으로
					<select name="o2">
						<option value="normal_prc"><?=$cfg['product_normal_price_name']?></option>
						<option value="sell_prc"><?=$cfg['product_sell_price_name']?></option>
						<?=$memprc?>
					</select> 을 일괄수정합니다.

					<br><br>새로운 가격은
					<select name="r1">
						<option value="1">1</option>
						<option value="10">10</option>
						<option value="100">100</option>
						<option value="1000">1000</option>
					</select> 원 단위로
					<select name="r2">
						<option value="1">내림</option>
						<option value="2">반올림</option>
						<option value="3">올림</option>
					</select> 합니다.
				</span>

				<span class="prc_chg_type2" style="display:none;">
					<select name="o3">
						<option value="normal_prc"><?=$cfg['product_normal_price_name']?></option>
						<option value="sell_prc"><?=$cfg['product_sell_price_name']?></option>
						<?=$memprc?>
					</select>
					를 <input type="text" name="replace_prc" class="input" size="10"> 원으로 일괄 수정합니다.
				</span>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //가격 일괄수정 -->
		<!-- 적립금 일괄수정 -->
		<form id="edt_layer_3" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_price.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="milage">
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품의</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
				적립금을
				<?=$cfg['product_sell_price_name']?>의
				<input type="text" name="p1" value="" class="input" size="10"> (소수점 가능)
				<select name="p2">
					<option value="1">%</option>
					<option value="2">원</option>
				</select> 로 일괄수정합니다.
				<br><br>새로운 적립금은
				<select name="r1">
					<option value="1">1</option>
					<option value="10">10</option>
					<option value="100">100</option>
					<option value="1000">1000</option>
				</select> 원 단위로
				<select name="r2">
					<option value="1">내림</option>
					<option value="2">반올림</option>
					<option value="3">올림</option>
				</select> 합니다.
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //적립금 일괄수정 -->
		<!-- 상태 일괄수정 -->
		<form id="edt_layer_4" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_price.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="stat">
			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품의</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
			</div>
			<table class="tbl_row tbl_row2">
				<colgroup>
					<col style="width:15%;">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th scope="row">상태</th>
						<td>
							<label><input type="radio" name="change_stat" value="" checked> 변화없음</label>
							<label><input type="radio" name="change_stat" value="2"> 정상</label>
							<label><input type="radio" name="change_stat" value="3"> 품절</label>
							<label><input type="radio" name="change_stat" value="4"> 숨김</label>
						</td>
					</tr>
					<tr>
						<th scope="row">상품 목록</th>
						<td>
							<label><input type="radio" name="perm_lst" value="" checked> 변화없음</label>
							<label><input type="radio" name="perm_lst" value="Y"> 노출</label>
							<label><input type="radio" name="perm_lst" value="N"> 미노출</label>
						</td>
					</tr>
					<tr>
						<th scope="row">상품 상세</th>
						<td>
							<label><input type="radio" name="perm_dtl" value="" checked> 변화없음</label>
							<label><input type="radio" name="perm_dtl" value="Y"> 노출</label>
							<label><input type="radio" name="perm_dtl" value="N"> 미노출</label>
						</td>
					</tr>
					<tr>
						<th scope="row">검색 결과</th>
						<td>
							<label><input type="radio" name="perm_sch" value="" checked> 변화없음</label>
							<label><input type="radio" name="perm_sch" value="Y"> 노출</label>
							<label><input type="radio" name="perm_sch" value="N"> 미노출</label>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //상태 일괄수정 -->
		<!-- 사입처 일괄수정 -->
		<form id="edt_layer_6" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="exec" value="seller">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="w" value="<?=$w?>">
			<?php
				$providers = "";
				$res = $pdo->iterator("select no, provider, arcade, floor from $tbl[provider] order by arcade, provider");
                foreach ($res as $prdata) {
					$provider = cutstr(stripslashes($prdata['provider']), 30);
					$arcade = trim(stripslashes($prdata['arcade']));
					if($arcade) $arcade = "[$arcade] ";
					$providers .= "<option value='$prdata[no]'>$arcade$provider</option>";
				}
			?>
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
				새 사입처
				<select name="seller_idx">
					<?=$providers?>
				</select>
				로 변경
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //사입처 일괄수정 -->
		<!-- 이동/바로가기/복사 -->
		<form id="edt_layer_5" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display: none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
				<select name="nctype" id="nctype">
					<option value="1">기본 매장분류</option>
					<?php if ($cfg['xbig_mng'] == "Y") { ?>
					<option value="4"><?=$cfg['xbig_name_mng']?></option>
					<?php } if ($cfg['ybig_mng'] == "Y") { ?>
					<option value="5"><?=$cfg['ybig_name_mng']?></option>
					<?php } ?>
				</select>
				<select name="nbig" onchange="chgCateInfinite(this, 2, 'n')">
					<option value="">::대분류::</option>
					<?=$item_1_1?>
				</select>
				<select name="nmid" onchange="chgCateInfinite(this, 3, 'n')">
					<option value="">::중분류::</option>
					<?=$item_1_2?>
				</select>
				<select name="nsmall" onchange="chgCateInfinite(this, 4, 'n')">
					<option value="">::소분류::</option>
					<?=$item_1_3?>
				</select>
				<?php if ($cfg['max_cate_depth'] >= 4) { ?>
				<select name="ndepth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
				<?php } ?>
				로
				<select name="exec" onchange="swapCopyDesc(this)">
					<option value="move">이동하기</option>
					<option value="shortcut">바로가기 생성</option>
					<option value="fullcopy">복사하기</option>
				</select>
			</div>
			<table class="tbl_row tbl_row2" id="imagecopy">
				<colgroup>
					<col style="width:15%;">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th scope="row">선택 항목</th>
						<td>
							<input type="checkbox" name="imgcopy" value="Y"> 대/중/소 상품이미지 복사
							<div class="list_info tp">
								<p>대/중/소 상품이미지 복사는 최대 10개 이하 상품 복사하기 시에만 사용이 가능합니다.</p>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
			<div id="shortcut_desc" style="display:none;">
				<div class="box_title">
					<h2 class="title">바로가기</h2>
				</div>
				<div class="box_bottom top_line">
					<ul class="list_info left">
						<li>
							바로가기 기능은 윈도우의 바로가기(Short Cut)와 유사한 개념으로 한 상품을 여러 분류에 진열할 때 사용합니다.<br>
							(기본 매장분류이외 기획전을 이용하여 동일한 상품을 여러 분류에 진열할 수도 있습니다.)
						</li>
						<li>본 상품을 수정할 경우 바로가기 상품 또한 함께 수정(분류/수정일 제외)되며, 본 상품을 삭제할 경우 바로가기 상품 또한 삭제됩니다.</li>
						<li>바로가기 상품은 기획전에 진열할 수 없으며, 분류와 수정일을 제외한 모든 정보를 본 상품을 따릅니다.</li>
					</ul>
				</div>
			</div>
			<div id="copy_desc" style="display:none;">
				<div class="box_title">
					<h2 class="title">복사하기</h2>
				</div>
				<div class="box_bottom top_line">
					<ul class="list_info left">
						<li>복사하기 기능은 유사한 상품을 연속해서 등록할 때 편리하게 사용할 수 있습니다. 동일 상품을 다른 분류에 노출할 경우 바로가기 기능을 이용해 주시길 바랍니다.</li>
						<li>복사된 상품은 추가 편집에 따른 <strong>숨김</strong>상태로 복사됩니다.</li>
						<li>상품상세설명은 원본으로부터 복사되며, 원본상품 내 업로드 된 상품상세설명 이미지를 삭제할 경우, 복사상품의 상품상세설명이 표시되지 않을 수 있습니다.</li>
						<li>복사되지 않는 항목 : 상품상세설명 이미지, 기획전, 재고, 등록일, 수정일, 조회수, 판매수</li>
						<li>선택 항목 : 대/중/소 상품이미지</li>
					</ul>
				</div>
			</div>
		</form>
		<!-- //이동/바로가기/복사 -->
		<!-- 윙Pos 적용 -->
		<form id="edt_layer_7" method="post" action="./" target="hidden<?=$now?>" onsubmit="return confirmConv(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_pos_conv.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select> 윙Pos 복합재고 옵션으로 변경 합니다.
				<ul class="list_info tp">
					<li>상품을 윙Pos 복합옵션 방식으로 자동 변경합니다. 단 <span class="point">필수옵션이 3개 이상인 상품</span>은 처리되지 않으며, '처리 예외 상품'란에 표시되므로 수동 처리 해 주십시오.</li>
					<li>품절이나 숨김 상태의 상품은 <span class="point">'재고 강제품절'</span>상태로 설정 됩니다.</li>
					<li>개인결제창 소속 상품은 윙Pos 복합옵션으로 변경되지 않으며, 바로가기 상품은 처리 개수에 포함되지 않습니다.</li>
					<li>변환 된 모든 복합옵션의 재고수량은 0개로 설정됩니다. 반드시 수동으로 재고를 입력 해 주십시오. 단, 수량 0개인 상태에도 상품 재고가 감산될 경우 갯수가 마이너스로 내려가며 계속 판매가 진행됩니다.</li>
				</ul>
				<div id="wp_distinct" style="display:none;">
					<ul class="list_msg">
					</ul>
				</div>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //윙Pos 적용 -->
		<!-- 상품항목일괄수정 -->
		<form id="edt_layer_8" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="field">
			<?php if ($admin['level'] > 3 && $cfg['partner_prd_accept'] == 'Y') { ?>
			<input type="hidden" name="ori_no" value="<?=$data['ori_no']?>" />
			<table class="tbl_row_reg">
				<tbody>
					<tr>
						<td>
							<textarea name="partner_cmt" class="txta" placeholder="&#13;&#10; 변경 내용 및 사유를 입력해주세요."><?=stripslashes($partner_cmt)?></textarea>
							<div class="list_info tp">
								<p class="warning">변경된 상품은 일시적으로 판매가 중지되며, 본사 승인 후 변경된 설정으로 판매하실 수 있습니다.</p>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php } ?>
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품의</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
				<select name="fno" onchange="setFieldType(this)">
					<option value="">:: 항목선택 ::</option>
					<?PHP
						$res = $pdo->iterator("select no, name from {$tbl['product_filed_set']} where category='0' order by no asc, name asc");
						foreach ($res as $data) {
							if($data['cname']) $data['name'] = stripslashes($data['cname']).' > '.$data['name'];
					?>
					<option value="<?=$data['no']?>" ftype="<?=$data['ftype']?>" soptions="<?=stripslashes($data['soptions'])?>"><?=$data['name']?></option>
					<?php } ?>
				</select> 내용을
				<span id="field_input"><input type="text" name="fvalue" class="input" size="15"></span> 으로 일괄수정/입력합니다.
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //상품항목일괄수정 -->
		<!-- 주문/판매수 수정 -->
		<form id="edt_layer_9" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="saleOrdNum">
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품의</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
				주문/판매수를 새로고침합니다.
				<span class="desc4">(실행 시 사이트 로딩이 느려질 수 있습니다. 주문이 많은시기에는 실행을 피해 주시기 바랍니다)</span>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //주문/판매수 수정 -->
		<!-- 기획전적용 -->
		<form id="edt_layer_10" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="toEbig">
			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
				선택한 기획전에
				<select name="ebig_mode">
					<option value="add">추가하기</option>
					<option value="remove">제외하기</option>
				</select>
			</div>
			<table class="tbl_row tbl_row2" id="imagecopy">
				<colgroup>
					<col style="width:15%;">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th scope="row">PC 기획전</th>
						<td>
						<?php
						$res = $pdo->iterator("select * from $tbl[category] where ctype in (2) and hidden='N' order by ctype asc, sort asc");
                        foreach ($res as $data) {
						?>
							<label class="p_cursor"><input type="checkbox" name="ebig[]" value="<?=$data['no']?>"> <?=stripslashes($data['name'])?></label>
						<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row">모바일 기획전</th>
						<td>
						<?php
						$res = $pdo->iterator("select * from $tbl[category] where ctype in (6) and hidden='N' order by ctype asc, sort asc");
                        foreach ($res as $data) {
						?>
							<label class="p_cursor"><input type="checkbox" name="mbig[]" value="<?=$data['no']?>"> <?=stripslashes($data['name'])?></label>
						<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row">선택 항목</th>
						<td>
							<div>
								<label class="p_cursor">
									<input type="checkbox" name="ebig_first" value="Y" checked>상품이 선택된 기획전에 포함될 경우 기획전의 최상위에 배열합니다.
								</label>
								<ul class="list_info tp">
									<li>다수의 상품이 한 기획전에 동시 추가될 경우 최근등록상품이 우선 배열됩니다.</li>
									<li>이미 기획전에 포함된 상품은 배열순서 변동이 발생되지 않습니다.</li>
								</ul>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //기획전적용 -->
		<!-- 가격환율반영일괄수정 -->
		<form id="edt_layer_11" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)" style="display:none;">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="exchangeRate">
			<div class="box_middle2 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)의</option>
				</select>
				관리 가격을 현재 기준환율으로 환산하여
				<select name="o1">
					<option value="sell_prc"><?=$cfg['product_sell_price_name']?></option>
					<option value="normal_prc"><?=$cfg['product_normal_price_name']?></option>
					<option value="all"><?=$cfg['product_sell_price_name']?>/<?=$cfg['product_normal_price_name']?></option>
					<?=$memprc?>
				</select>
				를 일괄수정합니다. (현재 설정 환율 <b><?=$cfg['cur_sell_price']?> <?=$cfg['currency_type']?> = <?=$cfg['cur_manage_price']?> <?=$cfg['m_currency_type']?></b>)

			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //가격환율반영일괄수정 -->
		<!-- 타임세일일괄수정 -->
		<form id="edt_layer_12" method="post" action="./" target="hidden<?=$now?>" style="display:none;" onsubmit="return edtConfirm(this)">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="timesale">

			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
			</div>

			<table class="tbl_row">
				<caption class="hidden">타임세일일괄수정</caption>
				<colgroup>
					<col style="width:15%">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th>사용여부</th>
						<td>
							<label><input type="radio" name="ts_use" value="N" checked> 사용안함</label>
							<label><input type="radio" name="ts_use" value="Y"> 사용함</label>
						</td>
					</tr>
					<tr class="timesale_S">
						<th>타임세일 설정</th>
						<td>
							<label><input type="radio" name="use_ts_set" value="N" checked> 개별설정</label>
							<label><input type="radio" name="use_ts_set" value="Y"> 세트설정</label>
						</td>
					</tr>
					<tr class="timesale_N">
						<th>한정/타임세일 시간</th>
						<td class="left">
							<input type="input" name="ts_dates" class="input datepicker" size="10" value="">
							<select name="ts_times">
								<?php for ($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
								<option value="<?=$i?>"><?=$i?> 시</option>
								<?php } ?>
							</select>
							<select name="ts_mins">
								<?php for ($i = 0; $i <= 59; $i++) {$i = sprintf('%02d', $i)?>
								<option value="<?=$i?>"><?=$i?> 분</option>
								<?php } ?>
							</select>
							~
							<input type="input" name="ts_datee" class="input datepicker" size="10" value="">
							<select name="ts_timee">
								<?php for ($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
								<option value="<?=$i?>"><?=$i?> 시</option>
								<?php } ?>
							</select>
							<select name="ts_mine">
								<?php for($i = 0; $i <= 59; $i++) {$i = sprintf('%02d', $i)?>
								<option value="<?=$i?>"><?=$i?> 분</option>
								<?php } ?>
							</select>
                            <label><input type="checkbox" name="ts_unlimited" class="ts_unlimited"> 무제한</label>
						</td>
					</tr>
					<tr class="timesale_N">
						<th>지정시간 할인</th>
						<td class="left">
							<input type="text" name="ts_saleprc" class="input right" size="5" value="">
							<label><input type="radio" name="ts_saletype" value="price"> 원</label>
							<label><input type="radio" name="ts_saletype" value="percent" checked> %</label>
							<div class="list_info tp">
								<p>미입력 또는 0 입력시 할인처리 되지 않습니다.</p>
							</div>
						</td>
					</tr>
					<tr class="timesale_N">
						<th>시간종료 후 상태</th>
						<td class="left">
							<select name="ts_state">
                                <option value="">변경 없음</option>
                                <?php for ($key = 3; $key <= 4; $key++) { ?>
									<?php if ($key != 1) { ?>
									<option value="<?=$key?>"><?=$_prd_stat[$key]?></option>
									<?php } ?>
								<?php } ?>
							</select>
							<div class="list_info tp">
								<p>지정시간이 종료되면 상품 상태가 변경됩니다.</p>
							</div>
						</td>
					</tr>
					<tr class="timesale_Y">
						<th>타임세일 세트선택</th>
						<td>
							<?=selectArray($_timesale_sets, 'ts_set', false, ':: 세트선택 ::', $data['ts_set'], 'chgTsSet(this.value)')?>
						</td>
					</tr>
					<tr class="timesale_Y">
						<th>할인/적립</th>
						<td><span class="ts_desc">-</span></td>
					</tr>
					<tr class="timesale_Y">
						<th>할인기간</th>
						<td><span class="ts_date">-</span></td>
					</tr>
					<tr class="timesale_Y">
						<th>할인기간 종료 후 상태</th>
						<td><span class="ts_state">-</span></td>
					</tr>
				</tbody>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<!-- //타임세일일괄수정 -->
        <!-- 상품정보고시 일괄 수정 -->
		<form id="edt_layer_13" method="post" action="./" target="hidden<?=$now?>" style="display:none;" onsubmit="return edtConfirm(this)">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="annoucement">

			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
			</div>

			<table class="tbl_row">
				<caption class="hidden">상품정보고시 일괄 수정 </caption>
				<colgroup>
					<col style="width:15%">
					<col>
				</colgroup>
                <tr>
                    <th>상품정보제공고시</th>
                    <td>
                        <span class="select_bottom"><?=selectArray($_annoucements, 'annoucements', null, ':: 변화없음 ::')?></span>
                    </td>
                </tr>
                <?php if ($scfg->comp('use_talkpay', 'Y') == true) { ?>
                <tr>
                    <th>카카오페이구매<br>상품정보제공고시</th>
                    <td>
                        <span class="select_bottom"><?=selectArray($_kakao_annoucements, 'kakao_annoucement_idx', null, ':: 변화없음 ::')?></span>
                    </td>
                </tr>
                <?php } ?>
            </table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
        </form>
        <!-- // 상품정보고시 일괄 수정 -->
	</div>
</div>
<?php } else { ?>
<div class="pcenter" style="margin-bottom: 20px"><span class="box_btn blue"><input type="submit" value="쿠폰적용" onclick="applyCoupon()"></span></div>
<?php } ?>
<!-- //하단 탭 메뉴 -->

<script type="text/javascript">
	searchDate(document.getElementById('prdSearchFrm'));

	var pf = document.getElementById('prdFrm');
	var tmp_lyr = '';
	$('#imagecopy').hide();

	function numSelect(pf) {
		var nums = '';
		if (pf.check_pno.length) {
			for (i=0; i < pf.check_pno.length; i++) {
				if (pf.check_pno[i].checked==true) nums+='@'+pf.check_pno[i].value;
			}
		} else if (pf.check_pno && pf.check_pno.checked==true) {
			nums='@'+pf.check_pno.value;
		}
		return nums;
	}

	function edtConfirm(f) {
		f.nums.value = '';
		if (f.where.selectedIndex == 0) {
			if(!checkCB(pf.check_pno,"변경할 상품을 선택해주세요.")) return false;
			f.nums.value = numSelect(pf);
		}
		// 필수값 체크
		switch(f.id) {
			case 'edt_layer_1' : // 판매설정수정
                /*
				if (f.no_interest[0].checked==true && f.event[0].checked==true && f.member_sale[0].checked==true && f.free_delivery[0].checked==true && f.checkout[0].checked==true && f.ipay[0].checked==true) {
					window.alert('이벤트/무이자/회원할인/무료배송/네이버 페이 설정이 모두 변화가 없습니다\n');
					return false;
				}
                */
                if($(f.talkpay).filter(':checked').val() == 'Y') {
                    if ($(f.kakao_annoucement_idx).val() == '') {
                        window.alert('카카오페이 상품 정보 제공 고시를 선택해주세요.');
                        return false;
                    }
                }
			break;
			case 'edt_layer_2' : // 가격일괄수정
				if(f.prc_chg_type.value == '1') {
					if (!checkBlank(f.p1,'일괄 수정폭을 입력해주세요.')) return false;
					if (parseInt(f.p1.value) < 0) {
						window.alert('일괄 수정폭을 0 이상 입력하세요');
						f.p1.focus();
						return false;
					}
					if (f.p2.selectedIndex == 0 && parseInt(f.p1.value) > 100){
						window.alert('일괄 수정폭을 100 이하로 입력하세요');
						f.p1.focus();
						return false;
					}
					if (f.p2.selectedIndex == 1 && !checkNum(f.p1,'%가 아닐 경우 일괄 수정폭을')) return false;
				}
				if(f.prc_chg_type.value == '2') {
					if(checkBlank(f.replace_prc, '변경할 금액을 입력해주세요.') == false) {
						f.replace_prc.focus();
						return false;
					}
				}
			break;
			case 'edt_layer_3' : // 적립금일괄수정
				if (!checkBlank(f.p1,'일괄 수정폭을 입력해주세요.')) return false;
				if (parseInt(f.p1.value) < 0) {
					window.alert('일괄 수정폭을 0 이상 입력하세요');
					f.p1.focus();
					return false;
				}
				if (f.p2.selectedIndex == 0 && parseInt(f.p1.value) > 100){
					window.alert('일괄 수정폭을 100 이하로 입력하세요');
					f.p1.focus();
					return false;
				}
				if (f.p2.selectedIndex == 1 && !checkNum(f.p1,'%가 아닐 경우 일괄 수정폭을')) return false;
			break;
			case 'edt_layer_5' : // 복사, 이동
				if(!f.nbig.value) {
					window.alert('이동/복사 할 분류를 선택해 주십시오');
					return false;
				}
				if(((<?=$NumTotalRec?> > 10 && f.where.selectedIndex > 0) || $("input:checkbox[id=check_pno]:checked").length > 10) && $('input:checkbox[name="imgcopy"]').is(":checked") == true) {
					window.alert('대/중/소 상품이미지 복사는\n최대 10개 이하 상품 복사하기 시에만 가능합니다.');
					return false;
				}
			break;
			case 'edt_layer_6' : // 사입처 변경
				if(!f.seller_idx.value) {
					window.alert('지정 할 사입처를 선택 해 주십시오.');
					return false;
				}
			break;
			case 'edt_layer_8' : // 상품항목일괄수정
				if(f.fno.selectedIndex < 1) {
					window.alert('항목을 선택해 주세요.');
					return false;
				}
			break;
		}
		if(!confirm('변경사항을 적용하시겠습니까?')) {
			return false;
		}

        printLoading();
	}

	function applyCoupon(){
		pf.exec.value='coupon';
		pf.body.value='product@product_update.exe';
		checkAll(pf.check_pno,true)
		pf.submit();
	}

	var wp_status = document.getElementById('wp_distinct');
	function confirmConv(f) {
		f.nums.value = '';
		if (f.where.selectedIndex == 0) {
			if(!checkCB(pf.check_pno,"변경할 상품을 선택해주세요.")) return false;
			f.nums.value = numSelect(pf);
		}
		if(confirm('윙Pos 변환 작업을 진행 하시겠습니까?')) {
			$('#wp_distinct').show();
			wp_status.children(1).innerHTML = '';
			f.submit();
		}
	}

	function wpStatus(msg, child) {
		switch(child) {
			case 'process' :
				wp_status.children(0).innerHTML = msg;
			break;
			case 'exception' :
				wp_status.children(1).innerHTML += msg;
			break;
		}
	}

	function setFieldType(obj) {
		obj = $(obj.options[obj.selectedIndex]);
		var ftype = obj.attr('ftype');
		if(ftype == '2') {
			var soptions = obj.attr('soptions').split(',');
			var temp = "<ul class='desc2'>";
			for(var key in soptions) {
				checked = key == 0 ? 'checked' : '';
				temp += "<li><input type='radio' name='fvalue' "+checked+" value=\""+soptions[key]+"\"> "+soptions[key]+'</li>';
			}
			temp += "</ul>";
			$('#field_input').html(temp);
		} else {
			$('#field_input').html("<input type='text' name='fvalue' class='input' size='15'>");
		}
	}

	function swapCopyDesc(obj) {
		if(obj.value == 'fullcopy') {
			$('#copy_desc').show();
			$('#shortcut_desc').hide();
			$('#imagecopy').show();
		} else if(obj.value == 'shortcut') {
			$('#copy_desc').hide();
			$('#shortcut_desc').show();
			$('#imagecopy').hide();
		} else {
			$('#copy_desc').hide();
			$('#shortcut_desc').hide();
			$('#imagecopy').hide();
		}
	}


	$('#nctype').change(function(){
		var ctype = this.value;
		var _this = this;
		var data = $.get("./?body=product@product_cate.exe&no="+$(this).val()+"&level=1&parent=ctype", function(data) {
			if(ctype == 4 || ctype == 5) $('select[name="exec"] option:gt(0)').attr('disabled', true);
			else $('select[name="exec"] option:gt(0)').attr('disabled', false);

			$(_this.form.nbig).find('option:gt(0)').remove();
			$(_this.form.nmid).find('option:gt(0)').remove();
			$(_this.form.nsmall).find('option:gt(0)').remove();

			if(data) {
				line = data.split("◁▷");
				for (i = 0; i < line.length; i++) 	{
					temp = line[i].split ("◀▶");
					if (temp[0] && temp[1]) {
						newOPT = document.createElement("OPTION");
						newOPT.value = temp[0];
						newOPT.innerText = temp[1];
						_this.form.nbig.appendChild(newOPT);
					}
				}
			}
		});
	});


	//입점파트너 레이어 추가
	var ptn_search = new layerWindow('product@product_join_shop.inc.exe');
	ptn_search.psel = function(no,stat) {
		if(stat == "신청") {
			alert("선택한 입점파트너는 ["+stat+"] 상태입니다.");
			return false;
		}
		document.prdSearchFrm.partner_no.value = no;
		ptn_search.close();
	}

	// 가격일괄수정 적용 방식
	$(':radio[name=prc_chg_type]').change(function(){
		if(this.value == '1') {
			$('.prc_chg_type1').show();
			$('.prc_chg_type2').hide();
		} else {
			$('.prc_chg_type1').hide();
			$('.prc_chg_type2').show();
		}
	});

	// 타임세일
	(setTsSet = function() {
		if($(':checked[name=use_ts_set]').val() == 'Y') {
			$('.timesale_N').hide();
			$('.timesale_Y').show();
		} else {
			$('.timesale_Y').hide();
			$('.timesale_N').show();
		}
	})();

	(setTsUse = function() {
		if($(':checked[name=ts_use]').val() == 'Y') {
			$('.timesale_N').show();
			$('.timesale_Y').hide();
			$('.timesale_S').show();
		} else {
			$('.timesale_Y').hide();
			$('.timesale_N').hide();
			$('.timesale_S').hide();
			$(':radio[name=use_ts_set][value=N]').prop('checked', true);
		}
	})();

	$(':radio[name=use_ts_set]').change(setTsSet);
	$(':radio[name=ts_use]').change(setTsUse);
    $('.ts_unlimited').on('click', function() {
        var f = document.edt_layer_12;
        if (this.checked == true) {
            $('[name=ts_datee]', f).datepicker('option', 'disabled', true).css('background', '#f2f2f2');
            $('[name=ts_timee], [name=ts_mine]', f).prop('disabled', true).css('background', '#f2f2f2');
        } else {
            $('[name=ts_datee]', f).datepicker('option', 'disabled', false).css('background', '');
            $('[name=ts_timee], [name=ts_mine]', f).prop('disabled', false).css('background', '');
        }
    });

$(function() {
    // 판매 설정 일괄 수정 카카오페이구매
    $('.talkpay_chg').on('change', function() {
        if (this.value == 'Y') {
            $('.kakao_annoucement').show();
        } else {
            $('.kakao_annoucement').hide();
        }
    });
});
    var oconfig = new layerWindow('product@product_config_product.exe');
</script>