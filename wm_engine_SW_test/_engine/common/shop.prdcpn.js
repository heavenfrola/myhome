function showPrdCpnList(attach_mode, cno) {
	var param = null;
	if(attach_mode == 1) {
		param = $(f).serializeObject();
	} else if(attach_mode == 'close') {
		$('#prdCouponArea').fadeOut('fast', function() {
			removeDimmed();
			this.remove();
		});
		return;
	} else {
		param = {
			'cno': cno,
			'cart_selected': ((typeof cart_selected == 'string') ? cart_selected : null)
		};
	}
	param.attach_mode = attach_mode;
	param.pay_type = $(':checked[name=pay_type]').val();
	if(typeof param.pay_type == 'undefined') param.pay_type = '';
	$.get('/main/exec.php?exec_file=shop/detail_prdcpn.php', param, function(r) {
		setDimmed();
		$('#prdCouponArea').remove();
		$('body').append('<div id="prdCouponArea">'+r.content+'</div>');
		$('#prdCouponArea').css({
			'zIndex': '1001',
			'position':'fixed',
			'top': '100px',
			'left': '50%',
			'margin-left': -($('#prdCouponArea').children(0).width()/2)+'px',
		});
		try {
			if(browser_type == 'mobile') {
				$('#prdCouponArea').css({
					'top': 0,
					'left': 0,
					'margin-left': 0,
				});
			}
		} catch(ex) {}

		// 체크박스 disable 이벤트
		$('.sw_PrdCpn').change(function() {
			setPrdCpnDisabled(attach_mode);
		});
		setPrdCpnDisabled(attach_mode);
	});
}

function setPrdCpnDisabled(attach_mode) {
	$('.sw_PrdCpn').prop('disabled', false);
	$('.sw_PrdCpn:checked').each(function() {
		var cno = $(this).attr('data-cno');
		var use_limit = $(this).attr('data-uselimit');
		var cpnno = $(this).attr('data-cpnno');

		if(use_limit == '4') $('.sw_PrdCpnCno_'+cno).not(this).prop('disabled', true);
		$('.sw_PrdCpnCno_'+cno+'.sw_PrdCpnUseLimit_4').not(this).prop('disabled', true);

		if(this.checked == true) {
			$('.sw_PrdCpn_'+cpnno).not(this).prop('disabled', true);
		}
	});
	$('.sw_PrdCpn:disabled').attr('checked', false);
}

// 상품별 쿠폰 적용하기
// pagetype 주문서에서 개별상품쿠폰 적용시
function setPrdCpnList(f, pagetype='') {
	var prdFrm = byName('prdFrm');
	var attach_mode = f.attach_mode.value;

	// 상품 상세용 가상 장바구니
	if(attach_mode == '1') {
		if($('.multi_option_vals').length > 0) { // 멀티옵션 사용시
			var prdcpn = new Array();
			$('.sw_PrdCpn:checked', f).each(function() {
				if(!prdcpn[$(this).data('cno')]) prdcpn[$(this).data('cno')] = '';
				prdcpn[$(this).data('cno')] += '@'+this.value;
			});

			$('.multi_option_vals').each(function() {
				var idx = $(this).data('idx');
				$('input[name="multi_option_prdcpn_no['+idx+']"]').val(prdcpn[idx]);
			});
			appendMultiCart(prdFrm);
		} else {
			var prdCpn = '';
			$('.sw_PrdCpn:checked').each(function() {
				prdCpn += '@'+this.value;
			});
			prdFrm.prdcpn_no.value = prdCpn;
			totalCal(prdFrm);
		}
		showPrdCpnList('close');
	} else {
		if(typeof useMilage == 'function' && pagetype == '') {
			if(order_cpn_milage == 2) {
				if($('.sw_PrdCpn:checked').length > 0) {
					if(browser_type == 'mobile') {
						cwith=confirm(_lang_pack.order_coupon_milage);
						if(cwith == false) {
							parent.$('.sw_PrdCpn:checked').each(function() {
								$(this).prop('checked', false);
							});
							byName('ordFrm').milage_prc.value = milage_prc;
							prdcpn_sale_prc = 0;
							useMilage(byName('ordFrm'), 3);
							showPrdCpnList('close');
						} else {
							$.ajax({
								'type': 'post',
								'url': '/main/exec.php', 
								'data': $(f).serialize(),
								'success': function(r) {
									if(r.result == 'success') {
										if(typeof byName('ordFrm').milage_prc != 'undefined') {
											byName('ordFrm').milage_prc.value = 0;
										}
										useMilage(byName('ordFrm'), 3);
										showPrdCpnList('close');
									} else {
										window.alert(r.message);
									}
								}
							});
						}
					} else {
						$('#prdCouponArea').css('display', 'none');
						parent.dialogConfirm(null, _lang_pack.order_coupon_milage, {
							Ok: function() {
								$.ajax({
									'type': 'post',
									'url': '/main/exec.php', 
									'data': $(f).serialize(),
									'success': function(r) {
										if(r.result == 'success') { 
											dialogConfirmClose();
											useMilage(byName('ordFrm'),3);
											showPrdCpnList('close');
										} else {
											window.alert(r.message);
										}
									}
								});
							},
							Cancel: function() {
								parent.$('.sw_PrdCpn:checked').each(function() {
									$(this).prop('checked', false);
								});
								if(typeof byName('ordFrm').milage_prc != 'undefined') {
									byName('ordFrm').milage_prc.value = milage_prc;
								}
								dialogConfirmClose();
								prdcpn_sale_prc = 0;
								useMilage(byName('ordFrm'),3);
								showPrdCpnList('close');
							}
						});
					}
				} else {
					$.ajax({
						'type': 'post',
						'url': '/main/exec.php', 
						'data': $(f).serialize(),
						'success': function(r) {
							if(r.result == 'success') { 
								useMilage(byName('ordFrm'), 3);
								showPrdCpnList('close');
							}	
						}
					});
				}
			} else {
				$.ajax({
					'type': 'post',
					'url': '/main/exec.php', 
					'data': $(f).serialize(),
					'success': function(r) {
						if(r.result == 'success') { 
							useMilage(byName('ordFrm'), 3);
							showPrdCpnList('close');
						}	
					}
				});
			}
		} else { // 장바구니
			$.ajax({
				'type': 'post',
				'url': '/main/exec.php', 
				'data': $(f).serialize(),
				'success': function(r) {
					if( pagetype == 'order') {
						useMilage(byName('ordFrm'), 3);
					}else {
						if(r.result == 'success') { // 주문서
							location.reload();
						} else {
							window.alert(r.message);
						}
					}
				}
			});
		}
	}
	return false;
}

// 개별상품 쿠폰 체크시 실시간 가격 출력
var prdcpn_prc_preview = [];
function previewPrdCpnPrc(obj, cno, buy_ea, opt_prc, attach_mode, pagetype='') {
	if(obj.checked == true && prdcpn_prc_preview[cno] == 0) {
		window.alert(_lang_pack.order_error_cpnuse8);
		return false;
	}

	var reserved = $(obj).attr('data-reserved');
	if(obj.checked == true && attach_mode == 1 && reserved != '0') {
		if(confirm('이미 장바구니에 설정 된 쿠폰입니다.\n해제하고 적용하시겠습니까?') == false) {
			obj.checked = false;
			return false;
		}
	}

	var prdcpn_no = '';
	$(':checked.sw_PrdCpnCno_'+cno).each(function() {
		prdcpn_no += '@'+this.value;
	});
	var pay_type = $(':checked[name=pay_type]').val();
	if(typeof pay_type == 'undefined') pay_type = '';
	var param = {
		'exec_file': 'shop/getAjaxData.php',
		'exec': 'getDetailPrice',
		'ano': $('.sw_PrdCpnCno_'+cno).attr('data-pno'),
		'buy_ea': buy_ea,
		'opt_prc': opt_prc,
		'prdcpn_no': prdcpn_no,
		'pay_type': pay_type,
	}

	$.get('/main/exec.php', param, function(r) {
		if(pay_type != '2') { // 무통장 전용 쿠폰 체크
			if(r.cpn_pay_type > 0) {
				window.alert(_lang_pack.order_error_prdcpn_paytype);
			}
		}
		var used_prdcpn_no = r.prdcpn_no.split('@');
		$('.sw_PrdCpnCno_'+cno).each(function() {
			if($.inArray(this.value, used_prdcpn_no) < 0) {
				this.checked = false;
			}
		});
		$('.prdcpn_prc_preview_'+cno).html(r.pay_prc_c);
		prdcpn_prc_preview[cno] = r.pay_prc;
	});

    // 주문서에서 개별상품 쿠폰 사용
	if (pagetype) {
		setPrdCpnList(document.prdform, 'order');
	}
	return true;
}