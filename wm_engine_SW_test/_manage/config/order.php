<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['order_auth']) $cfg['order_auth'] = 10;
	if(!$cfg['order_cpn_paytype']) $cfg['order_cpn_paytype'] = 1;
	if(!$cfg['order_milage_paytype']) $cfg['order_milage_paytype'] = 1;
	if(!$cfg['order_cpn_milage']) $cfg['order_cpn_milage'] = 1;
	if(!$cfg['blacklist_print']) $cfg['blacklist_print'] = "N";
	if(!$cfg['prdcode_print']) $cfg['prdcode_print'] = "N";
	if(!$cfg['approval_standby']) $cfg['approval_standby'] = "N";
	if(!$cfg['order_memo_print']) $cfg['order_memo_print'] = "Y";
	if(!$cfg['member_level_print']) $cfg['member_level_print'] = "N";
	if(!$cfg['member_memo_print']) $cfg['member_memo_print'] = "N";
	if(!$cfg['use_trash_ord']) $cfg['use_trash_ord'] = 'N';
	if(!$cfg['order_storage_print']) $cfg['order_storage_print'] = 'N';
    $scfg->def('use_order_phone', 'N');
	if(!$cfg['deny_placeorder_cancel']) $cfg['deny_placeorder_cancel'] = 'N';
	if(!$cfg['deny_decided_cancel']) $cfg['deny_decided_cancel'] = 'Y';
	if(!$cfg['invoice_essential']) $cfg['invoice_essential'] = 'N';
	if(!$cfg['stat1_direct_cancel']) $cfg['stat1_direct_cancel'] = 'Y';

	// 자동 배송완료 처리
	$_auto_dlv_finish = array(
		1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 30, 60, 90
	);

	// 배송완료 반품신청 기한
	$_deny_decided_cancel_date = array(
		1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 30, 60, 90
	);

	$wec_acc = new weagleEyeClient($_we, 'mall');
	$result = $wec_acc->call('getAutoDlvFinish');
	$auto_dlv_finish = $result[0]->day[0];

	if($partner_order) {
		$config_code = "partner_order";
		$box_first = "box_title first";
	} else {
	    $config_code = "order";
		$box_first = "box_title";
	}

    // 사용자 카드 취소 가능 여부
    $duel_cancel_able = true;
    if ($card['pg'] == 'allat') $duel_cancel_able = false;
    $duel_cancel_able = false; // 일단 기능 숨김

?>
<?php if (!$partner_order) { ?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="order2">
	<div class="box_title first">
		<h2 class="title">주문 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">주문 권한</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_auth" value="9" <?=checked($cfg['order_auth'],9)?>> 회원만 구매할 수 있습니다.</label> <span class="explain">(비회원의 경우 로그인 페이지로 유도합니다.)</span><br>
				<label class="p_cursor"><input type="radio" name="order_auth" value="10" <?=checked($cfg['order_auth'],10)?>> 회원 및 비회원 모두 구매할 수 있습니다.</label>
				<div style="padding-left:18px;">
					<label class="p_cursor"><input type="radio" name="order_style" value="" <?=checked($cfg['order_style'], '')?>> 비회원도 바로 주문</label> <br>
					<label class="p_cursor"><input type="radio" name="order_style" value="login" <?=checked($cfg['order_style'], 'login')?>>  비회원 주문 시 로그인 페이지로 유도</label>
					<ul class="list_msg">
						<li>로그인 페이지로 이동 후 비회원주문하기 버튼을 통해 주문할 수 있습니다.</li>
						<li>사용 전 <a href="?body=design@editor&type=&edit_pg=5%2F10" target="_blank">{{$비회원주문하기구문}}</a>이 삽입되어 있는지 확인바랍니다.</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">쿠폰 사용 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_cpn_paytype" value="1" <?=checked($cfg['order_cpn_paytype'],1)?>> 온라인 및 시리얼쿠폰을 모든 결제방식에서 사용할 수 있습니다.</label><br>
				<label class="p_cursor"><input type="radio" name="order_cpn_paytype" value="2" <?=checked($cfg['order_cpn_paytype'],2)?>> 온라인 및 시리얼쿠폰은 무통장입금 결제 시에만 사용할 수 있습니다.</label><br>
				<label class="p_cursor"><input type="radio" name="order_cpn_paytype" value="3" <?=checked($cfg['order_cpn_paytype'],3)?>> 주문 시 쿠폰 사용을 금지합니다. (이미 지급된 쿠폰은 마이페이지에서 확인할 수 있습니다.)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">할인제한 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_cpn_milage" value="1" <?=checked($cfg['order_cpn_milage'],1)?>> 쿠폰과 적립금은 중복 사용이 가능합니다.</label><br>
				<label class="p_cursor"><input type="radio" name="order_cpn_milage" value="2" <?=checked($cfg['order_cpn_milage'],2)?>> 쿠폰과 적립금은 중복 사용이 불가능합니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">자동 <?=$_order_stat[5]?></th>
			<td>
				배송일로부터
				<?=selectArray($_auto_dlv_finish, 'auto_dlv_finish', 1, '사용안함', $auto_dlv_finish)?>
				일 후 <strong class="p_color2"><?=$_order_stat[4]?></strong>인 주문을 <strong class="p_color2"><?=$_order_stat[5]?></strong>상태로 변경합니다.
				<ul class="list_msg">
					<li>구매 적립금은 <?=$_order_stat[5]?> 상태로 변경되는 시점에 지급됩니다.</li>
					<li><?=$_order_stat[4]?> 일자로부터 날짜가 변경되면 하루가 경과된 것으로 간주합니다.</li>
					<li>매일 새벽 3시에 일괄 처리됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">해외배송 추가필드</th>
			<td>
				<label><input type="checkbox" value="Y" name="order_add_field_use" <?=checked($cfg['order_add_field_use'],'Y')?>/> 주문자 추가필드 사용</label>
				<ul class="list_msg">
					<li>통관을 위한 추가사항이 필요할 때 사용합니다.</li>
					<li>중국발 주문은 주문자 ID 번호로 사용됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">주문 시 일반전화<br>필수여부 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_order_phone" value="Y" <?=checked($cfg['use_order_phone'],'Y')?>> 주문 시 일반전화를 필수로 입력받습니다.</label><br>
				<label class="p_cursor"><input type="radio" name="use_order_phone" value="N" <?=checked($cfg['use_order_phone'],'N')?>> 주문 시 일반전화를 필수로 입력받지 않습니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?=$_order_stat[4]?>처리 시 송장번호<br>필수여부 설정</th>
			<td>
				<ul>
					<li><label><input type="radio" name="invoice_essential" value="N" <?=checked($cfg['invoice_essential'], 'N')?>> 송장번호를 입력하지 않아도 <?=$_order_stat[4]?> 및 <?=$_order_stat[5]?>상태로 변경할 수 있습니다.</label></li>
					<li><label><input type="radio" name="invoice_essential" value="Y" <?=checked($cfg['invoice_essential'], 'Y')?>> 수동으로 <?=$_order_stat[4]?>처리 시 송장번호를 필수로 입력해야 합니다.</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><?=$_order_stat[1]?> 시 취소요청</th>
			<td>
				<ul>
					<li><label><input type="radio" name="order_cancel_type_1" value="Y" <?=checked($cfg['order_cancel_type_1'], 'Y')?>> 관리자 승인 없이 고객이 주문취소가 가능하며, 취소 사유를 고객이 </label>
					(<label class="p_cursor"><input type="radio" name="stat1_direct_cancel" value="Y" <?=checked($cfg['stat1_direct_cancel'], "Y")?>> 등록함</label>
					<label class="p_cursor"><input type="radio" name="stat1_direct_cancel" value="N" <?=checked($cfg['stat1_direct_cancel'], "N")?>> 등록하지 않음</label>)
					<li><label><input type="radio" name="order_cancel_type_1" value="" <?=checked($cfg['order_cancel_type_1'], '')?>> 취소요청 상태로 변경되며, 관리자가 직접 승인합니다.</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><?=$_order_stat[3]?> 후 환불요청</th>
			<td>
				<ul>
                    <?php if ($duel_cancel_able == true) { ?>
					<li><label><input type="radio" name="deny_placeorder_cancel" value="C" <?=checked($cfg['deny_placeorder_cancel'], 'C')?>> <?=$_order_stat[3]?> 전까지 고객이 PG결제를 직접 환불처리할수 있습니다.</label></li>
                    <?php } ?>
					<li><label><input type="radio" name="deny_placeorder_cancel" value="N" <?=checked($cfg['deny_placeorder_cancel'], 'N')?>> <?=$_order_stat[3]?> 전까지 고객이 환불신청 접수가 가능합니다.</label></li>
					<li><label><input type="radio" name="deny_placeorder_cancel" value="Y" <?=checked($cfg['deny_placeorder_cancel'], 'Y')?>> <?=$_order_stat[3]?> 상태에서 고객이 환불신청 접수가 불가능합니다.</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><?=$_order_stat[5]?> 후 반품요청</th>
			<td>
				<ul>
					<li><label><input type="radio" name="deny_decided_cancel" value="N" <?=checked($cfg['deny_decided_cancel'], 'N')?>> <?=$_order_stat[5]?> 시점에서 <?=selectArray($_deny_decided_cancel_date, 'deny_decided_cancel_date', true, '', $cfg['deny_decided_cancel_date'])?> 일 이내 <?=$_order_stat[16]?>이 가능합니다.</label></li>
					<li><label><input type="radio" name="deny_decided_cancel" value="Y" <?=checked($cfg['deny_decided_cancel'], 'Y')?>> <?=$_order_stat[5]?> 상태에서 고객에의해 <?=$_order_stat[16]?>이 불가능합니다.</label></li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<div>
	<div class="<?=$box_first?>">
		<h2 class="title">주문 관리 설정</h2>
	</div>
	<form method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return statCustom(this);">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="config_code" value=<?=$config_code?>>
		<table class="tbl_row">
			<caption class="hidden">주문 관리 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<?PHP
				include 'order_config_order.inc.php';
			?>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>
<script>
function statCustom(f) {
    var check = true;
    $('.order_stat_custom').each(function() {
        if (this.value.length > 9) {
            window.alert('주문상태명은 최대 9자까지 입력할 수 있습니다.');
            check = false;
            return false;
        }
    });
    if (check == false) return false;

    f.target = hid_frame;
    printLoading();

    return true;
}
</script>
<?php if (!$partner_order) { ?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="order3">
	<div class="box_title">
		<h2 class="title">주문 삭제/보관 기간 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문 삭제/보관 기간 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">삭제 주문서 이동</th>
			<td>
				<label><input type="radio" name="use_trash_ord" value="Y" <?=checked($cfg['use_trash_ord'], 'Y')?>> 주문 휴지통으로 이동</label>
				<label><input type="radio" name="use_trash_ord" value="N" <?=checked($cfg['use_trash_ord'], 'N')?>> 즉시 영구 삭제(복구 불가)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품 보관 기간</th>
			<td>
				<?=selectArray(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'trash_ord_trcd', true, '삭제안함', $cfg['trash_ord_trcd'])?>
				일 후 영구삭제
				<ul class="list_msg">
					<li>영구삭제된 상품의 모든 데이터(이미지 포함)는 복구가 불가능합니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="order_print_config">
	<div class="box_title">
		<h2 class="title">주문서 인쇄 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문서 인쇄 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">블랙리스트 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="blacklist_print" value="Y" <?=checked($cfg['blacklist_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="blacklist_print" value="N" <?=checked($cfg['blacklist_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 블랙리스트 회원 아이콘을 같이 출력할지 선택합니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">상품코드 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="prdcode_print" value="Y" <?=checked($cfg['prdcode_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="prdcode_print" value="N" <?=checked($cfg['prdcode_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 상품코드를 같이 출력할지 선택합니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">관리자메모 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_memo_print" value="Y" <?=checked($cfg['order_memo_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="order_memo_print" value="N" <?=checked($cfg['order_memo_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 관리자메모를 같이 출력할지 선택합니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">회원등급 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_level_print" value="Y" <?=checked($cfg['member_level_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="member_level_print" value="N" <?=checked($cfg['member_level_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 회원등급을 같이 출력할지 선택합니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">회원메모 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_memo_print" value="Y" <?=checked($cfg['member_memo_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="member_memo_print" value="N" <?=checked($cfg['member_memo_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 회원메모를 같이 인쇄할지 선택합니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">창고정보 인쇄</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_storage_print" value="Y" <?=checked($cfg['order_storage_print'], "Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="order_storage_print" value="N" <?=checked($cfg['order_storage_print'], "N")?>> 사용안함</label>
				<div class="explain">'주문서 인쇄'시 창고위치를 같이 인쇄할지 선택합니다.</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!--
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@order.config.exe">
	<input type="hidden" name="order_prd_change" value="Y">
	<input type="hidden" name="config_code" value="order_product_change">
	<div class="box_title">
		<h2 class="title">주문 상품 수량 및 옵션 변경</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>주문 내역 각 상품들의 수량과 옵션을 변경할 수 있는 기능을 사용합니다</li>
			<li>수량변경을 하실 경우 주문상태에 따라 적립금이 자동 지급/차감됩니다</li>
		</ul>
	</div>
	<div class="box_bottom box_bottom2">
		<?if($cfg['order_prd_change'] == "Y"){?>
		<span class="p_color2">[주문 상품 수량 및 옵션 변경 기능을 사용중입니다]</span>
		<?}else{?>
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?}?>
	</div>
</form>
-->
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@receipt.exe">
	<input type="hidden" name="config_code" value="order_receipt">
	<div class="box_title">
		<h2 class="title">계산서/영수증 출력</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">계산서/영수증 출력</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">하단 추가메시지</th>
			<td>
				<textarea name="receipt_footer" class="txta"><?=trim($pdo->row("select value from `{$tbl['default']}` where code='receipt_footer'"))?></textarea>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>