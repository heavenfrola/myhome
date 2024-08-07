<?PHP
	include_once $engine_dir."/_engine/include/design.lib.php";

	$_langs = array(
		'kor' => '한국어',
		'eng' => '영어',
		'ch1' => '중국어(간체)',
		'ch2' => '중국어(번체)',
		'jpn' => '일본어',
	);

	$odir=opendir($root_dir."/_skin");

	$_skin_arr=$_m_skin_arr=array();
	while($arr=readdir($odir)){
		if(is_dir($root_dir."/_skin/".$_dir) && $arr != "." && $arr != ".."){
			if(!skinFormatChk($arr)) continue;

			if(substr($arr, 0, 2) =='m_'){
				$_m_skin_arr[]=$arr;
			}else{
				$_skin_arr[]=$arr;
			}
		}
	}
	sort($_skin_arr);
	sort($_m_skin_arr);

	$_skin_dir=$root_dir."/_skin";
?>
<script type="text/javascript">
var r_currency_list = new layerWindow('config@reference_currency.exe');
</script>
<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@multi_shop.exe">
	<div class="box_title first">
		<h2 class="title">국가별 사용설정</h2>
	</div>
	<table class="tbl_row multi_shop">
		<caption class="hidden">국가별 사용설정</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:85%">
		</colgroup>
		<tr>
			<th scope="row">쇼핑몰명</th>
			<td>
				<input type="text" name="company_mall_name" class="input input_full" value="<?=inputText($cfg['company_mall_name'])?>">
				<span>쇼핑몰 관리용 명칭입니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">언어선택</th>
			<td class="language">
				<label class="p_cursor"><input type="radio" name="language_pack" value="kor" <?=checked($cfg['language_pack'], 'kor')?>> 한국어</label>
				<label class="p_cursor"><input type="radio" name="language_pack" value="eng" <?=checked($cfg['language_pack'], 'eng')?>> 영어</label>
				<label class="p_cursor"><input type="radio" name="language_pack" value="ch1" <?=checked($cfg['language_pack'], 'ch1')?>> 중국어(간체)</label>
				<label class="p_cursor"><input type="radio" name="language_pack" value="ch2" <?=checked($cfg['language_pack'], 'ch2')?>> 중국어(번체)</label>
				<label class="p_cursor"><input type="radio" name="language_pack" value="jpn" <?=checked($cfg['language_pack'], 'jpn')?>> 일본어</label>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">결제화폐</th>
			<td>
				<?=radioArray($_currency, 'currency_type', 2, $cfg['currency_type'], "setCurrency(this);")?>
				<input type="hidden" value="<?=$cfg['currency_decimal']?>" name="currency_decimal">
			</td>
		</tr>
		<tr>
			<td>
				<div>
					표기방법
					<input type="text" name="currency" class="input" size="5" value="<?=$cfg['currency']?>" style="text-align:center;" onkeyup="setCurrency(this);">
					<label class="p_cursor"><input type="radio" name="currency_position" value="F" <?=checked($cfg['currency_position'], 'F')?> onclick="ex_currency(1)"> 금액 앞 표기</label>
					<label class="p_cursor"><input type="radio" name="currency_position" value="B" <?=checked($cfg['currency_position'], 'B')?> onclick="ex_currency(2)"> 금액 뒤 표기</label>
					<span class="p_color2">미리보기) <span id="ex1">10,000</span><span class="currency_type_txt"><?=$cfg["currency"]?></span><span id="ex2">10,000</span></span>
					<span class="explain p_color">(사용자 페이지에 표시됩니다.)</span>
				</div>
				<div class="example">
					<ul class="list_msg">
						<li><span class="p_color2">쇼핑몰 운영 중</span> 결제화폐 설정을 변경할 경우 <a href="/_manage/?body=member@member_group">회원그룹</a>구매금액 조건 설정이 변동될 수 있습니다.</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">참조화폐</th>
			<td>
				<?=radioArray($_currency, 'r_currency_type', 2, $cfg['r_currency_type'],'setCurrency(this)')?>

				<!--<label class="p_cursor"><input type="radio" name="r_currency_type" value="custom" <?=$cfg['r_currency_type']&&!in_array($cfg['r_currency_type'],$_currency)?'checked':''?> onclick="setCurrency(this)"> 사용자 설정</label>-->

				<label class="p_cursor"><input type="radio" name="r_currency_type" value="" <?=checked($cfg['r_currency_type'], '')?> onclick="setCurrency(this)"> 사용안함</label>

				&nbsp;&nbsp;&nbsp;<label class="p_cusor"><input type="checkbox" value="Y" name="use_r_currency_custom" id="use_r_currency_custom" <?=checked($cfg['use_r_currency_custom'], 'Y')?> /> 사용자 정의</label>
				<span class="r_currency_custom_area" <?=$cfg['use_r_currency_custom']!='Y'?'style="display:none;"':''?>>
					<input type="text" class="input" name="r_currency_type_custom" value="<?=$cfg['r_currency_type_custom']?>"  size="5" maxlength="3"/>
					<span class="box_btn_s"><input type="button" value="국가별 화폐 선택" onclick="r_currency_list.open()"/></span>

					<input type="hidden" value="<?=$cfg['r_currency_decimal']?>" name="r_currency_decimal">
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<div class="r_currency_area" <?=$cfg['r_currency_type']=='N'?'style="display:none;"':''?>>
					표기방법
					<input type="text" name="r_currency" class="input" size="5" value="<?=$cfg['r_currency']?>" style="text-align:center;" onkeyup="setCurrency(this);">
					<label class="p_cursor"><input type="radio" name="r_currency_position" value="F" <?=checked($cfg['r_currency_position'], 'F')?> onclick="ex_currency(3)"> 금액 앞 표기</label>
					<label class="p_cursor"><input type="radio" name="r_currency_position" value="B" <?=checked($cfg['r_currency_position'], 'B')?> onclick="ex_currency(4)"> 금액 뒤 표기</label>
					<span class="p_color2">미리보기) <span id="ex3">10,000</span><span class="r_currency_type_txt"><?=$cfg["r_currency"]?></span><span id="ex4">10,000</span></span>
					<span class="explain p_color">(사용자 페이지에 표시됩니다.)</span>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">관리화폐</th>
			<td>
				<?=radioArray($_currency, 'm_currency_type', 2, $cfg['m_currency_type'],'setCurrency(this)')?>
				<label class="p_cursor"><input type="radio" name="m_currency_type" value="N" <?=checked($cfg['m_currency_type'], 'N')?> onclick="setCurrency(this)"> 사용안함</label>
				<input type="hidden" value="<?=$cfg['m_currency_decimal']?>" name="m_currency_decimal">
			</td>
		</tr>
		<tr class="m_currency_area" <?=$cfg['m_currency_type']=='N'?'style="display:none;"':''?>>
			<th scope="row">기준환율</th>
			<td>
				결제화폐 : <input type="text" name="cur_sell_price" id="cur_sell_price" class="input input_data input_won" data-decimal="<?=$cfg['currency_decimal']?>" value="<?=$cfg['cur_sell_price']?>" style="width:10%;">
				<span class="currency_type_txt"><?=$cfg['currency']?></span>
				<span style="font-weight:bold;font-size:20px;">&nbsp;&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;</span>
				관리화폐 : <input type="text" name="cur_manage_price" id="cur_manage_price" class="input input_data input_won" data-decimal="<?=$cfg['m_currency_decimal']?>" value="<?=$cfg['cur_manage_price']?>" style="width:10%;">
				<span class="m_currency_type_txt"><?=$cfg['m_currency_type']?></span>
			</td>
		</tr>
		<tr>
			<th scope="row">원가화폐</th>
			<td>
				<?=radioArray($_currency, 'b_currency_type', 2, $cfg['b_currency_type'],'setCurrency(this)')?>
				<input type="hidden" value="<?=$cfg['b_currency_decimal']?>" name="b_currency_decimal">
			</td>
		</tr>
		<tr>
			<th scope="row">배송국가</th>
			<td>
				<label class="p_cursor"><input type="radio" name="delivery_fee_type" value="D" <?=checked($cfg['delivery_fee_type'], 'D')?>> 국내 배송</label>
				<label class="p_cursor"><input type="radio" name="delivery_fee_type" value="O" <?=checked($cfg['delivery_fee_type'], 'O')?>> 해외 배송</label>
				<label class="p_cursor"><input type="radio" name="delivery_fee_type" value="A" <?=checked($cfg['delivery_fee_type'], 'A')?>> 국내/해외 배송</label>
			</td>
		</tr>
		<?php
		if(file_exists($_skin_dir."/config.".$_skin_ext['g'])){
			include_once $_skin_dir."/config.".$_skin_ext['g'];
		}else{
			$design['version']="V3";
		}
		?>
		<tr>
			<th scope="row">PC스킨</td>
			<td>
				<select name="skin" id="skin" class="select_multi">
					<option value="">- PC 사용스킨 선택 -</option>
					<?php foreach($_skin_arr as $k=>$v) { ?>
					<?php
						$_ds=$design["sn_".$v];
						$_skin_comment=($_ds) ? $_ds : "";
					?>
					<option value="<?=$v?>" <?=$design['skin'] == $v?'selected':''?>><?=$v?><?=$_skin_comment?'('.$_skin_comment.')':''?></option>
					<?php } ?>
				</select>
				<span class="box_btn_s"><input type="button" value="미리보기" onclick="skin_preview('skin')"></span>
				<span class="box_btn_s"><input type="button" value="스킨 전체보기" onclick="location.href='/_manage/?body=design@skin';"/></span>
				<input type="hidden" value="<?=$design['skin']?>" name="design_skin"/>
				<input type="hidden" value="<?=$design['version']?>" name="design_version"/>
				<input type="hidden" value="<?=$design['edit_skin']?>" name="design_edit_skin"/>
			</td>
		</tr>
		<?php
		if($cfg['mobile_use'] == 'Y') {
			unset($design);
			if(file_exists($_skin_dir."/mconfig.".$_skin_ext['g'])){
				include_once $_skin_dir."/mconfig.".$_skin_ext['g'];
			}else{
				$design['version']="mobile";
			}
		?>
		<tr>
			<th scope="row">모바일스킨</td>
			<td>
				<select name="mskin" id="mskin" class="select_multi">
					<option value="">- 모바일 사용스킨 선택 -</option>
					<?php foreach($_m_skin_arr as $k=>$v) { ?>
					<?php
						$_ds=$design["sn_".$v];
						$_skin_comment=($_ds) ? $_ds : "";
					?>
					<option value="<?=$v?>" <?=$design['skin'] == $v?'selected':''?>><?=$v?><?=$_skin_comment?'('.$_skin_comment.')':''?></option>
					<?php } ?>
				</select>
				<span class="box_btn_s"><input type="button" value="미리보기" onclick="skin_preview('mskin')"></span>
				<span class="box_btn_s"><input type="button" value="스킨 전체보기" onclick="location.href='/_manage/?body=wmb@skin';"/></span>
				<input type="hidden" value="<?=$design['skin']?>" name="mdesign_skin"/>
				<input type="hidden" value="<?=$design['version']?>" name="mdesign_version"/>
				<input type="hidden" value="<?=$design['edit_skin']?>" name="mdesign_edit_skin"/>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">아이콘</th>
			<td class="icon">
				<label class="p_cursor"><input type="radio" name="flag" value="multi_kor.jpg" <?=checked($cfg['flag'], 'multi_kor.jpg')?>> <img src="<?=$engine_url?>/_manage/image/common/multi_kor.jpg" alt=""></label>
				<label class="p_cursor"><input type="radio" name="flag" value="multi_eng.jpg" <?=checked($cfg['flag'], 'multi_eng.jpg')?>> <img src="<?=$engine_url?>/_manage/image/common/multi_eng.jpg" alt=""></label>
				<label class="p_cursor"><input type="radio" name="flag" value="multi_jpn.jpg" <?=checked($cfg['flag'], 'multi_jpn.jpg')?>> <img src="<?=$engine_url?>/_manage/image/common/multi_jpn.jpg" alt=""></label>
				<label class="p_cursor"><input type="radio" name="flag" value="multi_chi.jpg" <?=checked($cfg['flag'], 'multi_chi.jpg')?>> <img src="<?=$engine_url?>/_manage/image/common/multi_chi.jpg" alt=""></label>
				<label class="p_cursor"><input type="radio" name="flag" value="multi_thi.jpg" <?=checked($cfg['flag'], 'multi_thi.jpg')?>> <img src="<?=$engine_url?>/_manage/image/common/multi_thi.jpg" alt=""></label><br>
				<label class="p_cursor"><input type="radio" name="flag" value="user" <?=checked($cfg['flag'], 'user')?>> 직접업로드
				<?php if ($cfg['flag'] == 'user') { ?>
				<img src="<?=$cfg['flag_url']?>" alt="" width="30" style="vertical-align:middle;">
				<?php } ?>
				</label><br>
				<input type="file" name="userflag" class="input input_full">
				<?php if ($cfg['flag'] == 'user') { ?>
					<span class="box_btn_s blue"><a href="<?=$cfg['flag_url']?>" target="_blank">미리보기</a></span>
				<?php } ?>
				<span class="p_color">이미지 사이즈 30px * 30px</span>
				<div class="example">
					<ul class="list_msg">
						<li>브라우저 상단의 멀티샵 표시 아이콘으로 사용됩니다.</li>
						<li>대표이미지를 원하는 이미지로 등록가능 합니다.</li>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function setCurrency(o) {
		var _name = o.name.replace('_type','');
		if($('.'+_name+'_area').length > 0) $('.'+_name+'_area').show();

		if(eval("o.form."+_name)) eval("o.form."+_name).value = o.value;

		if(o.value == 'N' || o.value == ''){
			eval("o.form."+_name+"_decimal").value = '0';
			if($('.'+_name+'_type_area').length > 0) $('.'+_name+'_type_area').hide();
		}else{
			var jsArr = <?=json_encode($_currency_decimal)?>;
			var _price_name = (_name=='currency')?"cur_sell_price":"cur_manage_price";
			eval("o.form."+_price_name).setAttribute('data-decimal',jsArr[o.value]);

			if(o.type != 'text') {
				eval("o.form."+_name+"_decimal").value = jsArr[o.value];
			}

			$("."+_name+"_type_txt").html(o.value);
		}
	}

	function skin_preview(p){
		var _skin_name = $('#'+p).val();
		if(!_skin_name){
			alert("미리보기할 스킨을 선택하세요.");
		}else{
			var _opt = "";
			if(p == 'mskin') _opt = "width=380px, height=700p";
			window.open('./?body=design@skin_preview.frm&skin_name='+_skin_name, 'skin_preview', _opt);
		}
	}

	$(window).ready(function(){
		if ($('input[name="currency_position"][value="F"]').attr('checked')) {
			$('#ex1').hide();
		}else if ($('input[name="currency_position"][value="B"]').attr('checked')) {
			$('#ex2').hide();
		}

		if ($('input[name="r_currency_position"][value="F"]').attr('checked')) {
			$('#ex3').hide();
		}else if ($('input[name="r_currency_position"][value="B"]').attr('checked')) {
			$('#ex4').hide();
		}

		$('#use_r_currency_custom').click(function(){
			if($(this).is(':checked')){
				$('.r_currency_custom_area').show();
				$('input[name="r_currency_type"]').attr('checked',false);
				$('input[name="r_currency_type_custom"]').focus();
			}else{
				$('.r_currency_custom_area').hide();
				$('input[name="r_currency_type"]:input[value=""]').prop('checked',true);
				$('input[name="r_currency"]').val('');
			}
		});

		$('input[name="r_currency_type_custom"]').keyup(function(){
			$(this).val($(this).val().toUpperCase());
			$('input[name="r_currency"]').val($(this).val().toUpperCase());
			$(".r_currency_type_txt").html($(this).val());
		});

		$('input[name="r_currency_type"]').click(function(){
			if($('#use_r_currency_custom').is(':checked')){
				$('#use_r_currency_custom').attr('checked',false);
				$('.r_currency_custom_area').hide();
				$('input[name="r_currency_type_custom"]').val('');
			}
		});

	});

	function ex_currency(p){
		if (p == 1) {
			$('#ex1').hide();
			$('#ex2').show();
		} else if (p == 2) {
			$('#ex1').show();
			$('#ex2').hide();
		} else if (p == 3) {
			$('#ex3').hide();
			$('#ex4').show();
		} else if (p == 4) {
			$('#ex3').show();
			$('#ex4').hide();
		}
	}
</script>