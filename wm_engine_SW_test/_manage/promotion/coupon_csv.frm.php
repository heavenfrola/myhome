<?PHP

	$no = numberOnly($_GET['no']);
	$cpn = $pdo->assoc("select no, name from $tbl[coupon] where no='$no'");
	if(!$cpn['no']) msg('존재하지 않는 쿠폰정보입니다.', 'close');

    // 쿠폰 발급 SMS 사용 여부
    $use_cpn_sms = $pdo->row("select use_check from {$tbl['sms_case']} where `case`='38'");

?>
<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" style="width:500px;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="promotion@coupon.exe">
	<input type="hidden" name="exec" value="csv">
	<input type="hidden" name="no" value="<?=$cpn['no']?>">
	<table class="tbl_row">
		<caption class="hidden">쿠폰 csv</caption>
		<colgroup>
			<col style="width:30%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">지급 쿠폰명</th>
			<td><?=stripslashes($cpn['name'])?></td>
		</tr>
		<tr>
			<th scope="row">CSV 파일</th>
			<td><input type="file" name="csv" class="input" size="20"></td>
		</tr>
        <?php if ($use_cpn_sms == 'Y') { ?>
		<tr>
			<th scope="row">알림</th>
			<td><label><input type="checkbox" name="use_cpn_sms" value="Y"> 쿠폰 발급 SMS 발송</label></td>
		</tr>
        <?php } ?>
	</table>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>선택하신 쿠폰을 지급받을 회원아이디를 csv 형식으로 업로드해주세요.</li>
			<li>쿠폰을 다운받지 못하는 등급, 중복 다운로드가 불가능한 쿠폰은 지급되지 않습니다.</li>
            <?php if ($use_cpn_sms == 'Y') { ?>
            <li>쿠폰 대량 발급을 통한 SMS 발송 시 시간이 오래 소요될 수 있습니다.</li>
            <?php } ?>
            <li>쿠폰의 발급 제한 수량을 초과하여 지급할 수 없습니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<span class="box_btn_s gray"><input type="submit" value="닫기" onclick="self.close();"></span>
	</div>
</form>
<script type="text/javascript">
	function showDownloadList(success, cno) {
		if(confirm(success+' 명의 회원에게 쿠폰이 발급되었습니다.\n발급 내역을 확인하시겠습니까?')) {
			opener.document.location.replace('./?body=promotion@coupon_down_list&is_type=A&sFrom=cno&search_str='+cno);
		}
		self.close();
	}
</script>