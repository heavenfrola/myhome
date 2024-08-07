<?PHP

	if($cfg['logger_heatmap_HM_U']) {
		msg('이미 히트맵 신청이 완료되었습니다.', '?body=log@heatmap');
	}

	$wec = new weagleEyeClient($_we, 'account');
	$account_id = $wec->call('getAccountId');

	if(!preg_match('/^[a-z][a-z0-9]+$/', $account_id)) {
		echo 'API 연동 오류';
		return;
	}

	$cusId = 'ws_HMt'.$account_id;
	$cusPw = substr(str_replace('-', '', $_we['wm_key_code']), 2);

?>
<form method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="return formCheck(this)">
	<input type="hidden" name="body" value="log@heatmap.exe">
	<input type="hidden" name="partnerName" value="wisa">
	<input type="hidden" name="partnerNum" value="32741">
	<input type="hidden" name="cusId" value="<?=$cusId?>">
	<input type="hidden" name="level" value="4">
	<input type="hidden" name="isMobile" value="4">
	<input type="hidden" name="callback" value="<?=$root_url?>/main/exec.php?exec_file=log/heatmap.exe.php">
	<input type="hidden" name="domain" value="<?=$root_url?>">
	<div class="box_title first">
		<h2 class="title">히트맵신청</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">히트맵신청</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">상점명</th>
			<td><input type="text" name="cusNm" class="input" size="30" value="<?=inputText($cfg['company_mall_name'])?>"></td>
		</tr>
		<tr>
			<th scope="row">담당자 성명</th>
			<td><input type="text" name="staffNm" class="input" size="20"></td>
		</tr>
		<tr>
			<th scope="row">담당자 직위</th>
			<td><input type="text" name="staffLv" class="input" size="20"></td>
		</tr>
		<tr>
			<th scope="row">담당자 연락처</th>
			<td><input type="text" name="cusPhone" class="input" size="20"></td>
		</tr>
		<tr>
			<th scope="row">담당자 이메일</th>
			<td>
				<input type="text" name="cusEmail1" class="input" size="20"> @ <input type="text" name="cusEmail2" class="input" size="20">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function formCheck(f) {
		if(!checkText(f.cusNm, '상점명을')) return false;
		if(!checkText(f.staffNm, '담당자 성명을')) return false;
		if(!checkText(f.staffLv, '담당자 직위를')) return false;
		if(!checkText(f.cusPhone, '담당자 연락처를')) return false;
		if(!checkText(f.cusEmail1, '담당자 이메일주소를')) return false;
		if(!checkText(f.cusEmail2, '담당자 이메일주소를')) return false;

		return confirm('서비스약관에 동의하시겠습니까?');
	}
</script>