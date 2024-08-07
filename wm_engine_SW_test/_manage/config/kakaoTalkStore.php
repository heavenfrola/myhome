<?PHP

	if(!$cfg['use_kakaoTalkStore']) $cfg['use_kakaoTalkStore'] = 'N';
	if(!$cfg['kakaoTalkStore_ratio']) $cfg['kakaoTalkStore_ratio'] = 'SQUARE';
	if(!$cfg['kakaoTalkStore_imgno']) $cfg['kakaoTalkStore_imgno'] = 3;
	if(!$cfg['use_talkstore_qna']) $cfg['use_talkstore_qna'] = 'N';
	if(!$cfg['add_prd_img']) $cfg['add_prd_img'] = 3;

	// 상품이미지 이름
	$prd_image_ea = ($cfg['add_prd_img'] > 3) ? $cfg['add_prd_img'] : 3;
	$prd_image_names = array(
		1 => '대이미지',
		2 => '중이미지',
		3 => '소이미지',
	);
	for($i = 4; $i <= $prd_image_ea; $i++) {
		$prd_image_names[$i] = '추가이미지 '.($i-3);
	}

	$mgroup9_name = $pdo->row("select name from $tbl[member_group] where no=9");

?>
<form method="POST" action="./?" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="config@kakaoTalkStore.exe">

	<div class="box_title first">
		<h2 class="title">카카오톡 스토어 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">카카오톡 스토어 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<label><input type="radio" name="use_kakaoTalkStore" value="Y" <?=checked($cfg['use_kakaoTalkStore'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_kakaoTalkStore" value="N" <?=checked($cfg['use_kakaoTalkStore'], 'N')?>> 사용안함</label>
					<div class="list_info tp">
						<p>카카오톡 스토어 입점 안내는 '톡스토어 판매자 공식 블로그'를 참고해주세요. <a href="//redirect.wisa.co.kr/?code=joinTalkstore" target="_blank">바로가기</a></p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">API 인증키</th>
				<td>
					<input type="text" name="kakaoTalkStore_key" class="input input_full" value="<?=$cfg['kakaoTalkStore_key']?>">
					<div class="list_info tp">
						<p>카카오톡 스토어 판매자센터 > 상점관리 > 스토어 기본정보 내 '톡스토어API 인증키' 정보를 입력해주세요.</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">상품이미지</th>
				<td>
					<select name="kakaoTalkStore_imgno">
						<?php for($ii=1; $ii <= $cfg['add_prd_img']; $ii++) { ?>
						<option value="<?=$ii?>" <?=checked($cfg['kakaoTalkStore_imgno'], $ii, true)?>><?=$prd_image_names[$ii]?></option>
						<?php } ?>
					</select>
					<div>
						<label><input type="radio" name="kakaoTalkStore_ratio" value="SQUARE" <?=checked($cfg['kakaoTalkStore_ratio'], 'SQUARE')?>> 1:1 비율</label></li>
						<label><input type="radio" name="kakaoTalkStore_ratio" value="VERTICAL_LONG_RECTANGLE" <?=checked($cfg['kakaoTalkStore_ratio'], 'VERTICAL_LONG_RECTANGLE')?>> 3:4 비율</label>
					</div>
					<ul class="list_info tp">
						<li>이미지 형태에 따라 1:1 또는 3:4 비율을 설정할 수 있으며, 상품이미지 연동 시 이미지가 자동 크롭됩니다.</li>
						<li>이미지 사이즈의 경우 1:1 비율은 750X750, 3:4 비율은 750X1000 이상을 권장하며, 최소 1:1 비율은 375X375, 3:4 비율은 375X500 이상의 이미지만 등록할 수 있습니다.</li>
						<li>png, jpg, jpeg 형식의 이미지만 등록 가능하며, 10MB를 초과할 수 없습니다.</li>
					</ul>
				</td>
			</tr>
			<!--
			<tr>
				<th>할인 연동</th>
				<td>
					<label><input type="checkbox" name="kakaoTalkStore_msale" value="Y" <?=checked($cfg['kakaoTalkStore_msale'], 'Y')?>> 회원할인</label>
					<label><input type="checkbox" name="kakaoTalkStore_esale" value="Y" <?=checked($cfg['kakaoTalkStore_esale'], 'Y')?>> 이벤트</label>
					<ul class="list_info tp">
						<li>
							<span class="warring">설정 변경 전 카카오톡 스토어에 등록된 상품에는 변경 된 설정이 반영되지 않습니다.</span><br>
							기 등록된 상품이 있을 경우 경우 상품 수정을 통해 카카오톡 스토어에 할인 정보를 재전송 하거나, 카카오톡 스토어 관리자에서 할인 정보를 직접 수정해 주시기 바랍니다.
						</li>
						<li>회원할인 진행시 '<?=$mgroup9_name?>' 등급 기준으로 할인 금액이 책정됩니다.</li>
						<li>이벤트가 현금 전용인 경우 카카오톡 스토어에 반영되지 않습니다.</li>
						<li>이벤트가 회원 전용일 경우 회원할인도 같이 설정해야 이벤트가 반영됩니다.</li>
						<li>두 가지 할인이 모두 적용되는 상품은 할인율이 합산됩니다.</li>
					</ul>
				</td>
			</tr>
			-->
			<tr>
				<th scope="row">상품문의 연동</th>
				<td>
					<label><input type="radio" name="use_talkstore_qna" value="Y" <?=checked($cfg['use_talkstore_qna'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_talkstore_qna" value="N" <?=checked($cfg['use_talkstore_qna'], 'N')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>상품문의 연동은 카카오톡 스토어에 등록된 상품문의를 쇼핑몰의 상품Q&A에 연동하는 기능입니다.</li>
						<li>상품문의연동 시 작성자 정보는 수집되지 않습니다.</li>
						<li>연동된 상품문의에 대해 관리자 > 고객CRM > 상품문의 > 상품Q&A에서 답변 시 카카오톡 스토어에도 적용됩니다.</li>
					</ul>
				</td>
			</tr>
			<?php if (is_dir($engine_dir.'/_partner')) { ?>
			<!--
			<tr>
				<th scope="row">입점사 등록 허용</th>
				<td>
					<label><input type="radio" name="use_kts_partner" value="Y" <?=checked($cfg['use_kts_partner'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_kts_partner" value="N" <?=checked($cfg['use_kts_partner'], 'N')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>
							입점사별 배송비가 별도 책정될 경우 반드시 카카오톡 스토어 관리자에서 업체별 출고지를 한 후 상품별로 출고지를 설정해야합니다.<br>
							출고지에 따라 배송비가 묶음 됩니다.
						</li>
					</ul>
				</td>
			</tr>
			 -->
			<?php } ?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>전체 재고는 매일 오전 8시 카카오톡 스토어에 재전송됩니다.</li>
			<li>신규 주문 및 주문정보 변경사항은 10분에 한 번씩 자동 갱신됩니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>