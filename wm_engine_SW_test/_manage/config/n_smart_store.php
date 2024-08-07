<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 스마트스토어 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['n_smart_store']) $cfg['n_smart_store'] = 'N';
	if(!$cfg['n_smart_content2']) $cfg['n_smart_content2'] = 'N';
	if(!$cfg['use_n_smart_qna']) $cfg['use_n_smart_qna'] = 'N';

    $brands = array();
    if($cfg['xbig_mng'] == 'Y') {
        $brands['xbig'] = $cfg['xbig_name_mng'].' 분류';
    }
    if($cfg['ybig_mng'] == 'Y') {
        $brands['ybig'] = $cfg['ybig_name_mng'].' 분류';
    }
    $fields = $pdo->iterator("select no, name from {$tbl['product_filed_set']} where category=0 order by sort asc");
    foreach ($fields as $val) {
        $brands['field@'.$val['no']] = '[추가항목] '.stripslashes($val['name']);
    }

    $scfg->def('nstore_area2extraFee', 0);
    $scfg->def('nstore_area3extraFee', 0);

?>
<div class="box_title first">
	<h2 class="title">네이버 스마트스토어 설정</h2>
</div>
<form method="post" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="smartstore">
	<table class="tbl_row">
		<caption class="hidden">네이버 스마트스토어 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<label class="p_cursor"><input type="radio" name="n_smart_store" value="Y" <?=checked($cfg['n_smart_store'] ,'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="n_smart_store" value="N" <?=checked($cfg['n_smart_store'] ,'N')?>> 사용안함</label>
				</td>
			</tr>
            <tr>
                <th scope="row">애플리케이션 ID</th>
                <td>
                    <input type="text" name="n_store_app_id" value="<?=$cfg['n_store_app_id']?>" class="input input_full">
                    <div class="list_info tp">
                        <p>네이버 커머스API센터 > 어드민 > 내스토어 애플리케이션 > 애플리케이션 상세 경로의 "애플리케이션 ID" 정보를 입력해 주세요.</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">애플리케이션 시크릿</th>
                <td>
                    <input type="text" name="n_store_app_secret" value="<?=$cfg['n_store_app_secret']?>" class="input input_full">
                    <div class="list_info tp">
                        <p>네이버 커머스API센터 > 어드민 > 내스토어 애플리케이션 > 애플리케이션 상세 경로의 "애플리케이션 시크릿" 정보를 입력해 주세요.</p>
                    </div>
                </td>
            </tr>
            <!--
			<tr>
				<th scope="row">구) API 연동용 판매자ID</th>
				<td>
					<input type="text" name="n_smart_id" value="<?=$cfg['n_smart_id']?>" class="input input_full">
					<div class="list_info tp">
						<p>네이버 스마트스토어센터 > 스마트스토어관리 > 스토어 관리 > API 정보 탭 내 'API 연동용 판매자ID' 정보를 입력해주세요.</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">구) API ID</th>
				<td>
					<input type="text" name="n_smart_api_id" value="<?=$cfg['n_smart_api_id']?>" class="input input_full">
					<ul class="list_info tp">
						<li>네이버 스마트스토어센터 > 스마트스토어관리 > 스토어 관리 > API 정보 탭 내 'API ID' 정보를 입력해주세요.</li>
						<li class="warning">API 사용 설정을 사용으로 변경한 다음, API 대행사 내 상품/주문 API를 위사로 선택한 후 저장해주세요.</li>
					</ul>
				</td>
			</tr>
			-->
			<tr>
				<th scope="row">상품문의 연동</th>
				<td>
					<label><input type="radio" name="use_n_smart_qna" value="Y" <?=checked($cfg['use_n_smart_qna'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_n_smart_qna" value="N" <?=checked($cfg['use_n_smart_qna'], 'N')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>상품문의 연동은 스마트스토어에 등록된 상품문의를 쇼핑몰의 상품Q&A에 연동하는 기능입니다.</li>
						<li>연동된 상품문의에 대해 관리자 > 고객CRM > 상품문의 > 상품Q&A에서 답변 시 스마트스토어에도 적용됩니다.</li>
						<li>상품문의는 스마트스토어에서 작성시간을 제공하지 않으므로 수집시간이 등록일시로 지정됩니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">스마트스토어 상품상세설명</th>
				<td>
					<label><input type="radio" name="n_smart_content2" value="Y" <?=checked($cfg['n_smart_content2'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="n_smart_content2" value="N" <?=checked($cfg['n_smart_content2'], 'N')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>사용안함 선택 시 PC 상품상세설명이 연동됩니다.</li>
						<li>스마트스토어 상품상세설명이 사용함이더라도 내용을 입력하지 않을 경우 PC 상품상세설명이 자동으로 연동됩니다.</li>
					</ul>
				</td>
			</tr>
            <!--
            <tr>
                <th>제주 추가 배송비</th>
                <td>
                    <input type="text" name="nstore_area2extraFee" value="<?=$cfg['nstore_area2extraFee']?>" class="input"> 원
                </td>
            </tr>
            <tr>
                <th>제주 외 도서산간 배송비</th>
                <td>
                    <input type="text" name="nstore_area3extraFee" value="<?=$cfg['nstore_area3extraFee']?>" class="input"> 원
                </td>
            </tr>
            <tr>
                <th scope="row">브랜드 연결</th>
                <td>
                    <?=selectArray($brands, 'n_smart_brand', false, '사용안함', $cfg['n_smart_brand'])?>
                </td>
            </tr>
            <tr>
                <th scope="row">제조사 연결</th>
                <td>
                    <?=selectArray($brands, 'n_smart_manufacture', false, '사용안함', $cfg['n_smart_manufacture'])?>
                </td>
            </tr>
            -->
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>