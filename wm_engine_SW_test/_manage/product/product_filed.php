<?PHP

	$res = $pdo->iterator("select no, name, soptions, ftype, updir, upfile1 from $tbl[product_filed_set] where category='0' order by sort asc, no asc");
	$total = $pdo->row("select count(*) from `{$tbl['product_filed_set']}` where category=0");
	$ii = 0;

?>
<div class="box_title first">
	<h2 class="title">추가항목 관리</h2>
</div>
<form id="fieldSortFrm">
	<input type="hidden" name="body" value="product@product_field.exe">
	<input type="hidden" name="exec" value="remove">

	<table class="tbl_col tbl_col_bottom">
		<caption class="hidden">추가항목 관리</caption>
		<colgroup>
			<col style="width:50px">
			<col>
			<col style="width:250px">
			<col style="width:200px">
			<col style="width:120px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" class="check_all"></th>
				<th scope="col">이름</th>
				<th scope="col">형태</th>
				<th scope="col">항목 이미지</th>
				<th scope="col">순서</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$ii++;
					if($data['ftype'] == 1) $ftype = "직접입력형";
					else $ftype = "선택형";

					$soptions = stripslashes($data['soptions']);
					if($soptions) {
						$ftype .= "(".$soptions.")";
					}

					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					$fd_img = $data['upfile1'] ? "<img src=".$file_dir."/".$data['updir']."/".$data['upfile1']." style='max-height:50px;'>" : "";

					$idx = sprintf("%02d", $ii);
					$up_disabled = $ii == 1 ? "visibility: hidden;" : "";
					$dn_disabled = $ii == $total ? "visibility: hidden;" : "";
			?>
			<tr id="fno_<?=$data['no']?>" class="fieldset">
				<td><input type="checkbox" name="no[]" value="<?=$data['no']?>" class="check_one"></td>
				<td class="left"><a href="#" onclick="addFieldset('<?=$data['no']?>'); return false;"><strong><?=stripslashes($data['name'])?></strong></a></td>
				<td class="left"><?=$ftype?></td>
				<td><?=$fd_img?></td>
				<td style="line-height:34px;">
					<img src="<?=$engine_url?>/_manage/image/arrow_up.gif" onclick="fieldSort(this, -1);" class="p_cursor" style="<?=$up_disabled?>">
					<img src="<?=$engine_url?>/_manage/image/arrow_down.gif" onclick="fieldSort(this, 1);" class="p_cursor" style="<?=$dn_disabled?>">
				</td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="addFieldset('<?=$data['no']?>');"></span>
					<span class="box_btn_s gray"><input type="button" value="삭제" onClick="removePrdField(<?=$data['no']?>)"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom" style="height: 30px;">
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택삭제" onclick="removePrdField(this.form)"></span>
		</div>
		<div class="right_area">
			<span class="box_btn_s icon setup"><input type="button" value="항목추가" onclick="addFieldset();"></span>
		</div>
	</div>
</form>

<script type="text/javascript">
var fdFrm = new layerWindow('product@product_field_frm.exe');
fdFrm.reload = function() {
	$.get('<?=getURL().'&execmode=ajax'?>', function(r) {
		$('#fieldSortFrm').html($(r).filter('#fieldSortFrm').html());
		window.fdFrm.close();
		removeLoading();
	});
}

function fieldSort(obj, s){
	var source = $(obj).parents('tr.fieldset');
	var target = (s > 0) ? $(obj).parents('tr.fieldset').next() : $(obj).parents('tr.fieldset').prev();

	if(source.length == 1 && target.length == 1) {
		source = source.attr('id').replace('fno_', '');
		target = target.attr('id').replace('fno_', '');

		$.post('./index.php', {'body':'product@product_field.exe', 'exec':'sort', 'source':source, 'target':target}, function() {
			fdFrm.reload();
		});
	}
}

function removePrdField(f) {
	var param = null;
	if(typeof f == 'object') {
		if($('.check_one:checked').length == 0) {
			window.alert('삭제할 데이터를 선택해주세요.');
			return false;
		}
		param = $(f).serialize();
	} else {
		var form = document.getElementById('fieldSortFrm');
		param = {'body':form.body.value, 'exec':'remove', 'no[]':f};
	}

	if(confirm('선택한 항목을 삭제하시겠습니까?')) {
		printLoading();
		$.post('./index.php', param, function() {
			location.reload();
		});
	}
}

function addFieldset(fno) {
	fdFrm.open('&fno='+fno);
}

$(function() {
	chainCheckbox(
		$('.check_all'),
		$('.check_one')
	);
});
</script>