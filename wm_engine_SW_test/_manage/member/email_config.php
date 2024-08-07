<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  자동메일 설정
	' +----------------------------------------------------------------------------------------------+*/
	$checked = explode('@', preg_replace('/^@|@$/', '', $cfg['email_checked']));
	if($admin['partner_no']) {
		$data = $pdo->assoc("select partner_email, partner_email_use from `$tbl[partner_shop]` where no=$admin[partner_no]");
	}
	if($admin['level'] > 3) $title = "입점사";

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="checkFrm(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="email_checked" value="">
	<input type="hidden" name="config_code" value="email">
	<div class="box_title first">
		<h2 class="title"><?=$title?> 자동 이메일 설정</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden"><?=$title?> 자동 이메일 설정</caption>
		<colgroup>
			<col style="width:140px">
			<col style="width:220px">
			<col style="width:100px">
			<col style="width:100px">
			<col>
			<col style="width:160px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">구분</th>
				<th scope="col">항목</th>
				<th scope="col">고객</th>
				<th scope="col">관리자</th>
				<th scope="col">관리자 이메일</th>
				<th scope="col">설정</th>
			</tr>
		</thead>
		<tbody>
			<?if($sadmin!="Y") {?>
			<tr>
				<td rowspan=3>회원</td>
				<td>회원가입 <i class="icon_info btt" tooltip="신규 고객이 가입 완료되는 시점에서 발송되는 자동 이메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="1" <?=checked(in_array(1, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td></td>
			</tr>
			<tr>
				<td>휴면회원 사전안내 <i class="icon_info btt" tooltip="휴면회원으로 전환되기 30일 전 발송되는 자동 이메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="12" <?=checked(in_array(12, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td></td>
			</tr>
			<tr>
				<td>개인정보 이용내역 <i class="icon_info btt" tooltip="연 1회 개인정보 이용내역 안내 메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="22" <?=checked(in_array(22, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td></td>
			</tr>
			<tr>
				<td rowspan=3>주문</td>
				<td>주문내역확인 <i class="icon_info btt" tooltip="고객이 상품 주문 시 발송되는 자동 이메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="2" <?=checked(in_array(2, $checked),true)?>></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="0" <?=checked(in_array('0', $checked),true)?>></td>
				<td class="left">
					<input type="text" name="email_admin" value="<?=$cfg['email_admin']?>" class="input block">
					<div class="list_info">
						<p>미 입력 시 대표 이메일 적용 : <?=$cfg['admin_email']?> <a href="?body=config@info" target="_blank">변경</a></p>
					</div>
				</td>
				<td></td>
			</tr>
			<tr>
				<td>상품배송 <i class="icon_info btt" tooltip="주문서 상태가 ‘배송중’으로 변경되는 시점에서 발송되는 자동 이메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="3" <?=checked(in_array(3, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td></td>
			</tr>
			<tr>
				<td>배송완료 <i class="icon_info btt" tooltip="주문서 상태가 ‘배송완료’로 변경되는 시점에서 발송되는 자동 이메일"></i></td>
				<td><input type="checkbox" name="chk[]" id="chk" value="4" <?=checked(in_array(4, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td></td>
			</tr>
			<tr>
				<td>관리</td>
				<td>게시물 작성 <i class="icon_info btt" tooltip="고객이 게시물 작성 시 발송되는 자동 이메일"></i></td>
				<td>-</td>
				<td><input type="checkbox" name="chk[]" id="chk" value="10" <?=checked(in_array(10, $checked),true)?>></td>
				<td class="left">
					<input type="text" name="email_admin_board" value="<?=$cfg['email_admin_board']?>" class="input block">
					<div class="list_info">
						<p>미 입력 시 대표 이메일 적용 : <?=$cfg['admin_email']?> <a href="?body=config@info" target="_blank">변경</a></p>
					</div>
				</td>
				<td>
					<span class="box_btn_s"><input type="button" value="게시판 별 사용설정" onclick="callback.open();"></span>
				</td>
			</tr>
			<!--
			<tr>
				<td></td>
				<td>광고성정보 수신동의</td>
				<td><input type="checkbox" name="chk[]" id="chk" value="15" <?=checked(in_array(15, $checked),true)?>></td>
				<td>-</td>
				<td class="left"></td>
				<td><span class="box_btn_s"><input type="button" value="설정" onclick="goM('member@privacy')"></span></td>
			</tr>
			-->
			<?} else {?>
			<tr>
				<td>주문</td>
				<td>주문내역확인 <i class="icon_info btt" tooltip="고객이 상품 주문 시 발송되는 자동 이메일"></i></td>
				<td>-</td>
				<td><input type="checkbox" name="partner_email_use" value="Y" <?=checked($data[partner_email_use],"Y")?>></td>
				<td class="left">
					<input type="text" name="partner_email" value="<?=$data[partner_email]?>" class="input input_full">
				</td>
				<td></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function checkFrm(f){
		var tmp="";
		for (i=0; i<f.chk.length; i++) {
			if (f.chk[i].checked==true) tmp+='@'+f.chk[i].value;
		}
		f.email_checked.value=tmp+'@';
	}
	var callback = new layerWindow('board@callback.exe');

	function updateCallback(f) {
		f.target = hid_frame;
	}
</script>