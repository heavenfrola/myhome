<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 미리보기
	' +----------------------------------------------------------------------------------------------+*/

	if ($_GET['skin_preview_end'] == 'Y') {
		unset($_SESSION['skin_preview_name']);
?>
<script type="text/javascript">
	alert('스킨 미리보기가 종료되었습니다');
	top.self.close();
</script>
<?php
		exit;
	}

	if($admin['no'] && trim($_GET['skin_name']) != ""){
		$_SESSION['skin_preview_name']=trim($_GET['skin_name']);
	}else{
		return;
	}

	$preview_url = (substr($_GET['skin_name'], 0, 2) == 'm_') ? $m_root_url : $p_root_url;
	$preview_url .= '?sesskey='.session_id();

    header('Location: '.$preview_url);
    exit;

?>
<script type="text/javascript">
	window.onbeforeunload=function (){
		frm=document.getElementsByName(hid_frame);
		frm[0].src='./?body=<?=$body?>&skin_preview_end=Y';
	}
	$(function() {
		$('#skin_preview').height($(window).height()-5);
	});

	$(window).resize(function() {
		$('#skin_preview').height($(window).height()-5);
	});
</script>
<iframe src="<?=$preview_url?>" id="skin_preview" name="skin_preview" style="width:100%; height:100%; background: #fff;" frameborder="0"></iframe>