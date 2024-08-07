<?PHP

	$ord_input_fd=array(no=>"번호", ono=>"주문번호", dlv_no=>"배송업체 (등록된 배송업체명과 정확히 일치)", dlv_code=>"송장번호", opno=>"주문상품번호", member_id=>"회원아이디", buyer_name=>"구매자명", buyer_email=>"구매자 이메일", buyer_phone=>"구매자 전화번호", buyer_cell=>"구매자 휴대폰번호", addressee_name=>"수취인명", addressee_phone=>"수취인 전화번호", addressee_cell=>"수취인 휴대폰번호", addressee_zip=>"수취인 우편번호", addressee_addr=>"수취인 주소", dlv_memo=>"배송메세지", total_prc=>"결제금액", pay_type=>"결제방법", etc1=>"기타1", etc2=>"기타2", etc3=>"기타3", etc4=>"기타4", etc5=>"기타5");
	$ord_input_fd_selected=(!$cfg[ord_input_fd_selected]) ? "no,ono,dlv_no,dlv_code" : $cfg[ord_input_fd_selected];
	$_ord_input_fd_selected = explode(",", $ord_input_fd_selected);
	$essencial_fd="/ono/dlv_no/dlv_code/";
	$essencial_color="#FF3300";

?>
<?if($admin['level'] < 4) {?>
<!-- 엑셀 파일 형식 -->
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return valueSet(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="ord_input_fd_selected">
	<div class="box_title first">
		<h2 class="title">엑셀 파일 형식</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>입력할 엑셀 파일 내용의 필드순서를 하단 오른쪽의 순서와 매칭시켜야 정확한 입력이 가능합니다.</li>
			<li><u>"주문번호", "배송업체", "송장번호"</u> 필드의 순서가 정확히 일치되도록 설정해주시면 나머지 필드는 생략되거나 다른 정보일 경우에도 작동됩니다.</li>
			<li>엑셀 파일 내용에 주문상품번호를 설정할 경우 주문상품단위로 배송처리를 진행합니다. (주문상품번호 미입력시 처리하지 않습니다.)</li>
		</ul>
	</div>
	<div class="box_middle add_fld">
		<div class="fld_list">
			<h3>추가할 필드 선택</h3>
			<select id="sel1" class="select_n" name="fd_list" size="15" multiple>
				<?
					foreach($ord_input_fd as $key=>$val){
						if($key == 'opno') $style = "style='color:#3333ff;'";
						else {
							$style=(@strchr($essencial_fd, "/".$key."/")) ? " essencial=\"1\" style=\"color:$essencial_color;\"" : "";
						}
						echo "<option value='$key'$style>$val</option>";
				}
				?>
			</select>
		</div>
		<div class="add">
			<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
		</div>
		<div class="add_list">
			<h3>엑셀 파일내용</h3>
			<select id="sel2" class="select_n" name="fd_list_selected" size="15" multiple>
				<?
					foreach($_ord_input_fd_selected as $key=>$val){
						if($val == 'opno') $style = "style='color:#3333ff;'";
						else {
							$style=(@strchr($essencial_fd, "/".$val."/")) ? " class=\"essencial\" style=\"color:$essencial_color;\"" : "";
						}
						echo "<option value='$val'$style>".$ord_input_fd[$val]."</option>";
				}
				?>
			</select>
			<span class="box_btn_s icon delete"><input type="button" value="삭제" onclick="select2.remove();"></span>
			<span class="box_btn_s icon up"><input type="button" value="위로" onclick="select2.move(-1);"></span>
			<span class="box_btn_s icon down"><input type="button" value="아래로" onclick="select2.move(1);"></span>
		</div>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<!-- //엑셀 파일 형식 -->
<?}?>
<!-- 자동 송장번호 입력 -->
<form name="excelFrm" method="post" action="./" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return excel_send(this);">
	<input type="hidden" name="body" value="order@delivery_fileinput.exe">
	<div class="box_title">
		<h2 class="title">자동 송장번호 입력</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>입력할 엑셀 파일 내용의 필드순서를 매칭시켜야 정확한 입력이 가능합니다.</li>
			<li><u>"주문번호", "배송업체", "송장번호"</u> 필드의 순서가 정확히 일치되도록 설정해주시면 나머지 필드는 생략되거나 다른 정보일 경우에도 작동됩니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">자동 송장번호 입력</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">주문 상태 변경</th>
			<td>
				<label for="stat_0" class="p_cursor"><input type="radio" name="stat" id="stat_0" value="0"> 사용안함</label> &nbsp;
				<label for="stat_3" class="p_cursor"><input type="radio" name="stat" id="stat_3" value="3"> <?=$_order_stat[3]?></label> &nbsp;
				<label for="stat_4" class="p_cursor"><input type="radio" name="stat" id="stat_4" value="4" checked> <?=$_order_stat[4]?></label>
			</td>
		</tr>
		<tr>
			<th scope="row">변경 옵션</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="code_ignore" value="Y"> 이미 송장번호가 들어있는 주문서도 다시한번 업데이트합니다</label>
				<ul class="list_msg">
					<li>송장번호를 일괄 정정하거나, 이미 송장번호가 들어있는 <?=$_order_stat[3]?>주문을 <?=$_order_stat[4]?>으로 일괄 변경 시 사용하시면 됩니다</li>
					<li><?=$_order_stat[4]?> → <?=$_order_stat[3]?>상태로 반대로의 변경은 엑셀 송장 입력처리에서 지원하지 않습니다</li>
				</ul>
				<label class="p_cursor"><input type="checkbox" name="ignore_hold" value="Y"> "배송보류"로 설정된 상품도 포함하여 모두 처리합니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">업로드 파일 형식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="file_type" value="csv" checked> csv</label>
				<label class="p_cursor"><input type="radio" name="file_type" value="xls"> xls</label>
				<ul class="list_msg">
					<li>스마트 스토어 주문건의 경우<span class="p_color">'Excel 97 - 2003 통합 문서(.xls)'</span>형식의 파일만 업로드 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">엑셀 파일 첨부</th>
			<td>
				<input type="file" name="excel" class="input input_full">
				<?if($epstat == 2){?>
				<ul class="list_msg">
					<li>우체국 배송연동기능을 사용하고 계십니다</li>
					<li>주문상태를 <span class="desc3">'<?=$_order_stat[4]?>'</span>으로 선택한 후 배송업체명이 <span class="desc3">'우체국택배'</span>로 등록된 엑셀 파일을 업로드 하시면 해당 주문서가 배송예약됩니다</li>
					<?}?>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue" id="stpBtn"><input type="submit" value="전송"></span>
		<div class="process"></div>
	</div>
</form>
<!-- //자동 송장번호 입력 -->

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js?ver=20200821"></script>
<script type="text/javascript">
	var select1 = new R2Select('sel1');
	var select2 = new R2Select('sel2');

	var f=document.frm;
	function excel_send(f){
		if(!f.excel.value){ alert("엑셀 파일을 첨부하세요"); return false; }
		if(!confirm("송장번호를 입력하시겠습니까?")) return false;

		$('#stpBtn').hide();
		return true;
	}

	function valueSet(f){
		fd=f.fd_list_selected;
		val='';
		var ck = 0;
		for(ii=0; ii<fd.length; ii++){
			if(val) val += ',';
			val += fd[ii].value;

			if(fd[ii].value == 'ono' || fd[ii].value == 'dlv_no' || fd[ii].value == 'dlv_code') ck++;
		}
		if(ck < 3) {
			window.alert('엑셀 파일내용에는 주문번호, 배송업체, 송장번호의 세가지 항목이 모두들어있어야합니다\t');
			return false;
		}
		if(!confirm("현재 순서를 저장하시겠습니까?")) return false;
		f.ord_input_fd_selected.value=val;
	}
</script>
