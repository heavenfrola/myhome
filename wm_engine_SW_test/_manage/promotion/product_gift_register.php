<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사은품 관리
	' +----------------------------------------------------------------------------------------------+*/
	$gno = numberOnly($_GET['gno']);
	$release_btn = 'hidden';

	if(addField($tbl['product_gift'], 'order_gift_member', 'char(1)') == true) {
		addField($tbl['product_gift'], 'order_gift_first', 'char(1)');
		$pdo->query("update `{$tbl['product_gift']}` set `order_gift_member`='$cfg[order_gift_member]', `order_gift_first`='$cfg[order_gift_first]`");
	}

	if($gno) {
		$data=get_info($tbl['product_gift'],"no",$gno);
		checkBlank($data[no],"원본 자료를 입력해주세요.");

		if($data['upfile']) {
			$upfile_link = delImgStr($data, '');
		}

		if($data['complex_no'] > 0) {
			include_once $engine_dir.'/_engine/include/wingPos.lib.php';

			$prd = $pdo->assoc("select p.name, c.opts from $tbl[product] p inner join erp_complex_option c on p.no=c.pno where c.complex_no='$data[complex_no]'");
			$pname = stripslashes(trim($prd['name']));
			if($prd['opts']) $pname .= ' '.getComplexOptionName($prd['opts']);
			$release_btn = '';
		}
		$data['price_limit'] = parsePrice($data['price_limit']);
		$data['price_max'] = parsePrice($data['price_max']);

		${'attach_items_'.$data['attach_type']} = $data['attach_items'];
	}

	if(!$data['no']) {
		$data['use'] = 'N';
		$data['price_limit'] = '0';
	}
	if(empty($data['attach_type']) == true) $data['attach_type'] = '0';

	if(!$data['sdate'] || !$data['edate']) $no_date_chk = 'checked';
	else {
		$sdate = date('Y-m-d', $data['sdate']);
		$edate = date('Y-m-d', $data['edate']);
	}

	if($cfg['use_partner_shop'] == 'Y') {
		$_partners = array();
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat=2 order by corporate_name asc");
        foreach ($pres as $ptn) {
			$_partners[$ptn['no']] = stripslashes($ptn['corporate_name']);
		}
		if(!$data['partner_type']) $data['partner_type'] = '0';
	}

?>
<form name="mngGiftFrm" id="mngGiftFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onSubmit="return checkPrdGiftReg(this)">
	<input type="hidden" name="body" value="promotion@product_gift_register.exe">
	<input type="hidden" name="gno" value="<?=$gno?>">
	<div class="box_title first">
		<h2 class="title">사은품 기본정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">사은품 기본정보</caption>
		<colgroup>
			<col style="width:15%">
		<colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use" value="Y" <?=checked($data['use'],"Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="use" value="N" <?=checked($data['use'],"N")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>사은품명</strong></th>
			<td><input type="text" name="name" value="<?=inputText(stripslashes($data['name']))?>" class="input block"></td>
		</tr>
		<tr>
			<th scope="row">상품 연결</th>
			<td>
				<input type="hidden" name="complex_no" value="<?=$data['complex_no']?>" class="input complex_no">
				<span class="box_btn_s"><input type="button" value="검색" onclick="psearch.open();"></span>
				<span class="box_btn_s gray btn_release <?=$release_btn?>"><input type="button" value="해제" onclick="releaseComplex();"></span>
				<span class="p_color prd_name"><?=$pname?></span>
				<ul class="list_info pt">
					<li>상품등록 시 '재고관리'를 사용중인 상품에 한해 연결이 가능합니다.</li>
					<li>사은품 지급 시 재고가 차감되며, 재고가 없을 경우 사은품이 표시되지 않습니다.</li>
					<li>사은품 지급 시 일반상품과 동일하게 별도로 취소/환불/반품 등의 관리가 가능합니다.</li>
					<li>연결 된 사은품을 상품 목록에서 보이지 않게 하시려면 관리자 > 상품관리 내 노출위치에서 관리가 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">증정 조건</th>
			<td>
				<input type="text" name="price_limit" value="<?=$data['price_limit']?>" class="input" size="10"> 원 이상
				<input type="text" name="price_max" value="<?=$data['price_max']?>" class="input" size="10"> 원 이하 구매시
				<span class="explain">(미입력 시 무제한)</span>
				<ul class="list_info pt">
					<li>주문서의 실결제금액에서 배송비를 제외한 금액입니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">증정 대상</th>
			<td>
				<label><input type="radio" class="giftConfigEvent" name="order_gift_member" value="N" <?=checked($data['order_gift_member'],"N").checked($data['order_gift_member'],"")?>> 모든 고객</label>
				<label><input type="radio" class="giftConfigEvent" name="order_gift_member" value="Y" <?=checked($data['order_gift_member'],"Y")?>> 회원만</label>(
				<label><input type="checkbox" value="Y" name="order_gift_first" <?=checked($data['order_gift_first'],'Y')?>/>가입 후 첫 구매시만 증정)</label>
			</td>
		</tr>
		<?if($cfg['use_partner_shop'] == 'Y') {?>
		<tr>
			<th scope="row">적용 입점사</th>
			<td>
				<label class="p_cursor"><input type="radio" name="partner_type" value="0" <?=checked($data['partner_type'], '0')?>> 모든 상품</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="1" <?=checked($data['partner_type'], '1')?>> 본사 상품만</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="2" <?=checked($data['partner_type'], '2')?>> 지정 입점사 상품만</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="3" <?=checked($data['partner_type'], '3')?>> 본사 및 지정 입점사 상품</label>
			</td>
		</tr>
		<tr>
			<th scope="row">입점사 선택</th>
			<td>
				<?=selectArray($_partners, 'partner_no', null, ':: 선택 ::', $data['partner_no'])?>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">대상상품</th>
			<td>
				<ul class="setTarget">
					<li><label><input type="radio" name="attach_type" value="0" <?=checked($data['attach_type'], '0')?> onclick="settarget(0)"> 전체 적용</label></li>
					<li>
						<label><input type="radio" name="attach_type" value="1" <?=checked($data['attach_type'], '1')?> onclick="settarget(1)"> 지정 카테고리 적용</label>
						<span class="box_btn_s"><input type="button" <?=$disabled?> value="적용대상 확인/선택" onclick="selectTarget(1)"></span>
					</li>
					<li>
						<label><input type="radio" name="attach_type" value="2" <?=checked($data['attach_type'], '2')?> onclick="settarget(2)"> 지정 상품 적용</label>
						<span class="box_btn_s"><input type="button" <?=$disabled?> value="적용대상 확인/선택" onclick="selectTarget(2)"></span>
					</li>
					<li style="display:none;"><input type="radio" name="attach_type" value="3"></li>
					<li style="display:none;"><input type="radio" name="attach_type" value="4"></li>
				</ul>
				<input type="hidden" name="attach_items_1" value="<?=$attach_items_1?>">
				<input type="hidden" name="attach_items_2" value="<?=$attach_items_2?>">
				<input type="hidden" name="attach_items_5" value="<?=$attach_items_5?>">
			</td>
		</tr>
		<tr>
			<th scope="row">증정기간</th>
			<td>
				<p style="margin-bottom: 10px">
					<label><input type="checkbox" name="no_date" value="Y" <?=$no_date_chk?> onclick="noDate(this)"> 무제한</label>
				</p>
				<input type="text" name="sdate" value="<?=$sdate?>" class="input datepicker" size="8"> ~
				<input type="text" name="edate" value="<?=$edate?>" class="input datepicker" size="8">
				<ul class="list_info">
					<li>사은품 설정에서 증정기간이 설정된 경우 전체 증정기간 설정과 상품별 증정기간 설정이 모두 해당될 경우에만 사은품 선택이 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>사진</strong></th>
			<td><input type="file" name="upfile" class="input input_full"> <?=$upfile_link?></td>
		</tr>
		<tr>
			<th scope="row">메모</th>
			<td><textarea name="content" class="txta"><?=stripslashes($data[content])?></textarea></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='<?=$listURL?>'"></span>
	</div>
</form>
<script type="text/javascript">
var psearch = new layerWindow('erp@erp_inc.exe&instance=psearch');
psearch.psel = function(complex_no) {
	var _this = this;
	$.get('./index.php', {'body':'promotion@product_gift_register.exe', 'from_ajax':'true', 'exec':'getPrdName', 'complex_no':complex_no}, function(name) {
		$('.complex_no').val(complex_no);
		$('.prd_name').html(name);
		$('.btn_release').removeClass('hidden');
		_this.close();
	});
}

function releaseComplex() {
	$('.complex_no').val('');
	$('.prd_name').html('');
	$('.btn_release').addClass('hidden');
}

function noDate(o) {
	if(o.checked == true) {
		o.form.sdate.disabled = true;
		o.form.edate.disabled = true;
		o.form.sdate.style.background = '#f2f2f2';
		o.form.edate.style.background = '#f2f2f2';
		o.form.sdate.value = '';
		o.form.edate.value = '';
	} else {
		o.form.sdate.disabled = false;
		o.form.edate.disabled = false;
		o.form.sdate.style.background = '';
		o.form.edate.style.background = '';
	}
}

// 할인대상 선택변경 시 실행
function settarget(val) {
	$('.setTarget>li').find('.box_btn_s').addClass('hidden');
	$('.setTarget>li').eq(val).find('.box_btn_s').removeClass('hidden');
}

var targetSelector = new layerWindow();
targetSelector.msel = function(json) {
	$(':input[name=member_id]').val(json.member_id);
	setAddr(json);
	this.close();
}

function selectTarget(val) {
	switch(val) {
		case 1 :
			targetSelector.body  = 'promotion@coupon_category_inc.exe'
		break;
		case 2 :
			targetSelector.body  = 'promotion@coupon_product_inc.exe'
		break;
		case 5 :
			targetSelector.body  = 'promotion@coupon_partner_inc.exe'
		break;
	}
	targetSelector.body += '&case='+val;
	targetSelector.open();
}

function setTargetValue(val) {
	var data = '';
	$('.category_items:checked').each(function() {
		data += '['+this.value+']';
	});
	data = data.replace(/^@/, '');

	$('input[name=attach_items_'+val+']').val(data);

	return false;
}

function setTargetPrd(pno, val) {
	var input = $('input[name=attach_items_'+val+']');
	data = input.val();
	data = data.replace('['+pno+']', '')+'['+pno+']';
	input.val(data);

	reloadTargetPrd(val);
}

function resetTargetPrd(pno, val) {
	if(!confirm('선택한 상품을 선택취소하시겠습니까?')) return false;
	var input = $('input[name=attach_items_'+val+']');
	data = input.val();
	data = data.replace('['+pno+']', '');
	input.val(data);

	reloadTargetPrd(val);
}

function reloadTargetPrd(val) {
	var data = $('input[name=attach_items_'+val+']').val();
	$.post('?body=promotion@coupon_product_inc.exe&exec=selected&case='+val+'&var='+data, function(result) {
		$('#selectedPrds').find('ul').html(result);
	});
}

$(function() {
	noDate($('[name=no_date]')[0]);
	settarget(<?=$data['attach_type']?>);
});

function checkGiftConfigFrm(f) {
	if(f.order_gift_member.value == 'N') {
		f.order_gift_first.disabled = true;
	} else {
		f.order_gift_first.disabled = false;
	}
}

$('.giftConfigEvent', document.getElementById('mngGiftFrm')).click(function() {
	checkGiftConfigFrm(this.form);
});

checkGiftConfigFrm(document.getElementById('mngGiftFrm'));
</script>