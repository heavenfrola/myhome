<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 등록
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_GET['no']);
	if($no) {
		$data = $pdo->assoc("select * from `$tbl[provider]` where `no` = '$no'");
		if(!$data) msg("존재하지 않는 사입처코드입니다.", "back");
	}

?>
<form method="post" action="" target="hidden<?=$now?>" onsubmit="return providerFrm(this)">
	<input type="hidden" name="body" value="product@provider.exe">
	<input type="hidden" name="no" value="<?=$data[no]?>">
	<input type="hidden" name="exec" value="register">
	<table class="tbl_row">
		<caption>사입처 정보 입력</caption>
		<colgroup>
			<col style="width:8%">
			<col style="width:10%">
			<col style="width:32%">
			<col style="width:8%">
			<col style="width:10%">
			<col style="width:32%">
		<colgroup>
		<tr>
			<th scope="row" colspan="2"><strong>사입처명</strong></th>
			<td colspan="4"><input type="text" name="provider" class="input input_full" value="<?=inputText($data[provider])?>"></td>
		</tr>
		<tr>
			<th scope="row" colspan="2">대표자명</th>
			<td colspan="4"><input type="text" name="pceo" class="input" value="<?=inputText($data[pceo])?>"></td>
		</tr>
		<tr>
			<th scope="row"  colspan="2">위치</th>
			<td colspan="4"><input type="text" name="plocation" class="input input_full" value="<?=inputText($data[plocation])?>"></td>
		</tr>
		<tr>
			<th scope="row" colspan="2">상가</th>
			<td><input type="text" name="arcade" class="input" value="<?=inputText($data[arcade])?>"></td>
			<th scope="row" colspan="2">층</th>
			<td class="bcol2">
				<input type="text" name="floor" class="input" value="<?=inputText($data[floor])?>">
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">전화번호</th>
			<td><input type="text" name="ptel" class="input" value="<?=inputText($data[ptel])?>"></td>
			<th scope="row" colspan="2">휴대폰</th>
			<td class="bcol2">
				<input type="text" name="pcell" class="input" value="<?=inputText($data[pcell])?>">
			</td>
		</tr>
		<tr>
			<th scope="rowgroup" rowspan="3" class="line_r">계좌정보1</th>
			<th scope="row" >은행명</th>
			<td>
				<?=selectArray($bank_codes, 'account1_bank', 2, ':: 은행을 선택해 주세요 :;', $data['account1_bank'])?>
			</td>
			<th scope="rowgroup" rowspan="3" class="line_r">계좌정보2</th>
			<th scope="row" >은행명</th>
			<td>
				<?=selectArray($bank_codes, 'account2_bank', 2, ':: 은행을 선택해 주세요 :;', $data['account2_bank'])?>
			</td>
		</tr>
		<tr>
			<th scope="row">계좌번호</th>
			<td>
				<input type="text" name="account1" class="input" value="<?=inputText($data[account1])?>">
			</td>
			<th scope="row">계좌번호</th>
			<td>
				<input type="text" name="account2" class="input" value="<?=inputText($data[account2])?>">
			</td>
		</tr>
		<tr>
			<th scope="row">예금주</th>
			<td>
				<input type="text" name="account1_name" class="input" value="<?=inputText($data[account1_name])?>">
			</td>
			<th scope="row">예금주</th>
			<td>
				<input type="text" name="account2_name" class="input" value="<?=inputText($data[account2_name])?>">
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">메모</th>
			<td colspan="5"><textarea name="content" class="txta"><?=inputText($data[content])?></textarea></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="location.href='<?=$listURL?>'"></span>
	</div>
</form>

<script type="text/javascript">
	function providerFrm(f) {
		var msg = new Array("사입처명을");
		var field = new Array("provider");

		for (var key in field)	{
			var val = field[key];
			var input = f.elements[val];

			if (!input.value){
				window.alert(msg[key]+" 입력해 주십시오");
				try {
					input.focus();
				} catch (ex) {

				}
				return false;
			}
		}

        printLoading();

		return true;
	}
</script>