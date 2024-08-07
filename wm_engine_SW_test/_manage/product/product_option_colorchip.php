<?PHP

	if(isset($cfg['use_colorchip_cache']) == false) $cfg['use_colorchip_cache'] = 'N';

	$res = $pdo->iterator("select * from $tbl[product_option_colorchip] order by name asc");

	function parseColorChip($res) {
		$data = $res->current();
        $res->next();
		if ($data == false) return false;

		$data = array_map('stripslashes', $data);
		$data['size'] = setImageSize($data['w1'], $data['h1'], 50, 50);
		$data['hide_'.$data['type']] = "style='display:none;'";

		return $data;
	}

	$file_dir = getFileDir('_data/product/colorchip');
	$key = 0;

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.js"></script>
<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?=$engine_url?>" type="text/css">
<form id='regFrm' method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_option_colorchip.exe" />
	<input type="hidden" name="exec" value="add" />

	<table class="tbl_row">
		<colgroup>
			<col style="width:250px;">
		</colgroup>
		<caption>컬러칩 추가</caption>
		<tr>
			<th scope="row">이름</th>
			<td><input type="text" name="name[]" class="input"></td>
		</tr>
		<tr>
			<th scope="row">등록 형식</th>
			<td>
				<label><input type="radio" name="type[]" data-no="" value="code" checked> 색상코드 선택</label>
				<label><input type="radio" name="type[]" data-no="" value="file"> 이미지 첨부</label>
			</td>
		</tr>
		<tr class="field_code">
			<th scope="row">색상코드</th>
			<td><input type="text" name="code[]" class="input colorpicker_input"></td>
		</tr>
		<tr class="field_file" style="display: none;">
			<th scope="row">첨부파일</th>
			<td><input type="file" name="upfile1[]" class="input"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br>

<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_option_colorchip.exe" />
	<input type="hidden" name="exec" value="add" />

	<table class="tbl_col">
		<colgroup>
			<col style="width:250px;">
			<col style="width:60px;">
			<col>
			<col style="width:100px;">
		</colgroup>
		<caption>컬러칩 관리</caption>
		<thead>
			<tr>
				<th scope="col">옵션명</th>
				<th scope="col" colspan="2">컬러칩</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody id='tbody'>
            <?php while($data = parseColorChip($res)) { ?>
            <tr>
                <td rowspan="2">
                    <input type="hidden" name="no[]" value="<?=$data['no']?>">
                    <input type="text" name="name[]" class="input" value="<?=$data['name']?>">
                </td>
                <td class="center" rowspan="2">
                    <div class="field_code<?=$data['no']?>" <?=$data['hide_file']?>>
                        <div style="display:inline-block; width:30px; height:30px; background:<?=$data['code']?>" class="colorpicker_preview_<?=$data['no']?>"></div>
                    </div>
                    <div class="field_file<?=$data['no']?>" <?=$data['hide_code']?>>
                        <?if($data['upfile1']) {?>
                        <img src="<?=$file_dir?>/<?=$data['updir']?>/<?=$data['upfile1']?>" style="<?=$data['size'][2]?>">
                        <?}?>
                    </div>
                </td>
                <td class="left">
                    <label><input type="radio" name="type[<?=$key?>]" data-no="<?=$data['no']?>" value="code" <?=checked($data['type'], 'code')?>> 색상코드 선택</label>
                    <label><input type="radio" name="type[<?=$key?>]" data-no="<?=$data['no']?>" value="file" <?=checked($data['type'], 'file')?>> 이미지 첨부</label>
                </td>
                <td rowspan="2">
                    <span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeChip(<?=$data['no']?>)"></span>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <span class="field_code<?=$data['no']?>" <?=$data['hide_file']?>><input type="text" name="code[]" value="<?=$data['code']?>" data-no="<?=$data['no']?>" class="input colorpicker_input"></span>
                    <span class="field_file<?=$data['no']?>" <?=$data['hide_code']?>><input type="file" name="upfile1[]" class="input"></span>
                </td>
            </tr>
            <?php $key++;} ?>
		</tbody>
	</table>
	<div class="box_bottom left">
		<span class="box_btn"><input type="submit" value="컬러칩 수정"></span>
	</div>
</form>
<br>
<form method="POST" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_option_colorchip.exe">
	<input type="hidden" name="exec" value="migration">

	<table class="tbl_row">
		<colgroup>
			<col style="width:250px;">
		</colgroup>
		<caption>컬러칩 설정</caption>
		<tr>
			<th scope="row">상품목록에 컬러칩 출력</th>
			<td>
				<label><input type="radio" name="use_colorchip_cache" value="Y" <?=checked($cfg['use_colorchip_cache'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_colorchip_cache" value="N" <?=checked($cfg['use_colorchip_cache'], 'N')?>> 사용안함</label>
				<ul class="list_info">
					<?if(fieldExist($tbl['product'], 'colorchip_cache') == false) {?>
					<li>최초 설정 시 데이터베이스를 재구축하게 되며, 이로 인해 쇼핑몰 이용에 지장이 발생할 수 있습니다.</li>
					<li>상품 정보가 많은 경우 유휴시간을 이용해 설정해 주세요.</li>
					<?}?>
					<li>이 기능을 사용하기 위해서는 스킨 수정이 필요합니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function removeChip(no) {
	if(confirm('각 상품에 등록된 컬러칩이 같이 삭제됩니다.\n삭제하시겠습니까?')) {
		$.post('./index.php?body=product@product_option_colorchip.exe', {'exec':'remove', 'from_ajax':'true', 'no':no}, function(r) {
			$('#tbody').html(r);
		});
	}
}

var f = document.getElementById('regFrm');
function reloadChips() {
	$.post('./index.php?body=product@product_option_colorchip.exe', {'exec':'reload', 'from_ajax':'true'}, function(r) {
		$('#tbody').html($(r).find('#tbody').html());

		f.reset();
		$('.field_file').hide();
		$('.field_code').show();
		$('.colorpicker_input').css('background-color', '');
		$('.colorpicker').remove();

		attachEvent();
		removeLoading();
	});
}

function attachEvent() {
	$(':radio[name^="type["]').change(function() {
		var no = $(this).data('no');
		if(this.value == 'code') {
			$('.field_file'+no).hide();
			$('.field_code'+no).show();
		} else {
			$('.field_file'+no).show();
			$('.field_code'+no).hide();
		}
	});

	$('.colorpicker_input').bind({
		'focus' : function() {
			$('body').append('<div class="colorpicker"></div>');
			if(this.value == '') this.value = '#000000';
			$('.colorpicker').show();
			$('.colorpicker').farbtastic(this);
			$('.colorpicker').css({
				'position': 'absolute',
				'top': $(this).offset().top,
				'left': $(this).offset().left
			});
		},
		'blur' : function() {
			this.value = this.value.toUpperCase();
			$('.colorpicker').remove();
		}
	});
}
attachEvent();
</script>