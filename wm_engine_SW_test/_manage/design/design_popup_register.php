<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업 등록/수정
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	//팝업테이블 start_date,finish_date 필드 수정
	if($cfg['update_popup_time'] != 'Y') {
		$pdo->query("alter table {$tbl['popup']} modify start_date datetime not null");
		$pdo->query("alter table {$tbl['popup']} modify finish_date datetime not null");
		$pdo->query("insert into {$tbl['config']} (name, value) values ('update_popup_time', 'Y')");
	}

	$no = numberOnly($_GET['no']);
	if($no > 0) {
		$data = get_info($tbl['popup'], 'no', $no);
		$data = array_map('stripslashes', $data);
		$begin = explode(' ', date('Y-m-d H i', strtotime($data['start_date'])));
		if($data['finish_date'] != '0000-00-00 00:00:00' && $data['finish_date'] != '') $finish = explode(' ', date('Y-m-d H i', strtotime($data['finish_date'])));
		if(empty($finish) == true) {
			$use_date = 'N';
		}
	}
	else {
		$data['html']=3;
		$data['use']="Y";
		$dateSH=0;
		$dateFH=23;
	}

	if(!$data['layer']) {
		$data['layer']="N";
	}

	// 팝업 프레임
	$pres = $pdo->iterator("select * from {$tbl['popup_frame']} order by no desc");

	// 팝업 노출 페이지

	$page = explode('@', trim($data['page'], '@'));
	$page_detail = explode('@', trim($data['page_detail'], '@'));
	if(trim($data['page'], '@') == '') $page = array('main');

    // 에디터 파일
    $editor_file = new EditorFile();
    $editor_file->setId('popup', $no);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form name="popFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkFrm(this)" enctype="multipart/form-data">
	<input type="hidden" name="body" value="design@design_popup_register.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="type">
	<input type="hidden" name="ori_updir" value="<?=$data['updir']?>">
	<input type="hidden" name="start_date"  value="" />
	<input type="hidden" name="finish_date" value="" />
	<input type="hidden" name="page_detail" value="<?=$data['page_detail']?>">
	<input type="hidden" name="html" value="<?=$data['html']?>">
	<input type="hidden" name="unique" value="<?=$now?>">
    <input type="hidden" name="editor_code" value="<?=$editor_file->getId()?>">

	<div class="box_title first">
		<h2 class="title">팝업 관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">팝업 관리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">팝업 제목</th>
			<td>
				<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full">
			</td>
		</tr>
		<tr>
			<th scope="row">노출 영역 선택</th>
			<td>
				<label class="p_cursor"><input type="radio" name="device" value="" <?=checked($data['device'], '')?>> PC 쇼핑몰</label>
				<label class="p_cursor"><input type="radio" name="device" value="mobile" <?=checked($data['device'], 'mobile')?>> 모바일 쇼핑몰</label>
			</td>
		</tr>
		<tr>
			<th scope="row">사용 여부</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="use" value="Y" <?=checked($data['use'],"Y")?>> 사용</label>
			</td>
		</tr>
		<tr>
			<th scope="row">기간</th>
			<td>
				<p style="margin-bottom:5px;">
					<label><input type="checkbox" name="use_date" value="N" <?=checked($use_date, 'N')?> onclick="useDate();"> 무제한</label>
				</p>
				<input type="text" name="start_date_day" value="<?=$begin[0]?>" size="10" readonly class="input datepicker">
				<?=dateSelectBox(0,23,"start_date_h",$begin[1])?> 시
                <?=dateSelectBox(0,59,"start_date_m",$begin[2])?> 분 ~
				<input type="text" name="finish_date_day" value="<?=$finish[0]?>" size="10" readonly class="input datepicker">
                <?=dateSelectBox(0,23,"finish_date_h",$finish[1])?> 시
                <?=dateSelectBox(0,59,"finish_date_m",$finish[2])?> 분
			</td>
		</tr>
		<tr>
			<th scope="row">팝업 노출 페이지</th>
			<td>
				<label><input type="checkbox" name="page[]" value="main" <?=checked(in_array('main', $page), true)?>> 메인페이지</label>
				<label><input type="checkbox" name="page[]" value="list" <?=checked(in_array('list', $page), true)?>> 상품목록</label>
				<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(1);"></span>
				<label><input type="checkbox" name="page[]" value="detail" <?=checked(in_array('detail', $page), true)?>> 상품상세</label>
				<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(2);"></span>
				<label><input type="checkbox" name="page[]" value="intro" <?=checked(in_array('intro', $page), true)?>> 인트로페이지</label>
			</td>
		</tr>
		<tr>
			<th scope="row">팝업스킨 선택</th>
			<td>
				<ul>
					<?php foreach ($pres as $pdata) {?>
					<li><label><input type="radio" name="frame" value="<?=$pdata['no']?>" <?=checked($pdata['no'], $data['frame'])?>> <?=$pdata['title']?></label></li>
					<?php } ?>
				</ul>
				<ul class="list_msg">
					<li>레이어형태의 팝업을 사용하실 경우 팝업스킨 배경에 색상을 넣지 않으면 글자가 잘 나타나지 않을 수 있습니다</li>
				</ul>
			</td>
		</tr>
		<tr class="pcOnly">
			<th scope="row">팝업 형태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="layer" value="N" <?=checked($data['layer'],'N')?>> 팝업 (새창)</label>
				<label class="p_cursor"><input type="radio" name="layer" value="Y" <?=checked($data['layer'],'Y')?>> 레이어 (XP SP2 팝업차단과 무관하게 나타납니다)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">창위치</th>
			<td>
				<label class="p_cursor">가로 : <input type="text" name="posx" value="<?=$data['x']?>" class="input" size="3"> px</label> /
				<label class="p_cursor">세로 : <input type="text" name="posy" value="<?=$data['y']?>" class="input" size="3"> px</label><br>
				<span class="explain">(새창 사용시 브라우저 창의 좌측 상단 끝이 0,0 이며, 레이어 사용시 웹페이지 내용부터 계산합니다)</span>
			</td>
		</tr>
		<tr class="pcOnly">
			<th scope="row">창크기</th>
			<td>
				<label class="p_cursor">넓이 : <input type="text" name="w" value="<?=$data['w']?>" class="input" size="3"> px</label> /
				<label class="p_cursor">높이 : <input type="text" name="h" value="<?=$data['h']?>" class="input" size="3"> px</label><br>
				<span class="explain">(운영체제에 따라 실제 출력 부분 크기다 다를 수 있으며, 레이어 사용시 차이가 생길 수 있습니다)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">내용</th>
			<td>
				<?php if ($data['html'] != '3') { ?>
				<p>업로드 이미지 치환(1~3) : <b>{이미지1}</b> → 1번이미지</p>
				<?php } ?>
				<textarea name="content" id="content" rows="10" class="txta" style="height:500px;"><?=stripslashes($data['content'])?></textarea>
			</td>
		</tr>
		<?php if ($data['html']!='3') { for ($ii=1; $ii<=3; $ii++) { ?>
		<tr <?if($ii > 1){?>class="pcOnly"<?}?>>
			<th scope="row">이미지 <?=$ii?></th>
			<td>
				<input type="hidden" name="ori_upfile<?=$ii?>" value="<?=$data["upfile".$ii]?>">
				<input type="file" name="upfile<?=$ii?>" class="input input_full">
				<span class="box_btn_s"><a href="javascript:insertImageName('<?=$ii?>')">내용에삽입하기</a></span>
				<?=delImgStr($data,$ii)?>
			</td>
		</tr>
		<?php }} ?>
		<tr>
			<th scope="row">미리보기</th>
			<td>
				<span class="box_btn_s"><input type="button" value="미리보기" onclick="popupPreview(1);"></span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?php if ($no > 0) { ?>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="deletePopup(document.popFrm);"></span>
		<?php } ?>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='./?body=design@design_popup';"></span>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript">
	var f = document.popFrm

	function insertImageName(ii){
		var content = document.getElementById('content');
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
		f.body.value='design@design_popup_register.exe';
		if(!checkBlank(f.start_date_day,'시작일을 입력해주세요.')) return false;
		f.start_date.value=f.start_date_day.value+" "+f.start_date_h.value+':'+f.start_date_m.value+':00'
		if(f.use_date.checked == false) {
			if(!checkBlank(f.finish_date_day,'종료일을 입력해주세요.')) return false;
			f.finish_date.value=f.finish_date_day.value+" "+f.finish_date_h.value+':'+f.finish_date_m.value+':59';

			if(f.start_date.value > f.finish_date.value) {
				alert('시작일은 종료일 이전이어야합니다.');
				return false;
			}
		}

		if ($(f.frame).filter(':checked').length == 0){
			window.alert('팝업 스킨을 선택하세요');
			return false;
		}
		if(oEditors && oEditors.getById) {
			oEditors.getById['content'].exec("UPDATE_CONTENTS_FIELD", []);
		}
		if (!f.content.value){
			alert('내용을 입력하세요');
			return false;
		}
	}

	function deletePopup(f){
		if (confirm('삭제하시겠습니까?'))
		{
			f.exec.value='delete';
			f.submit();
		}
	}

	function popupPreview(type){
		var w = window.open('', 'popup_preview', 'width='+f.w.value+',height='+f.h.value+',toolbar=no,menubar=no,location=no,scrollbars=no,status=no,resizable=no');

		f.method = 'POST';
		f.type.value = type;
		f.target = 'popup_preview';
		f.body.value = 'design@design_popup_preview.frm';

		if(oEditors && oEditors.getById) {
			oEditors.getById['content'].exec("UPDATE_CONTENTS_FIELD", []);
		}

		f.submit();
	}

	function useDate() {
		if(f.use_date.checked == true) {
			f.finish_date_day.disabled = true;
			f.finish_date_h.disabled = true;
			f.finish_date_day.style.background = '#f2f2f2';
			f.finish_date_h.style.background = '#f2f2f2';
		} else {
			f.finish_date_day.disabled = false;
			f.finish_date_h.disabled = false;
			f.finish_date_day.style.background = '';
			f.finish_date_h.style.background = '';
		}
	}

	// 팝업 노출 페이지 설정
	var targetSelector = new layerWindow();
	function selectTarget(val) {
		switch(val) {
			case 1 :
				targetSelector.body  = 'design@design_popup_cate_inc.exe'
			break;
			case 2 :
				targetSelector.body  = 'design@design_popup_product_inc.exe'
			break;
		}
		targetSelector.open();
	}
	$(':radio[name=device]').click(function() {
		if(this.value == 'mobile') {
			$('.pcOnly').hide();
		} else {
			$('.pcOnly').show();
		}
	});

	// 선택된 상품 정보 새로고침
	function reloadTargetPrd() {
		$.get('?body=design@design_popup_product_inc.exe&exec=selected&datas='+f.page_detail.value, function(result) {
			$('#selectedPrds').find('ul').html(result);
		});
	}

	// 상품 선택
	function setTargetPrd(prefix, pno) {
		if(f.page_detail.value == '') f.page_detail.value = '@';
		if(f.page_detail.value.indexOf('@'+prefix+pno+'@') < 0) {
			f.page_detail.value += prefix+pno+'@';
			reloadTargetPrd();
		}
	}

	// 상품 선택 삭제
	function resetTargetPrd(prefix, pno) {
		f.page_detail.value = f.page_detail.value.replace('@'+prefix+pno, '');
		if(prefix == 'prd')	reloadTargetPrd();
		else reloadTargetCate();
	}

	// 선택된 카테고리 정보 새로고침
	function reloadTargetCate() {
		$.get('?body=design@design_popup_cate_inc.exe&exec=selected&datas='+f.page_detail.value, function(result) {
			$('#selectedCates').html(result);
		});
	}

	// 카테고리 선택
	function setTargetCate(f) {
		var cate = 0;
		$('.cates').each(function(idx) {
			if(idx > 0 && this.length > 0) {
				if(this.selectedIndex > -1) {
					cate = $(this).val();
				}
			}
		});
		if(cate > 0) {
			setTargetPrd('cate', cate);
			reloadTargetCate();
		} else {
			window.alert('카테고리를 선택해주세요.');
		}
	}

	<?php if ($data['html'] == '3') { ?>
    var editor_code = '<?=$editor_file->getId()?>';
    seCall('content', editor_code, 'popup');
	<?php } ?>

	$(function() {
		$(':checked[name=device]').click();
		useDate();
	});
</script>