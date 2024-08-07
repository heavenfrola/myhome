<?PHP

	// 발주추가 처리
	if($_POST['exec'] == 'process') {
		$order_no = $_POST['order_no'];
		$_complex_no = $_POST['complex_no'];
		$_sno = $_POST['sno'];
		$_qty = $_POST['qty'];
		$_price = $_POST['price'];
		$remark = addslashes(trim($_POST['remark']));

		if(count($_complex_no) == 0) msg('발주할 상품을 선택해 주세요.');

		$pdo->query("START TRANSACTION;");

		foreach($_complex_no as $key => $val) {
			$complex_no = $val;
			$sno = numberOnly($_sno[$key]);
			$qty = numberOnly($_qty[$key]);
			$price = parsePrice($_price[$key]);
			$amt = $price*$qty;

			$data = $pdo->assoc("select * from erp_complex_option where complex_no='$complex_no'");
			$chek = $pdo->row("select count(*) from erp_order_dtl where order_no='$order_no' and complex_no='$complex_no'");
			$ordr = $pdo->assoc("select * from erp_order where order_no='$order_no'");

			if(!$ordr['order_no']) $err = '존재하지 않는 발주서입니다.';
			if(!$data['complex_no']) $err = '존재하지 않는 상품입니다.';
			if(!$sno) $err = '사입처가 지정되지 않은 상품입니다.\n사입처가 있는 상품만 발주 가능합니다.';
			if($chek > 0) $err = '현재 발주서에 이미 동일한 상품이 발주상태입니다.\n수량변경을 원하실 경우 발주수정 또는 일괄수정 기능을 이용해 주십시오.';
			if($qty < 1) $err = '발주할 수량을 1개 이상 입력해 주십시오.';
			if($ordr['order_stat'] > 2) $err = '발주서가 입고완료되었거나 마감된 상태입니다.\n발주 또는 부분입고 상태인 발주서에서만 발주추가가 가능합니다.';

			if($err) {
				$pdo->query("ROLLBACK");
				msg($err);
			}

			$pdo->query("insert into erp_order_dtl(order_no, sno, complex_no, order_target_qty, order_qty, order_price, remark) values ('$order_no', '$sno', '$complex_no', '$qty', '$qty', '$price', '$remark')");
			$pdo->query("update erp_order set total_qty = total_qty+$qty, total_amt=total_amt+$amt where order_no='$order_no'");
		}

		$pdo->query("COMMIT");

		msg('', 'reload', 'parent');
	}

	include $engine_dir."/_engine/include/paging.php";

	// 다이얼로그 출력
	printAjaxHeader();

	$barcode = $_GET['barcode'];
	if($barcode) {
		$barcodes = implode(',', array_map(
			function($el) {
				return "'".trim($el)."'";
			},
			explode(',', $barcode)
		));
		$rows = $pdo->row("select count(*) from erp_complex_option where barcode in ($barcodes) and del_yn='N'");
		if($rows > 0) {
			$res = $pdo->iterator("select a.name, a.updir, a.upfile3, a.seller_idx, a.origin_prc, b.provider, c.complex_no, c.pno, c.opts from erp_complex_option c inner join $tbl[product] a on c.pno=a.no inner join $tbl[provider] b on a.seller_idx=b.no where c.barcode in ($barcodes) and del_yn='N'");
		} else {
			$QueryString = '';
			foreach($_GET as $key => $val) {
				if($key != 'page' && $val) {
					$QueryString .= "&$key=".urlencode($val);
				}
			}

			$sql = "select a.*, b.complex_no, b.barcode, b.opts from $tbl[product] a inner join erp_complex_option b on a.no=b.pno where b.del_yn='N' and a.name like '%$barcode%' and a.seller_idx>0";

			$page = numberOnly($_GET['page']);
			if($page <= 1) $page=1;
			$NumTotalRec = $pdo->row("select count(*) from $tbl[product] a inner join erp_complex_option b on a.no=b.pno where b.del_yn='N' and a.name like '%$barcode%' and a.seller_idx>0");
			$PagingInstance = new Paging($NumTotalRec, $page, 5, 10);
			$PagingInstance->addQueryString($QueryString);
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql .= $PagingResult['LimitQuery'];
			$res = $pdo->iterator($sql);

			$pg_res = $PagingResult['PageLink'];
			$pg_res = preg_replace('/href="([^"]+)"/', 'href="#" onclick="psearch.open(\'$1\'); return false"', $pg_res);
		}
	}

	function parseSKU($res) {
		global $pdo;

		$prd = $res->current();
        $res->next();
		if($prd == false) return false;

		$prd['name'] = stripslashes($prd['name']);
		$prd['provider'] = stripslashes($prd['provider']);
		$prd['option_name'] = stripslashes(getComplexOptionName($prd['opts']));

		$prd['order_prc'] = $pdo->row("select order_price from erp_order_dtl where complex_no=$data[complex_no] order by order_dtl_no desc limit 1");
		if(!$prd['order_prc']) $prd['order_prc'] = $prd['origin_prc'];
		$prd['order_prc'] = parsePrice($prd['order_prc']);

		return $prd;
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">발주추가</div>
	</div>
	<div id="popupContentArea">
		<form id="search" class="addFrm" onsubmit="return psearch.fsubmit(this);">
			<input type="hidden" name="body" value="<?=$_GET['body']?>">
			<input type="hidden" name="order_no" value="<?=$_GET['order_no']?>">
			<table class="tbl_row">
				<caption class="hidden">검색</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">검색</th>
					<td>
						<ul class="list_msg">
							<li>추가 발주하실 바코드를 입력해 주세요.</li>
							<li>사입처가 설정되지 않은 상품음 검색되지 않습니다.</li>
						</ul>
						<input type="text" name="barcode" class="input" size="20" value="<?=inputText($barcode)?>">
						<span class="box_btn_s blue"><input type="submit" value="검색"></span>
					</td>
				</tr>
			</table>
		</form>
		<?if($NumTotalRec > 0) {?>
		<table class="tbl_col">
			<caption class="hidden">검색 결과 리스트</caption>
			<colgroup>
				<col style="width:50px">
				<col span="3">
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><input type="checkbox" onclick="$('.barcd').prop('checked', this.checked)"></th>
					<th scope="col">상품명</th>
					<th scope="col">옵션</th>
					<th scope="col">바코드</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $data) {
						$imgstr = '';
						$file_dir = getFileDir($data[updir]);
						if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
							$is = setImageSize($data['w3'], $data['h3'], 50, 50);
							$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
						}
						$productname = stripslashes($data['name']);
				?>
				<tr>
					<td><input type="checkbox" name="cd[]" class="barcd" value="<?=$data['barcode']?>"></td>
					<td class="left">
						<div class="box_setup" style="padding-right: 0;">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$imgstr?></a></div>
							<p class="title">
								<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$productname?></a>
							</p>
						</div>
					</td>
					<td><?=getComplexOptionName($data['opts'])?></td>
					<td><?=$data['barcode']?></td>
				</tr>
				<?}?>
			</tbody>
		</table>
		<div class="box_bottom">
			<?=$pg_res?>
		</div>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="button" value="상품선택" onclick="selectBarcode()"></span>
			<span class="box_btn gray"><input type="button" value="창닫기" onclick="psearch.close()"></span>
		</div>
		<?} else if($res) {?>
		<form class="contentFrm" method="post" action="./" target="<?=$_GET['hid_frame']?>">
			<input type="hidden" name="body" value="erp@order_add.exe">
			<input type="hidden" name="exec" value="process">
			<input type="hidden" name="order_no" value="<?=$_GET['order_no']?>">
			<table class="tbl_col">
				<caption class="hidden">상품정보</caption>
				<colgroup>
					<col>
					<col style="width:80px">
					<col style="width:130px">
				</colgroup>
				<thead>
					<tr>
						<th scope="col">상품정보</th>
						<th scope="col">발주수량</th>
						<th scope="col">발주금액</th>
					</tr>
				</thead>
				<tbody>
					<?while($prd = parseSKU($res)) {?>
					<tr>
						<td class="left">
							<input type="hidden" name="complex_no[]" value="<?=$prd['complex_no']?>">
							<input type="hidden" name="sno[]" value="<?=$prd['seller_idx']?>">

							<div class="box_setup">
								<?if($prd['upfile3']) {?>
								<div class="thumb"><img src="<?=getFileDir($prd['updir'])?>/<?=$prd['updir']?>/<?=$prd['upfile3']?>" style="width:40px;"></div>
								<?}?>
								<p class="title"><a href="?body=product@product_register&pno=<?=$prd['pno']?>" target="_blank"><?=$prd['name']?></a></p>
								<p class="cstr"><?=$prd['option_name']?></p>
								<p class="func"><a href="?body=product@provider_register&no=<?=$prd['seller_idx']?>" target="_blank"><?=$prd['provider']?></a></p>
							</div>
						</td>
						<td><input type="text" name="qty[]" class="input" size="4" value="1"></td>
						<td>
							<div class="input_money">
								<input type="text" name="price[]" value="<?=$prd['order_prc']?>" class="input right" size="6">
								<span><?=$cfg['currency']?></span>
							</div>
						</td>
					</tr>
					<?}?>
				</tbody>
			</table>
			<div class="box_middle2 right">
				<input type="text" name="remark" class="input input_full" style="width: 100%;" placeholder="비고">
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="발주추가"></span>
				<span class="box_btn gray"><input type="button" value="창닫기" onclick="psearch.close()"></span>
			</div>
			<?}?>
		</form>
	</div>
</div>
<script type="text/javascript">
	function selectBarcode() {
		var barcode = '';
		$('.barcd:checked').each(function() {
			if(barcode) barcode += ','
			barcode += this.value;
		});
		$('input[name=barcode]').val(barcode);
		$('.addFrm').submit();
	}

	setTimeout(function() {
		$(':input[name=barcode]').select();
	}, 100);

	$("input[name='qty[]'], input[name='price[]']").focus(function() {
		this.select();
	});
</script>