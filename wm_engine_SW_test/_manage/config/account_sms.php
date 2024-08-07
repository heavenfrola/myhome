<?php

/**
 *  미입금 주문 자동 SMS 통보
 **/

$wec = new weagleEyeClient($_we, 'account');
$use_banking_sms = $wec->call('getBankSMS');

$scfg->def('banking_sms_time', '3');
$scfg->def('banking_sms_until', '7');

?>
<a name="sms"></a>
<table class="tbl_row">
	<caption class="hidden">미입금 주문 자동 SMS 통보</caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th scope="row">현재상태</th>
		<td>
            <label><input type="radio" name="use_banking_sms" value="Y" <?=checked($use_banking_sms, 'Y')?>> 사용함</label>
            <label><input type="radio" name="use_banking_sms" value="N" <?=checked($use_banking_sms, 'N')?>> 사용안함</label>
		</td>
	</tr>
	<tr>
		<th scope="row" rowspan="2">알림 설정</th>
        <td>
            주문일로부터
            <select name="banking_sms_time" style = "margin-bottom : 5px;"onchange="smsAuto(this.value)">
			    <?php for ($ii = 1; $ii <= 30; $ii++) {?>
				<option value="<?=$ii?>" <?=checked($cfg['banking_sms_time'], $ii, 1)?>><?=$ii?>일</option>
				<?php }?>
			</select>
            이내에 입금되지 않은 주문에 한하여 발송됩니다.
        </td>
	</tr>
    <tr>
        <td>
            주문일로부터
            <select name="banking_sms_time">
			    <?php for ($ii = 1; $ii <= 30; $ii++) {?>
				<option value="<?=$ii?>" <?=checked($cfg['banking_sms_time'], $ii, 1)?>><?=$ii?>일</option>
				<?php }?>
			</select>
            에서
            <select name="banking_sms_until">
				<?php for ($ii = 1; $ii <= 30; $ii++) {?>
				<option value="<?=$ii?>" <?=checked($cfg['banking_sms_until'], $ii,1)?>><?=$ii?>일</option>
				<?php }?>
			</select>
            까지 발송합니다.
        </td>
    </tr>
</table>
<div class="box_middle2 left">
    <ul class="list_info">
        <li>해당 서비스는 매일 오전 10시에 일괄적으로 발송됩니다.</li>
        <li>오전 10시 이후 주문은 다음 날 일괄적으로 발송됩니다.</li>
    </ul>
</div>
<script type="text/javascript">
$('select[name=banking_sms_time]').change(function() {
    $('select[name=banking_sms_time]').val($('select[name=banking_sms_time]').val());
});
</script>