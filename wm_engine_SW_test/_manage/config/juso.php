<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  행정자치부 주소 API 설정
	' +----------------------------------------------------------------------------------------------+*/

	$wec = new weagleEyeClient($_we, 'account');
	$asvcs = $wec->call('getSvcs', array('key_code'=>$wec->config['wm_key_code']));
	$account_type = $asvcs[0]->type[0];

?>
<form name="jusoFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return jusoFrmCheck(this);" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">

	<div class="box_title first">
		<h2 class="title">도로명주소 API 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">도로명주소 API 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">주소 API 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="juso_api_use" value="Y" <?=checked($cfg['juso_api_use'] ,'Y')?>> 행안부 API</label>
				<label class="p_cursor"><input type="radio" name="juso_api_use" value="D" <?=checked($cfg['juso_api_use'] ,'D')?>> 다음 API</label>
				<label class="p_cursor"><input type="radio" name="juso_api_use" value="N" <?=checked($cfg['juso_api_use'] ,'N').checked($cfg['juso_api_use'],"")?>> 사용안함</label>
				<ul class="list_msg">
					<li>행정안전부에서 제공하는 주소 API를 사용하여 항상 최신 기준의 주소 정보를 제공받으실수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<?php if ($cfg['juso_api_server'] == 2) { ?>
		<tr>
			<th scope="row">주소 API 승인키</th>
			<td>
				<input type="text" name="juso_api_key" value="<?=$cfg['juso_api_key']?>" class="input" size="64" >
				<ul class="list_msg">
					<li>주소 API 취득 방법</li>
					<li>1. <a href="https://www.juso.go.kr/addrlink/addrLinkRequestMainNew.do?cntcMenu=API" target="_blank">행정자체부 주소 API 신청페이지</a>에 접속해 주세요.</li>
					<li>2. 신청하기 버튼을 클릭해주세요.</li>
					<li>3. 신청서를 정확히 입력해 주세요. (URL 정보를 정확히 입력해주셔야 정상 동작합니다.)</li>
					<li>4. 발급받은 API 승인키를 관리자 설정에 입력해주세요.</li>
					<li class="p_color2">* 사이트 도메인이 변경된 경우 API 승인키를 재발급 하셔야합니다.</li>
					<li>행안부 서버 사용시 해킹 의심이 발생할 경우 통보없이 차단될수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<?php } ?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue" style="margin-top:10px;"><input type="submit" value="확인"></span>
	</div>
</form>