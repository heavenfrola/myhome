<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  특별회원 그룹 상세 수정
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from $tbl[member_checker] where no='$no'");
		if(!$data['no']) msg('존재하지 않는 특별회원그룹입니다.', 'back');

		$data = array_map('stripslashes', $data);
	}
	if(!$data['login_msg_type']) $data['login_msg_type'] = 'N';

    // 과거 기능 마이그레이션
    if ($data['no_sale'] == 'Y') {
        $data['no_discount'] = $data['no_coupon'] = 'Y';
    }

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@member_checker.exe">
	<input type="hidden" name="exec" value="edit">
	<input type="hidden" name="no" value="<?=$data['no']?>">

	<table class="tbl_row">
		<caption>특별회원그룹 설정</caption>
		<colgroup>
			<col style="width:150px">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>그룹명</strong></th>
			<td>
				<input type="text" name="name" value="<?=$data['name']?>" class="input input_full">
			</td>
		</tr>
		<tr>
			<th scope="row">그룹 속성</th>
			<td>
				<ul>
					<li><label><input type="checkbox" name="no_milage" value="Y" <?=checked($data['no_milage'], 'Y')?>> 상품/회원 적립금 지급 제한</label></li>
					<li><label><input type="checkbox" name="no_discount" value="Y" <?=checked($data['no_discount'], 'Y')?>> 모든 할인 적용 제한</label></li>
					<li><label><input type="checkbox" name="no_coupon" value="Y" <?=checked($data['no_coupon'], 'Y')?>> 쿠폰 사용 제한</label></li>
					<li><label><input type="checkbox" name="no_pg" value="Y" <?=checked($data['no_pg'], 'Y')?>> PG결제 사용 제한(무통장 결제만 가능)</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">시작페이지 주소</th>
			<td>
				<input type="text" name="homepage" value="<?=$data['homepage']?>" class="input input_full">
			</td>
		</tr>
		<tr>
			<th scope="row">로그인 메시지</th>
			<td>
				<textarea name="login_msg" class="txta"><?=$data['login_msg']?></textarea>
			</td>
		</tr>
	</table>

	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="submit" value="취소" onclick="history.back();"></span>
	</div>
</form>