<?printAjaxHeader();?>
<div id="popupContent" class="layerPop">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">입고수량 제외</div>
	</div>
	<div id="popupContentArea">
		<ul class="list_msg">
			<li>건별 입고 준비중인 상품중에서 주문된 상품만큼 입고개수를 제외시킵니다.</li>
			<li>수량이 0이나 마이너스가 되는 경우 입고처리 시 제외됩니다.</li>
			<li>여러번 실행하실 경우 가장 최신의 정보로 재계산됩니다.</li>
			<li>단, <span class="p_color2">같은 상품을 두번에 나누어 입고처리 할 경우</span> 본 기능은 한번만 이용하셔야 합니다.</li>
		</ul>
		<ul id="in_chg_status" style="overflow:auto; height:300px; border:1px solid #ccc;">
			<li>처리된 내역이 없습니다.</li>
		</ul>
	</div>
	<div class="pop_bottom">
		<?=$pg_res?>
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="in_status.close()"></span>
	</div>
</div>

<script type="text/javascript">
	var cfg = '';
	$(':input[name=dreserved]').each(function(){
		if(this.checked == true) cfg += ','+this.value;
	});

	$.post('?body=erp@in_remove.exe&stat='+cfg, function(data) {
		var data = data.split('\n');
		var len = data.length;
		var order = null;
		var status = $('#in_chg_status');
		var idx = 0;

		for(var i  = 0; i < len; i++) {
			order = data[i];
			if(!order) continue;
			order = order.split('@');

			var tr = $('#prd_complex_'+order[2]);
			if(tr.length > 0) {
				idx++;
				or_qty = tr.find('[name=or_qty[]]');
				ch_qty = order[3] - or_qty.val().toNumber();
				in_qty = tr.find('[name=in_qty[]]').val().toNumber()-ch_qty;
				or_qty.val(order[3]);

				if(ch_qty != 0) {
					var str = '<li><strong>'+idx+'. '+order[0]+'</strong> - '+order[1]+' 의 입고수량이 '+in_qty+'개로 변경 (-'+ch_qty+'개)</li>';
					if(idx == 1) status.html(str);
					else status.append(str);
					tr.find('[name=in_qty[]]').val(in_qty);
					tr.find('.in_amt').html(setComma(tr.find('[name=in_price[]]').val().toNumber()*in_qty));
				}
				$("#word").focus();
			}
		}
		window.alert('처리가 완료되었습니다.\n하단의 입고처리 버튼을 클릭하시면 입고가 완료됩니다.');
	});
</script>