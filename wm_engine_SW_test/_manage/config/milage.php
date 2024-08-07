<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  적립금 설정
	' +----------------------------------------------------------------------------------------------+*/

	$cfg['milage_review_auto']=(!$cfg['milage_review_auto']) ? "N" : $cfg['milage_review_auto'];
	if(!$cfg['milage_use']) $cfg['milage_use'] = 2;
	if(!$cfg['milage_type']) $cfg['milage_type'] = 1;
	if(!$cfg['is_milage_cash']) $cfg['is_milage_cash'] = 'Y';
	if(!$cfg['milage_use_unit']) $cfg['milage_use_unit'] = 0;
	$cfg['milage_use_unit'] = numberOnly($cfg['milage_use_unit'], true);

	$_milage_expire = array(
		'' => '제한없음',
		'3 years' => '3년',
		'2 years' => '2년',
		'1 years' => '1년',
		'9 months' => '9개월',
		'6 months' => '6개월',
		'3 months' => '3개월',
		'1 months' => '1개월',
		'15 days' => '15일',
	);

	$_milage_expire_sms = array(
		'1 days' => '1일',
		'3 days' => '3일',
		'7 days' => '7일',
		'15 days' => '15일',
		'1 months' => '1개월',
		'3 months' => '3개월',
	);
	$_milage_expire_sms_case = array(
		'A' => '정보성',
		'B' => '광고성',
	);

	$_milage_expire_email = array(
		'1 days' => '1일',
		'3 days' => '3일',
		'7 days' => '7일',
		'15 days' => '15일',
		'1 months' => '1개월',
		'3 months' => '3개월',
	);
	$_milage_expire_email_case = array(
		'A' => '정보성',
		'B' => '광고성',
	);

	$wec_acc = new weagleEyeClient($_we, 'etc');
	$result = $wec_acc->call('getMilageExpire');
	$cfg['expire_sms_use'] = ($result[0]->sms_use[0] == "Y") ? "Y" : "N" ;
	$cfg['expire_email_use'] = ($result[0]->email_use[0] == "Y") ? "Y" : "N" ;

	include_once $engine_dir.'/_engine/include/milage.lib.php';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckMilageDefaultConfig(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="milage_config">
	<div class="box_title first">
		<h2 class="title">적립금 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">적립금 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<input type="radio" name="milage_use" id="milage_use_1" value="1" <?=checked($cfg['milage_use'],1)?>> <label for="milage_use_1" class="p_cursor">사용함</label><br>
				<input type="radio" name="milage_use" id="milage_use_2" value="2" <?=checked($cfg['milage_use'],2)?>> <label for="milage_use_2" class="p_cursor">사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">구매적립금 적용 방식</th>
			<td>
				<input type="radio" name="milage_type" id="milage_type_1" value="1" <?=checked($cfg['milage_type'],1)?>> <label for="milage_type_1" class="p_cursor">상품별 적립금 설정</label>
				<p class="explain icon">상품 개별 등록 (상품 등록 및 수정시 개별적으로 등록합니다)</p>
				<input type="radio" name="milage_type" id="milage_type_2" value="2" <?=checked($cfg['milage_type'],2)?>> <label for="milage_type_2" class="p_cursor">실상품결제 금액의 <input type="text" name="milage_type_per" value="<?=$cfg['milage_type_per']?>" size="10" class="input" > % 를 적립</label>
				<ul class="list_msg">
					<li>결제 금액 단위의 적립금을 사용하실때에는 상품별로 설정된 적립금은 무시됩니다.</li>
					<li>실상품결제금액은 각종 할인 및 적립금 사용액을 제외하고 실제로 고객이 결제하는 금액에서 배송비를 제외한 금액입니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">현금할인 적용</th>
			<td>
				<ul>
					<li><label><input type="radio" name="is_milage_cash" value="Y" <?=checked($cfg['is_milage_cash'], 'Y')?>> 적립금을 사용해도 현금 전용 할인이 적용됩니다.</label></li>
					<li><label><input type="radio" name="is_milage_cash" value="N" <?=checked($cfg['is_milage_cash'], 'N')?>> 적립금 사용시 현금 전용 할인이 적용되지 않습니다.</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">유효기간</th>
			<td>
				<?=selectArray($_milage_expire, 'milage_expire', false, '', $cfg['milage_expire'])?>
				<ul class="list_msg">
					<li>변경된 설정은 설정 이후 적립된 적립금에만 반영되며, 기적립금에는 반영되지 않습니다.</li>
					<li>운영중인 사이트의 경우 최초 설정 시 시간이 걸릴 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 소멸 문자알림</th>
			<td>
				<label class="p_cursor"><input type="radio" name="expire_sms_use" id="milage_use_1" value="Y" <?=checked($cfg['expire_sms_use'],'Y')?>>사용함</label>
				<label class="p_cursor"><input type="radio" name="expire_sms_use" id="milage_use_2" value="N" <?=checked($cfg['expire_sms_use'],'N')?>>사용안함</label><br><br>
				적립금 소멸 <?=selectArray($_milage_expire_sms, 'milage_expire_sms', false, '', $cfg['milage_expire_sms'])?> 전 <?=selectArray($_milage_expire_sms_case, 'milage_expire_sms_case', false, '', $cfg['milage_expire_sms_case'])?>목적으로 문자알림을 보냅니다.
				<a href="?body=member@sms_config" class="sclink blank">고객문자알림 설정 바로가기</a>
				<ul class="list_msg">
					<li>고객 문자알림 설정 내 '적립금 소멸'에 대한 사용여부를 반드시 확인부탁드립니다.</li>
					<li>적립금 소멸 문자알림은 SMS수신 동의 대상 회원에게만 발송됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 소멸 이메일알림</th>
			<td>
				<label class="p_cursor"><input type="radio" name="expire_email_use" id="milage_use_1" value="Y" <?=checked($cfg['expire_email_use'],'Y')?>>사용함</label>
				<label class="p_cursor"><input type="radio" name="expire_email_use" id="milage_use_2" value="N" <?=checked($cfg['expire_email_use'],'N')?>>사용안함</label><br><br>
				적립금 소멸 <?=selectArray($_milage_expire_email, 'milage_expire_email', false, '', $cfg['milage_expire_email'])?> 전 <?=selectArray($_milage_expire_email_case, 'milage_expire_email_case', false, '', $cfg['milage_expire_email_case'])?> 목적으로 이메일알림을 보냅니다.
				<ul class="list_msg">
					<li>적립금 소멸 이메일알림은 이메일수신 동의 대상 회원에게만 발송됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckMilageConfig(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="milage4">
	<div class="box_title">
		<h2 class="title">적립금 사용/적립 기준</h2>
	</div>
	<div class="box_middle left">
		<p class="p_color2">미설정 또는 0 으로 설정시에 제한이 없습니다</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">적립금 사용/적립 기준</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">적립금 사용시 결제방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="order_milage_paytype" value="1" <?=checked($cfg['order_milage_paytype'],1)?>> 적립금은 모든 결제방식에서 사용할 수 있습니다.</label><br>
				<label class="p_cursor"><input type="radio" name="order_milage_paytype" value="2" <?=checked($cfg['order_milage_paytype'],2)?>> 적립금은 무통장입금 결제 시에만 사용할 수 있습니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">결제가능<br><u>최소</u> 적립금</th>
			<td>
				<input type="text" name="milage_use_min" value="<?=$cfg['milage_use_min']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?>
				<strong>이상 적립금이 있을 경우</strong> 사용 가능
				<?php upNum("this.form.milage_use_min"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">결제가능<br><u>최대</u> 적립금</th>
			<td>
				<label class="p_cursor">
					1. <input type="radio" name="milage_use_max_type" value="1" <?=checked($cfg['milage_use_max_type'],1).checked($cfg['milage_use_max_type'],"")?>> 적립금중
					<input type="text" name="milage_use_max_won" value="<?=$cfg['milage_use_max_won']?>" class="input right" size="10"  onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?>
					<b>이하만 </b> 사용 가능
				</label><br>
				<label class="p_cursor">
					2. <input type="radio" name="milage_use_max_type" value="2" <?=checked($cfg['milage_use_max_type'],2)?>> 총 상품금액 중
					<input type="text" name="milage_use_max_per" value="<?=$cfg['milage_use_max_per']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> %
					<b>이하만 </b> 사용 가능
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">결제가능<br>최소 구매금액</th>
			<td>
				상품을 <input type="text" name="milage_use_order_min" value="<?=$cfg['milage_use_order_min']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?>
				<b>이상 구매하였을 경우</b> 사용 가능
				<?php upNum("this.form.milage_use_order_min"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">사용단위</th>
			<td>
				<input type="text" name="milage_use_unit" value="<?=$cfg['milage_use_unit']?>" class="input right" size="10"> 원 단위로 사용 가능
				<span class="explain">(0 으로 입력시 체크하지 않습니다.)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 사용시<br>추가 적립</th>
			<td>
				<input type="radio" name="milage_use_give" id="milage_use_give_1" value="N" <?=checked($cfg['milage_use_give'],"N").checked($cfg['milage_use_give'],"")?>> <label for="milage_use_give_1" class="p_cursor">적립안함</label>
				<input type="radio" name="milage_use_give" id="milage_use_give_2" value="Y" <?=checked($cfg['milage_use_give'],"Y")?>> <label for="milage_use_give_2" class="p_cursor">적립함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">쿠폰 사용시<br>적립금 지급</th>
			<td>
				<input type="radio" name="use_cpn_milage" id="use_cpn_milage_1" value="N" <?=checked($cfg['use_cpn_milage'],"N").checked($cfg['use_cpn_milage'],"")?>> <label for="use_cpn_milage_1" class="p_cursor">적립안함</label>
				<input type="radio" name="use_cpn_milage" id="use_cpn_milage_2" value="Y" <?=checked($cfg['use_cpn_milage'],"Y")?>> <label for="use_cpn_milage_2" class="p_cursor">적립함</label>
				<p class="explain">적립안함으로 설정할 경우 모든 쿠폰 사용시 적립금이 지급되지 않습니다.</p>
				<label class="p_cursor"><input type="checkbox" name="use_cpn_milage_msg" value="Y" <?=checked($cfg['use_cpn_milage_msg'],"Y")?>> 주문서 페이지에서 쿠폰 선택 시 적립금 미지급 안내메시지를 출력 합니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">적립 가능한<br>결제 금액</th>
			<td>
				<input type="text" name="milage_save_min" value="<?=$cfg['milage_save_min']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?> <b>이상 결제할 경우</b>(상품 총액) 적립금 적립 <span class="explain">(<a href="./?body=promotion@event" class="small"><b>적립금 이벤트</b></a>중에는 이벤트 설정에 따릅니다)</span><br>
				<?php upNum("this.form.milage_save_min"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">첫구매 적립금</th>
			<td>
				<input type="text" name="first_order_milage" value="<?=$cfg['first_order_milage']?>" size="10" class="input right"> 원
				<ul class="list_msg">
					<li>첫번째 '배송완료' 주문 발생 시 지급됩니다.</li>
					<li>해당 주문이 취소돼도 지급한 적립금은 회수되지 않습니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckMilageGetConfig(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="milage3">
	<div class="box_title">
		<h2 class="title">적립금 적립 기준</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">적립금 적립 기준</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<?php if ($_use['reward'] == 'Y') { ?>
		<tr>
			<th scope="row">리워드 적립금</th>
			<td>
				<input type="text" name="milage_reward" value="<?=$cfg['milage_reward']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?><br>
				<?php upNum("this.form.milage_reward"); ?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">회원 가입</th>
			<td>
				<input type="text" name="milage_join" value="<?=$cfg['milage_join']?>"  class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?><br>
				<?php upNum("this.form.milage_join"); ?>
				<label class="p_cursor"><input type="checkbox" name="milage_join_add_info" value="Y" <?=checked($cfg['milage_join_add_info'],"Y")?>> 가입시 추가정보 (스킨 수정 필요) 를 입력했을 경우에만 적립</label>
			</td>
		</tr>
		<tr>
			<th scope="row">추천하는 사람<div class="desc">(신규 회원)</div></th>
			<td>
				<input type="text" name="milage_recom1" value="<?=$cfg['milage_recom1']?>"  class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?> <span class="explain">(회원가입 시 추천아이디를 입력하는 사람)</span><br>
				<?php upNum("this.form.milage_recom1"); ?>
				<label><input type="checkbox" name="recom_first_order1" value="Y" <?=checked($cfg['recom_first_order1'], 'Y')?>> 추천인 첫 주문 배송완료 시 지급</label>
			</td>
		</tr>
		<tr>
			<th scope="row">추천받는 사람<div class="desc">(기존 회원)</div></th>
			<td>
				<input type="text" name="milage_recom2" value="<?=$cfg['milage_recom2']?>"  class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?> <span class="explain">(회원가입 시 추천아이디로 입력 된 사람)</span><br>
				<?php upNum("this.form.milage_recom2"); ?>
				<label><input type="checkbox" name="recom_first_order2" value="Y" <?=checked($cfg['recom_first_order2'], 'Y')?>> 추천인 첫 주문 배송완료 시 지급</label>
			</td>
		</tr>
		<tr>
			<th scope="row">개인당 추천한도</th>
			<td>
				<input type="text" name="recom_limit" value="<?=$cfg['recom_limit']?>" class="input right" size="5" onkeyup="FilterNumOnly(this)"> 명
				<div class="explain">한 회원이 지정된 수를 초과해서 추천을 받을수 없습니다. 0 으로 설정하시면 제한없이 추천을 받을수 있습니다.</div>
			</td>
		</tr>
		<tr>
			<th scope="row">상품평 등록</th>
			<td>
				<label class="p_cursor"><input type="radio" name="milage_review_auto" value="Y" <?=checked($cfg['milage_review_auto'],"Y")?>> 자동 <span class="explain">(상품평 등록시 자동 적립)</span></label>
				<label class="p_cursor"><input type="radio" name="milage_review_auto" value="N" <?=checked($cfg['milage_review_auto'],"N")?>> 수동 <span class="explain">(관리자가 확인후 적립)</span></label><br>
				<input type="text" name="milage_review" value="<?=$cfg['milage_review']?>" class="input right" size="10"> <?=$cfg['currency_type']?> <span class="explain">(일반 텍스트)</span><br>
				<?php upNum("this.form.milage_review"); ?><br>
				<input type="text" name="milage_review_image" value="<?=$cfg['milage_review_image']?>" class="input right" size="10" onkeyup="FilterNumOnly(this)"> <?=$cfg['currency_type']?> <span class="explain">(이미지 첨부시 추가적립금)</span><br>
				<?php upNum("this.form.milage_review_image"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">상품 후기 설정</th>
			<td><a href="./?body=member@product_review_config" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="상품 후기 설정 링크"> 바로가기</a></td>
		</tr>
	</table>
	<div class="box_middle2">
		<ul class="list_msg left">
			<li>미입력 또는 0<?=$cfg['currency_type']?> 입력시 적립되지 않습니다.</li>
			<li>추천인 아이디가 부정확시 추천인 적립금은 적립되지 않습니다.</li>
			<?php if (!$_use['recom_member']) { ?>
			<li>현재 추천인 기능을 사용하지 않고 계십니다.</li>
			<?php } ?>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php
	if($wm_code=="WM06-005") {
		echo "<br>";
		include $engine_dir."/_manage/config/event.php";
	}
?>
<script type="text/javascript">
	function ckMilageDefaultConfig(f) {
		//FilterNumOnly(f.milage_type_per);
        printLoading();
	}
	function ckMilageConfig(f) {
		FilterNumOnly(f.milage_use_min);
		FilterNumOnly(f.milage_join);
		FilterNumOnly(f.milage_use_max_per);
		FilterNumOnly(f.milage_use_order_min);
		FilterNumOnly(f.milage_save_min);
        printLoading();
	}
	function ckMilageGetConfig(f) {
		FilterNumOnly(f.milage_reward);
		FilterNumOnly(f.milage_join);
		FilterNumOnly(f.milage_recom1);
		FilterNumOnly(f.milage_recom2);
		FilterNumOnly(f.recom_limit);
		FilterNumOnly(f.milage_review);
        printLoading();
	}
</script>