<?PHP

	$_seller = array();
	$prql = $pdo->iterator("select no, name from `$tbl[delivery_url]` where partner_no='$admin[partner_no]' order by `sort`, `no` desc");
    foreach ($prql as $prdata) {
		$_dlv_no[$prdata['no']] = stripslashes($prdata['name']);
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/erp/js/log.js"></script>
<form id="deliFrm" name="joinFrm" method="post" action="" target="hidden<?=$now?>" onsubmit="return delivery(this);">
	<div class="box_title first">
		<h2 class="title">바코드 배송처리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">바코드 배송배송처리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">배송업체</th>
			<td><?=selectArray($_dlv_no, 'dlv_no', 2, '', $dlv_no)?></td>
		</tr>
		<tr>
			<th scope="row">운송장 번호</th>
			<td>
				<input type="text" name="dlv_code" value="" class="input" style="font-size:15px;padding:3px;" size="20" onfocus="this.select();">
				<span class="box_btn_s blue"><input type="submit" value="확인"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">변경될 상태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="ext" value="4" checked> <?=$_order_stat[4]?></label>
				<label class="p_cursor"><input type="radio" name="ext" value="5"> <?=$_order_stat[5]?></label>
			</td>
		</tr>
	</table>
</form>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">운송장 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">운송장 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">수취인명</th>
			<td id="addressee">&nbsp;</td>
			<th scope="row">휴대폰번호</th>
			<td id="cell">&nbsp;</td>
		</tr>
		<tr>
			<th scope="row">주소</th>
			<td colspan="3" id="addr">&nbsp;</td>
		</tr>
		<tr>
			<th scope="row">처리결과</th>
			<td colspan="3" id="msg" class="p_color2" style="font-size:17px; font-weight:bold;">&nbsp;</td>
		</tr>
	</table>
</form>
<form id="prdFrm" name="prdFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<div class="box_title">
		<h2 class="title">배송완료 상품 목록</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">배송완료 상품 목록</caption>
		<colgroup>
			<col>
			<col style="width:140px">
			<col style="width:140px">
			<col style="width:60px">
			<col style="width:80px">
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
<object id="mplayer" CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" style="display:none;">
	<param name="autoStart" value="false">
	<param name="URL" value="">
</object>

<script type="text/javascript">
	function delivery(f) {
		if(f.dlv_code.value == '') {
			playwav('input');
			dispMsg('no', '운송장 번호 또는 상품바코드를 입력해주세요');
			f.dlv_code.focus();
			return false;
		}
		for(var i = 0; i < f.ext.length; i++) {
			if(f.ext[i].checked == true) var ext = f.ext[i].value;
		}
		$.ajax({
			'type': 'get',
			url: '?body=erp@invoice.exe&dlv_no=' + f.dlv_no.value + '&dlv_code=' + f.dlv_code.value + '&ext=' + ext,
			success: function(data) {
				var json = $.parseJSON(data);
				$("#msg").html(json.msg);
				$("#addressee").html(json.name);
				$("#cell").html(json.cell);
				$("#addr").html(json.addr);
				$("#list_header").html(json.html);
				f.dlv_code.select();

				switch(json.result) {
					case '200' :
						wpDelivery(json.ono, ext);
						playwav('ok');
					break;
					case '300' :
						playwav('completed');
					break;
					case '400' :
						playwav('notFound');
					break;
					case '600' :
						playwab('hold');
					break;
				}
				f.dlv_code.focus();
			}
		});
		return false;
	}
	function del_invoice(obj) {
		var tr = $(obj).parent().parent();
		var dlv_no = tr.find("input[name='idlv_no']").val();
		var dlv_code = tr.find("input[name='idlv_code']").val();
		Log.write(dlv_no);
		Log.write(dlv_code);
	}

	function wpDelivery(ono, ext) {
		var f = document.getElementById('orderFrm');
		f.ono.value = ono;
		f.exec.value = 'stat';
		f.ext.value = ext;
		f.submit();
	}

	function playwav(msg) {
		var mplayer = document.getElementById('mplayer');
		mplayer.URL = engine_url+'/_manage/erp/wav/'+msg+'.wma';
		mplayer.controls.play();
	}
</script>