<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  제공 코드 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

	versionChk("V3");

?>
<div class="box_title first">
	<h2 class="title">제공 코드 편집</h2>
</div>
<div class="box_middle left">
	<p style="padding-bottom:5px;"><?=editSkinNotice()?></p>
	<select name="edit_pg_select" onchange="edit_pg=this.value;">
		<option value="">=========== 페이지 선택 ===========</option>
		<?php
			$_edit_pg="";
			foreach($_edit_list as $key=>$val){
				foreach($_edit_list[$key] as $key2=>$val2){
					if($key == "게시판정보"){
						if(@preg_match("/^board_index/", $key2)) continue;
					}
					echo "<option value=\"$key2\">".$val2."</option>";
				}
			}
		?>
	</select>
</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs square">
		<li onclick="getCodeList('edit_pg', '', this);" id="c_edit_pg">페이지 코드</li>
		<li onclick="getCodeList('common', '', this);" id="c_common" class="selected">공통 코드</li>
		<li onclick="getCodeList('user_code', '', this);" id="c_user_code">사용자 코드</li>
		<li onclick="getCodeList('page_link', '', this);" id="c_page_link">페이지 링크</li>
	</ul>
</div>
<div class="context" style="margin:0px; padding:0px; height:0px;"></div>
<div id="code_list"></div>
<form name="popFrm" action="./pop.php" method="get">
	<input type="hidden" name="body" value="design@editor.frm">
	<input type="hidden" name="design_edit_key">
	<input type="hidden" name="design_edit_code">
	<input type="hidden" name="type" value="<?=$_GET['type']?>">
</form>

<script type="text/javascript">
	edt_mode='<?=$_edt_mode?>';
	edit_pg='<?=$_edit_pg?>';
	design_edit_key='<?=$design_edit_key?>';
	design_edit_code='<?=$design_edit_code?>';
	skinZoom='';
	popup_edit='';
	function printData(){
		result=req.responseText;
		var mdv=document.getElementById('code_list');
		mdv.innerHTML=result;
	}
	c_tmp='c_common';
	function getCodeList(code_key, txt, w){
		if(!code_key) code_key='';
		if(!txt) txt='';
		if(code_key == 'edit_pg'){
			if(edit_pg == ''){
				alert('페이지를 선택하여 주시기 바랍니다');
				window.edit_pg_select.focus();
				return;
			}
		}
		$.get('./?body=design@editor_code.exe&code_page=1&code_key='+code_key+'&txt='+txt+'&_edt_mode='+edt_mode+'&_edit_pg='+edit_pg+'&design_edit_key='+design_edit_key+'&design_edit_code='+design_edit_code+'&type=<?=$_GET['type']?>', function(r){
			if(r){
				tmp=document.getElementById(c_tmp);
				tmp.className='';

				$('#c_'+code_key).addClass('selected');
				if(w){
					c_tmp=w.id;
				}
				$('#code_list').html(r);
			}
		});
	}
	function editCode(key, code){
		f=document.popFrm;
		f.design_edit_key.value=key;
		f.design_edit_code.value=code;
		var viewId = 'codePop';
		if(getCookie('def_dmode') != '0') viewId+=code;

		var a = window.open('about:blank',viewId,'top=10,left=10,width=950,status=no,toolbars=no,scrollbars=yes,height=700');
		if(a) a.focus();
		f.target=viewId;
		f.submit();
	}
	function delCode(code) {

		if(confirm('삭제 후 복구가 불가능합니다.\t\n삭제하시겠습니까?')) {

			frm=document.getElementsByName(hid_frame);
			frm[0].src='./?body=design@editor.exe&del_code='+code+'&exec=delete&type=<?=$_GET['type']?>';
		}
	}
	function userCode(w){
		if(!w) w='';
		var viewId='userCode';
		if(getCookie('def_dmode')!='0') viewId+=w;
		url='./pop.php?body=design@editor_user.frm&type=<?=$_GET['type']?>&user_code='+w;
		window.open(url,viewId,'top=10,left=10,width=850,status=no,toolbars=no,scrollbars=yes,height=700');
	}
	window.onload=function (){
		<?php if($default_code) { ?>
		getCodeList('<?=$default_code?>', '', c_<?=$default_code?>);
		<?php } else { ?>
		getCodeList('common');
		<?php } ?>
	}

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});
</script>
<?php
	designValUnset();
?>