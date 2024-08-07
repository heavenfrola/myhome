<?PHP

	if(empty($cfg['autobill_pg']) == true) {
		$cfg['autobill_pg'] = 'nicepay';
		$cfg['autobill_test'] = 'Y';
	}

	${'card_pg_'.$cfg['autobill_pg']} = 'on';
    $card_pg_naverpay = ($cfg['use_nsp_sbscr'] == 'Y') ? 'on' : '';

	// 독립형 여부 확인
	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));
	if($asvcs[0]->type[0] == 10) {
		define('__STAND_ALONE__', true);
	}

    $scfg->def('nsp_sub_button_type', '1');

?>
<div class="box_title first">
	<h2 class="title">정기결제 설정</h2>
</div>
<div id="select_pg" class="box_tab first">
	<ul>
        <?php if ($cfg['autobill_pg'] == 'dacom') { ?>
		<li class="tab_dacom"><a href="#" onclick="chgAutobillPg('dacom'); return false;">토스페이먼츠<span class="toggle <?=$card_pg_dacom?>"><?=strtoupper($card_pg_dacom)?></span></a></li>
        <?php } ?>
		<li class="tab_nicepay"><a href="#" onclick="chgAutobillPg('nicepay')">NICE PAY<span class="toggle <?=$card_pg_nicepay?>"><?=strtoupper($card_pg_nicepay)?></span></a></li>
        <li class="tab_naverpay">
            <a href="#" onclick="chgAutobillPg('naverpay')" style="letter-spacing: -2px">
                네이버페이 결제형
                <span class="toggle <?=$card_pg_naverpay?>"><?=strtoupper($card_pg_naverpay)?></span>
            </a>
        </li>
	</ul>
</div>

<form method="POST" action="?" target="hidden<?=$now?>" class="bill_pg bill_dacom hidden">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="autobill_pg">
	<input type="hidden" name="autobill_pg" value="dacom">
	<div class="box_middle3 left">
		<div class="list_info">
			<p class="title">[토스페이먼츠 정기결제 PG 설정 안내]</p>
			<p>토스페이먼츠 정기결제 PG 설정은 <span class="warning">독립형(구매형) Enterprise</span>에서만 가능합니다.</p>
		</div>
	</div>
	<table class="tbl_row">
		<caption class="hidden">토스페이먼츠</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label><input type="radio" name="autobill_test" value="Y" <?=checked($cfg['autobill_test'], 'Y')?>> 테스트</label>
					<label><input type="radio" name="autobill_test" value="N" <?=checked($cfg['autobill_test'], 'N')?>> 실결제</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><input type="text" name="card_auto_dacom_id" value="<?=$cfg['card_auto_dacom_id']?>" class="input"></td>
			</tr>
			<tr>
				<th scope="row">상점 키값</th>
				<td><input type="text" name="card_dacom_auto_key" value="<?=$cfg['card_dacom_auto_key']?>" class="input input_full"></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인" /></span>
	</div>
</form>

<form method="POST" action="?" target="hidden<?=$now?>" class="bill_pg bill_nicepay hidden">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="autobill_pg" value="nicepay">
	<div class="box_middle3 left">
		<div class="list_info">
			<p class="title">[거래취소 비밀번호 설정]</p>
			<p>[ 나이스페이 상점관리자 &gt; 가맹점정보 &gt; 비밀번호관리 &gt; 거래취소비밀번호 ]에서 비밀번호 설정 및 저장한 거래취소 비밀번호를 기재해주세요.</p>
		</div>
	</div>
	<table class="tbl_row">
		<caption class="hidden">NICE PAY</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
            <?php if (defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
            <?php if (empty($cfg['card_auto_nicepay_mid']) == true) { ?>
            <tr>
                <th>가입안내/신청</th>
                <td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
            </tr>
            <?php } else { ?>
			<tr>
				<th scope="row">상점 아이디</th>
				<td><?=$scfg->get('card_auto_nicepay_mid')?></td>
			</tr>
			<tr>
				<th scope="row">상점 키값</th>
				<td><?=$scfg->get('card_auto_nicepay_key')?></td>
			</tr>
			<tr>
				<th scope="row">거래취소 비밀번호</th>
				<td><input type="password" name="card_auto_nicepay_pwd" value="<?=$cfg['card_auto_nicepay_pwd']?>" class="input" maxlength="10"></td>
			</tr>
            <?php } ?>
            <?php } else { ?>
			<tr>
				<th scope="row">상점 아이디</th>
				<td><input type="text" name="card_auto_nicepay_mid" value="<?=$cfg['card_auto_nicepay_mid']?>" class="input"></td>
			</tr>
			<tr>
				<th scope="row">상점 키값</th>
				<td><input type="text" name="card_auto_nicepay_key" value="<?=$cfg['card_auto_nicepay_key']?>" class="input input_full"></td>
			</tr>
			<tr>
				<th scope="row">거래취소 비밀번호</th>
				<td><input type="password" name="card_auto_nicepay_pwd" value="<?=$cfg['card_auto_nicepay_pwd']?>" class="input" maxlength="10"></td>
			</tr>
            <?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인" /></span>
	</div>
</form>

<form method="POST" action="?" target="hidden<?=$now?>" class="bill_pg bill_naverpay hidden" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">

    <div class="box_sort left">
        <i class="icon_info"></i>
        <span class="explain">
            네이버페이 결제형은 주문서 페이지에 결제수단으로 네이버페이가 제공됩니다.
            <a href="https://r.wisa.co.kr/?code=pg_naverpay2" target="_blank" class="p_color">바로가기</a>
        </span>
    </div>
    <table class="tbl_row cfg_tbl">
		<caption class="hidden">네이버페이 결제형</caption>
		<colgroup>
			<col style="width:8%">
            <col style="width:12%">
			<col>
			<col style="width:8%">
            <col style="width:12%">
			<col>
		</colgroup>
		<tbody>
			<?php if(!$cfg['nsp_sub_partnerId'] && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
			<tr>
				<th colspan="2">가입안내/신청</th>
				<td colspan="4"><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th colspan="2">사용 여부</th>
				<td colspan="4">
					<label><input type="radio" name="use_nsp_sbscr" value="Y" <?=checked($cfg['use_nsp_sbscr'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_nsp_sbscr" value="N" <?=checked($cfg['use_nsp_sbscr'], 'N')?>> 사용안함</label>
				</td>
			</tr>
            <?php if (defined('__STAND_ALONE__') == true || $admin['admin_id'] == 'wisa') { ?>
			<tr>
                <th rowspan="3" style="border-right: 1px solid #d6d6d6">정기결제</th>
				<th>파트너 ID</th>
				<td><input type="text" name="nsp_sub_partnerId" class="input" size="30" value="<?=$cfg['nsp_sub_partnerId']?>"></td>
                <th rowspan="3" style="border-right: 1px solid #d6d6d6">일괄결제</th>
				<th>파트너 ID</th>
				<td><input type="text" name="nsp_sub_partnerId2" class="input" size="30" value="<?=$cfg['nsp_sub_partnerId2']?>"></td>
			</tr>
			<tr>
				<th>클라이언트 ID</th>
				<td><input type="text" name="nsp_sub_clientId" class="input" size="30" value="<?=$cfg['nsp_sub_clientId']?>"></td>
				<th>클라이언트 ID</th>
				<td><input type="text" name="nsp_sub_clientId2" class="input" size="30" value="<?=$cfg['nsp_sub_clientId2']?>"></td>
			</tr>
			<tr>
				<th>클라이언트 Secret</th>
				<td><input type="text" name="nsp_sub_clientSecret" class="input" size="30" value="<?=$cfg['nsp_sub_clientSecret']?>"></td>
				<th>클라이언트 Secret</th>
				<td><input type="text" name="nsp_sub_clientSecret2" class="input" size="30" value="<?=$cfg['nsp_sub_clientSecret2']?>"></td>
			</tr>
			<tr>
				<th colspan="2">체인 ID</th>
				<td colspan="4">
                    <input type="text" name="nsp_chainId" class="input" size="30" value="<?=$cfg['nsp_chainId']?>">
                    <ul class="list_info">
                        <li>체인아이디가 발급되었을 때에만 입력해주세요.</li>
                    </ul>
                </td>
			</tr>
            <?php } ?>
			<tr>
				<th colspan="2">복합과세 사용</th>
				<td colspan="4">
                    <label><input type="radio" name="nsp_sub_use_tax" value="Y" <?=checked($cfg['nsp_sub_use_tax'], 'Y')?>> 사용함</label>
                    <label><input type="radio" name="nsp_sub_use_tax" value="N" <?=checked($cfg['nsp_sub_use_tax'], 'N')?>> 사용안함</label>
                </td>
			</tr>
			<tr>
				<th colspan="2" scope="row">주문서 연결 설정</th>
				<td colspan="4">
					<label><input type="radio" name="nsp_sub_openType" value="page" <?=checked($cfg['nsp_sub_openType'], 'page')?>> 현재창 연결</label>
					<label><input type="radio" name="nsp_sub_openType" value="popup" <?=checked($cfg['nsp_sub_openType'], 'popup')?>> 새창 연결</label>
				</td>
			<tr>
            <?php } ?>
            <tr>
                <th colspan="2">아이콘</th>
                <td colspan="4">
                    <select name="nsp_sub_button_type">
                        <option value="1" <?=checked($cfg['nsp_sub_button_type'], '1', true)?>>아이콘</option>
                        <option value="2" <?=checked($cfg['nsp_sub_button_type'], '2', true)?>>텍스트</option>
                        <option value="3" <?=checked($cfg['nsp_sub_button_type'], '3', true)?>>아이콘+텍스트</option>
                    </select>
                    <div style="padding-top: 10px;">
                        <div class="nsp_button_sample sample1"><img src="<?=$engine_url?>/_engine/card.naverSimplePay/naverpay_pc.png"></div>
                        <div class="nsp_button_sample sample2">네이버페이</div>
                        <div class="nsp_button_sample sample3"><img src="<?=$engine_url?>/_engine/card.naverSimplePay/naverpay_pc.png" style="vertical-align:middle;"> 네이버페이</div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function chgAutobillPg(pg) {
	$('#select_pg').find('.active').removeClass('active');
	$('#select_pg .tab_'+pg+'>a').addClass('active');

	$('.bill_pg').addClass('hidden');
	$('.bill_'+pg).removeClass('hidden');
}

$(':radio[name=autobill_pg_sel]').change(function() {
	chgAutobillPg(this.value);
});

(setNspButton = function(){
    var val = $('select[name=nsp_sub_button_type]').val();
    $('.nsp_button_sample').hide();
    $('.nsp_button_sample.sample'+val).show();
})();

$(document).ready(function() {
	chgAutobillPg('<?=$cfg['autobill_pg']?>');
    $('select[name=nsp_sub_button_type]').change(setNspButton);
});
</script>