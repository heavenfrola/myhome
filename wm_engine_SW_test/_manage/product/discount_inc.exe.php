<?PHP

	$pno = numberOnly($_REQUEST['pno']);
	$field = addslashes($_REQUEST['field']);

	if($_REQUEST['exec'] == 'regist') {
		Header('Content-type:application/json; charset='._BASE_CHARSET_);

		$data = array(
			'sale_type' => $_POST['sale_type'][0],
			'data' => array()
		);
		foreach($_POST['sale_ea'] as $key => $_ea) {
			$_rate = $_POST['sale_rate'][$key];
			if($_rate == 0) continue;

			if(isset($_prev_ea) == true) {
				if($_ea <= $_prev_ea) exit(json_encode(array('status'=>'error', 'message'=>'하단에 입력한 수량이 상단에 입력한 수량보다 커야 합니다.')));
				if($_rate <= $_prev_rate) exit(json_encode(array('status'=>'error', 'message'=>'하단에 입력한 할인율이 상단에 입력한 할인율보다 커야 합니다.')));
			}
			$data['data'][$_ea] = $_rate;

			$_prev_ea = $_ea;
			$_prev_rate = $_rate;
		}
		$res = (count($data['data']) > 0) ? json_encode($data) : '';

		addField($tbl['product'], $field, 'varchar(200) not null default "" after sell_prc');
		$pdo->query("update {$tbl['product']} set `$field`='$res' where no='$pno'");

		header('Content-type:application/json;');
		exit(json_encode(array(
			'status' => 'success',
			'field' => $field,
			'count' => count($data['data'])
		)));;
	}

	printAjaxHeader();

	$data = $pdo->row("select $field from {$tbl['product']} where no='$pno'");
	$data = json_decode($data);
	if(count($data) < 1) {
		$data = (object)array('sale_type' => 'p', 'data' => array('' => ''));
	}

	switch($field) {
		case 'qty_rate' :
			$title = '수량할인';
			$add_caption = '개 이상 구매 시';
		break;
		case 'set_rate' :
			$title = '담을수록 할인';
			$add_caption = '개 구매 시';
		break;
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop"><?=$title?></div>
	</div>
	<form id="setDiscountFrm">
		<input type="hidden" name="body" value="product@discount_inc.exe">
		<input type="hidden" name="exec" value="regist">
		<input type="hidden" name="pno" value="<?=$pno?>">
		<input type="hidden" name="field" value="<?=$field?>">

		<div id="popupContentArea">
			<table class="tbl_col">
				<caption class="hidden"><?=$title?></caption>
				<colgroup>
					<col>
					<col style="width:15%">
				</colgroup>
				<thead>
					<th>할인목록</th>
					<th>삭제</th>
				</thead>
				<tbody class="discountLine">
					<?php foreach ($data->data as $_ea => $_rate) { ?>
					<tr>
						<td class="left">
							<input type="text" name="sale_ea[]" class="input" size="4" value="<?=$_ea?>"> <?=$add_caption?>
							<input type="text" name="sale_rate[]" class="input" size="4" value="<?=$_rate?>">
							<select name="sale_type[]">
								<option value="p" <?=checked($data->sale_type, 'p', true)?>>%</option>
								<option value="m" <?=checked($data->sale_type, 'm', true)?>><?=$cfg['currency_type']?></option>
							</select>
							할인
						</td>
						<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="setDiscount.remove(this)"></span></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php if ($field == 'qty_rate') { ?>
			<div class="box_middle2 left">
				<ul class="list_info">
					<li>'<?=$cfg['currency_type']?>'단위 할인 시 상품 개당 할인가를 입력해 주세요.</li>
				</ul>
			</div>
			<?php } ?>
			<div class="box_middle2 left">
				<span class="box_btn blue"><input type="button" value="추가" onclick="setDiscount.add();"></span>
			</div>
		</div>
		<div class="pop_bottom">
			<span class="box_btn_s blue"><input type="button" value="확인" onclick="setDiscount.submit();"></span>
			<span class="box_btn_s gray"><input type="button" value="닫기" onclick="setDiscount.close();"></span>
		</div>
	</form>
</div>