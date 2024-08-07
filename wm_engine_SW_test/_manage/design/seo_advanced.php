<?PHP

	// DB에 있는 설정 가져 오기
	if(isTable($tbl['seo_config'])) {
		$seores = $pdo->iterator("select * from {$tbl['seo_config']}");
        foreach ($seores as $seodata) {
			${$seodata['tag_type']}[$seodata['page']] = array_map('stripslashes', $seodata);
		}
	}

	// 상품 이미지 이름
	$prd_image_ea = ($cfg['add_prd_img'] > 3) ? $cfg['add_prd_img'] : 3;
	$prd_image_names = array(
		1 => '대이미지',
		2 => '중이미지',
		3 => '소이미지',
	);
	for($i = 4; $i <= $prd_image_ea; $i++) {
		$prd_image_names[$i] = '추가이미지 '.($i-3);
	}

	// 각종 기본 값
	if(!$og['common']['image_use']) $og['common']['image_use'] = 'N';
	if(!$og['prdList']['image_use']) $og['prdList']['image_use'] = 'N';
	if(!$og['prdDetail']['image_use']) $og['prdDetail']['image_use'] = 'N';
	if(!$og['boardList']['image_use']) $og['boardList']['image_use'] = 'N';
	if(!$og['boardView']['image_use']) $og['boardView']['image_use'] = 'N';

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">

	<div class="box_middle3 left">
		<p class="explain">
			<i class="icon_info"></i>
			고급설정 사용 시 일반설정 내용은 적용되지 않습니다.
		</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">SEO 고급 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th>사용여부</th>
			<td>
				<label><input type="radio" name="use_seo_advanced" value="Y" <?=checked($cfg['use_seo_advanced'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_seo_advanced" value="N" <?=checked($cfg['use_seo_advanced'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="seo1" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@seo.exe">
	<input type="hidden" name="tag_type" value="meta">

	<div class="box_title">
		<h2 class="title">주요 페이지 SEO 태그 설정</h2>
	</div>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class="active"><a href="#common" onclick="return chgMetaTabs(this);">공통</a></li>
			<li><a href="#prdList" onclick="return chgMetaTabs(this);">상품분류</a></li>
			<li><a href="#prdDetail" onclick="return chgMetaTabs(this);">상품상세</a></li>
			<li><a href="#boardList" onclick="return chgMetaTabs(this);">게시판</a></li>
			<li><a href="#boardView" onclick="return chgMetaTabs(this);">게시물</a></li>
		</ul>
	</div>

	<table class="tbl_row seo_common">
		<caption class="hidden">주요 페이지 SEO 태그 설정(공통)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">타이틀(title)</th>
			<td>
				<input type="text" name="common_title" class="input block" value="<?=$meta['common']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 설명(description)</th>
			<td>
				<textarea name="common_description" class="txta" rows="5"><?=$meta['common']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 키워드(keyword)</th>
			<td>
				<input type="text" name="common_keyword" class="input  block" value="<?=$meta['common']['keyword']?>">
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_prdList" style="display:none;">
		<caption class="hidden">주요 페이지 SEO 태그 설정(상품분류)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">타이틀(title)</th>
			<td>
				<input type="text" name="prdList_title" class="input block" value="<?=$meta['prdList']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 설명(description)</th>
			<td>
				<textarea name="prdList_description" class="txta" rows="5"><?=$meta['prdList']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 키워드(keyword)</th>
			<td>
				<input type="text" name="prdList_keyword" class="input block" value="<?=$meta['prdList']['keyword']?>">
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_prdDetail" style="display:none;">
		<caption class="hidden">주요 페이지 SEO 태그 설정(상품상세)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">타이틀(title)</th>
			<td>
				<input type="text" name="prdDetail_title" class="input block" value="<?=$meta['prdDetail']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 설명(description)</th>
			<td>
				<textarea name="prdDetail_description" class="txta" rows="5"><?=$meta['prdDetail']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 키워드(keyword)</th>
			<td>
				<input type="text" name="prdDetail_keyword" class="input block" value="<?=$meta['prdDetail']['keyword']?>">
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_boardList" style="display:none;">
		<caption class="hidden">주요 페이지 SEO 태그 설정(게시판)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">타이틀(title)</th>
			<td>
				<input type="text" name="boardList_title" class="input block" value="<?=$meta['boardList']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 설명(description)</th>
			<td>
				<textarea name="boardList_description" class="txta" rows="5"><?=$meta['boardList']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 키워드(keyword)</th>
			<td>
				<input type="text" name="boardList_keyword" class="input block" value="<?=$meta['boardList']['keyword']?>">
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_boardView" style="display:none;">
		<caption class="hidden">주요 페이지 SEO 태그 설정(게시물)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">타이틀(title)</th>
			<td>
				<input type="text" name="boardView_title" class="input block" value="<?=$meta['boardView']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 설명(description)</th>
			<td>
				<textarea name="boardView_description" class="txta" rows="5"><?=$meta['boardView']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">메타태그 키워드(keyword)</th>
			<td>
				<input type="text" name="boardView_keyword" class="input block" value="<?=$meta['boardView']['keyword']?>">
			</td>
		</tr>
	</table>

	<div class="box_middle2 left">
		<p class="explain">
			<i class="icon_info"></i>
			정의된 치환문자를 활용하여 주요 페이지 SEO 태그 설정이 가능합니다.
			<span class="box_btn_s"><input type="button" value="치환문자 확인" class="code_btn" onclick="openReplaceCode(this);"></span>
		</p>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="seo2" method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@seo.exe">
	<input type="hidden" name="tag_type" value="og">

	<div class="box_title">
		<h2 class="title">오픈그래프 태그 설정</h2>
	</div>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class="active"><a href="#common" onclick="return chgMetaTabs(this);">공통</a></li>
			<li><a href="#prdList" onclick="return chgMetaTabs(this);">상품분류</a></li>
			<li><a href="#prdDetail" onclick="return chgMetaTabs(this);">상품상세</a></li>
			<li><a href="#boardList" onclick="return chgMetaTabs(this);">게시판</a></li>
			<li><a href="#boardView" onclick="return chgMetaTabs(this);">게시물</a></li>
		</ul>
	</div>

	<table class="tbl_row seo_common">
		<caption class="hidden">오픈그래프 태그 설정(공통)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">대표제목(og:title)</th>
			<td>
				<input type="text" name="common_title" class="input block" value="<?=$og['common']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">대표설명(og:description)</th>
			<td>
				<textarea name="common_description" class="txta" rows="5"><?=$og['common']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">대표이미지(og:image)</th>
			<td>
				<label><input type="radio" name="common_image_use" value="N" <?=checked($og['common']['image_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="common_image_use" value="Y" <?=checked($og['common']['image_use'], 'Y')?>> 파일업로드</label>
				<p>
					<input type="file" name="common_upfile1" class="input" size="100">
					<?=delImgStr($og['common'], 1)?>
				</p>
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_prdList" style="display:none;">
		<caption class="hidden">오픈그래프 태그 설정(상품분류)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">대표제목(og:title)</th>
			<td>
				<input type="text" name="prdList_title" class="input block" value="<?=$og['prdList']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">대표설명(og:description)</th>
			<td>
				<textarea name="prdList_description" class="txta" rows="5"><?=$og['prdList']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">대표이미지(og:image)</th>
			<td>
				<label><input type="radio" name="prdList_image_use" value="N" <?=checked($og['prdList']['image_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="prdList_image_use" value="Y" <?=checked($og['prdList']['image_use'], 'Y')?>> 파일업로드</label>
				<label><input type="radio" name="prdList_image_use" value="T" <?=checked($og['prdList']['image_use'], 'T')?>> 타이틀 이미지</label>
				<label><input type="radio" name="prdList_image_use" value="A" <?=checked($og['prdList']['image_use'], 'A')?>> 자동선택</label>
				<ul class="list_info">
					<li>타이틀 이미지 : 분류 설정에 등록되어있는 타이틀 이미지를 사용합니다.</li>
					<li>자동선택 : 분류에 등록되어있는 첫 번째 상품의 소이미지를 사용합니다.</li>
				</ul>
				<p>
					<input type="file" name="prdList_upfile1" class="input" size="100">
					<?=delImgStr($og['prdList'], 1)?>
				</p>
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_prdDetail" style="display: none;">
		<caption class="hidden">오픈그래프 태그 설정(상품상세)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">대표제목(og:title)</th>
			<td>
				<input type="text" name="prdDetail_title" class="input block" value="<?=$og['prdDetail']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">대표설명(og:description)</th>
			<td>
				<textarea name="prdDetail_description" class="txta" rows="5"><?=$og['prdDetail']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">대표이미지(og:image)</th>
			<td>
				<label><input type="radio" name="prdDetail_image_use" value="N" <?=checked($og['prdDetail']['image_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="prdDetail_image_use" value="Y" <?=checked($og['prdDetail']['image_use'], 'Y')?>> 파일업로드</label>
				<?for($i = 1; $i <= $prd_image_ea; $i++) {?>
				<label><input type="radio" name="prdDetail_image_use" value="<?=$i?>" <?=checked($og['prdDetail']['image_use'], $i)?>> <?=$prd_image_names[$i]?></label>
				<?}?>
				<p>
					<input type="file" name="prdDetail_upfile1" class="input" size="100">
					<?=delImgStr($og['prdDetail'], 1)?>
				</p>
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_boardList" style="display: none;">
		<caption class="hidden">오픈그래프 태그 설정(게시판)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">대표제목(og:title)</th>
			<td>
				<input type="text" name="boardList_title" class="input block" value="<?=$og['boardList']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">대표설명(og:description)</th>
			<td>
				<textarea name="boardList_description" class="txta" rows="5"><?=$og['boardList']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">대표이미지(og:image)</th>
			<td>
				<label><input type="radio" name="boardList_image_use" value="N" <?=checked($og['boardList']['image_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="boardList_image_use" value="Y" <?=checked($og['boardList']['image_use'], 'Y')?>> 파일업로드</label>
				<p>
					<input type="file" name="boardList_upfile1" class="input" size="100">
					<?=delImgStr($og['boardList'], 1)?>
				</p>
			</td>
		</tr>
	</table>

	<table class="tbl_row seo_boardView" style="display: none;">
		<caption class="hidden">오픈그래프 태그 설정(게시물)</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">대표제목(og:title)</th>
			<td>
				<input type="text" name="boardView_title" class="input block" value="<?=$og['boardView']['title']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">대표설명(og:description)</th>
			<td>
				<textarea name="boardView_description" class="txta" rows="5"><?=$og['boardView']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">대표이미지(og:image)</th>
			<td>
				<label><input type="radio" name="boardView_image_use" value="N" <?=checked($og['boardView']['image_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="boardView_image_use" value="Y" <?=checked($og['boardView']['image_use'], 'Y')?>> 파일업로드</label>
				<?for($i = 1; $i <= 4; $i++) {?>
				<label><input type="radio" name="boardView_image_use" value="<?=$i?>" <?=checked($og['boardView']['image_use'], $i)?>> 첨부파일 <?=$i?></label>
				<?}?>
				<p>
					<input type="file" name="boardView_upfile1" class="input" size="100">
					<?=delImgStr($og['boardView'], 1)?>
				</p>
			</td>
		</tr>
	</table>

	<div class="box_middle2 left">
		<p class="explain">
			<i class="icon_info"></i>
			정의된 치환문자를 활용하여 오픈그래프 태그 설정이 가능합니다.
			<span class="box_btn_s"><input type="button" value="치환문자 확인" class="code_btn" onclick="openReplaceCode(this);"></span>
		</p>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<div class="layer_view seo common" style="display:none;">
	<dl>
		<dt>(공통) 치환문자 안내</dt>
		<dd>
			<table class="tbl_inner full line">
				<caption class="hidden">(공통) 치환문자 안내</caption>
				<colgroup>
					<col style="width:200px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th scope="row">치환문자</th>
					<th scope="row">설명</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="left">{쇼핑몰명}</td>
					<td class="left">관리자 > 설정 > 일반설정 > 국가별 설정 내  설정된 쇼핑몰명이  출력됩니다.</td>
				</tr>
			</table>
		</dd>
	</dl>
	<a onclick="$('.layer_view.seo').hide();" class="close"></a>
</div>

<div class="layer_view seo prdList" style="display:none;">
	<dl>
		<dt>(상품분류) 치환문자 안내</dt>
		<dd>
			<table class="tbl_inner full line">
				<caption class="hidden">(상품분류) 치환문자 안내</caption>
				<colgroup>
					<col style="width:200px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th scope="row">치환문자</th>
					<th scope="row">설명</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="left">{쇼핑몰명}</td>
					<td class="left">관리자 > 설정 > 일반설정 > 국가별 설정 내  설정된 쇼핑몰명이  출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{분류명}</td>
					<td class="left">현재 접속한 분류에 설정된 분류명이 출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{상세분류명}</td>
					<td class="left">
						현재 접속한 분류의  대>중>소>세 분류명이 출력됩니다.<br>
						* 예)  아우터 > 코트/자켓
					</td>
				</tr>
			</table>
		</dd>
	</dl>
	<a onclick="$('.layer_view.seo').hide();" class="close"></a>
</div>

<div class="layer_view seo prdDetail" style="display:none;">
	<dl>
		<dt>(상품상세) 치환문자 안내</dt>
		<dd>
			<table class="tbl_inner full line">
				<caption class="hidden">(상품상세) 치환문자 안내</caption>
				<colgroup>
					<col style="width:200px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th scope="row">치환문자</th>
					<th scope="row">설명</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="left">{쇼핑몰명}</td>
					<td class="left">관리자 > 설정 > 일반설정 > 국가별 설정 내  설정된 쇼핑몰명이  출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{상품명}</td>
					<td class="left">상품에 설정된 상품명이 출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{참고상품명}</td>
					<td class="left">상품에 설정된 참고상품명이 출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{분류명}</td>
					<td class="left">상품이 속한 분류명이 출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{요약설명}</td>
					<td class="left">상품에  설정된 요약설명이 출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{검색키워드}</td>
					<td class="left">상품에 설정된 검색키워드가 출력됩니다.</td>
				</tr>
			</table>
		</dd>
	</dl>
	<a onclick="$('.layer_view.seo').hide();" class="close"></a>
</div>

<div class="layer_view seo boardList" style="display:none;">
	<dl>
		<dt>(게시판) 치환문자 안내</dt>
		<dd>
			<table class="tbl_inner full line">
				<caption class="hidden">(게시판) 치환문자 안내</caption>
				<colgroup>
					<col style="width:200px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th scope="row">치환문자</th>
					<th scope="row">설명</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="left">{쇼핑몰명}</td>
					<td class="left">관리자 > 설정 > 일반설정 > 국가별 설정 내  설정된 쇼핑몰명이  출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{게시판명}</td>
					<td class="left">
						관리자 > 게시판 > 게시판 > 게시판 관리에 설정된 게시판명이 출력됩니다.<br>
						* 상품Q&A의 경우 상품Q&A, 상품후기의 경우 상품후기로 출력됩니다.
					</td>
				</tr>
			</table>
		</dd>
	</dl>
	<a onclick="$('.layer_view.seo').hide();" class="close"></a>
</div>

<div class="layer_view seo boardView" style="display:none;">
	<dl>
		<dt>(게시물) 치환문자 안내</dt>
		<dd>
			<table class="tbl_inner full line">
				<caption class="hidden">(게시물) 치환문자 안내</caption>
				<colgroup>
					<col style="width:200px;">
					<col>
				</colgroup>
				<thead>
				<tr>
					<th scope="row">치환문자</th>
					<th scope="row">설명</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="left">{쇼핑몰명}</td>
					<td class="left">관리자 > 설정 > 일반설정 > 국가별 설정 내  설정된 쇼핑몰명이  출력됩니다.</td>
				</tr>
				<tr>
					<td class="left">{게시판명}</td>
					<td class="left">
						관리자 > 게시판 > 게시판 > 게시판 관리에 설정된 게시판명이 출력됩니다.<br>
						* 상품Q&A의 경우 상품Q&A, 상품후기의 경우 상품후기로 출력됩니다.
					</td>
				</tr>
				<tr>
					<td class="left">{게시물제목}</td>
					<td class="left">게시물의 제목이 출력됩니다.</td>
				</tr>
			</table>
		</dd>
	</dl>
	<a onclick="$('.layer_view.seo').hide();" class="close"></a>
</div>

<script type="text/javascript">
function chgMetaTabs(o) {
	// 탭
	var f = $(o).parents('form');
	f.find('.tab_sort>li').removeClass('active');
	$(o).parent().addClass('active');

	// 테이블
	var type = o.href.split('#');
	f.find('.tbl_row').hide();
	f.find('.tbl_row.seo_'+type[1]).show();

	$('.layer_view.seo').each(function() {
		if($(this).css('display') == 'block') {
			$('.layer_view.seo').hide();
			openReplaceCode(o);
			return;
		}
	});
}

function openReplaceCode(o) {
	var f = $(o).parents('form');

	var selectedType = f.find('.tab_sort>li.active>a').prop('href').split('#');
	var layer = $('.layer_view.seo.'+selectedType[1]);

	$('.layer_view.seo').not(layer).hide();
	layer.toggle();
	layer.css('top', f.find('.code_btn').offset().top+40);
}
</script>