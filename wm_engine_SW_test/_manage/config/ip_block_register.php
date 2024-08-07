<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  IP차단 설정
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$no = numberOnly($_GET['no']);
	$data = $pdo->assoc("select ip, title from `wm_deny_ip` where `no`='$no'");
	$title = stripslashes($data['title']);
	$block_ip_5 = explode('.', $data['ip']);
	for($ii=0; $ii<4; $ii++){
		${'block_ip_'.$ii} = $block_ip_5[$ii];
	}

?>
<form name="ipFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>?body=config@ip_block.exe"  onsubmit="return checkip(this)" target="hidden<?=$now?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">IP차단 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">IP차단 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">차단 IP</th>
			<td>
			<input type="text" name="block_ip_0" value="<?=$block_ip_0?>" class="input" maxlength="3" size="1">
			<input type="text" name="block_ip_1" value="<?=$block_ip_1?>" class="input" maxlength="3" size="1">
			<input type="text" name="block_ip_2" value="<?=$block_ip_2?>" class="input" maxlength="3" size="1">
			<input type="text" name="block_ip_3" value="<?=$block_ip_3?>" class="input" maxlength="3" size="1">
			</td>
		</tr>
		<tr>
			<th scope="row">등록사유</th>
			<td>
			<textarea name="title" class="txta"><?=inputText($title)?></textarea>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="history.back();"></span>
	</div>
</form>

<script language="JavaScript">
	function checkip(f){
		if(!checkNum(f.block_ip_0,'아이피는 숫자만 입력해주세요.')) return false;
		if(!checkNum(f.block_ip_1,'아이피는 숫자만 입력해주세요.')) return false;
		if(!checkNum(f.block_ip_2,'아이피는 숫자만 입력해주세요.')) return false;
		if(!checkNum(f.block_ip_3,'아이피는 숫자만 입력해주세요.')) return false;
		if(!checkBlank(f.title,'등록사유를 입력해주세요.')) return false;

        printLoading();
	}
</script>