<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품Q&A 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['product_qna_protect_name'] && !$cfg['qna_protect_name']) $cfg['qna_protect_name'] = $cfg['product_qna_protect_name'];
	if($cfg['product_qna_protect_id'] && !$cfg['qna_protect_id']) $cfg['qna_protect_id'] = $cfg['product_qna_protect_id'];
	if(!$cfg['product_qna_secret']) $cfg['product_qna_secret'] = "Y";
	if(!$cfg['product_qna_edit']) $cfg['product_qna_edit'] = "N";
	if(!$cfg['product_qna_del']) $cfg['product_qna_del'] = "N";
	if(!$cfg['product_qna_hitnum']) $cfg['product_qna_hitnum'] = "N";
	if(!$cfg['qna_protect_name']) $cfg['qna_protect_name'] = "N";
	if(!$cfg['qna_protect_name_strlen']) $cfg['qna_protect_name_strlen'] = "1";
	if(!$cfg['qna_protect_name_suffix']) $cfg['qna_protect_name_suffix'] = "**";
	if(!$cfg['qna_protect_id']) $cfg['qna_protect_id'] = "N";
	if(!$cfg['qna_protect_id_strlen']) $cfg['qna_protect_id_strlen'] = "3";
	if(!$cfg['qna_protect_id_suffix']) $cfg['qna_protect_id_suffix'] = "****";
	if(!$cfg['product_qna_row']) $cfg['product_qna_row'] = 20;
	if(!$cfg['product_qna_use_editor']) $cfg['product_qna_use_editor'] = 'N';
	if(!$cfg['product_qna_scallback']) $cfg['product_qna_scallback'] = 'N';
	if(!$cfg['product_qna_mcallback']) $cfg['product_qna_mcallback'] = 'N';
	if(!$cfg['product_qna_able_ext']) $cfg['product_qna_able_ext'] = 'jpg|jpeg|gif|png';

	$_date_type_array = explode('@', $cfg['date_type_qna']);
	$fsubject = $pdo->row("select value from $tbl[default] where code='qna_fsubject'");

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckQnaConfig(this)">
<input type="hidden" name="body" value="config@config.exe">
<input type="hidden" name="config_code" value="product_qna">
	<table class="tbl_row">
		<caption class="hidden">상품Q&A 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">문의 권한</th>
			<td>
				<input type="radio" id="r13" name="product_qna_auth" value="1" <?=checked($cfg['product_qna_auth'],1)?>> <label for="r13" class="p_cursor">모든 고객</label><br>
				<input type="radio" id="r14" name="product_qna_auth" value="2" <?=checked($cfg['product_qna_auth'],2)?>> <label for="r14" class="p_cursor">회원</label><br>
				<input type="radio" id="r15" name="product_qna_auth" value="3" <?=checked($cfg['product_qna_auth'],3)?>> <label for="r15" class="p_cursor">구매 고객</label>
			</td>
		</tr>
		<tr>
			<th scope="row">조회 수 표시</th>
			<td>
				<input type="radio" id="r30" name="product_qna_hitnum" value="N" <?=checked($cfg['product_qna_hitnum'],"N")?>> <label for="r30" class="p_cursor">사용안함</label> <span class="explain">(추천)</span><br>
				<input type="radio" id="r31" name="product_qna_hitnum" value="Y" <?=checked($cfg['product_qna_hitnum'],"Y")?>> <label for="r31" class="p_cursor">사용함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">비밀글 설정</th>
			<td>
				<input type="radio" id="r19" name="product_qna_secret" value="D" <?=checked($cfg['product_qna_secret'],"D")?>> <label for="r19" class="p_cursor">고객 선택</label><br>
				<input type="radio" id="r20" name="product_qna_secret" value="Y" <?=checked($cfg['product_qna_secret'],"Y")?>> <label for="r20" class="p_cursor">전체 비밀글</label><br>
				<input type="radio" id="r21" name="product_qna_secret" value="N" <?=checked($cfg['product_qna_secret'],"N")?>> <label for="r21" class="p_cursor">사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">한 페이지 글 수</th>
			<td>
				<input type="text" name="product_qna_row" size="5" maxlength="5" value="<?=$cfg['product_qna_row']?>" class="input" onkeyup="FilterNumOnly(this)"> 개
				<ul class="list_msg">
					<li>한페이지 100개 이내로 설정 가능합니다.</li>
					<li>관리자 공지는 게시물 수에 포함되지 않습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">제목 최소 글자 수</th>
			<td>
				<input type="text" name="product_qna_strlen" size="5" maxlength="5" value="<?=$cfg['product_qna_strlen']?>" class="input" onkeyup="FilterNumOnly(this)"> 자 이상
				<span class="explain">(0일 경우 제한 없음)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">고객 권한</th>
			<td>
				* 게시판 관리자로 로그인 시 수정/삭제 가능<br>
				* <span class="p_color2">수정</span> :
				<input type="radio" id="r22" name="product_qna_edit" value="Y" <?=checked($cfg['product_qna_edit'],"Y")?>> <label for="r22" class="p_cursor">가능</label>
				<input type="radio" id="r25" name="product_qna_edit" value="A" <?=checked($cfg['product_qna_edit'],"A")?>> <label for="r25" class="p_cursor">답변 전 가능</label>
				<input type="radio" id="r26" name="product_qna_edit" value="N" <?=checked($cfg['product_qna_edit'],"N")?>> <label for="r26" class="p_cursor">불가</label><br>
				* <span class="p_color2">삭제</span> :
				<input type="radio" id="r24" name="product_qna_del" value="Y" <?=checked($cfg['product_qna_del'],"Y")?>> <label for="r24" class="p_cursor">가능</label>
				<input type="radio" id="r28" name="product_qna_del" value="A" <?=checked($cfg['product_qna_del'],"A")?>> <label for="r28" class="p_cursor">답변 전 가능</label>
				<input type="radio" id="r27" name="product_qna_del" value="N" <?=checked($cfg['product_qna_del'],"N")?>> <label for="r27" class="p_cursor">불가</label><br>

			</td>
		</tr>
		<tr>
			<th scope="row">문의 분류</th>
			<td>
				<input type="text" name="product_qna_cate" value="<?=inputText($cfg['product_qna_cate'])?>" class="input" size="80">
				<p class="explain">쉼표<b>(,)</b>로 구분하세요 (예: 배송,입금,기타)</p>
			</td>
		</tr>
		<tr>
			<th scope="row">신규 글 표시</th>
			<td>
				<input type="text" name="product_qna_new_time" value="<?=$cfg['product_qna_new_time']?>" class="input " size="5" onkeyup="FilterNumOnly(this)"> 시간 <span class="explain">(미설정 시 작동하지 않음)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">신규게시글 작성 통보</th>
			<td>
				<div>
					* <span class="p_color2">문자알림 :</span>
					<label class="p_cursor"><input type="radio" name="product_qna_scallback" value="Y" <?=checked($cfg['product_qna_scallback'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="product_qna_scallback" value="N" <?=checked($cfg['product_qna_scallback'], 'N')?>> 사용안함</label>
					<span class="box_btn_s"><a href="?body=config@sms_config&sadmin=Y" target="_blank">설정</a></span>
				</div>
				<div>
					* <span class="p_color2">이메일알림 :</span>
					<label class="p_cursor"><input type="radio" name="product_qna_mcallback" value="Y" <?=checked($cfg['product_qna_mcallback'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="product_qna_mcallback" value="N" <?=checked($cfg['product_qna_mcallback'], 'N')?>> 사용안함</label>
					<span class="box_btn_s"><a href="?body=member@email_config" target="_blank">설정</a></span>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">날짜 형식</th>
			<td class="contentFrm">
				현재 설정 : <?=parseDateType($cfg['date_type_qna'])?>
				<input type="hidden" id="date_type_qna" name="date_type_qna" value="<?=$cfg['date_type_qna']?>">
				<div class="add_fld">
					<div class="fld_list">
						<select id="date_item" class="select_n" size="10">
							<?foreach($date_type_items as $key => $val) {?>
							<option value="<?=$key?>"><?=$val?></option>
							<?}?>
						</select>
					</div>
					<div class="add small">
						<span class="box_btn_s blue"><input type="button" value="추가 ▶" onclick="sel.addFromSelect(ditem, true);"></span><br><br>
						<span class="box_btn_s gray"><input type="button" value="제거 ◀" onclick="sel.remove()"></span>
					</div>
					<div class="add_list">
						<select id="date_select" class="select_n" size="10">
							<?foreach($_date_type_array as $val) {?>
							<option value="<?=$val?>"><?=$date_type_items[$val]?></option>
							<?}?>
						</select>
						<span class="box_btn_s blue"><input type="button" value="▲" onclick="sel.move(-1);"></span>
						<span class="box_btn_s blue"><input type="button" value="▼" onclick="sel.move(1);"></span>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">에디터</th>
			<td>
				<input type="radio" id="r60" name="product_qna_use_editor" value="N" <?=checked($cfg['product_qna_use_editor'],"N")?>> <label for="r60" class="p_cursor">사용안함</label><br>
				<input type="radio" id="r61" name="product_qna_use_editor" value="Y" <?=checked($cfg['product_qna_use_editor'],"Y")?>> <label for="r61" class="p_cursor">사용함</label>
			</td>
		</tr>
		<tr>
			<th rowspan="2">제목입력 제한</th>
			<td>
				<label class="p_cursor"><input type="radio" name="qna_fsubject" value="N" checked> 사용안함</label>
				<label class="p_cursor"><input type="radio" name="qna_fsubject" value="Y" <?=checked($cfg['qna_fsubject'], 'Y')?>> 사용함</label>
			</td>
		</tr>
		<tr>
			<td>
				<textarea name="fsubject" class="txta" rows="5" cols="80"><?=stripslashes($fsubject)?></textarea>
				<ul class="list_msg">
					<li>게시물 제목을 입력한 항목 중에서만 선택할 수 있습니다.</li>
					<li>제목은 1개이상 입력할 수 있으며, 각 제목 입력은 엔터로 구분해 주세요.</li>
					<li>
						설정한 내용을 적용 받으려면 <a href="?body=design@editor&type=&edit_pg=7%2F1">디자인관리>HTML 편집>페이지 편집 > 게시판정보 > 상품별 질문과 답변</a> 메뉴에서 글제목 입력 input 태그를 <span class="p_color2">{{$제한제목목록}}</span> 으로<br>
						<a href="?body=design@editor&type=&edit_pg=7%2F3">디자인관리>HTML 편집>페이지 편집 > 게시판정보 > 상품 질문과 답변 수정</a> 메뉴에서 글제목 입력 input 태그를 <span class="p_color2">{{$제한제목목록}}</span> 으로 대체해 주세요.
					</li>
					<li><span class="p_color2">{{$제한제목목록}}</span> 모듈에 제거한 input 태그를 그대로 붙여넣으시면 기능을 사용하지 않거나 '게시판 관리자' 로그인 시 수동으로 제목을 입력하실 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">업로드 가능 확장자</th>
			<td>
				<textarea name="product_qna_able_ext" class="txta" rows="5" cols="80"><?=inputText(str_replace('|', ',', $cfg['product_qna_able_ext']))?></textarea>
				<ul class="list_msg">
					<li>가능 확장자가 여러개인 경우 쉼표(,)로 구분하여 등록할 수 있습니다.</li>
					<li>이중 확장자는 보안을 위해 지원하지 않습니다. 예) aaa.bbb.jpg</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품문의 이동</th>
			<td>
				<label><input type="radio" name="use_trash_qna" value="Y" <?=checked($cfg['use_trash_qna'], 'Y')?>> 상품문의 휴지통으로 이동</label></label>
				<label><input type="radio" name="use_trash_qna" value="N" <?=checked($cfg['use_trash_qna'], 'N')?>> 즉시 영구 삭제(복구 불가)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품문의 보관 기간</th>
			<td>
				<?=selectArray(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'trash_qna_trcd', true, '삭제안함', $cfg['trash_qna_trcd'])?>
				일 후 영구삭제
				<ul class="list_msg">
					<li>영구삭제된 상품문의의 모든 데이터(이미지 포함)는 복구가 불가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">게시판 스팸차단</th>
			<td><a href="./?body=board@board_config">스팸 게시물 차단 옵션 설정 <img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	function ckQnaConfig(f) {
		FilterNumOnly(f.product_qna_strlen);
		FilterNumOnly(f.product_qna_new_time);

		var temp = '';
		var sel = document.getElementById('date_select');
		for(var x = 0; x < sel.options.length; x++) {
			if(temp) temp += '@';
			temp += sel.options[x].value;
		}
		document.getElementById('date_type_qna').value = temp;
	}

	var sel = new R2Select('date_select');
	var ditem = new R2Select('date_item');
</script>