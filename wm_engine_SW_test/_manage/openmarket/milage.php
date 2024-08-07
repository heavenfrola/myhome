<?PHP

	if(empty($cfg['milage_check_ip'])) {
		$cfg['milage_check_ip']  = '118.129.253.34@118.129.253.43';
		$cfg['milage_check_ip'] .= '@111.91.139.55@58.72.239.56@58.72.239.57@111.91.140.58@182.162.206.167';
	}

?>
<form method="post" target="hidden<?=$now?>">
	<input type="hidden" name="milage_set" value="Y">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="naver_milage">
	<div class="box_title first">
		<h2 class="title">네이버마일리지 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버마일리지 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">마일리지 아이디</th>
			<td><input type="text" name="milage_api_id" class="input" size="15" value="<?=$cfg['milage_api_id']?>"></td>
		</tr>
		<tr>
			<th scope="row">인증키</th>
			<td><input type="text" name="milage_api_key" class="input" size="50" value="<?=$cfg['milage_api_key']?>"></td>
		</tr>
		<tr>
			<th scope="row">상태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="milage_status" value="1" <?=checked($cfg['milage_status'], 1).checked($cfg['milage_status'], false)?>> 검수중</label>
				<label class="p_cursor"><input type="radio" name="milage_status" value="2" <?=checked($cfg['milage_status'], 2)?>> 검수완료/사용중</label>
				<label class="p_cursor"><input type="radio" name="milage_status" value="3" <?=checked($cfg['milage_status'], 3)?>> 사용중지</label>
				<ul class="list_msg">
					<li>검수중 - 네이버마일리지 검수상태시 체크(검수아이피에 기재된 아이피만 사용가능)</li>
					<li>검수완료/사용중 - 모든 고객 사용가능</li>
					<li>사용중지 - 모든 고객 사용불가</li>
					<li>검수완료/사용중으로 상태변경시 <u>네이버마일리지측의 검수가 필요</u>합니다.</li>
					<li>마케팅팀에 문의하여 <u class="p_color2">검수 완료 여부를 확인하시고</u> 변경해 주세요.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">검수아이피</th>
			<td>
				<input type="text" name="milage_check_ip" class="input" size="120" value="<?=$cfg['milage_check_ip']?>">
				<ul class="list_msg">
					<li>상태가 검수중일때 네이버마일리지/캐시를 사용할수 있는  IP(IP간 @ 구분)</li>
					<li class="p_color2">당신의 IP는 <?=$_SERVER['REMOTE_ADDR']?> 입니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">네이버유입<br>대표도메인설정</th>
			<td>
				<input type="text" name="milage_main_domain" class="input" size="100" value="<?=$cfg['milage_main_domain']?>">
				<ul class="list_msg">
					<li>네이버 지식쇼핑,네이버 페이등을 통해 유입되는 대표도메인을 등록하시면 됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">부도메인설정</th>
			<td>
				<input type="text" name="milage_domains" class="input" size="100" value="<?=$cfg['milage_domains']?>">
				<ul class="list_msg">
					<li>네이버 유입의 유실을 막기위해선 사용중인 모든 도메인을 등록하셔야 합니다.</li>
					<li>도메인간,로 구분하여 등록하시면됩니다.</li>
					<li>http://제외 </li>
					<li>예 : www.abc.com,abc.com,www.abcd.com,abcd.com</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>