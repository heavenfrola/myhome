<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 설정 저장 처리
	' +----------------------------------------------------------------------------------------------+*/

	#로고이미지저장
	if($_FILES['upfile']['name']) {

		if($_FILES['upfile']['size'] > 100000) msg('로고이미지 업로드 제한용량은 100kb까지입니다.');

		$img_type_exp=explode(".", $_FILES['upfile']['name']);
		$img_type=$img_type_exp[1];
		$updir=$dir['upload']."/".$dir['mobile'];
		makeFullDir($updir);

		$up_filename="logo";
		$up_info=uploadFile($_FILES["upfile"], $up_filename, $updir, "gif|png|jpg|jpeg");
	}

	#컨피그파일저장
?>
<form name="mobileConfigForm" method="post" action="<?=$_SERVER['PHP_SELF']?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="mobile_img_type" value="<?=$img_type?>">
	<input type="hidden" name="cfg_msg" value="설정되었습니다">
</form>
<script type="text/javascript">document.mobileConfigForm.submit();</script>
<?
	#msg("변경되었습니다", "reload", "parent");
?>