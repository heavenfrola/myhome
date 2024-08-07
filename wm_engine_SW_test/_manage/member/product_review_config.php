<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['product_review_protect_id'] && !$cfg['review_protect_id']) $cfg['review_protect_id'] = $cfg['product_review_protect_id'];
	if($cfg['product_review_protect_name'] && !$cfg['review_protect_name']) $cfg['review_protect_name'] = $cfg['product_review_protect_name'];
	if(!$cfg['product_review_edit']) $cfg['product_review_edit']=  "N";
	if(!$cfg['product_review_del']) $cfg['product_review_del'] = "N";
	if(!$cfg['product_review_hitnum']) $cfg['product_review_hitnum'] = "N";
	if(!$cfg['review_protect_name']) $cfg['review_protect_name'] = "N";
	if(!$cfg['review_protect_name_strlen']) $cfg['review_protect_name_strlen'] = "1";
	if(!$cfg['review_protect_name_suffix']) $cfg['review_protect_name_suffix'] = "**";
	if(!$cfg['review_protect_id']) $cfg['review_protect_id'] = "N";
	if(!$cfg['review_protect_id_strlen']) $cfg['review_protect_id_strlen'] = "3";
	if(!$cfg['review_protect_id_suffix']) $cfg['review_protect_id_suffix'] = "****";
	if(!$cfg['product_review_comment']) $cfg['product_review_comment'] = "2";
	if(!$cfg['product_review_row']) $cfg['product_review_row'] = 20;
	if(!$cfg['product_review_use_editor']) $cfg['product_review_use_editor'] = 'N';
	if(!$cfg['product_review_scallback']) $cfg['product_review_scallback'] = 'N';
	if(!$cfg['product_review_mcallback']) $cfg['product_review_mcallback'] = 'N';
	if(!$cfg['product_review_con_strlen']) $cfg['product_review_con_strlen'] = 0;

	$_date_type_array = explode('@', $cfg['date_type_review']);
	$fsubject = $pdo->row("select value from $tbl[default] where code='review_fsubject'");

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckReviewConfig(this)">
<input type="hidden" name="body" value="config@config.exe">
<input type="hidden" name="config_code" value="product_review">
	<div class="box_title first">
		<h2 class="title">상품후기 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품후기 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">작성 권한</th>
			<td>
				<input type="radio" id="r1" name="product_review_auth" value="1" <?=checked($cfg['product_review_auth'],1)?>> <label for="r1" class="p_cursor">모든 고객</label><br>
				<input type="radio" id="r2" name="product_review_auth" value="2" <?=checked($cfg['product_review_auth'],2)?>> <label for="r2" class="p_cursor">회원</label><br>
				<input type="radio" id="r3" name="product_review_auth" value="3" <?=checked($cfg['product_review_auth'],3)?>> <label for="r3" class="p_cursor">구매 고객</label> <span class="explain"><i class="icon_info"></i> 상품 구매 경력이 있는 회원만 작성할 수 있습니다.</span><br>
				<label><input type="radio" name="product_review_auth" value="4" <?=checked($cfg['product_review_auth'],4)?>> 구매 횟수</label> <span class="explain"><i class="icon_info"></i> 주문서 내 상품의 옵션 기준으로 주문 횟수만큼 작성할 수 있습니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">조회 수 표시</th>
			<td>
				<input type="radio" id="r28" name="product_review_hitnum" value="N" <?=checked($cfg['product_review_hitnum'],"N")?>> <label for="r28" class="p_cursor">사용안함</label> <span class="explain">(추천)</span><br>
				<input type="radio" id="r29" name="product_review_hitnum" value="Y" <?=checked($cfg['product_review_hitnum'],"Y")?>> <label for="r29" class="p_cursor">사용함</label>
			</td>
		</tr>
		<tr class="review_many">
			<th scope="row">중복 허용</th>
			<td>
				<input type="radio" id="r7" name="product_review_many" value="1" <?=checked($cfg['product_review_many'],1)?>> <label for="r7" class="p_cursor">한 회원이 한 상품에 하나만 등록</label><br>
				<input type="radio" id="r8" name="product_review_many" value="2" <?=checked($cfg['product_review_many'],2)?>> <label for="r8" class="p_cursor">한 회원이 한 상품에 여러개 등록 가능</label>
			</td>
		</tr>
		<tr>
			<th scope="row">최고 점수</th>
			<td>
				<input type="radio" id="r9" name="product_review_max" value="5" <?=checked($cfg['product_review_max'],5)?>> <label for="r9" class="p_cursor">5</label><br>
				<input type="radio" id="r10" name="product_review_max" value="10" <?=checked($cfg['product_review_max'],10)?> disabled> <label for="r10" class="p_cursor">10</label>
			</td>
		</tr>
		<tr>
			<th scope="row">한 페이지 글 수</th>
			<td>
				<input type="text" name="product_review_row" size="5" maxlength="5" value="<?=$cfg['product_review_row']?>" class="input" onkeyup="FilterNumOnly(this)"> 개
				<ul class="list_msg">
					<li>한페이지 100개 이내로 설정 가능합니다.</li>
					<li>관리자 공지는 게시물 수에 포함되지 않습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">제목 최소 글자 수</th>
			<td>
				<input type="text" name="product_review_strlen" size="5" maxlength="5" value="<?=$cfg['product_review_strlen']?>" class="input"> 자 이상
				<span class="explain">(0일 경우 제한 없음)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">내용 최소 글자 수</th>
			<td>
				<input type="text" name="product_review_con_strlen" size="5" maxlength="5" value="<?=$cfg['product_review_con_strlen']?>" class="input"> 자 이상
				<span class="explain">(0일 경우 제한 없음)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">댓글 정렬</th>
			<td>
				<input type="radio" id="r34" name="product_review_com_sort" value="1" <?=checked($cfg['product_review_com_sort'],1).checked($cfg['product_review_com_sort'],false)?>> <label for="r34" class="p_cursor">최신글이 위로</label><br>
				<input type="radio" id="r35" name="product_review_com_sort" value="2" <?=checked($cfg['product_review_com_sort'],2)?>> <label for="r35" class="p_cursor">최신글이 아래로</label>
			</td>
		</tr>
		<tr>
			<th scope="row">고객 권한</th>
			<td>
				* 게시판 관리자로 로그인 시 수정/삭제 가능<br>
				* <span class="p_color2">수정</span> :
				<input type="radio" id="r22" name="product_review_edit" value="Y" <?=checked($cfg['product_review_edit'],"Y")?>> <label for="r22" class="p_cursor">가능</label>
				<input type="radio" id="r23" name="product_review_edit" value="N" <?=checked($cfg['product_review_edit'],"N")?>> <label for="r23" class="p_cursor">불가</label><br>
				* <span class="p_color2">삭제</span> :
				<input type="radio" id="r24" name="product_review_del" value="Y" <?=checked($cfg['product_review_del'],"Y")?>> <label for="r24" class="p_cursor">가능</label>
				<input type="radio" id="r25" name="product_review_del" value="N" <?=checked($cfg['product_review_del'],"N")?>> <label for="r25" class="p_cursor">불가</label><br>
				* <span class="p_color2">댓글</span> :
				<input type="radio" id="product_review_comment2" name="product_review_comment" value="2" <?=checked($cfg['product_review_comment'],"2")?>> <label for="product_review_comment2" class="p_cursor">가능</label>
				<input type="radio" id="product_review_comment1" name="product_review_comment" value="1" <?=checked($cfg['product_review_comment'],"1")?>> <label for="product_review_comment1" class="p_cursor">불가</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품후기 등록</th>
			<td>
				<input type="radio" id="r11" name="product_review_atype" value="1" <?=checked($cfg['product_review_atype'],1)?>> <label for="r11" class="p_cursor">바로 등록</label> <span class="explain">(상품후기는 즉시 등록되므로 검증되지 않은 정보가 노출될 수 있습니다)</span><br>
				<input type="radio" id="r12" name="product_review_atype" value="2" <?=checked($cfg['product_review_atype'],2)?>> <label for="r12" class="p_cursor">관리자 승인 시 등록</label> (<label class="p_cursor"><input type="checkbox" name="product_review_atype_detail" value="Y" <?=checked($cfg['product_review_atype_detail'], 'Y')?>>  관리자 승인이 안된 상품후기는 작성자에게만 노출됩니다.</label>)
			</td>
		</tr>
		<tr>
			<th scope="row">상품평 적립금 설정</th>
			<td>
				<img src="<?=$engine_url?>/_manage/image/shortcut2.gif" border="0" align="top"> <a href="javascript:goM('config@milage')" class="p_color2">바로가기</a>
			</td>
		</tr>
		<tr>
			<th scope="row">상품평 분류</th>
			<td>
				<input type="text" name="product_review_cate" value="<?=inputText($cfg['product_review_cate'])?>" class="input" size="80">
				<p class="explain"><b>,</b> 로 구분 하세요 (예: 상품,서비스,기타)</p>
			</td>
		</tr>
		<tr>
			<th scope="row">신규 글 표시</th>
			<td>
				<input type="text" name="product_review_new_time" value="<?=$cfg['product_review_new_time']?>" class="input" size="5"> 시간 <span class="explain">(미설정 시 작동하지 않음)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">신규게시글 작성 통보</th>
			<td>
				<div>
					* <span class="p_color2">문자알림 :</span>
					<label class="p_cursor"><input type="radio" name="product_review_scallback" value="Y" <?=checked($cfg['product_review_scallback'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="product_review_scallback" value="N" <?=checked($cfg['product_review_scallback'], 'N')?>> 사용안함</label>
					<span class="box_btn_s"><a href="?body=config@sms_config&sadmin=Y" target="_blank">설정</a></span>
				</div>
				<div>
					* <span class="p_color2">이메일알림 :</span>
					<label class="p_cursor"><input type="radio" name="product_review_mcallback" value="Y" <?=checked($cfg['product_review_mcallback'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="product_review_mcallback" value="N" <?=checked($cfg['product_review_mcallback'], 'N')?>> 사용안함</label>
					<span class="box_btn_s"><a href="?body=config@email_config" target="_blank">설정</a></span>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">날짜 형식</th>
			<td class="contentFrm">
				현재 설정 : <?=parseDateType($cfg['date_type_review'])?>
				<input type="hidden" id="date_type_review" name="date_type_review" value="<?=$cfg['date_type_review']?>">
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
				<input type="radio" id="r60" name="product_review_use_editor" value="N" <?=checked($cfg['product_review_use_editor'],"N")?>> <label for="r60" class="p_cursor">사용안함</label><br>
				<input type="radio" id="r61" name="product_review_use_editor" value="Y" <?=checked($cfg['product_review_use_editor'],"Y")?>> <label for="r61" class="p_cursor">사용함</label>
                <?php if ($scfg->comp('use_review_image_cnt', 'Y') == false) { ?>
                <span class="box_btn_s"><input type="button" value="에디터 이미지를 포토리뷰로 처리" onclick="reviewImageMigration()"></span>
                <?php } ?>
			</td>
		</tr>
		<tr>
			<th rowspan="2">제목입력 제한</th>
			<td>
				<label class="p_cursor"><input type="radio" name="review_fsubject" value="N" checked> 사용안함</label>
				<label class="p_cursor"><input type="radio" name="review_fsubject" value="Y" <?=checked($cfg['review_fsubject'], 'Y')?>> 사용함</label>
			</td>
		</tr>
		<tr>
			<td>
				<textarea name="fsubject" class="txta" rows="5" cols="80"><?=stripslashes($fsubject)?></textarea>
				<ul class="list_msg">
					<li>게시물 제목을 입력한 항목 중에서만 선택할 수 있습니다.</li>
					<li>제목은 1개이상 입력할 수 있으며, 각 제목 입력은 엔터로 구분해 주세요.</li>
					<li>
						설정한 내용을 적용 받으려면 <a href="?body=design@editor&type=&edit_pg=7%2F5">디자인관리>HTML 편집>페이지 편집 > 게시판정보 > 상품별 이용후기</a> 메뉴에서 글제목 입력 input 태그를 <span class="p_color2">{{$제한제목목록}}</span> 으로<br>
						<a href="?body=design@editor&type=&edit_pg=7%2F7">디자인관리>HTML 편집>페이지 편집 > 게시판정보 > 상품 이용 후기 수정</a> 메뉴에서 글제목 입력 input 태그를 <span class="p_color2">{{$제한제목목록}}</span> 으로 대체해 주세요.
					</li>
					<li><span class="p_color2">{{$제한제목목록}}</span> 모듈에 제거한 input 태그를 그대로 붙여넣으시면 기능을 사용하지 않거나 '게시판 관리자' 로그인 시 수동으로 제목을 입력하실 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품후기 이동</th>
			<td>
				<label><input type="radio" name="use_trash_rev" value="Y" <?=checked($cfg['use_trash_rev'], 'Y')?>> 상품후기 휴지통으로 이동</label></label>
				<label><input type="radio" name="use_trash_rev" value="N" <?=checked($cfg['use_trash_rev'], 'N')?>> 즉시 영구 삭제(복구 불가)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품후기 보관 기간</th>
			<td>
				<?=selectArray(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'trash_rev_trcd', true, '삭제안함', $cfg['trash_rev_trcd'])?>
				일 후 영구삭제
				<ul class="list_msg">
					<li>영구삭제된 상품후기의 모든 데이터(이미지 포함)는 복구가 불가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">게시판 스팸차단</th>
			<td>
				<a href="./?body=board@board_config" class="desc4">스팸 게시물 차단 옵션 설정 <img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	function ckReviewConfig(f) {
		var temp = '';
		var sel = document.getElementById('date_select');
		for(var x = 0; x < sel.options.length; x++) {
			if(temp) temp += '@';
			temp += sel.options[x].value;
		}
		document.getElementById('date_type_review').value = temp;
	}

	var sel = new R2Select('date_select');
	var ditem = new R2Select('date_item');

    function chgReviewMany(o) {
        if (!o) {
            o = $(':checked[name=product_review_auth]')[0];
        }
        if (o.value == '4') {
            $('input[name=product_review_many]').filter('[value=1]').prop('checked', true);
            $('.review_many').hide();
        } else {
            $('.review_many').show();
        }
    }

	$(document).ready(function(){
		if($('input[name="product_review_atype"]:checked').val() == '1'){
			$('input[name="product_review_atype_detail"]').prop('checked',false);
			$('input[name="product_review_atype_detail"]').prop('disabled',true);
		}
		$('#r11').click(function () {
			$('input[name="product_review_atype_detail"]').prop('checked',false);
			$('input[name="product_review_atype_detail"]').prop('disabled',true);
        });
		$('#r12').click(function () {
			$('input[name="product_review_atype_detail"]').prop('disabled',false);
        });


        $(':radio[name=product_review_auth]').on('change', function() {
            chgReviewMany(this);
        });
        chgReviewMany();
	});

    function reviewImageMigration()
    {
        var msg = '상품 후기가 많은 사이트의 경우 사이트 운영에 일시적으로 이상이 발생할 수 있습니다.\n접속자가 많은 시간을 피해 설정을 진행해주세요.';
        if (confirm(msg) == true) {
            printLoading();
            $.post('./index.php', {'body':'member@product_review_update.exe', 'exec': 'migration'}, function(r) {
                location.reload();
            });
        }
    }
</script>