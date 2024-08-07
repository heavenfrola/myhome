<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업스킨 편집
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_GET['no']);
	if($no) {
		$data =  get_info($tbl['popup_frame'], 'no', $no);
	}
	else {
		$data['html'] = 1;
	}

?>
<form name="popFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkFrm(this)" enctype="multipart/form-data">
	<input type="hidden" name="body" value="design@design_popup_frame_register.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ori_updir" value="<?=$data['updir'];//미리보기시사용?>">
	<div class="box_title first">
		<h2 class="title">팝업스킨 편집</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">팝업스킨 편집</caption>
		<colgroup>
			<col style="width:15%">
		<colgroup>
		<tr>
			<th scope="row">팝업스킨 이름</th>
			<td><input type="text" name="title" value="<?=$data['title']?>" class="input input_full"></td>
		</tr>
		<tr>
			<th scope="row">내용</th>
			<td>
			<ul class="list_msg">
				<li>팝업 내용 : <b>{내용}</b></li>
				<li>하루동안 창열지 않기 : <b>{하루창}</b></li>
				<li>창닫기 링크 : <b>{창닫기}</b></li>
				<li>업로드 이미지 치환(1~3) : <b>{이미지1}</b> → 1번이미지</li>
				<li>업로드 이미지경로 치환(1~3) : <b>{이미지경로1}</b> → 1번이미지 경로</li>
				<!-- <img src="<?=$engine_url?>/_manage/image/design/popup_info.gif" vspace="5" width="414" height="161"></li> -->
				<li class="p_color2">{내용} 을 삽입하신 자리에는 팝업관리에서 등록하신 팝업내용으로 치환됩니다.</li>
			</ul>
			<textarea name="content" rows="15" class="txta" style="height:200px;"><?=stripslashes($data['content'])?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">HTML 예제</th>
			<td>
				<textarea name="exam" rows="15" class="txta" style="height:200px; background:#eee;" readonly><div style="width:100%; margin:0 auto; border:0; background:#fff;" align="center">
  <div>{내용}</div>
  <div style="text-align:right;"><a href="{하루창}">하루 동안 열지 않기</a> | <a href="{창닫기}">Close</a></div>
</div></textarea>
			</td>
		</tr>
		<?php
			for($ii=1; $ii<=3; $ii++){
		?>
		<input type="hidden" name="ori_upfile<?=$ii?>" value="<?=$data["upfile".$ii]?>">
		<tr>
			<th scope="row">이미지 <?=$ii?> <?=($ii == 1) ? "(배경)" : "";?></th>
			<td>
				<input type="file" name="upfile<?=$ii?>" class="input input_full">
				<span class="box_btn_s"><a href="javascript:insertImageName('<?=$ii?>')">내용에삽입하기</a></span>
				<?=delImgStr($data,$ii)?>
			</td>
		</tr>
		<?php
			}
		?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="deletePopup(document.popFrm);"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="location.href='./?body=design@design_popup_frame';"></span>
	</div>
</form>

<script type="text/javascript">
	function insertImageName(ii){
		<?php
			for($ii=1; $ii<=3; $ii++){
				if(!($data['upfile'.$ii] && is_file($root_dir."/".$data['updir']."/".$data['upfile'.$ii]))){
		?>
			if(document.popFrm['upfile'+ii].value == '' && <?=$ii?> == ii){
			alert('첨부하실 파일을 확인해주시기 바랍니다');
			return;
			}
		<?php
				}
			}
		?>
		var str = '{이미지'+ii+'}';
		var f = document.popFrm;
		var content = f.content;
		if(window.getSelection) {
			var s = content.value.substr(0, content.selectionStart);
			var e = content.value.substr(content.selectionEnd);
			content.value = s+'{이미지'+ii+'}'+e;
		} else {
			var _selection = document.selection;
			content.focus();
			var _range=(_selection.type) ? _selection.createRange() : _selection;
			_range.duplicate().text = '{이미지'+ii+'}'
		}
	}

	function checkFrm(f){
		f.target=hid_frame;
		f.body.value='design@design_popup_frame_register.exe';
		if (!checkBlank(f.title,'팝업틀 이름을 입력해주세요.')) return false;
		if (!f.content.value){
			alert('내용을 입력하세요');
			return false;
		}
	}

	function deletePopup(f){
		if (confirm('삭제하시겠습니까?'))
		{
			f.body.value='design@design_popup_frame_register.exe';
			f.exec.value='delete';
			f.submit();
		}
	}

</script>