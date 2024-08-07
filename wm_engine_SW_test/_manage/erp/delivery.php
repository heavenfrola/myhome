<?PHP

	$_seller = array();
	$prql = $pdo->iterator("select no, name from `$tbl[delivery_url]` where partner_no=0 or isnull(partner_no) order by `sort` asc, `no` desc");
    foreach ($prql as $prdata) {
		$_dlv_no[$prdata['no']] = stripslashes($prdata['name']);
	}

	if(!$_COOKIE['erp_delivery']) $_COOKIE['erp_delivery'] = 'N';
	$erp_delivery = ($_GET['exec'] == 'm') ? $_COOKIE['erp_delivery'] : 'Y';
	if(!$erp_delivery) $erp_delivery = 'N';

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/erp/js/log.js"></script>
<div id="safediv">
	<form id="safedivFrm" method="post" action="" target="hidden<?=$now?>" onsubmit="return delivery(this);">
		<div class="box_title first">
			<h2 class="title">출고 정보</h2>
		</div>
		<table class="tbl_row">
			<caption class="hidden">출고 정보</caption>
			<colgroup>
				<col style="width:15%">
				<col style="width:35%">
				<col style="width:15%">
				<col style="width:35%">
			</colgroup>
			<tr>
				<th scope="row">운송장/상품바코드</th>
				<td>
					<?=selectArray($_dlv_no, 'dlv_no', 2, '', $dlv_no)?>
					<input type="text" name="dlv_code" value="" class="input" size="20" onfocus="this.select();">
					<span class="box_btn_s blue"><input type="submit" value="확인"></span>
				</td>
				<th scope="row">확인 후 상태</td>
				<td>
					<?if($_GET['exec'] == 'm') {?>
					<label class="p_cursor"><input type="radio" name="check_option" value="N" onclick="setConfig('erp_delivery',this.value)" <?=checked($erp_delivery, 'N')?>> 상태 변경하지 않음</label>
					<label class="p_cursor"><input type="radio" name="check_option" value="M" onclick="setConfig('erp_delivery',this.value)" <?=checked($erp_delivery, 'M')?>> 변경 여부 매번확인</label>
					<?} else {?>
					<label class="p_cursor"><input type="radio" name="check_option" value="Y" onclick="setConfig('erp_delivery',this.value)" <?=checked($erp_delivery, 'Y')?>> 자동으로 배송중으로 변경</label>
					<?}?>
				</td>
			</tr>
			<tr>
				<td id="dlv_mbox" colspan="4"></td>
			</tr>
			<tr class="invoice_info" style="display:none;">
				<th scope="row">주문번호</th>
				<td id="order_no"></td>
				<th scope="row">입금일시</th>
				<td id="date2"></td>
			</tr>
			<tr class="invoice_info" style="display:none;">
				<th scope="row">수취인명<input type="hidden" id="invoice_no"></th>
				<td id="addressee"></td>
				<th scope="row">휴대폰번호</th>
				<td id="cell"></td>
			</tr>
			<tr class="invoice_info" style="display:none;">
				<th scope="row">주소</th>
				<td id="addr"></td>
				<th scope="row">총결제금액</th>
				<td id="total_prc"></td>
			</tr>
		</table>
	</form>

	<form id="prdFrm" name="prdFrm" method="post" action="./index.php" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="">
		<input type="hidden" name="exec" value="">
		<div class="box_title">
			<h2 class="title">출고상품 목록</h2>
		</div>
		<table class="tbl_col">
			<caption class="hidden">출고상품 목록</caption>
			<colgroup>
				<col>
				<col style="width:140px">
				<col style="width:120px">
				<col style="width:60px">
				<col style="width:100px">
				<col style="width:100px">
				<col style="width:100px">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">상품명</th>
					<th scope="col">바코드</th>
					<th scope="col">옵션</th>
					<th scope="col">수량</th>
					<th scope="col">판매가</th>
					<th scope="col">결제금액</th>
					<th scope="col">체크</th>
				</tr>
			</thead>
			<tbody id="list_header">
			</tbody>
		</table>
	</form>
	<form id="orderFrm" method="post" action="?" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="order@order_update.exe">
		<input type="hidden" name="ono" value="">
		<input type="hidden" name="exec" value="">
		<input type="hidden" name="ext" value="">
		<input type="hidden" name="no_reload" value="true">
		<input type="hidden" name="mode" value="erp_delivery">
	</form>
	<form id="logFrm" method="post" action="?" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="erp@delivery_log.exe">
		<input type="hidden" name="ono" value="">
		<input type="hidden" name="prefix" value="">
		<input type="hidden" name="cnt" value="0">
		<input type="hidden" name="codes" value="">
	</form>
</div>

<object id="mplayer" CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" style="display:none;">
	<param name="autoStart" value="false">
	<param name="URL" value="">
</object>

<script type="text/javascript">
	var prd_nums = Array();
	var prd_chks = Array();
	var prd_holds = Array();
	function delivery(f) {
		dispMsg();

		if(f.dlv_code.value == '') {
			playwav('input');
			dispMsg('no', '운송장 번호 또는 상품바코드를 입력해주세요');
			f.dlv_code.focus();
			return false;
		}
		var codes = document.getElementsByName('idlv_code');
		for(var i = 0; i < codes.length; i++) {
			if(codes[i].value == f.dlv_code.value) {
				playwav('selectedOrd');
				dispMsg('no', '이미 선택 된 운송장 번호입니다.');
				return false;
			}
		}

		var tr = $("#list_header>tr").eq(0);
		if(chk_prod(tr.next(), f)) {
			return false;
		}

		$.ajax({
			url: '?body=erp@invoice1.exe&dlv_no=' + f.dlv_no.value + '&dlv_code=' + f.dlv_code.value,
			success: function(json) {
				if(json.result != '200') {
					if(json.result != '500') {
						playwav(json.result);
						dispMsg('no', json.msg);
					} else {
						playwav('notExists');
						dispMsg('no', '주문서에 없는 상품입니다!');
					}
				} else {
					playwav('confirm');

					$('.safe_dlv_order').remove();
					$('.prd, .inv_prd').remove();

					$("#order_no").html("<a href='#' onclick='viewOrder(\""+json.order_no+"\"); return false;'>"+json.order_no+"</a>");
					$("#date2").html(json.date2);
					$("#invoice_no").val(json.invoice_no);
					$("#addressee").html(json.name);
					$("#cell").html(json.cell);
					$("#addr").html(json.addr);
					$("#list_header").prepend(json.html);
					$('#total_prc').html(setComma(json.total_prc));
					f.dlv_code.select();

					prd_nums[json.invoice_no] = json.prd_num;
					prd_chks[json.invoice_no] = 0;
					prd_holds[json.invoice_no] = json.hold_num;

					var x = document.getElementById('logFrm');
					x.cnt.value = 0;
					x.codes.value = '';

					$('.invoice_info').show();
				}
			}
		});
		/*
		playwav('error');
		dispMsg('no', '운송장 번호 또는 상품바코드를 입력해주세요');
		*/

		return false;
	}

	function chk_prod(tr, f) {
		if(tr.hasClass("safe_dlv_order")) tr = tr.next();

		if(tr.hasClass("prd")) {
			if(tr.find(".barcode").text() ==  f.dlv_code.value) {
				var pstat = tr.attr('stat').toNumber()

				if(pstat > 10) {
					playwav('part_cancel');
					dispMsg('no', '부분 취소 상품입니다.');
					return true;
				}

				if(pstat > 3 && pstat < 10) {
					playwav('part_dlv');
					dispMsg('no', '부분 배송 상품입니다.');
					return true;
				}
				if(tr.find(".chk").text() == '배송보류') {
					playwav('hold');
					dispMsg('no', '배송보류중인 상품입니다.');
					return true;
				}

				if(tr.find(".chk").html() != '체크') {
					var checked_qty = parseInt(tr.find(".chk").html());
					if(isNaN(checked_qty)) checked_qty = 0;
					tr.find(".chk").html(checked_qty+1)
					$('[name=dlv_code]').select();

					if(tr.find(".chk").html() == tr.find(".qty").html()) {
						tr.find(".chk").html("체크");
						dispMsg('');

						var code = tr.attr('dlvcode');
						var ono = tr.attr('ono');

						prd_chks[code]++;
						if(prd_nums[code]-prd_holds[code] == prd_chks[code]) {
							logCnt(tr.find(".barcode").html());
							playwav('ok');
							dispMsg('ok');
							wdLog(ono,'_s');

							// 완료처리
							var option = $('[name=check_option]:checked').val();
							switch(option) {
								case 'Y' :
									wpDelivery(ono);
								break;
								case 'M' :
									if(confirm(ono+' 주문의 상품이 모두 확인되었습니다.\n주문상태를 \'배송중\'으로 변경하시겠습니까?')) {
										wpDelivery(ono);
									} else {
										wdLog(ono);
									}
									dispMsg();
								break;
								case 'N' :
									wdLog(ono);
								break;
							}
						} else {
							logCnt(tr.find(".barcode").html());
							playwav('confirm');
						}
					} else {
						var ea = checked_qty+1;
						if(ea > 5) playwav('part_prd');
						else playwav('part_prd_'+ea);
					}
				} else {
					playwav('checked');
					dispMsg('no', '이미 체크 된 상품입니다.');
					//return chk_prod(tr.next(), f);
				}
				//dispMsg('no', '운송장 번호 또는 상품바코드를 입력해주세요');
				return true;
			} else {
				return chk_prod(tr.next(), f);
			}
		} else {
			f.dlv_code.select();
		}
		return false;
	}

	function wpDelivery(ono) {
		$.ajax({
			type : 'POST',
			url : './?body=order@order_update.exe',
			data: {'ono':ono, 'exec':'stat', 'ext':4, 'no_reload':'true', 'mode':'erp_delivery'},
			cache : false,
			error : function() {
				window.alert("통신 오류가 발생하였습니다.\n주문서의 상태가 정상적으로 변경되었는지 확인 해 주십시오.");
			},
			success : function(result) {
				viewOrder(ono);
				playwav('stat');
			}
		});
	}

	function logCnt(barcode) {
		var f = document.getElementById('logFrm');
		var cnt = f.cnt.value.toNumber();

		f.cnt.value = cnt+1;
		f.codes.value += '@'+barcode;
	}

	function wdLog(ono, prefix) {
		if(!prefix) prefix = '';

		var f = document.getElementById('logFrm');
		f.ono.value = ono;
		f.prefix.value = prefix;
		f.submit();
	}

	var interval;
	function dispMsg(result, msg) {
		$('[name=dlv_code]').select().focus();

		if(!msg) msg = '';
		clearInterval(interval);

		var mbox = document.getElementById('dlv_mbox');
		if(result == 'no') {
			$(mbox).fadeIn('fast');
			mbox.innerHTML = msg;
			mbox.style.backgroundColor = '#FFFF33';
		} else if(result == 'ok') {
			$(mbox).fadeIn('fast');
			mbox.innerHTML = '안전배송 확인 완료';
			mbox.style.backgroundColor = '#f2f2f2';
		}

		interval = setTimeout(function() {
			$(mbox).fadeOut('fast');
		}, 5000);
	}

	function playwav(msg) {
		var mplayer = document.getElementById('mplayer');
		try {
			mplayer.URL = engine_url+'/_manage/erp/wav/'+msg+'.wma';
			if(mplayer.controls) {
				mplayer.controls.play();
			}
		} catch(ex) {}
	}

	function putBarcode(barcode) {
		var f = document.getElementById('safedivFrm');
		var bak = f.dlv_code.value;

		f.dlv_code.value = barcode;
		delivery(f);
		f.dlv_code.value = bak;
	}

	$(document).ready(function() {
		$('[name=dlv_code]').select();
	});

	$('#safediv').click(function(o) {
		if(o.target.tagName != 'SELECT') {
			$(':input[name=dlv_code]')[0].focus();
		}
	});

	$('select').change(function(o) {
		$(':input[name=dlv_code]')[0].focus();
	});
</script>