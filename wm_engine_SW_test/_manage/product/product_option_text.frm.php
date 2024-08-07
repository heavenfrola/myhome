<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  텍스트 옵션 추가
	' +----------------------------------------------------------------------------------------------+*/

	$pno = numberOnly($_GET['pno']);
	$opno = numberOnly($_GET['opno']);
	$pop = $_GET['pop'];
    $stat = numberOnly($_GET['stat']);

	if($opno) { // 수정
		$title = "옵션 수정";
		$data = $pdo->assoc("select a.*, b.add_price, b.add_price_option, b.max_val, b.min_val from $tbl[product_option_set] a inner join $tbl[product_option_item] b on a.no=b.opno where a.no='$opno'");
		if(!$data['no']) msg('존재하지 않는 자료입니다.', 'close');
		$data = array_map('stripslashes', $data);
		$pno = $data['pno'];

		$attr1 = explode(',', $data['deco1']);
		foreach($attr1 as $val) {
			${'attr1_'.$val} = true;
		}
	} else { // 신규 등록
		$data['otype'] = "4B";
		$title = "옵션 추가";
	}

	$ww = ($pop) ? '670px' : '100%';
	$table_width = ($pop) ? '20%' : '12%';

?>
<form name="optFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data">
	<input type="hidden" name="body" value="product@product_option_text.exe">
	<input type="hidden" name="opno" value="<?=$data['no']?>">
	<input type="hidden" name="stat" value="<?=$stat?>">
	<input type="hidden" name="pno" value="<?=$pno?>">
	<input type="hidden" name="otype" value="4B">

	<div class="box_title hidden">
		<h2 class="title">텍스트옵션 관리</h2>
	</div>

	<table class="tbl_row" style="width:<?=$ww?>">
		<caption class="hidden"><?=$title?></caption>
		<colgroup>
			<col style="width:<?=$table_width?>">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>옵션명</strong></th>
			<td>
				<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input">
				<input type="file" name="upfile1" class="input">
				<p class="explain">이미지로 삽입을 원하실 경우 첨부해주세요</p>
				<?=delImgStr($data, 1);?>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>속성</strong></td>
			<td>
				<div id="necessaries">
					<label class="p_cursor"><input type="radio" name="necessary" value="Y" <?=checked($data['necessary'],"Y").checked($data['necessary'],"")?>> 필수</label>
					<label class="p_cursor"><input type="radio" name="necessary" value="N" <?=checked($data['necessary'],"N")?>> 선택</label>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">추가 가격</th>
			<td>
				<input type="text" name="add_price" class="input right" size="10" value="<?=parsePrice($data['add_price'])?>"> <?=$cfg['currency_type']?>
			</td>
		</tr>
		<tr>
			<td>
				입력 된 글자 길이에 따라 1자에 <input type="text" name="add_price_option" class="input right" size="10" value="<?=parsePrice($data['add_price_option'])?>"> <?=$cfg['currency_type']?> 추가
			</td>
		</tr>
		<tr>
			<th scope="row">메시지 길이</th>
			<td>
				최소 <input type="text" name="min_val" class="input right" size="5" value="<?=$data['min_val']?>"> 자 ~
				최대 <input type="text" name="max_val" class="input right" size="5" value="<?=$data['max_val']?>"> 자
				<ul class="list_msg">
					<li>빈값 혹은 0 입력시 무제한으로 처리 됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">입력 제한</th>
			<td>
				<label><input type="checkbox" name="attr1[]" value="1" <?=checked($attr1_1, true)?>> 영문 입력 제외</label><br>
				<label><input type="checkbox" name="attr1[]" value="2" <?=checked($attr1_2, true)?>> 한글 입력 제외</label><br>
				<label><input type="checkbox" name="attr1[]" value="5" <?=checked($attr1_5, true)?>> 숫자 입력 제외</label><br>
				<label><input type="checkbox" name="attr1[]" value="3" <?=checked($attr1_3, true)?>> 띄어쓰기 제외</label><br>
				<label><input type="checkbox" name="attr1[]" value="4" <?=checked($attr1_4, true)?>> 특수문자 입력 제외(!"\#$%&'()*+,\-./:;<=>?@[]^_`{|}~)</label><br>
			</td>
		</tr>
		<tr>
			<td>
				특수문자 금지시 허용 특수문자<br>
				<input type="text" class="input" name="attr2" value="<?=inputText($data['deco2'])?>">
			</td>
		</tr>
		<tr>
			<th scope="row">옵션 설명</th>
			<td>
				<input type="text" name="desc" class="input" size="67" value="<?=inputText($data['desc'])?>">
				<ul class="list_msg">
					<li>텍스트 입력을 받을 때 고객에게 안내할 메시지가 있는 경우 입력해주세요.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>