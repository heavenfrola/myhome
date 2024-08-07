<?PHP

	$_seller = array();
	$prql = $pdo->iterator("select no, provider, arcade, floor from $tbl[provider] order by arcade!='' desc, arcade asc, floor asc, provider asc");
    foreach ($prql as $prdata) {
		$_provider = cutStr(stripslashes($prdata['provider']), 50);
		$_arcade   = stripslashes($prdata['arcade']);
		if($prdata['floor']) $_arcade .= ' '.stripslashes($prdata['floor']).'층';
		if($_arcade) $_provider = '['.$_arcade.'] '.$_provider;
		$_seller[$prdata['no']] = $_provider;
	}

	$_dreserved = explode('@', $_COOKIE['dreserved']);
	if(!$_COOKIE['dreserved']) $_dreserved = array(2, 3);

?>
<form id="search" name="prdSearchFrm">
	<input type="hidden" id="complex_no">
	<div class="box_title first">
		<h2 class="title">입고상품 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">입고상품 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">상품명(바코드)</th>
			<td>
				<input type="text" id="word" value="" class="input" size="20" onfocus="this.select();" onkeydown="getProdByEnter(this);">
				<span class="box_btn_s"><input type="button" value="검색" onclick="getProd($('#word'));"></span>
			</td>
			<th scope="row">입고단가</th>
			<td><input type="text" id="in_price" name="in_price" value="" class="input" size="10" onfocus="this.select();" onkeydown="next_input(this);" onkeyup="FilterNumOnly(this);"></td>
		</tr>
		<tr>
			<th scope="row">사입처</th>
			<td><?=selectArray($_seller, "seller\" onkeydown=\"next_input(this);", 2, ":: 사입처 선택 ::", $seller)?></td>
			<th scope="row">입고수량</th>
			<td>
				<input type="text" id="in_qty" name="in_qty" value="1" class="input" size="4" onfocus="this.select();" onkeydown="next_input(this);" onkeyup="FilterNumOnly(this);">
				<span class="box_btn_s"><input type="button" id="reg_btn" value="등록" onclick="insProd();"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">상품명</th>
			<td id="product">
				<div class="box_setup">
					<div class="thumb"><a target="_blank" id="imgstr"></a></div>
					<dl>
						<dt id="product_name" class="title"></dt>
						<dd id="category_name" class="cstr"><?=$category_name?></dd>
					</dl>
				</div>
			</td>
			<th scope="row">옵션</th>
			<td id="complex_option_name"></td>
		</tr>
		<tr>
			<th scope="row">바코드</th>
			<td id="barcode"></td>
			<th scope="row">현재고</th>
			<td id="current_qty"></td>
		</tr>
	</table>
</form>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return order()">
	<input type="hidden" name="body" value="erp@order_in.exe">
	<input type="hidden" name="exec" value="in">
	<input type="hidden" name="ono" value="<?=$order_no?>">
	<input type="hidden" name="sno" value="<?=$order['sno']?>">
	<div class="box_title">
		<h2 class="title">입고상품 목록</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">입고상품 목록</caption>
		<colgroup>
			<col span="2">
			<col style="width:140px;">
			<col style="width:140px;">
			<col style="width:80px;">
			<col style="width:80px;">
			<col style="width:80px;">
			<col style="width:80px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">사입처</th>
				<th scope="col">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">옵션</th>
				<th scope="col">입고수량</th>
				<th scope="col">입고단가</th>
				<th scope="col">입고금액</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody id="list_body">
		</tbody>
	</table>
	<div class="box_middle2 right">
		<input type="text" id="remark_all" class="input" size="50" placeholder="비고 일괄 입력">
		<span class="box_btn_s" onclick="setRemarkAll()"><input type="button" value="입력"></span>
	</div>
	<div class="box_middle2 right">
		총입고수량 	<input type="text" id="total_qty" name="total_qty" class="input right" disabled value="0" size="3">
		총입고금액 	<input type="text" id="total_amt" name="total_amt" class="input right" disabled value="0" size="15">
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="입고처리"></span>
	</div>
</form>
<object id="mplayer" CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" style="display:none;">
	<param name="autoStart" value="false">
	<param name="URL" value="">
</object>

<script type="text/javascript">
	function getProdByEnter(obj) {
		if(event.keyCode == 13) {
			getProd(obj, true);
		}
	}

	function getProd(obj, direct) {
		if(!$(obj).val()) {
			playwav('error');
			window.alert('상품명이나 바코드를 입력 해 주세요.');
			obj.focus();
			return false;
		}
		$.ajax({
			url: '?body=erp@product.exe&word=' + $(obj).val(),
			success: function(data) {
				var json = eval(data);
				if(json.rows == 1) {
					setProd(json.datas[0], true);
				} else {
					playwav('error');
					wisaOpen('./pop.php?body=erp@product_list&search_type=name&search_str=' + $(obj).val(),'chgPrd', 'yes');
				}
			}
		});
	}

	function next_input(obj) {
		if(event.keyCode == 13) {
			if(obj.id == "in_qty") {
				insProd();
			} else {
				$("#in_qty").focus();
			}
		}
	}

	function insProd() {
		var seller = $("select[name='seller']");
		if($("#complex_no").val().length == 0) {
			alert("상품명을 입력해주세요.");
			$("#word").focus();
			return;
		}
		if(!checkNum(prdSearchFrm.in_price, "입고단가는")) return;
		if(!checkNum(prdSearchFrm.in_qty, "입고수량은")) return;
		if(seller.val() == null || seller.val().length == 0) {
			alert("사입처를 선택해주세요.");
			seller.focus();
			return;
		}
		var in_qty = parseInt($("#in_qty").val(), 10);
		var in_amt = (in_qty * parseInt($("#in_price").val(), 10)).toNumber();
		playwav('ok');
		var complex_no = $("#complex_no").val();
		var search = $(':input[name="pno[]"][value='+complex_no+']');
		if(search.length > 0) {
			var tr = search.parent().parent();
			in_qty = tr.find('[name=in_qty[]]').val().toNumber()+in_qty;

			tr.find('[name=in_qty[]]').val(in_qty);
			tr.find('.in_amt').html(setComma(tr.find('[name=in_price[]]').val().toNumber()*in_qty));
			$("#word").focus();
			return;
		}

		total(in_qty, in_amt);
		var html = "<tr class='prd_complex_"+complex_no+"'><td class='left' rowspan='2'><input type='hidden' name='sno2[]' value='"+seller.find("option:selected").val()+"'>"+seller.find("option:selected").text()+"</td>"+
				   "<td class='left' rowspan='2'><input type='hidden' name='pno[]' value='"+complex_no+"'>"+$("#product").html()+
				   "</td><td>"+$("#barcode").html()+"</td><td>"+$("#complex_option_name").html()+
				   "</td><td>"+
				   "<input type='text' name='in_qty[]' value='"+$("#in_qty").val()+"' class='input' size='3'>"+
				   "<input type='hidden' name='or_qty[]' value='0'></td>"+
				   "<td><input type='hidden' name='in_price[]' value='"+$("#in_price").val()+"'>"+$("#in_price").val()+
				   "</td><td class='in_amt'>"+in_amt+
				   "</td><td><span class='box_btn_s'><a onclick='delProd("+complex_no+");'>삭제</a></span></td></tr>"+
                   "<tr class='prd_complex_"+complex_no+"'><td class='left' colspan='6'><input type='text' name='remark[]' class='input input_full remark' placeholder='비고'></td></tr>";
			;
		$("#list_body").append(html);
		$("#word").focus();
	}

	function total(qty, amt) {
		var total_qty = $('#total_qty');
		var total_amt = $('#total_amt');
		total_qty.val(parseInt(total_qty.val(), 10) + qty);
		total_amt.val(parseInt(total_amt.val(), 10) + amt);
	}

	function delProd(complex_no) {
		$('.prd_complex_'+complex_no).remove();
	}

	function setProd(data, direct) {
		$("select[name='seller']").val(data.seller_idx);
		$("#complex_no").val(data.complex_no);
		$("#in_price").val(data.in_price);
		$("#imgstr").html(data.imgstr);
		$("#product_name").html(data.productname);
		$("#category_name").html(data.category_name);
		$("#complex_option_name").html(data.complex_option_name);
		$("#barcode").html(data.barcode);
		$("#current_qty").html(data.current_qty);
		$("#in_price").focus();
		$("#in_price").select();
		$('#word').val('').focus();

		if(direct == true) {
			playwav('confirm');
			$('#reg_btn').focus();
			//insProd();
		}
	}
	$('#word').focus();

	function order() {
		return confirm('입고처리 하시겠습니까?');
	}

	$('body').click(function(event) {
		if(event.target.tagName != 'INPUT' && event.target.tagName != 'SELECT') {
			$('#word').select();
		}
	});

	$(':text').click(function(event) {
		return false;
	});

	function playwav(msg) {
		var mplayer = document.getElementById('mplayer');
		mplayer.URL = engine_url+'/_manage/erp/wav/'+msg+'.wma';
		if(mplayer.controls) {
			mplayer.controls.play();
		}
	}

	function cfgDreserve() {
		var cfg = '';
		$(':input[name=dreserved]').each(function(){
			if(this.checked == true) cfg += '@'+this.value;
		});

		cfg = cfg.replace('/^@/', '');
		setConfig('dreserved', cfg);

		return;
	}

	function remove_reserve() {
		in_status.open();
	}

	function setRemarkAll() {
		$('.remark').val($('#remark_all').val());
	}

	var in_status = new layerWindow('erp@in_inc.exe');
</script>