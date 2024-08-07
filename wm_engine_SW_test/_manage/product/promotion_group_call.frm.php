<?PHP

	printAjaxHeader();


	$_search_type['promotion_nm'] = '프로모션 기획전명';
	$_search_type['pgrp_nm'] = '프로모션 상품그룹명';

?>
<div id="popupContent" class="popupContent layerPop" style="width:900px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">프로모션 상품그룹 불러오기</div>
	</div>
	<div id="popupContentArea">
		<form name="callFrm" id="callFrm" method="get" target="hidden<?=$now?>" style="margin:0 auto; position:relative;">
			<input type="hidden" name="nums" value="">
			<div class="left_reg">
				<div class="box_title_reg first">
					<h2 class="title">프로모션 기획전검색</h2>
				</div>
				<table class="tbl_row">
					<caption class="hidden">프로모션 기획전검색</caption>
					<colgroup>
						<col style="width:30%">
						<col>
					</colgroup>
					<tr>
						<th scope="row">검색</th>
						<td>
							<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
							<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
						</td>
					</tr>
					<tr class='close'>
						<th scope="row">진행상태</th>
						<td>
							<label class="p_cursor"><input type="radio" name="stat" value="" checked> 전체</label>
							<label class="p_cursor"><input type="radio" name="stat" value="2" <?=checked($_GET['stat'], "2")?>> 대기</label>
							<label class="p_cursor"><input type="radio" name="stat" value="3" <?=checked($_GET['stat'], "3")?>> 진행중</label>
							<label class="p_cursor"><input type="radio" name="stat" value="4" <?=checked($_GET['stat'], "4")?>> 완료</label>
						</td>
					</tr>
				</table>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="검색" onclick="prm_prd_search()"></span>
					<span class="box_btn"><input type="button" value="초기화" onclick="prm_call_reset()"></span>
				</div>
				<div id="call_list">
					<?include_once $engine_dir."/_manage/product/promotion_group_call.exe.php";?>
				</div>
			</div>
			<div class="pop_bottom">
				<span class="box_btn blue"><input type="button" value="선택적용" onclick="call_submit(document.callFrm);"></span>
				<span class="box_btn gray"><input type="button" value="취소" onclick="pgcall.close()"></span>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(':checkbox[name="check_pgrpno\[\]"], :checkbox.list_check').click(function() {
			if($(this).is(':checked') == true) $(this).parents('tr').addClass('checked');
			else $(this).parents('tr').removeClass('checked');
		});
	});
	function prm_prd_search(row, page) {
		var fdata = $("form[name=callFrm]").serialize();
		var frow = '';

		if(row) frow += '&row='+row;
		if(page) frow += '&page='+page;

		$.ajax({
			type : 'GET',
			url : './?body=product@promotion_group_call.exe',
			data: fdata+frow,
			dataType : 'html',
			success : function(result) {
				$("#call_list").html(result);

			}
		});
	}
	var srt2 = null;
	function call_submit(f) {		
		if(!checkCB(f.check_pgrpno,"불러올 상품그룹을 선택해주세요.")) return false;

		var gprp_no = [];
		if (f.check_pgrpno.length) {
			for (i=0; i < f.check_pgrpno.length; i++) {
				if (f.check_pgrpno[i].checked==true) gprp_no.push(f.check_pgrpno[i].value);
			}
		} else if (f.check_pgrpno && f.check_pgrpno.checked==true) {
			gprp_no.push(f.check_pgrpno.value);
		}
		var overlap_chk = false;
		gprp_no.forEach(function(val) {
			if(val) {
				if($('#prd_add_list tr').size()>0) {
					$('#prd_add_list tr').each(function() {
						var pgrp_no2 = $(this).attr('id');
						if(pgrp_no2 && pgrp_no2==val) {
							overlap_chk = true;
						}
					})
				}
			}
		})
		if(overlap_chk==true) {
			alert('동일한 상품그룹이 존재합니다. 제외 후 다시 시도해주세요.');
		}
		gprp_no.forEach(function(val) {
			if(overlap_chk==false) {
				$.get('?body=product@promotion_register_add.exe', {"pgrp_no":val}, function(data) {
					$('#prd_add_list').append(data);
					srt2 = new Sorttbl('groupFrm');
					$("#ui-datepicker-div").remove();
					setDatepicker();
				})
			}
		})
		pgcall.close();
	}
	function prm_call_reset() {
		location.reload();
	}
</script>