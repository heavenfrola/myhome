<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  현금영수증/세금계산서 설정
	' +----------------------------------------------------------------------------------------------+*/
	$scfg->def('cash_receipt_ness', 'N');
	$scfg->def('cash_receipt_nessprc', '100000');

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="cash_receipt">
	<?php
		if(!$cfg['cash_receipt_stat']) $cfg['cash_receipt_stat']=2;
		if($admin['admin_id'] == '' && $test){
	?>
	<div class="box_title first">
		<h2 class="title">현금영수증 발행 PG사</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">현금영수증 발행 PG사</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">현금영수증 발행PG사<br>(위사전용)</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_r_pg" value="dacom" <?=checked($cfg['cash_r_pg'],"dacom")?>> 통합 LG텔레콤</label><br>
				<label class="p_cursor"><input type="radio" name="cash_r_pg" value="kcp" <?=checked($cfg['cash_r_pg'],"kcp")?>> KCP Payplus</label><br>
				<label class="p_cursor"><input type="radio" name="cash_r_pg" value="allat" <?=checked($cfg['cash_r_pg'],"allat")?>> 삼성 All@Pay</label><br>
				<label class="p_cursor"><input type="radio" name="cash_r_pg" value="inicis" <?=checked($cfg['cash_r_pg'],"inicis")?>> inicis</label>
			</td>
		</tr>
	</table>
	<?php } ?>
	<?php if ($cfg['cash_r_pg'] == "dacom" ) { ?>
	<div class="box_title first">
		<h2 class="title">현금영수증 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">현금영수증 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_receipt_use" value="Y" <?=checked($cfg['cash_receipt_use'],'Y')?>> 사용</label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_use" value="N" <?=checked($cfg['cash_receipt_use'],'N').checked($cfg['cash_receipt_use'],"")?>> 미사용</label>
			</td>
		</tr>
		<tr>
			<th scope="row">발급 방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_receipt_auto" value="Y" <?=checked($cfg['cash_receipt_auto'],'Y')?>> 자동 발급</label>
				<?=($cfg['cash_receipt_auto_date']) ? "<span class=\"explain\">(".date("Y-m-d H:i", $cfg['cash_receipt_auto_date']).")</span>" : ""?>
				<ul class="list_msg">
					<li>설정된 발급 시점(예 : <?=$_order_stat[2]?>)에서 자동으로 현금영수증 발급/취소 처리를 실행합니다.</li>
					<li>주문서 부분배송/부분취소/부분환불/부분반품 시에는 자동발급 기능이 미적용됩니다.</li>
				</ul>
				<label class="p_cursor"><input type="radio" name="cash_receipt_auto" value="N" <?=checked($cfg['cash_receipt_auto'],'N')?>> 수동 발급</label>
				<ul class="list_msg">
					<li>상점에서 건별로 발급버튼을 눌러 익일 국세청으로 전송하는 방식입니다. (취소도 수기반영)</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">발급 시점
				<a href="#" class="tooltip_trigger" data-child="tooltip_cash_receipt_stat">설명</a>
				<div class="info_tooltip tooltip_cash_receipt_stat w700" style="top: 495px;left: 489px;">
					<h3>발급시점</h3>
					<p>의무 발급 사용 시 거래일(입금일)로부터 5일 이내 현금영수증을 발급해야 하므로, 입금 완료 상태에 발급을 권장합니다.</p>
					 <a href="#" class="tooltip_closer">닫기</a>
			   </div>
			</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="2" <?=checked($cfg['cash_receipt_stat'],'2')?>> <?=$_order_stat[2]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="3" <?=checked($cfg['cash_receipt_stat'],'3')?>> <?=$_order_stat[3]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="4" <?=checked($cfg['cash_receipt_stat'],'4')?>> <?=$_order_stat[4]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="5" <?=checked($cfg['cash_receipt_stat'],'5')?>> <?=$_order_stat[5]?></label>
			</td>
		</tr>
		<tr>
			<th scope="row">의무 발급
			<a href="#" class="tooltip_trigger" data-child="tooltip_cash_receipt">설명</a>
			<div class="info_tooltip tooltip_cash_receipt w700" style="top: 594px; left: 489px;">
                <h3>의무 발급(자진발급)</h3>
                <p>현금영수증 의무 발행 업종 사업자는 거래 건당 10만원 이상(부가가치세 포함) 현금 거래 시<br>소비자가 요구하지 않더라도  <strong>거래일(입금일)로부터 5일 이내 </strong>현금영수증을 발급해야 합니다.</p>
                <p>소비자가 현금영수증 발행을 원치 않을 경우 국세청 지정번호인 010-000-1234로 자진발급됩니다. <a href="https://help.wisa.co.kr/document/article/1358" target="_blank">자세히보기</a></p>
                <a href="#" class="tooltip_closer">닫기</a>
           </div>
			</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="cash_receipt_ness" value='Y' <?=checked($cfg['cash_receipt_ness'],'Y')?>> <input type="text" name="cash_receipt_nessprc" class="input" size="20" value="<?=$cfg['cash_receipt_nessprc']?>"> 원 이상 결제 시 의무 발급</label><br>
			</td>
		</tr>
		<tr>
			<th scope="row">발급 사업자번호</th>
			<td>
				<?=numberOnly($cfg['company_biz_num'])?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
	<?php
		}else{
		include_once $engine_dir."/_engine/include/ext.lib.php";
		$cfg['company_biz_num']=numberOnly($cfg['company_biz_num']);
		$b_num_ck=checkBizNo($cfg['company_biz_num']);
	?>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return confirmCashReceipt(this);">
	<input type="hidden" name="body" value="config@cash_receipt_comm.exe">
	<div class="box_title first">
		<h2 class="title">현금영수증 가맹등록</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li class="p_color2">현재 현금영수증 서비스가 신청되지 않았습니다.</li>
			<li>아래의 정보가 정확하지 않은 경우에는 <a href="./?body=config@info" target="_blank"><u>쇼핑몰정보 설정 페이지</u></a>에서 수정하신 뒤 등록하여 주시기 바랍니다.</li>
			<li>현금영수증 자동 발급건에 대한 확인은 <a href="./?body=order@order_cash_receipt_new" target="_blank">현금영수증 관리 페이지</a>에서 가능합니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">현금영수증 가맹등록</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">현금영수증 가맹<br>사업자번호</th>
			<td>
				<?=$cfg['company_biz_num']?>
				<?=(!$b_num_ck) ? "<span class=\"p_color2\"><b>! 유효하지 않은 사업자 번호입니다</b></span>" : "";?>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 가맹<br>사업자명(상호)</th>
			<td>
				<?=$cfg['company_name']?>
				<?=(!trim($cfg['company_name'])) ? "<span class=\"p_color2\"><b>! 쇼핑몰 정보 설정에서 정확한 상호를 입력해주시기 바랍니다</b></span>" : "";?>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 가맹<br>사업자 전화번호</th>
			<td>
				<?=$cfg['company_phone']?>
				<?=(!trim($cfg['company_phone'])) ? "<span class=\"p_color2\"><b>! 쇼핑몰 정보 설정에서 정확한 사업자 전화번호를 입력해주시기 바랍니다</b></span>" : "";?>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 가맹<br>사업자 대표자 성명</th>
			<td>
				<?=$cfg['company_owner']?>
				<?=(!trim($cfg['company_owner'])) ? "<span class=\"p_color2\"><b>! 쇼핑몰 정보 설정에서 정확한 사업자 대표자 정보를 입력해주시기 바랍니다</b></span>" : "";?>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 가맹<br>사업장 주소</th>
			<td>
				<?=$cfg['company_addr1']." ".$cfg['company_addr2']?>
				<?=(!trim($cfg['company_addr1'])) ? "<span class=\"p_color2\"><b>! 쇼핑몰 정보 설정에서 정확한 사업장 주소를 입력해주시기 바랍니다</b></span>" : "";?>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 발급 방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_receipt_auto" value="Y" checked> 자동 발급</label>
				<span class="explain">설정된 발급 시점(예 : <?=$_order_stat[2]?>)에서 자동으로 현금영수증 발급/취소 처리를 실행합니다.</span>
				<ul class="list_msg">
					<li>! 주문서 부분배송/부분취소/부분환불/부분반품 시에는 자동발급 기능이 미적용 됩니다.</li>
				</ul>
				<label class="p_cursor"><input type="radio" name="cash_receipt_auto" value="N"> 수동 발급</label>
				<span class="explain">상점에서 건별로 발급버튼을 눌러 익일 국세청으로 전송하는 방식입니다. (취소도 수기반영)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">현금영수증 발급 시점
				<a href="#" class="tooltip_trigger" data-child="tooltip_cash_receipt_stat">설명</a>
				<div class="info_tooltip tooltip_cash_receipt_stat w700" style="top: 495px;left: 489px;">
					<h3>발급시점</h3>
					<p>의무 발급 사용 시 거래일(입금일)로부터 5일 이내 현금영수증을 발급해야 하므로, 입금 완료 상태에 발급을 권장합니다.</p>
					 <a href="#" class="tooltip_closer">닫기</a>
			   </div>
			</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="2" <?=checked($cfg['cash_receipt_stat'],'2')?>> <?=$_order_stat[2]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="3" <?=checked($cfg['cash_receipt_stat'],'3')?>> <?=$_order_stat[3]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="4" <?=checked($cfg['cash_receipt_stat'],'4')?>> <?=$_order_stat[4]?></label><br>
				<label class="p_cursor"><input type="radio" name="cash_receipt_stat" value="5" <?=checked($cfg['cash_receipt_stat'],'5')?>> <?=$_order_stat[5]?></label>
			</td>
		</tr>
		<tr>
			<th scope="row">의무 발급
			<a href="#" class="tooltip_trigger" data-child="tooltip_cash_receipt">설명</a>
			<div class="info_tooltip tooltip_cash_receipt w700" style="top: 594px; left: 489px;">
                <h3>의무 발급(자진발급)</h3>
                <p>현금영수증 의무 발행 업종 사업자는 거래 건당 10만원 이상(부가가치세 포함) 현금 거래 시<br>소비자가 요구하지 않더라도  <strong>거래일(입금일)로부터 5일 이내 </strong>현금영수증을 발급해야 합니다.</p>
                <p>소비자가 현금영수증 발행을 원치 않을 경우 국세청 지정번호인 010-000-1234로 자진발급됩니다. <a href="https://help.wisa.co.kr/document/article/1358" target="_blank">자세히보기</a></p>
                <a href="#" class="tooltip_closer">닫기</a>
           </div>
			</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="cash_receipt_ness" value='Y' <?=checked($cfg['cash_receipt_ness'],'Y')?>> <input type="text" name="cash_receipt_nessprc" class="input" size="20" value="<?=$cfg['cash_receipt_nessprc']?>"> 원 이상 결제 시 의무 발급</label><br>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="현금영수증 가맹 등록 신청"></span>
	</div>
	<script language="JavaScript">
		function confirmCashReceipt(f){
			if(!checkBlank(f.LGD_REG_BUSINESSNUM, '사업자번호를 입력해주세요.')) return false;
			if(!checkBlank(f.LGD_REG_MERTNAME, '사업자명을 입력해주세요.')) return false;
			if(!checkBlank(f.LGD_REG_MERTPHONE, '사업자 전화번호를 입력해주세요.')) return false;
			if(!checkBlank(f.LGD_REG_CEONAME, '사업장 주소를 입력해주세요.')) return false;
		}
	</script>
	<?php } ?>
</form>