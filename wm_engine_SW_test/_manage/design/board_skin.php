<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 스킨 관리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";

	versionChk("V3");

	$_bskin=array();
	$_bskin_src=$root_dir."/board/_skin";
	$_odir=opendir($_bskin_src);
	while($_rdir=readdir($_odir)){
		if($_rdir == "." || $_rdir == ".." || !is_dir($_bskin_src."/".$_rdir)) continue;
		$_bskin[]=$_rdir;
	}
	sort($_bskin);

?>
<div class="box_title first">
	<h2 class="title">게시판 스킨 관리</h2>
</div>
<div class="box_middle left">
	<p class="p_color2">잘못된 코드 삽입으로 발생한 문제에 대해서는 책임지지 않습니다.</p>
</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs square">
		<li onclick="location.href='./?body=design@board';">스킨 목록</li>
		<li class="selected">스킨 관리</li>
	</ul>
</div>
<form name="editFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="return false;" enctype="multipart/form-data" class="register">
<input type="hidden" name="body" value="design@design_config.exe">
<input type="hidden" name="exec" value="board_skin">
<input type="hidden" name="bskin_name" value="">
<input type="hidden" name="mode" value="">
	<div class="box_middle2 left">
		<?php
			foreach ($_bskin as $key=>$val) {
		?>
		<label style="text-decoration:none; width:100%;" onmouseover="this.style.color='#cc0000';" onmouseout="this.style.color='';" class="p_cursor">
			<input type="radio" name="board_skin_select" value="<?=$val?>" onclick="this.form.bskin_name.value=this.value;">
			<span id="bskin_<?=$val?>" style="display:inline;"><?=$val?></span>
			<span id="bskin_edt_<?=$val?>" style="visibility:hidden; display:inline;">
				<input type="text" name="bskin_text[<?=$val?>]" class="input" value="<?=$val?>" style="border:1px solid #333300; height:16px; line-height:13px;" onclick="this.select();">
				<span class="box_btn_s"><input type="button" value="수정" onClick="bskinEdit('modify');"></span>
				<span class="box_btn_s"><input type="button" value="취소" onClick="bskinS('1');"></span>
			</span>
		</label>
		<br>
		<?php } ?>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="button" value="복사하기" onclick="bskinEdit('copy');"></span>
		<span class="box_btn blue"><input type="button" value="수정하기" onclick="bskinEdit('edit');"></span>
		<span class="box_btn blue"><input type="button" value="백업하기" onclick="bskinEdit('backup');"></span>
		<span class="box_btn gray"><input type="button" value="삭제하기" onclick="bskinEdit('delete');"></span>
	</div>
</form>

<script type="text/javascript">
	f=document.editFrm;
	function bskinDisabled(mode){
		len=f.board_skin_select.length;
		if(len){
			for(ii=0; ii<len; ii++){
				f.board_skin_select[ii].disabled=mode;
			}
		}
	}
	function bskinEdit(mode){
		skin_name=f.bskin_name.value;
		namefd=f['bskin_text['+skin_name+']'];
        if(skin_name == ''){
            alert('실행할 스킨을 선택하세요');
            return;
        }
        if(mode == 'edit'){
            bskinS();
            namefd.select();
            return;
        }
        if(mode == 'delete'){
            if(!confirm('\''+skin_name+'\' 스킨을 삭제하시겠습니까?')) return;
        }
		f.mode.value=mode;
		f.submit();
	}
	function bskinS(mode){
		if(!mode) mode='';
		obj1=document.getElementById('bskin_'+skin_name);
		obj2=document.getElementById('bskin_edt_'+skin_name);
		if(mode == ''){
			obj1.style.display='none';
			obj2.style.visibility='visible';
			bskinDisabled(true);
		}else{
			obj2.style.visibility='hidden';
			obj1.style.display='block';
			obj1.style.display='inline';
			bskinDisabled(false);
		}
	}
</script>