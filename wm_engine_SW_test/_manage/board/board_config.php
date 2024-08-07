<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 설정
	' +----------------------------------------------------------------------------------------------+*/

	$board_cnt = 0;
	if(!$cfg['use_trash_bbs']) $cfg['use_trash_bbs'] = 'N';
	if($cfg['use_trash_bbs'] == 'N' && isAutoIncrement('mari_board') < 1) { // pkey 재생성 필요 여부 체크
		$board_cnt = $pdo->row("select count(*) from mari_board");
	}

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">게시판 설정</h2>
	</div>
	<div class="box_bottom top_line left">
		<ul class="list_msg">
			<li>아래의 설정은 게시판 관리자를 제외한 회원에게 적용되며 <u>0 또는 미지정시 작동하지 않습니다</u></li>
			<li>
				게시판 별로 설정과 함께 전체 게시판 글/댓글 수 제한을 함께 체크합니다.<br>
				예) 전체 게시판 글수 제한 15개, A게시판 글수 제한 8개, B게시판 글수 제한 10개 일경우<br>
				A 게시판에는 하루 최대 8개의 글을 작성할 수 있습니다.<br>
				B 게시판에는 하루 최대 10개의 글을 작성할 수 있습니다.<br>
				A, B 게시판을 합쳐 하루 최대 15개의 글을 작성할 수 있습니다.<br>
				따라서, B 게시판에 10개의 글을 작성했다면, A 게시판에는 5개의 글을 더 작성할 수 있으며,<br>
				A 게시판에 8개의 글을 작성했다면, B 게시판에는 7개의 글을 작성할 수 있습니다.
			</li>
		</ul>
	</div>
	<div class="box_title">
		<h2 class="title">전체 게시판 글/댓글수합 제한</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">전체 게시판 글/댓글수합 제한</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">1인당<br>글쓰기 제한 (1일)</th>
			<td>
				<input type="text" name="board_day_write" value="<?=$cfg[board_day_write]?>" class="input" size="10">
			</td>
		</tr>
		<tr>
			<th scope="row">1인당<br>댓글 제한 (1일)</th>
			<td>
				<input type="text" name="board_day_comment" value="<?=$cfg[board_day_comment]?>" class="input" size="10">
			</td>
		</tr>
		<tr>
			<th scope="row">실명</th>
			<td><input type="text" name="admin_name" value="<?=inputText($cfg['admin_name'])?>" class="input"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="bbs_common">
	<div class="box_title">
		<h2 class="title">게시물 삭제/보관 기간 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시물 삭제/보관 기간 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">게시물 삭제 제한</th>
			<td>
				<label class="p_cursor"><input type="radio" id="r1" name="board_reply_del" value="N" <?=checked($cfg['board_reply_del'],"N").checked($cfg['board_reply_del'],"")?>> 답글이 있을 경우에는 삭제가 불가능합니다</label><br>
				<label class="p_cursor"><input type="radio" id="r2" name="board_reply_del" value="Y" <?=checked($cfg['board_reply_del'],"Y")?>> 답글이 있는 경우에도 삭제가 가능하며, 해당 글에 대한 답글을 모두 삭제합니다</label>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 게시물 이동</th>
			<td>
				<label><input type="radio" name="use_trash_bbs" value="Y" <?=checked($cfg['use_trash_bbs'], 'Y')?>> 게시물 휴지통으로 이동</label></label>
				<label><input type="radio" name="use_trash_bbs" value="N" <?=checked($cfg['use_trash_bbs'], 'N')?>> 즉시 영구 삭제(복구 불가)</label>
				<ul class="list_msg">
					<?if($cfg['use_trash_bbs'] == 'N' && $board_cnt > 10000) {?>
					<li>게시판 마이그레이션이 필요합니다. '사용함'으로 설정시 사이트가 잠시 느려질수 있으니 1:1고객센터 문의 글로 접수 바랍니다.</li>
					<?}?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 게시물 보관 기간</th>
			<td>
				<?=selectArray(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'trash_bbs_trcd', true, '삭제안함', $cfg['trash_bbs_trcd'])?>
				일 후 영구삭제
				<ul class="list_msg">
					<li>영구삭제된 게시물의 모든 데이터(이미지 포함)는 복구가 불가능합니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="temp_common">
	<div class="box_title">
		<h2 class="title">추가항목 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">추가항목 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">추가항목 개수</th>
			<td>
				최대 <?=selectArray(array(3, 4, 5, 6, 7, 8, 9, 10), 'board_add_temp', true, '', $cfg['board_add_temp'])?> 개 사용
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">게시판 스팸차단</h2>
	</div>
	<div class="box_middle left">
		<p>게시판 스팸차단 설정은 상품 게시판(상품 Q&A, 상품 후기 게시판)에 공통 적용됩니다.</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시판 스팸차단</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">금지어 설정</th>
			<td>
				<p class="p_color2">해당 금지어가 있는 게시물은 작성되지 않습니다.</p>
				<textarea class="txta" name="boardFilter"><?=inputText($cfg[boardFilter])?></textarea>
				<p class="explain">여러 단어를 입력할 경우 ,(쉼표)로 구분해 주십시오.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">금지IP 설정</th>
			<td>
				<p class="p_color2">지정된 아이피에서는 게시물을 작성할수 없습니다.</p>
				<textarea class="txta" name="boardDenyIP"><?=inputText($cfg[boardDenyIP])?></textarea>
				<p class="explain">여러 아이피를 입력할 경우 ,(쉼표)로 구분해 주십시오.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">영문스팸 차단</th>
			<td>
				<label class="p_cursor"><input type="radio" name="board_chk_Korean" value="N" <?=checked($cfg[board_chk_Korean],"N").checked($cfg[board_chk_Korean],"")?>> 한글 포함 여부를 체크하지 않습니다</label> (기본값)<br>
				<label class="p_cursor"><input type="radio" name="board_chk_Korean" value="Y" <?=checked($cfg[board_chk_Korean],"Y")?>> 메시지에 반드시 한글이 포함되어야 합니다</label> (영문권 스팸을 차단할수 있습니다)
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="config@config.exe">
<input type="hidden" name="config_code" value="captcha">
	<div class="box_title">
		<h2 class="title">캡차 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">캡차 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">Site key</th>
			<td>
				<input type="text" name="captcha_key" value="<?=$cfg['captcha_key']?>" class="input" size="50" >
			</td>
		</tr>
		<tr>
			<th scope="row">Secret key</th>
			<td>
				<input type="text" name="captcha_secret_key" value="<?=$cfg['captcha_secret_key']?>" class="input" size="50" >
			</td>
		</tr>
		<tr>
			<th scope="row">상품Q&A</th>
			<td>
				<label>
					<input type="radio" name="usecap_qna" value='Y' <?=checked($cfg['usecap_qna'], 'Y')?> onclick="click_chk('qna', 'Y');"/> 사용함
				</label>
				(
				<label class="p_cursor">
					<input type="checkbox" name="usecap_member_qna" value="Y" <?=checked($cfg['usecap_member_qna'], 'Y')?> <?=disabled($cfg['usecap_qna'])?>> 회원
				</label>
				<label class="p_cursor">
					<input type="checkbox" name="usecap_nonmember_qna" value="Y" <?=checked($cfg['usecap_nonmember_qna'], 'Y')?> <?=disabled($cfg['usecap_qna'])?>> 비회원
				</label>
				)
				<label><input type="radio" name="usecap_qna" value='' <?=checked($cfg['usecap_qna'],  '')?> onclick="click_chk('qna');"/> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품후기</th>
			<td>
				<label>
					<input type="radio" name="usecap_review" value='Y' <?=checked($cfg['usecap_review'], 'Y')?> onclick="click_chk('review', 'Y');"/> 사용함
				</label>
				(
				<label class="p_cursor">
					<input type="checkbox" name="usecap_member_review" value="Y" <?=checked($cfg['usecap_member_review'], 'Y')?> <?=disabled($cfg['usecap_review'])?>> 회원
				</label>
				<label class="p_cursor">
					<input type="checkbox" name="usecap_nonmember_review" value="Y" <?=checked($cfg['usecap_nonmember_review'], 'Y')?> <?=disabled($cfg['usecap_review'])?>> 비회원
				</label>
				)
				<label><input type="radio" name="usecap_review" value=''  <?=checked($cfg['usecap_review'],  '')?> onclick="click_chk('review');"/> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">1:1문의</th>
			<td>
				<label>
					<input type="radio" name="usecap_to" value='Y' <?=checked($cfg['usecap_to'], 'Y')?> onclick="click_chk('to', 'Y');"/> 사용함
				</label>
				(
				<label class="p_cursor">
					<input type="checkbox" name="usecap_member_to" value="Y" <?=checked($cfg['usecap_member_to'], 'Y')?> <?=disabled($cfg['usecap_to'])?>> 회원
				</label>
				<label class="p_cursor">
					<input type="checkbox" name="usecap_nonmember_to" value="Y" <?=checked($cfg['usecap_nonmember_to'], 'Y')?> <?=disabled($cfg['usecap_to'])?>> 비회원
				</label>
				)
				<label><input type="radio" name="usecap_to" value=''  <?=checked($cfg['usecap_to'],  '')?> onclick="click_chk('to');"/> 사용안함</label>
			</td>
		</tr>
		<?php
		$con_res = $pdo->iterator("select * from `mari_config` order by no");
        foreach ($con_res as $condata) {
		?>
		<tr>
			<th scope="row"><?=$condata['title']?></th>
			<td>
				<label>
					<input type="radio" name="usecap_<?=$condata['db']?>" value='Y' <?=checked($cfg['usecap_'.$condata['db']], 'Y')?> onclick="click_chk('<?=$condata['db']?>', 'Y');"/> 사용함
				</label>
				(
				<label class="p_cursor">
					<input type="checkbox" name="usecap_member_<?=$condata['db']?>" value="Y" <?=checked($cfg['usecap_member_'.$condata['db']], 'Y')?> <?=disabled($cfg['usecap_'.$condata['db']])?>> 회원
				</label>
				<label class="p_cursor">
					<input type="checkbox" name="usecap_nonmember_<?=$condata['db']?>" value="Y" <?=checked($cfg['usecap_nonmember_'.$condata['db']], 'Y')?> <?=disabled($cfg['usecap_'.$condata['db']])?>> 비회원
				</label>
				)
				<label><input type="radio" name="usecap_<?=$condata['db']?>" value=''  <?=checked($cfg['usecap_'.$condata['db']],  '')?> onclick="click_chk('<?=$condata['db']?>');"/> 사용안함</label>
			</td>
		</tr>
		<?
		}
		?>
	</table>
	<div class="box_middle2 left">
		<div class="summary_sns">
			<p class="title">캡차 Site key, Secret key 발급 안내</p>
			<ol>
				<li>1) <a href="https://www.google.com/recaptcha/admin#list" target="_blank" class="p_color">https://www.google.com/recaptcha/admin#list</a> 접속</li>
				<li>
					2) 사이트를 등록합니다.
					<ul>
						<li>- 라벨 (사이트를 구별할 수 있는 이름을 입력합니다.)</li>
						<li>- reCAPTCHA 유형 (reCAPTCHA v2 타입을 선택합니다.)</li>
						<li>- 도메인 (http:// 또는 https://를 제외한 도메인주소를 입력합니다.)</li>
						<li>- reCAPTCHA 서비스 약관에 동의해 주세요. (서비스 약관에 대한 동의여부를 체크합니다.)</li>
					</ul>
				</li>
				<li>3) Site key, Secret key를 확인합니다.</li>
			</ol>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script>
function click_chk(db, chk) {
	if(!chk) {
		$('input[name=usecap_member_'+db+']').prop('disabled', true);
		$('input[name=usecap_nonmember_'+db+']').prop('disabled', true);
	}else {
		$('input[name=usecap_member_'+db+']').prop('disabled', false);
		$('input[name=usecap_nonmember_'+db+']').prop('disabled', false);
	}
}
</script>