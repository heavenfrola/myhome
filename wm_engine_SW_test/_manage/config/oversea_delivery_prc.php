<?php

	if(!$_GET['delivery_com']) $_GET['delivery_com'] = $pdo->row("select no from `".$tbl['delivery_url']."` where overseas_delivery='O' order by `sort`, `no` desc limit 1");

	## 배송사 목록
	$sql="select * from `".$tbl['delivery_url']."` where overseas_delivery='O' order by `sort`, `no` desc";
	$res = $pdo->iterator($sql);
	$delivery_list="";
	$chk=0;

    foreach ($res as $data) {
		$str="$data[name]";
		$chk = ($_GET['delivery_com']==$data['no'])?'selected':'';
		$delivery_list.="<option value=\"$data[no]\" $chk>$str</option>\n";
		$chk++;
	}

	if(!$chk) msg("설정된 배송업체가 없습니다. 배송업체를 먼저 추가하세요.","?body=config@delivery_prv");

	$res = $pdo->iterator("select * from ${tbl['os_delivery_area']} where delivery_com='${_GET['delivery_com']}' order by `order` asc");
	$title_cnt = $res->rowCount();

	$title_arr = array();
	$free_arr = array();

	if($title_cnt){
        foreach ($res as $data) {
			$title_arr[$data['no']] = $data['name'];
			$free_arr[$data['no']]['oversea_dlv_free'] = $data['oversea_dlv_free'];
			$free_arr[$data['no']]['oversea_dlv_free_limit'] = $data['oversea_dlv_free_limit'];
		}
	}

	$res = $pdo->iterator("select * from ${tbl['os_delivery_prc']} where delivery_com='${_GET['delivery_com']}' order by `order` asc");
	$data_cnt = $res->rowCount();

	$data_arr = array();
	if($data_cnt == 0){
		for($i=0;$i<=4;$i++){
			$data_arr[$i]['weight'] = "";
			$data_arr[$i]['area_no'] = "";
			$data_arr[$i]['price'] = "";
			$data_arr[$i]['transfer_price'] = "";
		}
	}else{
        foreach ($res as $data) {
			$data_arr[$data['weight']]['weight'] = $data['weight'];
			$data_arr[$data['weight']][$data['area_no']]['area_no'] = $data['area_no'];
			$data_arr[$data['weight']][$data['area_no']]['price'] = $data['price'];
			$data_arr[$data['weight']][$data['area_no']]['transfer_price'] = $data['transfer_price'];
		}
	}

	$transfer_price = $pdo->row("select transfer_price from ${tbl['delivery_url']} where no='${_GET['delivery_com']}'");

	if($transfer_price == 'N'){
		$currency_decimal = $cfg['m_currency_decimal'];
	}else if($transfer_price == 'Y'){
		$currency_decimal = $cfg['currency_decimal'];
	}

?>
<style>
	.Lfloat{float:left;}
	.Rfloat{float:right;}
	.clear{clear:both;}
	.left{text-align:left;}
	.file_input_hidden {position:absolute; left:0; top:5px; z-index:5; height:35px; opacity:0; filter: alpha(opacity=0); -ms-filter: "alpha(opacity=0)"; -khtml-opacity:0; -moz-opacity:0; cursor:pointer;border:1px solid red;width:120px;}
	.file_input_button{position:relative;}
</style>

<form name="deliveryFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return prc_chk(this);" enctype="multipart/form-data">
	<input type="hidden" name="body" value="config@oversea_delivery.exe">
	<input type="hidden" name="exec" value="prc">
	<input type="hidden" name="config_code" value="oversea_delivery_prc">
	<div class="box_title first">
		<h2 class="title">배송비 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배송비 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">배송사 선택</th>
			<td>
				<select id="select" name="delivery_com">
					<?=$delivery_list?>
				</select>
				&nbsp;&nbsp;&nbsp;<span><a href="?body=config@delivery_prv" style="color:red;">☞ 배송사 설정 바로가기</a></span>
			</td>
		</tr>
		<tr>
			<th scope="row">무료배송 설정</th>
			<td>
				<table class="tbl_row">
				<colgroup>
					<col style="width:25%">
					<col style="width:*">
				</colgroup>
					<? foreach($title_arr as $k=>$v){ ?>
					<tr>
						<th class="left"><?=$v?></th>
						<td>
							<label><input type="checkbox" value="Y" name="oversea_dlv_free[<?=$k?>]" data-no="<?=$k?>" class="oversea_dlv_free" <?=$free_arr[$k]['oversea_dlv_free']=='Y'?'checked':''?> /> 무료배송 사용</label>
							<span style="display:<?=$free_arr[$k]['oversea_dlv_free']=='Y'?'':'none'?>;" id="dlv_limit_box<?=$k?>">&nbsp;&nbsp;&nbsp;<input type="text" value="<?=$free_arr[$k]['oversea_dlv_free_limit']>0?$free_arr[$k]['oversea_dlv_free_limit']:''?>" name="oversea_dlv_free_limit[<?=$k?>]" class="input input_won" size="10" data-decimal="<?=$cfg['m_currency_decimal']?>"/> <?=$cfg['currency_type']?> 이상일 경우 무료배송</span>
						</td>
					</tr>
					<? } ?>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="tbl_col tbl_col_bottom" id="weight_tbl">
					<caption style="border-bottom:1px solid #c9c9c9;">
						<div class="Lfloat">
							<? if($cfg['m_currency_type'] != 'N' && $cfg['m_currency_type'] != $cfg['currency_type'] ) {?>
							<span class="box_btn_s icon setup"><input type="button" value="<?=$transfer_price=='W' || $transfer_price=='N'?$cfg['currency_type']:$cfg['m_currency_type']?> 화폐로 환산" onClick="transferPrice('<?=$_GET['delivery_com']?>');"></span>
							<? } ?>
							<span class="box_btn_s icon copy2"><input type="button" value="엑셀업로드"><input type="file" name="excel_file" class="file_input_hidden" onchange="upExcelFile('<?=$_GET['delivery_com']?>')"></span>


							<span class="box_btn_s icon backup"><input type="button" value="엑셀 다운" onclick="makeExcelSample('<?=$_GET['delivery_com']?>')"></span>
						</div>
						<div class="Rfloat"><span class="box_btn_s blue"><input type="button" value="중량별 배송비 추가 ↓" onClick="AddWeight();"></span></div>
						<div class="clear"></div>
					</caption>
					<thead>
						<tr>
							<th width="9%">중량<br/>(KG)</th>
							<?
								if(count($title_arr) > 0){
									foreach($title_arr as $k=>$v) {
							?>
									<th>
										<?=$v?><br/>
										<?
											echo "(".$cfg['currency_type'].")";
											
										?>
									</th>
							<?
									}
								}
							?>
							<th width="9%">삭제</th>
						</tr>
					</thead>
					<tbody>
						<? if(count($title_arr) > 0) { ?>
							<? $i=1;foreach($data_arr as $k=>$v) { ?>
								<tr id="data_tr<?=$i?>">
									<td><input type="text" name="weight[]" class="input" value="<?=$v['weight']?>" style="width:70%;" /></td>
									<? foreach($title_arr as $sk=>$sv) { ?>
											<td><input type="text" name="price[<?=$sk?>][]" value="<?=$v[$sk]['price']?>" class="input input_won" style="width:70%;" data-decimal="<?=$transfer_price == 'W'?'0':$currency_decimal?>" />
												<!--<input type="hidden" value="<?=$v[$sk]['transfer_price']?>" name="transfer_price[<?=$sk?>][]" />-->
											</td>
									<? } ?>
									<td><span class="box_btn_s gray"><input type="button" value="삭제" onClick="RemoveWeight('<?=$i?>');"></span></td>
								</tr>
							<? $i++;} ?>
						<? }else{ ?>
							<tr>
								<td colspan="2">배송 지역을 먼저 설정해 주세요.</td>
							</tr>
						<? } ?>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script>
	$(document).ready(function(){
		$('#select').change(function(){
			location.href="<?=$PHP_SELF?>?body=<?=$body?>&delivery_com="+$(this).val();
		});

		$('.oversea_dlv_free').click(function(){

			var no = $(this).attr('data-no');

			if($(this).is(':checked')){
				$('#dlv_limit_box'+no).show();
				$('input[name="oversea_dlv_free_limit['+no+']"]').focus();
			}else{
				$('#dlv_limit_box'+no).hide();
			}
		});
	});

	function AddWeight(){
		var _cnt = $("input[type='text'][name='weight[]']").length;
		_cnt++;
		var html = "";
		var jsArr = <?=json_encode($title_arr)?>;

		html += "<tr id=\"data_tr"+_cnt+"\">\n";
		html += "\t<td><input type=\"text\" name=\"weight[]\" class=\"input\" style=\"width:70%;\" /></td>\n";
		for(var i in jsArr){
			html += "\t<td><input type=\"text\" name=\"price["+i+"][]\" class=\"input\" style=\"width:70%;\" /></td>\n";
		}
		html += "\t<td><span class=\"box_btn_s gray\"><input type=\"button\" value=\"삭제\" onClick=\"RemoveWeight('"+_cnt+"');\"></span></td>\n";

		html += "</tr>";


		$('#weight_tbl tbody').append(html);
		location.href="#data_tr"+_cnt;
	}

	function prc_chk(f){
		var val_cnt = 0;

		$("input[type='text'][name='weight[]']").each(function(){
			if(!$.trim($(this).val())){
				alert("중량을 입력하세요");
				$(this).focus();
				val_cnt = 0;
				return false;
			}else{
				val_cnt++;
			}
		});

		f.exec.value = "prc";

		if(val_cnt == 0) return false;
	}

	function RemoveWeight(num){
		$('#data_tr'+num).remove();
	}

	function transferPrice(delivery_com){
		var f = document.deliveryFrm;
		if(confirm("현재 입력된 금액을 설정된 환율로 계산하여 수정하시겠습니까?")){
			f.exec.value = "transfer";
			f.submit();
			f.exec.value = "prc";
		}
	}

	function makeExcelSample(delivery_com){
		var f = document.deliveryFrm;

		f.body.value = "config@oversea_delivery_prc_excel.exe";
		f.submit();
		f.body.value = "config@oversea_delivery.exe";
	}

	// 엑셀 업로드
	function upExcelFile(){
		var f = document.deliveryFrm;

		if(confirm($('#select option:selected').text()+" 배송사의 중량 및 배송 금액을 첨부하실 파일의 내용으로 교체하시겠습니까?")){
			f.exec.value = "excel_price";
			f.submit();
		}else{
			f.excel_file.value = "";
		}
	}
</script>