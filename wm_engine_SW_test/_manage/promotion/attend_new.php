<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 설정/수정
	' +----------------------------------------------------------------------------------------------+*/

	if(!isTable($tbl['attend_new'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['attend_new']);
		$pdo->query($tbl_schema['attend_list']);
	}

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from $tbl[attend_new] where no='$no'");
		$data = array_map('stripslashes', $data);

		$start_date = date('Y-m-d', $data['start_date']);
		if($data['finish_date'] == '2147483647') {
			$unlimited = 'Y';
		} else {
			$finish_date = $data['finish_date'] > 0 ? date('Y-m-d', $data['finish_date']) : '';
			if(!$finish_date) $unlimited = 'Y';
		}
	} else {
		$data['event_type'] = 1;
		$data['repeat_type'] = 1;
		$data['check_type'] = 1;
		$data['check_use'] = 'Y';
	}

	$today = date('Y-m-d');
	$cpnres = $pdo->iterator("select no, name from $tbl[coupon] where down_type = 'D' and (rdate_type=1 or (rstart_date <= '$today' and rfinish_date >= '$today')) order by name asc");
    foreach ($cpnres as $cpn) {
		$cpn['name'] = stripslashes($cpn['name']);
		$sel = $cpn['no'] == $data['prize_cno'] ? 'selected' : '';
		$coupon_list .= "<option value='$cpn[no]' $sel>$cpn[name]</option>";
	}

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@attend.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">출석체크 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">출석체크 설정</caption>
		<colgroup>
			<? if($no == 1) {?>
			<col style="width:20%">
			<?} else {?>
			<col style="width:15%">
			<?}?>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="check_use" value="Y" <?=checked($data['check_use'], 'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="check_use" value="N" <?=checked($data['check_use'], 'N')?>> 중단</label>
			</td>
		</tr>
		<tr>
			<th scope="row">출석체크 명</th>
			<td><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full"></td>
		</tr>
		<tr>
			<th scope="row">기간 설정</th>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" class="input datepicker" size="10"> 부터
				<input type="text" name="finish_date" value="<?=$finish_date?>" class="input datepicker" size="10"> 일 까지
				<label class="p_cursor"><input type="checkbox" id="unlimited" name="unlimited" value="Y" <?=checked($unlimited, 'Y')?>> 무제한</label>
				<!--
				<ul class="list_msg">
					<li>이미 생성된 출석체크 기간과 중복이 불가능합니다.</li>
					<li>무제한 출석체크가 진행중인 경우 해당 출석체크 이벤트를 종료하셔야 새로운 이벤트를 등록 가능합니다.</li>
				</ul>
				-->
			</td>
		</tr>
		<tr>
			<th scope="row">참여 방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_type" value="1" <?=checked($data['event_type'], 1)?>> 누적 참여형</label>
				<label class="p_cursor"><input type="radio" name="event_type" value="2" <?=checked($data['event_type'], 2)?>> 연속 참여형</label>
				<ul class="list_msg">
					<li>누적 참여형 : 출석체크 기간 동안 총 출석일이 일정 기간 이상 출석한 회원에게 혜택 지급</li>
					<li>연속 참여형 : 출석체크 기간 동안 연속으로 일정 기간 출석한 회원에게 혜택 지급</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">달성 조건</th>
			<td>
				총 출석횟수가 <input type="text" name="complete_day" value="<?=$data['complete_day']?>" class="input" size="5"> 일 이상 출석 시 성공
			</td>
		</tr>
		<tr>
			<th scope="row">혜택</th>
			<td>
				<ul>
					<li>쿠폰   지급 <select name="prize_cno"><option value="">:: 쿠폰선택 ::</option><?=$coupon_list?></select></li>
					<li>적립금 지급 <input type="text" name="prize_milage" value="<?=$data['prize_milage']?>" class="input" size="5"> 원</li>
				</ul>
				<ul class="list_msg">
					<li>출석 혜택은 동시에 여러 가지로 지급하실 수 있습니다.</li>
					<li>지급될 각 쿠폰, 적립금은 각각의 설정을 따릅니다.</li>
					<li>출석체크로 쿠폰 지급 시 수동발급으로 생성된 쿠폰만 사용 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">혜택 중복여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="repeat_type" value="1" <?=checked($data['repeat_type'], 1)?>> 이벤트 기간 중 한 번만 지급</label><br>
				<label class="p_cursor"><input type="radio" name="repeat_type" value="2" <?=checked($data['repeat_type'], 2)?>> 이벤트 기간 중 출석체크 조건 달성 시 마다 지급</label>
				<p class="explain">쿠폰 다운로드 횟수가 초과된 경우 추가 지급되지 않으며 쿠폰 고유의 설정을 따릅니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">출석체크 방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="check_type" value="1" <?=checked($data['check_type'], 1)?>> 출석체크 버튼 클릭</label><br>
				<label class="p_cursor"><input type="radio" name="check_type" value="2" <?=checked($data['check_type'], 2)?>> 로그인 시 자동 체크</label>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<ul class="list_msg left">
			<li>1회 이상 출석체크가 된 경우 기간 수정이 불가능합니다.</li>
			<li>1회 이상 혜택 지급이 된 경우 혜택 내용 및 참여 방식, 달성 조건, 혜택 중복 여부의 수정이 불가능합니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<?if($no > 0) {?>
		<span class="box_btn blue"><input type="submit" value="수정"></span>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="removeAttend(<?=$data['no']?>)"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close();"></span>
		<?} else {?>
		<span class="box_btn blue"><input type="submit" value="생성"></span>
		<?}?>
	</div>
</form>

<script type="text/javascript">
	function checkUnlimited() {
		var checked = $('#unlimited').attr('checked');
		if(checked == true) {
			$('input[name=finish_date]').attr('disabled', true);
		} else {
			$('input[name=finish_date]').attr('disabled', false);
		}
	}

	function removeAttend(no) {
		if(confirm('출석체크 삭제시 모든 참여내역 및 혜택 지급 내역이 삭제됩니다.\n정말 삭제하시겠습니까?')) {
			$.post('./index.php?body=promotion@attend.exe', {'no':no, 'exec':'remove'}, function(r) {
				opener.location.reload();
				self.close();
			});
		}
	}

	$(window).ready(function() {
		$('#unlimited').click(function() {
			checkUnlimited();
		});
		checkUnlimited();
	});
</script>