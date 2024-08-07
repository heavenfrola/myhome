<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매직앱 설치유도
	' +----------------------------------------------------------------------------------------------+*/
	
	$app_config = 'Y';
	include_once $engine_dir."/_manage/wmb/push.exe.php";

	if(!$cfg['app_config_use']) $cfg['app_config_use'] = "N";

?>
<form id="cfgfrm" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">매직앱 설치유도</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">매직앱 설치유도</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="app_config_use" value="Y" <?=checked($cfg['app_config_use'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="app_config_use" value="N" <?=checked($cfg['app_config_use'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>