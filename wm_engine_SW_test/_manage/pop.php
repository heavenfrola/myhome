<?PHP

	include_once "manage.header.php";

	$pop = 1;
	$close_btn = '<span class="box_btn_s box_btn_option gray"><input type="button" value="창닫기" onclick="self.close()"></span>';
	if(!$pop_width) {
		$pop_width = "100%";
	}
	$pg_dsn = 'admin';

?>
<style type="text/css" title="">
body {background:none;}
</style>
<div class="popupContent">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop"></div>
		<?php if($_inc[0] == 'design') { ?>
		<div id="allView">
			<span class="box_btn_s"><a href="javascript:;" onClick="layTgl2('setDiv');">설정</a></span>
		</div>
		<?php } ?>
	</div>
	<?php include $body_file; ?>
</div>
<?php
	if($_inc[0] == 'design') {
		$_dmode = array("한개의 창만 사용", "여러개의 새창 사용");
		if(!$dmode) {
			$def_dmode = $_COOKIE['def_dmode'];
			if(!$def_dmode) $def_dmode = 0;
			$dmode = $def_dmode;
		}
?>
<div id="setDiv" style="display: none;" class="register setDiv">
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th style="width: 130px">코드편집창 새창설정</th>
			<td><?=selectArray($_dmode,"dmode", 2, "", $def_dmode,"setConfig('def_dmode',this.value);location.reload();");?></td>
		</tr>
	</table>
	<div class="footer"><span class="btn gray small"><input type="button" value="닫기" onClick="layTgl2('setDiv');"></span></div>
</div>
<?php } ?>

<script type="text/javascript">
	window.onload = function() {
		if(typeof(window["selfResize"]) == "function") selfResize();
	}

	$(function(){
		$('.datepicker').datepicker({
			monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
			dayNamesMin: ['일','월','화','수','목','금','토'],
			weekHeader: 'Wk',
			dateFormat: 'yy-mm-dd',
			autoSize: false,
			changeYear: true,
			changeMonth: true,
			showButtonPanel: true,
			currentText: '오늘 <?=date("Y-m-d", $now)?>',
			closeText: '닫기'
		});
	});
</script>
<?php close(1); ?>