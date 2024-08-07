<?PHP

	if($cfg['erp_order'] == '') $cfg['erp_order'] = 1;
	if($cfg['labelPrinter'] == '') $cfg['labelPrinter'] = 1;
	if($cfg['labelLineHeight'] == '') $cfg['labelLineHeight'] = 80;
	if($cfg['topmargin'] == '') $cfg['topmargin'] = 9;
	if($cfg['leftmargin'] == '') $cfg['leftmargin'] = 25;
	if(!$cfg['erp_auto_hold']) $cfg['erp_auto_hold'] = 'N';
	if(!$cfg['erp_auto_release']) $cfg['erp_auto_release'] = 'N';
	if(!$cfg['erp_force_limit']) $cfg['erp_force_limit'] = 'N';
    if(!$cfg['use_erp_transaction']) $cfg['use_erp_transaction'] = 'N';
    $scfg->def('use_erp_transaction', 'N');

    // 트랜젝션 지원여부
    $pdo->query('start transaction');
    $disable_transaction = ($pdo->geterror()) ? 'disabled' : '';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="erp">
	<div class="box_title first">
		<h2 class="title">재고설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">재고설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">재고감산설정</th>
			<td>
				<ul>
					<li><label><input type="radio" name="erp_timing" value="1" <?=checked($cfg['erp_timing'],'1')?>> 주문접수 시 재고 감산합니다.</label></li>
					<li>
						<label><input type="radio" name="erp_timing" value="2" <?=checked($cfg['erp_timing'],'2')?>> 주문상태를 <?=$_order_stat[2]?>상태로 변경 시 재고 감산합니다.</label></li>
						<p class="explain" style="margin-left: 15px">└ 재고가 부족한 한정상품이 자동입금확인 될 경우 주문상태가 '<?=$_order_stat[2]?>'가 아닌 '<u><?=$_order_stat[20]?></u>'상태로 변경되며, 재고가 차감되지 않습니다.</p>
					</li>
					<li><label><input type="radio" name="erp_timing" value="3" <?=checked($cfg['erp_timing'],'3')?>> 주문상태가 <?=$_order_stat[3]?>상태로 변경 시 재고 감산합니다.</label></li>
					<li><label><input type="radio" name="erp_timing" value="4" <?=checked($cfg['erp_timing'],'4')?>> 주문상태가 <?=$_order_stat[4]?>상태로 변경 시 재고 감산합니다.</label></li>
					<li><label><input type="radio" name="erp_timing" value="5" <?=checked($cfg['erp_timing'],'5')?>> 주문상태가 <?=$_order_stat[5]?>상태로 변경 시 재고 감산합니다.</label></li>
				</ul>
				<ul class="list_msg">
					<li><span class="p_color2">쇼핑몰 운영 중 설정을 변경</span>하시면 변경 이전 처리된 주문의 재고가 틀려질수 있습니다.</li>
					<li>재고가 여러번 차감되거나 차감되지 않을수도 있으며 취소시에도 재고 복구에 이상이 발생하게 되므로 신중하게 처리해 주십시오.</li>
				</ul>
			</td>
		</tr>
        <tr>
            <th>중복차감 금지</th>
            <td>
                <label>
                    <label><input type="radio" name="use_erp_transaction" value="N" <?=checked($cfg['use_erp_transaction'], 'N')?>> 사용안함</label><br>
                    <label><input type="radio" name="use_erp_transaction" value="Y" <?=checked($cfg['use_erp_transaction'], 'Y')?> <?=$disable_transaction?>></label>
                    대량 주문 발생 시 재고가 중복차감되지 않도록 처리합니다.
                </label>
                <ul class="list_info">
                    <li>더 많은 서버 자원이 필요하여, 수용 가능한 최대 접속자 수가 감소할 수 있습니다.</li>
                    <li>카드 및 간편결제 도중 재고가 마감될 경우 자동으로 결제를 취소합니다.</li>
                </ul>
            </td>
        </tr>
		<tr>
			<th scope="row">재고복구설정</td>
			<td>
				<?
					$undo = explode(',', $cfg['erp_stock_undo']);
					for($i = 12; $i <= 19; $i++) {
						$chk = (in_array($i, $undo)) ? 'checked' : '';
				?>
				<label class="p_cursor"><input type="checkbox" name="erp_stock_undo[]" value="<?=$i?>" <?=$chk?>> <?=$_order_stat[$i]?></label>
				<?}?>
				<input type="hidden" name="erp_stock_undo[]" value="y">
				<ul class="list_msg">
					<li>
						체크 한 상태로 변경될 때 재고가 복구(증가)되며, 체크를 해제하시면 해당 상태로 변경될 때 재고가 복구되지 않습니다.
						<a href="javascript:;" onclick="layTgl($('#example'),'fast');">자세히</a>
					</li>
					<li>
						ex) <?=$_order_stat[2]?> 시 재고감산, <?=$_order_stat[12]?>이 미체크, <?=$_order_stat[13]?>가 체크 된 경우
						<ul id="example" style="display: none;">
							<li><?=$_order_stat[2]?>에서  <?=$_order_stat[12]?>상태로 변경 시 : <span class="p_color2">재고변경 안됨</span></li>
							<li><?=$_order_stat[2]?>에서  <?=$_order_stat[13]?>상태로 변경 시 : <span class="p_color2">재고 복구</span></li>
							<li><?=$_order_stat[12]?>에서 <?=$_order_stat[13]?>상태로 변경 시 : <span class="p_color2">재고 복구</span></li>
							<li><?=$_order_stat[13]?>에서 <?=$_order_stat[12]?>상태로 변경 시 : <span class="p_color2">재고 감산</span></li>
							<li><?=$_order_stat[12]?>에서 <?=$_order_stat[12]?>상태로 변경 시 : <span class="p_color2">재고변경 안됨</span></li>
						</ul>
					</li>
					<li><span class="p_color2">쇼핑몰 운영 중 설정을 변경</span>하시면 변경 이전 처리된 주문의 재고가 틀려질수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr class="optDlvHold">
			<th scope="row">자동 배송보류</th>
			<td>
				<label><input type="radio" name="erp_auto_hold" value="N" <?=checked($cfg['erp_auto_hold'], 'N')?>> 사용안함</label><br>
				<label><input type="radio" name="erp_auto_hold" value="Y" <?=checked($cfg['erp_auto_hold'], 'Y')?>> 재고가 0개 이하인 '무제한'옵션 주문시 주문상품을 배송보류로 처리 합니다.</label>
				<ul class="list_msg">
					<li>수동 상태변경시에는 자동 배송보류가 되지 않으며, 고객 주문시에만 설정이 반영됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr class="optDlvHold">
			<th scope="row">자동 배송보류 해제</th>
			<td>
				<label><input type="radio" name="erp_auto_release" value="N" <?=checked($cfg['erp_auto_release'], 'N')?>> 사용안함</label><br>
				<label><input type="radio" name="erp_auto_release" value="Y" <?=checked($cfg['erp_auto_release'], 'Y')?>> 상품이 입고되면 입고 수량에 따라 주문상품의 배송보류가 자동 해제됩니다.</label>
				<ul class="list_msg">
					<li>건별 입고, 발주 입고, 일괄발주서 입고시 반영됩니다.</li>
					<li>주문 취소로 인한 재고 발생시에도 보류가 해제되나 해당 주문상품이 보류 상태일때는 보류 해제가 발생하지 않습니다.</li>
				</ul>
			</td>
		</tr>
		<tr class="optDlvHold">
			<th scope="row">조정출고시 자동 배송보류</th>
			<td>
				<label><input type="radio" name="erp_auto_output_hold" value="N" <?=checked($cfg['erp_auto_output_hold'], 'N')?>> 사용안함</label><br>
				<label><input type="radio" name="erp_auto_output_hold" value="Y" <?=checked($cfg['erp_auto_output_hold'], 'Y')?>> 상품이 조정출고되면 출고 수량에 따라 주문 상품이 자동으로 배송보류 됩니다.</label>
				<ul class="list_msg">
					<li>재고 조정, 일괄재고 조정, 바코드 재고조정, 건별 출고처리 시 재고가 차감될 경우 반영됩니다.</li>
					<li>주문 시간이 늦은 주문부터 먼저 배송보류 됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">무제한 바코드 한계 재고 설정</th>
			<td>
				<label><input type="radio" name="erp_force_limit" value="N" <?=checked($cfg['erp_force_limit'], 'N')?>> 사용안함</label><br>
				<label><input type="radio" name="erp_force_limit" value="Y" <?=checked($cfg['erp_force_limit'], 'Y')?>> 사용함</label>
				<ul class="list_msg">
					<li>보유재고 이상으로 주문 가능한 무제한 바코드의 최대 판매 한계(마이너스 재고)를 설정합니다.</li>
					<li>고객 주문시에만 체크되며 관리자에의한 교환등은 제한 없이 처리 가능합니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">바코드설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">바코드설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">바코드 형식</th>
			<td class="barcode">
				<div>
					<label class="p_cursor"><input type="radio" name="barcode_type" value="1" onclick="barcodePreview()" <?=checked($cfg['barcode_type'], 1)?> checked> 년월일-랜덤코드 7자리</label><br>
					<label class="p_cursor"><input type="radio" name="barcode_type" value="2" onclick="barcodePreview()" <?=checked($cfg['barcode_type'], 2)?>> 월일-랜덤코드 11자리</label><br>
					<label class="p_cursor"><input type="radio" name="barcode_type" value="3" onclick="barcodePreview()" <?=checked($cfg['barcode_type'], 3)?>> 랜덤코드 16자리</label>
				</div>
				<img id="preview" src="?body=erp@barcode.exe&text=">
			</td>
		</tr>
		<tr>
			<th scope="row">프린터종류</th>
			<td>
				<select name="labelPrinter">
					<option value="1" <?=checked($cfg['labelPrinter'], 1, 1)?>>전용 라벨프린터 (1x2)</option>
					<option value="2" <?=checked($cfg['labelPrinter'], 2, 1)?>>전용 라벨프린터 (1x1)</option>
					<optioN value="3" <?=checked($cfg['labelPrinter'], 3, 1)?>>일반 라벨용지 (3줄)</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">상단 여백</th>
			<td><input type="text" name="topmargin" size="5" value="<?=$cfg[topmargin]?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">좌측 여백</th>
			<td><input type="text" name="leftmargin" size="5" value="<?=$cfg[leftmargin]?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">라벨 줄 간격</th>
			<td>
				<input type="text" name="labelLineHeight" size="5" value="<?=$cfg[labelLineHeight]?>" class="input">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function barcodePreview() {
		var code = $(':checked[name=barcode_type]').val();
		var sample = Array('<?=date('Ymd')?>-a42bd', '<?=date('md')?>-32bdf305c', '6ef4b5642cbd6f');
		$('#preview').attr('src', '?body=erp@barcode.exe&text='+sample[code-1]);
	}

	function chgErpTiming() {
		var erpTiming = $(':checked[name=erp_timing]').val();
		if(erpTiming == '1') {
			$('.optDlvHold').show();
		} else {
			$('.optDlvHold').hide();
		}
	}
	$(':radio[name=erp_timing]').change(chgErpTiming);

	$(document).ready(function() {
		barcodePreview();
		chgErpTiming();
	});
</script>