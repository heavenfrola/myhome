var qna_edit=1;
function addCart(f,next) {
	var astr = '';
	var ems = '';

	if(f.stat.value!=2) {
		ems = _lang_pack.shop_error_soldout;
		window.alert(ems);
		return;
	}

	var min_ord=eval(f.min_ord.value);
	var max_ord=eval(f.max_ord.value);
	var buy_ea = (f.buy_ea) ? eval(f.buy_ea.value) : 1;

	if(buy_ea < min_ord) {
		window.alert(_lang_pack.shop_error_minord.format(min_ord));
		f.buy_ea.value=min_ord;
		return;
	}

	if(max_ord && buy_ea>max_ord) {
		window.alert(_lang_pack.shop_error_maxord.format(max_ord));
		f.buy_ea.value=max_ord;
		return;
	}
	var opt_no = f.opt_no.value.toNumber();
	if(opt_no > 0 && $('input[name^="multi_option_pno["]').length == 0) {
		for(j=1; j<=eval(opt_no); j++)	{
			var obj = f.elements['option'+j];
			var necessary = f.elements['option_necessary'+j];
			var otype = f.elements['option_type'+j];
			var oname = f.elements['option_name'+j];
			var ea_num = f.elements['option_ea_num'+j];
			var how_cal = f.elements['option_how_cal'+j];

			if(!necessary) continue;
			if(necessary.value=="Y" || necessary.value=="C") { // necessary
				if(otype.value==2) { // select
					if(!checkSel(obj, _lang_pack.shop_error_optionset1.format(oname.value))) return;
				} else if(otype.value == 4) { // text
					if(how_cal.value == '3' || how_cal.value == '4') { // 면적옵션
						var err = false;
						$(f.elements).filter('[name="option'+j+'[]"]').each(function() {
							if(!checkBlank(this, _lang_pack.shop_error_optionset2.format(oname.value, this.title))) {
								err = true;
								return false;
							}
						});
						if(err) return;
					} else { // 일반 텍스트 옵션
						if(!checkBlank(obj, _lang_pack.shop_error_optionset1.format(oname.value))) return;
					}
				} else if(otype.value == 5) {
					if(!checkBlank(obj, _lang_pack.shop_error_optionset1.format(oname.value))) return;
				} else { // radio, checkbox
					if(!checkCB(obj, _lang_pack.shop_error_optionset1.format(oname.value))) return;
				}
			}

			// 옵션재고체크
			if(f.elements['option_ea_ck'+j].value == "Y" && necessary.value=="Y" && f.ea_type.value != '1') {
				if(ea_num.value < 1) {
					window.alert(_lang_pack.shop_error_optionsoldout.format(oname.value));
					return;
				}
				if(buy_ea > ea_num.value) {
					window.alert(_lang_pack.shop_error_optionmaxord.format(oname.value, ea_num.value));
					f.buy_ea.value=ea_num.value;
					return;
				}
			}
			// >
		}
	}

	try{ // smartMD
		if(_TRK_LID && _TRK_PI && _TRK_PN && _TRK_PI == 'PDV') {
			_trk_clickTrace('SCI', _TRK_PN);
		}
	} catch(_e) {}

	if(ace_counter == '1') { // acecounter old
		AEC_F_D(_AEC_prodidlist[0],'i',buy_ea);
	}

	if(typeof AEC_CALL_STR_FUNC != 'undefined') { // acecounter new
		AEC_CALL_STR_FUNC(_A_pl[0], 'i', f.buy_ea.value);
	}

	if(typeof ACC_PRODUCT != 'undefined') { // acecounter mobile
		ACC_PRODUCT(f.buy_ea.value);
	}

	// facebook Pixel
	if(typeof fbq == 'function') {
		try {
			var _currency = (currency_type == '원') ? 'KRW' : currency_type;
			var fbprice = parseInt(f.new_total_prc.value);
			fbq('track', 'AddToCart', {content_ids:[f.pno.value], content_type:'product', value:fbprice, currency:_currency});
		} catch(ex) {}
	}

	var is_subscribe = (f.sbscr) ? $(f.sbscr).filter(':checked').val() : false;
	if(is_subscribe == 'Y' || is_subscribe == 'v2') {
		var sbscr_option_val = "";
		var sbscr_buy_ea = "";

		if($('#detail_multi_option li').length>0) {
			$('#detail_multi_option li').each(function(i){
				var num = i;
				sbscr_option_val += (sbscr_option_val == "") ? $(':input[name="multi_option_vals['+num+']"]').val() : "|"+$(':input[name="multi_option_vals['+num+']"]').val();
				sbscr_buy_ea += (sbscr_buy_ea == "") ? $(':input[name="m_buy_ea['+num+']"]').val() : "|"+$(':input[name="m_buy_ea['+num+']"]').val();
			});
		}else {
			sbscr_buy_ea = f.buy_ea.value;
		}

		var detail = 'shop/subscription.php';
		if (is_subscribe == 'v2') {
			detail = 'shop/detail_subscription.php';
		}

		setDimmed();
		$.post(root_url+'/main/exec.php?exec_file='+detail+'&stripheader=true&striplayout=1', {'hash':f.pno.value, 'sbscr_option_val':sbscr_option_val, 'sbscr_buy_ea':sbscr_buy_ea}, function(data) {
			if (is_subscribe == 'v2') {
				$('body').append('<div id="__subscription_detail_layer">'+data+'</div>');
				window.subscribe = new subscriptionDetail();
				subscribe.exec();

				// events
				$(':radio[name=sbscr_period]').on('change', function() {
					window.subscribe.exec();
				});
			} else { // 구버전
				$('body').append(data);
			}
		});
		return;
	}

	if(typeof cart_direct_order != 'undefined' && next==2) {
		if(cart_direct_order == 'D' && cart_cnt != '0') {
			if(browser_type == 'mobile') {
				cwith=confirm(_lang_pack.shop_confirm_cart);
			} else {
				dialogConfirm(null, _lang_pack.shop_confirm_cart, {
					Ok: function() {
						addCartExec(f, next, true);
					},
					Cancel: function() {
						addCartExec(f, next, false);
						dialogConfirmClose();
					}
				});
				return;
			}
		}
		else if(cart_direct_order == 'Y') cwith=0;
		else cwith=1;
	} else if(next == 'checkout' || next == 'payco') {
		cwith=0;
	} else {
		cwith=1;
	}

	addCartExternalActions({
		'hash': f.pno.value,
		'product_name': f.product_name.value,
		'total_prc': f.total_prc.value,
		'buy_ea': buy_ea
	}, 'add');

	return addCartExec(f, next, cwith);
}

function addCartExec(f, next, cwith) {
	if(!cwith) cwith = '';
	f.exec.value = 'add';
	var ac = '';
	var qd = f.qd.value;

	if (next==1 || next==2) {
		ac = root_url+'/main/exec.php?exec_file=cart/cart.exe.php&accept_json=Y&cwith='+cwith;
		f.next.value = next;

		$.ajax({
			type: 'post',
			url:  ac,
			dataType: 'json',
			data: $(f).serialize(),
			async: false,
			success: function(r) {
				if (r.result == 'OK' || r.result == 'success') {
					top.$('.front_cart_rows').html(r.cart_rows);
					if (browser_type == 'mobile') {
						if (confirm(_lang_pack.shop_confirm_cartok)) {
							location.href = r.url;
						} else {
							openQuickCart(9, 'reload');
						}
					} else {
						dialogConfirm(null, _lang_pack.shop_confirm_cartok, {
							Ok: function() {
								if (!isEmpty(qd)) parent.location.href = r.url;
								else location.href = r.url;
							},
							Cancel: function() {
								openQuickCart(9, 'reload');
								dialogConfirmClose();
							}
						});
					}
				} else {
					if (!isEmpty(r.message)) {
						if (r.message) alert(r.message.replaceAll("\\'", "'"));
					}
					if (r.url && r.url != 'about:blank') {
						if (next == 'checkout' && npay_target == 'blank') {
							if (browser_type == 'mobile') location.href = r.url;
							else window.open(r.url);
						} else if (!isEmpty(qd)) {
							parent.location.href = r.url;
						} else {
							location.href = r.url;
						}
					}
				}
			}
		});
	} else if (next == 'checkout' || next == 'payco' || next == 'talkpay') {
		tg=hid_frame;

		const is_mobile = (navigator.userAgent.match(/Android|iPhone|iPad|iPod/)) ? true : false;

		if(next == 'checkout' && npay_target == 'blank') {
			tg = '_blank';
			if (is_mobile == true) tg = '_self';
		}
		ac=root_url+'/main/exec.php?exec_file=cart/cart.exe.php&cwith='+cwith + '&is_mobile=' + is_mobile;
		f.next.value=next;
		f.exec.value='add';
		f.target=tg;
		f.action=ac;

		if (next == 'talkpay') {
			return 'success';
		}

		f.submit();
	} else {
		ac = root_url+'/shop/order.php';
		f.target = '';
		f.action = ac;
		f.submit();
	}
}

function addMultiCart(f,next) {
	if(f.stat.value!=2) {
		window.alert(_lang_pack.shop_error_soldout);
		return;
	}

	var opt_no=eval(f.opt_no.value);
	var min_ord=eval(f.min_ord.value);
	var max_ord=eval(f.max_ord.value);
	var buy_ea=0;
	var ea_num=f["buy_ea[]"].length;
	if(!ea_num) ea_num=1;
	if(ea_num == 1) {
		buy_ea += eval(f["buy_ea[]"].value);
		if(opt_no>0) {
			for(ii=1; ii<=opt_no; ii++) {
				if(f['option_necessary'+ii].value=="Y") {
					if(f['option_type'+ii].value==2) { if(!checkSel(f['option'+ii+'[]'], _lang_pack.shop_error_optionset1.format(f['option_name'+ii].value))) return; }
					else{ if(!checkCB(f['option'+ii+'[]'], _lang_pack.shop_error_optionset1.format(f['option_name'+ii].value))) return; }
				}
			}
		}
	}else{
		for(jj=0; jj<ea_num; jj++) {
			buy_ea += eval(f["buy_ea[]"][jj].value);
			if(opt_no>0) {
				for(ii=1; ii<=opt_no; ii++) {
					if(f['option_necessary'+ii].value=="Y") {
						if(f['option_type'+ii].value==2) { if(!checkSel(f['option'+ii+"[]"][jj], _lang_pack.shop_error_optionset1.format(f['option_name'+ii].value))) return; }
						else{ if(!checkCB(f['option'+ii][jj], _lang_pack.shop_error_optionset1.format(f['option_name'+ii].value))) return; }
					}
				}
			}
		}
	}
	if(buy_ea<min_ord) {
		window.alert(_lang_pack.shop_error_minord.format(min_ord)); return;
	}
	if(max_ord && buy_ea>max_ord) { alert(_lang.pack.shop_error_maxord.format(max_ord)); return; }

	tg=hid_frame;
	ac=root_url+'/main/exec.php?exec_file=cart/cart.exe.php';
	f.next.value=next;

	f.exec.value='multi_option';
	f.target=hid_frame;
	f.action=root_url+'/main/exec.php?exec_file=cart/cart.exe.php';
	f.submit();
}

function priceCal(f) {
	var mpc;
	if(typeof f.multi_price=='undefined') mpc=0;
	else mpc=eval(f.multi_price.value);

	if(mpc==0) return;
	if(mpc>1) {
		for(m=0; m<mpc; m++) {
			if(f.price[m].checked==true) {
				tmp=f.price[m].value.split("::");
				price=eval(tmp[1]);
				break;
			}
		}
	} else {
		tmp=f.price.value.split("::");
		price=tmp[1].toNumber();
	}

	f.new_total_prc.value = f.total_prc.value.toNumber() + price;
}

function optionCal(f,opt_no,sval, o, multi) {
	if(multi) {
		setMultiOptionSoldout(f, opt_no, multi);
		return;
	}
	if(o) {
		var min_length = parseInt(o.getAttribute('data-min-length'));
		if(isNaN(min_length) == false && min_length > o.value.length) {
			window.alert("'"+f.elements['option_name'+opt_no].value+"' "+_lang_pack.shop_error_content.format(min_length));
			o.focus();
			return false;
		}
	}

	var how_cal = f.elements['option_how_cal'+opt_no].value;
	if(how_cal == 3 || how_cal == 4) {
		areaCal(f, opt_no);
		return;
	}
	tmp=sval.split("::");
	f.elements['option_sel_item'+opt_no].value=tmp[0];
	f.elements['option_prc'+opt_no].value=tmp[1];
	f.elements['option_ea_num'+opt_no].value=tmp[2];	

	// 마지막 선택 옵션일 경우에만 데이터 요청
	let last_selected = null;
	for (let i = 1; i <= parseInt(f.opt_no.value); i++) {
		if(f['option' + i].value) {
			last_selected = i;
		}
	}
	if (opt_no == last_selected) {
		totalCal(f);

		// 부가이미지 체크
		$.get(root_url+'/main/exec.php?exec_file=shop/getAjaxData.php', {'exec':'getAddImgList', 'hash':f.pno.value, 'opt_no':opt_no, 'ino':tmp[3]}, function(r) {
			if(r) {
				var json = $.parseJSON(r);
				setOptionAddImage(json);
			}
		});		
	}
}

function areaCal(f, opt_no) {
	var how_cal = f.elements['option_how_cal'+opt_no].value;
	if(how_cal != '3' && how_cal != '4') return;

	var input = $('[name="option'+opt_no+'[]"]');
	var item = $('[name="option_area'+opt_no+'[]"]');
	var param = {"exec_file":"shop/getAjaxData.php", "exec":"getAreaOptionPrc", "no":"", "val":""};
	input.each(function(idx) {
		if(idx > 1) return;

		this.value = Math.abs(parseInt($.trim(this.value)));
		if(isNaN(this.value)) this.value = 0;

		param['no'] += '@'+item.eq(idx).val();
		param['val'] += '@'+this.value;
	});

	if(/@0/.test(param['val'])) return;

	$.get(root_url+'/main/exec.php', param, function(result) {
		var json = $.parseJSON(result);
		if(json.errmsg) window.alert(json.errmsg);

		var input = $('#area_opid_'+json.opno);
		if(input.attr('tagName') == 'INPUT') input.val(setComma(json.price));
		else input.html(setComma(json.price));

		if(!f.elements['area_prc'+opt_no]) {
			$(f).append("<input type='hidden' name='area_prc"+opt_no+"' value='"+json.price+"'>");
		} else {
			f.elements['area_prc'+opt_no].value = json.price;
		}
		totalCal(f);
	});
}

function totalCal(f) {
	if (f.buy_ea && f.min_ord) {
		var buy_ea = parseInt(f.buy_ea.value);
		var min_ord = parseInt(f.min_ord.value);
		if(min_ord > buy_ea) f.buy_ea.value = min_ord;
	}

	if(f.sell_prc_consultation && f.sell_prc_consultation.value) {
		return;
	}
	priceCal(f);
	$.post('/main/exec.php', $(f).serialize()+'&exec_file=shop/getAjaxData.php&exec=getDetailPrice', function(r) {
		f.new_total_prc.value = r.pay_prc;
		$('.prd_prc_str').html(setComma(r.prd_prc)); // 할인 전 낱개 가격
		$('.sell_prc_str').html(setComma(r.pay_prc)); // 할인 후 낱개 가격
		$('.prd_prc_str_total').html(setComma(r.prd_prc)); // 할인 전 총 가격
		$('.sell_prc_str_total').html(setComma(r.pay_prc)); // 할인 후 총 가격
		if($('#sell_r_prc_str').length > 0 && $.trim(r_currency_type)) $('#sell_r_prc_str').html(showExchangeFee(r.pay_prc)); //참조금액
	});
}

function orderCartAll() {
	c1=eval(document.cartFrm1.cart_rows.value);
	c2=eval(document.cartFrm2.cart_rows.value);

	total_cart=c1+c2;
	if(total_cart==0) {
		window.alert(_lang_pack.shop_error_nocart);
		return;
	}
	location.href=root_url+'/shop/order.php';
}

function checkQnaFrm(f) {

	if(qa==2)
	{
		if(!memberOnly(this_url,1,1)) return false;

	}
	if(qa=='' && mlv==10)
	{
		if(f.name.value=='이름') f.name.value='';
		if(!checkBlank(f.name, _lang_pack.common_input_name)) return false;

		if(f.pwd.value=='비밀번호' || f.pwd.value=='pass') f.pwd.value='';
		if(!checkBlank(f.pwd, _lang_pack.common_input_pwd)) return false;
	}

	if(typeof f.cate!='undefined')
	{
		if(!checkSel(f.cate, _lang_pack.common_input_cate)) return false;
	}
	if(!checkBlank(f.title, _lang_pack.common_input_subject)) return false;
	if(qna_strlen) {
		if(qna_strlen > f.title.value.length) { alert(_lang_pack.shop_error_titlen.format(qna_strlen)); return false; }
	}
	if(f.qnaContent) {
		if(!submitContents('qnaContent', _lang_pack.common_input_content)) return false;
		f.content.value=f.qnaContent.value;
	}else{
		if(!checkBlank(f.content, _lang_pack.common_input_content)) return false;
	}

	f.target = hid_frame;

	if(seCalled && oEditors) { // 에디터 초기화
		var tmp = seCalled.split('@');
		for(var key in tmp) {
			if(tmp[key] == 'qnaContent') {
				seCalled = seCalled.replace('@qnaContent', '');
				oEditors[(key-1)] = null;
				break;
			}
		}
	}

	//캡차
	if(!f.no.value) {
		if($('#grecaptcha_element2').length > 0) {
			if(typeof(grecaptcha) != 'undefined') {
				var response = grecaptcha.getResponse(grecaptcha_element_2_id);
				if(response.length == 0) {
					alert(_lang_pack.board_capcha_connot);
					return false;
				}
			}
		}
	}

	printFLoading();

	return true;
}

// 상품 질답 쓰기 레이어 열기
function writeQna() {
	if(qna_edit==2)
	{
		f=document.qnaFrm;
		if(typeof f.name == 'object') {
			f.name.readOnly=false;
			f.name.style.backgroundColor='';
			f.name.value='';
		}
		if(typeof f.pwd == 'object') {
			f.pwd.readOnly=false;
			f.pwd.style.backgroundColor='';
			f.pwd.value='';
		}
		f.title.value="";
		f.content.value="";
		qna_edit=1;
	}

	var tmp=document.getElementById('qnaWriteDiv');
	if(qa=='2' || qa=='3')
	{
		if(memberOnly(this_url,1,1)) {
			layTgl(tmp);
			if(document.getElementById('qnaContent')) {
				var editor = new R2Na('qnaContent', '', '');
				editor.initNeko(editor_code, 'product_qna', 'img');
			}
		}
	}
	else {
		layTgl(tmp);
		if(document.getElementById('qnaContent')) {
			var editor = new R2Na('qnaContent', '', '');
			editor.initNeko(editor_code, 'product_qna', 'img');
		}
	}
}

// Qna수정, 삭제
function delQna(no) {
	var f = $('[name=qnaFrm]');
	if(!f.length) {
		$('body').append("<form method='post' name='qnaFrm' action='/main/exec.php'><input type='hidden' name='exec_file' /><input type='hidden' name='no' /><input type='hidden' name='exec' /></form>");
	}

	var ams='';
	if(alv!='') {
		ams = _lang_pack.shop_confirm_rmadmin;
	}
	if(!confirm(_lang_pack.common_confirm_delete+ams)) return;
	var f = document.qnaFrm;
	f.no.value=no;
	f.exec_file.value='shop/qna_reg.exe.php';
	f.exec.value='delete';
	f.target=hid_frame;
	f.submit();
}
function conDelQna(no) {
	f=document.forms["qna_pfrm"+no];
	f.exec_file.value="shop/qna_reg.exe.php";
	f.exec.value='delete';
	document.getElementById('qna_pwd'+no).style.display='block';
	document.getElementById('qna_modi'+no).style.display='none';
}
function editQna(no) {
	var f = $('[name=qnaFrm]');
	if(!f.length) {
		$('body').append("<form method='post' name='qnaFrm' action='/main/exec.php'><input type='hidden' name='exec_file' /><input type='hidden' name='no' /><input type='hidden' name='exec' /></form>");
	}

	if(seCalled && oEditors) { // 에디터 초기화
		var tmp = seCalled.split('@');
		for(var key in tmp) {
			if(tmp[key] == 'qnaModiContent'+no) {
				seCalled = seCalled.replace('@qnaModiContent'+no, '');
				oEditors[(key-1)] = null;
				break;
			}
		}
	}

	qna_edit=2;
	f=document.qnaFrm;
	f.no.value=no;
	f.exec_file.value='shop/qna_edit.php';
	f.exec.value='';
	f.target=hid_frame;
	f.submit();
}
function checkQnapwdFrm(f) {
	if(!checkBlank(f.pwd, _lang_pack.common_input_pwd)) return false;
	f.target = hid_frame;
}
function checkQnaModiFrm(f) {
	if(window.editorID) {
		try { submitContents(window.editorID, ''); } catch (ex) { }
	}

	if(typeof f.cate!='undefined')
	{
		if(!checkSel(f.cate, _lang_pack.common_input_cate)) return false;
	}
	if(!checkBlank(f.title, _lang_pack.common_input_subject)) return false;
	if(f.qnaContent) {
		if(!submitContents('qnaContent', _lang_pack.common_input_content)) return false;
		f.content.value=f.qnaContent.value;
	}else{
		if(!checkBlank(f.content, _lang_pack.common_input_content)) return false;
	}
	f.target = hid_frame;
}

// review 수정, 삭제
function checkReviewpwdFrm(f) {
	if(!checkBlank(f.pwd, _lang_pack.common_input_pwd)) return false;
	f.target = hid_frame;
}
function checkReviewModiFrm(f) {
	if(!checkBlank(f.title, _lang_pack.common_input_subject)) return false;

	var tmpContentId = f.content.getAttribute('id');
	try {
		if(!submitContents(tmpContentId, _lang_pack.common_input_content)) return false;
		f.content.value=f.tmpContentId.value;
	} catch(ex) {
		if(!checkBlank(f.content, _lang_pack.common_input_content)) return false;
	}
	f.target = hid_frame;
}

function zoomView(pno,w,h) {
	if(!w) w=735;
	if(!h) h=630;

	url=root_url+'/shop/zoom.php?pno='+pno;
	window.open(url,'wmZoomView','top=10,left=10,height='+h+',width='+w+',status=no,scrollbars=no,toolbar=no,menubar=no');
}

function noPrd() {
	window.alert(_lang_pack.shop_error_outprd);
}

function orderCust(tp,newstat) {
	var cf=document.orderCustFrm;
	var oldstat=eval(cf.stat.value);
	if(oldstat>10 && newstat>10) {
		window.alert(_lang_pack.shop_error_cancel);
		return;
	}
	if((oldstat == 13 || oldstat == 15 || oldstat == 17 || oldstat == 19)  && newstat == 1) {
		window.alert(_lang_pack.shop_error_cancel);
		return;
	}
	if((newstat==12 || newstat==14) && cancelable != 'true') { // 취소
		alert(_lang_pack.shop_error_dlvbefore);
		return;
	}
	if(newstat==16  && returnable != 'true') {
		alert(_lang_pack.shop_error_dlvafter);
		return;
	}
	if(newstat == 12 && directcancel == 'true') {
		if(confirm(_lang_pack.mypage_confirm_direct_cancel) == false) {
			return false;
		}
	}
	cf.cate1.value=tp;
	cf.cate2.value=newstat;

	if(mlv==10) cf.method='post';
	else cf.method='get';
	cf.submit();
}

function checkCounselFrm(f) {
	if(mlv==10)
	{
		if(!checkBlank(f.name, _lang_pack.common_input_name)) return false;
		if(!f.email.value) {
			window.alert(_lang_pack.shop_error_counsel);
			return false;
		}
	}

	if(typeof oEditors.getById != 'undefined' && oEditors.getById['counsel_cnt']) {
		submitContents('counsel_cnt', '');
	}

	if(!checkBlank(f.title, _lang_pack.common_input_subject)) return false;
	if(!checkBlank(f.content, _lang_pack.common_input_content)) return false;

	//캡차
	if($('#grecaptcha_element').length>0) {
		if(typeof(grecaptcha) != 'undefined'){
			if(grecaptcha.getResponse() == ""){
				alert(_lang_pack.board_capcha_connot);
				return false;
			}
		}
	}

	if(confirm(_lang_pack.mypage_confirm_counsel_reg) == false) {
		return false;
	}

	printFLoading();

	return true;
}


function toggleAttatchImage(s,w,h) {
	var mimg1=document.getElementById('mainImg');
	if(mimg1.src==s) return;
	var mimg=document.getElementById('mimg_div');
	str='<img id="mainImg" src="'+s+'" width="'+w+'" height="'+h+'">';
	mimg.innerHTML=str;
}

function csView(no,stat) {
	layTglList('rev','revQna',no);

	return;

	if(!stat) {
		layTglList('rev','revQna',no);
	} else {
		window.alert(_lang_pack.shop_info_answerready);
		return;
	}
}

function checkQnaSecret(f) {
	f.target = hid_frame;
	if(!checkBlank(f.pwd, _lang_pack.common_input_pwd)) return false;
}

function multiCart(f) {
	total_ea=0;
	for(i=0; i<f.buy_ea.length; i++) {
		total_ea+=eval(f.buy_ea[i].value);
	}
	if(total_ea<=0) {
		window.alert(_lang_pack.shop_input_buyea);
		return;
	}
	f.submit();
}

function receiveProduct(ono, escrow_type, escrow_id) {
	if(!confirm(_lang_pack.shop_confirm_complete)) return;

	// 이니에스크로 수취확인
	if(escrow_type == 'ini_escrow') {
		enable_click();
		focus_control();

		tf=document.ini;
		tf.oid.value=ono;
		tf.tid.value=escrow_id;

		if(pay(tf)) tf.submit();

		return;
	}

	// 올앳에스크로 수취확인
	if(escrow_type == 'allat_escrow') {
		var f = document.getElementById('allat_confirm_form');
		if(f) {
			f.allat_shop_id.value = escrow_id;
			f.allat_order_no.value = ono;
			f.shop_receive_url.value = root_url+'/main/exec.php?exec_file=mypage/receive.exe.php&abc=123&ono='+ono
			ftn_app(document.sendFm);
			return false;
		}
	}

	gurl=root_url+"/main/exec.php?exec_file=mypage/receive.exe.php&ono="+ono;
	window.frames[hid_frame].location.href=gurl;
}

// 하나 에스크로 구매완료/거절
function UserDefine() {
	var f = document.cporder;
	var ctype = ( document.cporder.ctype.value == "CFRM" ) ? "구매완료가" : "구매거절이";

	if(status_cd == "SUCCESS")	{
		alert("에스크로 "+ctype+" 성공적으로 완료되었습니다.");
	} else if(status_cd == "CANCEL") {
		alert("에스크로 "+ctype+" 취소되었습니다.");
	} else {
		alert("에스크로 에러"+status_cd);
	}
}

function modRevCmt(no) {
	f=document.reviewDelFrm;
	f.no.value=no;
	f.exec.value='modify';
	f.exec_file.value='shop/review_comment.exe.php';
	f.submit();
}


/* +----------------------------------------------------------------------------------------------+
' |  Naver Checkout
' +----------------------------------------------------------------------------------------------+*/
function buy_nc() {
	if(naver.NaverPayButton.prdable != 'Y') {
		window.alert(_lang_pack.shop_naverpay_disabled);
		return false;
	}
	if(naver.NaverPayButton.enable == 'N') {
		window.alert(_lang_pack.shop_naverpay_soldout);
		return false;
	}
	addCart(document.prdFrm, 'checkout');
}

function wishlist_nc() {
	if(naver.NaverPayButton.prdable != 'Y') {
		window.alert(_lang_pack.shop_naverpay_disabled);
		return false;
	}
	if(naver.NaverPayButton.enable == 'N') {
		window.alert(_lang_pack.shop_naverpay_soldout);
		return false;
	}

	if(is_mobile == 'mobile') {
		$.post(root_url+'/main/exec.php?exec_file=mypage/wish.exe.php', {'pagetype':'mobilecheckout', 'exec':'checkout', 'pno':prdFrm.pno.value}, function(url) {
			document.location.href = url;
		});
	} else {
		addWish(document.prdFrm, 'checkout');
	}
}

function order_nc() {
	if(naver.NaverPayButton.enable == 'N') {
		window.alert(_lang_pack.shop_error_nocart2);
		return false;
	}

	var fr = $('form[name=cartFrm], form[name=cartFrm0]');
	if(fr.length == 1) {
		fr = fr[0];
		fr.exec.value = 'checkout';
		if(npay_target == 'blank') fr.target = '_blank';
		fr.target = '_self';
		fr.submit();
		fr.target = hid_frame;
	} else {
		window.alert(_lang_pack.shop_naverpay_error);
	}
}


/**
 * 카카오페이 구매
 **/
function buy_talkpay(onSuccess, onFailure) 
{
	var f = document.prdFrm;
	$.post(f.action, $(f).serialize(), function(r) {
		if (typeof r == 'object') {
			if (r.orderSheetId) {
				onSuccess(r.orderSheetId);
			} else {
				window.alert(r.message);
				onFailure();
			}
		} else {
			window.alert(r);
			onFailure();
		}
	});
}

function wishlist_talkpay()
{
	window.alert(error.message);
}

function order_talkpay(onSuccess, onFailure)
{
	var fr = $('form[name=cartFrm], form[name=cartFrm0]');
	if(fr.length == 1) {
		var f = fr[0];
		f.exec.value = 'talkpay';
		$.post(f.action, $(f).serialize(), function(r) {
			if (r.orderSheetId) {
				onSuccess(r.orderSheetId);
			} else {
				window.alert(r.message);
				onFailure();
			}
		});
	} else {
		onFailure();
		window.alert('카카오페이 구매가 불가능합니다.');
	}
}


/* +----------------------------------------------------------------------------------------------+
' |  payco
' +----------------------------------------------------------------------------------------------+*/
function buy_payco() {
	addCart(document.prdFrm, 'payco');
}

function order_payco() {
	var fr = $('form[name=cartFrm], form[name=cartFrm0]');
	fr = fr[0];
	fr.exec.value = 'paycoCart';
	fr.submit();
}


function prdBoardView(type, no) {
	var ajax_divname = (type == 'review') ? 'revContent'+no : 'revQna'+no;
	var w = document.getElementById(ajax_divname);
	if(!w) return;

	if(w.style.display == 'block') {
		w.innerHTML = '';
		w.style.display = 'none';
		return;
	}

	var pg_type = pg_type ? pg_type : '';
	var hid_now = hid_now ? hid_now : '';
	var no = no ? no : '';

	$.post(root_url+'/main/exec.php', {'exec_file':'shop/'+type+'_reg.exe.php', 'urlfix':'Y', 'exec':'view', 'hid_now':hid_now, 'no':no, 'pg_type':pg_type}, function(r) {
		var mdv = $('#'+ajax_divname);
		var r = $(r).find('#'+ajax_divname).html();

		mdv.html(r).show().find('img').hide();
		var fwidth = 0;
		mdv.find('img').each(function() {
			var t = $(this);
			if(!fwidth) fwidth = t.parent().width();
			t.css('max-width', '100%').css('height', 'auto').show();
			if(t.width() > fwidth && fwidth > 0) t.width(fwidth);
		});
		mdv.css('display', 'block');
	});
}

function autoImgResize(obj, w) {
	if(obj.offsetWidth > w) obj.style.width  =w+'px';
}


/* +----------------------------------------------------------------------------------------------+
' |  다중옵션 관련 스크립트
' +----------------------------------------------------------------------------------------------+*/
function setMultiOption(o) {

	if ($('.otype4.input').length > 0) return; // 면적옵션과 같이 이용 불가
	
	var sel_options = ''; // 선택된 옵션 데이터
	var last_value = ''; // 현재까지 선택된 옵션 값
	var curr = o; // 선택한 옵션 
	var idx = o.index('.wing_multi_option', f);
	var next = null; // 다음 옵션

	if(o.prop('name').search(/\]$/) > 0) { // 세트 및 관련상품에서 같은 멀티 옵션은 처리 제외
		var of = o.parents('form');
		if (of.data('prd_type') == '4') {
			totalCal(of[0]);
			return;
		}
	}

	var pno = $(o).data('pno');
	if($('.necessary_Y.pno'+pno).length == 0) return;

	$('.wing_multi_option.pno'+pno, f).each(function(i) {
		var otype = $(this).data('type'); // 옵션타입
		var necessary = $(this).data('necessary'); // 필수여부
		if(o.data('necessary') == 'P' && this != o[0]) return;

		// 현재 옵션의 다음 옵션 찾기
		if(next == null && necessary == 'Y' && i > idx && o.prop('name') != this.name) { 
			next = this;
		}
		if(next == null && necessary == 'Y' && i == idx && !this.value) {
			next = this;
		}

		// 선택 된 옵션보다 하단에 있는 옵션을 선택 해제
		if(i > idx) { 
			if(otype == '5A' || otype == '5B') selectOptionChip(i+1, '')
			if(this.type == 'radio') this.checked = false;
			if(this.tagName == 'SELECT' || this.type == 'text') $(this).val('');
		}

		// 텍스트 옵션
		var min_length = parseInt($(this).data('min-length'));
		if(min_length > this.value.length) {
			return false;
		}

		if((otype == '3A' || otype == '3B') && this.checked == false) return; // 미선택 라디오버튼은 처리에서 제외

		sel_options += (sel_options == '') ? this.value : '<split_option>'+this.value;

		// 다음 옵션의 재고 체크용. 현재까지 선택된 옵션
		if(this.value && otype != '4A' && otype != '4B') {
			var tmp = this.value.split('::');
			last_value += '@'+tmp[3];
		}
	});

	if ($(f).data('prd_type') != '1') { // 일반 상품이 아니면 리턴
		return;
	}

	// 필수 옵션이 선택되지 않은 경우
	$('.wing_multi_option.necessary_Y.pno'+pno, f).each(function() {
		if (this.type == 'radio') {
			if ($('[name="'+this.name+'"]:checked', f).length == 0) {
				next = this;
				return false;
			}
		}
		if(this.value == '' && o.data('necessary') != 'P') {
			next = this;
			return false;
		}
	});

	if(next == null) { // 멀티 옵션 생성
		if(sel_options != '') appendMultiCart(f, sel_options, f.pno.value);
	} else { // 하단 옵션 재고 체크
		setOptionSoldout(f, next, last_value);
	}

}

// 멀티 카트 추가
function appendMultiCart(f, sel_options, pno) {
	// 옵션 데이터 구성
	var data = [];
	var new_multi_idx = 0;
	$('.multi_option_vals').each(function(idx) { // 기존 옵션
		if(typeof sel_options == 'number' && sel_options == idx) { // 삭제
			sel_options = null;
			$('.wing_multi_option').each(function(idx) {
				if (this.type == 'radio' || this.type == 'checkbox') this.checked = false;
				else if (this.type == 'hidden') {
					selectOptionChip(this.name.replace('option', ''), '');
				}
				else $(this).val('');
			});
			return;
		}
		data[new_multi_idx] = [
			this.value, 
			$('input[name="m_buy_ea['+idx+']"]').val(), 
			$('input[name="multi_option_prdcpn_no['+idx+']"]').val(),
			$('input[name="multi_option_pno['+idx+']"]').val()
		];
		new_multi_idx++;
	});
	if(sel_options) { // 새로 추가한 옵션
		var buy_ea = (f.buy_ea ? f.buy_ea.value : 1);
		var _opt = sel_options.split('<split_option>');
		var tmp = _opt[_opt.length-1].split('::');
		if(tmp[4] != "cpx0" && /^cpx[0-9]+$/.test(tmp[4]) == true && tmp[5]){
			buy_ea = tmp[5];
		}
		data[$('.multi_option_vals').length] = [sel_options, buy_ea, f.prdcpn_no.value];
	}

	if(sel_options || pno) {
		// 새로 추가한 옵션
		data[$('.multi_option_vals').length] = [
			sel_options, 
			buy_ea, 
			(f.prdcpn_no ? f.prdcpn_no.value : null),
			pno
		];

		// 중복 체크
		var tmp = sel_options.split('<split_option>');
		var hash = '';
		for(var i = 0; i < tmp.length; i++) {
			if(!tmp[i]) continue;
			var tmp2 = tmp[i].split('::');
			hash += (tmp2.length == 1) ? '_'+tmp[i] : '_'+tmp2[3];
		}
		hash = hash.replace(/[!"#$%&'()*+,.\/:;?@\[\\\]^`\{|\}~]/g,"\\$&");
		hash = (pno) ? pno+hash : f.pno.value+hash;
		if($('.multi_option_hash_'+hash).length > 0) {
			window.alert(_lang_pack.shop_error_selected);
			return false;
		}			
	}

	// 멀티옵션 출력
	var param = {
		'exec_file': 'shop/getAjaxData.php',
		'exec': 'getMultiOption',
		'from_ajax': 'Y',
		'data': data,
		'pno': f.pno.value, 
		'prdcpn_no': (f.prdcpn_no) ? f.prdcpn_no.value : 0,
		'prd_type': $(f).data('prd_type')
	}
	$.post(root_url+'/main/exec.php', param, function(r) {
		if(typeof r == 'string' && r != '') {
			window.alert(r);
			return;
		}

		$('#detail_multi_option').html(r.html);
		$('#detail_multi_option').children().each(function(idx) {
			$(this).addClass('wing_multi_option_'+idx);
		});

		if ($(f).data('prd_type') == '5' || $(f).data('prd_type') == '6') {
			totalCal(f);
		} else {
			r.pay_prc = parseFloat(r.pay_prc);
			$('#detail_multi_option_prc, .sell_prc_str_total').html(setComma(r.pay_prc.toFixed(currency_decimal)));
			if($('#detail_multi_r_option_prc').length > 0 && $.trim(r_currency_type)) {
				$('#detail_multi_r_option_prc').html(showExchangeFee(r.pay_prc)); // 참조금액
			}
		}
	});
}

// 상위 옵션 선택시 하위 옵션의 품절 체크
function setOptionSoldout(f, next, last_value) {
	var opt_no = next.name.replace('option', '');

	_otype = $('[name="option_type'+opt_no+'"]', f).val();
	_ness = $('[name="option_necessary'+opt_no+'"]', f).val();
	if(last_value && _ness == 'Y' && f.ea_type.value == '1') {
		$.get(root_url+'/main/exec.php?exec_file=shop/getAjaxData.php', {'exec':'getOptionStock', 'item_no':last_value}, function(r) {
			var soldout = r.split('@');
			switch(_otype) {
				case '2' :
				case '3' :
					var target = $('[name=option'+opt_no+']');
					if(target.prop('tagName') == 'SELECT') target = target.find('option');
					target.each(function() {
						this.disabled = false;
						if(this.text) this.text = this.text.replace(' ('+soldout_name+')', '');
						for(var key in soldout) {
							if(soldout[key] && this.value.search('::'+soldout[key]+'::cpx') > -1) {
								this.disabled = true;
								if(this.text) this.text += ' ('+soldout_name+')';
								break;
							}
						}
					});
				break;
				case '5' :
					var set = $('.optChipSet'+opt_no);
					set.each(function() {
						$(this).removeClass('soldout');
						for(var key in soldout) {
							if(soldout[key] == this.getAttribute('data')) {
								$(this).addClass('soldout');
							}
						}
					});
				break;
			}
		});
	}
}

function multiChgRemove(idx) {
	appendMultiCart(f, idx);
}

function multiChgEa(idx, value, min_ord) {
	var o = $('input[name="m_buy_ea['+idx+']"]');
	var n = o.val().toNumber() + value;
	if (!min_ord) {
		var min_ord = parseInt(f.min_ord.value);
	}
	if(n < 1) return;
	if(min_ord > n) return;

	o.val(n);

	var prdcpn_no = $('input[name="multi_option_prdcpn_no['+idx+']"]').val();
	appendMultiCart(f, null);
}

function multiTotalPrc() {
	var new_prc = 0;
	var hash = f.pno.value;
	var total_cnt = anx_cnt = 0;

	appendMultiCart(f, null);

	$("input[name^='multi_option_prc[']").each(function() {
		idx = this.name.replace(/multi_option_prc\[([0-9]+)\]/, '$1');
		new_prc += (this.value.toNumber())*$('[name="m_buy_ea['+idx+']"]', f).val().toNumber();
		comp_no = $('[name="comp_no['+idx+']"]', f).val().toNumber();
		o_hash = $('[name="multi_option_pno['+idx+']"]', f).val()

		total_cnt++; // 전체 추가 옵션 수량
		if(o_hash != hash) anx_cnt++; // 총 부속상품 수량
	});

	// 기본 상품이 옵션이 없고 부속옵션만 있을 경우 총 가격에 기본 상품 가격 추가
	if(anx_cnt > 0 && total_cnt == anx_cnt) {
		new_prc += f.pay_prc.value.toNumber();
	}
	$('#detail_multi_option_prc').html(setComma(new_prc.toFixed(currency_decimal)));
	if($('#detail_multi_r_option_prc').length > 0 && $.trim(r_currency_type)) $('#detail_multi_r_option_prc').html(showExchangeFee(new_prc)); // 참조금액

	return new_prc;
}

$(window).ready(function() {
	if(typeof f == 'undefined' || f.name != 'prdFrm') return;
	$('.wing_multi_option', f).change(function() {
		setMultiOption($(this));
	});
});

// 골라담기 버튼
function addMultiSet(multi_idx) {
	var pno = $('[name="pno['+multi_idx+']"').val();
	var opt_no = 0;
	var sel_options = '';
	while (1) {
		opt_no++;
		var obj = $('[name="option'+opt_no+'\['+multi_idx+'\]"]');
		if (obj.prop('type') == 'radio' || obj.prop('type') == 'checkbox') {
			obj = obj.filter(':checked');
		}
		if (obj.length == 0) break;
		sel_options += (sel_options == '') ? obj.val() : '<split_option>'+obj.val();
	}

	appendMultiCart(f, sel_options, pno);
	return false;
}

// 관련상품 옵션 실시간 재고확인
function setMultiOptionSoldout(f, opt_no, multi) {
	var opt_no = parseInt(opt_no);
	var last_value = '';
	var next_sel = null;

	// 다음 옵션이 있는지 검색
	var tmp_no = opt_no;
	while(1) {
		tmp_no++;
		tmp_sel = $(f.elements['option'+tmp_no+'\['+multi+'\]']);
		if(tmp_sel.length == 0) return;
		if(tmp_sel.data('necessary') == 'Y') {
			next_sel = tmp_sel;
			break;
		}
	}
	if(!next_sel) return;

	// 현재 선택된 옵션 구성
	for(var i = 1; i <= opt_no; i++) {
		sel = $(f.elements['option'+i+'\['+multi+'\]']);

		if(sel.data('necessary') != 'Y') continue;
		if(sel.val()) {
			last_value += '@'+sel.val().split('::')[3];
		}
	}
	if(!last_value) return;

	// 하위 옵션 품절 정보 가져오기
	$.get(root_url+'/main/exec.php?exec_file=shop/getAjaxData.php', {'exec':'getOptionStock', 'item_no':last_value}, function(r) {
		var soldout = r.split('@');
		$(next_sel).find('option').each(function() {
			this.disabled = false;
			this.text = this.text.replace(' ('+soldout_name+')', '');
			for(var key in soldout) {
				if(soldout[key] && this.value.search('::'+soldout[key]+'::cpx') > -1) {
					this.disabled = true;
					this.text += ' ('+soldout_name+')';
					break;
				}
			}
		});
	});
}

// 컬러칩
function selectOptionChip(opt_no, val, s) {
	var f = byName('prdFrm');
	var o = f.elements['option'+opt_no];

	// 품절체크
	var tmp = val.split('::');
	if($('.optChipItem'+tmp[3]).hasClass('soldout') == true) {
		window.alert(_lang_pack.shop_error_optionsoldout.format(tmp[0]));
		return false;
	}

	if(typeof selectColorOption == 'function') {
		selectColorOption(opt_no, tmp[3]);
	}

	// 정상진행
	o.value = val;
	optionCal(f, opt_no, val);
	if(s) setMultiOption($(o));
}

// 옵션변경시 부가이미지 변경 override 하여 스킨별로 커스텀 가능
function setOptionAddImage(json) {
	$('#product_add_image_list').html(json.html); // 부가이미지 영역 변경
	toggleAttatchImage(json.main_img); // 기본이미지 세팅

	// 부가이미지 마우스오버 세팅 재설정
	$('#product_add_image_list').find('img[src$="#addimg"]').each(function(idx) {
		var t = $(this);
		t.attr('upfile1', t.attr('src'));
		t.mouseover(function() {
			attatchAddImage(this,$('#mainImg').width());
		});
	});
}

function attatchAddImage(obj, w, h) {
	$('#mainImg').css({height:"auto", width:"auto"});
	if(w > 0) $('#mainImg').width(w);
	if(h > 0) $('#mainImg').width(h);

	$('#mainImg').attr('src', obj.src);
	$('#mainImg').attr('upfile1', $(obj).attr('upfile1'));
}

function cartChgOption(cno) {
	if(browser_type == 'mobile') {
		$.get('/main/exec.php', {'exec_file':'cart/cart_chgOption.php', 'from_ajax':'true', 'cno':cno}, function(r) {
			var layer = $("<div id='option_change_layer'></div>").html(r);
			layer.css({
				'position':'fixed',
				'z-index':'1001',
				'top':0,
				'width':'100%',
				'height':'100%',
				'overflow-y':'auto',
				'background':'#fff'
			});
			$('body').children().not('script').each(function(i) {
				var _this = $(this);
				if(_this.css('display') != 'none') {
					_this.addClass('wing_overlay_hide');
					_this.hide();
				}
			});
			$('body').append(layer);
		});
	} else {
		window.open(
			'/main/exec.php?exec_file=cart/cart_chgOption.php&cno='+cno,
			'changeCart',
			'"scrollbars=yes, resizable=no, width=200px, height=200px, left=450px, top=200px");'
		);
	}
}

function closecartChgOption() {
	$('.wing_overlay_hide').show().removeClass('wing_overlay_hide');
	$('#option_change_layer').remove();
}

function cartLiveCalc(f) {
	var cno = '';
	if (!f) {
		f = document.cartFrm;
	}
	$(f).find(':checked[name^=cno]').each(function() {
		cno += ','+this.value;
	});

	var sbscr = (f.sbscr && f.sbscr.value == 'Y') ? 'Y' : 'N';

	$.ajax({
		type: 'post',
		url:  root_url+'/main/exec.php?exec_file=cart/cart_calc.exe.php',
		dataType : 'json',
		data: {'cno': cno.substr(1), 'sbscr': sbscr},
		async : false,
		success: function(r) {
			$('.total_prd_prc').html((r.total==0) ? 0 : setComma(r.total_prd_prc));
			$('.total_order_price_cartlist').html((r.total==0) ? 0 : setComma(r.total_pay_prc));
			$('.total_order_price_r_cartlist').html((r.total==0) ? 0 : setComma(r.total_pay_prc_r));
			$('.dlv_prc_cart').html((r.total==0) ? 0 : setComma(r.total_dlv_prc));
			$('.dlv_prc_basic').html((r.total==0) ? 0 : setComma(r.total_dlv_prc_basic));
			$('.dlv_prc_prd').html((r.total==0) ? 0 : setComma(r.total_dlv_prc_prd));
			$('.total_sale1_prc').html((r.total==0) ? 0 : setComma(r.total_sale1_prc));
			$('.total_sale2_prc').html((r.total==0) ? 0 : setComma(r.total_sale2_prc));
			$('.total_sale3_prc').html((r.total==0) ? 0 : setComma(r.total_sale3_prc));
			$('.total_sale4_prc').html((r.total==0) ? 0 : setComma(r.total_sale4_prc));
			$('.total_sale6_prc').html((r.total==0) ? 0 : setComma(r.total_sale6_prc));
			$('.total_sale7_prc').html((r.total==0) ? 0 : setComma(r.total_sale7_prc));
			$('.total_sale8_prc').html((r.total==0) ? 0 : setComma(r.total_sale8_prc));
			$('.total_total_milage').html((r.total==0) ? 0 : setComma(r.total_total_milage));
			if (parseInt(removeComma(r.total_pay_prc)) < 1) {
				$('.payco').hide();
			} else {
				$('.payco').show();
			}
			
			var pdata = $('#partner_data').val();
			if(pdata) {
				var _ptnno = pdata.split("|");
				$.each(_ptnno, function(key, val) {
					var ptn = (r.ptndata) ? r.ptndata[val] : '';
					$('.prd_prc_cart_'+val).html((ptn) ? setComma(ptn.prd_prc) : 0);
					$('.dlv_prc_cart_'+val).html((ptn) ? setComma(ptn.dlv_prc) : 0);
					$('.dlv_prc_basic_'+val).html((ptn) ? setComma(ptn.dlv_prc_basic) : 0);
					$('.dlv_prc_prd_'+val).html((ptn) ? setComma(ptn.dlv_prc_prd) : 0);
					$('.milage_cart_'+val).html((ptn) ? setComma(ptn.milage_prc) : 0);
					$('.total_order_price_cartlist_'+val).html((ptn) ? setComma(ptn.pay_prc) : 0);
					$('.total_order_price_r_cartlist_'+val).html((ptn) ? setComma(ptn.pay_prc_r) : 0);
					$('.sale1_prc_'+val).html((ptn) ? setComma(ptn.sale1_prc) : 0);
					$('.sale2_prc_'+val).html((ptn) ? setComma(ptn.sale2_prc) : 0);
					$('.sale3_prc_'+val).html((ptn) ? setComma(ptn.sale3_prc) : 0);
					$('.sale4_prc_'+val).html((ptn) ? setComma(ptn.sale4_prc) : 0);
					$('.sale6_prc_'+val).html((ptn) ? setComma(ptn.sale6_prc) : 0);
					$('.sale7_prc_'+val).html((ptn) ? setComma(ptn.sale7_prc) : 0);
					$('.sale8_prc_'+val).html((ptn) ? setComma(ptn.sale8_prc) : 0);
				});
			}
		}
	});
}


// 재입고 알림 스크립트 Start ===============================
// 재입고 알림 신청 페이지 로드
function get_notify_restockPage(pno) {
	var exec_file = "shop/notify_restock.php";

	// 휴대전화, 수신동의 초기화
	$("input[name='buyer_cell']").val('');
	$("input[name='buyer_cell_agree']").prop('checked', false);

	$.ajax({
		type: "GET"
		, url: "/main/exec.php"
		, data: {exec_file:exec_file, pno:pno, now:hid_now}
		, success: function (response) {
			// 성공
			setDimmed();
			$('#notify_restock').remove();
			$('body').append('<div id="notify_restock">'+response.content+'</div>');
			$('#notify_restock').css({
				'zIndex': '1001',
				'position':'fixed',
				'top': '100px',
				'left': '50%',
				'margin-left': -($('#notify_restock').children(0).width()/2)+'px',
			});
			try {
				if(browser_type == 'mobile') {
					$('#notify_restock').css({
						'top': 0,
						'left': 0,
						'margin-left': 0,
					});
				}
			} catch(ex) {}
		}
		, error: function (response, status, error) {
			// 실패
		}
	});
}

// 선택 옵션의 하위옵션 체크
function notify_restock_optionSelect(opt_index, opt_no) {
	// pno hash
	var pno_hash = $("[name='pno']").val();

	// 선택된 opts 생성
	var opts = "";
	var opt_cnt = $("[name='notify_restock_opt_count']").val();
	var selected_array = [];
	for(var i=1; i<=opt_cnt; i++) {
		// 하위옵션 선택값 초기화
		if(i > opt_index) {
			$("[name='notify_restock_option_no"+i+"']").val('');
		}

		// 상위옵션 미선택시
		var _val = $("[name='notify_restock_option_no"+i+"']").val();
		if(i < opt_index && _val == "") {
			alert(_lang_pack.notify_restock_select_parent_option);

			// SELECT 선택값(selected) 초기화
			$("[opt_index='"+opt_index+"'] option:eq(0)").prop("selected", true);
			return false;
		}

		// 선택 값 세팅
		if(i == opt_index) {
			$("[name='notify_restock_option_no"+opt_index+"']").val(opt_no);
			_val = opt_no;
		}
		if(_val) selected_array.push("%"+_val+"%");
	}
	selected_array.sort();
	opts = "_" + selected_array.join("_") + "_";

	$.get(root_url+'/main/exec.php?exec_file=shop/getAjaxData.php', {'exec':'getOptionSoldoutCheck', 'opts':opts, 'pno_hash':pno_hash}, function(response) {
		var jsondata = JSON.parse(response);
		// 모든 옵션 loop
		$(".is_notify_restock_option").each(function() {
			var this_index = $(this).attr("opt_index");
			// 하위옵션에 대해서만 처리
			if(this_index > opt_index) {
				// 하위옵션 초기화처리
				$(this).css("display", "");
				$(this).removeClass("selected");
				if($(this)[0].type == "radio") {
					$(this).prop("checked", false);
					$("#notify_restock_option_txt"+$(this).val()).css("display", "");
				}

				// 옵션노출처리
				$("option:eq(0)", this).prop("selected", true); // 기본값 선택처리
				$("option", this).each(function () {
					$(this).prop("disabled", false);
					$(this).css("display", "");
					if ($(this).val() != "" && !jsondata.includes($(this).val())) {
						$(this).prop("disabled", true);
						$(this).css("display", "none");
					}
				});
			}
		});
	});

	$.post('/main/exec.php', $(document.notify_restock_form).serialize()+'&exec=getDetailPrice', function(r) {
		$('.notify_restock_prc').html(setComma(r.pay_prc));
	});
}

function close_notify_restockPage() {
	$('#notify_restock').fadeOut('fast', function() {
		removeDimmed();
		this.remove();
	});
	return false;
}
// 재입고 알림 스크립트 End   ===============================

/** 
	상품상세 더보기
*/
$(function() {
	if($('.wing-detail-more-contents').length > 0) {
		$('.wing-detail-more-view, .wing-detail-more-hide').click(function() {
			var target_h = 0;
			var now_h = $('.wing-detail-more-contents').height();
			var state = $('.wing-detail-more-area').data('state');

			if(state == 'opened') { // 닫기
				clearInterval(window.wing_detail_interval);
				$('.wing-detail-more-view').show();
				$('.wing-detail-more-hide').hide();
				$('.wing-detail-more-cover').show();
				target_h = $('.wing-detail-more-area').data('height');
			} else { // 열기
				window.wing_detail_interval = null;
				$('.wing-detail-more-view').hide();
				$('.wing-detail-more-hide').show();
				$('.wing-detail-more-cover').hide();
				target_h = $('.wing-detail-more-contents').prop('scrollHeight');
			}

			if(state == 'opened') {
				$(window).scrollTop($(window).scrollTop()-(now_h-parseInt(target_h)));
			}
			$('.wing-detail-more-contents').animate({'height': target_h}, function() {
				window.wing_detail_interval = setInterval(function() { // 이미지가 많을 경우
					target_h_ck = $('.wing-detail-more-contents').prop('scrollHeight');
					if (target_h_ck > target_h) $('.wing-detail-more-contents').height(target_h_ck);
				}, 1000);

				$('.wing-detail-more-area').data('state', ((state == 'opened') ? 'closed' : 'opened'));
			});
		});

		$('.wing-detail-more-hide').hide();

		if($('.wing-detail-more-contents').prop('scrollHeight') == $('.wing-detail-more-contents').innerHeight()) {
			$('.wing-detail-more-contents').height('auto');
			$('.wing-detail-more-view').hide();
			$('.wing-detail-more-cover').remove();
		}
	}
});
// 재입고 알림 스크립트 End   ===============================

/**
 * 마이페이지 결제수단 변경하기 레이어 출력
 */
function openChgPaytype(ono) {
	$.get('/main/exec.php', {'exec_file':'mypage/order_paytype.php', 'ono':ono}, function(r) {
		if(r.status == 'success') {
			setDimmed();
			$('body').append("<div id='chgPayType_area'>"+r.content+"</div>");
		} else {
			window.alert(r.message);
		}
	});
}

/**
 * 마이페이지 결제수단 변경하기 결제수단에 따른 결제 금액 재계산
 */
function mypageChgPayType() {
	var f = document.getElementById('chgMypagePayTypeFrm');
	if(f == null) {
		window.alert('결제방식 선택 오류');
		return false;
	}
	var param = $(f).serialize()+'&exec=recalculation';
	$.post('/main/exec.php', param, function(r) {
		$('[class^=order_area_sale]').hide();
		for(var key in r) {
			$('.chg_after_'+key).html(r[key]);
			if(/^sale[0-9]+$/.test(key) == true) {
				if(r[key] != '0') $('.order_area_'+key).show();
			}
		}
	});
}

function chgMypagePayType(f) {
	f.target=hid_frame

	if(!$(f.pay_type).val()) {
		window.alert('결제방식을 선택해주세요.');
		return false;
	}

	var msg = '주문서의 결제방식이 변경됩니다.';
	if(change_pay_recalc == 'Y') {
		msg += '\n결제금액이 현재 기준으로 재계산되며,\n변경된 내용은 되돌릴수 없습니다.';
	}

	if(confirm(msg+'\n\n계속 진행하시겠습니까?')) {
		return true;
	}
	return false;
}

function productURLCopy() {
	var clipboard = new Clipboard('.detail_url');
	clipboard.on('success', function(e) {
		dialogConfirm(null, '상품주소가 복사되었습니다.', {
			Ok: function() {
				dialogConfirmClose();
			}
		});
	});
	$('.detail_url').click();
}

function editAddressee(ono)
{
	window.open(
		root_url+'/main/exec.php?exec_file=mypage/sbscr_dlv_edit.php&sno='+ono, 
		'addressee_edit', 
		'status=no, width=100px, height=100px, top=100, left=100, scrollbars=yes'
	);
}