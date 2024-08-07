function sbscrCheck(type) {
	if(type=='N') {
		$('form[name=prdFrm]').find('#naver_checkout_buttons').show();
		$('form[name=prdFrm]').find('#payco_detail_btn').show();
		$('form[name=prdFrm]').find('.none_sbscr').show();

		$('.necessary_P').prop('disabled', false).css('background', '');
	}else {
		$('form[name=prdFrm]').find('#naver_checkout_buttons').hide();
		$('form[name=prdFrm]').find('#payco_detail_btn').hide();
		$('form[name=prdFrm]').find('.none_sbscr').hide();

		$('.necessary_P').prop('disabled', true).val('').css('background', '#f2f2f2');
		
		$('#detail_multi_option').html('');
	}
}

function addcartSbscr(next) {
	if(typeof cart_direct_order != 'undefined') {
		if(cart_direct_order == 'D' && sbscr_cart_type == 'T' && sbscr_cart_cnt != 0) {
			if(browser_type == 'mobile' && next == 2) {
				cwith=confirm(_lang_pack.shop_confirm_cart);
			} else {
				addcartSbscrExec(next, true);
				return;
			}
		}
		else if(cart_direct_order == 'D' && sbscr_cart_type == 'S' && sbscr_cart_cnt != 0) {
			parent.dialogConfirm(null, "장바구니에 정기배송 상품이 존재합니다.", {
				Ok: function() {
					parent.dialogConfirmClose();
					return false;
				}
			});
		}
		else if(cart_direct_order == 'Y') cwith=0;
		else cwith=1;
	}else {
		cwith=1;
	}
	
	addcartSbscrExec(next, cwith);
}

function addcartSbscrExec(next, cwith) {
	if(!cwith) cwith = '';
	var tg = ac = '';

	var fdata = $("form[name=prdFrm]").serialize();
	var fdata2 = $("form[name=sbscrFrm]").serialize();
	var dlv_cnt = $('.sbscr_dlv_cnt').html();
	var sbscrf = document.sbscrFrm;
	var qd = $("form[name=prdFrm] [name=qd]").val();

	if(sbscrf.sbscr_end_date && sbscrf.sbscr_end_date.value != '0' && sbscrf.sbscr_start_date.value>=sbscrf.sbscr_end_date.value) {
		alert("정확한 날짜를 선택해주세요.");
		return false;
	}
	$.ajax({
		type : 'POST',
		url : root_url+'/main/exec.php?exec_file=cart/cart.exe.php&cwith='+cwith,
		data: fdata+'&'+fdata2+'&sbscr_dlv_cnt='+dlv_cnt+'&exec=add&from_ajax=true',
		success : function(r) {
			if (typeof r == 'object') {
				cart_no = r.cart_no;
				result = r.result;
			} else {
				result = r;
			}
			if(result == 'OK') {
				if(next==2) {
					parent.location.href = root_url+'/shop/order.php?sbscr=Y&cart_selected='+cart_no;
				}else {
					if(parent.browser_type == 'mobile') {
						if(confirm(_lang_pack.shop_confirm_cartok)) {
							parent.location.href=root_url+'/shop/cart.php?sbscr=Y';
						}
					} else {
						dialogConfirm(null, _lang_pack.shop_confirm_cartok, {
							Ok: function() {
								if (!isEmpty(qd)) parent.location.href=root_url+'/shop/cart.php?sbscr=Y';
								else location.href=root_url+'/shop/cart.php?sbscr=Y';
							},
							Cancel: function() {
								dialogConfirmClose();
							}
						});
					}
				}
			} else {
				window.alert(result);
			}
		}
	});
}

function sbscrTypeChk(val, all, split) {
	if(all=='Y') {
		$('.paytype_gr3').show();
	}
	if(split=='Y') {
		$('.paytype_gr4').show();
	}
	if(val=='Y') {
		$('.paytype_gr1').show();
		$('.paytype_gr2').hide();
		$('.order_area_firsttime_pay_prc').css('display', 'none'); // 일괄 - 첫결제금액 숨김
        if ($('#pay_type1')) $('#pay_type1').click();
        else $('.paytype_gr1').eq(0).children('input:radio').click();
	}else if(val=='N') {
		$('#bank_info').hide();
		$('.order_cancel_msg').hide();
		$('.paytype_gr2').show();
		$('.paytype_gr1').hide();
		$('.order_area_firsttime_pay_prc').css('display', ''); // 정기 - 첫결제금액 노출
        if ($('#pay_type23')) $('#pay_type23').click();
        else $('.paytype_gr2').eq(0).children('input:radio').click();
	}
}

function mypageSbscr(sbono, val, last) {
	if(val=='stop') {
		parent.location.href = root_url+'/mypage/counsel_list.php';
	}else if(val=='cancel') {
		if(confirm(last+" 회차 이후의 정기배송을 취소하시겠습니까?")) {
			$.ajax({
				type : 'POST',
				url : root_url+'/main/exec.php?exec_file=mypage/mypage_sbscr.exe.php',
				data: 'sbono='+sbono+'&last='+last+'&type=cancel',
				dataType : 'html',
				success : function(result) {
					if(result=='OK') {
						alert("정기배송이 취소 완료되었습니다.");
						location.reload();
					}else {
						alert("잠시 후 다시 시도해주세요.");
					}
				}
			});
		}
	}else if(val=='edit') {
		editAddressee(sbono);
	}
}

function viewSbscr(ono,anchor) {
	var viewId = 'viewOrderV2';
	if(getCookie('def_omode') != '0') viewId+=ono;
	viewId = viewId.replace(/[^0-9a-z]/i, '');
	var nurl = './?body=order@sbscr_view.frm&sbono='+ono;
	if(anchor) nurl += anchor;
	if(isCRM) window.location.href = nurl;
	else window.open(nurl,viewId,'top=10,left=10,status=no,toolbars=no,scrollbars=yes,height=400px,width=1500px');
}

function subscriptionDetail() {
	this.form = document.sbscrFrm;
}

subscriptionDetail.prototype.exec = function() 
{
	// 현재 상태
	var param = {
		'sbscr_period': $(this.form.sbscr_period).filter(':checked').val(),
		'buy_ea': this.form.sbscr_ea.value,
		'pno': this.form.sbscr_pno.value,
		'option_val': this.form.sbscr_option.value,
		'sell_prc': this.form.sbscr_sell_prc.value,
		'start_date': this.form.sbscr_start_date.value,
		'end_date': (this.form.sbscr_end_date) ? this.form.sbscr_end_date.value : 0,
		'sale_use': this.form.sbscr_sale_yn.value,
		'sale_ea': this.form.sbscr_sale_ea.value,
		'sale_percent': this.form.sbscr_sale_percent.value
	}

	// 달력
	var _this = this;
	var cal_setting = date_picker_default;
	cal_setting.minDate = this.form.sbscr_start_date.value;
	cal_setting.onSelect = function() {
		_this.exec();
	}

	$.ajax({
		type: 'post',
		url: root_url+'/main/exec.php?exec_file=shop/sbscr.exe.php',
		data: param,
		dataType : 'json',
		success: function(json) {
			cal_setting.minDate = json.detail_start_date;
			cal_setting.maxDate = json.sbscr_dlv_end;
			cal_setting.beforeShowDay = function(date) { // 휴일 및 배송불가 요일 처리
				var day = date.getDay().toString();
				var timestamp = Math.floor(date/1000).toString();
				return [
					($.inArray(day, json.weekSelectable) > -1 && $.inArray(timestamp, json.holidays) == -1)
				]
			}

			$(_this.form.sbscr_start_date).val(json.detail_start_date);
			$(_this.form.sbscr_start_date).datepicker(cal_setting);
			$(_this.form.sbscr_end_date).val(json.detail_end_date);
			$(_this.form.sbscr_end_date).datepicker(cal_setting);
			$(".sbscr_sell_prc").html(json.detail_sbscr_sell_prc);
			$(".sbscr_dlv_prc").html(json.detail_sbscr_dlv_prc);
			$(".sbscr_ea_sell_prc").html(json.detail_sbscr_ea_prc);
			$(".sbscr_dlv_cnt").html(json.detail_sbscr_dlv_cnt);
			$(".sbscr_total_prc").html(json.detail_sbscr_pay_prc);
			$(".sbscr_info_option").html(json.detail_sbscr_option_text);
			$("#sbscr_date_list").val(json.date_list);
			$("#sbscr_start_date_text").html(json.detail_start_date);
			$("#sbscr_end_date_text").html(json.detail_end_date);
			$("#sbscr_start_yoil_text").html(json.detail_sbscr_start_yoil);
			$("#sbscr_end_yoil_text").html(json.detail_sbscr_end_yoil);
			$('.sbscr_info_Interval').html(json.detail_interval_str);
			$('.sbscr_info_date').html(json.detail_date_str);
			$('.sbscr_info_week').html(json.detail_sbscr_start_yoil);
			$('.sbscr_info_first_date').html(json.detail_start_date);
		}
	});
}

subscriptionDetail.prototype.close = function() {
	removeDimmed();
	$('#__subscription_detail_layer').remove();
}