<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개별 배송비 등록
	' +----------------------------------------------------------------------------------------------+*/

	if(isset($cfg['delivery_free_limit']) == false) {
		$cfg['delivery_free_limit'] = 50000;
	}

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from {$tbl['product_delivery_set']} where no='$no' $ptn_qry");
	} else {
		$data['delivery_type'] = 4;
		$data['delivery_base'] = 1;
		$data['free_delivery_area'] = 'Y';
		$data['free_yn'] = 'N';
	}

	// 고정 배송
	if($data['delivery_type'] == '6') {
		$policy_static_prc = $data['delivery_free_limit'];
		$data['delivery_free_limit'] = '';
	}

	// 차등 배송 기본값
	if(empty($data['delivery_free_limit'])) {
		$data['delivery_free_limit'] = json_encode(array(array(0, $cfg['delivery_free_limit'], $cfg['delivery_fee'])));
	}
	$delivery_free_limit = json_decode($data['delivery_free_limit']);

	// 현재 메뉴에서 사용하지 않는 배송타입 제거
	unset($_delivery_types[1], $_delivery_types[2], $_delivery_types[3]);

	// 배송비 반복문
	$last_prc = 0;
	function parseData(&$array, &$cnt = 0) {
		global $last_prc;

		if(is_array($array) == false) return false;

		$data = current($array);
		if($data == false) {
			reset($array);
			return false;
		}

		$data['last_prc'] = $last_prc;
		$last_prc = parsePrice($data[1]); // 최종 금액 범위

		next($array);
		return $data;
	}

	$listURL = getListURL('delivery_set');
	if(empty($listURL)) $listURL = '?body=config@delivery_set';

?>
<form id="deliveryFrm" method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="config@delivery_set_regist.exe">
	<input type="hidden" name="no" value="<?=$no?>">

	<table class="tbl_row">
		<caption>개별 배송비 관리</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?if($no > 0) {?>
			<tr>
				<th scope="col">개별배송비 코드</th>
				<td><?=$no?></td>
			</tr>
			<?}?>
			<tr>
				<th scope="col">세트명</th>
				<td><input type="text" name="set_name" value="<?=inputText($data['set_name'])?>" class="input input_full"></td>
			</tr>
			<tr>
				<th scope="col">배송비 유형</th>
				<td>
					<?=selectArray($_delivery_types, 'delivery_type', false, null, $data['delivery_type'], 'setDeliveryType(this)')?>
				</td>
			</tr>
			<tr>
				<th scope="col">지역별 추가 배송비</th>
				<td>
					<ul>
						<li><label><input type="radio" name="free_delivery_area" value="Y" <?=checked($data['free_delivery_area'], 'Y')?>> 지역별 추가 배송비를 사용합니다.</label></li>
						<li><label><input type="radio" name="free_delivery_area" value="X" <?=checked($data['free_delivery_area'], 'X')?>> 지역별 추가 배송비를 사용하지 않습니다.</label></li>
					</ul>
				</td>
			</tr>
			<tr class="delivery_base hidden">
				<th scope="col">금액 기준</th>
				<td>
					<li><label><input type="radio" name="delivery_base" value="1" <?=checked($data['delivery_base'], '1')?>> 주문금액(할인 전 판매가 기준)</label></li>
					<li><label><input type="radio" name="delivery_base" value="2"  <?=checked($data['delivery_base'], '2')?>> 결제금액(최종 결제금액 기준)</label></li>
				</td>
			</tr>
			<tr>
				<th scope="col">프로모션 기준</th>
				<td>
					<li><label><input type="radio" name="free_yn" value="Y" <?=checked($data['free_yn'], 'Y')?>> 무료배송 이벤트/회원 무료배송을 적용합니다.</label></li>
					<li><label><input type="radio" name="free_yn" value="N"  <?=checked($data['free_yn'], 'N')?>> 무료배송 이벤트/회원 무료배송을 적용하지 않습니다.</label></li>
				</td>
			</tr>
			<!-- 금액별 배송 -->
			<tr class="delivery_type_desc type4 type5 hidden">
				<th scope="col"><span class="unit_nm" style="font-weight:bold;">금액</span>별 배송</th>
				<td>
					<p style="margin-bottom:15px;">
						<label><input type="checkbox" name="delivery_loop_type" value="Y" <?=checked($data['delivery_loop_type'], 'Y')?>> 범위 반복 설정</label>
					</p>
					<table class="tbl_inner line full delivert_free_limit_N">
						<thead>
							<tr>
								<th><span class="unit_nm">금액</span> 범위</th>
								<th>배송비</th>
							</tr>
						</thead>
						<tbody>
							<?while($policy = parseData($delivery_free_limit)) {?>
							<tr class="polices">
								<td class="left">
									<input type="text" name="policy_N_std[]" value="<?=$policy['last_prc']?>" class="input prc_start" size="10" readonly style="background:#f2f2f2;"> <span class="unit"><?=$cfg['currency_type']?></span> 이상
									<span class="less" <?=$less_disaply?>>
										~ <input type="text" name="policy_N_end[]" value="<?=$policy[1]?>" class="input prc_end" size="10"> <span class="unit"><?=$cfg['currency_type']?></span> 미만
									</span>
								</td>
								<td class="left" style="position:relative;">
									<input type="text" name="policy_N_prc[]" value="<?=$policy[2]?>" class="input" size="10"> <?=$cfg['currency_type']?>
									<div class="right_area btn_set">
										<span class="box_btn_s"><input type="button" value="삭제" onclick="removePolicy(this);"></span>
									</div>
								</td>
							</tr>
							<?}?>
						</tbody>
					</table>

					<table class="tbl_inner line full delivert_free_limit_Y">
						<thead>
							<tr>
								<th>금액 범위</th>
								<th>배송비</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="left">
									<input type="text" name="policy_Y_std" value="<?=$delivery_free_limit[0][0]?>" class="input" size="10"> <span class="unit"><?=$cfg['currency_type']?></span> 마다 부과
								</td>
								<td class="left">
									<input type="text" name="policy_Y_prc" value="<?=$delivery_free_limit[0][2]?>" class="input" size="10"> <?=$cfg['currency_type']?>
								</td>
							</tr>
							</tbody>
					</table>
				</td>
			</tr>
			<!-- // 금액별 배송 -->
			<!-- 고정 배송 -->
			<tr class="delivery_type_desc type6 hidden">
				<th scope="col">고정 배송</th>
				<td>
					구매금액 및 구매건수와 상관없이
					<input type="text" name="policy_static_prc" value="<?=$policy_static_prc?>" class="input" size="10"> <?=$cfg['currency_type']?>
				</td>
			</tr>
			<!-- // 고정 배송 -->
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="location.href='<?=$listURL?>'"></span>
	</div>
</form>
<script type="text/javascript">
	var f = document.querySelector('#deliveryFrm');

	function setDeliveryType() {
		var delivery_type = $(':selected', f.delivery_type).val();
		$('.delivery_type_desc').not('.type'+delivery_type).addClass('hidden');
		$('.delivery_type_desc.type'+delivery_type).removeClass('hidden');
		$('.delivery_base').addClass('hidden');

		if(delivery_type == '4') {
			$('.unit').html('<?=$cfg['currency_type']?>');
			$('.unit_nm').html('금액');
			$('.delivery_base').removeClass('hidden');
		}
		else if(delivery_type == '5') {
			$('.unit').html('개');
			$('.unit_nm').html('수량');
		}
	}

	// 새로운 정책 추가
	function addDeliveryPolicy(obj) {
		if(typeof obj == 'object') {
			var delivery_type = $(':selected', f.delivery_type).val();
			var org_tr = $(obj).parents('tr').eq(0).last();

			// 배송비 유형에 따른 첫 기본 값 자동 입력
			if($('.polices').length == 1) {
				if($('select[name=delivery_type]').val() == '5') {
					$('.prc_end').eq(0).val('1');
				} else {
					$('.prc_end').eq(0).val('<?=$cfg['delivery_free_limit']?>');
				}
			}

			var new_tr = org_tr.clone();
			new_tr.find('.prc_start').val(org_tr.find('.prc_end').val());
			//new_tr.find('.prc_end').val(org_tr.find('.prc_end').val().toNumber()+(delivery_type == '4' ? 1000 : 1));
			new_tr.find('.prc_end').val('0');

			org_tr.after(new_tr);
			setPriceEvent(new_tr.find('.prc_end')[0]);
		}
		// 추가 버튼 이동
		$('.add_btn').remove();
		$('.polices').last().find('.btn_set').append('<span class="box_btn_s blue add_btn"><input type="button" value="추가" onclick="addDeliveryPolicy(this);"></span>');

		// input 가격 변경 이벤트 재설정
		$('.prc_end').bind('change keyup', function() {
			setPriceEvent(this);
		}).focus(function() {
			this.select();
		});
		$('.delivery_type_desc').find('.less').last().hide();
	}

	// 금액 범위 input에 가격 변경 이벤트 설정
	function setPriceEvent(o) {
		o.value = o.value.toNumber();
		$('.prc_end').each(function(idx) {
			if(this == o) {
				$('.prc_start').eq(idx+1).val(o.value)
			}
		});
		$('.delivery_type_desc').find('.less').show();
		$('.delivery_type_desc').find('.less').last().hide();
	}

	// 정책 삭제
	function removePolicy(o) {
		if($(o).parents('tbody').eq(0).find('.polices').length == 1) {
			window.alert('더이상 삭제할수 없습니다.');
			return false;
		}
		if(confirm('배송 정책을 삭제하시겠습니까?')) {
			$(o).parents('tr').eq(0).remove();
			addDeliveryPolicy();

			$('input[name="policy_N_std[]"]').eq(0).val('0');
		}
	}

	// 범위 반복 설정
	function setDeliveryLoopType() {
		if(f.delivery_loop_type.checked == true) {
			$('.delivert_free_limit_N').hide();
			$('.delivert_free_limit_Y').show();
		} else {
			$('.delivert_free_limit_Y').hide();
			$('.delivert_free_limit_N').show();
		}
	}

	$(document).ready(function() {
		setDeliveryType();
		addDeliveryPolicy();
		setDeliveryLoopType();
		$(':checkbox[name=delivery_loop_type]').change(function() {
			setDeliveryLoopType();
		});
		$('.delivery_type_desc').find('.less').show();
		$('.delivery_type_desc').find('.less').last().hide();
	});
</script>