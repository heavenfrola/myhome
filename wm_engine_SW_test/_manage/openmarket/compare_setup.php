<?PHP

		$arr = array();
		if($cfg['xbig_mng'] == 'Y') {
			$arr['xbig'] = $cfg['xbig_name_mng'].' 분류';
		}
		if($cfg['ybig_mng'] == 'Y') {
			$arr['ybig'] = $cfg['ybig_name_mng'].' 분류';
		}
		$sql = $pdo->iterator("select no, name from {$tbl['product_filed_set']} where category='0' order by name asc");
        foreach ($sql as $fd) {
			$arr['field@'.$fd['no']] .= '[추가항목] '.stripslashes($fd['name']);
		}
		$time = array();
		for($ii=0;$ii<=24;$ii++) {
			$time[] = $ii;
		}

    $scfg->def('use_navershopping_book', 'N');

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return naver_chk(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="compare">
    <input type="hidden" name="compare_make_default" value="3">
	<div class="box_title first">
		<h2 class="title">네이버쇼핑</h2>
	</div>
	<div class="box_middle">
		<p class="explain left">네이버쇼핑 입점업체로 등록 하고자 할 경우 여러 업체간의 정보 비교를 위해 네이버쇼핑의 요구사항에 맞는 엔진파일을 생성하고 등록하여야 합니다.</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버쇼핑</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">엔진파일 생성</th>
			<td>
				<input type="radio" name="compare_use" id="s1" value="Y" <?=checked($cfg['compare_use'],"Y")?>> <label for="s1" class="p_cursor">사용 </label>
				<span class="explain">(먼저 네이버쇼핑에 입점 신청을 하여야 합니다)</span><br>
				<input type="radio" name="compare_use" id="s2" value="" <?=checked($cfg['compare_use'],"")?>> <label for="s2" class="p_cursor">사용 안함</label>
				<span class="explain"> (네이버쇼핑에 입점 하지 않을경우 사용하지 않는것이 좋습니다)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td>
				<input type="radio" name="compare_image_no" id="img1" value="2" <?=checked($cfg['compare_image_no'],"2")?>> <label for="img1" class="p_cursor">중간 이미지</label>
				<input type="radio" name="compare_image_no" id="img2" value="3" <?=checked($cfg['compare_image_no'],"3")?>> <label for="img2" class="p_cursor">작은 이미지</label>
				<input type="radio" name="compare_image_no" id="img3" value="1" <?=checked($cfg['compare_image_no'],"1")?>> <label for="img3" class="p_cursor">큰 이미지</label>
				<input type="radio" name="compare_image_no" id="img0" value="0" <?=checked($cfg['compare_image_no'],"0")?>> <label for="img0" class="p_cursor">네이버쇼핑 전용이미지</label>
				<ul class="list_msg">
					<li>사용하지 않거나 업로드 되지 않은 이미지를 선택하시면 상품의 이미지 정보가 네이버쇼핑에 정상적으로 제공되지 않습니다.</li>
					<li>설정한 종류의 이미지가 업로드 되지 않은 상품일 경우 대이미지-중이미지-소이미지 순서로 등록된 이미지를 찾아 네이버쇼핑에 전달합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">브랜드 연결</th>
			<td>
				<?=selectArray($arr, 'compare_brand', false, '사용안함', $cfg['compare_brand'])?>
			</td>
		</tr>
		<tr>
			<th scope="row">제외 카테고리</th>
			<td>
				<span class="box_btn_s"><a href="?body=product@catework&pgCode=2010">설정하기</a></span>
				<span class="explain">(개인결제창 카테고리로 설정하시면 해당 상품은 EP에 수록되지 않습니다)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">상품명 설정</th>
			<td>
				<input type="radio" name="compare_goods_name" value="1" id="t9" <?=checked($cfg['compare_goods_name'], "1")?>> <label for="t9" class="p_cursor">상품명</label><br>
				<input type="radio" name="compare_goods_name" value="2" id="t10" <?=checked($cfg['compare_goods_name'], "2")?>> <label for="t10" class="p_cursor">검색키워드</label><br>
				<input type="radio" name="compare_goods_name" value="3" id="t11" <?=checked($cfg['compare_goods_name'], "3")?>> <label for="t11" class="p_cursor">상품명 + 검색키워드</label>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">상품후기 갯수</th>
			<td>
				<input type="radio" name="compare_review_use" id="t12" value="Y" <?=checked($cfg['compare_review_use'],"Y")?>> <label for="t12" class="p_cursor">사용 </label>
				<ul class="list_msg">
					<li>'상품평 개수 출력' 사용 시 상품이나 후기가 많을 경우 네이버쇼핑 엔진 생성에 더 많은 시간이 소요됩니다.</li>
					<li>상품등록시 저장시간이 오래 소요된다고 판단되실 경우 옵션을 끄거나, 엔진파일 생성 방식을 '상품정보 변경시 엔진파일 업데이트 하지 않음' 으로 변경하신 후 주기적으로 수동으로 변경해 주시기 바랍니다.</li>
				</ul>
				<input type="radio" name="compare_review_use" id="t13" value="" <?=checked($cfg['compare_review_use'],"")?>> <label for="t13" class="p_cursor">사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">해외구매대행 여부</th>
			<td>
				<input type="radio" name="import_flag_use" id="t14" value="Y" <?=checked($cfg['import_flag_use'],"Y")?>> <label for="t14" class="p_cursor">사용 </label>
				<input type="radio" name="import_flag_use" id="t15" value="" <?=checked($cfg['import_flag_use'],"")?>> <label for="t15" class="p_cursor">사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">오늘출발 사용여부</th>
			<td>
				<input type="radio" name="compare_today_start_use" id="t16" value="Y" <?=checked($cfg['compare_today_start_use'],"Y")?>> <label for="t16" class="p_cursor">사용 </label>
				( 주문마감시간 : <?=selectArray($time, 'compare_today_time', false, '주문마감시간', $cfg['compare_today_time'])?>  시 )
				<ul class="list_msg">
					<li>오늘출발 사용여부에 따라 상품 등록/수정 시 상품 별 '네이버쇼핑 오늘출발 설정'이 가능합니다.</li>
					<li>주문마감시간을 일괄적으로 설정할 수 있습니다.</li>
				</ul>
				<input type="radio" name="compare_today_start_use" id="t17" value="" <?=checked($cfg['compare_today_start_use'],"")?>> <label for="t17" class="p_cursor">사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">도서EP 사용여부</th>
			<td>
				<label><input type="radio" name="use_navershopping_book" value="Y" <?=checked($cfg['use_navershopping_book'],"Y")?>> 사용함 </label>
				<label><input type="radio" name="use_navershopping_book" value="N" <?=checked($cfg['use_navershopping_book'],"N")?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info left">
			<li>상품DB는 매일 자정에 자동으로 네이버쇼핑 처리를 위한 서버에 저장되며, 네이버쇼핑 업데이트 주기마다 서버에 접근하여 상품이 수집됩니다.</li>
			<li>네이버쇼핑 상품 업데이트 현황은 네이버쇼핑 관리자 > 상품관리 > 상품정보 수신 현황 > 상품DB 업데이트 현황에서 확인할 수 있습니다.</li>
		</ul>
		<br>
		<div class="list_info">
			<p class="title">[네이버쇼핑 업데이트 주기 안내]</p>
			<p>
				하루 수신회수와 주기는 몰등급에 따라 상이하며, 기본 4회(01시/10시/14시/18시)로 DB 업데이트가 진행되며, 네이버쇼핑 고객센터를 통해 수신주기 및 수신시간을 변경할 수 있습니다.<br>
				(※ 설정 가능한 시간은 01:00 ~ 18:00)
			</p>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?
	include $engine_dir."/_manage/openmarket/compare_engine_url.php";
	if($cfg['compare_use'] != 'Y') $disabled = 'disabled';
?>
<form name="ogFrm" method="post" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="compare_tracker">
	<div class="box_title">
		<h2 class="title">네이버쇼핑 ROI 트래커</h2>
	</div>
	<div class="box_middle left">
		<dl>
			<dt class="p_color2">ROI 트래커란?</dt>
			<dd>ROI Tracker 란 '네이버쇼핑'에 게시한 상품이 소비자에게 얼마나 노출되고 있고, 소비자들이 어떤 상품을 클릭하여 귀사에 방문을 하며, 얼마나 구매하는지에 대한 리포트 제공을 위한 트래킹 툴입니다.</dd>
			<dd>
				ROI 트래커 사용시 다음과 같은 장점이 있습니다.
				<ul class="list_msg">
					<li>귀사의 상품 현황에 대한 정확한 효과 파악이 가능합니다.</li>
					<li>상품 개개별로 효율 확인이 가능하기 때문에 노출/클릭/주문 상관관계 분석을 통한 상품별 광고 계획 수립이 가능합니다.</li>
					<li>카테고리별 차별화된 마케팅이 가능합니다.</li>
					<li>부가광고에 대한 정확한 효율 측정이 가능합니다.</li>
					<li>손쉬운 광고 및 노출 영역별 효율 측정이 가능하도록 확장하실 수 있습니다.</li>
				</ul>
			</dd>
			<dd>
				ROI 트래커의 결과는 네이버쇼핑 관리자에서 확인 가능합니다. <a href="javascript:;" onclick="layTgl(document.getElementById('roi_sample'))" class="p_color">적용 예시화면</a>
				<div id="roi_sample" style="display:none;">
					<img src="<?=$engine_url?>/_manage/image/openmarket/mm_roi.gif" alt="네이버 ROI 트래커 적용예시">
				</div>
			</dd>
		</dl>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버쇼핑 ROI 트래커</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="roi_use" value="N" checked> 미사용</label>
				<label class="p_cursor"><input type="radio" name="roi_use" value="Y" <?=checked($cfg['roi_use'], 'Y')?> <?=$disabled?>> 사용</label>
				<p class="explain">네이버쇼핑 사용중일때만 선택가능합니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">체크기간</th>
			<td>
				<select name="roi_term">
					<?for($i = 30; $i >= 3; $i--) {?>
					<option value="<?=$i?>" <?=checked($cfg['roi_term'], $i, 1)?>><?=$i?> 일</option>
					<?}?>
				</select>
				내로 구매한 고객만 구매정보 전송
				<ul class="list_msg">
					<li>네이버쇼핑을 통해 상품에 접근한 고객이 몇일 내에 구매를 해야 구매결과를 전송할지 설정합니다.</li>
					<li>네이버의 권장 설정값은 <span class="p_color2">30일</span>입니다. 작게 설정하시면 광고효과가 낮게 평가될 수 있습니다.</li>
					<li>설정 변경을 하셔도 설정 이전 고객들은 새로운 설정값의 적용을 받지 않으며, 새롭게 네이버쇼핑에 들어오신 고객들에게만 새로운 설정이 적용됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function naver_chk(f) {
	if(f.compare_today_start_use.value=="Y") {
		if(f.compare_today_time.value=="") {
			alert("오늘출발 사용 시 주문마감시간을 설정해주세요.");
			return false;
		}
	}
    printLoading();
}
</script>