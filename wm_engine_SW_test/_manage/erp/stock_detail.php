<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$pno = numberOnly($_GET['pno']);
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	if(!$pno) msg('존재하지 않는 코드입니다.');

	$kind = array('I' => '입고', 'U' => '조정입고', 'O' => '출고', 'P' => '조정출고');

	// 목록수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$afield = '';
	if($cfg['use_partner_shop'] == 'Y') {
		$afield = ', a.partner_no';
	}

	$sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc $afield " .
		   "      ,(select name from wm_category x where a.big = x.no) as big" .
		   "      ,(select name from wm_category x where a.mid = x.no) as mid" .
		   "      ,(select name from wm_category x where a.small = x.no) as small" .
		   "      , b.barcode, b.opts " .
		   "      , b.force_soldout, curr_stock(complex_no) as current_qty " .
		   "  from wm_product a inner join erp_complex_option b on b.pno=a.no" .
		   " where b.del_yn='N' and b.complex_no = '$pno'";

	$prod = $pdo->assoc($sql);

	if($admin['level'] == 4) {
		if($prod['partner_no'] != $admin['partner_no']) msg('권한이 없는 데이터입니다.', 'back');
	}

	$productname = ($prod['wm_sc']) ? $prod['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $prod['name'];

	// 이미지 파일명
	$file_dir = getFileDir($prod['updir']);
	if($prod['upfile3']) {
		$is = setImageSize($prod['w3'], $prod['h3'], 50, 50);
		$imgstr = "<img src='$file_dir/{$prod['updir']}/{$prod['upfile3']}' $is[2]>";
	}
	$category_name = $prod['big'];
	if($prod['mid']) $category_name .= $cate_sprit.$prod['mid'];
	if($prod['small']) $category_name .= $cate_sprit.$prod['small'];

	$NumTotalRec = $pdo->row("select count(1) from erp_inout where complex_no = {$pno}");

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block=10;

	foreach($_GET as $key=>$val) {
		if($key!="page") {
			if($key == "listURL")
				$QueryString.="&".$key."=".urlencode($val);
			else
				$QueryString.="&".$key."=".$val;
		}
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql = "select reg_date, inout_kind, qty, (select name from `$tbl[mng]` b where a.reg_user = b.admin_id) as reg_user, remark" .
		   "     , (select sum(if(inout_kind in ('I', 'U'), x.qty, -x.qty)) from erp_inout x where complex_no=a.complex_no and inout_no <= a.inout_no) as cqty" .
		   "  from erp_inout a" .
		   " where a.complex_no=$pno" .
		   " order by inout_no desc";

	$sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<!-- 검색 폼 -->
<form id="stockFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="erp@stock_adjust.exe">
	<input type="hidden" name="exec" value="single">
	<input type="hidden" name="complex_no" value="<?=$pno?>">

	<div class="box_title first">
		<h2 class="title">상품 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 정보</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">상품명</th>
			<td>
				<div class="box_setup">
					<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prod['hash']?>" target="_blank"><?=$imgstr?></a></div>
					<dl>
						<dt class="title"><a href="./?body=product@product_register&pno=<?=$prod['no']?>" target="_blank"><?=$productname?></a></dt>
						<dd class="cstr"><?=$category_name?></dd>
					</dl>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">옵션명</th>
			<td><?=getComplexOptionName($prod['opts'])?></td>
		</tr>
		<tr>
			<th scope="row">재고코드</th>
			<td><?=$prod['barcode']?></td>
		</tr>
		<tr>
			<th scope="row">현재고</th>
			<td>
				<?=selectArray($_erp_force_stat, 'force_soldout', false, null, $prod['force_soldout'])?>
				<input type="text" name="qty" class="input right" size="5" value="<?=$prod['current_qty']?>"> 개
				<div style="padding-top: 5px;">
					<input type="text" name="remark" class="input input_full" placeholder="조정사유">
				</div>
				<ul class="list_msg">
					<li>주문이나 관리자 작업에 의해 실시간으로 재고가 변경되고 있을수 있습니다. 재고 변경시 유의 해주시기 바랍니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<form id="search" name="prdSearchFrm" style="margin:0;">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 재고이력이 검색되었습니다.
		<span class="box_btn_s btns"><a href="./?body=erp@stock_detail_excel.exe&pno=<?=$pno?>">엑셀다운</a></span>
	</div>
</form>
<!-- //검색 총합 -->
<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			내역수
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
<!-- //정렬 -->
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<table class="tbl_col">
		<caption class="hidden">상품 정보 리스트</caption>
		<colgroup>
			<col style="width:120px">
			<col style="width:90px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:80px">
			<col>
			<col>
		</colgroup>
		<tr>
			<th scope="col">처리일시</th>
			<th scope="col">입·출고</th>
			<th scope="col">처리수량</th>
			<th scope="col">누적재고</th>
			<th scope="col">변경자</th>
			<th scope="col">조정사유</th>
		</tr>
		<?php
        foreach ($res as $data) {
        ?>
		<tr>
			<td><?=$data['reg_date'] == "1900-01-01 00:00:00" ? "기초재고 등록" : substr($data['reg_date'], 0, 16)?></td>
			<td><?=$kind[$data['inout_kind']]?></td>
			<td style='color:<?=$data['inout_kind'] == "I" || $data['inout_kind'] == "U" ? "blue;'>+" : "red;'>-"?><?=number_format($data['qty'])?></td>
			<td><?=number_format($data['cqty'])?></td>
			<td><?=$data['reg_user']?></td>
			<td class="left"><?=$data['remark']?></td>
		</tr>
		<?php } ?>
	</table>
	<div class="box_bottom">
		<div class="paging"><?=$pageRes?></div>
		<?php if ($_GET['ref']) { ?>
			<div class="center">
				<span class="box_btn"><input type="button" value="닫기" onclick="self.close();"></span>
			</div>
		<?php } else { ?>
			<div class="left_area">
				<span class="box_btn"><input type="button" value="목록" onclick="location.href='<?=urldecode($_GET['listURL'])?>'"></span>
			</div>
		<?php } ?>
	</div>
</form>
<script type="text/javascript">
const f = document.getElementById('stockFrm');
const def_qty = '<?=$prod['current_qty']?>';
const def_soldout = '<?=$prod['force_soldout']?>';

$(f.qty).focus(function() {this.select()});

$(f).submit(function() {
	if(this.qty.value == def_qty && this.force_soldout.value == def_soldout) {
        removeLoading();
		window.alert('변경된 내역이 없습니다.');
		return false;
	}
	if(!this.remark.value) {
        removeLoading();
		window.alert('조정사유를 입력해주세요.');
		this.remark.focus();
		return false;
	}
	return true;
});
</script>