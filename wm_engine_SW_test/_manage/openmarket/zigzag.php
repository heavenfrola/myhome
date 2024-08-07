<?PHP

	if(isset($cfg['use_zigzag']) == false) $cfg['use_zigzag'] = 'N';
	if(isset($cfg['add_prd_img']) == false) $cfg['add_prd_img'] = 3;
	if(isset($cfg['zigzag_image_no']) == false) $cfg['zigzag_image_no'] = 2;

	function getImageNameByNo($no) {
		switch($no) {
			case '1' : return '대이미지';
			case '2' : return '중이미지';
			case '3' : return '소이미지';
		}
		return '추가이미지'.($no-3);
	}

	$ep_product = $root_url.'/_data/compare/zigzag/engine.php?apiKey='.$cfg['zigzag_apikey'];
	$ep_cate = $root_url.'/_data/compare/zigzag/category.php?apiKey='.$cfg['zigzag_apikey'];

    $create_key_style = ($cfg['use_zigzag'] == 'Y') ? '' : 'none';
    $key_btn_name = ($cfg['zigzag_apikey']) ? '재발급' : '생성';

    // 재고관리 미적용 상품 체크
    $cnt = sql_row("select count(*) from {$tbl['product']} where ea_type=2 and stat in (2, 3)");

?>
<?if ($cnt > 0) {?>
<div class="msg_topbar warning left">
    재고관리 미사용 상품이 <?=number_format($cnt)?>개 확인되었습니다. '<strong>재고관리 사용</strong>' 설정된 상품만 지그재그 연동이 가능합니다.<br>
</div>
<br>
<?}?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="openmarket@zigzag.exe">
	<div class="box_title first">
		<h2 class="title">지그재그 연동</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버쇼핑</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label><input type="radio" name="use_zigzag" value="Y" <?=checked($cfg['use_zigzag'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_zigzag" value="N" <?=checked($cfg['use_zigzag'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td>
				<?for($i = 1; $i <= $cfg['add_prd_img']; $i++) {?>
				<label><input type="radio" name="zigzag_image_no" value="<?=$i?>" <?=checked($cfg['zigzag_image_no'], $i)?>> <?=getImageNameByNo($i)?></label>
				<?}?>
				<ul class="list_msg">
					<li>사용하지 않거나 업로드 되지 않은 이미지를 선택하시면 상품의 이미지 정보가 정상적으로 제공되지 않습니다.</li>
					<li>설정한 종류의 이미지가 업로드 되지 않은 상품일 경우 대이미지-중이미지-소이미지 순서로 등록된 이미지를 설정합니다.</li>
				</ul>
			</td>
		</tr>
		<tr class="createKey" style="display:<?=$create_key_style?>">
			<th scope="row">API키 생성</th>
			<td>
				<input type="text" name="zigzag_apikey" value="<?=$cfg['zigzag_apikey']?>" placeholder="API키를 생성해주세요." size="50" class="input" readonly>
				<?if(isset($cfg['zigzag_apikey']) == true) {?>
				<span class="box_btn_s"><input type="button" value="api키 복사" class="clipboard" data-clipboard-text="<?=$cfg['zigzag_apikey']?>"></span>
				<?}?>
				<span class="box_btn_s"><input type="button" value="<?=$key_btn_name?>" onclick="generateZigzagKey();"></span>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li><strong>'선택 옵션'</strong>,  <strong>'부속 옵션'</strong> 또는 <strong>'텍스트 옵션'</strong>이 있는 상품은 지그재그와 연동되지 않습니다.</li>
            <li>지그재그 연동 시 <strong>'숨김 카테고리'</strong>및 그 상품들도 연동됩니다. 해당 카테고리에서 노출/판매가 불가한 상품은 반드시 상품을 '숨김'처리 해주세요.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<?if($cfg['use_zigzag'] == 'Y') {?>
<div class="box_title">
	<h2 class="title">지그재그 엔진파일 생성</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">지그재그 엔진파일 생성</caption>
	<colgroup>
		<col style="width:15%">
		<col>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th>상품EP</th>
		<td><a href="<?=$ep_product?>" class="p_color" target="_blank"><?=$ep_product?></a></td>
		<td>
			<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$ep_product?>"></span>
		</td>
	</tr>
	<tr>
		<th>카테고리EP</th>
		<td><a href="<?=$ep_cate?>" class="p_color" target="_blank"><?=$ep_cate?></a></td>
		<td>
			<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$ep_cate?>"></span>
		</td>
	</tr>
</table>
<?}?>
<script type="text/javascript">
new Clipboard('.clipboard').on('success', function() {
    window.alert('복사되었습니다.');
});
function generateZigzagKey() {
	var key = $('input[name=zigzag_apikey]');
	if(key.val() == '' || confirm('API키를 다시 생성하시겠습니까?\nAPI키 변경 시 지그재그와의 연동이 해제될수 있습니다.') == true) {
		printLoading();
		$.post('./index.php', {'body':'openmarket@zigzag.exe', 'exec':'generate_key'}, function(r) {
			key.val(r);
			window.alert('API키가 생성되었습니다.');
			location.reload();
		});
	}
}

$(function() {
    $(':radio[name=use_zigzag]').on('click', function() {
        if (this.value == 'Y') $('.createKey').show();
        else $('.createKey').hide();
    });
});
</script>