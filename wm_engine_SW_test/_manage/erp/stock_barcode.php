<form id="barcodeFrm" onsubmit="return stockBarcode(this)">
	<input type="hidden" name="body" value="erp@stock_barcode.exe">
	<div class="box_title first">
		<h2 class="title">바코드 재고조정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">바코드 재고조정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">처리방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="mode" value="P" checked> 차감</label>
				<label class="p_cursor"><input type="radio" name="mode" value="U"> 증가</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품바코드</th>
			<td>
				<input type="text" id="barcode" name="barcode" value="" class="input" size="20" tabindex="1">
				<div id="prd_area">
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">처리수량</th>
			<td>
				<input type="text" name="ea" value="1" class="input" size="3"> EA
				<ul class="list_msg">
					<li>화면 어디에서나 +키를 누르면 처리수량이 하나씩 증가되며, -키를 누르면 하나씩 감소됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div id="finish_msg" class="box_middle2" style="display:none;">
		<ul class="list_msg left">
			<li>바코드 리더의 버튼을 한번 더 누르시거나 Enter키를 누르시면 처리가 완료 됩니다.</li>
			<li>1개 이상의 동일 상품을 한번에 처리하시려면 처리수량을 변경하신후 Enter키를 눌러 주십시오.</li>
			<li>* 키를 누르시면 처리수량이 1로 변경됩니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인" id="btn" tabindex="2"></span>
	</div>
</form>
<div class="box_title">
	<h2 class="title">지난 처리내역</h2>
</div>
<div id="log_console" class="box_bottom top_line">
	<ul class="list_msg left">
	</ul>
</div>
<object id="mplayer" CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" style="display:none;">
	<param name="autoStart" value="false">
	<param name="URL" value="">
</object>

<script type="text/javascript">
	$('#barcode').bind({
		focus: function() {this.style.background = '#FFFF99';},
		blur : function() {
			this.style.background = '';
			$.post('?body=erp@stock_barcode.exe', {exec:'barcode', barcode:this.value}, function(data) {
				var json = jQuery.parseJSON(data);
				if(json.result == 1000) {
					$('#prd_area').html(json.html);
					playwav('confirm');
				}
			});
		},
		keydown	: function(ev) {
			if(ev.keyCode == 13) {
				$('#btn').focus();
				return false;
			}
		}
	});

	$('#btn').bind({
		focus: function() {
			if($('#barcode').val() != '') {
				$('#finish_msg').css('display','block')
			}
		},
		blur : function() {
			$('#finish_msg').css('display','none');
			$('#barcode').focus();
		}
	});

	$('body').keydown(function(ev) {
		switch(ev.keyCode) {
			case 107 : return setEa(1); break;
			case 109 : return setEa(-1); break;
			case 106 : return setEa(); break;
		}
	});

	$(document).ready(function(){
		$('#barcode').focus();
	});

	function setEa(num) {
		var ea = document.getElementById('barcodeFrm').ea;
		var no = (num) ? ea.value.toNumber()+num : 1;
		if(no < 1) no = 1;
		ea.value = no;

		return false;
	}

	function stockBarcode(f) {
		if(!checkBlank(f.barcode, '상품바코드를 입력해주세요.')) return false;
		if(f.ea.value.toNumber() < 1) {
			window.alert('처리 수량을 한개 이상 입력 해 주십시오.');
			return false;
		}
		$('#btn').css('display','none');
		var mode = $('input[name=mode]:checked').val();
		$.ajax({
			url		: '?body=erp@stock_barcode.exe',
			cache	: false,
			async	: false,
			type	: 'POST',
			data	: {mode:mode,barcode:f.barcode.value,ea:f.ea.value},
			success	: function(data) {
						var json = jQuery.parseJSON(data);
						if(json.result == '1000') {
							$('#barcode').val('').focus();
							$('#prd_area').html('');
							$('#log_console > UL').append('<li>'+json.msg+'</li>').animate({scrollTop:'+=30'}, 'slow');
							//f.ea.value = 1;
							playwav('ok');
						} else if(json.result == '0001') { // 데이터 연속 입력 오류
							$('#barcode').select().focus();
						} else {
							window.alert(json.msg);
							$('#barcode').select().focus();
							playwav('error');
						}
					}
		});


		$('#btn').css('display','block');
		return false;
	}

	function playwav(msg) {
		try {
			var mplayer = document.getElementById('mplayer');
			mplayer.URL = engine_url+'/_manage/erp/wav/'+msg+'.wma';
			mplayer.controls.play();
		} catch(ex) {}
	}
</script>