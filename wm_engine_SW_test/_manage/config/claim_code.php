<?php

	$res = $pdo->iterator("select * from {$tbl['claim_reasons']} order by no asc");
	addField($tbl['claim_reasons'], 'admin_only', 'enum("Y","N") not null default "Y"');

?>
<form id="regFrm" method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@claim_code.exe">
	<input type="hidden" name="no" value="">
	<div class="box_title first">
		<h2 class="title">사유 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">사유 등록</caption>
		<colgroup>
			<col style="width:20%;">
		</colgroup>
		<tr>
			<th scope="row">사유 등록</th>
			<td>
				<input type="text" name="reason" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th scope="row">관리자 전용</th>
			<td>
				<label><input type="radio" name="admin_only" value="N" checked> 전체</label>
				<label><input type="radio" name="admin_only" value="Y"> 관리자 전용</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="reset" value="취소"></span>
	</div>
</form>

<div class="box_title">
	<h2 class="title">취소/환불/반품/교환 사유 목록</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">취소/환불/반품/교환 사유 목록</caption>
	<colgroup>
		<col>
		<col style="width:100px">
		<col style="width:150px">
		<col style="width:80px">
		<col style="width:80px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">사유</th>
			<th scope="col">속성</th>
			<th scope="col">등록자</th>
			<th scope="col">수정</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
        <?php foreach ($res as $data) {?>
		<tr>
			<td class="left">
				<?=inputText($data['reason'])?>
			</td>
			<td>
				<?php if ($data['admin_only'] == 'Y') { ?>관리자 전용<?php } ?>
			</td>
			<td><?=$data['admin_id']?></td>
			<td><span class="box_btn_s"><input type="button" value="수정" onclick="editReason(<?=$data['no']?>)"></span></td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeReason(<?=$data['no']?>)"></span></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">
	function removeReason(no) {
		if(confirm('선택한 사유를 삭제하시겠습니까?')) {
            printLoading();
			$.post('./index.php', {'body':'config@claim_code.exe', 'exec':'removeReason', 'no':no}, function() {
				location.reload();
			});
		}
	}

	function editReason(no) {
		var f = document.getElementById('regFrm');
		$.post('./index.php', {'body':'config@claim_code.exe', 'exec':'getReason', 'no':no}, function(r) {
			f.no.value = r.no;
			f.reason.value = r.reason;
			$(f.admin_only).filter('[value='+r.admin_only+']').prop('checked', true);
		});
	}
</script>