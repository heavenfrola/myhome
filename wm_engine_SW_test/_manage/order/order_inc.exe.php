<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문검색
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$search_key = addslashes($_GET['search_key']);
	$search_str = mb_convert_encoding(addslashes(trim($_GET['search_str'])), _BASE_CHARSET_, array('utf8', 'euckr'));
	if($search_str && in_array($search_key, array('buyer_name', 'buyer_cell', 'buyer_phone', 'ono', 'member_id'))) {
		$w .= " and `$search_key` like '%$search_str%'";
	}


	$sql = "select no, member_id, ono, title, pay_prc, addressee_zip, buyer_name, buyer_phone, buyer_cell, buyer_email, addressee_name , addressee_phone, addressee_cell, addressee_addr1, addressee_addr2, date1, pay_type from $tbl[order] where stat not in (11, 31, 32, 33) $w  order by date1 desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$NumTotalRec = $pdo->row("select count(*) from {$tbl['order']} where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, 10, 10);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$group=getGroupName();

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="osearch.open(\'$1\')"', $pg_res);

	include_once $engine_dir.'/_manage/manage.lib.php';

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">주문검색</div>
	</div>
	<div id="popupContentArea">
		<div class="box_title first">
			<form id="search" onsubmit="return osearch.fsubmit(this);">
				<input type="hidden" name="body" value="<?=$_GET['body']?>">
				<select name="search_key">
					<option value="buyer_name" <?=checked($search_key, 'buyer_name', 1)?>>주문자명</option>
					<option value="buyer_cell" <?=checked($search_key, 'buyer_cell', 1)?>>주문자전화</option>
					<option value="buyer_phone" <?=checked($search_key, 'buyer_phone', 1)?>>주문자휴대폰</option>
					<option value="ono" <?=checked($search_key, 'ono', 1)?>>주문번호</option>
					<option value="member_id" <?=checked($search_key, 'member_id', 1)?>>회원아이디</option>
				</select>
				<input type="text" name="search_str" class="input" size="20" value="<?=inputText($search_str)?>">
				<span class="box_btn gray"><input type="submit" id="searchBtn" value="검색"></span>
			</form>
		</div>
		<table class="tbl_col">
			<caption class="hidden">주문검색</caption>
			<colgroup>
				<col style="width:19%">
				<col style="width:15%">
				<col style="width:15%">
				<col>
				<col style="width:15%">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">주문번호</th>
					<th scope="col">주문자</th>
					<th scope="col">연락처</th>
					<th scope="col">배송주소</th>
					<th scope="col">주문일</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $data) {
						$data = array_map('stripslashes', $data);
						$data['date1'] = date('y-m-d H:i', $data['date1']);
						$data['addressee_addr1'] = stripslashes($data['addressee_addr1']);
						$data['addressee_addr2'] = stripslashes($data['addressee_addr2']);
						$data['cash_reg_num'] = $pdo->row("select cash_reg_num from {$tbl['cash_receipt']} where ono='{$data['ono']}' order by no desc limit 1");
						if(empty($data['cash_reg_num'])) $data['cash_reg_num'] = $data['buyer_cell'];

						if (strpos($_SERVER['HTTP_REFERER'], 'order_cash_receipt_sub')) {
						    $href = "./?body=order@order_cash_receipt_sub&ono=".$data['ono'];
                        } else {
                            $json  = "{";
                            $json .= "'ono':'$data[ono]',";
                            $json .= "'title':'".addslashes(strip_tags($data['title']))."',";
                            $json .= "'member_id':'$data[member_id]',";
                            $json .= "'name':'".addslashes(inputtext($data['buyer_name']))."',";
                            $json .= "'cell':'$data[buyer_cell]',";
                            $json .= "'phone':'$data[buyer_phone]',";
                            $json .= "'email':'$data[buyer_email]',";
                            $json .= "'zip':'$data[addressee_zip]',";
                            $json .= "'addr_name':'".addslashes(inputtext($data['addressee_name']))."',";
                            $json .= "'addr_cell':'$data[addressee_cell]',";
                            $json .= "'addr_phone':'$data[addressee_phone]',";
                            $json .= "'addr1':'".addslashes(inputtext($data['addressee_addr1']))."',";
                            $json .= "'addr2':'".addslashes(inputtext($data['addressee_addr2']))."',";
                            $json .= "'reg_cell_num':'{$data['cash_reg_num']}',";
                            $json .= "'pay_prc':'".parsePrice($data['pay_prc'])."',";
                            $json .= "'pay_type':'{$data['pay_type']}'";
                            $json .= "}";
                            $href = "javascript:osearch.msel(".$json.");";
                        }
				?>
				<tr>
					<td><a href="<?=$href?>"><strong><?=$data['ono']?></strong></a></td>
					<td><?=$data['buyer_name']?><br><?=$data['member_id']?></td>
					<td><?=$data['buyer_phone']?><br><?=$data['buyer_cell']?></td>
					<td class="left"><?=$data['addressee_addr1']?><br><?=$data['addressee_addr2']?></td>
					<td><?=$data['date1']?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="osearch.close()"></span>
	</div>
</div>