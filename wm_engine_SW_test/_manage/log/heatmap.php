<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  히트맵 로그인 연동
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['logger_heatmap_cusId'] || !$cfg['logger_heatmap_PASSWORD']) msg('서비스 신청 후 이용해 주세요.', '?body=log@heatmap_apply');
	$cusPw = $cfg['logger_heatmap_PASSWORD'].'_'.time();

?>
<form method="post" action="http://heatmap.co.kr/heatmap/login.do" target="_blank">
	<input type="hidden" name="param" value="partnersLoginPs">
	<input type="hidden" name="cusPw" value="<?=$cusPw?>">
	<div class="box_title first">
		<h2 class="title">히트맵 로그인 연동</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">히트맵 로그인 연동</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">아이디</th>
			<td><input type="text" name="cusId" class="input" size="20" value="<?=$cfg['logger_heatmap_cusId']?>" readonly></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="히트맵 접속"></span>
	</div>
</form>