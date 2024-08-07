<?PHP

	if($cfg['use_partner_shop'] == 'Y') msg('입점몰 기능 사용시 이용이 불가능한 기능입니다.');

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품금액별 할인설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!isTable($tbl['order_config_prdprc'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['order_config_prdprc']);
	}

	$res = $pdo->iterator("select * from $tbl[order_config_prdprc] order by prd_prc desc");
	if(!$cfg['prdprc_sale_use']) $cfg['prdprc_sale_use'] = 'N';

?>
<form id="prcFrm" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@order_prdprc.exe">
	<div class="box_title first">
		<h2 class="title">주문상품금액 설정</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">주문상품금액 설정</caption>
		<colgroup>
			<col style="width:80px">
			<col>
			<col style="width:300px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">순번</th>
				<th scope="col">상품금액 범위</th>
				<th scope="col">할인율</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
            <?php foreach ($res as $data) {?>
			<tr>
				<td>
					<?=++$idx?>
					<input type="hidden" name="no[]" value="<?=$data['no']?>">
				</td>
				<td class="left">
					<input type="text" name="prd_prc[]" class="input right" size="10" value="<?=$data['prd_prc']?>"> <?=$cfg['currency']?>
					<?if($last > 0){?>
					이상 ~ <?=number_format($last)?> <?=$cfg['currency']?> 미만 구매시
					<?} else {?>
					이상 구매시
					<?}?>
				</td>
				<td>
					<input type="text" name="per[]" class="input right" size="10" value="<?=$data['per']?>">
					<select name="unit[]">
						<option value="p" <?=checked($data['unit'], "p", 1)?> >%</option>
						<option value="m" <?=checked($data['unit'], "m", 1)?> ><?=$cfg['currency']?></option>
					</select>
					할인
				</td>
				<td>
					<span class="box_btn_s"><input type="button" value="삭제" onclick="delPrdPrc(<?=$data['no']?>)"></span>
				</td>
			</tr>
			<?$last = $data['prd_prc'];}?>
			<tr>
				<td>추가</td>
				<td class="left"><input type="text" name="prd_prc[]" class="input right" size="10"> <?=$cfg['currency']?> 이상 구매 시</td>
				<td>
					<input type="text" name="per[]" class="input right" size="10">
					<select name="unit[]">
						<option value="p">%</option>
						<option value="m">원</option>
					</select>
					할인
				</td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<p class="left">%로 할인 시 이벤트/회원/쿠폰으로 할인되기 전의 상품금액을 기준으로 할인금액이 계산됩니다.</p>
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function delPrdPrc(no) {
		if(confirm('선택하신 설정을 삭제하시겠습니까?')) {
			$.post('?body=config@order_prdprc.exe', {"exec":"remove", "no":no}, function(x) {
				document.location.reload();
			});
		}
	}
</script>
<form method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">할인 옵션</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">할인 옵션</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">할인사용</th>
			<td>
				<ul>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_use" value="Y" <?=checked($cfg['prdprc_sale_use'], "Y")?>> 사용함</label></li>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_use" value="N" <?=checked($cfg['prdprc_sale_use'], "N")?>> 사용안함</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">대상</th>
			<td>
				<ul>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_mtype" value="1" <?=checked($cfg['prdprc_sale_mtype'], '1')?>> 전체 고객</label></li>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_mtype" value="2" <?=checked($cfg['prdprc_sale_mtype'], '2')?>> 회원만</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">결제수단</th>
			<td>
				<ul>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_ptype" value="1" <?=checked($cfg['prdprc_sale_ptype'], '1')?>> 모든 결제</label></li>
					<li><label class="p_cursor"><input type="radio" name="prdprc_sale_ptype" value="2" <?=checked($cfg['prdprc_sale_ptype'], '2')?>> 현금 결제일때만</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">중복할인 설정</th>
			<td>
				회원할인/이벤트할인/쿠폰 적용시 추가할인을
				<select name="prdprc_sale_add">
					<option value="1" <?=checked($cfg['prdprc_sale_add'], '1', true)?>>허용</option>
					<option value="2" <?=checked($cfg['prdprc_sale_add'], '2', true)?>>허용안함</option>
				</select>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>