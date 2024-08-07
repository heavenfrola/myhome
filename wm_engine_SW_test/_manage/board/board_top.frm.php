<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 상단 디자인
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_REQUEST['no']);

	unset($data);
	if($no) {
		$data=get_info('mari_config',"no",$no);
		checkBlank($data[no],"게시판 정보를 입력해주세요.");
	}

?>
<script language="javasscript" type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form name="cateFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return saveEdit(this);">
	<input type="hidden" name="body" value="board@board_top.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">게시판명</th>
			<td><?=$data[title]?></td>
		</tr>
		<tr>
			<th scope="row">DB</th>
			<td><?=$data[db]?></td>
		</tr>
		<tr>
			<th scope="row">속성</th>
			<td><label class="p_cursor"><input type="checkbox" name="top_use" value="Y" <?=checked($data['top_use'],"Y")?>> 출력</label></td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">상단 출력 디자인</h2>
	</div>
	<div class="box_bottom top_line">
		<textarea id="content2" name="content2" class="txta" style="height: 300px;"><?=stripslashes($data['top_content'])?></textarea>
	</div>
	<div class="box_title">
		<h2 class="title">업로드 이미지</h2>
	</div>
	<div class="box_bottom top_line">
		<iframe name="imgFr" src="./?body=board@board_file.frm" width="100%" height="200" scrolling="yes" frameborder="0"></iframe>
	</div>
	<div id="cateDelDiv" class="box_full center" style="display:none">
		<p><b>현재 상단 디자인을 삭제하시겠습니까?</b></p>
		<div id="okLayer1">
			<span class="box_btn gray"><input type="button" value="삭제" onclick="realDelCate();"></span>
			<span class="box_btn gray"><input type="button" value="취소" onclick="delCate();"></span>
		</div>
		<div id="okLayer2" style="display:none">
			<span class="p_color"><b>삭제중...</b></span>
			<input type="text" name="del_prds" value="계산중" class="none_input2" size="5">
			<input type="text" name="all_prds" value="계산중" class="none_input2" size="5">
		</div>
	</div>
	<div class="pop_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="delCate();"></span>
		<span class="box_btn gray"><input type="button" value="창닫기" onclick="wclose();"></span>
	</div>
</form>

<script language="JavaScript">
	var cf=document.cateFrm;

	var imgFr=1;
	var editor = new R2Na('content2');
	window.onload=function() {
		selfResize();
	}

	function delCate(){
		layTgl(document.getElementById('cateDelDiv'));
		selfResize();
	}

	function realDelCate(){
		f=document.cateFrm;
		f.exec.value="delete";
		layTgl(document.getElementById('okLayer1'));
		layTgl(document.getElementById('okLayer2'));
		selfResize();
		f.submit();
	}

	function saveEdit(f) {
		if(oEditors.getById) oEditors.getById['content2'].exec("UPDATE_CONTENTS_FIELD", []);
	}
</script>