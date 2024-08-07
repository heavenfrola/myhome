<form id="cfgfrm" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">

	<div class="box_middle3 left">
		<p class="explain">
			<i class="icon_info"></i>
			고급설정 미사용 시 일반설정 내용이 적용됩니다.
		</p>
	</div>

	<table class="tbl_row">
		<caption class="hidden">SEO 일반 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row" rowspan="3">웹브라우저 타이틀</th>
			<td>
				<textarea name="br_title" class="txta" cols="115" rows="5"><?=stripslashes($cfg['br_title'])?></textarea>
				<p class="explain">웹브라우저(인터넷 익스플로러) 상단에 나타나는 문구입니다</p>
			</td>
		</tr>
		<tr>
			<td>
				<label class="p_cursor"><input type="radio" name="br_title_prd" value=""  <?=checked($cfg['br_title_prd'], '')?>> 상품 상세페이지에서 기본 타이틀 사용</label><br>
				<label class="p_cursor"><input type="radio" name="br_title_prd" value="1" <?=checked($cfg['br_title_prd'], '1')?>> 상품 상세페이지에서 상품명을 브라우저 타이틀로 사용</label><br>
				└ <label class="p_cursor"><input type="checkbox" name="br_title_cate" value="Y" <?=checked($cfg['br_title_cate'], 'Y')?>> 현재 카테고리 출력</label><br>
				<label class="p_cursor"><input type="radio" name="br_title_prd" value="2" <?=checked($cfg['br_title_prd'], '2')?>> 상품 상세페이지에서 검색 키워드를 브라우저 타이틀로 사용</label>
			</td>
		</tr>
		<tr>
			<td>
				<label class="p_cursor"><input type="radio" name="br_title_board" value=""  <?=checked($cfg['br_title_board'], '')?>> 게시판/후기 글 보기에서 기본 타이틀 사용</label><br>
				<label class="p_cursor"><input type="radio" name="br_title_board" value="1" <?=checked($cfg['br_title_board'], '1')?>> 게시판/후기 글 보기에서 글 제목을 타이틀로 사용</label>
			</td>
		</tr>
		<tr>
			<th scope="row">META 검색 키워드</th>
			<td>
				<textarea name="meta_key" class="txta" cols="115" rows="5"><?=stripslashes($cfg['meta_key'])?></textarea>
				<div class="explain">HTML 상단 소스  <u>&lt;meta name="keywords" content="" &gt;</u> 부분 (메타방식 검색엔진에서 참조하는 부분입니다)</div>
			</td>
		</tr>
		<tr>
			<th scope="row">META 검색 설명</th>
			<td>
				<textarea name="meta_des" class="txta" cols="115" rows="5"><?=stripslashes($cfg['meta_des'])?></textarea>
				<div class="explain">HTML 상단 소스  <u>&lt;meta name="description" content="" &gt;</u> 부분</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function disabledDTD(ckbtn) {
		var f = document.getElementById('cfgfrm');
		if(!ckbtn || ckbtn.checked == true) {
			f.frontDTD.value = ' ';
			f.frontDTD.style.backgroundColor = '#f2f2f2';
			f.frontDTD.readOnly = true;
			f.DTDuse.checked = true;
		} else {
			f.frontDTD.value = '';
			f.frontDTD.style.backgroundColor = '';
			f.frontDTD.readOnly = false;
		}
	}

	<?php if ($cfg['frontDTD'] == ' ') { ?>
	disabledDTD();
	<?php } ?>

	function removeDefault(code) {
		if(confirm('삭제시 복구가 불가능합니다.\n선택하신 태그를 삭제하겠습니까?')) {
			$.post('./index.php', {'body':'design@title_meta.exe', 'exec':'removeHead', 'code':code}, function(r) {
				$('.'+code).remove();
			});
		}
	}
</script>