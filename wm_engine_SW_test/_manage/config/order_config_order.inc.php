<tr>
	<th scope="row"><?=$_order_stat[3]?> 처리</th>
	<td>
		<label class="p_cursor"><input type="checkbox" name="auto_stat3" value="Y" <?=checked($cfg['auto_stat3'],'Y')?>> 주문서 출력 시 <?=$_order_stat[2]?> 주문을 자동으로 <?=$_order_stat[3]?>으로 변경합니다.</label><br>
		<label class="p_cursor"><input type="checkbox" name="auto_stat3_2" value="Y" <?=checked($cfg['auto_stat3_2'],'Y')?>> 송장번호 입력 시 <?=$_order_stat[2]?> 주문을 자동으로</label>
		(<input type="radio" name="auto_stat3_2_w" id="auto_stat3_2_w2" value="3" <?=checked($cfg['auto_stat3_2_w'],'3')?>><label for="auto_stat3_2_w2" class="p_cursor"><?=$_order_stat[3]?></label>
		<input type="radio" name="auto_stat3_2_w" id="auto_stat3_2_w1" value="4" <?=checked($cfg['auto_stat3_2_w'],'4').checked($cfg['auto_stat3_2_w'],'')?>><label for="auto_stat3_2_w1" class="p_cursor"><?=$_order_stat[4]?></label>)
		으로 변경합니다.
	</td>
</tr>
<tr>
	<th scope="row">주문상태 설정<br><span class="box_btn_s"><a onclick="orderColorReset(); return false;">초기설정 복구하기</a></span></th>
	<td>
		<script type="text/javascript" src="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.js"></script>
		<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?=$engine_url?>" type="text/css">
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$('.colorpicker').click(function() {
					$('.colorpicker_marker').hide();
					$(this).parent().find('.colorpicker_marker').show();
				});
				$('.colorpicker').blur(function() {
					orderColorChg();
					$('.colorpicker_marker').hide();
				});
			});

			function orderColorChg() {
				$('.colorpicker').each(function() {
					$(this).parent().find('label').css('color', $(this).val());
				});
			}

			function orderColorReset() {
				<? foreach($_order_color_def as $k => $v) { ?>
				$('#order_color<?=$k?>').val('<?=$v?>').css('background', '<?=$v?>');
				<? } ?>
                $('.order_stat_custom').val('');
				orderColorChg();
			}
		</script>
		<ul class="color_select">
			<?
				foreach($_order_color as $k => $v) {
				if(empty($cfg['order_color'.$k])) $cfg['order_color'.$k]=$_order_color[$k];
			?>
				<li>
					<input type="text" id="order_color<?=$k?>" name="order_color<?=$k?>" value="<?=$cfg['order_color'.$k]?>" class="colorpicker input" maxlength="7">
                    <input
                        type="text"
                        name="order_stat_custom_<?=$k?>"
                        placeholder="<?=$_order_stat_o[$k]?>"
                        class="input order_stat_custom"
                        value="<?=$scfg->get('order_stat_custom_'.$k)?>"
                    >
					<div id="colorpicker<?=$k?>" class="colorpicker_marker"></div>
					<script type="text/javascript">
						setTimeout(function() {
							$('#colorpicker<?=$k?>').farbtastic('#order_color<?=$k?>');
							orderColorChg();
						}, 100)
					</script>
				</li>
			<?
				}
			?>
		</ul>
	</td>
</tr>
<tr>
	<th scope="row">주문조회 항목설정</th>
	<td>
		<label class="p_cursor"><input type="checkbox" name="bank_name2" value="Y" <?=checked($cfg['bank_name2'],'Y')?>> 입금자 표시</label>
		<label class="p_cursor"><input type="checkbox" name="recipient" value="Y" <?=checked($cfg['recipient'],'Y')?>> 수령인 표시</label>
		<label class="p_cursor"><input type="checkbox" name="bank_price" value="Y" <?=checked($cfg['bank_price'],'Y')?>> 실결제액 표시</label>
		<label class="p_cursor"><input type="checkbox" name="ord_list_phone" value="Y" <?=checked($cfg['ord_list_phone'],'Y')?>> 휴대폰번호 표시</label><br>
		<label class="p_cursor">
			<input type="checkbox" name="ord_list_mgroup" value="Y" <?=checked($cfg['ord_list_mgroup'],'Y')?>> 회원 등급
			<span class="explain">(주문 당시의 등급이 아닌 현재 등급이 표시됩니다.)</span>
		</label><br>
		<label class="p_cursor">
			<input type="checkbox" name="ord_list_memo_icon" value="Y" <?=checked($cfg['ord_list_memo_icon'],'Y')?>> 주문 메모 아이콘 표시
			<span class="explain">(최초 설정 시 사이트가 일시적으로 느려질 수 있습니다)</span>
		</label><br>
		<label class="p_cursor">
			<input type="checkbox" name="ord_list_first_prc" value="Y" <?=checked($cfg['ord_list_first_prc'],'Y')?>> 최초 결제액표시
			<span class="explain">(한 화면에 100개 이내 출력시에만 지원됩니다.)</span>
		</label><br>
		<label class="p_cursor">
			<input type="checkbox" name="ord_list_postpone" value="Y" <?=checked($cfg['ord_list_postpone'],'Y')?>> 배송보류 정보 표시
		</label>
	</td>
</tr>
<?if($admin['level'] != 4){?>
<tr>
	<th scope="row"><?=$_order_stat[11]?> 주문<br>함께 보기</th>
	<td>
		<label class="p_cursor"><input type="radio" name="approval_standby" value="Y" <?=checked($cfg['approval_standby'], "Y")?>> 사용함</label>
		<label class="p_cursor"><input type="radio" name="approval_standby" value="N" <?=checked($cfg['approval_standby'], "N")?>> 사용안함</label>
		<ul class="list_msg">
			<li> <?=$_order_stat[11]?> 주문이 주문배송 > 전체주문조회 리스트에서 보이도록 설정합니다.</li>
			<li> <?=$_order_stat[11]?> 주문은 주문자가 결제 진행을 끝까지 진행하지않고 도중에 취소하거나,<br>승인 후 주문상태 업데이트 과정에서 누락된 경우입니다.</li>
		</ul>
	</td>
</tr>
<?}?>