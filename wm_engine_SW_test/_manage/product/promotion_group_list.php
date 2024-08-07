<?PHP

	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name = editSkinName();

	if(!isTable($tbl['promotion_link'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['promotion_link']);
		$pdo->query($tbl_schema['promotion_list']);
		$pdo->query($tbl_schema['promotion_pgrp_link']);
		$pdo->query($tbl_schema['promotion_pgrp_list']);
	}

	printAjaxHeader();

	$_search_type['promotion_nm'] = '프로모션 기획전명';
	$_search_type['pgrp_nm'] = '프로모션 상품그룹명';

?>
<?if(!is_file($root_dir."/_skin/".$_skin_name."/CORE/shop_promotion.wsr")) {?>
<div class="msg_topbar sub quad warning">
	사용 중인 스킨 내 프로모션 기획전 페이지 작업유무를 확인해주세요.<br><br>
	프로모션 기획전 및 프로모션 상품그룹 관리를 사용하기 위해서는<br>
	반드시 사용중인 스킨 내 프로모션 기획전 페이지가 존재해야 합니다.<br><br>
	<strong>[PC 쇼핑몰]</strong> <a href="?body=design@editor&type=&edit_pg=4%2F10" target="_blank" class="list_move">바로가기</a>
	<strong>[모바일 쇼핑몰]</strong> <a href="?body=wmb@editor&type=mobile&edit_pg=4%2F10" target="_blank" class="list_move">바로가기</a>
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?}?>
<form name="prmSearchFrm" id="prmSearchFrm" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">프로모션 상품그룹 관리</h2>
	</div>
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
					<div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn('prmSearchFrm', $_COOKIE['prd_detail_search_on'])?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'prd_promotion_search_on');" <?=checked($_COOKIE['prd_promotion_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">진행상태</th>
				<td>
					<label class="p_cursor"><input type="radio" name="stat" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="stat" value="2" <?=checked($stat, 2)?>> 대기</label>
					<label class="p_cursor"><input type="radio" name="stat" value="3" <?=checked($stat, 3)?>> 진행중</label>
					<label class="p_cursor"><input type="radio" name="stat" value="4" <?=checked($stat, 4)?>> 종료</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>

<form id="prmlFrm" name="prmlFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="margin-top:35px">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<?
		$delete_btn = "Y";
		include_once $engine_dir."/_manage/product/promotion_group_call.exe.php";
	?>
	<div class="box_middle2 left">
		<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="groupSelectDelete(document.prmlFrm)"></span>
		<!-- <span class="box_btn gray"><input type="button" value="전체 삭제" class="groupSelectDelete(document.prmlFrm, 'all')"></span> -->
		<div class="right_area">
			<span class="box_btn blue"><input type="button" value="프로모션 상품그룹 등록" onclick="searchGroup(this,'','Y')"></span>
		</div>
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function() {
		$(':checkbox[name="check_pgrpno\[\]"], :checkbox.list_check').click(function() {
			if($(this).is(':checked') == true) $(this).parents('tr').addClass('checked');
			else $(this).parents('tr').removeClass('checked');
		});
	});
    var pgsearch = new layerWindow('product@promotion_group.frm');
	function searchGroup(obj, pgrp_no, list) {
		var sparam = '';

		if(list) sparam += '?list_yn=Y';
		if(pgrp_no) sparam += '?pgrp_no='+pgrp_no;

		pgsearch.input = obj;
		pgsearch.open(sparam, {"name":"pop1", "topmargin":+30, "leftmargin":-250});
	}
	pgsearch.pcan = function(opno) {
		var prdArray = [];
		var f = document.prmgFrm;
		var pno = f.pno.value;
		var gno = $('#gno').val();
		if(pno) {
			_pno = pno.split("|");
			_pno.forEach(function(val) {
				if(opno != val) {
					prdArray.push(val);
				}
			})
			f.pno.value = prdArray.join("|");
			$.get('?body=product@promotion_group.exe', {"pno":prdArray}, function(data) {
				$('#sort_list').html(data);
			})
			$.get('?body=product@promotion_product.exe', {"exec":"product_delete", "gno":gno, "pno":opno}, function(data) {
				$('#prm_sort #'+opno).remove();
			})
		}
	}
	function groupSelectDelete(f, type) {
		if(type!='all') {
			if(!checkCB(f.check_pgrpno,"삭제할 프로모션 상품그룹을 선택해주세요.")) return false;
			if(!confirm("프로모션 상품그룹을 삭제하시겠습니까?")) return false;
		}

		f.body.value="product@promotion_register.exe";
		f.exec.value = (type == 'all') ? 'all_delete' : 'delete';
		f.method='post';
		f.target=hid_frame;
		f.submit();
	}
</script>