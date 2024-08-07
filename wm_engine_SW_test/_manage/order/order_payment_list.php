<?PHP

	$_search_type = array(
		'reason' => '사유',
		'comment' => '상세사유',
		'bank_account' => '계좌번호',
		'bank_name' => '예금주',
		'reg_id' => '처리자아이디',
	);

?>
<form action="">
	<div class="box_title first">
		<h2 class="title">결제내역</h2>
	</div>
	<div id="search">
		<table class="tbl_row">
			<caption class="hidden">결제 내역 검색</caption>
			<colgroup>
				<col style="width:12%;">
				<col>
			</colgroup>
			<tr>
				<th scope="row">상태</th>
				<td>
					<label><input type="radio" name="stat" value="" <?=checked('', $_GET['stat'])?>> 전체</label>
					<label><input type="radio" name="stat" value="1" <?=checked('1', $_GET['stat'])?>> 미승인</label>
					<label><input type="radio" name="stat" value="2" <?=checked('2', $_GET['stat'])?>> 승인</label>
				</td>
			</tr>
			<tr>
				<th scope="row">구분</th>
				<td>
					<label><input type="radio" name="type" value="" <?=checked('', $_GET['type'])?>> 전체</label>
					<label><input type="radio" name="type" value="1" <?=checked('1', $_GET['type'])?>> 입금</label>
					<label><input type="radio" name="type" value="2" <?=checked('2', $_GET['type'])?>> 환불</label>
				</td>
			</tr>
			<tr>
				<th scope="row">거래일자</th>
				<td>
					<input type="text" name="dates" value="<?=$dates?>" class="input datepicker" size="10"> ~
					<input type="text" name="datee" value="<?=$datee?>" class="input datepicker" size="10">
				</td>
			</tr>
		</table>
		<div class="box_bottom">
			<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
			<input type="text" name="search_str" value="<?=inputText($search_str)?>" size="40" class="input">
			<span class="box_btn gray"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
		</div>
	</div>
</form>