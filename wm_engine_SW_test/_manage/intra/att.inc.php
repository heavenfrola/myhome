<?PHP

	if($cfg['intra_day_check'] != 'Y') return;

	$myatt = $pdo->assoc("select * from $tbl[intra_day_check] where member_no='$admin[no]' and date='".date('Y-m-d', $now)."'");
	$late = (date("H:i", $now) > $cfg['intra_day_check_start']) ? 'Y' : 'N';
	$_my_attending = ($myatt['no']) ? 1 : 0; // 나의 출석
	$_my_leaving = ($myatt['etime']) ? 1 : 0; // 나의 퇴근
	$_stime = $_my_attending ? date('H:i:s', $myatt['stime']) : '출근전';
	$_etime = $_my_leaving ? date('H:i:s', $data['etime']) : '퇴근전';

?>
<form name="dayckFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" class="work">
<input type="hidden" name="body" value="intra@att_ck.exe">
<input type="hidden" name="mode" value="">
<input type="hidden" name="late" value="<?=$late?>">
	<div id="latediv" class="left">
		<p>지각사유 입력 후 출석체크하기 버튼을 눌러주세요</p>
		<p>
			<input type="checkbox" name="re_modi" value="Y" id="late_re_modi">
			<label for="late_re_modi" class="p_cursor">지각정정을 요청합니다.</label>
		</p>
		<textarea name="late_detail" class="txta"></textarea>
		<div class="bottom_btn center">
			<span class="box_btn blue"><input type="button" value="확인" onclick="day_check(1)"></span>
			<span class="box_btn gray"><input type="button" value="취소" onclick="$('#latediv').hide()"></span>
		</div>
	</div>
</form>
<script type="text/javascript">
	function day_check(mode){
		var f = document.dayckFrm;
		f.mode.value = mode;
		if(mode == '1') {
			<?if($_my_attending) {?>
			window.alert("이미 출근체크가 완료되었습니다.");
			return;
			<?} else {?>
			if(f.late.value == 'Y'){
				var w = document.getElementById("latediv");
				if(w.style.display == 'block') {
					if(!checkBlank(f.late_detail, '지각 사유를 입력해주세요.')) return;
				} else {
					w.style.display='block';
					return;
				}
			}
			<?}?>
			f.submit();
		} else if(mode == '2') {
			<?if($_my_leaving){?>
			window.alert("이미 퇴근하셨습니다");
			return;
			<?} else {?>
			if(!confirm('퇴근하시겠습니까?')) return;
			f.submit();
			<?}?>
		}
	}
</script>