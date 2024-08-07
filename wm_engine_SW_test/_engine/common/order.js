var order_full_milage=0;
var total_order_price=0;
var milage_prc=0;
var naver_milage_prc=0;
var cpn_sale=0;
var ack=1;
var _pg_charge_alert=0;
var cpn_sale_only = false;
var tax_prc=0;
var no_milage= 0;
var only_setprd= 0;

function checkOrdFrm(f) {

    $('[name='+hid_frame+']').remove();
    $('body').prepend('<iframe name="'+hid_frame+'" src="about:blank" width="0" height="0" scrolling="no" frameborder="0" style="display:none"></iframe>');

	if(f.setOpenSSL) { // 전역변수 openssl_bak 은 common.js 에 선언되어있습니다
		if(!openssl_bak) openssl_bak = f.action;
		f.action = (f.setOpenSSL.checked == true) ? ssl_url : openssl_bak;
	}

	if(f.agree_guest_order && mlv > 9) {
		if(f.agree_guest_order.checked != true) {
			window.alert(_lang_pack.order_info_guestorder);
			return false;
		}
	}

	if(f.ono.value) {
		return true;
	}

	if(!checkBlank(f.buyer_name, _lang_pack.order_input_buyer)) return false;

	p1=checkPhone(f.buyer_phone ? f.buyer_phone : f.elements['buyer_phone[]']);
	p2=checkPhone(f.buyer_cell ? f.buyer_cell : f.elements['buyer_cell[]']);

	if(nec_buyer_phone=='S') {
		if(p1!=9 && p2!=9) {
			window.alert(_lang_pack.order_input_number);
			f.buyer_phone[p1].focus();
			f.buyer_phone[p1].select();
			return false;
		}
	} else {
		// 국가선택이 없을때만 전화번호 체크(해외배송이 아닐때만)
		if(!f.nations){
			if(p1!=9 && use_order_phone !='N') {
				window.alert(_lang_pack.order_input_phone);
				if(f.elements['buyer_phone[]']){
					f.buyer_phone[p1].focus();
					f.buyer_phone[p1].select();
				}else{
					f.buyer_phone.focus();
					f.buyer_phone.select();
				}
				return false;
			}
		}
		if(p2!=9) {
			window.alert(_lang_pack.order_input_cell);
			if(f.elements['buyer_cell[]']){
				f.buyer_cell[p2].focus();
				f.buyer_cell[p2].select();
			}else{
				f.buyer_cell.focus();
				f.buyer_cell.select();
			}
			return false;
		}
	}

	if(typeof f.buyer_zip!='undefined')	{
		if(!checkBlank(f.buyer_zip, _lang_pack.order_input_zipcode)) return false;
		if(!checkBlank(f.buyer_addr1, _lang_pack.order_input_addr1)) return false;
		if(!checkBlank(f.buyer_addr2, _lang_pack.order_input_addr2)) return false;
	}

	if(nec_buyer_email!='N') {
		if(!checkBlank(f.buyer_email, _lang_pack.order_input_email)) return false;
		if(!CheckMail(f.buyer_email.value)) {
			window.alert(_lang_pack.order_wrong_email);
			f.buyer_email.focus();
			return false;
		}
	}

	if(!checkBlank(f.addressee_name, _lang_pack.order_input_rname)) return false;

	if(!checkBlank(f.nations, _lang_pack.order_select_nations)) return false;

	p3=checkPhone(f.addressee_phone ? f.addressee_phone : f.elements['addressee_phone[]']);
	p4=checkPhone(f.addressee_cell ? f.addressee_cell : f.elements['addressee_cell[]']);
	if(nec_buyer_phone=='S') {
		if(p3!=9 && p4!=9) {
			window.alert(_lang_pack.order_input_rnumber);
			f.addressee_phone[p3].focus();
			f.addressee_phone[p3].select();
			return false;
		}
	} else {
		if(!checkBlank(f.addressee_phone_code, _lang_pack.order_input_rphone)) return false;

		// 국가선택이 없을때만 전화번호 체크(해외배송이 아닐때만)
		if(!f.nations){
			if(p3!=9 && use_order_phone !='N') {
				window.alert(_lang_pack.order_input_rphone);
				if(f.elements['addressee_phone[]']){
					f.addressee_phone[p3].focus();
					f.addressee_phone[p3].select();
				}else{
					f.addressee_phone.focus();
					f.addressee_phone.select();
				}
				return false;
			}
		}

		if(!checkBlank(f.addressee_cell_code, _lang_pack.order_input_rcell)) return false;
		if(p4!=9) {
			window.alert(_lang_pack.order_input_rcell);
			if(f.elements['addressee_cell[]']){

				f.addressee_cell[p4].focus();
				f.addressee_cell[p4].select();			
			}else{
				f.addressee_cell.focus();
				f.addressee_cell.select();
			}
			return false;
		}
	}

	if(!checkBlank(f.addressee_zip, _lang_pack.order_input_rzipcode)) return false;
	if(!checkBlank(f.addressee_addr1, _lang_pack.order_input_raddr1)) return false;
	if(f.addressee_addr3 && !checkBlank(f.addressee_addr3, _lang_pack.order_input_raddr1)) return false;
	if(f.addressee_addr4 && !checkBlank(f.addressee_addr4, _lang_pack.order_input_raddr1)) return false;
	if(!checkBlank(f.addressee_addr2, _lang_pack.order_input_raddr2)) return false;
	if(!checkBlank(f.delivery_com, _lang_pack.order_select_delivery_com)) return false;


	if(total_add_info>0) {
		for(i=0; i<total_add_info; i++) {
			if(typeof f.elements['add_info_style'+i]=='undefined' || skip_add_info[i]=="Y") {
				continue;
			}

			var tmp=f.elements['add_info_style'+i].value.split('::');

			if(tmp[0]=="Y") {
				if(tmp[1]=="radio" || tmp[1]=="checkbox") {
					if(!checkCB(f.elements['add_info'+i], _lang_pack.common_input_addinfo.format(tmp[2])))	return false;
				} else if(tmp[1]=="text" || tmp[1]=="date") {
					if(!checkBlank(f.elements['add_info'+i], _lang_pack.common_input_addinfo.format(tmp[2])))	return false;
				} else if(tmp[1]=="select" && !checkSel(f.elements['add_info_style'+i], _lang_pack.common_input_addinfo.format(tmp[2]))) {
					return false;
				}

			}
		}
	}

	//f.order_full_milage.value=order_full_milage;
	if(order_full_milage==0) {
		if(sbscr!='Y') {
			if(!checkCB(f.pay_type, _lang_pack.order_select_paytype)) return false;
		}
		var pay_type2_chk = "";
		if(typeof $.prop == 'function') {
			pay_type2_chk = $('#pay_type2').prop('checked');
		}else {
			pay_type2_chk = $('#pay_type2').attr('checked');
		}
		if(pay_type2_chk==true && (!f.sbscr_all || f.sbscr_all.value != 'N')) {
			if(!checkSel(f.bank, _lang_pack.order_input_bank)) return false;
			if(typeof f.bank_name!='undefined' && !checkBlank(f.bank_name, _lang_pack.order_input_depositor)) return false;
		}

		useMilage(f,3);
	}

	if(typeof f.reconfirm != 'undefined') {
		if(!checkCB(f.reconfirm, _lang_pack.order_reconfirm_checked)) return false;
	}

	if(document.getElementById('order2') && document.getElementById('order2').style.display!='none') {
		document.getElementById('order2').style.display='none';
	}

	if (document.getElementById('order3') && document.getElementById('order3').style.display!='none') {
		document.getElementById('order3').style.display='none';
	}

	return true;
}

function confirmOrder() {
	var ordLay1=document.getElementById('order1');
	var ordLay2=document.getElementById('order2');

	if($('#order2').css('display') == 'none') {
		if(!checkOrdFrm(document.ordFrm)) return;
	}
	layTgl(ordLay1);
	layTgl(ordLay2);
}

function copyInfo(f) {
	if(f.copy_info.checked == true) {
		$(f.addressee_name).val(f.buyer_name.value);
		if(f.addressee_cell) $(f.addressee_cell).val(f.buyer_cell.value);
		if(f.addressee_phone) $(f.addressee_phone).val(f.buyer_phone.value);
		if(f.buyer_zip) $(f.addressee_zip).val(f.buyer_zip.value);
		if(f.buyer_addr1) $(f.addressee_addr1).val(f.buyer_addr1.value);
		if(f.buyer_addr2) $(f.addressee_addr2).val(f.buyer_addr2.value);

		// old skin
		$('[name^=\'addressee_cell[\']', f).each(function(i) {
			this.value = $('[name^=\'buyer_cell[\']', f).eq(i).val();
		});
		$('[name^=\'addressee_phone[\']', f).each(function(i) {
			this.value = $('[name^=\'addressee_phone[\']', f).eq(i).val();
		});
	} else {
		$(f.addressee_name).val('');
		if(f.addressee_cell) $(f.addressee_cell).val('');
		if(f.addressee_phone) $(f.addressee_phone).val('');
		if(f.buyer_zip) $(f.addressee_zip).val('');
		if(f.buyer_addr1) $(f.addressee_addr1).val('');
		if(f.buyer_addr2) $(f.addressee_addr2).val('');

		// old skin
		$('[name^=\'addressee_cell[\']', f).val('');
		$('[name^=\'addressee_phone[\']', f).val('');
	}
}

function putOldAddressee(o,cart_weight) {

	if(!o) return false;
	of=o.form;

	if(o.value) {
		tmp1=o.value.split("<wisamall>");
		tmp2=tmp1[1];
		tmp3=tmp1[2];

		of.addressee_name.value=tmp1[0].replace(/\\(?!\\)/g, ''); //strip_slashes;
		if(of.nations){
			if(!tmp1[6]) tmp1[6] = "";
			of.nations.value = tmp1[6];
			getIntShipping(of.nations, cart_weight,of.delivery_com);
		}

		// 해외 배송 폼일때
		if(of.nations && tmp1[6]){		

			var _tmp2 = tmp2.split("-");
			var _tmp3 = tmp3.split("-");
			var _tmp2_code = _tmp2[0];
			var _tmp3_code = _tmp3[0];

			onChangeCountryState(of.nations,tmp1[4]);

			if(of.elements['addressee_phone[]']){
				var addressee_phone = of.elements['addressee_phone[]'];

				if(_tmp2_code) addressee_phone_code.value=_tmp2_code;
				if(_tmp2[0]) addressee_phone[0].value=_tmp2[0];
				if(_tmp2[1]) addressee_phone[1].value=_tmp2[1];
				if(_tmp2[2]) addressee_phone[2].value=_tmp2[2];
			}else{
				_tmp2.splice(0,1);
				_tmp2 = _tmp2.join("")
				of.addressee_phone.value=_tmp2.replace(/-/gi,"");
				of.addressee_phone_code.value = _tmp2_code;
			}

			if(of.elements['addressee_cell[]']){
				var addressee_cell = of.elements['addressee_cell[]'];

				if(_tmp3_code) addressee_cell_code.value=_tmp3_code;
				if(_tmp3[0]) addressee_cell[0].value=_tmp3[0];
				if(_tmp3[1]) addressee_cell[1].value=_tmp3[1];
				if(_tmp3[2]) addressee_cell[2].value=_tmp3[2];
			}else{
				_tmp3.splice(0,1);
				_tmp3 = _tmp3.join("")
				of.addressee_cell.value=_tmp3.replace(/-/gi,"");
				of.addressee_cell_code.value = _tmp3_code;
			}
		}else{
		// 일반 국내배송 폼일때
			if(of.elements['addressee_phone[]']){
				var addressee_phone = of.elements['addressee_phone[]'];
				var _tmp2 = tmp2.split("-");

				if(_tmp2[0]) addressee_phone[0].value=_tmp2[0];
				if(_tmp2[1]) addressee_phone[1].value=_tmp2[1];
				if(_tmp2[2]) addressee_phone[2].value=_tmp2[2];
			}else{
				of.addressee_phone.value=tmp2.replace(/-/gi,"");
			}

			if(of.elements['addressee_cell[]']){
				var addressee_cell = of.elements['addressee_cell[]'];
				var _tmp3 = tmp3.split("-");

				if(_tmp3[0]) addressee_cell[0].value=_tmp3[0];
				if(_tmp3[1]) addressee_cell[1].value=_tmp3[1];
				if(_tmp3[2]) addressee_cell[2].value=_tmp3[2];
			}else{
				of.addressee_cell.value=tmp3.replace(/-/gi,"");			
			}
		}

		of.addressee_zip.value=tmp1[3];
		of.addressee_addr1.value=tmp1[4].replace(/\\(?!\\)/g, ''); //strip_slashes
		of.addressee_addr2.value=tmp1[5].replace(/\\(?!\\)/g, ''); //strip_slashes
		if(of.addressee_addr3 && tmp1[7]) of.addressee_addr3.value = tmp1[7].replace(/\\(?!\\)/g, ''); //strip_slashes;
		if(of.addressee_addr4 && tmp1[8]) of.addressee_addr4.value = tmp1[8].replace(/\\(?!\\)/g, ''); //strip_slashes;


	} else {
		of.addressee_name.value=of.addressee_zip.value=of.addressee_addr1.value=of.addressee_addr2.value='';
		$(of.addressee_phone).val('');
		$(of.addressee_cell).val('');
		$(of).find("input[name^='addressee_phone[']").val('');
		$(of).find("input[name^='addressee_cell[']").val('');
		if(of.addressee_addr3) of.addressee_addr3="";
		if(of.addressee_addr4) of.addressee_addr4="";
	}
	//구 우편번호 유효성 체크
	if (of.addressee_zip.value.length < 7 && of.addressee_zip.value.indexOf('-') !== -1) {
		//길이가 7자 미만이면서 '-'가 포함된 경우
		of.addressee_zip.value = ''; //비움처리
	}
}

// 이벤트 할인/적립 정보 구하기
function getEventSale(f) {
	return;
}

// 회원등급별 혜택 정보 구하기
function getMemberSale(f) {
	return;
}

function useMilage(f,t) {
	var pay_type = $(':checked[name=pay_type]').val();

	if(f.milage_prc) {
		var _milage_prc = parseInt(f.milage_prc.value.replace(/,/g, ''));
		if(_milage_prc > 0) {
			var _tmp = _milage_prc % milage_use_unit;
			if(_tmp != 0 && isNaN(_tmp) == false) {
				window.alert('적립금은 '+setComma(milage_use_unit)+currency+' 단위로만 사용하실수 있습니다.');
				f.milage_prc.value = 0;
			}
		} else {
			f.milage_prc.value = 0;
		}
	}

	total_pay_price = eval(f.total_order_price.value-f.delivery_prc.value); // 전체 결제액

	// 해외배송시 
	if((delivery_fee_type == 'O' || (f.delivery_fee_type && f.delivery_fee_type.value == 'O')) && f.nations){
		if(f.nations.value) total_pay_price = eval(f.total_order_price.value);
	}

	if(typeof f.member_total_prc!='undefined') member_total_prc=eval(f.member_total_prc.value);
	else member_total_prc=0;

	if(order_cpn_paytype == 2) { // 쿠폰 현금결제시만
		if(document.getElementById("pay_type2").checked == true) {
			if(typeof f.coupon != 'undefined') {
				if(typeof f.off_cpn_use == 'undefined' || f.off_cpn_use.value == 'N') {
					for(ii=0; ii<f.coupon.length; ii++) {
						f.coupon[ii].disabled=false;
					}
				}
			}
			if(typeof f.cpn_auth_code != 'undefined') {
				f.cpn_auth_code.disabled=false;
			}
		}else{
			if(typeof f.coupon != 'undefined') {
				document.getElementById('no_cpn').checked=true;
				for(ii=0; ii<f.coupon.length; ii++) {
					f.coupon[ii].disabled=true;
				}
				cpn_sale=0;
			}
			if(typeof f.cpn_auth_code != 'undefined') {
				f.cpn_auth_code.disabled=true;
				f.off_cpn_use.value="N";
				document.all.off_cpn_used.innerText="";
				document.all.off_cpn_img2.style.display="none";
				document.all.off_cpn_img1.style.display="block";
			}
		}
	} else {
		if(document.getElementById("pay_type2") && document.getElementById("pay_type2").checked == true) {
			if(typeof f.coupon_pay_type != 'undefined') {
				if(typeof f.off_cpn_use == 'undefined' || f.off_cpn_use.value == 'N') {
					var cpt = document.getElementsByName('coupon_pay_type');
					var coupon = document.getElementsByName('coupon');
					for(ii=0; ii<cpt.length; ii++) {
						if(cpt[ii].value == 2) coupon[ii].disabled = false;
					}
				}
			}
		}else{
			if(typeof f.coupon_pay_type != 'undefined') {
				if(typeof f.off_cpn_use == 'undefined' || f.off_cpn_use.value == 'N') {
					var cpt = document.getElementsByName('coupon_pay_type');
					var coupon = document.getElementsByName('coupon');
					for(ii=0; ii<cpt.length; ii++) {
						if(cpt[ii].value == 2 && coupon[ii].checked == true) {
							coupon[ii].disabled = true;
							$('#no_cpn').click();
						}
						else if(cpt[ii].value == 2) coupon[ii].disabled = true;
					}
				}
			}
		}
	}

	if(order_milage_paytype == 2) { // 적립금 현금결제시만
		if(typeof f.milage_prc != 'undefined') {
			if(document.getElementById("pay_type2").checked == true) {
				f.milage_prc.disabled=false;
			}else{
				f.milage_prc.value=0;
				f.milage_prc.blur();
				f.milage_prc.disabled=true;
			}
		}
	}

	order_full_milage=0; // 모두 적립금으로 결제시 0 아님
	if(typeof f.milage_prc=='undefined') // 적립금 사용 불가
	{
		milage_prc=0;
	}
	else
	{
		milage_prc=f.milage_prc.value;
	}

	if(typeof f.totalUseAmount=='undefined') {

		naver_milage_prc=0;
	}
	else
	{

		naver_milage_prc=parseInt(f.totalUseAmount.value);
	}

	if(typeof f.emoney_prc=='undefined') // 예치금 사용 불가
	{
		emoney_prc=0;
	}
	else
	{
		var _emoney_prc = parseInt(f.emoney_prc.value.replace(/,/g, ''));
		if (_emoney_prc > 0) {
			emoney_prc=_emoney_prc;
		} else {
			f.emoney_prc.value = 0;
			emoney_prc=f.emoney_prc.value;
		}
	}

	if(typeof f.off_cpn_use=='undefined' || typeof f.off_cpn_price=='undefined' || typeof f.off_cpn_no=='undefined') // 2007-02-09 : 오프라인쿠폰 사용 불가
	{
		off_cpn_price=0;
	}
	else
	{
		if(f.off_cpn_use.value == "Y") off_cpn_price=f.off_cpn_price.value;
		else off_cpn_price=0;
	}

	if(t==1) // 적립금 수정 입력 onFocus
	{
		f.milage_prc.value=removeComma(milage_prc);
		f.milage_prc.select();
	}
	else if(t==2) // 2 : 적립금 수정 입력  onBlur
	{
		f.milage_prc.value=setComma(milage_prc);
	}
	else if(t==11) // 예치금 수정 입력 onFocus
	{
		f.emoney_prc.value=removeComma(emoney_prc);
		f.emoney_prc.select();
	}
	else if(t==12) // 2 : 예치금 수정 입력  onBlur
	{
		f.emoney_prc.value=setComma(emoney_prc);
	}

	milage_prc=removeComma(milage_prc).toNumber();
	emoney_prc=removeComma(emoney_prc).toNumber();
	off_cpn_price=eval(off_cpn_price);
	var usable_milage=eval(f.usable_milage.value);

	if(milage_prc>usable_milage) {
		window.alert(_lang_pack.order_error_usablemileage.format(setComma(usable_milage)));
		f.milage_prc.value=0;
		useMilage(f,1);
		return;
	}
	if(emoney_prc>usable_emoney) {
		window.alert(_lang_pack.order_error_usableemoney.format(setComma(usable_emoney)));
		f.emoney_prc.value=0;
		useMilage(f,11);
		return;
	}
	var total_emoneys = milage_prc+emoney_prc;
	total_pay_price -= total_emoneys;

	if(document.getElementById('pay_type4') && escrow_limit > 0) {
		if(document.getElementById('pay_type4').checked == true && total_pay_price < escrow_limit) { // 2007-08-24
			window.alert(_lang_pack.order_error_escrow.format(setComma(total_pay_price), setComma(escrow_limit)));
			document.getElementById('pay_type2').checked = true;
			return;
		}
	}

	if(total_pay_price <= 0 && total_emoneys > 0) {
		order_full_milage = 1;
	}

	// PG 결제시 추가 가격 안내
	var _pg_num = null;
	if(f.pay_type) {
		switch(f.pay_type.value) {
			case '1' : _pg_num = '1'; break;
			case '4' : _pg_num = '4'; break;
			case '5' : _pg_num = '5'; break;
			case '2' : _pg_num = null; break;
			case ''  : _pg_num = null; break;
			default  : _pg_num = 'E'; break;
		}
		if(_pg_num) {
			var pg_charge = parseInt(eval('pg_charge_'+_pg_num));
			if(pg_charge > 0 && window._pg_charge_alert != _pg_num) {
				window.alert(_lang_pack.order_info_pgup.format(pg_charge));
				window._pg_charge_alert = _pg_num;
			}
		}
	}

	// 주문금액 계산
	var dlv_prc = 0;
	var add_dlv_fee = 0;
	var free_dlv_prc = 0;
	var pay_type = $(f.pay_type).filter(':checked').val();
	var param = {'exec_file':'order/getDlvPrc.php', 'exec':'delivery'};
	if(pay_type) param['pay_type'] = pay_type;
	if(f.addressee_zip) param['addressee_zip'] = f.addressee_zip.value;
	if(f.addressee_zip) param['addressee_addr1'] = f.addressee_addr1.value;
	if(f.cart_selected) param['cart_selected'] = f.cart_selected.value;
	if(f.milage_prc) param['milage_prc'] = f.milage_prc.value;
	if(f.emoney_prc) param['emoney_prc'] = f.emoney_prc.value;
	if(f.coupon) param['coupon'] = $(':checked[name=coupon]', f).val();
	if (param['coupon'] === 'undefined') param['coupon'] = '';
	if(f.cpn_auth_code) param['cpn_auth_code'] = f.cpn_auth_code.value;
	if(f.off_cpn_use) param['off_cpn_use'] = f.off_cpn_use.value;
	if(f.nations) param['nations'] = f.nations.value;
	if(f.delivery_fee_type) param['delivery_fee_type'] = f.delivery_fee_type.value;
	if(sbscr) param['sbscr'] = sbscr;
	if(document.all.delivery_com) param['delivery_com'] = document.all.delivery_com.value;
	if($(':checked.selected_gift').length > 0) {
		var selected_gift = '';
		$(':checked.selected_gift').each(function() {
			selected_gift += ','+this.value;
		});
		param['selected_gift'] = selected_gift;
	}
	$.ajax({
		'url':'/main/exec.php',
		'data':param,
		'method':'post',
		'dataType':'json',
		'async':false,
		'cache':false,
		'success': function(r) {
			sum_prd_prc = parseFloat(r.sum_prd_prc);
			dlv_prc = parseFloat(r.dlv_prc);
			add_dlv_fee = parseFloat(r.add_dlv_prc);
			free_dlv_prc = parseFloat(r.free_dlv_prc);
			free_dlv_prc_m = parseFloat(r.free_dlv_prc_m);
			free_dlv_prc_e = parseFloat(r.free_dlv_prc_e);
			set_sale_prc = parseFloat(r.sale1);
			event_sale_prc = window.event_sale_prc = parseFloat(r.sale2-free_dlv_prc_e);
			event_sale_milage_amt = parseFloat(r.event_milage);
			time_sale_prc = parseFloat(r.sale3);
			msale_prc = window.msale_prc = parseFloat(r.sale4-free_dlv_prc_m);
			msale_milage = parseFloat(r.member_milage);
			cpn_sale = parseFloat(r.sale5);
			remain_cpn_prc = parseFloat(r.remain_cpn_prc);
			prdprc_sale_prc = parseFloat(r.sale6);
			prdcpn_sale_prc = parseFloat(r.sale7);
			sbscr_sale_prc = parseFloat(r.sale8);
			sale9_prc = parseFloat(r.sale9);
			pg_charge_prc = parseFloat(r.sale0);
			total_milage = parseFloat(r.total_milage);
			total_sale_prc = parseFloat(r.total_sale_prc);
			oversea_free_dlv_stat = r.oversea_free_dlv_stat;
			default_delivery_fee = r.default_delivery_fee;
			tax_prc = r.tax_prc;
			tax_use_delivery_com = r.tax_use_delivery_com;
			off_cpn_price = 0;
			gift_html = r.gift_html;
			sbscr_firsttime_pay_prc = r.sbscr_firsttime_pay_prc;
            cpn_name_list = r.cpn_name;
            no_milage = r.no_milage;
            only_setprd = r.only_setprd;

			if (window.addressee_zip != f.addressee_zip.value) {
				window.addressee_zip = f.addressee_zip.value
				if (r.delivery_range == false) {
					$('.form_result_unable_shipping').show().html(_lang_pack.order_error_unable_shipping.replace("\n", "<br>"));
					window.alert(_lang_pack.order_error_unable_shipping);
				} else {
					$('.form_result_unable_shipping').hide().html('');
				}
			}

			// 쿠폰 사용시 적립금 사용 불가
			if(order_cpn_milage == 2) {
				if(cpn_sale > 0 || prdcpn_sale_prc > 0) {
					$(f.milage_prc).val('0').prop('disabled', true).css('background', '#f8f8f8');
				}else{
					$(f.milage_prc).prop('disabled', false).css('background', '#fff');
				}
			}

			if(r.no_milage > 0) {
				$(f.milage_prc).prop('disabled', true).css('background', '#f8f8f8');
			}
			if(r.no_cpn > 0) {
				$('input[name=coupon]').prop('disabled', true);
				$('#no_cpn').prop('checked', true);
				$(f.cpn_auth_code).prop('disabled', true).css('background', '#f8f8f8');
                $('.oncpn_cnt').html('0');
			}
			else $('#no_cpn').prop('checked', false);

            if (only_setprd > 0) {
                $('input[name=coupon]').prop('disabled', true);
                $('#no_cpn').prop('checked', true);
                $(f.cpn_auth_code).prop('disabled', true).css('background', '#f8f8f8');
                $('.oncpn_cnt').html('0');
            }

			// 해외배송시 
			if((delivery_fee_type == 'O' || param['delivery_fee_type'] == 'O') && f.nations){
				f.delivery_prc.value = dlv_prc;
			}

			if(r.delivery_com_use == 'F' || r.delivery_com_use == 'O'){
				var _delivery_msg = "";
				
				if(r.delivery_com_use == 'F') _delivery_msg = _lang_pack.delivery_impossible_msg;
				else _delivery_msg = _lang_pack.delivery_overweight_msg;

				alert(_delivery_msg);
				f.nations.value="";
				if(f.delivery_com.type == 'select-one'){
					f.delivery_com.value = "";
					tax_prc = 0;
				}
			}

			total_pay_price -= total_sale_prc;

			if(param['off_cpn_use'] == 'Y' && cpn_sale == 0) {
				window.alert(_lang_pack.order_error_cpnuse7);
				cancelOffCpn(true);
			}

			if(param['coupon'] && cpn_sale == 0) {
				if(remain_cpn_prc > 0) {
					window.alert(_lang_pack.order_error_cpnuse8);
				}
				cancelOnCpn();
				cpn_sale = 0;
				useMilage(f, 3);
				window.alert(_lang_pack.order_error_cpnuse7);
				return;
			}

			// 기본 배송비
			total_pay_price = total_pay_price+dlv_prc;
			if($('#delivery_r_prc2').length > 0) $('#delivery_r_prc2').text(showExchangeFee(dlv_prc));

			// 추가 배송비
			var add_dlv_fee_str = '';
			if(add_dlv_fee > 0) {
				add_dlv_fee_str = ' ('+_lang_pack.order_service_dtype3+' '+setComma(add_dlv_fee)+')'
			}

			// 배송비 출력
			$('#delivery_prc2, .delivery_prc').text(setComma(dlv_prc)+add_dlv_fee_str);
			if(dlv_prc == 0) {
				$('.delivery_prc').html(_lang_pack.order_info_freedelivery);
			}
			$('.dlv_prc_basic').text(setComma(r.basic_dlv_prc)); // 일반 배송비
			$('.dlv_prc_prd').text(setComma(r.prd_dlv_prc)); // 개별 배송비

			// 이벤트 무료배송
			var sale_summary = '';
			if(free_dlv_prc_e > 0) {
				//total_pay_price = total_pay_price-free_dlv_prc_e;
				sale_summary += '<li><span>'+_lang_pack.order_service_freedelivery+'</span> : '+setComma(free_dlv_prc_e)+' '+currency+'</li>';
			}

			// 회원 무료배송
			if(free_dlv_prc_m > 0) {
				//total_pay_price=total_pay_price-free_dlv_prc_m;
				dlv_prc = 0;
				sale_summary += '<li><span>'+_lang_pack.order_service_mfreedelivery+'</span> : '+setComma(free_dlv_prc_m)+' '+currency+'</li>';
			}

			// 예치금, 적립금 초과 사용 체크
			if(total_emoneys > 0 && total_emoneys > total_pay_price+total_emoneys) {
				if(milage_prc > 0 && emoney_prc > 0) {
					window.alert(_lang_pack.order_error_overpoint);
					f.milage_prc.value = 0;
                    f.emoney_prc.value = 0;
					useMilage(f, 1);
				} else if(milage_prc > 0) {
					window.alert(_lang_pack.order_error_overmileage);
					f.milage_prc.value = 0;
					useMilage(f, 1);
				} else {
					window.alert(_lang_pack.order_error_overemoney);
					f.emoney_prc.value = 0;
					useMilage(f, 11);
				}
				return;
			}

			if(currency_decimal > 0){
				if(String(total_pay_price).indexOf('.') > -1 && currency_decimal > 0) total_pay_price = total_pay_price.toNumber().toFixed(currency_decimal);
			}else{
				total_pay_price = Math.ceil(total_pay_price);
			}

			$('#gift_area').html(gift_html);

			if(tax_prc > 0) total_pay_price = (total_pay_price.toNumber() + tax_prc.toNumber()).toFixed(currency_decimal);

			$('#event_sale_prc').html(setComma(event_sale_prc));
			$('#event_sale_milage').html(setComma(event_sale_milage_amt));
			$('#event_r_sale_milage').html(showExchangeFee(event_sale_milage_amt));
			$('#msale_prc').html(setComma(msale_prc));
			if($('#msale_r_prc').length > 0) $('#msale_r_prc').html(showExchangeFee(msale_prc));
			$('.prdprc_sale_prc').html(setComma(prdprc_sale_prc));
			$('.prdcpn_sale_prc').html(setComma(prdcpn_sale_prc));
			$('.sbscr_sale_prc').html(setComma(sbscr_sale_prc));
			$('.sale9_prc').html(setComma(sale9_prc));
			$('#total_order_price_div').html(setComma(total_pay_price));
			$('#total_r_order_price_div').html(showExchangeFee(total_pay_price));
			$('#total_order_price_div2').html(setComma(total_pay_price));
			$('.use_milage_prc').html(setComma(milage_prc));
			$('.use_emoney_prc').html(setComma(emoney_prc));
			$('.use_emoney_field').css('display', (emoney_prc == 0) ? 'none' : '');
			$('.use_milage_field').css('display', (milage_prc == 0) ? 'none' : '');
			$('.total_milage').html(setComma(total_milage));
			if($('.use_tax_field').length > 0) $('.use_tax_field').css('display', (tax_prc == 0) ? 'none' : '');

			if((param['delivery_fee_type'] == 'O' || delivery_fee_type == 'O')){
				//if(f.nations.value && tax_use_delivery_com == 'N') alert(_lang_pack.order_no_tax_msg);

				if($('#tax_prc').length > 0) $('#tax_prc').html(setComma(tax_prc));
				if($('#tax_r_prc').length > 0) $('#tax_r_prc').html(showExchangeFee(tax_prc));
			}

			// 시리얼 쿠폰
			if(cpn_sale > 0 && $(f.off_cpn_use).val() == 'Y') {
				$('#off_cpn_used').html(_lang_pack.order_info_offcpn+' - '+setComma(cpn_sale));
				$('#off_cpn_img1').hide();
				$('#off_cpn_img2').show();
			}

			var prd_milage = total_milage-event_sale_milage_amt-msale_milage; // 상품기본적립금

			$('.order_saleinfo_set_prc').html(setComma(set_sale_prc));
			$('.order_saleinfo_event_prc').html(setComma(event_sale_prc));
			$('.order_saleinfo_event_dlv').html(setComma(free_dlv_prc_e));
			$('.order_saleinfo_timesale').html(setComma(time_sale_prc));
			$('.order_saleinfo_member_prc').html(setComma(msale_prc));
			$('.order_saleinfo_member_dlv').html(setComma(free_dlv_prc_m));
			$('.order_saleinfo_member_milage').html(setComma(msale_milage));
			$('.order_saleinfo_event_milage').html(setComma(event_sale_milage_amt));
			$('.order_saleinfo_cpn_prc').html(setComma((cpn_sale+off_cpn_price).toFixed(currency_decimal)));
			$('.order_saleinfo_prd_prc').html(setComma(prdprc_sale_prc));
			$('.order_saleinfo_prdcpn_prc').html(setComma(prdcpn_sale_prc));
			$('.order_saleinfo_sbscr_prc').html(setComma(sbscr_sale_prc));
			$('.order_saleinfo_sale9_prc').html(setComma(sale9_prc));
			$('.order_saleinfo_pgcharge_prc').html(setComma(pg_charge_prc*-1));
			$('.order_saleinfo_prd_milage').html(setComma(prd_milage));
			$('.order_info_sale_prc').html(setComma(r.pay_prc));
			$('.order_info_firsttime_pay_prc').html(setComma(sbscr_firsttime_pay_prc));
			$('.order_info_free_dlv_prc_e').html(setComma(free_dlv_prc_e));
			$('.order_info_free_dlv_prc_m').html(setComma(free_dlv_prc_m));

			// 할인 영역별 숨김
			$('.order_area_set_prc').css('display', (set_sale_prc == 0) ? 'none' : '');
			$('.order_area_event_prc').css('display', (event_sale_prc == 0) ? 'none' : '');
			$('.order_area_event_dlv').css('display', (free_dlv_prc_e == 0) ? 'none' : '');
			$('.order_area_event_milage').css('display', (event_sale_milage_amt == 0) ? 'none' : '');
			$('.order_area_timesale').css('display', (time_sale_prc == 0) ? 'none' : '');
			$('.order_area_member_prc').css('display', (msale_prc == 0) ? 'none' : '');
			$('.order_area_member_dlv').css('display', (free_dlv_prc_m == 0) ? 'none' : '');
			$('.order_area_member_milage').css('display', (msale_milage == 0) ? 'none' : '');
			$('.order_area_cpn_prc').css('display', ((cpn_sale+off_cpn_price) == 0) ? 'none' : '');
			$('.order_area_prd_prc').css('display', (prdprc_sale_prc == 0) ? 'none' : '');
			$('.order_area_prdcpn_prc').css('display', (prdcpn_sale_prc == 0) ? 'none' : '');
			$('.order_area_pgcharge_prc').css('display', (pg_charge_prc == 0) ? 'none' : '');
			$('.order_area_total_milage').css('display', (total_milage == 0) ? 'none' : '');
			$('.order_area_prd_milage').css('display', (prd_milage == 0) ? 'none' : '');
			$('.order_area_sbscr_prc').css('display', (sbscr_sale_prc == 0) ? 'none' : '');
			$('.order_area_sale9_prc').css('display', (sale9_prc == 0) ? 'none' : '');
			$('.order_area_total_sale_prc').css('display', (total_sale_prc == 0) ? 'none' : '');
			$('.order_area_free_dlv_prc_e').css('display', (free_dlv_prc_e == 0) ? 'none' : '');
			$('.order_area_free_dlv_prc_m').css('display', (free_dlv_prc_m == 0) ? 'none' : '');
			$('.order_area_firsttime_pay_prc').css('display', (sbscr_firsttime_pay_prc == 0) ? 'none' : '');

			if( $('[name=sbscr_all]').length ) {
				var sbscr_all = $('[name=sbscr_all]:checked').val();
				$('.order_area_firsttime_pay_prc').css('display', (sbscr_firsttime_pay_prc == 0 && sbscr_all != 'N') ? 'none' : '');
			}

			// 총 할인가격
			if(set_sale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_setsale+'</span> : '+setComma(set_sale_prc)+' '+currency+'</li>';
			if(event_sale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_eventsale+'</span> : '+setComma(event_sale_prc)+' '+currency+'</li>';
			if(free_dlv_prc_e > 0) sale_summary += '<li><span>'+_lang_pack.order_service_freedelivery+'</span> : '+setComma(free_dlv_prc_e)+' '+currency+'</li>';
			if(time_sale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_timesale+'</span> : '+setComma(time_sale_prc)+' '+currency+'</li>';
			if(msale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_msale+'</span> : '+setComma(msale_prc)+' '+currency+'</li>';
			if(free_dlv_prc_m > 0) sale_summary += '<li><span>'+_lang_pack.order_service_mfreedelivery+'</span> : '+setComma(free_dlv_prc_e)+' '+currency+'</li>';
			if(msale_milage > 0) sale_summary += '<li><span>'+_lang_pack.order_service_mmileage+'</span> : '+setComma(msale_milage)+' '+currency+'</li>';
			if(event_sale_milage_amt > 0) sale_summary += '<li><span>'+_lang_pack.order_service_emileage+'</span> : '+setComma(event_sale_milage_amt)+' '+currency+'</li>';
			if(cpn_sale > 0) sale_summary += '<li><span>'+_lang_pack.order_service_cpnsale+'</span> : '+setComma(cpn_sale)+' '+currency+'</li>';
			if(sbscr_sale_prc > 0) sale_summary += '<li><span>정기배송할인금액</span> : '+setComma(sbscr_sale_prc)+' '+currency+'</li>';
			if(sale9_prc > 0) sale_summary += '<li><span>상품별수량할인금액</span> : '+setComma(sale9_prc)+' '+currency+'</li>';
			if(prdprc_sale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_prcsale+'</span> : '+setComma(prdprc_sale_prc)+' '+currency+'</li>';
			if(prdcpn_sale_prc > 0) sale_summary += '<li><span>'+_lang_pack.order_service_cpnsale+'2</span> : '+setComma(prdcpn_sale_prc)+' '+currency+'</li>';
			$('.total_sale_summary').html("<ul>"+sale_summary+"</ul>");
			$('.total_sale_prc').html(setComma((total_sale_prc).toFixed(currency_decimal)));
			if($('.total_r_sale_prc').length > 0) $('.total_r_sale_prc').html(showExchangeFee(total_sale_prc));

			// 전체 결제금액이 적립금+예치금일 경우
			if((milage_prc > 0 || emoney_prc > 0) && total_pay_price == 0 && (sum_prd_prc+dlv_prc-total_sale_prc) == (milage_prc+emoney_prc)) {
				var tmp = $(':radio[name=pay_type]');
				if(tmp.filter(':checked').length == 0) {
					tmp.filter('[value=1]').attrprop('checked', true);
					if(tmp.filter(':checked').length == 0) {
						tmp.eq(0).attrprop('checked', true);
					}
				}
				$('.pay_methods').hide();
			} else {
				$('.pay_methods').show();
			}
            // 적용쿠폰 이름 노출
			var cpn_html = '';
            if( cpn_name_list ) {
                var cpn_name_arr = cpn_name_list.split('|');
                cpn_name_arr.forEach(function (cname) {
                    cpn_html += ( cname ? '<li><p><strong>'+cname+'</strong> 쿠폰이 적용되었습니다.</p></li>' : "");
                });
            }
			$('.used_cpn_list').html(cpn_html);
            // 쿠폰레이어 할인금액 노출
            $('#cpnsale_prc').html(setComma(cpn_sale+prdcpn_sale_prc));
            $('#cpnsale_prc_view').val(setComma(cpn_sale+prdcpn_sale_prc));
		},
		'error':function(r,s,e){
		alert(e);
		}

	});

	if(document.getElementById('cash_reg') && cash_receipt_use=='Y') { // 2007-06-29 : 현금영수증 신청
		if(document.getElementById('pay_type2') && document.getElementById('pay_type2').checked==true && total_pay_price > 0) {
			f.cash_reg_num.disabled=false;
		}else{
			f.cash_reg_num.disabled=true;
			f.cash_reg_num.value='';
		}
	}
}

function useCpn(on,sale_type,sale_prc,prc_limit,sale_limit,stype,cpn_no,use_limit) {

	var f = document.ordFrm;
	var obj = f.coupon[on];
	var nc = document.getElementById('no_cpn');
	var pay_type = $(':checked[name=pay_type]').val();

	var total_order_price = eval(f.total_order_price.value)-f.delivery_prc.value.toNumber();
	var event_sale_prc = window.event_sale_prc;
	var msale_prc = window.msale_prc;
	cpn_sale_only = false;

	if(use_cpn_milage == 'N' && use_cpn_milage_msg == 'Y' && typeof cpn_no != 'undefined') {
		window.alert(_lang_pack.order_error_cpnuse1);
	}

	if(use_limit == 3) {
		msale_prc = 0;
		event_sale_prc = 0;
	}

	$.get(root_url+'/main/exec.php?exec_file=order/coupon_attach_check.exe.php&nocache='+(new Date().getTime()), {"pay_type":pay_type, "cpn_no":cpn_no, "use_limit":use_limit, "event_sale_prc":event_sale_prc, "msale_prc":msale_prc, "cart_selected":cart_selected}, function(result) {
		var json = $.parseJSON(result);
		var result = json.cpn_prc.toNumber();

		if(result < 1) {
			if(json.cpnerr) window.alert(json.cpnerr);
			cancelOnCpn();
			cpn_sale = 0;
			useMilage(f, 3);
			return false;
		}

		total_order_price = parseInt(result);

		if(nc.checked == true) {
			cpn_sale = 0;
		} else {
			prc_limit=eval(prc_limit);
			sale_prc=eval(sale_prc);

			if(stype == 3 || stype == 4) { // 무료배송쿠폰
				if(prc_limit && prc_limit>total_order_price) {
					window.alert(_lang_pack.order_error_cpnuse2.format(setComma(prc_limit)));
					cpn_sale = 0;
					nc.checked = true;
					useMilage(f,3);
					return;
				}
				if(parseInt(f.delivery_prc.value.toNumber()) == 0) {
					window.alert(_lang_pack.order_error_cpnuse3);
					cpn_sale = 0;
					nc.checked = true;
					useMilage(f,3);
					return;
				}
				cpn_sale = f.delivery_prc.value.toNumber();
			} else {
				var cpn_sale_prc = 0;  // 쿠폰 할인 적용될 상품금액
				switch(use_limit) {
					case '1' : // 이벤트, 회원할인을 제외하고 쿠폰처리
						//cpn_sale_prc = total_order_price - event_sale_prc - msale_prc;
						if(event_sale_prc > 0 && event_ptype == 2) {
							//window.alert('쿠폰 사용 시 현금할인이벤트를 받으실 수 없습니다.\n각 할인금액을 확인하신 후 쿠폰 사용여부를 재검토 해 주세요.');
						}
						cpn_sale_prc = total_order_price;
					break;
					case '2' : // 할인이 있을경우 쿠폰사용 하지 않음
						if(event_sale_prc <= 0 && msale_prc <= 0) cpn_sale_prc = total_order_price;
						else {
							window.alert(_lang_pack.order_error_cpnuse4);
							cancelOnCpn();
							return false;
						}
					break;
					case '3' : // 다른 할인을 취소하고 쿠폰할인만 처리
						cpn_sale_only = true;
						cpn_sale_prc = total_order_price;
					break;
					default  : // 중복할인
						cpn_sale_prc = total_order_price;
					break;
				}

				if(prc_limit && prc_limit > cpn_sale_prc) {
					if(event_sale_prc > 0 || msale_prc > 0) window.alert(_lang_pack.order_error_cpnuse5.format(setComma(prc_limit)));
					else window.alert(_lang_pack.order_error_cpnuse6.format(setComma(prc_limit)));

					nc.checked = true;
					cpn_sale = 0;
					return;
				}

				if(sale_type == 'm') {
					cpn_sale = sale_prc;
				} else if(sale_type == 'e') {
					cpn_sale = cpn_sale_prc;
				} else {
					cpn_sale = cpn_sale_prc*(sale_prc/100);
					if(f.currency_decimal && parseInt(f.currency_decimal.value) > 0){
						cpn_sale = (cpn_sale/10)*10;
					}else{
						cpn_sale = Math.floor(cpn_sale/10)*10;
					}
					sale_limit = eval(sale_limit);
					if(cpn_sale > sale_limit) cpn_sale = sale_limit;
				}
			}
		}

		if(cpn_sale > 0 && cpn_sale > total_pay_price) {
			cpn_sale = total_pay_price;
		}
		if(order_cpn_milage == 2) {
			if(sale_type) {
				if(browser_type == 'mobile') {
					cwith=confirm(_lang_pack.order_coupon_milage);
					if(cwith == false) {
						cancelOnCpn();
						cpn_sale = 0;
						if(typeof f.milage_prc != 'undefined') {
							f.milage_prc.value = milage_prc;
						}
						useMilage(f,3);
					} else {
						useMilage(f,3);
					}
				} else {
					dialogConfirm(null, _lang_pack.order_coupon_milage, {
						Ok: function() {
							dialogConfirmClose();
							useMilage(f,3);
						},
						Cancel: function() {
							cancelOnCpn();
							cpn_sale = 0;
							if(typeof f.milage_prc != 'undefined') {
								f.milage_prc.value = milage_prc;
							}
							dialogConfirmClose();
							useMilage(f,3);
							return;
						}
					});
				}
			}
		}
		useMilage(f,3);
	});
}

function useCpn3(on,sale_type,sale_prc,prc_limit,sale_limit,stype,cpn_no,sale) {
	var f = document.ordFrm;
	var obj = f.coupon[on];
	var nc = document.getElementById('no_cpn');
	if(nc.checked==true) {
		cpn_sale=0;
	} else {
		prc_limit=eval(prc_limit);
		sale_prc=eval(sale_prc);

		total_order_price=eval(f.total_order_price.value)-f.delivery_prc.value.toNumber(); // 상품금액

		if(prc_limit && prc_limit>total_order_price) {
			window.alert(order_error_cpnuse6.format(setComma(prc_limit)));
			nc.checked = true;
			cpn_sale = 0;
			return;
		}

		if(stype == 2) {
			cpn_sale=sale;
		} else {
			if(sale_type=='m') {
				cpn_sale=sale_prc;
			} else {
				cpn_sale=total_order_price*(sale_prc/100);
				cpn_sale=Math.floor(cpn_sale/10)*10;
				sale_limit=eval(sale_limit);
				if(cpn_sale>sale_limit) {
					cpn_sale=sale_limit;
				}
			}
		}
	}

	document.ordFrm.c_sale_price.value=cpn_sale; // 쿠폰 할인 금액
	useMilage(f,3);
}

// 쿠폰 사용 초기화
function cancelOnCpn(){
	var no_cpn = document.getElementById('no_cpn');
	if (no_cpn) no_cpn.checked = true;
	$('#select_coupon_list').val('');
	$(':checked[name=coupon]', f).val('');
}

function giftName(f) {
	if(f.gift_name_no.checked==true) {
		textDisable(f.gift_name,1);
	} else {
		textDisable(f.gift_name);
	}
}

function checkOrdGift(f) {
	if(total_gift < 2) {
		if(!checkCB(f["gift[]"], _lang_pack.order_select_gift)) return false;
	} else {
		var ck = 0;
		for(ii = 0; ii < f["gift[]"].length; ii++) {
			if(f["gift[]"][ii].checked) ck++;
		}
		if(ck < 1) {
			window.alert(_lang_pack.order_select_gift); return false;
		}
	}
}

function cpnAuthCode() { // 오프라인 쿠폰 인증코드입력
	var f = document.ordFrm;

	if(order_cpn_paytype == 2 && document.getElementById("pay_type2").checked == false) {
		window.alert(_lang_pack.order_error_offcpn1);
		return;
	}
	if (order_cpn_paytype == 3) {
		window.alert('사용가능한 쿠폰이 존재하지 않습니다.')
		return;
	}

	var fd = f.cpn_auth_code;
	if(!checkBlank(fd, _lang_pack.order_error_offcpn2)) return;
	var fname = eval(hid_frame);

	fname.window.location = root_url+"/main/exec.php?exec_file=order/order_cpn_auth.php&total_order_price="+total_order_price+"&auth_code="+fd.value;
}

function useOffCpn() { // 오프라인 쿠폰 사용
	var f = document.ordFrm;
	var is_cash = (document.getElementById("pay_type2") && document.getElementById("pay_type2").checked == true) ? true : false;

	if((order_cpn_paytype == 2 && is_cash == false) || (order_cpn_paytype == 1 && is_cash == false && f.off_cpn_pay_type.value == 2)) {
		window.alert(_lang_pack.order_error_offcpn1);
		return;
	}
	//if(!confirm(_lang_pack.order_confirm_offcpn)) return;

	var off_cpn_sale = 0;
	var prc_limit = f.off_cpn_min.value.toNumber();
	var sale_prc = f.off_cpn_sale.value.toNumber();
	var sale_type = f.off_cpn_type.value;
	var total_order_price = f.total_order_price.value.toNumber() - f.delivery_prc.value.toNumber();
	if(f.delivery_fee_type) delivery_fee_type = f.delivery_fee_type.value;

	if(delivery_fee_type == 'O' && f.nations){
		if(f.nations.value) total_order_price = f.total_order_price.value.toNumber();
	}

	total_order_price = total_order_price.toFixed(currency_decimal).toNumber();


	switch(f.off_cpn_use_limit.value) {
		case '1' : // 이벤트, 회원할인을 제외하고 쿠폰처리
			cpn_sale_prc = total_order_price - window.event_sale_prc - window.msale_prc;
		break;
		case '2' : // 할인이 있을경우 쿠폰사용 하지 않음
			if(event_sale_prc <= 0 && msale_prc <= 0) cpn_sale_prc = total_order_price;
			else {
				window.alert(_lang_pack.order_error_offcpn4);
				return false;
			}
		break;
		default  : // 중복할인
			cpn_sale_prc = total_order_price;
		break;
	}

	if(prc_limit && prc_limit > cpn_sale_prc) {
		$('#off_cpn_div1').show();
		$('#off_cpn_div2').hide();
		window.alert(_lang_pack.order_error_cpnuse2.format(setComma(prc_limit))); 
		return; 
	}

	if(sale_type=='m') off_cpn_sale = sale_prc;
	else {
		off_cpn_sale=cpn_sale_prc*(sale_prc/100);
		//off_cpn_sale=Math.floor(off_cpn_sale/10)*10;
		if(currency_decimal > 0) off_cpn_sale=off_cpn_sale;
		else off_cpn_sale=Math.floor(off_cpn_sale/10)*10;

		sale_limit=eval(f.off_cpn_limit.value);
		if(off_cpn_sale > sale_limit) { 
			window.alert(_lang_pack.order_error_offcpn3.format(setComma(sale_limit))); 
			off_cpn_sale=sale_limit; 
		}
	}

	f.off_cpn_price.value=off_cpn_sale.toFixed(currency_decimal);

	if(typeof f.coupon != 'undefined' && f.coupon.length) {
		document.getElementById('no_cpn').checked=true;
		useCpn(f,'','');
		for(ii=0; ii<f.coupon.length; ii++) {
			f.coupon[ii].disabled=true;
		}
	}
	f.off_cpn_use.value="Y";

	document.all.off_cpn_used.innerText = _lang_pack.order_info_offcpn+' - '+f.off_cpn_price.value+' ';
	document.all.off_cpn_img1.style.display="none";
	document.all.off_cpn_img2.style.display="";

	useMilage(f,3);
}

function cancelOffCpn(force) { // 오프라인 쿠폰 취소
	f=document.ordFrm;

	if(force != true) {
		if(!confirm(_lang_pack.order_confirm_cpncancel)) return;
	}
	if(typeof f.coupon != 'undefined' && f.coupon.length) {
		for(ii=0; ii<f.coupon.length; ii++) {
			f.coupon[ii].disabled=false;
		}
	}
	f.off_cpn_use.value="N";
	useMilage(f,3);
	document.all.off_cpn_used.innerText="";
	document.all.off_cpn_img2.style.display="none";
	document.all.off_cpn_img1.style.display="";

	$('#off_cpn_div1').show();
	$('#off_cpn_div2').hide();
}

// 해외배송비 구하기
function getIntShipping(obj, weight, delivery_com) {
	var f = document.getElementsByName('ordFrm');
	f[0].delivery_prc.value = 0;
	useMilage(f[0]);
	/*
	var nations = (obj && typeof obj == 'object') ? obj.value : null;
	var delivery = (delivery_com && typeof delivery_com == 'object') ? delivery_com.value : null;

	$.post('/main/exec.php?exec_file=order/getShipping.php', {"nations":nations,"weight":weight,"delivery":delivery}, function(data) {
		var f = document.getElementsByName('ordFrm');
		if(f[0]) {
			if(data == 'F'){
				alert(_lang_pack.delivery_impossible_msg);
				if(delivery_com.type == 'select-one'){
					delivery_com.value = "";
				}else{
					obj.value = "";
				}
			}else{
				f[0].total_order_price.value = f[0].total_order_price.value.toNumber()-f[0].delivery_prc.value.toNumber();
				f[0].delivery_prc.value = data;
				f[0].total_order_price.value = f[0].total_order_price.value.toNumber()+data.toNumber();

				$('#delivery_prc2').html(setComma(data));
				//$('#delivery_prc2').html("test");
				//$('.delivery_prc').html(setComma(data));
				useMilage(f[0]);
			}
		}
	});
	*/
}
function onChangeOrderAddr(url,cart_selected, obj){
	if(cart_selected){
		location.href=url+"?cart_selected="+cart_selected+"&delivery_fee_type="+obj.value;
	}else{
		location.href=url+"?delivery_fee_type="+obj.value;	
	}
}

function onChangePhoneCode(obj){
	var _code = $(obj).children('option:selected').attr('data-phone');

	if($('select[name="addressee_phone_code"]')) $('select[name="addressee_phone_code"]').val(_code);
	if($('select[name="addressee_cell_code"]')) $('select[name="addressee_cell_code"]').val(_code);

	onChangeCountryState(obj,'');
}

function onChangeCountryState(obj,addr1){
	var param = {'nations':$(obj).val()};
	var add_code = "";

	if($('#order input[name=addressee_addr1]').length > 0){
		add_code = $('#order input[name=addressee_addr1]');
	}else if($('#order select[name=addressee_addr1]').length > 0){
		add_code = $('#order select[name=addressee_addr1]');
	}

	$.ajax({
		'url':'/main/exec.php?exec_file=shop/get_dlv_com.php',
		'data':"nations="+$(obj).val()+"&addressee_addr1="+addr1,
		'method':'POST',
		'dataType':'json',
		'async':true,
		'success': function(r) {
			$('#order select[name=delivery_com]').html(r.nations);
			$('#nAddrFrm select[name=n_delivery_com]').html(r.nations);

			if(r.state){
				if($('#order input[name=addressee_addr1]').length > 0 && r.change == 'S'){
					add_code.replaceWith(r.state);
				}else{
					if($('#order select[name=addressee_addr1]').length > 0)	add_code.replaceWith(r.state);
				}
			}
		}
	});
}

function kakaopayDisabled(type) {
	$('[name=pay_type]').on('change', function() {
		if (this.value == '12') {
			if (type == 'price') {
				window.alert('500만원을 초과하는 상품은 카카오페이로 결제할 수 없습니다.');
			} else if (type == 'sbscr') {
				window.alert('배송 기간이 2개월 이상인 상품은 카카오페이로 결제할 수 없습니다.');
			}
			window.pay_type.click();
		}
		window.pay_type = $(':checked[name=pay_type]');
	});
	window.pay_type = $(':checked[name=pay_type]');
}

function press_han(obj) {
	obj.value = obj.value.replace(/[^0-9]/g, '');
}

document.addEventListener("DOMContentLoaded", function() {
    const preventTypeArr = ['text','tel','email'];
	const preventEls = document.querySelectorAll('[name=ordFrm] input');
	for (let i = 0; i < preventEls.length; i++)
	{
        if (preventTypeArr.includes(preventEls[i].getAttribute('type')))
        {
            preventEls[i].setAttribute('autocomplete','new-password');
        }
	}
});

// 배송지변경 레이어 호출
function openOrderAddress(address_type, prev_page='', addr_no='') {
    $.post(root_url+'/main/exec.php?exec_file=member/order_delivery_frm.php&stripheader=true&striplayout=1&address_type='+address_type, {'delivery_type':ord_delivery_type, 'prev_page':prev_page, 'addr_no':addr_no, 'ono':ono, 'sbono':sbono}, function(data) {
        if( $('#__order_delivery_make_layer').length ) {
            $('#__order_delivery_make_layer').html(data);
        }else {
            setDimmed();
            $('body').append('<div id="__order_delivery_make_layer">'+data+'</div>');
        }
    });
}

// 나의주소록 삭제
function myaddressDelete(addr_no, obj) {
    if(!confirm(_lang_pack.common_confirm_delete)){
        return false;
    }

    $.ajax({
		type: 'post',
		url: root_url+'/main/exec.php?exec_file=member/myaddress.exe.php',
		data: 'addr_no='+addr_no+'&exec=delete',
		dataType : 'html',
		success: function(r) {
            var json = $.parseJSON(r);
            if(json.result) $(obj).parents('li').remove();
            if(!json.result) alert(json.msg);
		}
	});
}

// 선택한 주소 적용
function closeOrderAddress(address_type) {
    if( address_type ) {
        var title = '', name = '', phone = '', cell = '', zipcode = '', addr1 = '', addr2 = '', addr3 = '', addr4 = '', nations = '', _default = '', n_addr_add = '', n_addr_update = '', n_addr_default = '', delivery_com = '';
        var addr_no = $('[name=chk_addr]:checked').val();

        if( address_type == "0" || address_type == "1" ) {
            if(!checkCB(document.nAddrFrm.chk_addr, _lang_pack.order_address_select)) return;
            _default = $('[name=addr_'+addr_no+'_default]').val();

            title = $('[name=addr_'+addr_no+'_title]').val();
            name = $('[name=addr_'+addr_no+'_name]').val();
            phone = $('[name=addr_'+addr_no+'_phone]').val();
            cell = $('[name=addr_'+addr_no+'_cell]').val();
            zipcode = $('[name=addr_'+addr_no+'_zipcode]').val();
            addr1 = $('[name=addr_'+addr_no+'_addr1]').val();
            addr2 = $('[name=addr_'+addr_no+'_addr2]').val();

            addr3 = ( $('[name=addr_'+addr_no+'_addr3]').length ? $('[name=addr_'+addr_no+'_addr3]').val() : "");
            addr4 = ( $('[name=addr_'+addr_no+'_addr4]').length ? $('[name=addr_'+addr_no+'_addr4]').val() : "");
            nations = ( $('[name=addr_'+addr_no+'_nations]').length ? $('[name=addr_'+addr_no+'_nations]').val() : "");
        }else {
			let f = document.nAddrFrm;
            var addr_no = f.addr_no.value;

            title = $('[name=addr_title]').val();
            name = $('[name=n_addr_name]').val();
            phone = $('[name=n_addr_phone]').val();
            cell = $('[name=n_addr_cell]').val();
            zipcode = $('[name=n_addr_zip]').val();
            addr1 = $('[name=n_addr1]').val();
            addr2 = $('[name=n_addr2]').val();
            addr3 = ($('[name=n_addr3]').length ? $('[name=n_addr3]').val() : "");
            addr4 = ($('[name=n_addr4]').length ? $('[name=n_addr4]').val() : "");
			nations = ($('[name=n_nations]').length ? $('[name=n_nations]').val() : "");
			delivery_com = ($('[name=n_delivery_com]').length ? $('[name=n_delivery_com]').val() : "");

            n_addr_add = ( $('[name=n_addr_add]').is(':checked') ? "Y" : "");
            n_addr_update = ( $('[name=n_addr_update]').is(':checked') ? addr_no : "");
            n_addr_default = ( $('[name=n_addr_default]').is(':checked') ? "Y" : "");

            if ( !title && mlv != '10' ) { alert(_lang_pack.order_input_rtitle); return false; }
            if ( !name ) { alert(_lang_pack.order_input_rname); return false; }
            if ( !nations && ord_delivery_type == "O") { alert(_lang_pack.order_select_nations); return false; }

            let p3 = checkPhone(f.n_addr_phone);
            let p4 = checkPhone(f.n_addr_cell);
            if (nec_buyer_phone=='S') {
                if (p3!=9 && p4!=9) {
                    window.alert(_lang_pack.order_input_rphone);
                    f.n_addr_phone[p3].focus();
                    f.n_addr_phone[p3].select();
                    return false;
                }
            } else {
                if (!checkBlank(f.addressee_phone_code, _lang_pack.order_input_rphone)) return false;

                // 국가선택이 없을때만 전화번호 체크(해외배송이 아닐때만)
                if (!f.n_nations) {
                    if (p3!=9 && use_order_phone !='N') {
                        window.alert(_lang_pack.order_input_rphone);
                        if (f.elements['n_addr_phone[]']) {
                            f.n_addr_phone[p3].focus();
                            f.n_addr_phone[p3].select();
                        } else {
                            f.n_addr_phone.focus();
                            f.n_addr_phone.select();
                        }
                        return false;
                    }
                }

                if (!checkBlank(f.addressee_cell_code, _lang_pack.order_input_rcell)) return false;
                if (p4!=9) {
                    window.alert(_lang_pack.order_input_rcell);
                    if (f.elements['n_addr_cell[]']) {
                        f.n_addr_cell[p4].focus();
                        f.n_addr_cell[p4].select();
                    } else {
                        f.n_addr_cell.focus();
                        f.n_addr_cell.select();
                    }
                    return false;
                }
            }
        }

        if ( !zipcode ) { alert(_lang_pack.order_input_rzipcode); return false; }
        if ( !addr1 ) { alert(_lang_pack.order_input_raddr1); return false; }
        if ( !addr3 && ord_delivery_type == "O" ) { alert(_lang_pack.order_input_raddr1); return false; }
        if ( !addr4 && ord_delivery_type == "O" ) { alert(_lang_pack.order_input_raddr1); return false; }
        if ( !addr2 ) { alert(_lang_pack.order_input_raddr2); return false; }
        if ( ord_delivery_type == "O" && address_type == "2" && !delivery_com) { alert(_lang_pack.order_select_delivery_com); return false; }

        // 주문완료 후 배송지 변경
        var ono = document.nAddrFrm.ono.value;
        var sbono = document.nAddrFrm.sbono.value;
        if( ono || sbono ) {
            if(!confirm('배송지를 변경하시겠습니까?')) return ;

            $.ajax({
                type: 'post',
                url: root_url+'/main/exec.php?exec_file=member/myaddress.exe.php',
                data: 'exec=change&ono='+ono+'&sbono='+sbono+'&title='+title+'&name='+name+'&zipcode='+zipcode+'&addr1='+addr1+'&addr2='+addr2+'&addr3='+addr3+'&addr4='+addr4+'&nations='+nations+'&cell='+cell+'&phone='+phone+'&addr_add='+n_addr_add+'&addr_update='+n_addr_update+'&addr_default='+n_addr_default+'&addr_no='+addr_no,
                dataType : 'html',
                success: function(r) {
                    var json = $.parseJSON(r);
                    if( json.result ) {
                        $('#default_addr_name').html(name);
                        $('#default_addr_phone').html(cell+' / '+phone);
                        $('#default_addr').html('['+zipcode+'] '+addr1+' '+addr2+' '+addr3+' '+addr4);
                    }
                    if( !json.result ) {
                        alert(json.msg);
                        return false;
                    }
                }
            });

        }else {
            var of = document.ordFrm;
            of.addr_name.value = title;
            of.addressee_name.value = name;
            of.addressee_zip.value = zipcode;
            of.addressee_addr1.value = addr1;
            of.addressee_addr2.value = addr2;
            if(of.addressee_addr3) of.addressee_addr3.value = addr3;
            if(of.addressee_addr4) of.addressee_addr4.value = addr4;
            if(of.nations) of.nations.value = nations;
            if (of.delivery_com) of.delivery_com.value = delivery_com;

            of.addressee_cell.value = cell.replace(/-/gi,"");
            of.addressee_phone.value = phone.replace(/-/gi,"");
            of.addr_add.value = n_addr_add;
            of.addr_update.value = n_addr_update;
            of.addr_default.value = n_addr_default;

            if( _default ) $('#default_addr_display').show();
            else $('#default_addr_display').hide();

            $('#default_addr_name').html(of.addressee_name.value+' ('+of.addr_name.value+')');
            $('#default_addr_phone').html(of.addressee_cell.value+' / '+of.addressee_phone.value);
            $('#default_addr').html('['+of.addressee_zip.value+'] '+of.addressee_addr1.value+' '+of.addressee_addr2.value+' '+addr3+' '+addr4);
			$.ajax({
				type: 'post',
				url: root_url+'/main/exec.php?exec_file=member/myaddress.exe.php',
				data: 'exec=setdefault&addr_no='+addr_no+'&nations='+nations+'&addr_default='+n_addr_default,
				dataType : 'json',
				success: function(r) {
					if( !r.result ) {
						alert(r.msg);
						return false;
					}
				}
			});

			let addr_btn_txt = (browser_type == 'mobile') ? '변경' : '정보 변경';
			if ($('.n_addr_info')) $('.n_addr_info').html('<a onclick="openOrderAddress(0);" class="n_addr_btn">'+addr_btn_txt+'</a>');
			useMilage(document.ordFrm,3);
        }
    }

    removeDimmed();
    $('#__order_delivery_make_layer').remove();
}

// 적립금, 예치금 전체사용
function useAllmilage(type, prc) {
    prc = removeComma(prc);
    if (type == 'milage') {
        if (order_cpn_milage == 2 && (cpn_sale > 0 || prdcpn_sale_prc > 0)) {
            alert('쿠폰과 적립금은 중복 사용이 불가능합니다.');
            return;
        }
        if (no_milage > 0) {
            return;
        }
    }

    if( prc > 0 ) {
		if ($('[name='+type+'_prc]').val() > 0) $('[name='+type+'_prc]').val(0);
		else if (prc > total_pay_price) $('[name='+type+'_prc]').val(total_pay_price);
		else $('[name='+type+'_prc]').val(prc);
        useMilage(document.ordFrm,3);
    }
}

// 쿠폰사용 레이어
function couponChoiceLayer( open_type='' ) {
    var pay_type = $('[name=pay_type]:checked').val();
    var nocpn = $('#no_cpn').is(':checked');

	if (order_cpn_paytype == 2 && pay_type != '2') {
		window.alert(_lang_pack.order_error_offcpn1);
		return;
	}

    if( open_type == 'close' ) {
        $('#__myaddress_make_layer').remove();
        removeDimmed();
    }else {
        var coupon = $('[name=coupon]').val();
		if (order_cpn_paytype == 3 || only_setprd > 0) {
			window.alert('사용 가능한 쿠폰이 존재하지 않습니다.')
			return;
		}
		setDimmed();
        $.post(root_url+'/main/exec.php?exec_file=member/order_coupon_frm.php&stripheader=true&striplayout=1', {'coupon_no':coupon, 'pay_type':pay_type, 'cart_selected': cart_selected}, function(data) {
            $('body').append('<div id="__myaddress_make_layer">'+data+'</div>');
            // 체크박스 disable 이벤트
            $('.sw_PrdCpn').change(function() {
                setPrdCpnDisabled(2);
            });
        });
    }
}

