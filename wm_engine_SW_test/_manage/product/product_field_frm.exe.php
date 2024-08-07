<?PHP

	printAjaxHeader();

	$fno = numberOnly($_GET['fno']);
	if($fno) {
		$title = "항목 수정";

		$data = get_info($tbl['product_filed_set'], "no", $fno);
		if(!$data['no']) {
			alert('존재하지 않는 항목입니다.');
			javac('fdFrm.close();');
		}
	}
	else {
		$data['ftype'] = 1;
		$title = "항목 추가";
		$data['category'] = $category;
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">추가항목 관리</div>
	</div>
	<div id="popupContentArea">

		<form id="pfieldFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return checkPrdField(this)" enctype="multipart/form-data" <?=$fieldFrm_style?>>
			<input type="hidden" name="body" value="product@product_field.exe">
			<input type="hidden" name="fno" value="<?=$fno?>">
			<input type="hidden" name="exec" value="">
			<div class="box_title first">
				<h2 class="title"><?=$title?></h2>
			</div>
			<table class="tbl_row" style="width:<?=$ww?>">
				<caption class="hidden"><?=$title?></caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row"><strong>항목명</strong></th>
					<td><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input"></td>
				</tr>
				<?if($cfg['opmk_api'] == 'shopLinker') {?>
				<tr>
					<th scope="row">샵링커 상품고시 코드</th>
					<td>
						<input type="text" name="shoplinker_cd" value="<?=inputText($data['shoplinker_cd'])?>" class="input">
						<ul class="list_msg">
							<li>샵링커 연동 이용시, 연동 코드가 정상적으로 입력되어야 고시 정보가 전송됩니다.</li>
							<li><a href="./index.php?body=config@openmarket" target="_blank">설정>오픈마켓</a> 메뉴에서 최초 1회 자동으로 샵링커용 상품고시 세트를 생성하실수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<?}?>
				<tr>
					<th scope="row"><strong>형태</strong></td>
					<td>
						<p>
							<label class="p_cursor"><input type="radio" name="ftype" id="ftype" value="1" onClick="prdField()" <?=checked($data['ftype'], 1)?>>
							직접입력형</label>
						</p>
						<p>
							<label class="p_cursor"><input type="radio" name="ftype" id="ftype" value="2" onClick="prdField()" <?=checked($data['ftype'], 2)?>>
							선택형</label>
						</p>
					</td>
				</tr>
                <?php if ($scfg->comp('use_erp_interface', 'Y') == true && $scfg->comp('erp_interface_name', 'dooson') == true) { ?>
                <tr>
                    <th scope="row">두손ERP 필드</th>
                    <td>
                        <input type="text" name="doosoun_fd" value="<?=$data['doosoun_fd']?>" class="input" size="20">
                    </td>
                </tr>
                <?php } ?>
				<tr class="ftypeSelect" style="display:none;">
					<th scope="row">선택형</td>
					<td>
						<textarea name="soptions" class="txta"><?=stripslashes($data['soptions'])?></textarea>
						<p class="explain">
							<strong>"<span class="p_color2">,</span>"</strong> 로 구분하며 띄어쓰기를 포함합니다 (예 : <span class="p_color">한국,미국,일본</span> 과 <span class="p_color">한국&nbsp;&nbsp;,미국&nbsp;&nbsp;,일본</span> 은 서로 다른 결과를 보여줍니다)
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">항목 이미지</th>
					<td>
						<input type="file" name="upfile1" class="input">
						<?if($data[updir] && $data[upfile1]){
								$img = $root_url ."/".$data[updir] ."/". $data[upfile1];
								$del = delImgStr($data, 1);
								if($del) echo $del;
						?>
						<?}?>
						<p class="explain">상품상세페이지에 노출될 항목이미지입니다.</p>
					</td>
				</tr>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
				<span class="box_btn"><input type="button" value="닫기" onclick="fdFrm.close();"></span>
			</div>
		</form>
	</div>
	<script type="text/javascript">
	$(':radio[name=ftype]').change(function() {
		if(this.value == '1') $('.ftypeSelect').hide();
		else $('.ftypeSelect').show();
	});
	if("<?=$data['ftype']?>" == '2') $('.ftypeSelect').show();
	</script>
</div>