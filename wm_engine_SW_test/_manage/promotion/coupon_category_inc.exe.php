<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카테고리선택
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$case = numberOnly($_GET['case']);
	switch($case) {
		case 1: $title = '혜택을 적용할 카테고리를 선택하세요'; break;
		case 3: $title = '혜택을 적용 제외할 카테고리를 선택하세요'; break;
	}

	$disabled = ($_GET['download_cnt']) ? 'disabled' : '';

	function readCategory($ctype, $level = 1, $parent = null) {
		global $tbl, $_cate_colname, $case, $cfg, $disabled, $pdo;

		if($parent) {
			$colname = $_cate_colname[1][($level-1)];
			$w .= " and `$colname`='$parent'";
		}

		$res = $pdo->iterator("select no, name, level from {$tbl['category']} where ctype=$ctype and level='$level' $w order by sort asc");
        foreach ($res as $data) {
			$data['name'] = stripslashes($data['name']);
			echo "<li class='depth_{$data['level']}'><label><input type='checkbox' $disabled class='category_items' value='{$data['no']}' onclick='setTargetValue($case);'> {$data['name']}</label><li>";

			if($data['level'] < $cfg['max_cate_depth']) readCategory($ctype, ($data['level']+1), $data['no']);
		}
		return;
	}

?>
<style type='text/css'>
.categoryList {padding:10px 5px; margin-bottom: 10px; border:1px solid #aaa;}
.categoryList legend {font-weight:bold;}
.categoryList li {margin:2px 0; padding-left:20px; background:url('<?=$engine_url?>/_manage/image/icon/ic_folder_o.gif') no-repeat left;}
.categoryList li label {cursor:pointer;}
<?for($i = 2; $i <= $cfg['max_cate_depth']; $i++) {?>
.depth_<?=$i?> {margin-left:<?=(20*($i-1))?>px !important;}
<?}?>
</style>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop"><?=$title?></div>
	</div>
	<div id="popupContentArea" style="overflow:auto; height:400px; margin:5px 0;">
		<fieldset class="categoryList">
			<legend>매장분류</legend>
			<ul>
				<? readCategory(1); ?>
			</ul>
		</fieldset>
		<?if($cfg['xbig_mng']) {?>
		<fieldset class="categoryList">
			<legend><?=$cfg['xbig_name']?></legend>
			<ul>
				<? readCategory(4); ?>
			</ul>
		</fieldset>
		<?}?>
		<?if($cfg['ybig_mng']) {?>
		<fieldset class="categoryList">
			<legend><?=$cfg['ybig_name']?></legend>
			<ul>
				<? readCategory(5); ?>
			</ul>
		</fieldset>
		<?}?>
		<fieldset class="categoryList">
			<legend>기획전</legend>
			<ul>
				<? readCategory(2); ?>
			</ul>
		</fieldset>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="button" value="확인" onclick="targetSelector.close();"></span>
	</div>
</form>

<script type='text/javascript'>
	var data = $('input[name=attach_items_<?=$case?>]').val().replace(/^\[/, '').replace(/\]$/, '').split('][');
	for(var i = 0; i < data.length; i++) {
		if(data[i]) $('.category_items[value='+data[i]+']').prop('checked', true);
	}
</script>