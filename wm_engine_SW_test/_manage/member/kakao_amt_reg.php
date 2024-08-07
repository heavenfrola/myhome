<?PHP

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from $tbl[alimtalk_template] where no='$no' and reg_status!='RMVD'");
		$button1 = json_decode($data['button1'], true);
		$button2 = json_decode($data['button2'], true);
		$button3 = json_decode($data['button3'], true);
		$button4 = json_decode($data['button4'], true);
		$button5 = json_decode($data['button5'], true);
		$data = array_map('stripslashes', $data);

		// 템플릿 최신 검수상태 체크
		$wec_alm = new weagleEyeClient($_we, 'alimtalk');
		$ret = $wec_alm->call('getTemplateStatus', array('templateCode'=>$data['templateCode']));
		$ret = json_decode($ret);
		$ret = $ret->data;

		if($ret->status) {
			$pdo->query("
				update $tbl[alimtalk_template] set
					reg_status='$ret->inspectionStatus', tmp_status='$ret->status'
				where templateCode='$ret->templateCode'
			");
		}
		if(!$ret->status) {
			msg('조회할수 없는 메시지 입니다.', 'back');
		}
		if($ret->inspectionStatus != 'REG' && $ret->inspectionStatus != 'JEC' && $ret->status != 'R') {
			msg("등록/반려 상태일때만 수정이 가능합니다.\\n한번 심사 통과 된 메시지는 수정이 불가능 합니다.", 'back');
		}
	}

	include $engine_dir.'/_engine/sms/sms_module.php';
	foreach($sms_case_admin as $key => $val) {
		$sms_case_title[$val] = '(관리자) '.$sms_case_title[$val];
		if($val == 27 || $val == 32 || $val == 33 || $val == 34 || $val == 35 || $val == 37) {
			$sms_case_title[$val] = '(정기배송) '.$sms_case_title[$val];
		}
	}
	unset($sms_case_title[16]);
	unset($sms_case_title[19]);
	unset($sms_case_title[21]);

?>
<style type="text/css">
.input.disabled {
	background: #f2f2f2;
}
</style>
<form name="amtFrm" method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="member@kakao_amt_reg.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="listURL" value="<?=$listURL?>">
	<div class="box_title first">
		<h2 class="title">카카오 알림톡 메시지 관리</h2>
	</div>
	<div class="kakao_amt_reg">
		<table class="tbl_row">
			<caption class="hidden">카카오 알림톡 메시지 관리</caption>
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row"><strong>발송분류</strong></th>
				<td>
					<?=selectArray($sms_case_title, 'sms_case', false, null, $data['sms_case'], "chgMessage(this.value);")?> 시 발송
					<span class="box_btn_s"><input type="button" value="샘플메시지 가져오기" onclick="getAlimtalkSample(this)"></span>
					<p class="msg_chg"><strong>메시지 치환</strong> :
						<span id="message"></span>
					</p>
					<table class="tbl_mini2 full" style="display:none;">
						<caption class="hidden">메시지 치환 안내</caption>
						<colgroup>
							<col style="width:100px">
							<col>
						</colgroup>
						<tr>
							<th>회원가입</th>
							<td class="left message_1"> #{이름} #{아이디}</td>
						</tr>
						<tr>
							<th>주문완료</th>
							<td class="left message_2"> #{주문자} #{주문번호} #{금액} #{계좌번호} <i class="icon_info"></i>계좌번호는 무통장입금/에스크로결제 고객에게만 표시됩니다</td>
						</tr>
						<tr>
							<th><?=$_order_stat[2]?></th>
							<td class="left message_3"> #{주문자} #{주문번호} #{금액}</td>
						</tr>
						<tr>
							<th><?=$_order_stat[3]?></th>
							<td class="left message_4"> #{주문자} #{주문번호}</td>
						</tr>
						<tr>
							<th><?=$_order_stat[4]?></th>
							<td class="left message_5"> #{주문자} #{주문번호} #{배송사} #{송장번호}</td>
						</tr>
						<tr>
							<th>부분<?=$_order_stat[4]?></th>
							<td class="left message_14"> #{주문자} #{배송사} #{송장번호}</td>
						</tr>
						<tr>
							<th><?=$_order_stat[5]?></th>
							<td class="left message_6"> #{주문자} #{주문번호}</td>
						</tr>
						<tr>
							<th>인증번호발송</th>
							<td class="left message_22"> #{인증번호}</td>
						</tr>
						<tr>
							<th>상품질문답변</th>
							<td class="left message_8"> #{이름}</td>
						</tr>
						<tr>
							<th>입금요청</th>
							<td class="left message_9"> #{주문자} #{주문번호} #{계좌번호} #{금액}</td>
						</tr>
						<tr>
							<th>무통장 자동취소</th>
							<td class="left message_26"> #{주문자} #{주문번호}</td>
						</tr>
						<tr>
							<th>자동입금확인</th>
							<td class="left message_15"> #{주문자} #{주문번호} #{금액}</td>
						</tr>
						<tr>
							<th>무통장 주문</th>
							<td class="left message_13"> #{주문자} #{계좌번호} #{금액}</td>
						</tr>
						<tr>
							<th>적립금소멸(정보성)</th>
							<td class="left message_20"> #{이름} #{소멸적립금} #{소멸예정일}</td>
						</tr>
						<tr>
							<th>광고성정보 수신여부 변경</th>
							<td class="left message_23"> #{광고성정보변경일자} #{SMS이메일수신동의여부}</td>
						</tr>
						<tr>
							<th>재입고 알림 신청</th>
							<td class="left message_24"> #{재입고상품명} #{재입고상품옵션}</td>
						</tr>
						<tr>
							<th>재입고 알림 발송</th>
							<td class="left message_25"> #{이름} #{재입고상품명} #{재입고상품옵션}</td>
						</tr>
						<tr>
							<th>휴면회원 사전안내</th>
							<td class="left message_28"> #{이름} #{휴면처리일}</td>
						</tr>
						<tr>
							<th><?=$_order_stat[15]?></th>
							<td class="left message_29"> #{주문자} #{주문번호} #{금액}</td>
						</tr>
						<tr>
							<th><?=$_order_stat[17]?></th>
							<td class="left message_30"> #{주문자} #{주문번호} #{주문상품명} #{금액}</td>
						</tr>
						<tr>
							<th>(관리자) 회원가입</th>
							<td class="left message_11"> #{이름} #{아이디}</td>
						</tr>
						<tr>
							<th>(관리자) 주문완료</th>
							<td class="left message_12"> #{주문자} #{주문번호} #{금액} #{계좌번호} <i class="icon_info"></i>계좌번호는 무통장입금/에스크로결제 고객에게만 표시됩니다</td>
						</tr>
						<tr>
							<th>(관리자) 가상계좌, 자동입금확인</th>
							<td class="left message_18"> #{주문자} #{금액}</td>
						</tr>
						<tr>
							<th>(관리자) 신규게시글 작성</th>
							<td class="left message_17"> #{게시판명}</td>
						</tr>
                        <tr>
                            <td><strong>주문 완료</strong></td>
                            <td class="left message_32">#{주문자} #{주문번호} #{금액} #{첫배송일}</td>
                        </tr>
                        <tr>
                            <td><strong>배송시작(정기결제)</strong></td>
                            <td class="left message_27">#{주문자} #{금액} #{상품명} #{배송예정일}</td>
                        </tr>
                        <tr>
                            <td><strong>배송시작(일괄결제)</strong></td>
                            <td class="left message_33">#{주문자} #{상품명} #{배송예정일}</td>
                        </tr>
                        <tr>
                            <td><strong><?=$_order_stat[13]?></strong></td>
                            <td class="left message_34">#{주문자} #{주문번호}</td>
                        </tr>
                        <tr>
                            <td><strong>회차취소</strong></td>
                            <td class="left message_27">#{주문자} #{주문번호}</td>
                        </tr>
                        <tr>
                            <td><strong>쿠폰발급</strong></td>
                            <td class="left message_38">#{이름} #{쿠폰명} #{쿠폰만료일}</td>
                        </tr>
                        <tr>
                            <td><strong>개인정보 이용내역 안내</strong></td>
                            <td class="left message_31"> - </td>
                        </tr>
						<tr>
							<td><strong>회원가입 승인(사업자/14세 미만)</strong></td>
							<td class="left message_41"> #{이름} #{아이디} </td>
						</tr>
						<tr>
							<td><strong>적립금수동지급</strong></td>
							<td class="left message_41"> #{이름} #{지원적립금} #{적립금유효기간} </td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row"><strong>템플릿명</strong></th>
				<td>
                    <input type="text" name="templateName" class="input block" value="<?=$data['templateName']?>">
                    <div class="name_check explain p_color2"></div>
                </td>
			</tr>
			<tr>
				<th scope="row"><strong>메시지 내용</strong></th>
				<td>
					<textarea name="templateContent" class="txta" onkeyup="setPreview(this, true);" onchange="setPreview(this, true)"><?=$data['templateContent']?></textarea>
					<p class="explain"><i class="icon_info"></i> 메시지 내용은 한/영 구분 없이 띄어쓰기 포함 최대 1,000자까지 사용할 수 있습니다.<span class="count"><b class="bytes">0</b> / 1000자</span>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					링크버튼<br><span class="explain">(5개까지 등록가능)</span>
				</th>
				<td>
					<div class="btn_type">
						<div id='auto_dlv' style="display:none;"><label><input type="checkbox" id="auto_dlv_add" name="auto_dlv_add" value="Y" onclick="auto_show();">배송조회 자동추가</label></div>
						<p class="title">버튼명</p>
						<input type="text" name="button_name" value="" class="input block" maxlength="14">
						<p class="title">URL(PC)</p>
						<div class="box">
							<input type="text" id="button_purl" name="button_purl" value="" class="input block" placeholder="http://">
							<span class="box_btn_s"><input type="button" value="연결확인" onClick="link_check('button_purl');"></span>
						</div>
						<p class="title">URL(Mobile)</p>
						<div class="box">
							<input type="text" id="button_murl" name="button_murl" value="" class="input block" placeholder="http://">
							<span class="box_btn_s"><input type="button" value="연결확인" onClick="link_check('button_murl');"></span>
						</div>
					</div>
					<span class="box_btn gray"><input type="button" name="" value="적용" class="" onClick="button_submit(this.form);"></span>
				</td>
			</tr>
			<!--
			<tr>
				<th>버튼 사용</th>
				<td>
					<label><input type="radio" name="buttonType" value="N" checked> 미사용</label>
					<label><input type="radio" name="buttonType" value="C"> 사용</label>
					<ul class="list_msg">
						<li>주문관련 메시지 사용시 주문상세 페이지로 이동하는 링크를 제공합니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th>버튼 이름</th>
				<td><input type="text" name="buttonName" class="input" size="30"></td>
			</tr>
			-->
		</table>
		<div class="preview">
			<h3>카카오 알림톡 미리보기</h3>
			<div class="box">
				<div class="inner contentPreview"></div>
			</div>
			<ul class="list_btn">
				<?if(count($button1)>0) {?>
				<li data-index='<?=$button1['ordering']?>'>
					<?=$button1['name']?>
					<a onClick='button_del("<?=$button1['ordering']?>")' class="del" data-purl='<?=$button1['linkPc']?>' data-murl='<?=$button1['linkMo']?>'>삭제</a>
					<input type='hidden' name='button_name[]' value='<?=$button1['name']?>'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='<?=$button1['linkPc']?>'><input type='hidden' name='button_murl[]' value='<?=$button1['linkMo']?>'>
				</li>
				<?}?>
				<?if(count($button2)>0) {?>
				<li data-index='<?=$button2['ordering']?>'>
					<?=$button2['name']?>
					<a onClick='button_del("<?=$button2['ordering']?>")' class="del" data-purl='<?=$button2['linkPc']?>' data-murl='<?=$button2['linkMo']?>'>삭제</a>
					<input type='hidden' name='button_name[]' value='<?=$button2['name']?>'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='<?=$button2['linkPc']?>'><input type='hidden' name='button_murl[]' value='<?=$button2['linkMo']?>'>
				</li>
				<?}?>
				<?if(count($button3)>0) {?>
				<li data-index='<?=$button3['ordering']?>'>
					<?=$button3['name']?>
					<a onClick='button_del("<?=$button3['ordering']?>")' class="del" data-purl='<?=$button3['linkPc']?>' data-murl='<?=$button3['linkMo']?>'>삭제</a>
					<input type='hidden' name='button_name[]' value='<?=$button3['name']?>'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='<?=$button3['linkPc']?>'><input type='hidden' name='button_murl[]' value='<?=$button3['linkMo']?>'>
				</li>
				<?}?>
				<?if(count($button4)>0) {?>
				<li data-index='<?=$button4['ordering']?>'>
					<?=$button4['name']?>
					<a onClick='button_del("<?=$button4['ordering']?>")' class="del" data-purl='<?=$button4['linkPc']?>' data-murl='<?=$button4['linkMo']?>'>삭제</a>
					<input type='hidden' name='button_name[]' value='<?=$button4['name']?>'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='<?=$button4['linkPc']?>'><input type='hidden' name='button_murl[]' value='<?=$button4['linkMo']?>'>
				</li>
				<?}?>
				<?if(count($button5)>0) {?>
				<li data-index='<?=$button5['ordering']?>'>
					<?=$button5['name']?>
					<a onClick='button_del("<?=$button5['ordering']?>")' class="del" data-purl='<?=$button5['linkPc']?>' data-murl='<?=$button5['linkMo']?>'>삭제</a>
					<input type='hidden' name='button_name[]' value='<?=$button5['name']?>'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='<?=$button5['linkPc']?>'><input type='hidden' name='button_murl[]' value='<?=$button5['linkMo']?>'>
				</li>
				<?}?>
			</ul>
		</div>
	</div>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>카카오 알림톡은 정보성 메시지만 발송할 수 있으며, <span class="warning">광고성 메시지는 심사에서 반려</span>됩니다.</li>
			<li>등록한 메시지를 사용하기 위해서는 카카오의 검수가 필요하며, 수일이 소요될 수 있습니다.</li>
			<li>검수가 완료된 메시지는 고객 문자알림 설정에서 최종적으로 사용 여부를 설정할 수 있습니다. <a href="?body=member@sms_config" target="_blank">바로가기</a></li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="location.href='<?=getListURL('kakao_amt_msg')?>'"></span>
	</div>
</form>
<script type="text/javascript">
function getAlimtalkSample(btn) {
	var f = btn.form;
	$.post('./index.php?body=member@kakao_amt_reg.exe', {'exec':'sample', 'sms_case':f.sms_case.value}, function(json) {
        if (json.replace == 'OK') {
            window.alert('제공 중인 샘플 메시지가 없습니다.');
            return false;
        }
		f.templateContent.value = json.message;
		setPreview(f.templateContent, false);

		$('.contentPreview').html(json.replace);
	});
}

function setPreview(content, preview) {
	$('.bytes').html(content.value.length);
	if(preview == false) return;

	var f = content.form;
	$.post('./index.php?body=member@kakao_amt_reg.exe', {'exec':'preview', 'sms_case':f.sms_case.value, 'message':content.value}, function(json) {
		$('.contentPreview').html(json.message);
	});
}

function button_submit(f) {
	var button_name = f.button_name.value;
	var button_purl = f.button_purl.value;
	var button_murl = f.button_murl.value;
	var li_count = $('.preview .list_btn li').length+1;

	if(!button_name) {
		alert("버튼명이 입력되지 않았습니다.");
		return false;
	}
	if(!button_purl) {
		alert("URL(PC)가 입력되지 않았습니다.");
		return false;
	}
	if(!button_murl) {
		alert("URL(Mobile)이 입력되지 않았습니다.");
		return false;
	}
	if(li_count<=5) {
		var chk_index = 0;
		$('.preview .list_btn li').each(function(i) {
			var now_index = $(this).data('index');
			if(chk_index==0 || now_index >= chk_index) {
				chk_index = now_index;
			}
		})
		chk_index = chk_index + 1;
		$('.preview .list_btn').append("<li data-index='"+chk_index+"'>"+button_name+"<a data-purl='"+button_purl+"' data-murl='"+button_murl+"' class='del' onClick='button_del("+chk_index+")'></a><input type='hidden' name='button_name[]' value='"+button_name+"'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='"+button_purl+"'><input type='hidden' name='button_murl[]' value='"+button_murl+"'></li>");
		f.button_name.value = "";
		f.button_purl.value = "";
		f.button_murl.value = "";
	}else {
		alert("링크버튼은 최대 5개까지 등록할 수 있습니다.");
		return false;
	}
}

function button_del(idx) {
	$('.preview .list_btn li').each(function() {
		var now_index = $(this).data('index');
		if(idx==now_index) {
			$(this).remove();
		}
	})
}

$(".list_btn").sortable({
	'placeholder': 'placeholder',
	'cursor':'all-scroll',
	'scroll': false
});

$('#button_purl').click(function() {
	$(this).val('http://');
});
$('#button_murl').click(function() {
	$(this).val('http://');
});

$('input[name=templateName]').on('change keyup', function(r) {
    $.post('./index.php', {'body':'member@kakao_amt_reg.exe', 'exec':'name_check', 'value':this.value, 'no':'<?=$no?>'}, function(r) {
        if (r.result == 'overlapping') {
            $('.name_check').html('삭제되었거나 이미 등록된 템플릿명입니다.');
        } else {
            $('.name_check').html('');
        }
    });
});

function link_check(href) {
	var url = $('#'+href).val();
	window.open(url);
}
chgMessage(document.amtFrm.sms_case.value);
function chgMessage(val) {
	$('#message').html($('.message_'+val).html());
	if(val=='5' || val=='14') {
		$('#auto_dlv').show();
	}else {
		$('#auto_dlv').hide();
	}
}

function auto_show() {
	var auto_add = $('#auto_dlv_add').is(":checked");
	if(auto_add==true) {
		$('input[name=button_name]').val('배송조회');
		$('#button_purl').val('http://redirect.wisa.co.kr/#{배송조회링크}');
		$('#button_murl').val('http://redirect.wisa.co.kr/#{배송조회링크}');
	}else {
		$('input[name=button_name]').val('');
		$('#button_purl').val('');
		$('#button_murl').val('');
	}
}
</script>