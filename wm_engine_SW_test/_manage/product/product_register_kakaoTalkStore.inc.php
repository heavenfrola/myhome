<?PHP

	use Wing\API\Kakao\KakaoTalkStore;

	if($admin['level'] > 3 && $cfg['use_kts_partner'] != 'Y') {
		return;
	}

	$kts = new KakaoTalkStore();

	$kko = $pdo->assoc("select * from $tbl[product_talkstore] where pno='$pno'");
	if(is_array($kko)) $kko = array_map('stripslashes', $kko);
	if(!$kko['taxType']) $kko['taxType'] = 'TAX';
	if(!$kko['productCondition']) $kko['productCondition'] = 'NEW';
	if(!$kko['deliveryMethodType']) $kko['deliveryMethodType'] = 'DELIVERY';
	if(!$kko['displayStatus']) $kko['displayStatus'] = ($data['stat'] == 4) ? 'HIDDEN' : 'OPEN';
	if(!$kko['originAreaType']) $kko['originAreaType'] = 'LOCAL';
	$kko['kko_use_prc'] = ($kko['talkstore_prc'] > 0) ? 'Y' : 'N';

	$_talkstore_annouce = array();
	$tres = $pdo->iterator("select idx, title from $tbl[product_talkstore_announce] order by title asc");
    foreach ($tres as $tmp) {
		$_talkstore_annouce[$tmp['idx']] = stripslashes($tmp['title']);
	}

	$origin_area = $kts->getOriginArea();
	$origin_area = json_decode($origin_area);
	$origin_area_type = '';
	$_origin_area = array();
	foreach($origin_area as $val) {
		if($val->level == '1') {
			$origin_area_type = $val->name;
			$_origin_area[$origin_area_type] = array();
		}
		$_origin_area[$origin_area_type][$val->code] = $val->name;
	}

	$addresses = $kts->getAddressed();
	$addresses = json_decode($addresses);
	$_addresses = array();
	foreach($addresses as $val) {
		$_addresses[$val->addressId] = $val->name;
	}

?>
		<div class="box_title_reg kakao">
			<h2 class="title">
				카카오톡 스토어
				<label class="p_cursor"><input type="checkbox" name="kko_useYn" value="Y" <?=checked($kko['useYn'], 'Y')?>> 사용함</label>
			</h2>
			<a href="./?body=config@kakaoTalkStore" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>

		<table class="tbl_row_reg kakao_talk_store_tbl" style="display:none;">
			<caption class="hidden">카카오톡 스토어 항목</caption>
			<colgroup>
				<col style="width:134px">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">전시상태</th>
					<td>
						<label class="p_cursor"><input type="radio" name="kko_displayStatus" value="OPEN" <?=checked($kko['displayStatus'], 'OPEN')?>> 전시중</label>
						<label class="p_cursor"><input type="radio" name="kko_displayStatus" value="HIDDEN" <?=checked($kko['displayStatus'], 'HIDDEN')?>> 전시중지</label>
					</td>
				</tr>
				<tr>
					<th scope="row">판매가</th>
					<td>
						<label class="p_cursor"><input type="checkbox" name="kko_use_prc" value="Y" <?=checked($kko['kko_use_prc'], 'Y')?>> 별도입력</label>
						<input type="text" name="talkstore_prc" class="input input_won" value="<?=parsePrice($kko['talkstore_prc'])?>">
						<div class="list_info tp">
							<p>카카오톡 스토어에 등록할 판매가를 별도로 입력할 수 있습니다.</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">카테고리</th>
					<td>
						<?if(empty($kko['categoryName']) == false) {?>
						<div class="list_info tp">
							<p><?=$kko['categoryName']?></p>
						</div>
						<?}?>
						<select name="kko_categoryId[]" class="kko_category">
							<option value="">:: 대분류 ::</option>
						</select>
						<select name="kko_categoryId[]" class="kko_category">
							<option value="">:: 중분류 ::</option>
						</select>
						<select name="kko_categoryId[]" class="kko_category">
							<option value="">:: 소분류 ::</option>
						</select>
						<select name="kko_categoryId[]" class="kko_category">
							<option value="">:: 세분류 ::</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">인증 정보</th>
					<td>
						<?=selectArray($kts->getCertType(), 'kko_certType', false, null, $kko['certType'])?>
						<input type="text" name="kko_certCode" value="<?=inputText($kko['certCode'])?>" class="input kko_certCode" placeholder="인증코드를 입력해주세요." style="display: none;">
					</td>
				</tr>
				<tr>
					<th scope="row">부가세</th>
					<td>
						<label class="p_cursor"><input type="radio" name="kko_taxType" value="TAX" <?=checked($kko['taxType'], 'TAX')?>> 과세상품</label>
						<label class="p_cursor"><input type="radio" name="kko_taxType" value="DUTYFREE" <?=checked($kko['taxType'], 'DUTYFREE')?>> 면세</label>
						<label class="p_cursor"><input type="radio" name="kko_taxType" value="SMALL" <?=checked($kko['taxType'], 'SMALL')?>> 영세</label>
					</td>
				</tr>
				<tr>
					<th scope="row">상품상태</th>
					<td>
						<label class="p_cursor"><input type="radio" name="kko_productCondition" value="NEW" <?=checked($kko['productCondition'], 'NEW')?>> 새상품</label>
						<label class="p_cursor"><input type="radio" name="kko_productCondition" value="OLD" <?=checked($kko['productCondition'], 'OLD')?>> 중고상품</label>
						<label class="p_cursor"><input type="radio" name="kko_productCondition" value="STOCKED" <?=checked($kko['productCondition'], 'STOCKED')?>> 재고상품</label>
						<label class="p_cursor"><input type="radio" name="kko_productCondition" value="REFURBISH" <?=checked($kko['productCondition'], 'REFURBISH')?>> 리퍼상품</label>
						<label class="p_cursor"><input type="radio" name="kko_productCondition" value="DISPLAY" <?=checked($kko['productCondition'], 'DISPLAY')?>> 전시상품</label>
					</td>
				</tr>
				<tr>
					<th scope="row">배송방법</th>
					<td>
						<label class="p_cursor"><input type="radio" name="kko_deliveryMethodType" value="DELIVERY" <?=checked($kko['deliveryMethodType'], 'DELIVERY')?>> 택배배송</label>
						<label class="p_cursor"><input type="radio" name="kko_deliveryMethodType" value="DIRECT" <?=checked($kko['deliveryMethodType'], 'DIRECT')?>> 직접배송</label>
					</td>
				</tr>
				<tr>
					<th scope="row">출고지</th>
					<td><?=selectArray($_addresses, 'kko_shippingAddressId', false, ':: 주소선택 ::', $kko['shippingAddressId'])?></td>
				</tr>
				<tr>
					<th scope="row">반품/교환지</th>
					<td>
						<?=selectArray($_addresses, 'kko_returnAddressId', false, ':: 주소선택 ::', $kko['returnAddressId'])?>
						<div class="list_info tp">
							<p>출고지 및 반품/교환지 주소는 카카오톡 스토어 판매자센터 > 상점관리 > 출고/배송지 관리 메뉴에서 미리 등록해야 합니다. <a href="https://store-buy-sell.kakao.com/itemReturnInformation/list" target="_blank">바로가기</a></p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" rowspan="2">원산지</th>
					<td>
						<label class="p_cursor"><input type="radio" name="kko_originAreaType" value="LOCAL" <?=checked($kko['originAreaType'], 'LOCAL')?>> 국내산</label>
						<label class="p_cursor"><input type="radio" name="kko_originAreaType" value="IMPORT" <?=checked($kko['originAreaType'], 'IMPORT')?>> 외국산</label>
						<label class="p_cursor"><input type="radio" name="kko_originAreaType" value="USER_INPUT" <?=checked($kko['originAreaType'], 'USER_INPUT')?>> 혼합/기타</label>
					</td>
				</tr>
				<tr>
					<td>
						<span class="originArea originArea_LOCAL"><?=selectArray($_origin_area['국내산'], 'kko_originAreaCode_LOCAL', false, ':: 원산지코드 ::', $kko['originAreaCode'])?></span>
						<span class="originArea originArea_IMPORT"><?=selectArray($_origin_area['외국산'], 'kko_originAreaCode_IMPORT', false, ':: 원산지코드 ::', $kko['originAreaCode'])?></span>
						<span class="originArea originArea_USER_INPUT"><input type="text" name="kko_originAreaContent" class="input input_full" value="<?=$kko['originAreaContent']?>" placeholder=" 혼합/기타 내용"></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						상품정보고시
						<a href="./?body=product@kakaoTalkstore_ann" class="setup btt" tooltip="설정" target="_blank"></a>
					</th>
					<td>
						<?=selectArray($_talkstore_annouce, 'kko_announcementType', false, ':: 상품정보고시 선택 ::', $kko['announcementType'])?>
					</td>
				</tr>
				<tr>
					<th scope="row" rowspan="2">A/S</th>
					<td><input type="text" name="kko_asPhoneNumber" class="input" value="<?=$kko['asPhoneNumber']?>" placeholder=" A/S 연락처"></td>
				</tr>
				<tr>
					<td>
						<p><input type="text" name="kko_asGuideWords" class="input input_full" value="<?=$kko['asGuideWords']?>" placeholder=" A/S 안내문구 입력"></p>
						<div class="list_info tp">
							<p>A/S 연락처의 경우 하이픈(-)을 포함하여 전화번호를 정확히 기입해주세요.(하이픈이 없을 경우 전송 시 문제가 발생될 수 있습니다.)</p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<script type="text/javascript">
		var kkoUseYn;
		(kkoUseYn = function() {
			if($(':checkbox[name=kko_useYn]').prop('checked') == true) {
				$('.kakao_talk_store_tbl').show();
			} else {
				$('.kakao_talk_store_tbl').hide();
			}
		})();
		$(':checkbox[name=kko_useYn]').change(kkoUseYn);

		// 원산지 변경
		var chgKakaoArea;
		(chgKakaoArea = function() {
			$('.originArea').hide();
			$('.originArea_'+$(':checked[name=kko_originAreaType]').val()).show();
		})();
		$(':radio[name=kko_originAreaType]').change(chgKakaoArea);

		// 인증정보 선택
		var chgKakaoCert;
		(chgKakaoCert = function() {
			var certType = $('select[name=kko_certType]').val();
			if(certType == 'NOT_APPLICABLE' || certType == 'DETAIL_REF') {
				$('.kko_certCode').hide();
			} else {
				$('.kko_certCode').show();
			}
		})();
		$('select[name=kko_certType]').change(chgKakaoCert);

		// 카카오 카테고리 정보 로딩
		var getKakaoCategory;
		(getKakaoCategory = function(o) {
			var cate = $('.kko_category');
			if(!o) {
				var eq = 0;
				o = cate.eq(0);
			} else {
				// 하위 카테고리 내용 비우기
				var index = o.index('.kko_category');
				var eq = index+1;
				cate.filter(':gt('+index+')').each(function() {
					$(this).find('option:gt(0)').remove();
				});
			}
			var categoryId = o.val().replace(/@.*$/, '');
			$.get('?', {'body':'product@product_cate_kakaoTalkStore.exe', 'categoryId':categoryId}, function(r) {
				$(r).each(function() {
					cate.eq(eq).append("<option value='"+this.id+"@"+this.name+"'>"+this.name+"</option>");
				});
			});
		})();
		$('.kko_category').change(function() {
			getKakaoCategory($(this));
		});

		// 개별 판매가 사용
		var useKakaoPrc;
		(useKakaoPrc = function(o) {
			var check = $(':checkbox[name=kko_use_prc]');
			if(check.prop('checked') == true) {
				$(':input[name=talkstore_prc]').show().val(document.prdFrm.sell_prc.value);
			} else {
				$(':input[name=talkstore_prc]').hide();
			}
		})();
		$(':checkbox[name=kko_use_prc]').change(useKakaoPrc);
		</script>