function checkByte(f, unlimit) {
	var tmpStr1, tmpStr2;
	tmpStr1=f.msg.value;
	tmpStr2='';
	tcount=0;
	temp=0;
	_sms_total_byte=parseInt(f.sms_total_byte.value);

	for (k=0;k<tmpStr1.length;k++)
	{
		onechar=tmpStr1.charAt(k);
		if (escape(onechar).length > 4) {
			temp=2;
		}
		else if (onechar != '\r'){
			temp=1;
		}
		else temp=0;

		tcount+=temp;

		if (tcount > _sms_total_byte && unlimit != true) {
			alert("\n 메시지 내용은 "+_sms_total_byte+"바이트\n\n (영문1자=1바이트,한글1자=2바이트)를 초과할 수 없습니다    \n\n 초과된 부분은 삭제됩니다\n");
			f.msg.value=tmpStr2;
			tcount-=temp;
			break;
		}
		else {
			// LMS - Han
			_lms_total_byte=parseInt(f.lms_total_byte.value);
			if(tcount > _lms_total_byte){
				alert("\n 메시지 내용은 "+_lms_total_byte+"바이트\n\n (영문1자=1바이트,한글1자=2바이트)를 초과할 수 없습니다    \n\n 초과된 부분은 삭제됩니다\n");
				f.msg.value=tmpStr2;
				tcount-=temp;
				break;
			}
			tmpStr2+=onechar;
		}
	}

	f.msglen.value=tcount;
	return;
}

function mmsCheck(f){
	tcount=parseInt(f.msglen.value);
	sms_point_span=document.getElementById('sms_point');
	sms_total_num_span=document.getElementById('sms_total_num');
	sms_total_point_span=document.getElementById('sms_total_point');
	sms_type_span=document.getElementById('sms_type');
	if(!sms_type_span) return;
	_sms_point=_sms_total_point=_sms_type=mms='';
	_sms_total_num=parseInt(document.getElementById('senderCount').innerHTML);
	_sms_total_byte=parseInt(f.sms_total_byte.value);
	_lms_total_byte=parseInt(f.lms_total_byte.value);
	if(tcount <= _sms_total_byte){
		_sms_point=parseInt(f.sms_use_point1.value);
		_sms_type='SMS';
		f.msgtemp.value=f.msgtemp.defaultValue;
	}
	if(tcount > _sms_total_byte){
		_sms_point=parseInt(f.sms_use_point2.value);
		_sms_type='LMS';
		f.msgtemp.value='/ '+_lms_total_byte+' Byte';
	}
	if(f.file_list.value) mms=1;
	if(mms == 1){
		_sms_point=parseInt(f.sms_use_point3.value);
		_sms_type='MMS';
		f.msgtemp.value='/ '+_lms_total_byte+' Byte';
	}
	_sms_total_point=_sms_point*_sms_total_num;
	sms_type_span.innerHTML='<strong>'+_sms_type+'</strong>';
}

function addSpecialChar(str,t){
	if (t==parent)
	{
		t.phone.msg.value=str;
	}
	else t.phone.msg.value+=str;
	checkByte(t.phone);
}

function CheckHP(phone){
	if (!phone)
	{
		alert('받는 분의 번호중 빈칸이 있습니다');
		return false;
	}

	var strPhone = new String(phone);
	if(strPhone.length < 10){
		window.alert('받는 분의 번호는 10자 이상이어야 합니다.');
		return false;
	}
	return true;
}

function checkSMS(f){
	if (!f.msg.value)
	{
		alert('전송할 메세지를 입력하세요');
		f.msg.focus();
		return false;
	}
	f.send_num.value=trim(f.send_num.value.replace(/-/g,''));
	if (0 && !f.send_num.value)
	{
		alert('보내시는 분의 번호를 입력하세요');
		f.send_num.focus();
		return false;
	}
	if (!CheckType(f.send_num.value, NUM)) {
		alert('보내시는 분의 번호는 숫자만 입력하세요');
		f.send_num.focus();
		return false;
	}
	f.rec_num.value=trim(f.rec_num.value.replace(/-/g,''));
	if (!f.rec_num.value)
	{
		alert('받는 분의 번호를 하나 이상 입력하세요');
		f.rec_num.focus();
		return false;
	}
	rec_nums=f.rec_num.value.split("\r\n");

	for (i=0; i<rec_nums.length; i++)
	{
		if (CheckHP(rec_nums[i])==false)
		{
			return false;
		}
	}

	$('#smsBlind').remove();
	$('#smsING').remove();

	$('body').append("<div id='smsBlind' class='blind'></div>");
	$('body').append("<ul id='smsING' class='desc1'><li><img src='"+engine_url+"/_manage/image/icon/msg_send.gif' /> 문자를 전송중입니다.</li><li>대량 발송일 경우 오랜시간이 소요될 수 있습니다.</li></ul>");

	return true;
}

function checkKakaoSMS(f){
	if (!f.msg.value)
	{
		alert('전송할 메세지를 입력하세요');
		f.msg.focus();
		return false;
	}
	f.kakao_rec_num.value=trim(f.kakao_rec_num.value.replace(/-/g,''));
	if (!f.kakao_rec_num.value)
	{
		alert('받는 분의 번호를 하나 이상 입력하세요');
		f.kakao_rec_num.focus();
		return false;
	}
	rec_nums=f.kakao_rec_num.value.split("\r\n");

	for (i=0; i<rec_nums.length; i++)
	{
		if (CheckHP(rec_nums[i])==false)
		{
			return false;
		}
	}

	$('#smsBlind').remove();
	$('#smsING').remove();

	$('body').append("<div id='smsBlind' class='blind'></div>");
	$('body').append("<ul id='smsING' class='desc1'><li>메세지를 전송 중입니다.</li><li>대량 발송 시 시간이 다소 소요될 수 있습니다.</li></ul>");

	return true;
}

function countSmsRec(){
	f=document.phone;
	rec_nums=f.rec_num.value.split("\n");
	document.getElementById('senderCount').innerHTML=rec_nums.length;
}

function clearSms(){
	document.phone.msg.value='';
	checkByte(document.phone);
}

function kakaocountRec(){
	f=document.kakao_phone;
	rec_nums=f.kakao_rec_num.value.split("\n");
	document.getElementById('KakaosenderCount').innerHTML=rec_nums.length;
}