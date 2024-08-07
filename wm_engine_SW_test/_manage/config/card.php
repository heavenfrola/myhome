<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카드연동 설정
	' +----------------------------------------------------------------------------------------------+*/
	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));
	if($asvcs[0]->type[0] == 10) {
		define('__STAND_ALONE__', true);
	}

	if(defined('__STAND_ALONE__') || $admin['admin_id'] == 'wisa') {
		include_once $engine_dir.'/_manage/config/card_wisa.php';
		if($cfg['mobile_use'] == 'Y' || $cfg['mobile_pg_use'] == 'Y') include_once $engine_dir.'/_manage/config/card_mobile_wisa.php';
	} else {
		include_once $engine_dir.'/_manage/config/card_admin.php';
		if($cfg['mobile_use'] == 'Y' || $cfg['mobile_pg_use'] == 'Y') include_once $engine_dir.'/_manage/config/card_mobile_admin.php';
	}
	if(!$cfg['escrow_deli_term']) $cfg['escrow_deli_term'] = '05';
	if(empty($cfg['card_confirm']) == true) $cfg['card_confirm'] = 'N';

?>
<?php if ($scfg->comp('pay_type_7', 'Y') == true) {?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="config_code" value="danal">
	<div class="box_title">
		<h2 class="title">휴대폰 결제 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_info">
			<li>사용 전 반드시 계약 여부를 확인해 주세요.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">휴대폰 결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">서비스 선택</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_danal" value="Y" <?=checked($cfg['mobile_danal'], 'Y')?>> 다날 휴대폰 결제</label>
				</td>
			</tr>
			<tr>
				<th scope="row">서비스 아이디 입력</th>
				<td>
					<input type="text" name="danal_subcp_id" value="<?=$cfg['danal_subcp_id']?>" class="input">
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>

<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="card_info">
	<div class="box_title">
		<h2 class="title">신용카드 결제 안내문구 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_info">
			<li>PG연동 시 각 카드사마다 심사를 진행하며, 승인기간이 10~15일 정도 소요될 수 있습니다.</li>
			<li>승인되지 않은 카드사로 결제 요청 시 결제가 진행되지 않으므로 안내 문구를 입력해주시기 바랍니다.</li>
			<li>사용 용도에 따라 별도의 안내 문구도 삽입할 수 있습니다. (무이자 할부 안내 등)</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">신용카드 결제 안내문구 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">유의사항 및 공지사용</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_confirm" value="N" <?=checked($cfg['card_confirm'],"N")?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="card_confirm" value="Y" <?=checked($cfg['card_confirm'],"Y")?>> 사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">안내 문구</th>
				<td>
					<input type="text" name="use_card" value="<?=$cfg['use_card']?>" class="input input_full">
					<ul class="list_info tp">
						<li>예) 현재 사용가능한 카드는 삼성, 엘지입니다</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>