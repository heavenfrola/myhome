<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배송비 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['delivery_free_milage'] != 'Y') $cfg['delivery_free_milage'] = 'N';
	if(empty($cfg['use_prd_dlvprc']) == true) $cfg['use_prd_dlvprc'] = 'N';
    $scfg->def('adddlv_type', '2');

	${'select_'.$cfg['adddlv_type']} = 'on';
	${'active_'.$cfg['adddlv_type']} = 'active';

    addField($tbl['delivery_area_detail'], 'ri', 'text after dong');
    if ($scfg->comp('use_partner_shop', 'Y')) {
    	addField($tbl['partner_delivery'], "delivery_prd_free2", "char(1) not null default 'N'");
    }

?>

<style>
	.Lfloat{float:left;}
	.Rfloat{float:right;}
	.clear{clear:both;}
	.left{text-align:left;}
	.file_input_hidden {position:absolute; left:0; top:5px; z-index:5; height:35px; opacity:0; filter: alpha(opacity=0); -ms-filter: "alpha(opacity=0)"; -khtml-opacity:0; -moz-opacity:0; cursor:pointer;border:1px solid red;width:120px;}
	.file_input_button{position:relative;}
</style>

<!-- 배송정책 설정 -->
<form id="deliveryFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="delivery">
	<div class="box_title first">
		<h2 class="title">배송정책 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배송비 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">배송비 유형</th>
			<td>
				<select name="delivery_type" onchange="setDeliveryType();">
					<option value="1" <?=checked($cfg['delivery_type'], '1', true)?>>무료 배송</option>
					<option value="2" <?=checked($cfg['delivery_type'], '2', true)?>>착불 배송</option>
					<option value="3" <?=checked($cfg['delivery_type'], '3', true)?>>금액별 배송</option>
					<option value="6" <?=checked($cfg['delivery_type'], '6', true)?>>고정 배송</option>
				</select>
			</td>
		</tr>
		<tr class="delivery_type_desc type1 hidden">
			<th scope="row">무료 배송</th>
			<td>
				구매 금액 및 구매 건수와 상관없이 0원
			</td>
		</tr>
		<tr class="delivery_type_desc type2 hidden">
			<th scope="row">착불 배송</th>
			<td>
				착불 배송비 <input type="text" name="dlv_fee2" value="<?=parsePrice($cfg['dlv_fee2'])?>" size="10" class="input"> <?=$cfg['currency_type']?>
				<div class="explain">
					네이버페이 사용 시 기본 배송비 설정을 착불로 사용할 경우 반드시 착불 배송비를 입력해주세요.
				</div>
			</td>
		</tr>
		<tr class="delivery_type_desc type3 hidden">
			<th scope="row">금액별 배송</th>
			<td>
				<ul>
					<li><label><input type="radio" name="delivery_base" value="1" <?=checked($cfg['delivery_base'], '1')?>> 주문금액(할인 전 판매가 기준)</label></li>
					<li>
						<label><input type="radio" name="delivery_base" value="2"  <?=checked($cfg['delivery_base'], '2')?>> 결제금액(최종 결제금액 기준)</label>
						<?if($cfg['use_partner_delivery'] == 'Y') { // 입점몰 사용시 해당 설정 사용 불가?>
						<input type="hidden" name="delivery_free_milage" value="N">
						<?} else {?>
						<div style="margin-left: 25px;">
							<p>└ <label><input type="checkbox" name="delivery_free_milage" value="Y" <?=checked($cfg['delivery_free_milage'],"Y")?>> 사용 적립금</label></p>
							<p class="explain">예) 50,000원 미만일 경우 배송비 2,500원 부과시 50,000원의 상품을 구매하고 적립금을 1,000원 사용한 경우</p>
							<ul class="list_msg">
								<li>사용 적립금 체크 : 배송비 0원</li>
								<li>사용 적립금 미체크 : 배송비 2,500원 부과</li>
							</ul>
						</div>
						<?}?>
					</li>
				</ul><br>

				<table class="tbl_inner line full">
					<thead>
						<tr>
							<th>금액 범위</th>
							<th>배송비</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="left">
								<input type="text" name="delivery_free_limit_s" class="input" size="10" value="0" disabled> <?=$cfg['currency_type']?> 이상 ~
								<input type="text" name="delivery_free_limit" class="input" size="10" value="<?=$cfg['delivery_free_limit']?>"> <?=$cfg['currency_type']?> 미만
							</td>
							<td class="left">
								<input type="text" name="delivery_fee" value="<?=parsePrice($cfg['delivery_fee'])?>" class="input" size="10"> <?=$cfg['currency_type']?>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr class="delivery_type_desc type6 hidden">
			<th scope="row">고정 배송</th>
			<td>
				구매금액 및 구매건수와 상관없이 <input type="text" name="dlv_fee3" value="<?=parsePrice($cfg['dlv_fee3'])?>" size="10" class="input"> <?=$cfg['currency_type']?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- // 배송정책 설정 -->

<?php if (!$partner_delivery) { ?>
<!-- 개별 배송비 설정 -->
<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="prd_dlvprc">
	<div class="box_title">
		<h2 class="title">개별 배송비 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">개별 배송비 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th>
                개별 배송비
                <?php if ($scfg->comp('use_prd_dlvprc', 'Y') == true) { ?>
                <a href="?body=config@delivery_set" class="sclink3">설정</a>
                <?php } ?>
            </th>
			<td>
				<label><input type="radio" name="use_prd_dlvprc" value="Y" <?=checked($cfg['use_prd_dlvprc'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_prd_dlvprc" value="N" <?=checked($cfg['use_prd_dlvprc'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<?if(fieldExist($tbl['product'], 'delivery_set') == false) {?>
	<div class="box_middle2">
		<ul class="list_info left">
			<li>설정 시 주문 데이터베이스를 마이그레이션 합니다. 누적 주문이 많은 사이트의 경우 몇 분정도 주문이 안되거나 사이트가 느려질수 있으므로 유휴시간대에 설정해 주시기 바랍니다.</li>
		</ul>
	</div>
	<?}?>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- // 개별 배송비 설정 -->
<?php } ?>

<?
	if(!$cfg['adddlv_type']) $cfg['adddlv_type'] = 1;
	$dlvhide[1] = $cfg['adddlv_type'] == 1 ? 'block' : 'none';
	$dlvhide[2] = $cfg['adddlv_type'] == 2 ? 'block' : 'none';
	$dlvsel[$cfg['adddlv_type']] = 'selected';

?>
<div class="box_title">
	<h2 class="title">지역별 추가배송비 설정</h2>
</div>
<div class="box_bottom top_line left">
	<ul class="list_msg">
		<li>지역명으로 간단히 설정하시려면 '간편설정'을 시, 구, 동별 세부설정을 원하시면 '세부 설정'을 이용하세요.</li>
		<li>간편설정과 세부설정은 동시에 사용하실수 없으며, 선택된 설정 방식만 적용됩니다.</li>
		<?if($has_zipdb != true) {?>
		<li>세부 설정은 별도의 우편번호 DB가 있어야 사용 가능합니다.</li>
		<?}?>
	</ul>
</div>
<div id="controlTab">
	<form name="deliveryFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data">
	<input type="hidden" name="body" value="config@delivery.exe">
	<input type="hidden" name="exec" value="excel">
	<div id="select_config" class="box_tab first">
		<ul>
			<li class="ctab_1"><a id="ctab_1" href="#" onclick="return setDlvType(1);" class="<?=$active_1?>">간편설정<span class="toggle <?=$select_1?>"><?=strtoupper($select_1)?></span></a></li>
			<li class="ctab_2"><a id="ctab_2" href="#" onclick="return setDlvType(2);" class="<?=$active_2?>">세부설정<span class="toggle <?=$select_2?>"><?=strtoupper($select_2)?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon copy2"><input type="button" value="엑셀업로드"><input type="file" name="excel_file" class="file_input_hidden" onchange="upExcelFile('<?=$_GET['delivery_com']?>')"></span>
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=config@add_delivery_excel.exe'"></span>
		</div>
	</div>
	</form>
	<div class="context">
		<div class="box_bottom left">
			<form id="edt_layer_1" name="deliveryFrm2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return confirm('현재 설정을 저장하시겠습니까?');" style="display:<?=$dlvhide[1]?>">
				<input type="hidden" name="body" value="config@delivery.exe">
				<input type="hidden" name="exec" value="area">
				<input type="hidden" name="config_code" value="delivery_addprice">
				<ul class="list_msg">
					<li>지역별로 배송비를 차등적용하고 싶으실 경우 원하시는 지역을 콤마(,)로 구분하여 입력후 추가/할인 배송비를 입력해주세요. 예) 서울,경기 : -1000</li>
					<li>단, 입력하시기 전에 먼저 <a href="javascript:;" onclick="zipSearch();"><u>우편번호 검색</u></a>으로 <span class="desc3">지역명을 확인</span>하신 후 입력해주시기 바랍니다.</li>
					<li>엑셀 업로드 이용 시, 다운로드 받아 수정한 엑셀을 .csv 형식으로 저장하여 업로드해 주시기 바랍니다.</li>
				</ul>
				<p><label class="p_cursor"><input type="checkbox" name="free_delivery_area" value="Y" id="free_delivery_area" <?=checked($cfg['free_delivery_area'],"Y")?>> 무료배송 시에도 지역별 추가배송비 부과</label></p>
				<table id="areaTd" class="tbl_col tbl_col_bottom">
					<colgroup>
						<col style="width:70px">
						<col>
						<col style="width:140px">
						<col style="width:70px">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">번호</th>
							<th scope="col">지역명 (도서,산간등)</th>
							<th scope="col">추가배송비</th>
							<th scope="col">삭제</th>
						</tr>
					</thead>
					<tbody>
						<?php
							// 2007-04-05 : 지역별 배송료 설정 - Han
							$areaNum=$pdo->row("select count(`no`) from `$tbl[delivery_area]` where partner_no='$admin[partner_no]'");
							if(!$areaNum) $areaNum=1;
							for($ii=1,$jj=0; $ii<=$areaNum; $ii++,$jj++){
								$afee = $pdo->assoc("select * from `$tbl[delivery_area]` where partner_no='$admin[partner_no]' order by `no` limit $jj,$ii");
								$arealen=strlen($afee[area]);
								if($arealen > 2) $afee[area]=substr($afee[area], 1, $arealen-2);
						?>
						<tr>
							<td class="center">
								<?=$ii?>
								<input type="hidden" name="no[]" value="<?=$afee['no']?>">
							</td>
							<td><input type="text" name="area[]" class="input" style="width:95%" value="<?=$afee[area]?>"></td>
							<td><input type="text" name="price[]" class="input" size="10" value="<?=$afee[price]?>"> <?=$cfg['currency_type']?></td>
							<td><span class="box_btn_s"><input type="button" value="삭제" onClick="removePolicy(this);"></span></td>
						</tr>
						<?}?>
					</tbody>
				</table>
				<div class="box_middle2 left">
				<span class="box_btn blue"><input type="button" value="추가" onclick="jsAddArea();"></span>
				</div>
			</form>
			<?php
				if(!isTable($tbl['delivery_area_detail'])) {
					include $engine_dir.'/_config/tbl_schema.php';
					$pdo->query($tbl_schema['delivery_area_detail']);
				}
				include_once $engine_dir.'/_manage/config/delivery2.exe.php';
			?>
			<form id="edt_layer_2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:<?=$dlvhide[2]?>">
				<input type="hidden" name="body" value="config@delivery2.exe">
				<input type="hidden" name="exec" value="update">
				<input type="hidden" name="sido" value="">
				<input type="hidden" name="gugun" value="">
				<input type="hidden" name="dong" value="">
				<input type="hidden" name="ri" value="">
				<input type="hidden" name="no" value="">
				<ul class="list_msg">
					<li>원하는 지역을 선택하고 추가배송비를 입력하면 해당지역에 추가배송비가 부과됩니다.</li>
					<li>하나의 지역을 두개 이상의 추가배송비 지역으로 등록했을 경우 먼저 등록된 것을 우선으로 적용합니다.</li>
					<li>행정구역변경으로 지명이 변경되었을 경우 반드시 함께 변경을 하셔야 합니다.</li>
					<li>엑셀 업로드 이용 시, 행정구역만 기재해주셔야 합니다. ex) 강원 전체인 경우 '시/도' 필드에 '강원' 만 입력</li>
					<li>엑셀 업로드 이용 시, 다운로드 받아 수정한 엑셀을 .csv 형식으로 저장하여 업로드해 주시기 바랍니다.</li>
				</ul>
				<p>
					<label class="p_cursor"><input type="checkbox" id="fda" value="Y" id="free_delivery_area" <?=checked($cfg['free_delivery_area'],"Y")?>> 무료배송 시에도 지역별 추가배송비 부과</label>
					<span class="box_btn_s blue"><input type="button" value="저장" onclick="setfda()"></span>
				</p>
				<table class="tbl_col tbl_col_bottom">
					<colgroup>
						<col style="width:200px">
						<col>
						<col style="width:100px">
						<col style="width:70px">
						<col style="width:70px">
						<col style="width:90px">
						<col style="width:130px">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">배송지별칭</th>
							<th scope="col">지역</th>
							<th scope="col">추가배송비</th>
							<th scope="col">수정</th>
							<th scope="col">삭제</th>
							<th scope="col">우선순위</th>
							<th scope="col">등록일</th>
						</tr>
					</thead>
					<tbody id="d_list">
						<?=getDeliveryArea()?>
					</tbody>
				</table>
				<table class="tbl_col" style="margin:10px 0;">
					<colgroup>
						<col style="width:220px">
						<col>
						<col style="width:150px">
						<col style="width:110px">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">배송지별칭</th>
							<th scope="col">지역</th>
							<th scope="col">추가배송비</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><input type="text" name="ad2_name" class="input" size="25" value=""></td>
							<td id="seletedZip">아래에서 지역을 선택해 주세요.</td>
							<td><input type="text" name="ad2_prc" class="input" size="15" value=""></td>
							<td class="ad2_btns">
								<span class="box_btn_s blue dlv_btn1"><input type="submit" value="추가"></span>
								<span class="box_btn_s gray dlv_btn2"><input type="button" onclick="reloadDeliveryArea();" value="취소" style="display:none;"></span>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="addrbox_frame">
					<ul id="sido" class="addrbox">
						<?=getAddr('sido')?>
					</ul>
					<ul id="gugun" class="addrbox">
					</ul>
					<ul id="dong" class="addrbox">
					</ul>
					<ul id="ri" class="addrbox">
					</ul>
				</div>
			</form>
		</div>
		<?if($cfg['adddlv_type'] != 2) {?>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="button" onclick="adddlvConfig();" value="확인"></span>
			<div class="process"></div>
		</div>
		<?}?>
	</div>
</div>

<?php require __ENGINE_DIR__.'/_manage/config/delivery_range.php'; ?>

<script language="JavaScript">
	function editDlvList(o){
		f=o.form;
		idx=o.selectedIndex;
		tmp=o[idx].text.split("http");
		f.name.value=tmp[0];
		f.url.value="http"+tmp[1];
		f.no.value=o[idx].value;
		f.btn10.value="수정하기";
	}

	var num=<?=$areaNum?>;
	function jsAddArea(){
		num++;
		var table = document.getElementById("areaTd");
		var tr = document.createElement('TR');
		$(table).find('tbody').append(tr);

		var td1 = document.createElement('TD');
		var td2 = document.createElement('TD');
		var td3 = document.createElement('TD');
		var td4 = document.createElement('TD');
		var td5 = document.createElement('TD');

		td1.innerHTML = num;
		td1.className = 'center';
		td2.innerHTML = "<input type='text' name='area[]' class='input' style='width:95%' value=''>";
		td3.innerHTML = "<input type='text' name='price[]' class='input' size='10' value=''> <?=$cfg['currency_type']?>";
		td4.innerHTML = "<span class='box_btn_s'><input type='button' value='삭제' onClick='removePolicy(this);'></span>";

		tr.appendChild(td1);
		tr.appendChild(td2);
		tr.appendChild(td3);
		tr.appendChild(td4);

	}
	function removePolicy(o) {
		if(confirm('삭제하시겠습니까?')) {
			$(o).parents('tr').eq(0).remove();
			$('input[name="no[]"]').eq(0).val('0');
		}
	}

	function setDeliveryType() {
		var f = document.querySelector('#deliveryFrm');
		var delivery_type = $(':selected', f.delivery_type).val();
		$('.delivery_type_desc').not('.type'+delivery_type).addClass('hidden');
		$('.delivery_type_desc.type'+delivery_type).removeClass('hidden');

		deliveryCheck();
	}

	function deliveryCheck(){
		var f = document.getElementById('deliveryFrm');
		if($('select[name=delivery_type]').val() == '3') {
			textDisable(f.delivery_free_limit,'');
			textDisable(f.delivery_fee,'');
		} else {
			textDisable(f.delivery_free_limit,'1');
			textDisable(f.delivery_fee,'1');
		}
	}

	$(function() {
		setDeliveryType();
		addDelivery2();
	});

	// 추가배송비 세부설정
	var adFrm = document.getElementById('edt_layer_2');
	function addDelivery2(target) {
		if(!target) target = 'sido';
		if(target == 'ri') return;

		$('#'+target).find('label').on('click', function() {
            var o = $(this).parents('li').eq(0);

			if(target != 'ri') {
				o.parents('ul').find('.selected').removeClass('selected');
				o.addClass('selected');
                if(target != 'dong') {
    				adFrm.elements[target].value = o.text();
                }
			}

			var sido = $('#sido>li').filter('.selected').text();
			var gugun = $('#gugun>li').filter('.selected').text();
			var dong = $('#dong>li').filter('.selected').text();
			var ri = $('#ri>li').filter('.selected').text();

			if(target == 'gugun' && o.index() == 0) {
				gugun = '';
				adFrm.gugun.value = '';
			}

			var next_child = null;
			switch(target) {
				case 'sido' :
					next_child = 'gugun';
					adFrm.gugun.value = '';
					adFrm.dong.value = '';
					$('#gugun').html('');
					$('#dong').html('');
					$('#ri').html('');
				break;
				case 'gugun' :
					next_child = 'dong';
					adFrm.dong.value = '';
					$('#dong').html('');
					$('#ri').html('');
					if(o.hasClass('all') == true) {
						setZip();
						return;
					}
				break;
				case 'dong' :
					next_child = 'ri';
					adFrm.ri.value = '';
					$('#ri').html('');
					if(o.hasClass('all') == true) {
						setZip();
						return;
					}
                    if (o.find(':checkbox').prop('checked') == false) {
                        return;
                    }
				break;
			}

			if(next_child) {
				$.post('?body=config@delivery2.exe', {"exec":"getAddr", "next_child":next_child, "sido":sido, "gugun":gugun, "dong":dong}, function(result) {
					$('#'+next_child).html(result);
					addDelivery2(next_child);
				});
			}
			setZip();
		});
	}

	function setDong(o, target) {
		if($(o).parents('li.all').length == 1) {
			$('#'+target).find(':checkbox').not(o).prop('checked', false);
		} else {
            // 리 선택모드에서 동 중복선택 모드로 변경
            if (target == 'dong') {
                if ($('#ri>li:not(.all)').find(':checked').length > 0) {
                    $('#dong').find(':checked').not(o).prop('checked', false);
                }
            }

            // 리 중복 선택 모드
			$('#'+target).find('.all').find(':checkbox').prop('checked', false);
            if (target == 'ri') {
                var sel = $('#dong>li.selected').find(':checkbox');
    			$('#dong').find(':checkbox').not(sel).prop('checked', false);
                adFrm['dong'].value = sel.val();
            }
  			$('#'+target+'>li.all').find(':checkbox').prop('checked', false);
		}

		var tmp = '';
		$('#'+target).find(':checked').each(function() {
			tmp += ","+this.value;
		});
		tmp = tmp.replace(/^,/, '');
		adFrm[target].value = tmp;

		setZip();
	}

	function setZip() {
		var zip = '아래에서 지역을 선택해 주세요.';
		if(adFrm.sido.value) zip = adFrm.sido.value;
		if(adFrm.gugun.value) zip += ' '+adFrm.gugun.value;
		if(adFrm.dong.value) {
			var d = adFrm.dong.value.split(',');
			if(d.length == 1) zip += ' '+adFrm.dong.value;
			else zip += ' '+d[0]+' 외 '+(d.length-1);
		}
		if(adFrm.ri.value) {
			var d = adFrm.ri.value.split(',');
			if(d.length == 1) zip += ' '+adFrm.ri.value;
			else zip += ' '+d[0]+' 외 '+(d.length-1);
		}
		if(adFrm.sido.value) {
			if(!adFrm.gugun.value) zip += ' 전체';
			else if(!adFrm.dong.value) zip += ' 전체';
		}
		$('#seletedZip').html(zip);
	}

	function reloadDeliveryArea() {
		adFrm.gugun.value = '';
		adFrm.dong.value = '';
		adFrm.ri.value = '';
		adFrm.sido.value = '';
		$('#gugun').html('');
		$('#dong').html('');
		$('#ri').html('');
		$('input[name="no"]').val('');
		$('#sido>li').removeClass('selected');

		$.post('?body=config@delivery2.exe', {"exec":"reload"}, function(result) {
			$('#d_list').html(result);
			adFrm.reset();
			parent.setZip();
		});

		$('.ad2_btns input[type="submit"]').val('추가');
		$('.ad2_btns input[value="취소"]').hide();
	}

	function removeDeliveryArea(no) {
		if(!confirm('선택한 배송지설정을 삭제하시겠습니까?')) return false;
		$.post('?body=config@delivery2.exe', {"exec":"remove", "no":no}, function() {
			reloadDeliveryArea();
		});
	}

	function modifyDeliveryArea(no) {
		$.post('?body=config@delivery2.exe', {"exec":"modify", "no":no}, function(json) {
			$('#seletedZip').html(json.area);
			$('input[name="ad2_name"]').val(json.name);
			$('input[name="ad2_prc"]').val(json.addprc);

			$('#sido>li').removeClass('selected');

			$('#sido').html(json.sido);
			$('#gugun').html(json.gugun);
			$('#dong').html(json.dong);
            /*
			addDelivery2('sido');
			addDelivery2('gugun');
            */

			$('input[name="no"]').val(json.no);
			$('input[name="sido"]').val(json.sido);
			$('input[name="gugun"]').val(json.gugun);
			$('input[name="dong"]').val(json.dong);
			$('input[name="ri"]').val(json.ri);

			$('.ad2_btns input[type="submit"]').val("수정");
			$('.ad2_btns input[type="button"]').show();

			$('#sido').html(json.sido_list);
			$('#gugun').html(json.gugun_list);
			$('#dong').html(json.dong_list);
			$('#ri').html(json.ri_list);

			addDelivery2('sido');
			addDelivery2('gugun');
			addDelivery2('dong');
		});
	}

	function sortDeliveryArea(no, dir, obj) {
		var obj = $(obj).parents('tr').eq(0);
		var target = (dir > 0) ? obj.next() : obj.prev();
		if(target.length == 0) {
			window.alert('우선순위 변경이 불가능합니다.');
			return;
		}

		$.post('?body=config@delivery2.exe', {"exec":"sort", "no":no, "dir":dir}, function(r) {
			if(dir < 0) {
				target.before(obj);
			} else {
				target.after(obj);
			}
		});
	}

	function setDlvType(type) {

		if(confirm('\'확인\'을 누르시면 설정이 즉시 변경됩니다.\n지역별 추가배송비 설정을 변경하시겠습니까?')) {
			$.post('?body=config@config.exe', {"adddlv_type":type}, function() {
				location.reload(true);
			});
		}
		return false;
	}

	function setfda() {
		var val = $('#fda').prop('checked') == true ? 'Y' : 'N';
		$.post('?body=config@config.exe', {"free_delivery_area":val, "from_ajax":true}, function() {
			window.alert('설정이 적용되었습니다.');
		});
	}

	// 엑셀 업로드
	function upExcelFile(){
		var f = document.deliveryFrm3;

		if(confirm("지역별 추가배송비 설정을 업로드 하시겠습니까?")){
			f.submit();
		} else {
			f.excel_file.value = "";
		}
	}

	function adddlvConfig(){
		var f = document.deliveryFrm2;

		if(confirm("현재 설정을 저장하시겠습니까?")){
			f.submit();
		} else {
			f.excel_file.value = "";
		}
	}
</script>