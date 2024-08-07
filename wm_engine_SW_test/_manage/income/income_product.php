<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품별매출
	' +----------------------------------------------------------------------------------------------+*/

	$_search_type['name']='상품명';
	$_search_type['keyword']='검색 키워드';
	$_search_type['origin_name']='장기명';

	$_shortcut['N'] = '바로가기제외';
	$_shortcut['Y'] = '바로가기포함';

	$search_type = addslashes($_GET['search_type']);
	$shortcut = addslashes($_GET['shortcut']);
	$dates = addslashes($_GET['dates']);
	$datee = addslashes($_GET['datee']);
	$sdates = addslashes($_GET['sdates']);
	$sdatee = addslashes($_GET['sdatee']);
	$search = addslashes($_GET['search']);
	$cate_colname = $_GET['cate_colname'];
	$ostat = $_GET['ostat'];
	$cno = numberOnly($_GET['cno']);
	$seller = addslashes($_GET['seller']);
	$pstat = $_GET['pstat'];
	$listURL = urlencode(getURL());
	$selected = ($data['provider'] == $_GET['seller']) ? 'selected' : '';
	$row = numberOnly($_GET['row']);
    $sort = numberOnly($_GET['sort']);

	// 상품 등록일
	$_dates = strtotime($dates);
	$_datee = strtotime($datee)+86399;
	if($dates && $datee) $pw .= " and p.`reg_date` between $_dates and $_datee";
	if($dates && !$datee) $pw .= " and p.`reg_date` >= $_dates";
	if(!$dates && $datee) $pw .= " and p.`reg_date` <= $_datee";

	// 상품 구매일
	$_sdates = strtotime($sdates);
	$_sdatee = strtotime($sdatee)+86399;
	if($sdates && $sdatee) $w .= " and x.`date1` between $_sdates and $_sdatee";
	if($sdates && !$sdatee) $w .= " and x.`date1` >= $_sdates";
	if(!$sdates && $sdatee) $w .= " and x.`date1` <= $_sdatee";

	if(!$w && !$pw) $search = ''; // 상품등록일이나 구매일이 있을때만 검색


	// 카테고리 검색
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
	}
	$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($sql as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ($_GET[$cl] == $cate['no']) ? "selected" : "";
		${"item_".$cate['ctype']."_".$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}
	for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
		$cl = $scf = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) {
			if($shortcut == 'Y') $pw .= " and (p.$cl='$val' or s.$cl='$val')";
			else $pw .= " and p.$cl='$val'";
			break;
		}
	}

	// 기획전 검색
	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype`='2' order by `level`,`sort`");
    foreach ($sql as $data) {
		$sel = $data['no'] == $_GET['cno'] ? 'selected' : '';
		$item_2 .= "\n\t<option value='$data[no]' $sel>".stripslashes($data[name])."</option>";
	}
	if($cno) $pw .= " and p.`ebig` like '%@$cno%'";

	// 사입처 검색
	$sql = $pdo->iterator("select * from `$tbl[provider]` order by `provider`");
    foreach ($sql as $data) {
		$selected = ($data['provider'] == $_GET['seller']) ? 'selected' : '';
		$data['provider'] = stripslashes($data['provider']);
		$_seller .= "<option value='$data[provider]' $selected>$data[provider]</option>\n";
	}
	if($_GET['seller']) $pw .= " and `seller` = '$seller'";

	// 주문상태
	foreach($_order_stat as $key => $val) {
		if($key >= 20) continue;
		$_otype = ($key > 10) ? 2 : 1;
		if(is_array($ostat)) $checked = in_array($key, $ostat) ? 'checked' : '';
		${'_search_ostat_'.$_otype} .= "<li><label class='p_cursor'><input type='checkbox' name='ostat[]' value='$key' $checked> $val</label></li>\n";
	}
	if(is_array($ostat)) {
		$ostat = implode(',', $ostat);
		if($ostat) $w .= " and o.`stat` in ($ostat)";
	}

	// 상품상태
	$_prd_stat = array(2 => '정상', 3 => '품절', 4 => '숨김');
	foreach($_prd_stat as $key => $val) {
		if(is_array($pstat)) $checked = in_array($key, $pstat) ? 'checked' : '';
		$search_pstat .= "<label class='p_cursor'><li><input type='checkbox' name='pstat[]' value='$key' $checked> $val</li></label>\n";
	}
	if(is_array($pstat)) {
		$pstat = implode(',', $pstat);
		if($pstat) $pw .= " and p.`stat` in ($pstat)";
	}

	// 키워드 검색
	$search_str = addslashes(trim($_GET['search_str']));
	if($search_type && $search_str) {
		$pw .= " and p.`$search_type` like '%$search_str%'";
	}

	// 입점 파트너 검색
	if($cfg['use_partner_shop'] == 'Y') {
		if(strlen($_GET['partner_no']) > 0) {
			$partner_no = numberOnly($_GET['partner_no']);
			$pw .= " and p.partner_no='$partner_no'";
		}
		$_partners = array(0 => '본사');
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat between 2 and 4 order by corporate_name asc");
        foreach ($pres as $pdata) {
			$_partners[$pdata['no']] = stripslashes($pdata['corporate_name']);
		}
	}

	// 정렬
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_group = '?'.preg_replace('/&group=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	if($sort == null) $sort = 2;
	for ($i = 1; $i <= 7; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}
	$_sort = array(
		"p.`name` asc",
		"p.`name` desc",
		"`amount` desc",
		"`amount` asc",
		"`price` desc",
		"`price` asc",
		"p.`reg_date` desc",
		"p.`reg_date` asc",
		"p.`sell_prc` desc",
		"p.`sell_prc` asc",
		"p.`normal_prc` desc",
		"p.`normal_prc` asc",
		"p.`hit_view` desc",
		"p.`hit_view` asc",
	);
	$ord = $_sort[$sort];

	if(!$row) $row = 50;

	if($shortcut == 'Y' && $scf) {
		$prd_field = "(select p.*, if(s.`$scf`='{${$scf}}',s.wm_sc,0) as wm_sc2, if(p.wm_sc>0, p.wm_sc, p.no) as pno from `wm_product` p left join wm_product s on s.wm_sc=p.no where p.`stat` > 1 and p.`stat` < 5 $pw group by pno)";
	} else {
		$prd_field = $tbl['product'];
		$w = " and p.`stat` > 1 and p.`stat` < 5 $pw ".$w;
	}

	$g = '';
	if(!$_GET['group']) $_GET['group'] = 'prd';
	$group = $_GET['group'];
	if($group == 'opt') {
		$g .= ", o.complex_no";
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  통계리스트
	' +----------------------------------------------------------------------------------------------+*/
	$cq = getSaleField('-o.');
	$qry = "select p.*, sum(`buy_ea`) as `amount`, sum(o.`total_prc` $cq) as `price`, o.`option` from $prd_field p inner join `$tbl[order_product]` o on p.`no` = o.`pno` inner join `$tbl[order]` x on o.`ono` = x.`ono` where o.`stat` not in (11, 31, 32) and (x.x_order_id='' or x.x_order_id in ('checkout', 'talkstore') or x.x_order_id is null)  $w group by o.`pno` $g order by $ord limit $row";

	if($search == 'true') {
		$res = $pdo->iterator($qry);
	}

	$xls_query = '';
	foreach($_GET as $key => $val) {
		if($key == 'body' || !$val) continue;
		if(is_array($val)) {
			foreach($val as $key2 => $val2) {
				$xls_query .= "&".$key."[$key2]=".urlencode($val2);
			}
		} else {
			$xls_query .= "&$key=".urlencode($val);
		}
	}
	if($body == 'income@income_product_excel.exe' || $body == 'order@income_product_excel.exe' ) {
		return;
	}
?>
<form id="search" method="get">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<input type="hidden" name="search" value="true">
	<input type="hidden" name="row" value="<?=$row?>">
	<div class="box_title first">
		<h2 class="title">상품별매출</h2>
	</div>

	<div id="search">
		<div class="box_search box_search2">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_shortcut,"shortcut",2,"",$shortcut)?>
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품별매출</caption>
			<colgroup>
				<col style="width:15%">
				<col style="width:35%">
				<col style="width:15%">
				<col style="width:35%">
			</colgroup>
			<tr>
				<th scope="row">상품등록일별</th>
				<td>
					<input type="text" name="dates" value="<?=$dates?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="datee" value="<?=$datee?>" size="10" readonly class="input datepicker">
				</td>
				<th scope="row">판매기간별</th>
				<td>
					<input type="text" name="sdates" value="<?=$sdates?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="sdatee" value="<?=$sdatee?>" size="10" readonly class="input datepicker">
				</td>
			</tr>
			<tr>
				<th scope="row">매장분류</th>
				<td colspan="3">
					<select name="big" onchange="chgCateInfinite(this, 2, '');">
						<option value="">::대분류::</option>
						<?=$item_1_1?>
					</select>
					<select name="mid" onchange="chgCateInfinite(this, 3, '');">
						<option value="">::중분류::</option>
						<?=$item_1_2?>
					</select>
					<select name="small" onchange="chgCateInfinite(this, 4, '');">
						<option value="">::소분류::</option>
						<?=$item_1_3?>
					</select>
					<?if($cfg['max_cate_depth'] >= 4) {?>
					<select name="depth4">
						<option value="">::세분류::</option>
						<?=$item_1_4?>
					</select>
					<?}?>
				</td>
			</tr>
			<tr>
				<th scope="row">기획전</th>
				<td colspan="3">
					<select name="cno">
						<option value="">:: 기획전 ::</option>
						<?=$item_2?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">주문상태</th>
				<td colspan="3">
					<ul class="list_common inline">
						<?=$_search_ostat_1?>
					</ul>
					<ul class="list_common inline">
						<?=$_search_ostat_2?>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">상품상태</th>
				<td colspan="3">
					<ul class="list_common inline">
						<?=$search_pstat?>
					</ul>
				</td>
			</tr>
			<tr>
				<?if($cfg['use_partner_shop'] == 'Y' && $sadmin != 'Y') {?>
				<th>입점파트너</th>
				<td>
					<?=selectArray($_partners, 'partner_no', null, '전체', $partner_no)?>
				</td>
				<?}?>
				<th scope="row">사입처</th>
				<td>
					<select name="seller">
						<option value="">:: 사입처 ::</option>
						<?=$_seller?>
					</select>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다
	<span class="box_btn_s btns icon excel"><a href="./?body=income@income_product_excel.exe<?=$xls_query?>">엑셀다운</a></span>
</div>
<?if($search != 'true') {?>
<div class="box_bottom top_line">
	<ul class="list_msg left">
		<li>기간과 검색조건을 선택한 후, 검색을 누르세요.</li>
		<li><span class="p_color2">등록기간</span>이나 <span class="p_color2">판매기간</span> 둘 중 하나는 반드시 입력 되어야 합니다.</li>
		<li>지나치게 큰 날짜 조건으로 검색 할 경우 결과 출력이 늦어지거나 사이트 전체 속도가 저하될 수 있으므로 주의 해 주시기 바랍니다.</li>
	</ul>
</div>
<?return;}?>
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">출력갯수</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
				<option value="200" <?=checked($row,200,1)?>>200</option>
				<option value="500" <?=checked($row,500,1)?>>500</option>
				<option value="1000" <?=checked($row,1000,1)?>>1000</option>
			</select>
			위까지
			<select name="group" onchange="location.href='<?=$qs_without_group?>&group='+this.value">
				<option value="prd" <?=checked($group, 'prd', true)?>>상품별</option>
				<option value="opt" <?=checked($group, 'opt', true)?>>옵션별</option>
			</select>
			출력
		</dd>
	</dl>
</div>
<div class="box_middle">
	<ul class="list_info left">
		<li>조회수는  기간검색과  상관없이 전체 기간의 조회수를 나타납니다.</li>
		<li>소비자가 및 판매가격은 현재 등록되어있는 가격을 기준으로 하며, 실판매가는 판매 당시의 할인을 적용한 실제 판매가격의 합이 출력됩니다.</li>
		<li>상품별 출력 시에만 일괄 기획전적용이 지원됩니다.</li>
		<li>옵션별 출력의 경우 <span class="warning">재고관리 상품</span>만 지원됩니다.</li>
	</ul>
</div>
<form id="incomeFrm" name="incomeFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">상품별매출 리스트</caption>
	<colgroup>
		<?if($group=="prd") {?>
		<col style="width:40px">
		<?}?>
		<col style="width:60px">
		<col style="width:80px">
		<col>
		<col style="width:150px">
		<col style="width:80px">
		<col style="width:80px">
		<col style="width:80px">
		<col style="width:80px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<?if($group=="prd") {?>
			<th scope="col"><input type="checkbox" onclick="checkAll(document.incomeFrm.check_pno,this.checked)"></th>
			<?}?>
			<th scope="col">순번</th>
			<th scope="col">상품상태</th>
			<th scope="col"><a href="<?=$sort1?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></th>
			<th scope="col">사입처/장기명</th>
			<th scope="col"><a href="<?=$sort6?>"><?=$cfg['product_normal_price_name']?> <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir6?>.gif" class="arrow <?=$arrowcolor6?>"></a></th>
			<th scope="col"><a href="<?=$sort5?>"><?=$cfg['product_sell_price_name']?> <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir5?>.gif" class="arrow <?=$arrowcolor5?>"></a></th>
			<th scope="col"><a href="<?=$sort7?>">조회 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir7?>.gif" class="arrow <?=$arrowcolor7?>"></a></th>
			<th scope="col"><a href="<?=$sort2?>">주문 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
			<th scope="col"><a href="<?=$sort3?>">실판매가 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
			<th scope="col"><a href="<?=$sort4?>">등록일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
		</tr>
	</thead>
	<tbody>
		<?
			$idx = 0;
            foreach ($res as $data) {
				$idx++;
				$data['name'] = stripslashes(strip_tags($data['name']));
				$data['origin_name'] = stripslashes(strip_tags($data['origin_name']));
				$data['reg_date'] = date('Y-m-d', $data['reg_date']);

				$shortcut = '';
				if($data['wm_sc2'] > 0) {
					$shortcut = "<img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>";
				}

				$file_dir = getFileDir($data['updir']);

				if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
					$is = setImageSize($data['w3'], $data['h3'], 50, 50);
					$data['imgstr'] = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
				}

				$cstr = makeCategoryName($data, 1);

		?>
		<tr>
			<?if($group=="prd") {?>
			<td>
				<input type="hidden" name="pno[]" value="<?=$data[no]?>">
				<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>">
			</td>
			<?}?>
			<td><?=$idx?></td>
			<td><?=$_prd_stat[$data['stat']]?></td>
			<td class="left">
				<div class="box_setup">
					<div class="thumb"><a href="/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$data['imgstr']?></a></div>
					<dl>
						<dt class="title"><a href="./?body=product@product_register&pno=<?=$data['no']?>&listURL=<?=$listURL?>" target="_blank"><?=$shortcut?> <?=$data['name']?></a></dt>
						<?if($group == 'opt') {?>
						<dd><?=parseOrderOption($data['option'])?></dd>
						<?}?>
						<dd class="cstr"><?=$cstr?></dd>
					</dl>
					<div class="clear">
						<span class="box_btn_s"><a href="./?body=product@product_register&pno=<?=$data['no']?>&listURL=<?=$listURL?>">상품수정</a></span>
						<?if($sadmin != 'Y'){?><span class="box_btn_s"><a href="#" onclick="edtEbig(<?=$data['no']?>); return false;">기획전수정</a></span><?}?>
						<span class="box_btn_s"><a href="?body=income@income_product_detail&pno=<?=$data['no']?>&ref=income" target="_blank">개별상품판매분석</a></span>
					</div>
				</div>
			</td>
			<td class="left">
				<ul class="list_msg">
					<li><?=$data['seller']?></li>
					<li class="p_color2"><?=$data['origin_name']?></li>
				</ul>
			</td>
			<td><?=number_format($data['normal_prc'])?></td>
			<td><?=number_format($data['sell_prc'])?></td>
			<td><?=number_format($data['hit_view'])?></td>
			<td><?=number_format($data['amount'])?></td>
			<td><?=number_format($data['price'])?></td>
			<td><?=$data['reg_date']?></td>
		</tr>
		<?}?>
	</tbody>
</table>
</form>
<?if($sadmin != 'Y') {?>
<div id="controlTab">
	<ul class="tabs">
		<li id="ctab_1" onclick="tabSH(1)" class="selected">기획전적용</li>
	</ul>
	<div class="context">
		<!-- 기획전적용 -->
		<form id="edt_layer_1" method="post" action="./" target="hidden<?=$now?>" onsubmit="return edtConfirm(this)">
			<input type="hidden" name="body" value="product@product_update.exe">
			<input type="hidden" name="w" value="<?=$w?>">
			<input type="hidden" name="nums" value="">
			<input type="hidden" name="exec" value="toEbig">
			<input type="hidden" name="ebig_mode" value="add">
			<input type="hidden" name="income_use" value="Y">
			<input type="hidden" name="cq" value="<?=$cq?>">
			<input type="hidden" name="g" value="<?=$g?>">
			<input type="hidden" name="ord" value="<?=$ord?>">
			<input type="hidden" name="row" value="<?=$row?>">
			<input type="hidden" name="prd_field" value="<?=$prd_field?>">
			<div class="box_middle3 left">
				<select name="where">
					<option value="1">선택한 상품을</option>
					<option value="2" id="search_product">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)을</option>
				</select>
				선택한 기획전에 추가하기
			</div>
			<table class="tbl_row tbl_row2" id="imagecopy">
				<colgroup>
					<col style="width:10%;">
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
						<?}?>
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
						<?}?>
						</td>
					</tr>
					<tr>
						<th scope="row">선택 항목</th>
						<td>
							<label class="p_cursor">
								<input type="radio" name="ebig_first" value="Y" checked>기존 배열된 상품을 유지한 상태에서 최상위에 배열합니다.
							</label><br>
							<label class="p_cursor">
								<input type="radio" name="ebig_first" value="N">기존 배열된 상품을 유지한 상태에서 최하위에 배열합니다.
							</label><br>
							<label class="p_cursor">
								<input type="radio" name="ebig_first" value="D">기존 배열된 상품을 모두 삭제 한 후 배열합니다.
							</label>
							<div class="list_info tp">
								<p>이미 기획전에 포함된 상품은 배열순서 변동이 발생되지 않습니다.</p>
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
	</div>
</div>
<?}?>
<script type="text/javascript">
	var tprd = document.getElementById('total_prd');
	var tprd2 = document.getElementById('search_product');
	if(tprd) tprd.innerHTML = '<?=$idx?>';
	if(tprd2) tprd2.innerHTML = '현재 검색된 모든 상품(<?=$idx?>개)을';

	var esearch = new layerWindow('product@product_edtEbig.exe');
	function edtEbig(pno) {
		esearch.open('&pno='+pno);
	}

	window.onload=function (){
		ebig_check();
	}

	function ebig_check() {
		var group_val = $("select[name='group'] option:selected").val();
		if(group_val=='prd') {
			$('#controlTab').show();
		}else {
			$('#controlTab').hide();
		}
	}

	var pf = document.getElementById('incomeFrm');

	function numSelect(pf) {
		var nums = '';
		if(pf.check_pno.length) {
			for(i=0; i < pf.check_pno.length; i++) {
				if(pf.check_pno[i].checked==true) nums += '@'+pf.check_pno[i].value;
			}
		}else if(pf.check_pno && pf.check_pno.checked==true) {
			nums = '@'+pf.check_pno.value;
		}
		return nums;
	}

	function edtConfirm(f) {
		f.nums.value = '';
		if(f.where.selectedIndex==0) {
			if(!checkCB(pf.check_pno,"변경할 상품을 선택해주세요.")) return false;
			f.nums.value = numSelect(pf);
		}

		if($(':checked[name="ebig[]"]').length==0 && $(':checked[name="mbig[]"]').length==0) {
			alert('선택된 기획전이 없습니다.');
			return false;
		}

		//선택된 기획전이 없습니다
		if(!confirm('변경사항을 적용하시겠습니까?')) {
			return false;
		}
		printLoading();
	}
</script>