<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  접속통계 설정
	' +----------------------------------------------------------------------------------------------+*/
	$log_file=($cfg['log_file']=="Y") ? "파일시스템" : "MySQL";
	if(empty($cfg['count_log_use']) == true) $cfg['count_log_use'] = 'Y';
	if(empty($cfg['use_log_scheduler']) == true) $cfg['use_log_scheduler'] = 'N';

?>
<form name="eveFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="log@count_log_config.exe">
	<div class="box_title first">
		<h2 class="title">접속통계 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">접속통계 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">통계 기록</th>
			<td>
				<ul>
					<li><label><input type="radio" name="count_log_use" value="Y" <?=checked($cfg['count_log_use'], 'Y')?>> 사용함</label></li>
					<li><label><input type="radio" name="count_log_use" value="N" <?=checked($cfg['count_log_use'], 'N')?>> 사용안함</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">중복 체크</th>
			<td>
				<label class="p_cursor"><input type="radio" name="log_term" value="2" <?=checked($cfg[log_term],"2").checked($cfg[log_term],"1").checked($cfg[log_term],"")?>> 웹브라우저를 다시 시작하면 증가 <span class="explain">(추천)</span></label><br>
				<label class="p_cursor"><input type="radio" name="log_term" value="3" <?=checked($cfg[log_term],"3")?>> 하루에 한번 증가</label>
			</td>
		</tr>
		<tr>
			<th scope="row">로깅 옵션</th>
			<td>
				<label class="p_cursor"><input type="radio" name="log_unknown" value="" <?=checked($cfg[log_unknown],"")?>> 정상적인 접속만 로그 기록</label><br>
				<label class="p_cursor"><input type="radio" name="log_unknown" value="Y" <?=checked($cfg[log_unknown],"Y")?>> 로봇접속을 포함한 모든 접속을 로그 기록</label>
			</td>
		</tr>
		<tr>
			<th scope="row">스케쥴링 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_log_scheduler" value="Y" <?=checked($cfg['use_log_scheduler'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="use_log_scheduler" value="N" <?=checked($cfg['use_log_scheduler'], 'N')?>> 사용안함<br>
				<ul class="list_info">
					<li>이벤트 등으로 인한 대량 유입 발생 시 사이트의 접속 속도가 빨라집니다.</li>
					<li>접속통계 및 조회 수, 판매 수 정보가 최대 한 시간 후에 반영됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"> 로그 저장소</th>
			<td><?=$log_file?></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>