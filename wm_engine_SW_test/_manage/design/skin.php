<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	$_skin_dir=$root_dir."/_skin/".$_skin_name;
	$_skin_url=$root_url."/_skin/".$_skin_name;
	include_once $_skin_dir."/skin_config.".$_skin_ext['g'];

	$sort = $_GET['sort'];
	if(empty($sort) == true) $sort = 'name';
	$sort_sel = array(
		'name' => '스킨명순',
		'date' => '등록일순'
	);

	versionChk("V3");

	$odir=opendir($root_dir."/_skin");
	$_skin_arr=array();
	while($arr=readdir($odir)){
		if(is_dir($root_dir."/_skin/".$arr) && $arr != "." && $arr != ".."){
			if(!skinFormatChk($arr)) continue;

			if($_GET['type'] == 'mobile') {
				if(substr($arr, 0, 2) !='m_') continue;
			} else {
				if(substr($arr, 0, 2) =='m_') continue;
			}

			if($design['skin'] == $arr) {
				$_skin_current = $arr;
				continue;
			}
			if($design['edit_skin'] == $arr) {
				$_skin_edit = $arr;
				continue;
			}

			$_date = filectime($root_dir."/_skin/".$arr);
			$_sort_name = $arr;
			$_sort_date = $_date.'_'.$arr;
			$_skin_arr[${'_sort_'.$sort}] = $arr;
			$_ctime[$arr] = $_date;
		}
	}
	ksort($_skin_arr);

	function parseSkin($nm) {
		global $root_dir, $manage_url, $design, $_ctime, $_skin_ext;

        if (file_exists($root_dir.'/_skin/'.$nm.'/skin_config.'.$_skin_ext['g']) == true) {
            require $root_dir.'/_skin/'.$nm.'/skin_config.'.$_skin_ext['g'];
        }
        $skin_type = (isset($_skin['skin_type']) == true) ? $_skin['skin_type'] : '';

		$_skin = array();
		$_skin['name'] =  $nm;
		$_skin['dir'] = $root_dir.'/_skin/'.$nm;
		$_skin['desc'] = $design['sn_'.$nm];
		$_skin['preview'] = (file_exists($_skin['dir'].'/preview.jpg') == true) ? '<img src="'.$manage_url.'/_skin/'.$nm.'/preview.jpg">' : '<span style="color:#c1c1c1; line-height:143px;">NO PREVIEW</span>';
		$_skin['flag'] = (file_exists($_skin['dir'].'/flag.gif') == true) ? '<img src="'.$manage_url.'/_skin/'.$nm.'/flag.gif" style="border:1px solid #d6d6d6;">' : '';
		$_skin['ctime'] = date('Y.m.d', $_ctime[$nm]);
		$_skin['split'] = ($_skin['desc']) ? ' | ' : '';
        $_skin['skin_type'] = $skin_type;

		return $_skin;
	}

	if(isset($_skin_edit) == false) $_skin_edit = $_skin_current;
	$_skin_current = parseSkin($_skin_current);
	$_skin_edit = parseSkin($_skin_edit);

	if($_GET['type'] == 'mobile') {
		$skin_preview_val = "width=380px, height=700px";
	}

?>
<div class="box_title first">
	<h2 class="title">추천 스킨</h2>
	<span class="btns">
		<span class="box_btn"><a href="http://redirect.wisa.co.kr/selfdesign" target="_blank">스킨 디자인 가이드</a></span>
		&nbsp;
		<span class="box_btn"><a href="http://www.wisa.co.kr/skinshop" target="_blank">더보기</a></span>
	</span>
</div>
<div class="box_bottom top_line">
	<?php if($type == "mobile") {?>
	<iframe name="skin_mobile" src="//www.wisa.co.kr/skin_mobile.php" scrolling="no" frameborder="0" style="width:100%; height:189px;"></iframe>
	<?php }else{ ?>
	<iframe name="skin_pc" src="//www.wisa.co.kr/skin_pc.php" scrolling="no" frameborder="0" style="width:100%; height:189px;"></iframe>
	<?php } ?>
</div>
<form name="skinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>?type=<?=$_GET['type']?>" target="hidden<?=$now?>" onsubmit="return ckFrm(this);">
<input type="hidden" name="body" value="design@skin.exe">
<input type="hidden" name="exec">
<input type="hidden" name="skin_name">
<input type="hidden" name="prefix">
	<div class="box_title">
		<h2 class="title">현재 디자인 스킨</h2>
	</div>
	<ul class="list_useskin">
		<?php foreach(array('use' => $_skin_current, 'edit' => $_skin_edit) as $key => $val) { ?>
		<li class="<?=$key?>">
			<?php if($val['name']) { ?>
			<div class="thumb">
				<div class="img"><?=$val['preview']?></div>
			</div>
			<div class="info">
				<div class="name">
                    <?=$val['flag']?> <?=$val['name']?>
                    <?php if ($val['skin_type']) { ?>
                    [<?=$val['skin_type']?>]
                    <?php } ?>
                </div>
				<div class="memo">
					<div id="<?=$key?>#_cinput_<?=$val['name']?>" style="display:none;">
						<input type="text" name="skin_comment[<?=$key?>#_<?=$val['name']?>]" size="54" class="input skin_comment_<?=$val['name']?>" maxlength="100" value="<?=$val['desc']?>" onkeypress="if(event.keyCode == 13) return false;">
						<span class="box_btn_s"><input type="button" value="입력" onclick="skinComment('<?=$val['name']?>', 3, '<?=$key?>')"></span>
					</div>
					<div id="<?=$key?>#_cview_<?=$val['name']?>"><span class="ctext_<?=$val['name']?>"><?=$val['desc']?></span></div>
				</div>
				<span class="box_btn_s"><input type="button" value="미리보기" onclick="window.open('./?body=design@skin_preview.frm&skin_name=<?=urlencode($val['name'])?>', 'skin_preview', '<?=$skin_preview_val?>');"></span>
				<span class="box_btn_s"><input type="button" value="스킨 설명글 수정" onclick="skinComment('<?=$val['name']?>', 1, '<?=$key?>');"></span>
			</div>
			<div class="btn">
				<ul>
					<li><span class="box_btn_s icon copy"><input type="button" value="스킨복사" onclick="openSkinDialog('copy', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon backup"><input type="button" value="스킨백업" onclick="fileExec(document.frm2, 'backup', '', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon delete"><input type="button" value="스킨삭제" onclick="openSkinDialog('delete', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon setup"><input type="button" value="스킨설정" onclick="skinConfig('<?=$val['name']?>');"></span></li>
					<?php if ($key == 'edit') { ?>
					<li><span class="box_btn_s icon use"><input type="button" value="사용스킨으로 설정" onclick="skinSelect('<?=$val['name']?>');"></span></li>
					<?php } ?>
					<?php if ($key == 'use') { ?>
					<li><span class="box_btn_s icon edit"><input type="button" value="편집스킨으로 설정" onclick="skinSelect('<?=$val['name']?>', 'edit');"></span></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</li>
		<?php } ?>
	</ul>
	<div class="box_title">
		<h2 class="title">스킨 선택</h2>
		<div class="btns">
			<?=selectArray($sort_sel, 'sort', false, null, $sort, "location.href='?body=design@skin&sort='+this.value")?>
		</div>
	</div>
	<ul class="list_skin">
		<?PHP
			foreach($_skin_arr as $key => $val){
				$val = parseSkin($val);
		?>
		<li>
			<div class="thumb">
				<div class="img"><?=$val['preview']?></div>
			</div>
			<div class="info">
				<div class="name">
                    <?=$val['flag']?> <?=$val['name']?>
                    <?php if ($val['skin_type']) { ?>
                    [<?=$val['skin_type']?>]
                    <?php } ?>
                </div>
				<div class="memo">
					<div id="cinput_<?=$val['name']?>" style="display:none;">
						<input type="text" name="skin_comment[<?=$val['name']?>]" size="54" class="input" maxlength="100" value="<?=$val['desc']?>" onkeypress="if(event.keyCode == 13) return false;">
						<span class="box_btn_s"><input type="button" value="입력" onclick="skinComment('<?=$val['name']?>', 3)"></span>
					</div>
					<div id="cview_<?=$val['name']?>">등록일 : <?=$val['ctime']?> <span class="ctext_<?=$val['name']?>"><?=$val['split']?><?=$val['desc']?></span></div>
				</div>
				<span class="box_btn_s"><input type="button" value="미리보기" onclick="window.open('./?body=design@skin_preview.frm&skin_name=<?=urlencode($val['name'])?>', 'skin_preview', '<?=$skin_preview_val?>');"></span>
				<span class="box_btn_s"><input type="button" value="스킨 설명글 수정" onclick="skinComment('<?=$val['name']?>', 1);"></span>
			</div>
			<div class="btn">
				<ul>
					<li><span class="box_btn_s icon copy"><input type="button" value="스킨복사" onclick="openSkinDialog('copy', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon backup"><input type="button" value="스킨백업" onclick="fileExec(document.frm2, 'backup', '', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon delete"><input type="button" value="스킨삭제" onclick="openSkinDialog('delete', '<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon setup"><input type="button" value="스킨설정" onclick="skinConfig('<?=$val['name']?>');"></span></li>
				</ul>
				<ul>
					<li><span class="box_btn_s icon use"><input type="button" value="사용스킨으로 설정" onclick="skinSelect('<?=$val['name']?>');"></span></li>
					<li><span class="box_btn_s icon edit"><input type="button" value="편집스킨으로 설정" onclick="skinSelect('<?=$val['name']?>', 'edit');"></span></li>
				</ul>
			</div>
		</li>
		<?php } ?>
	</ul>
	<div class="box_middle2">
		<ul class="list_msg left">
			<li>현재 사용 중인 스킨을 변경하시려면 해당 스킨명을 선택하신 뒤 사용 스킨으로 설정 버튼을 클릭하시면 바로 적용이 됩니다.</li>
			<li>편집 스킨을 따로 설정하실 경우 사이트 운영에 영향을 주지 않으므로 편집하신 뒤 스킨 미리보기 기능을 통해 테스트하실 수 있습니다.</li>
			<li>현재 사용 중인 스킨은 삭제가 불가능합니다.</li>
			<li>백업 또는 다운 받으신 <u>압축 파일</u>로 스킨을 생성 하실 수 있으며 지원하는 스킨의 형식에 벗어나는 경우 생성되지 않을 수 있습니다.</li>
		</ul>
	</div>
</form>
<form name="frm2" method="post" action="<?=$_SERVER['PHP_SELF']?>?type=<?=$_GET['type']?>" target="hidden<?=$now?>" onSubmit="return false;">
    <input type="hidden" name="body" value="design@design_config.exe">
    <input type="hidden" name="exec" value="">
    <input type="hidden" name="file_name">
    <input type="hidden" name="selected_skin">
</form>
<script type="text/javascript">
	selected_skin='';
	function ckFrm(f){
		ori_skin='<?=$design['skin']?>';
		if(selected_skin == '' || selected_skin == ori_skin){
			alert("현재 사용중인 스킨입니다     ");
			return false;
		}
		return confirm('\n사이트 전체의 디자인이 변경됩니다\n\n\''+selected_skin+'\' 스킨으로 변경하시겠습니까?              \n');
	}
	function skinComment(w, type, prefix){
		var prefix2 = (prefix) ? prefix+'#_' : '';
		var f = document.skinFrm;
		var fd = f['skin_comment['+prefix2+w+']'];
		var w1 = document.getElementById(prefix2+'cinput_'+w);
		var w2 = document.getElementById(prefix2+'cview_'+w);

		f.prefix.value = (prefix) ? prefix : '';
		if(type == 1){
			w2.style.display='none';
			w1.style.display='block';
			fd.focus();
		}else if(type == 2){
			w1.style.display='none';
			w2.style.display='block';
		}else if(type == 3){
			f.exec.value='skin_comment';
			f.skin_name.value=w;
			f.submit();

			$('.skin_comment_'+w).val(fd.value);
		}
	}
	function fileExec(f, exec, filename, skin_name){
		f.selected_skin.value=skin_name;
		if(exec == 'backup'){
			if(!confirm('\n\''+skin_name+'\' 스킨을 백업하시겠습니까?    ')) return;
		}else if(exec == 'bak_restore'){
			f.file_name.value=filename;
			if(!confirm('\n해당 파일로 스킨을 복구하시겠습니까?\n\n해당 스킨이 이미 존재할 경우에는 자동 백업이 이루어 집니다          \n')) return;
		}else if(exec == 'bak_delete'){
			f.file_name.value=filename;
			if(!confirm('\n\''+filename+'\' 파일을 삭제하시겠습니까?         \n\n')) return;
		}
		f.exec.value=exec;
		f.submit();
	}
	function skinConfig(skin_name){
		<?php if($_GET['type'] == 'mobile') { ?>
		skin_name += '&type=mobile';
		<?php } ?>
		skinconfig.open('skin_name='+skin_name);
	}
	function skinSelect(nm, edit){
        printLoading();

		if(!edit) edit = '';
		$.post('./index.php', {'body':'design@skin.exe', 'exec':'skin_select', 'skin':nm, 'prefix':edit, 'type':'<?=$type?>'}, function(r) {
			location.reload();
		});
	}

	/**
	 * @brief 스킨 복사하기 레이어를 출력합니다.
	 * @param skin_name [in] 원본 스킨명
	 */
	var skinDialog = null;
	function openSkinDialog(exec, skin_name) {
		setDimmed();
		skinDialog = new layerWindow('design@skin_'+exec+'_inc.exe&skinname='+skin_name+'&type=<?=$type?>');
		skinDialog.close = function() {
			this.pop.fadeOut('fast', function() {
				$(this).remove();
				removeDimmed();
			});
		}
		skinDialog.open();
	}

	var skinconfig = new layerWindow('design@skin_config_inc.exe');
</script>