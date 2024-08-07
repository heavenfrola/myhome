<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  퀵프리뷰 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($type == 'mobile') $device_flag = 'm';

	include_once $engine_dir."/_engine/include/design.lib.php";
	include $root_dir.'/_skin/'.$device_flag.'config.cfg';

	$skin_name = ($design['edit_skin']) ? $design['edit_skin'] : $design['skin'];
	include $root_dir."/_skin/".$skin_name."/skin_config.".$_skin_ext['g'];

	if(!$_skin['qd1_bgcolor']) $_skin['qd1_bgcolor'] = '#000000';
	if(!$_skin['qd1_use']) $_skin['qd1_use'] = 'N';
	if(!$_skin['qd2_use']) $_skin['qd2_use'] = 'N';
	if(!$_skin['qd2_htype']) $_skin['qd2_htype'] = '1';
	if(!$_skin['qd1_scroll']) $_skin['qd1_scroll'] = '1';
	if($_skin['qd2_ctype'] == 6) $qd2_manual = $_skin['qd2_cno'];

	$_opacity = array();
	for($i = 0; $i <= 100; $i+=10) {
		$_opacity[] = $i;
	}
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.js"></script>
<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?=$engine_url?>" type="text/css">
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		$('.colorpicker').click(function() {
			$('.colorpicker_marker').hide();
			$(this).parent().find('.colorpicker_marker').show();
		});
		$('.colorpicker').blur(function() {
			orderColorChg();
			$('.colorpicker_marker').hide();
		});
	});

	function orderColorChg() {
		$('#c1_color').css('background', $('.colorpicker').val());
	}
</script>
<form method="post" action="?" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@skin.exe">
	<input type="hidden" name="exec" value="cfg">
	<input type="hidden" name="config_code" value="qd">
	<input type="hidden" name="edit_skin" value="<?=$skin_name?>">
	<div class="box_title first">
		<h2 class="title">퀵프리뷰 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">퀵프리뷰 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
			<col style="width:550px">
		</colgroup>
		<tr>
			<th scope="row">사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="qd1_use" value="Y" <?=checked($_skin['qd1_use'], 'Y')?>> 사용함</label><br>
				<label class="p_cursor"><input type="radio" name="qd1_use" value="N" <?=checked($_skin['qd1_use'], 'N')?>> 사용안함</label>
			</td>
			<td rowspan="6" class="sample">
				<img src="<?=$engine_url?>/_manage/image/design/quickdetail/sample.jpg" alt="sample">
				<p class="explain icon left"><strong>‘퀵프리뷰’</strong>란 상품 상세 화면에 들어가지 않고도 상품 목록에서 상품의 모든 정보를 확인할 수 있는 기능입니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">가로길이</th>
			<td>
				<input type="text" name="qd1_width" class="input numberOnly" size="10" value="<?=$_skin['qd1_width']?>"> (px 및 % 사용가능)
			</td>
		</tr>
		<tr>
			<th scope="row">상단여백</th>
			<td>
				<input type="text" name="qd1_margin" class="input numberOnly" size="10" value="<?=$_skin['qd1_margin']?>"> (px 및 % 사용가능)
			</td>
		</tr>
		<tr>
			<th scope="row">배경색</th>
			<td>
				<span id="c1_color" class="box_color" style="background:<?=$_skin['qd1_bgcolor']?>"></span>
				<input type="text" id="c1_code" name="qd1_bgcolor" class="input colorpicker" size="10" value="<?=$_skin['qd1_bgcolor']?>">
				<span class="color_select"><ul><li><div id="colorpicker" class="colorpicker_marker"></li></ul></div>
				<script type="text/javascript">$('#colorpicker').farbtastic('#c1_code'); orderColorChg();</script>
			</td>
		</tr>
		<tr>
			<th scope="row">배경투명도</th>
			<td>
				<?=selectArray($_opacity, 'qd1_opacity', 1, '', $_skin['qd1_opacity'])?> %
			</td>
		</tr>
		<tr>
			<th scope="row">스크롤바 위치</th>
			<td>
				<label class="p_cursor"><input type="radio" name="qd1_scroll" value="1" <?=checked($_skin['qd1_scroll'], '1')?>> 전체창</label><br>
				<label class="p_cursor"><input type="radio" name="qd1_scroll" value="2" <?=checked($_skin['qd1_scroll'], '2')?>> 본문</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>