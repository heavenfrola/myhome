<?PHP

	include_once $engine_dir.'/_manage/erp/order_list_search.inc.php';

	foreach($_GET as $key=>$val) {
		if($key != 'page') $qs .= '&'.$key.'='.$val;
	}
	$xls_query = preg_replace('/&body=[^&]+/', '', $qs);
	$xls_query = str_replace('&body='.$pgCode, '', $xls_query);
	$xls_query = preg_replace('/&?exec=[^&]+/', '', $xls_query);

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	for($i = 1; $i <= 4; $i++) {
		$var1 = ($i-1)*2;
		$var2 = $var1+1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	$NumTotalRec = $pdo->row("select count(*) from erp_order a where 1 = 1".$w);
	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block = 10;

	if(!$QueryString) $QueryString = '&body='.$_GET['body'];
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$list_sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];

	$res = $pdo->iterator($list_sql);

	setListURL('?body='.$body);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<!-- 검색 폼 -->
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h2 class="title">발주 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">발주 정보</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">발주일자</th>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" readonly class="input datepicker"> ~
				<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" readonly class="input datepicker">
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
			</td>
		</tr>
		<tr>
			<th scope="row">발주상태</th>
			<td><?=selectArray($stat,"order_stat",2,"::전체::",$order_stat)?></td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray(array("order_no"=>"발주번호"),"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 발주가 검색되었습니다
	<div class="btns">
		<span class="box_btn_s icon excel"><a href="./?body=erp@order_list_dexcel.exe<?=$xls_query?>&set=in">입고처리용 엑셀다운</a></span>
		<span class="box_btn_s icon excel"><a href="./?body=erp@order_list_dexcel.exe<?=$xls_query?>">엑셀다운</a></span>
	</div>
</div>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			발주수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row, 10, 1)?>>10</option>
				<option value="20" <?=checked($row, 20, 1)?>>20</option>
				<option value="30" <?=checked($row, 30, 1)?>>30</option>
				<option value="50" <?=checked($row, 50, 1)?>>50</option>
				<option value="70" <?=checked($row, 70, 1)?>>70</option>
				<option value="100" <?=checked($row, 100, 1)?>>100</option>
			</select>
		</dd>
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<!-- //정렬 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_close.exe">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption class="hidden">발주 정보 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:120px">
			<col style="width:120px">
			<col>
			<col style="width:100px">
			<col style="width:80px">
			<col style="width:100px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAllLocal(this.checked)"></th>
				<th scope="col"><a href="<?=$sort1?>">발주번호 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></th>
				<th scope="col"><a href="<?=$sort2?>">발주일자 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
				<th scope="col"><a href="<?=$sort3?>">사입처 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col"><a href="<?=$sort4?>">발주상태 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="col">발주상품수</th>
				<th scope="col">총발주금액</th>
				<th scope="col">발주서 입고</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td>
					<input type="checkbox" name="check_ono[]" id="check_ono" value="<?=$data['order_no']?>" <?=$data['order_stat']=="3" || $data['order_stat']=="5" ? " disabled" : ""?>>
				</td>
				<td><a href="./?body=erp@order_detail&ono=<?=$data['order_no']?>" class="p_color"><?=$data['order_no']?></a></td>
				<td><?=$data['order_date']?></td>
				<td class="left"><?=$data['provider']?></td>
				<td><?=$stat[$data['order_stat']]?></td>
				<td><?=number_format($data['total_qty'])?></td>
				<td><?=number_format($data['total_amt'])?></td>
				<td>
					<?if($data['order_stat'] < 3) {?>
					<span class="box_btn_s blue"><input type="button" value="입고" id="<?=$data['order_no']?>"></span>
					<?} else {?>
					-
					<?}?>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn gray"><input type="button" value="발주취소" onclick="orderClose();"></span>
		</div>
		<?=$pageRes?>
	</div>
</form>

<script type="text/javascript">
	searchDate(document.getElementById('search'));

	function checkAllLocal(ck){
		var obj = document.prdFrm.check_ono;
		if(!obj) return false;

		if(obj.length) {
			for(i=0; i<obj.length; i++) {
				if(obj[i].disabled == false) obj[i].checked=ck;
			}
		} else if(obj) {
			if(obj.disabled == false) obj.checked=ck;
		}
	}

	function orderClose() {
		var check_ono = document.getElementsByName("check_ono[]");
		var isChk = true;
		for(var i=0; i<check_ono.length; i++) {
			if(check_ono[i].checked) {
				isChk = false;
				break;
			}
		}
		if(isChk) {
			alert('선택한 발주건이 없습니다.');
			return;
		}

		if(!confirm("선택한 발주를 취소처리하시겠습니까?")) return;
		prdFrm.submit();
	}

	function dtlExcel() {
		var ono = '';
		var check_ono = document.getElementsByName('check_ono');
		for(var i = 0; i < check_ono.length; i++) {
			if(check_ono[i].checked == true) {
				if(ono != '') ono += ','+check_ono[i].value;
				else ono = check_ono[i].value;
			}
		}

		var f = window.frames[hid_frame];
		f.location.href = '.?body=erp@order_list_dexcel.exe<?=$xls_query?>';
	}

	$('input[value=입고]').click(function() {
		location.href = '?body=erp@order_in&order_no='+this.id;
	});
</script>