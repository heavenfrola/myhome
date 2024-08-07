<?PHP

	if(isset($_POST['result']) == true) {
		require 'pg_compare_result.inc.php';
		return;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  PG 승인결과 대시 및 승인대기 해제
	' +----------------------------------------------------------------------------------------------+*/

	$data = $pdo->row("select data from $tbl[excel_preset] where type='pg' and name='used'");
	if(!$data) {
		$data = 'ono';
	}
	$data = explode(',', $data);

	$fd = array(
		'ono' => '주문번호',
		'confirm_date' => '승인일시',
		'tid' => '승인코드',
		'card_nm' => '카드명',
		'interest' => '할부정보',
		'space' => '기타항목',
	);

	if($_POST['exec']) return;

	// 왼쪽 전체 항목 셀렉트
	$items_all = '';
	foreach($fd as $key => $val) {
		$items_all .= "<option value='$key'>$val</option>\n";
	}

	// 오른쪽 선택 항목 셀렉트
	$items_sel = '';
	foreach($data as $key => $val) {
		$nm = $fd[$val];
		$opt = ($val == 'ono') ? 'class="essencial"' : '';
		$items_sel .= "<option value='$val' $opt>$nm</option>\n";
	}

?>
<style type="text/css">
.essencial {
	color: #ff3300;
}
</style>
<form method="POST" action="?" target="hidden<?=$now?>" onsubmit="return makeSortOrder(this);">
	<input type="hidden" name="body" value="order@pg_compare.exe">
	<input type="hidden" name="exec" value="setup">
	<input type="hidden" name="data" value="">

	<div class="box_title first">
		<h2 class="title">CSV 파일 형식</h2>
	</div>
	<div class="box_middle">
		<ul class="list_info left">
			<li>입력할 CSV파일 내용의 필드순서를 하단 오른쪽의 순서와 매칭해야 정확한 전산처리가 가능합니다.</li>
			<li>정상 승인상태의 주문정보만 업로드해 주세요. 취소된 주문이 입금처리 될 수 있습니다.</li>
			<li>주문번호만 있어도 복구가 가능하나, 선택 입력사항의 승인코드(LG U+의 경우 TID, KCP는 거래번호 항목)를 정확하게 입력해야 카드취소 연동이 가능합니다.</li>
		</ul>
	</div>
	<div class="box_middle add_fld">
		<div class="fld_list">
			<h3>추가할 필드 선택</h3>
			<select id="items_all" class="select_n" size="15">
				<?=$items_all?>
			</select>
		</div>
		<div class="add">
			<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
		</div>
		<div class="add_list">
			<h3>CSV 파일내용</h3>
			<select id="items_sel" class="select_n" size="15">
				<?=$items_sel?>
			</select>
			<span class="box_btn_s icon delete"><input type="button" value="삭제" onclick="select2.remove();"></span>
			<span class="box_btn_s icon up"><input type="button" value="위로" onclick="select2.move(-1);"></span>
			<span class="box_btn_s icon down"><input type="button" value="아래로" onclick="select2.move(1);"></span>
		</div>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- //CSV 파일 형식 -->

<form method="POST" action="?" target="hidden<?=$now?>" enctype="multipart/form-data">
	<input type="hidden" name="body" value="order@pg_compare.exe">
	<input type="hidden" name="exec" value="upload">

	<div class="box_title">
		<h2 class="title">PG사 엑셀 업로드</h2>
	</div>
	<div class="box_middle">
		<ul class="list_info left">
			<li>입력할 CSV파일 내용의 필드순서를 매칭해야 정확한 전산처리가 가능합니다.</li>
			<li>승인대기 상태의 주문만 처리되며, 첫 번째 줄 또한 처리되지 않습니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">PG승인결과 대사</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">CSV 파일 첨부</th>
				<td>
					<input type="file" name="csv" class="input input_full">
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="전송"></span>
	</div>
</form>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	var select1 = new R2Select('items_all');
	var select2 = new R2Select('items_sel');

	function makeSortOrder(f) {
		var data = '';
		$('#items_sel>option').each(function() {
			if(data) data += ',';
			data += this.value;
		});
		f.data.value = data;
		return true;
	}
</script>
