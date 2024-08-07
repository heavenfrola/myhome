<?PHP

	$_search_type['name']='사은품명';
	$_search_type['content']='설명';

	$w = " and `delete` != 'Y'";

	// 검색어
	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}

	$price_limit_s = numberOnly($_GET['price_limit_s']);
	$price_limit_f = numberOnly($_GET['price_limit_f']);
	$use = $_GET['use'];
	if($price_limit_s && $price_limit_f) $w.=" and `price_limit` between '{$price_limit_s}' and '{$price_limit_f}'";
	if($use) $w.=" and `use`='{$use}'";

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block = 10;

	$ono = addslashes(trim($_GET['ono']));
	$order_gift=$pdo->row("select `order_gift` from `$tbl[order]` where `ono`='$ono'");
    $gift_temp_arr = explode('@', str_replace('_', '@', $order_gift)); //문자 치환 후 @기준으로 배열생성
    $gift_temp_arr = array_filter($gift_temp_arr); //빈값, 0, false 제거
    $exclude_order_gift = implode(',', $gift_temp_arr); //쉼표 문자열로 합침
	if($exclude_order_gift) $w.=" and `no` not in ($exclude_order_gift)";

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[product_gift]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$pageRes = $PagingResult['PageLink'];

	$list_sql = "select * from $tbl[product_gift] where 1 $w order by no desc ".$PagingResult['LimitQuery'];
	$res = $pdo->iterator($list_sql);
	$listURL = urlencode(getURL());

?>
<style type="text/css" title="">
body {background:none;}
</style>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="ono" value="<?=$ono?>">
	<input type="hidden" name="sort" value="">
	<div class="box_title first">
		<h3 class="title">사은품 추가</h3>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품추가</caption>
		<colgroup>
			<col style="width:20%;">
			<col style="width:30%;">
			<col style="width:20%;">
			<col style="width:30%;">
		</colgroup>
		<tr>
			<th scope="row">증정조건</th>
			<td colspan="3">
				<input type="text" name="price_limit_s" value="<?=$price_limit_s?>" class="input" style="width:100px"> ~
				<input type="text" name="price_limit_f" value="<?=$price_limit_f?>" class="input" style="width:100px">
			</td>
		</tr>
		<tr>
			<th scope="row">사용여부</th>
			<td colspan="3">
				<label class="p_cursor"><input type="radio" name="use" value="" <?=checked($use, '')?>> 전체</label>
				<label class="p_cursor"><input type="radio" name="use" value="Y" <?=checked($use, 'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="use" value="N" <?=checked($use, 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&ono=<?=$ono?>'"></span>
	</div>
</form>
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다.
</div>
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			사은품수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="order@order_add_gift.exe">
	<input type="hidden" name="ono" value="<?=$ono?>">
	<input type="hidden" name="no" value="">
	<table class="tbl_col tbl_col_bottom">
		<colgroup>
			<col style="width:70px;">
			<col>
			<col style="width:120px;">
			<col style="width:75px;">
			<col style="width:75px;">
			<col style="width:100px;">
			<col style="width:70px;">
		</colgroup>
		<thead>
			<tr>
				<th colspan="2">사은품명</th>
				<th>증정조건</th>
				<th>차감포인트</th>
				<th>사용여부</th>
				<th>등록일</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$imgstr = '';
					if($data['upfile'] && is_file($root_dir."/".$data['updir']."/".$data['upfile'])) $imgstr = "<img src='$root_url/{$data[updir]}/{$data[upfile]}' width='50px'>";
					$data['use_str']=($data['use'] == 'Y')  ? '사용' : '사용안함';
					$data[name] = stripslashes(strip_tags($data['name']));
			?>
			<tr>
				<td><?=$imgstr?></td>
				<td class="left"><?=$data['name']?></td>
				<td><?=number_format($data['price_limit'])?>원 이상구매</td>
				<td><?=number_format($data['point_limit'])?></td>
				<td><?=$data['use_str']?></td>
				<td><?=date('Y.m.d', $data['reg_date'])?></td>
				<td><span class="box_btn_s blue"><input type="button" value="추가" onclick="selectAddGift(<?=$data['no']?>)"></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="pop_bottom"><?=$pageRes?></div>
</form>

<script type="text/javascript">
	function selectAddGift(no) {
		if(!opener || opener.closed) {
			window.alert('주문상세 윈도우를 찾을 수 없습니다.\n창을 닫고 다시 처리 해 주십시오.');
			return;
		}
		var f=document.prdFrm;
		f.no.value=no;
		f.submit();
	}
</script>