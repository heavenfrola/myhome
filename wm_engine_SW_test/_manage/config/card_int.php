<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  해외카드연동 설정
	' +----------------------------------------------------------------------------------------------+*/

	$alipay_useable_pay = "GBP : 영국 파운드<br/>HKD : 홍콩 달러<br/>USD : 미국 달러<br/>CHF : 스위스 프랑<br/>SGD : 싱가포르 달러<br/>SEK : 스웨덴 크로나<br/>DKK : 덴마크 크로네<br/>NOK : 노르웨이 크로네<br/>JPY : 일본 엔<br/>CAD : 캐나다 달러<br/>AUD : 호주 달러<br/>EUR : 유로<br/>NZD : 뉴질랜드 달러<br/>RUB : 러시아 루블<br/>MOP : 마카오 파타카";
	$alipay_useable_pay_arr = array('GBP','HKD','USD','CHF','SGD','SEK','DKK','NOK','JPY','CAD','AUD','EUR','NZD','RUB','MOP');

	$paypal_useable_pay = "AUD : Australian dollar<br/>BRL : Brazilian real**<br/>CAD : Canadian dollar<br/>EUR : Euro<br/>HKD : Hong Kong dollar<br/>JPY : Japanese yen*<br/>MYR : Malaysian ringgit**<br/>TWD : New Taiwan dollar*<br/>NZD : New Zealand dollar<br/>PHP : Philippine peso<br/>PLN : Polish złoty<br/>GBP : Pound sterling<br/>RUB : Russian ruble<br/>SGD : Singapore dollar<br/>THB : Thai baht<br/>USD : United States dollar";
	$paypal_useable_pay_arr = array('AUD','BRL','CAD','EUR','HKD','JPY','MYR','TWD','NZD','PHP','PLN','GBP','RUB','SGD','THB','USD');

	$paypal_c_useable_pay = "USD : 미국 달러<br/>JPY : 일본 엔<br/>NZD : 뉴질랜드<br/>SGD : 싱가포르<br/>GBP : 영국<br/>EUR : 유로<br/>AUD : 호주<br/>CAD : 캐나다<br/>HKD : 홍콩<br/>CHF : 스위스";
	$paypal_c_useable_pay_arr = array('USD','JPY','NZD','SGD','GBP','EUR','AUD','CAD','HKD','CHF');

	$sbipay_useable_pay = "USD : 미국 달러<br/>JPY : 일본 엔";
	$sbipay_useable_pay_arr = array('USD','JPY');

	$eximbay_useable_pay = "KRW : Korea Won<br/>USD : US Dollar<br/>EUR : Euro<br/>GBP : Pounds Sterling<br/>JPY : Japan Yen<br/>THB : Thailand Baht<br/>SGD : Singapore Dollar<br/>RUB : Russian Ruble<br/>HKD : Hong Kong Dollars<br/>CAD : Canadian Dollars<br/>AUD : Australian Dollars";
	$eximbay_useable_pay_arr = array('KRW','USD','EUR','GBP','JPY','THB','SGD','RUB','HKD','CAD','AUD');

	// 기본값
	if(!$cfg['alipay_real_stat']) $cfg['alipay_real_stat'] = 'Y';
	if(!$cfg['alipay_use_ssl']) $cfg['alipay_use_ssl'] = 'http';
	if($cfg['paypal_real_stat'] != 'sandbox') $cfg['paypal_real_stat'] = 'live';
	if($cfg['paypal_c_real_stat'] != 'TEST') $cfg['paypal_c_real_stat'] = 'LIVE';
	if($cfg['sbipay_real_stat'] != 'TEST') $cfg['sbipay_real_stat'] = 'LIVE';
	if($cfg['eximbay_real_stat15'] != 'TEST') $cfg['eximbay_real_stat15'] = 'LIVE';
	if($cfg['eximbay_real_stat16'] != 'TEST') $cfg['eximbay_real_stat16'] = 'LIVE';
	if($cfg['eximbay_real_stat18'] != 'TEST') $cfg['eximbay_real_stat18'] = 'LIVE';
	if($cfg['eximbay_real_stat19'] != 'TEST') $cfg['eximbay_real_stat19'] = 'LIVE';
	if($cfg['eximbay_real_stat20'] != 'TEST') $cfg['eximbay_real_stat20'] = 'LIVE';
    $scfg->def('use_econtext', 'N');

	// 서비스 타입
	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));
	if($asvcs[0]->type[0] == 10) {
		define('__STAND_ALONE__', true);
	}

	// 탭
	$easypay_list = array(
		'paypal' => 'Paypal', // Direct
		'paypal_c' => 'Paypal(e)',
		'alipay' => 'Alipay',
		'alipay_e' => 'Alipay(e)',
		'wechat' => 'Wechat pay',
		'exim' => '글로벌 신용카드',
        'econtext' => 'Econtext',
	);

    if ($scfg->comp('use_paypal_direct', 'Y') == true) unset($easypay_list['paypal_c']);
    else unset($easypay_list['paypal']);

    if ($scfg->comp('use_alipay_direct', 'Y') == true) unset($easypay_list['alipay_e']);
    else unset($easypay_list['alipay']);

	foreach($easypay_list as $key => $val) {
		if($cfg['use_'.$key] != 'Y') $cfg['use_'.$key] = 'N';
		${'_use_'.$key} = ($cfg['use_'.$key] == 'Y') ? 'on' : 'off';
	}

	function checkEditPG() {
		global $cfg, $admin;

		if(defined('__STAND_ALONE__') == true || $admin['admin_id'] == 'wisa') return true;

		foreach(func_get_args() as $val) {
			if(isset($cfg[$val]) == false || empty($cfg[$val]) == true) {
				echo '<div class="box_middle2 left">현재 서비스 연동설정이 되어있지 않습니다.<span class="box_btn_s gray"><input type="button" value="해외결제 계약안내/신청" onclick="goMywisa(\'?body=cooperate@payment_o\')"></span></div>';
				return false;
			}
		}

		return true;
	}

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="card_int">
	<div class="box_title first">
		<h2 class="title">해외결제 설정</h2>
	</div>
	<div id="select_pg" class="box_tab first">
		<ul>
			<?php foreach($easypay_list as $key => $val) { ?>
			<li class="tab_<?=$key?>"><a href="#" onclick="cardPG('<?=$key?>'); return false;"><?=$val?><span class="toggle <?=${'_use_'.$key}?>"><?=strtoupper(${'_use_'.$key})?></span></a></li>
			<?php } ?>
		</ul>
	</div>

	<!--ICB 알리페이-->
	<div id="pg_alipay"  style="display:none;">
		<?php if (checkEditPG('alipay_subcp_id', 'alipay_subcp_key') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$alipay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($alipay_useable_pay_arr)?>' name="alipay_useable_pay">
		</div>
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Alipay 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_alipay" value="Y" <?=checked($cfg['use_alipay'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_alipay" value="N" <?=checked($cfg['use_alipay'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="N" name="alipay_real_stat" <?=checked($cfg['alipay_real_stat'],'N')?>> 테스트</label>
						<label><input type="radio" value="Y" name="alipay_real_stat" <?=checked($cfg['alipay_real_stat'],'Y')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Partner ID</th>
					<td><input type="text" name="alipay_subcp_id" value="<?=$cfg['alipay_subcp_id']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">Partner Key</th>
					<td><input type="text" name="alipay_subcp_key" value="<?=$cfg['alipay_subcp_key']?>" class="input input_full"></td>
				</tr>
				<tr>
					<th scope="row">SSL 사용</th>
					<td>
						<label><input type="radio" value="https" name="alipay_use_ssl" <?=checked($cfg['alipay_use_ssl'],'https')?>> 사용함</label>
						<label><input type="radio" value="http" name="alipay_use_ssl" <?=checked($cfg['alipay_use_ssl'],'http')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">cacert 업로드</th>
					<td>
						<input type="file" name="alipay_key_cacert" value="<?=$cfg['alipay_key_cacert']?>">
						<?php if (file_exists($cfg['alipay_key_cacert']) && $cfg['alipay_key_cacert']) { ?>
						<span><label>[<input type="checkbox" value="Y" name="alipay_key_cacert_del" /> 삭제]</label> 업로드 key 파일 : <?=$cfg['alipay_key_cacert']?></span>
						<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>

	<div id="pg_alipay_e" style="display:none;">
		<?php if (checkEditPG('eximbay_mall_id19', 'eximbay_secret_key19') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$eximbay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($eximbay_useable_pay_arr)?>' name="eximbay_useable_pay">
		</div>
		<input type="hidden" value="alipay_e" name="eximbay_pay_type19">
		<input type="hidden" value="P003" name="eximbay_pay_paymethod19">  <!-- PC용 결제 코드 wechat only-->
		<input type="hidden" value="P003" name="eximbay_pay_mpaymethod19">  <!-- 모바일용 결제 코드 wechat only-->
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Alipay 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_alipay_e" value="Y" <?=checked($cfg['use_alipay_e'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_alipay_e" value="N" <?=checked($cfg['use_alipay_e'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="TEST" name="eximbay_real_stat19" <?=checked($cfg['eximbay_real_stat19'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="eximbay_real_stat19" <?=checked($cfg['eximbay_real_stat19'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">가맹점 아이디</th>
					<td><input type="text" name="eximbay_mall_id19" value="<?=$cfg['eximbay_mall_id19']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key19" value="<?=$cfg['eximbay_secret_key19']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>

	<div id="pg_paypal" style="display:none;">
		<?php if (checkEditPG('paypal_subcp_id', 'paypal_subcp_key') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$paypal_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($paypal_useable_pay_arr)?>' name="paypal_useable_pay">
		</div>
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Paypal 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_paypal" value="Y" <?=checked($cfg['use_paypal'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_paypal" value="N" <?=checked($cfg['use_paypal'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="sandbox" name="paypal_real_stat" <?=checked($cfg['paypal_real_stat'],'sandbox')?>> 테스트</label>
						<label><input type="radio" value="live" name="paypal_real_stat" <?=checked($cfg['paypal_real_stat'],'live')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 ID</th>
					<td><input type="text" name="paypal_subcp_id" value="<?=$cfg['paypal_subcp_id']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">키 패스워드</th>
					<td><input type="text" name="paypal_subcp_key" value="<?=$cfg['paypal_subcp_key']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>

	<div id="pg_paypal_c" style="display:none;">
		<?php if (checkEditPG('eximbay_mall_id16', 'eximbay_secret_key16') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$eximbay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($eximbay_useable_pay_arr)?>' name="eximbay_useable_pay">
		</div>
		<input type="hidden" value="paypal" name="eximbay_pay_type16">
		<input type="hidden" value="P001" name="eximbay_pay_paymethod16">
		<input type="hidden" value="P001" name="eximbay_pay_mpaymethod16">
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Paypal 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_paypal_c" value="Y" <?=checked($cfg['use_paypal_c'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_paypal_c" value="N" <?=checked($cfg['use_paypal_c'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="TEST" name="eximbay_real_stat16" <?=checked($cfg['eximbay_real_stat16'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="eximbay_real_stat16" <?=checked($cfg['eximbay_real_stat16'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">가맹점 아이디</th>
					<td><input type="text" name="eximbay_mall_id16" value="<?=$cfg['eximbay_mall_id16']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key16" value="<?=$cfg['eximbay_secret_key16']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>

		<!--
		<table class="tbl_row cfg_tbl">
			<caption>구 Paypal 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="TEST" name="paypal_c_real_stat" <?=checked($cfg['paypal_c_real_stat'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="paypal_c_real_stat" <?=checked($cfg['paypal_c_real_stat'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Public Key</th>
					<td><input type="text" name="paypal_c_public_key" value="<?=$cfg['paypal_c_public_key']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">Secret Key</th>
					<td><input type="text" name="paypal_c_secret_key" value="<?=$cfg['paypal_c_secret_key']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		-->

		<?php } ?>
	</div>

	<div id="pg_econtext" style="display:none;">
		<?php if (checkEditPG('eximbay_mall_id18', 'eximbay_secret_key18') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$eximbay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($eximbay_useable_pay_arr)?>' name="econtext_useable_pay">
		</div>
		<input type="hidden" value="econtext" name="eximbay_pay_type15">
		<input type="hidden" value="P006" name="eximbay_pay_paymethod15">
        <table class="tbl_row cfg_tbl">
            <caption class="hidden">Econtext 일본 편의점 계좌이체</caption>
            <colgroup>
                <col style="width:15%">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row">사용여부</th>
                    <td>
                        <label><input type="radio" name="use_econtext" value="Y" <?=checked($cfg['use_econtext'], 'Y')?>> 사용함</label>
                        <label><input type="radio" name="use_econtext" value="N" <?=checked($cfg['use_econtext'], 'N')?>> 사용안함</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">실행모드</th>
                    <td>
                        <label><input type="radio" value="TEST" name="eximbay_real_stat15" <?=checked($cfg['eximbay_real_stat15'],'TEST')?>> 테스트</label>
                        <label><input type="radio" value="LIVE" name="eximbay_real_stat15" <?=checked($cfg['eximbay_real_stat15'],'LIVE')?>> 실결제</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">가맹점 아이디</th>
                    <td><input type="text" name="eximbay_mall_id15" value="<?=$cfg['eximbay_mall_id15']?>" class="input"></td>
                </tr>
                <tr>
                    <th scope="row">가맹점 Secret Key</th>
                    <td><input type="text" name="eximbay_secret_key15" value="<?=$cfg['eximbay_secret_key15']?>" class="input input_full"></td>
                </tr>
			</tbody>
		</table>
		<?php } ?>
	</div>

	<div id="pg_wechat" style="display:none;">
		<?php if (checkEditPG('eximbay_mall_id18', 'eximbay_secret_key18') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$eximbay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($eximbay_useable_pay_arr)?>' name="eximbay_useable_pay">
		</div>
		<input type="hidden" value="wechat" name="eximbay_pay_type18">
		<input type="hidden" value="P141" name="eximbay_pay_paymethod18">  <!-- PC용 결제 코드 wechat only-->
		<input type="hidden" value="P142" name="eximbay_pay_mpaymethod18">  <!-- 모바일용 결제 코드 wechat only-->
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Wechat pay 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_wechat" value="Y" <?=checked($cfg['use_wechat'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_wechat" value="N" <?=checked($cfg['use_wechat'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="TEST" name="eximbay_real_stat18" <?=checked($cfg['eximbay_real_stat18'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="eximbay_real_stat18" <?=checked($cfg['eximbay_real_stat18'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">가맹점 아이디</th>
					<td><input type="text" name="eximbay_mall_id18" value="<?=$cfg['eximbay_mall_id18']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key18" value="<?=$cfg['eximbay_secret_key18']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>

	<div id="pg_exim" style="display:none;">
		<?php if (checkEditPG('eximbay_mall_id20', 'eximbay_secret_key20') == true) { ?>
		<div class="box_sort left">
			<i class="icon_info"></i>
			<span class="explain">
				사용가능 화폐에 포함되지 않은 결제화폐는 사용할 수 없습니다.
				<a href="#none" onmouseover="showToolTipHTML(event,'<?=$eximbay_useable_pay?>')" onmouseout="hideToolTip();" class="p_color">사용가능 화폐</a>
			</span>
			<input type="hidden" value='<?=serialize($eximbay_useable_pay_arr)?>' name="eximbay_useable_pay">
		</div>
		<input type="hidden" value="eximbay" name="eximbay_pay_type20">
		<input type="hidden" value="P000" name="eximbay_pay_paymethod20">
		<table class="tbl_row cfg_tbl">
			<caption class="hidden">Eximbay 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">사용여부</th>
					<td>
						<label><input type="radio" name="use_exim" value="Y" <?=checked($cfg['use_exim'], 'Y')?>> 사용함</label>
						<label><input type="radio" name="use_exim" value="N" <?=checked($cfg['use_exim'], 'N')?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">실행모드</th>
					<td>
						<label><input type="radio" value="TEST" name="eximbay_real_stat20" <?=checked($cfg['eximbay_real_stat20'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="eximbay_real_stat20" <?=checked($cfg['eximbay_real_stat20'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">가맹점 아이디</th>
					<td><input type="text" name="eximbay_mall_id20" value="<?=$cfg['eximbay_mall_id20']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key20" value="<?=$cfg['eximbay_secret_key20']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function cardPG(pg) {
	$('#select_pg').find('.active').removeClass('active');
	$('#select_pg .tab_'+pg+'>a').addClass('active');

	$('div[id^=pg_]').hide();
	$('#pg_'+pg).show();
}
cardPG('paypal');
</script>