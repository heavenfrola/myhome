<?PHP

	$_seller = array();
	$prql = $pdo->iterator("select no, provider, arcade, floor from $tbl[provider] order by arcade!='' desc, arcade asc, floor asc, provider asc");
    foreach ($prql as $prdata) {
		$_provider = stripslashes($prdata['provider']);
		$_arcade = stripslashes($prdata['arcade']);
		if($prdata['floor']) $_arcade .= ' '.stripslashes($prdata['floor']).'층';
		if($_arcade) $_provider = "[$_arcade] ".$_provider;
		$_seller[$prdata['no']] = $_provider;
	}

?>
<form id="search" name="prdSearchFrm">
    <input type="hidden" id="complex_no">

	<div class="box_title first">
		<h2 class="title">출고상품 정보</h2>
	</div>
    <table class="tbl_row">
		<caption class="hidden">출고상품 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th>상품명(바코드)</th>
			<td colspan="3">
				<input type="text" id="word" value="" class="input" size="20" onfocus="this.select();" onkeydown="getProdByEnter(this);">
				<span class="box_btn_s"><input type="button" value="검색" onclick="getProd($('#word'));"></span>
			</td>
		</tr>
        <tr>
            <th>사입처</th>
            <td colspan="3"><?=selectArray($_seller, "seller", 2, ":: 사입처 선택 ::", $seller)?></td>
        </tr>
        <tr><!-- 20180314-1847340 -->
            <th>상품명</th>
            <td id="product">
				<div class="box_setup">
					<div class="thumb"><a target="_blank" id="imgstr"></a></div>
					<dl>
						<dt id="product_name" class="title"></dt>
						<dd id="complex_option_name" class="cstr"></dd>
						<dd id="category_name" class="cstr"></dd>
					</dl>
				</div>
            </td>
            <th>바코드</th>
            <td id="barcode"></td>
        </tr>
        <tr>
            <th>현재고</th>
            <td id="current_qty"></td>
            <th>출고수량</th>
            <td>
                <input type="text"
					id="in_qty"
					name="in_qty"
					value="1"
					class="input"
					size="4"
					onfocus="this.select();"
					onkeydown="next_input(this);"
					onkeyup="FilterNumOnly(this);"
				>
                <span class='box_btn_s'><input type="button" id="reg_btn" value="등록" onclick="insProd();"/></span>
            </td>
        </tr>
    </table>
</form>

<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return order();">
    <input type="hidden" name="body" value="erp@stock_adjust.exe" />
    <input type="hidden" name="ono" value="<?=$order_no?>" />
    <input type="hidden" name="sno" value="<?=$order['sno']?>" />

	<div class="box_title">
		<h2 class="title">출고상품 목록</h2>
	</div>
    <table class="tbl_col">
        <caption class="hidden">출고상품 목록</caption>
		<colgroup>
			<col>
			<col>
			<col>
			<col>
			<col style="width:80px;">
			<col style="width:80px;">
		</colgroup>
		<thead>
			<tr>
				<th>사입처</th>
				<th>상품명</th>
				<th>옵션</th>
				<th>바코드</th>
				<th>출고수량</th>
				<th>삭제</th>
			</tr>
		</thead>
		<tbody class="list_body">
		</tbody>
    </table>
    <div class="box_bottom left">
		<div>
			총 출고 수량 <input type="text" class="total_qty input right" value="0" size="5" disabled>
		</div>
		<div class="right_area">
			<input type="text" id="alldesc" class="input" size="30">
			<span class="box_btn_s"><input type="button" value="비고 일괄입력" onclick="setAlldesc()"></span>
		</div>
	</div>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="출고처리"></span>
    </div>
</form>

<script type="text/javascript">
	function getProdByEnter(obj) {
		if(event.keyCode == 13) {
			getProd(obj, true);
		}
	}

	function getProd(obj, direct) {
		if(!$(obj).val()) {
			window.alert("상품명이나 바코드를 입력 해 주세요.");
			obj.focus();
			return false;
		}

		$.ajax({
			"url": "?body=erp@product.exe&word="+$(obj).val(),
			"success": function(json) {
				if(json.rows == 1) {
					setProd(json.datas[0], true);
				} else {
					wisaOpen("./pop.php?body=erp@product_list&search_type=name&search_str="+$(obj).val(), "chgPrd", "yes");
				}
			}
		});
	}

	function next_input(obj) {
		if(event.keyCode == 13) {
			if(obj.id == "in_qty") insProd();
			else $("#in_qty").focus();
		}
	}

	function insProd() {
		var seller = $("select[name='seller']");
		if($("#complex_no").val().length == 0) {
			window.alert("상품명이나 바코드를 입력해주세요.");
			$("#word").focus();
			return;
		}

		if(!checkNum(prdSearchFrm.in_qty, "출고수량을 입력해주세요.")) return;

		if(seller.val() == null) {
			window.alert("사입처를 선택해주세요.");
			seller.focus();
			return;
		}

		var in_qty = parseInt($("#in_qty").val(), 10);

		// 똑같은 상품이 있을 경우 수량 변경
		var complex_no = $("#complex_no").val();
		var search = $(":input[name='pno[]'][value='"+complex_no+"']");
		if(search.length > 0) {
			var tr = search.parent().parent();
			in_qty = tr.find("[name='in_qty[]']").val().toNumber()+in_qty;
			tr.find("[name='in_qty[]']").val(in_qty);
			$("#word").focus();
			return;
		}

		var html  = "<tr class='prd_complex_"+complex_no+"'>";
			html += "<td rowspan='2'><input type='hidden' name='sno2[]' value='"+seller.find("option:selected").val()+"' />"+seller.find("option:selected").text()+"</td>"+
				    "<td rowspan='2'>"+
					"<input type='hidden' name='complex_no[]' value='"+complex_no+"'>"+
					"<input type='hidden' name='pno[]' value='"+complex_no+"' />"+$("#product").html()+
					"</td>"+
					"<td>"+$("#complex_option_name").html()+"</td>"+
					"<td>"+$("#barcode").html()+"</td>"+
				    "<td class='right number in_qty'>"+
					"<input type='text' name='out_qty[]' value='"+$("#in_qty").val()+"' class='out_qty input' size='3' onfocus='this.select();' onchange='getTotal();'>"+
					"<input type='hidden' name='org_stock_qty[]' value='0'></td>"+
					"<td class='center'><span class='box_btn_s gray'><input type='button' value='삭제' onclick='delProd("+complex_no+");'></span></td></tr>"+
					"<tr class='prd_complex_"+complex_no+"'><td colspan='4' class='left'>비고 <input type='text' class='input remark' name='adjust_reason[]' size='50' onfocus='this.select()' /></td></tr>";
		$(".list_body").append(html);
		$("#word").focus();
		getTotal();
	}

	function delProd(complex_no) {
		$('.prd_complex_'+complex_no).remove();
		getTotal();
	}

	function getTotal() {
		var total_qty = 0;
		$('.out_qty').each(function() {
			total_qty += this.value.toNumber();
		});
		$('.total_qty').val(setComma(total_qty));
	}

	function setProd(data, direct) {
		$("select[name='seller']").val(data.seller_idx);
		$("#complex_no").val(data.complex_no);
		$("#imgstr").html(data.imgstr);
		$("#product_name").html(data.productname);
		$("#category_name").html(data.category_name);
		$("#complex_option_name").html(data.complex_option_name);
		$("#barcode").html(data.barcode);
		$("#current_qty").html(data.current_qty);
		$('#word').val("").focus();

		if(direct == true) {
			$('#reg_btn').focus();
		}
	}
	$('#word').focus();

	function order() {
		return confirm('출고처리 하시겠습니까?');
	}

	$('body').click(function(event) {
		if(event.target.tagName && event.target.tagName == "INPUT" && event.target.type == "text") {
			return false;
		}

		if(event.target.tagName != "SELECT" && event.target.tagName != "input") {
			$('#word').select();
			return;
		}
	});

	$(':text').click(function(event) {
		return false;
	});

	function setAlldesc() {
		var desc = $.trim($('#alldesc').val());
		$('input.remark').val(desc);
	}
</script>