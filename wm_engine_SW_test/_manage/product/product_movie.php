<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  flv 동영상 본문삽입 다이얼로그
	' +----------------------------------------------------------------------------------------------+*/
	include_once $root_dir.'/_skin/config.cfg';

	if(file_exists($root_dir."/_skin/$design[skin]/img/flash/flvPlayer.swf")) {
		$player = getimagesize($root_dir."/_skin/$design[skin]/img/flash/flvPlayer.swf");
	}

?>
<form onsubmit="return putFLV(this)" style="width:400px">
	<input type="hidden" name="filename" value="<?=$filename?>">
	<input type="hidden" name="area" value="<?=$area?>">
	<div class="box_middle">
		<ul class="list_msg left">
			<li>FLV 동영상을 재생하기 위해서는 서버보안 설정및 플레이어 설치가 필요하므로, 동영상 재생을 위한 준비가 되어있는지 확인 해 주시기 바랍니다.</li>
			<li>상세설명 페이지의 속도가 느려질 수 있으므로 <span class="p_color2">편집창에서는 동영상이 재생 되지 않으며, 코드형태로 표시</span>됩니다.</li>
		</ul>
	</div>
	<?if(!$player){?>
	<div class="box_full left">동영상 플레이어가 설치되어있지 않습니다.</div>
	<div class="pop_bottom">
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close()"></span>
	</div>
	<?return;}?>
	<table class="tbl_row">
		<caption class="hidden">동영상 설정</caption>
		<colgroup>
			<col style="width:30%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">미리보기</th>
			<td>
				<div id="Movie" align="center" style="width: 100px; height: 100px"></div>
				<script type="text/javascript">
					flashMovie('Movie','/_skin/<?=$design['skin']?>/img/flash/flvPlayer.swf', '100px', '100px','flvPath=<?=$filename?>&volumValue=10','transparent');
				</script>
			</td>
		</tr>
		<tr>
			<th scope="row">가로 해상도</th>
			<td><input type="text" name="width" class="input" size="5" value="<?=$player[0]?>"> px</td>
		</tr>
		<tr>
			<th scope="row">세로 해상도</th>
			<td><input type="text" name="height" class="input" size="5" value="<?=$player[1]?>"> px</td>
		</tr>
		<tr>
			<th scope="row">볼륨</th>
			<td><input type="text" name="vol" class="input" size="5" value="50"> %</td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close()"></span>
	</div>
</form>

<script type="text/javascript">
	setPoptitle('FLV 동영상 삽입');
	selfResize();

	function putFLV(f) {
		if(!checkBlank(f.width, '동영상 가로 길이를 입력해주세요.')) return false;
		if(!checkBlank(f.height, '동영상 세로 길이를 입력해주세요.')) return false;
		if(!checkBlank(f.vol, '볼륨을 입력해주세요.')) return false;

		tag = "[[동영상삽입영역@Movie<?=$now?>@"+f.filename.value+"@"+f.width.value+"@"+f.height.value+"@"+f.vol.value+"]]";

		var area = opener.document.getElementById(f.area.value);

		R2Na_no = area.getAttribute('no');

		R2Na = opener.document.getElementById("R2Na_"+R2Na_no).contentWindow;
		R2Na.focus();
		oRange = R2Na.document.selection;
		oRange.createRange().pasteHTML(tag);
		opener.R2NaTocontent(opener.R2Na_bid[R2Na_no]);

		self.close();
		return false;

	}
</script>