<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 수동 주문서 생성
	' +----------------------------------------------------------------------------------------------+*/

	$sms = $pdo->assoc("select * from $tbl[sms_case] where `case`='2'");
	$sms_send = ($sms['use_check'] == 'Y') ? '<input type="checkbox" name="sms_send" value="Y"> 주문관련 SMS를 발송합니다.' : '';

	if(!$prd_prc) $prd_prc = 0;
	if(!$cfg['milage_type_per']) $cfg['milage_type_per'] = 0;

	// 추가 항목
	$_ord_add_info = array();
	$add_field_file = $root_dir.'/_config/order.php';
	if (file_exists($add_field_file) == true) {
	    include_once $add_field_file;
	    foreach ($_ord_add_info as $key => $val) {
	        $_ord_add_info[$key]['class'] = 'input';
	        $_ord_add_info[$key]['result'] = orderAddFrm($key, 1);
	        if ($_ord_add_info[$key]['ncs'] == 'Y') {
	            $_ord_add_info[$key]['name'] = "<strong>".$_ord_add_info[$key]['name']."</strong>";
	        }
	    }
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/order.js?<?=date('YmdHi')?>"></script>
<form id="ordFrm" name="adminOrdFrm" method="post" action='./index.php' target="hidden<?=$now?>" onsubmit="return ordCheck(this)" style='min-width: 800px;'>
	<input type="hidden" name="body" value="order@order_admin.exe">
	<div class="box_title first">
		<h2 class="title">주문정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문정보</caption>
		<colgroup>
			<col style="width:15%;">
		</colgroup>
		<tr>
			<th scope="row">결제방식</th>
			<td>
				<select name="pay_type" onchange="chgPaytype($(this))">
					<option value="">:: 결제방식 ::</option>
					<?php foreach ($_pay_type as $key => $val) { ?>
					<option value="<?=$key?>" <?=checked($ord['pay_type'], $key, true)?>><?=$val?></option>
					<?php } ?>
				</select>
				<span class="explain">입력하신 결제방식으로 매출통계에 그대로 반영되므로 이를 감안하여 입력해 주시기 바랍니다.</span>
			</td>
		</tr>
		<tr id="bank_info" style="display:none;">
			<th scope="row">입금계좌</th>
			<td>
				<select name="bank">
					<option value="">::입금은행선택::</option>
					<?php
					$res = $pdo->iterator("select no, bank, account, owner from `$tbl[bank_account]` order by `sort`");
                    foreach ($res as $data) {
						$checked = ($data['bank'].' '.$data['account'].' '.$data['owner'] == $ord['bank']) ? 'selected' : '';
						echo "<option value=\"$data[no]\" $checked>$data[bank] $data[account] $data[owner]</option>\n";
					}
					?>
					</select>
				입금자 명 : <input type="text" name="bank_name" value="<?=$ord['bank_name']?>" class="input" size="12">
				<?php if ($cfg['cash_receipt_use'] == "Y") { ?>
				<div class="desc1">
				현금영수증 신청용 핸드폰번호/사업자번호/현금영수증카드번호 : <input type="text" name="cash_reg_num" size="15" class="input" value="<?=$ord['bank_name']?>">
				</div>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row">배송비</th>
			<td><input type="text" name="dlv_prc" value="<?=$ord['dlv_prc']?>" class="input" size="12"></td>
		</tr>
		<tr>
			<th scope="row">예치금 사용</th>
			<td>
				<input type="text" name="emoney_prc" value="0" class="input" size="12">
				<span class="explain">실제로 고객의 예치금에서 차감되므로, 해당 고객이 소지한 예치금 이상은 입력하실 수 없습니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 사용</th>
			<td>
				<input type="text" name="milage_prc" value="0" class="input" size="12">
				<span class="explain">실제로 고객의 적립금에서 차감되므로, 해당 고객이 소지한 적립금 이상은 입력하실 수 없습니다.</span>
			</td>
		</tr>
        <?php foreach ($_ord_add_info as $key => $val) { ?>
        <tr>
            <th><?=$val['name']?></th>
            <td><?=$val['result']?></td>
        </tr>
        <?php } ?>
	</table>
	<?php if ($_GET['body'] == 'order@order_exchg.frm') { ?>
	<input type="hidden" name="parent" value="<?=$ono?>">
	<input type="hidden" name="opno" value="<?=$opno?>">
	<div class="box_title first">
		<h2 class="title">교환주문서 추가정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">교환주문서 추가정보</caption>
		<colgroup>
			<col style="width:15%;">
		</colgroup>
		<tr>
			<th scope="row">교환전 상품금액</th>
			<td colspan="4"><?=number_format($rev_prc)?> 원</td>
		</tr>
		<tr>
			<th scope="row" rowspan="<?=count($oprdcache)?>">교환대상상품</th>
		<?php foreach ($oprdcache as $key => $val) { ?>
		<?php if ($key != 0) echo '<tr>'; ?>
			<th><?=$val['name']?></th>
			<td><?=str_replace('<split_small>', ' : ', str_replace('<split_big>', ' / ', $val['option']))?></td>
			<td><?=$val['buy_ea']?> ea</td>
			<td class="right"><?=$val['total_prc']?> 원</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">교환후 상품금액</th>
			<td colspan="4">
				<input type="text" name="ex_prd_prc" value="<?=$prd_prc?>" class="input" size="12">
				<ul class="list_msg">
					<li>하단의 상품정보를 편집하셔서 교환하실 상품을 결정하시면 교환할 상품금액이 자동으로 계산됩니다.</li>
					<li>cs 상에 발생하는 추가금액 혹은 할인금액을 최종 상품금액에 반영해주세요.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">주문상태</th>
			<td colspan="4">
				<select name="ex_stat">
				<?php for ($i = 1; $i <= 4; $i++) { ?>
					<option value="<?=$i?>" <?=checked($i, $ord['stat'], true)?>><?=$_order_stat[$i]?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
	</table>
	<?php } ?>
	<div class="box_title">
		<h2 class="title">배송정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배송정보</caption>
		<colgroup>
			<col style="width:15%;">
		</colgroup>
		<tr>
			<th scope="row">주문회원</th>
			<td>
				<input type="text" name="member_id" value="<?=$ord['member_id']?>" class="input" size="12" readonly onclick="msearch.open();">
				<span class="box_btn_s blue"><input type="button" value="회원검색" onclick="msearch.open();"></span>
				<span class="box_btn_s blue"><input type="button" value="주문검색" onclick="osearch.open();"></span>
				<span class="box_btn_s gray"><input type="button" value="검색취소" onclick="setAddr()"></span>
				<div class="explain">회원이거나 주문이력이 있는 고객일 경우 검색을 이용해 주시고, 비회원은 주문 정보를 수동으로 입력해 주시기 바랍니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">주문자 명</th>
			<td><input type="text" name="buyer_name" class="input" size="10" value="<?=$ord['buyer_name']?>"></td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td><input type="text" name="buyer_phone" class="input" size="14" value="<?=$ord['buyer_phone']?>"></td>
		</tr>
		<tr>
			<th scope="row">휴대폰</th>
			<td>
				<input type="text" name="buyer_cell" class="input" size="14"  value="<?=$ord['buyer_cell']?>">
				<label class="explain p_cursor"><?=$sms_send?> (주문 관련 SMS 수신 동의: <input type="checkbox" name="sms" value="Y" <?=checked($ord['sms'],'Y')?>>)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">메일주소</th>
			<td>
				<input type="text" name="buyer_email" class="input" size="30" value="<?=$ord['buyer_email']?>">
				<label class="explain p_cursor"><input type="checkbox" name="mail_send" value="Y" <?=checked($ord['mail_send'],'Y')?>> 주문관련 메일을 발송합니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">수령자 명</th>
			<td>
				<input type="text" name="addressee_name" class="input" size="10" value="<?=$ord['addressee_name']?>">
				<label class="explain p_cursor"><input type="checkbox" onclick="equalAddr(this)"> 주문자와 동일</label>
			</td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td><input type="text" name="addressee_phone" class="input" size="14" value="<?=$ord['addressee_phone']?>"></td>
		</tr>
		<tr>
			<th scope="row">휴대폰</th>
			<td><input type="text" name="addressee_cell" class="input" size="14"  value="<?=$ord['addressee_cell']?>"></td>
		</tr>
		<tr>
			<th scope="row">배송지 주소</th>
			<td>
				<input type="text" name="addressee_zip" class="input" size="7"  value="<?=$ord['addressee_zip']?>" onclick="zipSearchM('adminOrdFrm','addressee_zip','addressee_addr1','addressee_addr2')" readonly>
				<span class="box_btn_s"><input type="button" value="우편번호 찾기" onClick="zipSearchM('adminOrdFrm','addressee_zip','addressee_addr1','addressee_addr2')"></span>
				<p><input type="text" name="addressee_addr1" class="input" size="50" value="<?=$ord['addressee_addr1']?>"></p>
				<p><input type="text" name="addressee_addr2" class="input" size="50" value="<?=$ord['addressee_addr2']?>"></p>
			</td>
		</tr>
		<tr>
			<th scope="row">주문메세지</th>
			<td>
				<textarea name="dlv_memo" class="txta" cols="50" rows="5" style="width: 99%; "><?=$ord['dlv_memo']?></textarea>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">상품정보</h2>
		<span class="box_btn_s blue btns"><input type="button" onclick="psearch.open()" value="+ 상품추가"></span>
	</div>
	<div class="box_sort">
		상품합계 : <strong id="prd_prc" class="desc3">0</strong> 원
	</div>
	<table class="tbl_col">
		<caption class="hidden">상품정보</caption>
		<colgroup>
			<col>
			<col style="width:160px">
			<col style="width:100px">
			<col style="width:70px">
			<col style="width:140px">
			<?php if($cfg['use_prd_dlvprc'] == 'Y') { ?>
			<col style="width:100px">
			<?php } ?>
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">상품명</th>
				<th scope="col">옵션</th>
				<th scope="col">기본가</th>
				<th scope="col">수량</th>
				<th scope="col">할인</th>
				<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
				<th scope="col">개별배송비</th>
				<?php } ?>
				<th scope="col">실판매가</th>
				<th scope="col">적립금</th>
				<th scope="col">회원적립금</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody id="ord_prd" >
			<?=$preload_products?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" id="stpBtn" value="등록완료"></span>
	</div>
</form>

<script type="text/javascript">
	var f = $('#ordFrm')[0];

	function chgPaytype(obj) {
		document.getElementById('bank_info').style.display = (obj.val() == '2') ? '' : 'none';
	}
	chgPaytype($(f.pay_type));

	layerWindow.prototype.msel = function(json) {
		$(':input[name=member_id]').val(json.member_id);
		mmile_per = parseInt(json.mmile_per);
		if(!mmile_per) mmile_per = 0;
		setAddr(json);
		getPrd_prc();
		this.close();
	}
	layerWindow.prototype.psel = order_prd_add_func;

	$(window).bind({
		'keydown': function(e) {
			if(e.ctrlKey == true) $('.popupContent').find(':button[value=선택]').val('계속선택');
		},
		'keyup': function(e) {
			if(e.ctrlKey == false) $('.popupContent').find(':button[value=계속선택]').val('선택');
		}
	});

	var msearch = new layerWindow('member@member_inc.exe');
	var osearch = new layerWindow('order@order_inc.exe');
	var psearch = new layerWindow('product@product_inc.exe&type=add&stat[]=2&stat[]=4');

	function pdel(obj) {
		while(obj[0].tagName != 'TR') {
			obj = obj.parent();
		}
		obj.remove();
		getPrd_prc();
	}

	function setAddr(json) {
		if(!json) {
			$(':input[name=member_id]').val('');
			json = {'name':'', 'phone':'', 'cell':'', 'email':'', 'zip':'', 'addr1':'', 'addr2':''}
		}

		f.buyer_name.value = json.name;
		f.buyer_phone.value = json.phone;
		f.buyer_cell.value = json.cell;
		f.buyer_email.value = json.email;
		f.addressee_name.value = json.name;
		f.addressee_phone.value = json.phone;
		f.addressee_cell.value = json.cell;
		f.addressee_zip.value = json.zip;
		f.addressee_addr1.value = json.addr1;
		f.addressee_addr2.value = json.addr2;
	}

	function equalAddr(obj) {
		if(obj.checked == true) {
			f.addressee_name.value = f.buyer_name.value;
			f.addressee_phone.value = f.buyer_phone.value
			f.addressee_cell.value = f.buyer_cell.value;
		} else {
			f.addressee_name.value = '';
			f.addressee_phone.value = '';
			f.addressee_cell.value = '';
		}
	}

	function ordCheck(f) {
		if(!checkBlank(f.pay_type, '결제방식을 입력해주세요.')) return false;
		if(f.pay_type.value == '2' && (f.bank.value == '' || f.bank_name.value == '')) {
			return checkBlank(f.bank_name, '입금 계좌를 입력해주세요.');
		}
		if(!checkBlank(f.buyer_name, '주문자명을 입력해주세요.')) return false;
		//if(!checkBlank(f.buyer_phone, '주문자 전화번호를 입력해주세요.')) return false;
		if(!checkBlank(f.buyer_cell, '주문자 휴대전화 번호를 입력해주세요.')) return false;
		if(!checkBlank(f.buyer_email, '주문자 메일 주소를 입력해주세요.')) return false;
		if(!checkBlank(f.addressee_name, '수령자명을 입력해주세요.')) return false;
        /*
		if(!checkBlank(f.addressee_phone, '수령자 전화번호를 입력해주세요.')) return false;
		if(!checkBlank(f.addressee_phone, '수령자 휴대전화 번호를 입력해주세요.')) return false;
		if(!checkBlank(f.addressee_zip, '배송지 우편번호를 입력해주세요.')) return false;
		if(!checkBlank(f.addressee_addr1, '배송지 주소를 입력해주세요.')) return false;
		if(!checkBlank(f.addressee_addr2, '배송지 상세주소를 입력해주세요.')) return false;
        */
		if($(':input[name="pno[]"]').length < 1) {
			window.alert('주문 상품을 추가 해 주십시오.');
			return false;
		}

		if(confirm('새로운 주문서를 생성하시겠습니까?')) {
			printLoading();
			$('#stpBtn').hide();
			return true;
		}

		return false;
	}
</script>