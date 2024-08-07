<?PHP

	// 참조할 에디터 선택
	switch($_GET['content_no']) {
		case 'm' :
			$content_id = 'm_content';
		break;
		default :
			$content_id = 'content2';
	}

?>
<style type="text/css" title="">
body {background:#fff;}
</style>

<div id="previewTop" class="pop_bottom">
	<span class="box_btn blue"><input type="button" onclick="submit();" value="적용"></span>
	<span class="box_btn gray"><input type="button" onclick="self.close()" value="닫기"></span>
</div>
<div style="padding:5px;">
	<textarea name="contentField" id="contentField" style="width:100%; height:800px;"></textarea>
</div>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript">
	var origin = opener.oEditors.getById['<?=$content_id?>'];
	var field = document.getElementById('contentField');
	$(document).ready(function() {
		if(!opener.document) return;
		field.value = origin.getContents();
		seCall('contentField', '');
		return;
	});

	function submit() {
		var content = oEditors.getById['contentField'].getContents();
		origin.setContents(content);
		self.close();
	}
</script>
<?close(1);?>