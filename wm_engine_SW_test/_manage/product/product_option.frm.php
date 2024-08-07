<style type="text/css" title="">
.box_btn_option {display:inline-block; *zoom: 1; overflow:hidden; text-align:center; vertical-align:middle;}
.box_btn_option * {-webkit-appearance:none; display:inline-block; *zoom: 1; min-width:120px; height:38px; margin:0; padding:0 36px; border:1px solid #d2d2d2; outline:0; background:#fff; font-family:'dotum', '돋움', sans-serif; font-weight:bold; text-align:center !important; line-height:38px; letter-spacing: -1px; white-space: nowrap; box-sizing:border-box; -webkit-box-sizing:border-box; -moz-box-sizing:border-box; cursor:pointer;}
</style>
<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  옵션 세트 추가
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$pno = numberOnly($_GET['pno']);
	$opno = numberOnly($_GET['opno']);
	if($_GET['stat']) $stat = numberOnly($_GET['stat']);

	$pro = get_info($tbl['product'], "no", $pno);
	if($pro['ea_type'] == 3) $St = "Y";
	$total_ea = $pro['ea'];
	if($total_ea < 1) $total_ea = 0;

	if($stat == "5") { // 세트
		$title = "세트";
	}else { // 상품
		$hid1 = "<!--";
		$hid2 = "//-->";
	}

	if($opno) { // 수정
		$title = "옵션 $title 수정";
		$data = $pdo->assoc("select * from $tbl[product_option_set] where no='$opno'");
		if(!$data['no']) msg("존재하지 않는 자료입니다", "close");
		$necessary = $data['necessary'];

		if(!$data['updir']) $data['updir'] = $dir['upload']."/prd_common";

		if($data['how_cal'] == 3 || $data['how_cal'] == 4) {
			$iidx = 0;
			$items = $pdo->iterator("select * from $tbl[product_option_item] where opno='$data[no]' order by sort asc");
            foreach ($items as $itemdata) {
				$iidx++;
				$itemdata['iname'] = stripslashes($itemdata['iname']);
				${'option'.$iidx} = $itemdata;
			}
		}
	}
	else { // 신규 등록
		$data['otype'] = "2A";
		$title = "옵션 $title 추가";
	}

	$ww = ($pop) ? "750px" : "100%";
	$table_width = ($pop) ? "20%" : "12%";

?>
<script type="text/javascript">
	var St = "<?=$St?>";
	var ea = <?=$total_ea;?>;
	var popup = '<?=$pop?>';
</script>
<form name="optFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onSubmit="return checkPrdOption(this)">
	<input type="hidden" name="body" value="product@product_option.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="opno" value="<?=$data[no]?>">
	<input type="hidden" name="stat" value="<?=$stat?>">
	<input type="hidden" name="pno" value="<?=$pno?>">
	<input type="hidden" name="total_ea" value="<?=$total_ea?>">
	<?=$hid1?>
	<div class="box_title first">
		<h2 class="title">
			옵션세트 관리
			<div class="btns">
				<span class="box_btn_s blue"><a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_option_text.frm&pop=1&pno=<?=$pno?>&stat=<?=$stat?>&otype=4B', 'pfldpot2', 'yes', 100,100);">텍스트옵션 추가</a></span>
			</div>
		</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>상품 옵션은 상품 개별적으로 설정할 수 있습니다</li>
			<li>세트로 만들어 두시면 비슷한 옵션 적용시 저장된 옵션 세트를 불러 수정하여 적용할 수 있습니다</li>
		</ul>
	</div>
	<?=$hid2?>
	<?if($necessary == "Y" && !$opno) {?>
	<div class="box_middle left">
		<p class="explain">새로운 옵션 추가 시 <span class="p_color">기존에 입력된 재고 데이터가 있을 경우</span> 더이상 재고연동이 되지 않으며,<br>새롭게 기초재고를 입력하셔야 합니다.</p>
	</div>
	<?}?>
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
				<? if($St == "Y"){ ?>
				<input type="checkbox" name="ea_ck" value="Y" style="border:none;" <?=checked($data['ea_ck'],"Y");?>>상품재고량 연결
				<span style="color:#FF3300;"> - 한정수량 : <?=$total_ea;?></span>&nbsp;
				[품절옵션숨김<input type="checkbox" name="out_hide" value="Y" style="border:none;" <?=checked($data['out_hide'],"Y");?>>]
				<? } ?>
				<input type="file" name="upfile1" class="input">
				<p class="explain">이미지로 삽입을 원하실 경우 첨부해주세요</p>
				<?=delImgStr($data,1);?>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2"><strong>속성</strong></td>
			<td>
				<?if($necessary == "C" || $data['necessary'] == "C") {?>
					<label><input type="radio" name="necessary" value="C" checked> 복합재고 옵션</label><br>
				<?} else {?>
					<div id="necessaries">
						<label class="p_cursor"><input type="radio" name="necessary" value="Y" <?=checked($data['necessary'],"Y").checked($data['necessary'],"")?>> 필수</label>
						<label class="p_cursor"><input type="radio" name="necessary" value="N" <?=checked($data['necessary'],"N")?>> 선택</label>
						<?if($cfg['use_option_product'] == 'Y') {?>
						<label class="p_cursor"><input type="radio" name="necessary" value="P" <?=checked($data['necessary'],"P")?>> 부속상품(선택)</label>
						<?}?>
					</div>
				<?}?>
			</td>
		</tr>
		<tr>
			<td>
				<ul id="otypes">
					<?foreach($_otype as $key=>$val) { if($key == '4B') continue;?>
					<li style="display: inline-block; width: 30%"><label class="p_cursor"><input type="radio" name="otype" value="<?=$key?>" <?=checked($key,$data['otype'])?>> <?=$val?></label></li>
					<?}?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">단위</th>
			<td>
				<input type="text" name="unit" value="<?=inputText($data[unit])?>" class="input" size="5">
				<?=selectArray($_ounit,"unit_sample",1,"::직접입력::",$data[unit],"this.form.unit.value=this.value")?>
				<span class="explain">(필요시에만)</span>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">추가금 계산방법</th>
			<td><label class="p_cursor"><input type="radio" name="how_cal" value="1" checked> ＋ [더하기]</label></td>
		</tr>
		<tr>
			<td>
				<label class="p_cursor"><input type="radio" name="how_cal" value="3" <?=checked($data['how_cal'], '3')?>> 면적합산</label>
				<label class="p_cursor"><input type="radio" name="how_cal" value="4" <?=checked($data['how_cal'], '4')?>> 면적</label>
			</td>
		</tr>
		<tr class="how_cal_1">
			<th scope="row">추가금액 표시</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="deco_use" value="1" <?=checked($data['deco_use'],"Y")?>> 표시</label><br>
				<input type="text" name="deco1" value="<?=inputText($data[deco1])?>" class="input" size="3">
				<span>추가금액</span>
				<input type="text" name="deco2" value="<?=inputText($data[deco2])?>" class="input" size="3">
				<span class="explain">추가가격 앞뒤에 표시할 기호나 문구를 넣으세요. 예: <b>(</b>추가금액<b>)</b></span>
			</td>
		</tr>
		<tr class="how_cal_1">
			<th scope="row">옵션 항목</th>
			<td>
				<?if($necessary == "C" || $data['necessary'] == "C") {?>
				<ul class="list_msg">
					<li>재고에 영향을 미칠 수 있으므로 <u>새로운 옵션이 추가</u>되고 <u>기존 옵션이 사라질 경우</u>, 기존 옵션의 이름을 변경하지 마시고 반드시 기존옵션을 삭제 하시거나 품절처리 하신 후 새로운 옵션을 등록 해 주십시오.</li>
				</ul>
				<?}?>
				<table id="optItem" class="tbl_inner line full">
					<thead>
						<tr>
							<th>옵션 항목</th>
							<th>추가 금액</th>
							<th>숨김</th>
							<?if($St == "Y"){?>
							<th>수량</th>
							<?}?>
							<?if($cfg['use_option_product'] == 'Y') {?>
							<th class="option_product">부속상품</th>
							<?}?>
							<th>순서</th>
							<th class="lastChild">삭제</th>
						</tr>
					</thead>
					<tbody id="option_items">
						<?PHP
							include 'product_option_item.frm.php';
						?>
					</tbody>
				</table>
				<div style="padding:5px 0;">
					<span class="box_btn blue opt_add_btn"><input type="button" value="추가" onClick="addOptItem('<?=$pno?>', '<?=$opno?>', true)"></span>
				</div>
			</td>
		</tr>
		<tr class="how_cal_3 how_cal_4">
			<th scope="row" rowspan="3">면적 옵션</th>
			<td>
				1x1 당 추가가격 : <input type="text" name="add_price" class="input" size="10" value="<?=$option1['add_price']?>"> 원
				<select name="add_price_option">
					<option value="0">절사 없음</option>
					<option value="1" <?=checked($option1['add_price_option'], 1, 1)?>>1단위 절사</option>
					<option value="10" <?=checked($option1['add_price_option'], 10, 1)?>>10단위 절사</option>
					<option value="100" <?=checked($option1['add_price_option'], 100, 1)?>>100단위 절사</option>
					<option value="1000" <?=checked($option1['add_price_option'], 1000, 1)?>>1000단위 절사</option>
				</select><br>
				최소 면적 : <input type="text" name="min_area" class="input" size="10" value="<?=$option1['min_area']?>">
				미만 시
				<select name="min_area_option">
					<option value="Y" <?=checked($option1['min_area_option'], 'Y', 1)?>>최소면적으로 판매</option>
					<option value="N" <?=checked($option1['min_area_option'], 'N', 1)?>>판매 불가</option>
				</select>
			</td>
		</tr>
		<tr class="how_cal_3 how_cal_4">
			<td>
				옵션설명 : <input type="text" name="desc" class="input" size="40" value="<?=inputText($data['desc'])?>"><br>
				입력란크기 : <input type="text" name="deco2" class="input" size="5" value="<?=inputText($data['deco2'])?>"> 글자<br>
				반복태그 : <input type="text" name="deco1" class="input" size="10" value="<?=inputText($data['deco1'])?>"> <span>(기본값 : div)</span>
			</td>
		</tr>
		<tr class="how_cal_3 how_cal_4">
			<td>
				<input type="hidden" name="ino[]" value="<?=$option1['no']?>">
				<input type="hidden" name="ino[]" value="<?=$option2['no']?>">
				<table class="tbl_inner line full">
					<thead>
						<tr>
							<th scope="col">옵션명</th>
							<th scope="col">최대값</th>
							<th scope="col">최소값</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><input type="text" name="item[]" size="20" class="input" value="<?=inputText($option1['iname'])?>"></td>
							<td><input type="text" name="max_val[]" size="7" class="input" value="<?=$option1['max_val']?>"></td>
							<td><input type="text" name="min_val[]" size="7" class="input" value="<?=$option1['min_val']?>"></td>
						</tr>
						<tr>
							<td><input type="text" name="item[]" size="20" class="input" value="<?=inputText($option2['iname'])?>"></td>
							<td><input type="text" name="max_val[]" size="7" class="input" value="<?=$option2['max_val']?>"></td>
							<td><input type="text" name="min_val[]" size="7" class="input" value="<?=$option2['min_val']?>"></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>

<form id="optionAttahFrm" method="post" enctype="multipart/form-data" target="hidden<?=$now?>" style="display:none; position:absolute; border:1px solid #000; background:#fff;"></form>

<script type="text/javascript">
	var of = document.optFrm;
	var prd_option_ea = 0;
	var item_cnt = 0;
	var load_complete = false;

	function setArrayFields(field, order, value) {
		var field = document.getElementsByName(field+'[]')[order];
		if(field) {
			if(field.type == 'checkbox') {
				if(field.value == value) field.checked = true;
			}
			else field.value = value;
		}
	}

	// 옵션별 부가 이미지 업로드
	function optAttach(idx, ev) {
		var frm = $('#optionAttahFrm');
		var pno = of.pno;

		if(idx == null) {
			$('#blind').remove();
			frm.html('').hide();
			return;
		}

		$.post('./index.php?body=product@product_option_img.exe&pno=<?=$pno?>&opno=<?=$opno?>&idx='+idx, function(data) {
			$('body').append("<div id='blind'></div>");
			frm.html(data).css('display', 'block');
			frm.css('left', ($('body').width()/2)-(frm.width()/2))
				.css('top', ($('body').height()/2)-(frm.height()/2))
				.css('zIndex', '2');
		});
	}

	setPoptitle('옵션추가');


	// 면적옵션
	function setHowCal() {
		var how_cal = $(':checked[name=how_cal]').val();
		if(!how_cal) return;

		if(how_cal == 3 || how_cal == 4) {
			var necessary = $(':checked[name=necessary]').val();
			if(necessary == 'C') {
				window.alert('복합옵션에서는 면적옵션을 선택하실 수 없습니다.');
				$('[name=how_cal][value=1]').attr('checked', true);
				return;
			}
		}

		// 계산법
		$('[class^=how_cal_]').hide();
		$('.how_cal_'+how_cal).show();
		$('[class^=how_cal_]').find('input').attr('disabled', true);
		$('.how_cal_'+how_cal).find('input').attr('disabled', false);

		// 속성
		var otypes = $(':radio[name=otype]');
		otypes.attr('disabled', false);
		if(how_cal == 3 || how_cal == 4) {
			//otypes.not('[value=4A]').attr('disabled', true);
			otypes.filter('[value=4A]').attr('checked', true);
			$(of.necessary).eq(1).attr('disabled', true);
			$(of.necessary).eq(0).attr('checked', true);
		} else {
			//otypes.filter('[value=4A]').attr('disabled', true);
			if(otypes.filter(':checked').val() == '4A') otypes.eq(0).attr('checked', true);
			$(of.necessary).eq(1).attr('disabled', false);
		}
	}

	var colorChipList = '';
	function getColorChip(val) {
		if(!colorChipList) {
			$.ajax({
				'method':'post',
				'url':'./index.php',
				'data':{'body':'product@product_option.exe', 'exec':'getColorChipList'},
				'async':false,
				'success':function(r) {
					colorChipList = r;
				}
			});
		}

		var str = "<select name='colorchip[]' onchange='chgItemNameFromChip(this)'><option value=''>:: 선택 ::</option>";
		str += colorChipList;
		str += "</select>";
		str = $(str);

		if(val) {
			str.find('option').each(function() {
				if(this.text == val) {
					this.selected = true;
					return;
				}
			});
		}

		return str;
	}

	function chgItemNameFromChip(o) {
		$("[name='colorchip[]']").each(function(idx) {
			if(o == this) {
				var name = (this.value) ? $(this).find(':selected').text() : '';
				$("[name^='item[']").eq(idx).val(name);
				return;
			}
		});
	}

	var current_otype = null;
	function setOtype() {
		var otype = $(':checked[name=otype]', of).val();
		if(otype == '5A') {
			$('input[name^="item["]').each(function() {
				if(this.style.display != 'none') {
					this.style.display = 'none';
					$(this).after(getColorChip(this.value));
				}
			});
		} else {
			$('select[name="colorchip[]"]').remove();
			$('input[name^="item["]').show();
		}
		if(otype == '4A') {
			$(':radio[name=how_cal][value=3]').prop('checked', true);
		} else {
			$(':radio[name=how_cal][value=1]').prop('checked', true);
		}
		if($(':checked[name=necessary]', of).val() == 'P') {
			$('.option_product').show();
			$('.add_price').prop('readOnly', true).val('0').css('background', '#eee');
		} else {
			$('.option_product').hide();
			$('.add_price').prop('readOnly', false).css('background', '');;
		}

		setHowCal();
		current_otype = otype;
	}

	$('#otypes, #necessaries').find(':radio').click(function() {
		var new_otype = $(':checked[name=otype]', of).val();
		if(current_otype != new_otype && (current_otype == '4A' || new_otype == '4A')) {
			if(confirm('추가금 계산방법이 변경되어 현재 입력하신 내용이 초기화됩니다.\n계속하시겠습니까?') == false) {
				return false;
			}
		}
		setOtype();
	});

	$('[name=how_cal]').click(function() {
		setHowCal();
	});

	$(document).ready(function(){
		setOtype();
		load_complete = true;
		window.focus();
	});

	var psearch = new layerWindow('erp@erp_inc.exe');
	psearch.open = function(param, opt_item_idx) {
		var url = './index.php?body='+this.body+'&hid_frame='+hid_frame;
		var _this = this;
		if(param) {
			param = param.replace(/&?body=[^&]+/, '');
			param = param.replace(/&?hid_frame=[^&]+/, '');
			param = param.replace(/^\?/, '');
			if(/^(&|\?)/.test(param) == false) param = '&'+param;
			url += param;
		}
		if(typeof opt_item_idx == 'number') this.opt_item_idx = parseInt(opt_item_idx);

		$.get(url, function(data) {
			$('.layerPop').remove();
			a = $('body').append(data);

			_this.pop = $('.layerPop');
			_this.pop.css({
				top:0,
				left:0,
				border:0,
				padding:0,
				width:'100%',
				height:document.documentElement.scrollHeight
			});
		});
	}
	psearch.psel = function(complex_no) {
		eval('var json = json_'+complex_no);

		var iname = json.name;
		if(json.opt_name) iname += '('+json.opt_name+')';
		var tr = $('#option_row_'+this.opt_item_idx);
		tr.find('.thumb').html(json.imgstr);
		tr.find('.title').html(json.name);
		tr.find('.opt_name').html(json.opt_name);
		tr.find('.complex_no').val(complex_no);
		tr.find('.name').val(iname);

		this.close();

	}

	function sortOptionItem(direction, item_no) {
		var param = {
			'body': 'product@product_option.exe',
			'exec': 'sortItem',
			'direction': direction,
			'item_no': item_no
		};
		$.post('./index.php', param, function(r) {
			$('#option_items').html(r);
			setOtype();
		});
	}
</script>