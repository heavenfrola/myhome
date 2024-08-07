<?PHP

	if(!$cfg['use_fb_pixel']) $cfg['use_fb_pixel'] = 'N';
	if(!$cfg['use_fb_ad_feed']) $cfg['use_fb_ad_feed'] = 'N';
	if(!$cfg['fb_ad_image_no']) $cfg['fb_ad_image_no'] = 2;
	if(!$cfg['fb_ad_goods_name']) $cfg['fb_ad_goods_name'] = 1;
	if(!$cfg['use_fb_npay']) $cfg['use_fb_npay'] = 'N';
    $scfg->def('use_fb_conversion', 'N');

	$feed_url1 = $root_url.'/_data/compare/fb/product.csv.php';

    $phpversion = explode('.', phpversion());
    $fb_disabled = ($phpversion[0] < 7) ? 'disabled' : '';

?>
<form name="facebookF" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return validateForm()">
	<input type="hidden" name="body" value="openmarket@facebook.exe">
	<input type="hidden" name="config_code" value="facebook_pixel">
	<div class="box_title first">
		<h2 class="title">페이스북</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">페이스북</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">Facebook 픽셀 사용</th>
			<td colspan="2">
				<label><input type="radio" name="use_fb_pixel" value="Y" <?=checked($cfg['use_fb_pixel'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_fb_pixel" value="N" <?=checked($cfg['use_fb_pixel'], 'N')?>> 사용안함</label>
				<ul class="list_msg">
					<li>
						전환추적 픽셀 생성이 필요합니다.
						<a href="https://www.facebook.com/ads/manager/pixel/facebook_pixel" target="_blank" class="p_color">생성하기</a>
					</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">픽셀 ID</th>
			<td colspan="2">
				<input type="text" name="fb_pixel_id" id="fb_pixel_id" class="input" size="20" value="<?=$cfg['fb_pixel_id']?>">
				<ul class="list_info">
					<li>픽셀 설정페이지에서 코드 보기를 하신 후 파란색으로 표시된 아이디를 입력해주세요.</li>
					<li>또는 이메일로 코드 전송시 픽셀 아이디가 표시됩니다.</li>
					<li>수집된 데이터가 분석되는데 약 24시간 정도가 소요됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">상품Feed 생성</th>
			<td colspan="2">
				<label><input type="radio" name="use_fb_ad_feed" value="Y" <?=checked($cfg['use_fb_ad_feed'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_fb_ad_feed" value="N" <?=checked($cfg['use_fb_ad_feed'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td colspan="2">
				<input type="radio" name="fb_ad_image_no" id="img1" value="2" <?=checked($cfg['fb_ad_image_no'],"2")?>> <label for="img1" class="p_cursor">중간 이미지</label>
				<input type="radio" name="fb_ad_image_no" id="img2" value="3" <?=checked($cfg['fb_ad_image_no'],"3")?>> <label for="img2" class="p_cursor">작은 이미지</label>
				<input type="radio" name="fb_ad_image_no" id="img3" value="1" <?=checked($cfg['fb_ad_image_no'],"1")?>> <label for="img3" class="p_cursor">큰 이미지</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품명 설정</th>
			<td colspan="2">
				<input type="radio" name="fb_ad_goods_name" value="1" id="t9" <?=checked($cfg['fb_ad_goods_name'], "1")?>> <label for="t9" class="p_cursor">상품명</label><br>
				<input type="radio" name="fb_ad_goods_name" value="2" id="t10" <?=checked($cfg['fb_ad_goods_name'], "2")?>> <label for="t10" class="p_cursor">검색키워드</label><br>
				<input type="radio" name="fb_ad_goods_name" value="3" id="t11" <?=checked($cfg['fb_ad_goods_name'], "3")?>> <label for="t11" class="p_cursor">상품명 + 검색키워드</label>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">네이버페이 트래킹</th>
			<td colspan="2">
				<ul>
					<li><label><input type="radio" name="use_fb_npay" value="Y" <?=checked($cfg['use_fb_npay'], 'Y')?>> 네이버페이 주문을 픽셀로 전송합니다.</label></li>
					<li><label><input type="radio" name="use_fb_npay" value="N" <?=checked($cfg['use_fb_npay'], 'N')?>> 네이버페이 주문을 픽셀로 전송하지 않습니다.</label></li>
				</ul>
                <ul class="list_info">
                    <li>페이스북 서버에 문제가 발생할 경우 네이버페이 주문 수집이 정상적으로 되지 않을 수 있습니다.</li>
                </ul>
			</td>
		</tr>
		<tr>
			<th scope="row">전환 API 사용</th>
			<td>
                <label><input type="radio" name="use_fb_conversion" value="Y" <?=checked($cfg['use_fb_conversion'], 'Y')?> <?=$fb_disabled?>> 사용함</label>
                <label><input type="radio" name="use_fb_conversion" value="N" <?=checked($cfg['use_fb_conversion'], 'N')?>> 사용안함</label>
                <?php if ($fb_disabled) { ?>
                <ul class="list_info">
                    <li>페이스북 구매전환 API를 사용하기 위해서는 PHP 7 이상이 설치되어있어야 합니다.</li>
                    <li>기능 이용을 위한 시스템 세팅 또는 서버이전 필요합니다. 위사 호스팅 이용 시 1:1 고객센터로 문의해 주세요.</li>
                </ul>
                <?php } ?>
            </td>
            <td style="border-left: 1px solid #d6d6d6;">
				<dl>
					<dt class="title">
                        <strong>Access Token</strong>
                        <a href="https://developers.facebook.com/docs/marketing-api/conversions-api/get-started#via-events-manager" target="_blank" class="setup" style="margin: 0"></a>
                    </dt>
                    <dd>
                        <ul class="list_info">
                            <li>정확한 정보를 입력하지 않을 경우 주문완료 후 완료페이지로 정상적으로 이동되지 않을수 있습니다.</li>
                            <li>설정 후 반드시 주문테스트를 진행해 주시기 바랍니다.</li>
                            <li>
                                여섯가지의 이벤트에 대응됩니다.
                                <a href="#" class="tooltip_trigger" data-child="tooltip_events" style="float: none;"></a>
                                <div class="info_tooltip tooltip_events">
                                    <ul>
                                        <li>- 페이지 뷰(PageView)</li>
                                        <li>- 상품 조회(ViewContent)</li>
                                        <li>- 검색(Search)</li>
                                        <li>- 장바구니에 담기(addCart)</li>
                                        <li>- 결제 시작(InitiateCheckout)</li>
                                        <li>- 구매(Purchase)</li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </dd>
                    <dd>
                        <textarea name="fb_pixel_conversion" class="txta"><?=$cfg['fb_pixel_conversion']?></textarea>
                    </dd>
                </dl>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="engineFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="openmarket@show.exe">
	<input type="hidden" name="filetype">
	<div class="box_title">
		<h2 class="title">페이스북 Feed URL</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">페이스북 Feed URL</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>상품 FEED</th>
			<td>
				<?if($cfg['use_fb_ad_feed'] == 'Y') {?>
				<a href="<?=$feed_url1?>" target="_blank"><?=$feed_url1?></a>
				<?} else {?>
				<div class="explain">사용중이 아닙니다.</div>
				<?}?>
			</td>
		</tr>
	</table>
</form>

<script language="JavaScript">
	function makeFile(f, filetype){
		if(!confirm('상품수가 많을 경우 처리시간이 길어질 수 있습니다')) return;
		f.filetype.value=filetype;
		f.submit();
	}

    function validateForm() {
        let f = $('[name=facebookF]');
        let id_input = $('#fb_pixel_id', f);
        let id = id_input.val().trim();
        id_input.val(id);

        if ($('[name=use_fb_pixel]:radio:checked', f).val() == 'Y') {
            if (!id) {
                alert('픽셀 ID를 입력해주세요.');
                id_input.focus();
                return false;
            } else if(!/^\d+$/.test(id)) {
                alert('픽셀 ID는 숫자만 입력가능합니다.');
                id_input.focus();
                return false;
            }
        }

        if ($('[name=use_fb_conversion]:radio:checked', f).val() == 'Y') {
            if (!$('[name=fb_pixel_conversion]', f).val()) {
                alert('Access Token을 입력해주세요.');
                $('[name=fb_pixel_conversion]', f).focus();
                return false;
            }
        }

        return true;
    }
</script>