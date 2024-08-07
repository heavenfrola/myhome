<?PHP

	$res = $pdo->iterator("select no, name from $tbl[category] where ctype=9 and level=1 order by sort asc");
    foreach ($res as $data) {
		$data['name'] = stripslashes($data['name']);
		$cate_9 .= "<option value='$data[no]'>$data[name]</option>";
	}

?>
<form id='search' method='get' action='./index.php' onsubmit="return insertStorage(this)">
	<input type='hidden' name='body' value='<?=$_GET['body']?>' />

	<table class="tbl_row">
		<caption>바코드 창고 배치</caption>
		<colgroup>
			<col style="width:150px;">
			<col>
		</colgroup>
		<tr>
			<th scope="row">창고위치</th>
			<td>
				<select name="big" onchange="chgCateInfinite(this, 2, '')">
					<option value="">::대분류::</option>
					<?=$cate_9?>
				</select>
				<select name="mid" onchange="chgCateInfinite(this, 3, '')">
					<option value="">::중분류::</option>
				</select>
				<select name="small"onchange="chgCateInfinite(this, 4, '')">
					<option value="">::소분류::</option>
				</select>
				<select name="depth4">
					<option value="">::세분류::</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">상품바코드</th>
			<td>
				<input type="text" id="barcode" value="" class="input" onfocus="this.select();" onkeydown="return searchBarcode(event);">
				<span class="box_btn_s"><input type="button" value="검색" onclick="searchBarcode();"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">상품명</th>
			<td>
				<div class="box_setup hidden" style="margin-top: 5px;">
					<div class="thumb"><a class="prdlink" target="_blank"><img class="prdthumb" src="" width="50px;"></a></div>
					<dl>
						<dt class="title"><a class="editlink" target="_blank"></a></dt>
						<dd class="price"></dd>
					</dl>
					<input type="hidden" name="complex_no" class="complex_no">
				</div>
			</td>
		</tr>
	</table>

	<div class="box_bottom">
		<span class="box_btn blue"><input id="submit_btn" type="submit" value="등록"></span>
	</div>
</form>
<br>

<form method="post" action="./index.php" onsubmit="this.target=hid_frame">
	<input type="hidden" name="body" value="erp@storage.exe">
	<input type="hidden" name="exec" value="storage_in">
	<table class="tbl_col">
		<thead>
			<tr>
				<th>상품명</th>
				<th>창고명</th>
				<th>창고위치</th>
				<th>삭제</th>
			</tr>
		</thead>
		<tbody id="lists">

		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input id="submit_btn" type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function searchBarcode(e) {
	if(e && e.keyCode != 13) return true;

	var barcode = $('#barcode').val();
	$.get('./?body=erp@storage.exe', {'exec':'searchBarcode', 'barcode':barcode}, function(json) {
		if(json.result != 'success') {
			psearch.open('search_key=name&search_str='+barcode);
		} else {
			setComplex(json);
		}
	});
	return false;
}

var psearch = new layerWindow('product@product_inc.exe&instance=psearch');
psearch.psel = function(pno) {
	$.get('./?body=erp@storage.exe', {'exec':'searchBarcode', 'pno':pno}, function(json) {
		setComplex(json);
	});
	this.close();
}

function setComplex(json) {
	var product_box = $('.box_setup');

	product_box.removeClass('hidden');
	product_box.find('.complex_no').val(json.complex_no);
	product_box.find('.editlink').html(json.name);
	product_box.find('.prdlink').attr('href', json.front_link);
	product_box.find('.editlink').attr('href', json.mng_link);
	product_box.find('.prdthumb').attr('src', json.thumb);
	product_box.find('.price').html(json.price);

	window.jsondata = json;

	$('#submit_btn').focus();
	$('#barcode').val('');
}

function insertStorage(f) {
	if(typeof window.jsondata != 'object') {
		window.alert('상품을 선택해주세요.');
		return false;
	}
	if(f.big.value == '') {
		window.alert('배치할 창고를 선택해주세요.');
		return false;
	}

	$.get('./?body=erp@storage.exe', {'exec':'getStorageNo', 'big':f.big.value, 'mid':f.mid.value, 'small':f.small.value, 'depth4':f.depth4.value}, function(storage) {
		if(!storage.no) {
			window.alert('등록되지 않은 창고위치입니다.');
			return false;
		}

		var jsondata = window.jsondata;

		if($("input[name='pno[]']").filter("[value='"+jsondata.pno+"']").length > 0) {
			window.alert('동일한 상품이 선택되어 있습니다..');
			return false;
		}

		var idx = $('.storage_in').length;
		var tr  = "<tr class='storage_in storage_in_"+idx+"'>";
			tr += "	<td class='left'>";
			tr += "		<img src='"+jsondata.thumb+"' style='width:50px; vertical-align: middle;'> "+jsondata.name;
			tr += "		<input type='hidden' name='pno[]' value='"+jsondata.pno+"'>";
			tr += "		<input type='hidden' name='storage_no[]' value='"+storage.no+"'>";
			tr += "	</td>";
			tr += "	<td>"+storage.name+"</td>";
			tr += "	<td>"+storage.location+"</td>";
			tr += "	<td><span class='box_btn_s gray'><input type='button' value='삭제' onclick='removeStorageIn("+idx+")'></span></td>";
			tr += "</tr>";
			tr = $(tr);
		$('#lists').prepend(tr);
		tr.css('backgroundColor', '#ffffcc');
		tr.animate({'backgroundColor':'#fff'}, 500);

		delete window.jsondata;
		$('.box_setup').addClass('hidden');
	});

	$('#barcode').select();
	return false;
}

function removeStorageIn(idx) {
	if(confirm('창보 배치를 취소하시겠습니까?')) {
		$('TR.storage_in_'+idx).remove();
	}
}
</script>