<?PHP

	printAjaxHeader();

	$_search_type['name'] = '상품명';
	$_search_type['keyword'] = '검색 키워드';
	$_search_type['code'] = '상품 코드';
	$_search_type['origin_name'] = '장기명';
	$_search_type['seller'] = '사입처';

	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	$start_date = preg_replace('/[^0-9-]/', '', $_GET['start_date']);
	$finish_date = preg_replace('/[^0-9-]/', '', $_GET['finish_date']);
	if(!$start_date || !$finish_date) $all_date = "Y";
	if(!$start_date && !$finish_date) {
		$start_date =  date('Y-m-d', strtotime('-15 days'));
		$finish_date = date("Y-m-d", $now);
	}
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

	foreach($_cate_colname as $key => $val) {
		foreach($val as $key2 => $val2) {
			${$val2} = numberOnly($_GET[$val2]);
		}
	}

?>
<div id="popupContent" class="popupContent layerPop pop2 w1500">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">상품선택</div>
	</div>
	<div id="popupContentArea" class="promotion_product_frm nopd">
		<form name="prmprdFrm2" method="get" target="hidden<?=$now?>" id="prmprdFrm2" class="search">
			<div class="box_title first">
				<h2 class="title">상품선택</h2>
			</div>
            <div class="layerscroll">
			<table class="tbl_row">
				<caption class="hidden">프로모션 상품그룹 등록</caption>
				<colgroup>
					<col style="width:150px">
					<col>
				</colgroup>
				<tr>
					<th scope="row">검색</th>
					<td>
						<?=selectArray($_search_type,"search_type",2,"",$_GET['search_type'])?>
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</td>
				</tr>
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
						<?if($cfg['max_cate_depth'] >= 4) {?>
						<select name="depth4">
							<option value="">::세분류::</option>
							<?=$item_1_4?>
						</select>
						<?}?>
					</td>
				</tr>
				<?if($cfg['xbig_mng'] == "Y"){?>
				<tr>
					<th scope="row"><?=$cfg['xbig_name']?> 매장분류</th>
					<td>
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
						<?if($cfg['max_cate_depth'] >= 4) {?>
						<select name="xdepth4">
							<option value="">::세분류::</option>
							<?=$item_4_4?>
						</select>
						<?}?>
					</td>
				</tr>
				<?}?>
				<?if($cfg['ybig_mng'] == "Y"){?>
				<tr class='close'>
					<th scope="row"><?=$cfg['ybig_name']?> 매장분류</th>
					<td>
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
						<?if($cfg['max_cate_depth'] >= 4) {?>
						<select name="ydepth4">
							<option value="">::세분류::</option>
							<?=$item_5_4?>
						</select>
						<?}?>
					</td>
				</tr>
				<?}?>
				<tr class="close">
					<th scope="row">상품 등록일</th>
					<td>
						<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
						<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					</td>
				</tr>
				<tr class="close">
					<th scope="row">상품가격</th>
					<td>
						<input type="text" name="prd_prc_s" size="10" value="<?=$prd_prc_s?>" class="input"> ~ <input type="text" name="prd_prc_f" size="10" value="<?=$prd_prc_f?>" class="input">
					</td>
				</tr>
				<tr class="close">
					<th scope="row">구매후기</th>
					<td>
						<input type="radio" name="review" value="" checked> 전체
						<input type="radio" name="review" value="Y"> 후기많은순
					</td>
				</tr>
				<tr class="close">
					<th scope="row">상태</th>
					<td>
						<label class="p_cursor"><input type="radio" name="stat" value="" checked> 전체</label>
						<label class="p_cursor"><input type="radio" name="stat" value="2" <?=checked($stat, 2)?>> 정상</label>
						<label class="p_cursor"><input type="radio" name="stat" value="3" <?=checked($stat, 3)?>> 품절</label>
						<label class="p_cursor"><input type="radio" name="stat" value="4" <?=checked($stat, 4)?>> 숨김</label>
					</td>
				</tr>
			</table>
			<div class="box_middle3">
				<span class="box_btn blue"><input type="button" value="검색" onclick="prm_prd_submit()"></span>
				<span class="box_btn"><input type="button" value="초기화" onclick="prm_prd_reset()"></span>
				<span class="box_btn"><input type="button" id="detail_show" value="상세접기" onclick="detail_close()"></span>
			</div>
			<div id="product_list">
				<?include_once $engine_dir."/_manage/product/promotion_product_list.exe.php";?>
			</div>
            </div>
		</form>
		<div class="add_except">
			<span class="add"><input type="button" value="추가" onclick="add_products();"></span>
			<span class="except"><input type="button" value="제외" onclick="remove_products();"></span>
		</div>
		<form name="prmprdFrm" method="get" target="hidden<?=$now?>" id="prmprdFrm" class="prd_join">
			<div class="box_title first">
				<h2 class="title">상품등록리스트</h2>
				<div class="total">
					<dl class="list">
						<dt class="hidden">정렬</dt>
						<dd>
							<select name="orderby" onchange="prm_prd_ordby(this.value);">
								<option value="" <?=checked($orderby,'')?>>::정렬선택::</option>
								<option value="1" <?=checked($orderby,1)?>>높은가격순</option>
								<option value="2" <?=checked($orderby,2)?>>낮은가격순</option>
								<option value="3" <?=checked($orderby,3)?>>판매량높은순</option>
								<option value="4" <?=checked($orderby,4)?>>판매량낮은순</option>
								<option value="5" <?=checked($orderby,5)?>>조회수높은순</option>
								<option value="6" <?=checked($orderby,6)?>>조회수낮은순</option>
							</select>
						</dd>
					</dl>
				</div>
			</div>
			<div class="limit_box layerscroll">
				<table id="prm_product_list" name="prm_product_list" class="tbl_col">
					<input type="hidden" id="pgrp_no" name="pgrp_no" value="<?=$pgrp_no?>">
					<caption class="hidden">상품검색</caption>
					<colgroup>
                        <col style="width:50px">
						<col style="width:80px">
                        <col style="width:150px">
						<col style="width:80px">
						<col style="width:80px">
						<col style="width:80px">
                        <col style="width:80px">
					</colgroup>
					<thead>
						<tr>
                            <th scope="col"><input type="checkbox" id="cball_prd_add" onclick="checkAll($('.cb_prd_add'),this.checked)"></th>
                            <!-- 상품등록리스트 전체체크 체크박스 생성 -->
							<th scope="col">순번</th>
							<th scope="col">상품</th>
							<th scope="col">가격</th>
							<th scope="col">적립금</th>
							<th scope="col">상태</th>
							<th scope="col">제외</th>
						</tr>
					</thead>
					<tbody id="confirm_prd">
						<?include_once $engine_dir."/_manage/product/promotion_product_add.exe.php";?>
					</tbody>
				</table>
			</div>
			<div class="box_bottom">
				<ul class="list_btn_move">
					<li><span class="btn_move last_h"><input type="button" name="" value="마지막" onclick="srt5.toBottom();"></span></li>
					<li><span class="btn_move next_h"><input type="button" name="" value="다음" onclick="listsort2('plus');"></span></li>
					<li><span class="btn_move prev_h"><input type="button" name="" value="이전" onclick="listsort2('minus');"></span></li>
					<li><span class="btn_move first_h"><input type="button" name="" value="처음" onclick="srt5.toTop();"></span></li>
				</ul>
				<span class="ea"><input type="text" id="step2" name="step2" value="1" class="input" size="1"> 칸 이동</span>
			</div>
		</form>
	</div>
	<div class="pop_bottom">
		<span class="box_btn blue"><input type="button" onclick="prm_prd_confirm()" value="선택완료"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="prdsearch.close({'name':'pop2'})"></span>
	</div>
</div>
<script type="text/javascript">
	searchDate(document.prmprdFrm2);
	$("#ui-datepicker-div").remove();
	setDatepicker();

    $('.close').hide();
    $('#detail_show').val('상세열기');

    $('.layerscroll').height($('.layerscroll').height());

	var srt4 = null;
	var srt5 = null;
	$(function() {
		srt4 = new Sorttbl('prm_product_search');
		srt5 = new Sorttbl('prm_product_list');
	});
	function listsort2(type) {
		var step2 = $('#step2').val();
		if(type=='plus') {
			srt5.move(+(step2));
		}else {
			srt5.move(-(step2));
		}
	}
	function detail_close() {
		if($('.close').css('display') == 'none') {
			$('.close').show();
			$('#detail_show').val('상세접기');
		}else {
			$('.close').hide();
			$('#detail_show').val('상세열기');
		}
	}
	function prm_prd_submit(row, page) {
		var fdata = $("form[name=prmprdFrm]").serialize();
		var fdata2 = $("form[name=prmprdFrm2]").serialize();
		var frow = '';

		if(row) frow += '&row='+row;
		if(page) frow += '&page='+page;

		$.ajax({
			type : 'GET',
			url : './?body=product@promotion_product_list.exe',
			data: fdata+'&'+fdata2+'&'+frow,
			dataType : 'html',
			success : function(result) {
				$("#product_list").html(result);
				srt4 = new Sorttbl('prm_product_search');
			}
		});
	}
	function prm_prd_reset() {
		$.get('?body=product@promotion_product_list.exe', function(data) {
			$('#product_list').html(data);
		})
	}
	function add_products() {
		var prdArray = [];
		$('#prm_product_search tr.checked').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			prdArray.push(pno);
		});
		if(prdArray.length>0) prdsearch.psel(prdArray, "Y");
	}
	function remove_products() {
		$('#prm_product_list tr.checked').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			prdsearch.pcan(pno);
		});
	}
	function prm_prd_confirm() {
		var prdArray = [];
		var _pno = '';
		$('#prm_product_list tr').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			prdArray.push(pno);
			_pno += '|'+pno;
		});

		$('#prm_sort').find('#pno').val(_pno);

		$.get('?body=product@promotion_group.exe', {"pno":prdArray}, function(data) {
			$('#sort_list').html(data);
		})

		prdsearch.close({"name":"pop2"});
	}

	function prm_prd_ordby(ordby) {
		var prdArray = [];
		$('#prm_product_list tr').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			prdArray.push(pno);
		});

		$.get('?body=product@promotion_product_add.exe', {"pno":prdArray,"orderby":ordby}, function(data) {
			$('#confirm_prd').html(data);
			srt5 = new Sorttbl('prm_product_list');
		})
	}

</script>