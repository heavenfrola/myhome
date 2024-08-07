function setExDlv(prc) {
	$('input[name=ex_dlv_prc]').val(prc);
	autoRepayPrc();
}

function autoRepayPrc() {
	var prc = 0;
	var prc1 = prc2 = prc3 = prc4 = prcm = prce = 0;
	var is_cpn_recalc = $('.autorepayprc[name="cpn_recalc"]').prop('checked');
	var repay_prc_org = $('[name="repay_prc_org\[\]"]');
	var repay_sale5 = $('[name="repay_sale5\[\]"]');
	$('.autorepayprc[name="repay_prc[]"]').each(function(i) {
		var prc = parseFloat(this.value);
		if(!prc) prc = 0;
		if(is_cpn_recalc == true) {
			prc += parseFloat(repay_sale5.eq(i).val());
		}
		//this.value = prc;

		prc1 += parseFloat(prc);
	});

	if(prc1 == 0 && typeof exc_origin_prc != 'undefined') {
		prc1 = exc_new_prc-exc_origin_prc;
	}

	if($('.autorepayprc[name=ex_dlv_prc]').length == 1) {
		prc2 += $('.autorepayprc[name=ex_dlv_prc]').val().toNumber();
	}

	if($('.autorepayprc[name=add_dlv_prc]').length == 1 && $(':checked[name=add_dlv_type]').val() == 2) {
		prc2 += $('.autorepayprc[name=add_dlv_prc]').val().toNumber();
	};

	if($('.autorepayprc[name=repay_dlv_prc]').length == 1) {
		if($(':checked[name=dlv_prc_no_return]').length == 1) prc3 += 0;
		else prc3 += $('.autorepayprc[name=repay_dlv_prc]').val().toNumber();
	};

	if($('.autorepayprc[name=repay_prd_dlv_prc]:checked').length == 1) {
		prc4 = $('.autorepayprc[name=repay_prd_dlv_prc]').val().toNumber();
	};	
	if(prc4 > 0) $('.repay_prc4_area').show();
	else $('.repay_prc4_area').hide();

	if($('.autorepayprc[name=emoney_prc], .autorepayprc[name=emoney_repay]').length == 1) {
		prce = $('.autorepayprc[name=emoney_prc], .autorepayprc[name=emoney_repay]').val().toNumber();
	}

	if($('.autorepayprc[name=milage_prc], .autorepayprc[name=milage_repay]').length == 1) {
		prcm = $('.autorepayprc[name=milage_prc], .autorepayprc[name=milage_repay]').val().toNumber();
	}

	prc = prc1-prc2+prc3+prc4-prce-prcm;

	if(typeof prd_stat_refresh == 'undefined') prd_stat_refresh = 0;
	if(prd_stat_refresh == 0 && prc < 0 && (prce > 0 || prcm > 0)) {
		window.alert('예치금/적립금 사용에 의해 환불할 금액이 0보다 작습니다.\n복구 적립금이나 예치금에서 차감해 주세요.');
		prd_stat_refresh = 1;
	}

	$('.repay_calc').val(prc.toFixed(currency_decimal));
	$('.repay_calc').html(setComma(prc.toFixed(currency_decimal)));
	$('.repay_prc1').html(setComma(prc1.toFixed(currency_decimal)));
	$('.repay_prc2').html(setComma(prc2.toFixed(currency_decimal)));
	$('.repay_prc3').html(setComma(prc3.toFixed(currency_decimal)));
	$('.repay_prc4').html(setComma(prc4.toFixed(currency_decimal)));
	$('.repay_prce').html(setComma(prce.toFixed(currency_decimal)));
	$('.repay_prcm').html(setComma(prcm.toFixed(currency_decimal)));

	showRepayMethod();
}

function showRepayMethod() {
	if($('input.repay_calc').length == 1 && $('input.repay_calc').val().toNumber() != 0) {
		$('.repay_method').show();
		if($(':checked.repay_pay_method').val() != '2') {
			$('.repay_method.bank').css('visibility','hidden');
		} else {
			$('.repay_method.bank').css('visibility','visible');
		}
	}
	else $('.repay_method').hide();
}

function showInputMethod(o) {
	if(o.value != '2') {
		$('.input_method.bank').hide();
	} else {
		$('.input_method.bank').show();
	}
}

function orderPaymentComplete(no) {
	if(confirm('승인하시겠습니까?')) {
		$.post('./index.php?body=order@order_payment.exe', {'no':no}, function(r) {
			var json = $.parseJSON(r);
			window.alert(json.msg);
			if(json.result == 'success') location.reload();
		});
	}
}

function showRepayMethod() {
	if($('input.repay_calc').length == 1 && $('input.repay_calc').val().toNumber() != 0) {
		$('.repay_method').show();
		if($(':checked.repay_pay_method').val() != '2') {
			$('.repay_method.bank').css('visibility','hidden');
		} else {
			$('.repay_method.bank').css('visibility','visible');
		}
	}
	else $('.repay_method').hide();
}

function showInputMethod(o) {
	if(o.value != '2') {
		$('.input_method.bank').hide();
	} else {
		$('.input_method.bank').show();
	}
}

// 교환, 주문수동등록시 주문상품별 할인가 변경
function setOrderProductSale(obj) {
	if(typeof obj == 'object') {
		while(obj[0].tagName != 'TR') {
			obj = obj.parent();
		}
		var idx = obj.index()
		var price = parseFloat(obj.find('input[name="sell_prc[]"]').val())*parseFloat(obj.find('input[name="buy_ea[]"]').val());
		price = price.toFixed(currency_decimal);
	}
	var method = '';
	for(var key in _order_sales) {
		method += '&'+key+'='+obj.find('input[name="'+key+'[]"]').val();
	}
	addSaleLayer.open('?idx='+idx+'&price='+price+method);
}

function setAddProductSale(obj) {
	var f = obj.form;
	var price = f.sell_prc.value.toNumber()*f.buy_ea.value.toNumber();
	var method = '';
	for(var key in _order_sales) {
		method += '&'+key+'='+$(f).find('input[name="'+key+'[]"]').val();
	}
	addSaleLayer.open('?idx=0&price='+price+method);

}

var addSaleLayer = new layerWindow('order@order_admin_setSale_inc.exe');
addSaleLayer.confirm = function() {
	var f = $('.salePrcFrm')[0];
	var idx = f.idx.value;
	var tr = $('#ord_prd').find('tr').eq(idx);
	if(tr.length == 0) tr = $('tr#ord_prd');
	var prc = total_sale = 0;
	for(var key in _order_sales) {
		if(f.elements[key]) {
			prc = parseFloat(f.elements[key].value);
			if(isNaN(prc)) prc = 0;
			total_sale+= prc;
			tr.find('input[name="'+key+'[]"]').val(prc);
			if(prc != 0) {
				tr.find('.admin_order_'+key).removeClass('hidden');
			} else {
				tr.find('.admin_order_'+key).addClass('hidden');
			}
			$('.product_'+key).eq(idx).html(setComma(prc));
		}
	}
	if(typeof getPrd_prc == 'function') getPrd_prc(total_sale); // 교환, 수동주문
	if(typeof setAddPrdMilage == 'function') setAddPrdMilage(tr.find('[name=sell_prc]')[0]); // 상품 추가

	addSaleLayer.close();
}

/**
 * 상품별 할인 제거
 **/
function removeProductSale(o, sn)
{
	var parent = $(o).parents('.admin_order_'+sn);
	parent.addClass('hidden');

	parent.find('input[name="'+sn+'[]"]').val('0');
	parent.find('.product_'+sn).html('');

	getPrd_prc();
}

// ksnet 영수증 출력
function ksnetReceipt(tr_no) {
	var receiptWin = "http://pgims.ksnet.co.kr/pg_infoc/src/bill/credit_view.jsp?tr_no="+tr_no;
	window.open(receiptWin, "" , "scrollbars=no,width=434,height=640");
}

// kakao 영수증 출력
function kakaoReceipt(tid, kakao_hash, mobile) {
	var d_type = "";
	var status ="toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=540,height=840";
	if(mobile=="Y") d_type = "m";
	else d_type = "p";
	var url = "https://pg-web.kakao.com/v1/confirmation/"+d_type+"/"+tid+"/"+kakao_hash;
	window.open(url,"popupIssue",status);
}

// payco 영수증 출력
function paycoReceipt(ono, payco_ono, payco_sellerKey) {
	var url = 'https://bill.payco.com/seller/receipt/'+payco_sellerKey+'/'+ono+'/'+payco_ono;
	window.open(url, 'popupIssue', 'status=no, width=200px, height=200px');
}

// nicepay 영수증 출력
function nicepayReceipt(tno) {
	window.open(
		'https://npg.nicepay.co.kr/issue/IssueLoader.do?TID='+tno+'&type=0',
		'popupIssue',
		'status=no, width=200px, height=200px'
	);
}

// tosspayment 영수증 출력
function tossReceipt(tno) {
	window.open(
		'https://pay.toss.im/payfront/web/external/sales-check?payToken='+tno+'&transactionId=12637496-8a46-488c-bc30-febded96656f',
		'popupIssue',
		'status=no, width=200px, height=200px'
	);
}

// samsungpay 영수증 출력
function samsungpayReceipt(tno) {
	window.open(
		'https://www.danalpay.com/receipt/ispay/auth.aspx?tid='+tno+'&cpgb=1',
		'popupIssue',
		'status=no, width=200px, height=200px'
	);
}

// 교환 및 주문 상품 추가 메뉴의 상품 및 주문 금액 계산
function getPrd_prc(no_autosale) {
	var pno = $(':input[name="pno[]"]');
	var buy_ea = $(':input[name="buy_ea[]"]');
	var sell_prc = $(':input[name="sell_prc[]"]');
	var prd_dlv_prc = $(':input[name="prd_dlv_prc[]"]');
	var delivery_set = $(':input[name="delivery_set[]"]');
	var a_prd_milage = $('.a_prd_milage');
	var a_mem_milage = $('.a_mem_milage');
	var ono = (f.parent) ? f.parent.value : '';
	var mid = (f.member_id) ? f.member_id.value : '';

	var qty_data = [];
	pno.each(function(idx) {
		if(this.value != '' && $('input[name="prd_disable[]"]').eq(idx).val() == '') {
			qty_data[idx] = {
				'pno': pno.eq(idx).val(),
				'sell_prc': sell_prc.eq(idx).val(),
				'buy_ea': buy_ea.eq(idx).val()
			}
			if (no_autosale != true) {
				for(var key in _order_sales) {
					var input = $(':input[name="'+key+'[]"]').eq(idx);
					if (input.parents('dl').hasClass('hidden') == false) {
						qty_data[idx][key] = input.val();
					} else {
						qty_data[idx][key] = 0;
					}
				}
			}
		}
	});

	exc_new_prc = 0;
    $('#prd_prc, input[name=ex_prd_prc]').html(setComma(exc_new_prc.toFixed(currency_decimal)));
	if(qty_data.length > 0) {
		$.post('./index.php', {'body':'order@order_calculator.exe', 'ono':ono, 'mid':mid, 'data':qty_data}, function(r) {
			for(var key in r.products) {
				var prd = r.products[key];
				var pay_prc = (parseInt(prd.sum_sell_prc)+parseInt(prd.prd_dlv_prc)).toFixed(currency_decimal);

				prd_dlv_prc.eq(key).val(prd.prd_dlv_prc);
				a_prd_milage.eq(key).val(prd.total_milage);
				a_mem_milage.eq(key).val(prd.member_milage);

				for(var fn in _order_sales) {
					$(':input[name="'+fn+'[]"]').eq(key).val(prd[fn]);
					$('.product_'+fn).eq(key).html(setComma(prd[fn]));

					if(prd[fn] > 0) $('.admin_order_'+fn).eq(key).removeClass('hidden');
					else $('.admin_order_'+fn).eq(key).addClass('hidden');;
				}

				$('.sell_prc_preview').eq(key).html(setComma(pay_prc));

				exc_new_prc += parseInt(pay_prc);
			}
			$('#prd_prc, input[name=ex_prd_prc]').html(setComma(exc_new_prc.toFixed(currency_decimal)));
			autoRepayPrc();
		});
	}
}

// 교환 및 주문 상품 추가 메뉴의  상품 옵션 변경시 옵션별 추가 가격 합산
function optionCal(multi, o, json) { // 옵션별 추가가격 체크
	var opt_prc = 0;
	var t = null;
	$('[name^="option"][name$="['+multi+']"]').each(function(){
		if(t) $(this).val('');
		if(o == this) t = this;

		var temp = this.value;
		if(temp) {
			if(this.getAttribute('data-otype') == '4B') { // 텍스트 옵션
				var add_price = $(this).attr('data-add-price').toNumber();
				var add_price_option = $(this).attr('data-add-price-option').toNumber();
				opt_prc += add_price;
				if(add_price_option > 0) {
					opt_prc += (this.value.length*add_price_option);
				}
			} else {
				temp = temp.split('::');
				opt_prc += temp[1].toNumber();
			}
		}
	});

	if(typeof json == 'undefined') {
		var param = {"exec_file":"shop/getAjaxData.php", "urlfix":"Y", "exec":"getAreaOptionPrc", "no":"", "val":""};
		var inos = $(':hidden[multi='+multi+']');
		if(inos.length > 0) {
			$(':text[multi='+multi+']').each(function(x){
				this.value = $.trim(this.value);
				param['no'] += '@'+inos.eq(x).val();
				param['val'] += '@'+this.value;
			});
			$.get(manage_url+'/main/exec.php', param, function(result) {
				var json = $.parseJSON(result);
				optionCal(multi, o, json);
			});
			return;
		}
	}
	if(json && json.how_cal == '3' && json.price > 0) opt_prc += json.price;

	var idx = -1;
	var m = $('input[name="m[]"]').each(function(i){
		if(this.value == multi) idx = i;
	});

	if(idx > -1) {
		var sell_prc = $(':input[name="sell_prc[]"]');
		var sell_prc_org = $(':input[name="sell_prc_org[]"]');

		if(json && json.how_cal == '4') sell_prc_org[idx].value = json.price;
		sell_prc[idx].value = sell_prc_org[idx].value.toNumber()+opt_prc;
	}
	getPrd_prc();

	setMultiOptionSoldout(multi); // 옵션 재고 체크
}

// 상품 교환, 추가
var order_prd_add_func = function(pno, e) {
	var multi = $('input[name="m[]"]').last().val();
	if (!multi) multi = 1;
	else multi = parseInt(multi)+1;

	// 필수옵션 체크
	var check_necessary = true;
	$('.product_select_options_'+pno).find('select, input[type=text]').each(function(idx) {
		if($(this).data('necessary') == 'Y' && $(this).val() == '') {
			window.alert('\''+$(this).data('name')+'\' 옵션을 설정해주세요.');
			check_necessary = false;
			return false;
		}
	});
	if(check_necessary == false) return false;

	// 선택한 옵션 데이터
	var ovals = $('.product_select_options_'+pno).find('select, input[type=text]').serialize();
	ovals += '&buy_ea='+$('.buy_ea_pno_'+pno).val();
	if(this.ono) {
		ovals += '&ono='+this.ono;
	}

	// 주문 상품 레이블 생성
	$.post('./index.php?body=order@order_admin.exe&exec=prd&pno='+pno+'&multi='+multi+'&'+ovals, function(data) {
		if($("<table>"+data+"</table>").find('.is_max_ord_mem').length > 0) {
			if(confirm('추가하신 상품 중\n회원주문한도 설정되어있는 상품이 포함되어있습니다.\n그래도 추가하시겠습니까?') == false) {
				return false;
			}
		}
		$('.prd_blank').remove(); // 추가할 상품을 선택해주세요 박스 제거
		$('#ord_prd').append(data);

		// 가격 재계산
		getPrd_prc(true);
	});
	$('.jquery_btt').remove();

	if(window.event.ctrlKey != true) {
		this.close();
	}
}