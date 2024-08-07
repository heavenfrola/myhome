<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  간편결제 설정
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Kakao\KakaoTalkPay;

	// 독립형 여부 확인
	$weca = new weagleEyeClient($_we, 'account');
	$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));
	if($asvcs[0]->type[0] == 10) {
		define('__STAND_ALONE__', true);
	}

    //국내결제 세팅비 결제 체크 (1:미결제 , 2:결제)
    $wec_setting_fee = new WeagleEyeClient($_we, 'etc');
    $setting_fee_chk = $wec_setting_fee->call("getPgSetStat",array('key_code'=>$wec->config['wm_key_code']));

	// 네이버페이
	$wec_acc = new weagleEyeClient($_we, 'account');
	$npay = $wec_acc->call('checkoutStatus');

	$npay_status = $npay[0]->stat[0];
	$npay_id = $npay[0]->checkout_id[0];
	$npay_key = $npay[0]->auth_key[0];
	$npay_btn_key = $npay[0]->button_auth_key[0];

	if($npay_status > 0) $cfg['use_checkout'] = 'Y';
	if($cfg['checkout_auth'] != 'N') $cfg['checkout_auth'] = 'Y';
	if(!$cfg['checkout_detail_btn']) $cfg['checkout_detail_btn'] = 'A_1';
	if(!$cfg['checkout_cart_btn']) $cfg['checkout_cart_btn'] = 'A_1';
	if(!$cfg['m_checkout_detail_btn']) $cfg['m_checkout_detail_btn'] = 'MA_1';
	if(!$cfg['m_checkout_cart_btn']) $cfg['m_checkout_cart_btn'] = 'MA_1';
	if(!$cfg['checkout_wish']) $cfg['checkout_wish'] = '2';
	if(!$cfg['npay_ver']) $cfg['npay_ver'] = 1;
	if(!$cfg['npay_review_stat']) $cfg['npay_review_stat'] = 2;
	if(!$cfg['use_npay_qna']) $cfg['use_npay_qna'] = 'N';
	if(!$cfg['npay_target']) $cfg['npay_target'] = 'self';
	if(!$cfg['npay_truncate_cart']) $cfg['npay_truncate_cart'] = 'Y';

	$skin = array(
		'A_1' => 'A-Type', 'B_1' => 'B-Type',
		'C_1' => 'C-Type 색상 1', 'C_2' => 'C-Type 색상 2', 'C_3' => 'C-Type 색상 3',
		'D_1' => 'D-Type 색상 1', 'D_2' => 'D-Type 색상 2', 'D_3' => 'D-Type 색상 3',
		'E_1' => 'E-Type 색상 1', 'E_2' => 'E-Type 색상 2', 'E_3' => 'E-Type 색상 3'
	);

	$m_skin = array(
		'MA_1' => 'A-Type',
		'MB_1' => 'B-Type',
	);

    // 네이버페이 결제형
    $scfg->def('use_nsp', 'N');
    $scfg->def('nsp_use_tax', 'N');
    $scfg->def('nsp_openType', 'page');

    // 카카오 페이구매
    $_talkpay_btn_types = array(
        '210x83_false' => '210x83 라이트',
        '210x83_true' => '210x83 투명',
        '236x88_false' => '236x88 라이트',
        '236x88_true' => '236x88 투명',
        '285x88_false' => '285x88 라이트',
        '285x88_true' => '285x88 투명',
        '124x115_false' => '124x115 라이트',
        '124x115_true' => '124x115 투명',
    );
    $_talkpay_btn_m_types = array(
        '290x95_false' => '290x95 라이트',
        '290x95_true' => '290x95 투명',
        '310x100_false' => '310x100 라이트',
        '310x100_true' => '310x100 투명',
    );
    $scfg->def('talkpay_truncate_cart', 'N');
    $scfg->def('talkpay_btn_type', '210x83_false');
    $scfg->def('talkpay_btn_type_m', '290x95_false');
    $scfg->def('nsp_button_type', '1');

    // 카카오페이구매 상점 상태
    $kkt_status = '';
    if ($scfg->get('talkpay_ShopKey')) {
        $kkt = new KakaoTalkPay($scfg);
        $kkt_info = $kkt->shopStatus();
        switch($kkt_info->serviceStatus) {
            case 'ACTIVE' : $kkt_status = '정상 (ACTIVE)'; break;
            case 'INACTIVE' : $kkt_status = '미연동 (INACTIVE)'; break;
            case 'BLOCKED' : $kkt_status = '이용제한 (BLOCKED)'; break;
            case 'PAUSE' : $kkt_status = '일시정지 (PAUSE)'; break;
            case 'UNAVAILABLE' : $kkt_status = '서비스 불가 (UNAVAILABLE)'; break;
        }
        if (!$kkt_status) {
            $kkt_status = $kkt_info->message;
        }
    }

	// 페이코
	if(!$cfg['use_payco']) $cfg['use_payco'] = 'N';
	if(!$cfg['payco_testmode']) $cfg['payco_testmode'] = 'Y';
	if(!$cfg['payco_productId']) $cfg['payco_productId'] = 'PROD_EASY';
	if(!$cfg['payco_productId2']) $cfg['payco_productId2'] = 'DELIVERY_PROD';

	$_payco_btn_types = array(
		'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
		'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
		'C1', 'C2', 'C3', 'C4', 'C5', 'C6'
	);

	// 카카오페이
	if(!$cfg['use_kakaopay']) $cfg['use_kakaopay'] = 'N';

	$wec_etc = new weagleEyeClient($GLOBALS['_we'], 'etc');
	$result = $wec_etc->call('getKakaoKey', array('kakao_cid' => $cfg['kakao_cid']));
	$wisa_kakao = json_decode($result, true);

	if(!$cfg['kakao_id'] && !$cfg['kakao_version']) $cfg['kakao_version'] = "new";

	if($cfg['kakao_cid'] && $cfg['kaka_admin_key'] && $cfg['kakao_version'] == "new") {
		$disabled = ($wisa_kakao['kakao_result'] == 'true') ? "" : "disabled";
	}

	// 탭처리
	$easypay_list = array(
		'checkout' => '네이버페이 주문형',
		'nsp' => '네이버페이 결제형',
        'talkpay' => '톡체크아웃',
		'kakaopay' => '카카오페이',
		'payco' => '페이코',
		'tosspayment' => '토스계좌결제',
		'tosscard' => '토스결제',
        'samsungpay' => '삼성페이'
	);
	if($cfg['use_tosspayment'] != 'Y') unset($easypay_list['tosspayment']);
	foreach($easypay_list as $key => $val) {
		if(empty($cfg['use_'.$key]) == true) $cfg['use_'.$key] = 'N';
		${'_use_'.$key} = ($cfg['use_'.$key] == 'Y') ? 'on' : 'off';
	}

?>
<style>
.nsp_button_sample {
    display: none;
}
</style>

<?php if ($npay_status == 2 && $npay_id != $cfg['checkout_id']) { ?>
<div class="msg_topbar sub quad warning">
	네이버페이 승인이 완료되었으나, 승인정보가 아직 쇼핑몰에 반영되지 않았습니다.<br>
	상세페이지 및 장바구니 버튼을 선택한 후	확인 버튼을 클릭하시면 네이버페이가 활성화됩니다.
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>

<div class="box_title first">
	<h2 class="title">간편결제 설정</h2>
</div>
<div id="select_pg" class="box_tab first">
	<ul>
		<?php foreach($easypay_list as $key => $val) { ?>
		<li class="tab_<?=$key?>"><a href="#<?=$key?>" onclick="cardPG('<?=$key?>');" style="letter-spacing:-2px"><?=$val?><span class="toggle <?=${'_use_'.$key}?>"><?=strtoupper(${'_use_'.$key})?></span></a></li>
		<?php } ?>
	</ul>
</div>

<form id="pg_checkout" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@naverpay.exe">
	<input type="hidden" name="checkout_auth" value="Y">
	<input type="hidden" name="checkout_wish" value="2">
	<input type="hidden" name="config_code" value="naverpay_config">
	<div class="box_sort left">
        <i class="icon_info"></i>
        <span class="explain">
            네이버페이 주문형은 상품 상세페이지, 장바구니에 네이버페이 구매버튼이 노출되며, 네이버페이 주문서를 통해 주문을 진행합니다.
            <a href="https://r.wisa.co.kr/?code=pg_naverpay1" target="_blank" class="p_color">바로가기</a>
        </span>
	</div>
	<table class="tbl_row cfg_tbl">
		<caption class="hidden">네이버페이 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if ($npay_status < 2) { ?>
			<tr>
				<th scope="row">가입안내/신청</th>
				<td>
					<?php if ($npay_status == 1) { ?>
					<div class="explain">네이버페이 가입승인 진행 중입니다.</div>
					<?php } else { ?>
					<span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span>
					<span class="box_btn_s"><input type="button" value="네이버페이 아이디 설정하기" onclick="goMywisa('?body=support@cooperate@npay_reg');"></span>
					<?php } ?>
				</td>
			</tr>
			<?php } else if($npay_status == 2) { ?>
			<tr>
				<th scope="row">네이버페이 아이디</th>
				<td><input type="text" name="checkout_id" class="input" size="15" value="<?=$npay_id?>" readonly style="background:#ffecec"></td>
			</tr>
			<tr>
				<th scope="row">인증키</th>
				<td><input type="text" name="checkout_key" class="input" size="50" value="<?=$npay_key?>" readonly style="background:#ffecec"></td>
			</tr>
			<tr>
				<th scope="row">주문API 버전</th>
				<td>
					<label><input type="radio" name="npay_ver" value="1" <?=checked($cfg['npay_ver'], 1)?>> 1.0</label>
					<label><input type="radio" name="npay_ver" value="2" <?=checked($cfg['npay_ver'], 2)?>> 2.1 (통합장바구니)</label>
					<ul class="list_info tp">
						<li>도서산간 배송비 연동은 2.1 버전에서만 제공됩니다.</li>
						<li>네이버페이 최종 승인 요청 시 사용할 API 버전을 네이버페이에 전달해야 하며 버전이 다를 경우 주문이 되지 않습니다.</li>
						<li>네이버페이 2.1버전으로 전환 시 1.0버전으로 변경이 불가합니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">주문서 연결 설정</th>
				<td>
					<label><input type="radio" name="npay_target" value="self" <?=checked($cfg['npay_target'], 'self')?>> 현재창 연결</label>
					<label><input type="radio" name="npay_target" value="blank" <?=checked($cfg['npay_target'], 'blank')?>> 새창 연결</label>
				</td>
			<tr>
			<tr>
				<th scope="row">장바구니 설정</th>
				<td>
					<ul>
						<li><label><input type="radio" name="npay_truncate_cart" value="Y" <?=checked($cfg['npay_truncate_cart'], 'Y')?>> 주문서 페이지 이동 시 장바구니를 비웁니다.</label></li>
						<li><label><input type="radio" name="npay_truncate_cart" value="N" <?=checked($cfg['npay_truncate_cart'], 'N')?>> 주문서 페이지 이동 시 장바구니를 유지합니다.</label></li>
					</ul>
				</td>
            </tr>
			<tr>
				<th scope="row" rowspan="2">상품평 수집</th>
				<td>
					<ul>
						<li><label><input type="checkbox" name="npay_review_general" value="Y" <?=checked($cfg['npay_review_general'], 'Y')?>> 일반 상품평(단문형)</label></li>
						<li><label><input type="checkbox" name="npay_review_premium" value="Y" <?=checked($cfg['npay_review_premium'], 'Y')?>> 프리미엄 상품평(장문형, 이미지 포함)</label></li>
					</ul>
					<ul class="list_info tp">
						<li>프리미엄 상품평 수집 시 <a href="?body=member@product_review_config" target="_blank">상품후기설정</a>이 '에디터 사용함'으로 설정되어 있어야 문서양식 및 이미지가 정상적으로 표기됩니다.</li>
						<li>네이버페이를 통해 작성된 상품평은 관리자에서 수정 및 삭제되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<ul>
						<li><label><input type="radio" name="npay_review_stat" value="2" <?=checked($cfg['npay_review_stat'], 2)?>> 바로 게시</label></li>
						<li><label><input type="radio" name="npay_review_stat" value="1" <?=checked($cfg['npay_review_stat'], 1)?>> 관리자 승인 후 게시</label></li>
					</ul>

				</td>
			</tr>
			<tr>
				<th scope="row">상품문의수집</th>
				<td>
					<ul>
						<li><label><input type="radio" name="use_npay_qna" value="N" <?=checked($cfg['use_npay_qna'], 'N')?>> 사용안함</label></li>
						<li><label><input type="radio" name="use_npay_qna" value="Y" <?=checked($cfg['use_npay_qna'], 'Y')?>> 사용함</label></li>
					</ul>
					<ul class="list_info tp">
						<li>상품문의는 고객CRM <a href="./?body=member@product_qna" target="_blank">상품Q&A</a> 메뉴 접근 시 최근 5일 치(최대 1,000개)가 수집됩니다.</li>
						<li>상품문의는 비밀글로 수집되며, 비밀번호는 <span>네이버페이 주문번호</span>로 자동 설정됩니다.</li>
						<li>수집되는 문의에 대해 스마트윙에서 답변 작성 시 바로 네이버페이도 적용됩니다.</li>
						<li>네이버페이 관리자에서 답변이 작성된 경우 답변일은 문의일로 강제 지정됩니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">버튼인증키</th>
				<td>
					<input type="text" name="checkout_btn_key" class="input" size="50" value="<?=$npay_btn_key?>" readonly style="background:#ffecec">
				</td>
			</tr>
			<tr>
				<th scope="row">상세페이지 버튼</th>
				<td>
					<?=selectArray($skin, 'checkout_detail_btn', null, null, $cfg['checkout_detail_btn'], "npay_btn('detail', this.value, '')")?>
					<div style="margin-top:5px;"><img id="checkbtn_detail" src="<?=$engine_url?>/_manage/image/openmarket/checkout/checkout_<?=$cfg['checkout_detail_btn']?>.png"></div>
				</td>
			</tr>
			<tr>
				<th scope="row">장바구니 버튼</th>
				<td>
					<?=selectArray($skin, 'checkout_cart_btn', null, null, $cfg['checkout_cart_btn'], "npay_btn('cart', this.value, 'b')")?>
					<span class="explain">장바구니에서는 찜하기 버튼이 출력되지 않습니다.</div>
					<div style="margin-top:5px;"><img id="checkbtn_cart" src="<?=$engine_url?>/_manage/image/openmarket/checkout/checkout_<?=$cfg['checkout_cart_btn']?>b.png"></div>
				</td>
			</tr>
			<tr>
				<th scope="row">모바일<br>상세페이지 버튼</th>
				<td>
					<?=selectArray($m_skin, 'm_checkout_detail_btn', null, null, $cfg['m_checkout_detail_btn'], "npay_btn('m_detail', this.value, '')")?>
					<div style="margin-top:5px;"><img id="checkbtn_m_detail" src="<?=$engine_url?>/_manage/image/openmarket/checkout/checkout_<?=$cfg['m_checkout_detail_btn']?>.png" style="width:250px"></div>
				</td>
			</tr>
			<tr>
				<th scope="row">모바일<br>장바구니 버튼</th>
				<td>
					<?=selectArray($m_skin, 'm_checkout_cart_btn', null, null, $cfg['m_checkout_cart_btn'], "npay_btn('m_cart', this.value, 'b')")?>
					<span class="explain">장바구니에서는 찜하기 버튼이 출력되지 않습니다.</div>
					<div style="margin-top:5px;"><img id="checkbtn_m_cart" src="<?=$engine_url?>/_manage/image/openmarket/checkout/checkout_<?=$cfg['m_checkout_cart_btn']?>b.png" style="width:250px"></div>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_nsp" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
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
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
            <?php if(!$cfg['nsp_partnerId'] && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa' && $setting_fee_chk !== '2') { ?>
			<tr>
				<th>가입안내/신청</th>
				<td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th>사용 여부</th>
				<td>
					<label><input type="radio" name="use_nsp" value="Y" <?=checked($cfg['use_nsp'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_nsp" value="N" <?=checked($cfg['use_nsp'], 'N')?>> 사용안함</label>
				</td>
			</tr>
            <?php if (defined('__STAND_ALONE__') == true || $admin['admin_id'] == 'wisa' || $setting_fee_chk === '2') { ?>
			<tr>
				<th>파트너 ID</th>
				<td><input type="text" name="nsp_partnerId" class="input" size="30" value="<?=$cfg['nsp_partnerId']?>"></td>
			</tr>
			<tr>
				<th>클라이언트 ID</th>
				<td><input type="text" name="nsp_clientId" class="input" size="30" value="<?=$cfg['nsp_clientId']?>"></td>
			</tr>
			<tr>
				<th>클라이언트 Secret</th>
				<td><input type="text" name="nsp_clientSecret" class="input" size="30" value="<?=$cfg['nsp_clientSecret']?>"></td>
			</tr>
			<tr>
				<th>체인 ID</th>
				<td>
                    <input type="text" name="nsp_chainId" class="input" size="30" value="<?=$cfg['nsp_chainId']?>">
                    <ul class="list_info">
                        <li>체인아이디가 발급되었을 때에만 입력해주세요.</li>
                    </ul>
                </td>
			</tr>
            <?php } ?>
			<tr>
				<th>복합과세 사용</th>
				<td>
                    <label><input type="radio" name="nsp_use_tax" value="Y" <?=checked($cfg['nsp_use_tax'], 'Y')?>> 사용함</label>
                    <label><input type="radio" name="nsp_use_tax" value="N" <?=checked($cfg['nsp_use_tax'], 'N')?>> 사용안함</label>
                </td>
			</tr>
			<tr>
				<th scope="row">주문서 연결 설정</th>
				<td>
					<label><input type="radio" name="nsp_openType" value="page" <?=checked($cfg['nsp_openType'], 'page')?>> 현재창 연결</label>
					<label><input type="radio" name="nsp_openType" value="popup" <?=checked($cfg['nsp_openType'], 'popup')?>> 새창 연결</label>
				</td>
			<tr>
            <?php } ?>
            <tr>
                <th>아이콘</th>
                <td>
                    <select name="nsp_button_type">
                        <option value="1" <?=checked($cfg['nsp_button_type'], '1', true)?>>아이콘</option>
                        <option value="2" <?=checked($cfg['nsp_button_type'], '2', true)?>>텍스트</option>
                        <option value="3" <?=checked($cfg['nsp_button_type'], '3', true)?>>아이콘+텍스트</option>
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

<form id="pg_talkpay" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
    <div class="box_sort left">
		<i class="icon_info"></i>
		<span class="explain">
            2023년 09월 05일부로 카카오 페이구매의 서비스명이 톡체크아웃으로 변경되었습니다.
		</span>
    </div>
    <table class="tbl_row cfg_tbl">
		<caption class="hidden">톡체크아웃 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if (!$cfg['talkpay_ShopKey'] && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
			<tr>
				<th>가입안내/신청</th>
				<td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th>사용 여부</th>
				<td>
					<label><input type="radio" name="use_talkpay" value="Y" <?=checked($cfg['use_talkpay'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_talkpay" value="N" <?=checked($cfg['use_talkpay'], 'N')?>> 사용안함</label>
				</td>
			</tr>
            <?php if ($kkt_status) { ?>
			<tr>
				<th>판매점 서비스 상태</th>
				<td>
                    <?=$kkt_status?>
                    <?php if ($kkt_info->serviceStatus == 'PAUSE') { ?>
                    <span class="msg_bubble warning">상태가 일시정지일 경우 확인버튼을 한번 더 눌러주세요.</span>
                    <?php } ?>
				</td>
			</tr>
            <?php } ?>
			<tr>
				<th scope="row">판매점 식별값</th>
				<td><input type="text" name="talkpay_ShopKey" class="input" size="50" value="<?=$cfg['talkpay_ShopKey']?>"></td>
			</tr>
			<tr>
				<th scope="row">장바구니 설정</th>
				<td>
					<ul>
						<li><label><input type="radio" name="talkpay_truncate_cart" value="Y" <?=checked($cfg['talkpay_truncate_cart'], 'Y')?>> 주문서 페이지 이동 시 장바구니를 비웁니다.</label></li>
						<li><label><input type="radio" name="talkpay_truncate_cart" value="N" <?=checked($cfg['talkpay_truncate_cart'], 'N')?>> 주문서 페이지 이동 시 장바구니를 유지합니다.</label></li>
					</ul>
				</td>
            </tr>
            <tr>
                <th>구매 버튼</th>
                <td>
                    <?=selectArray($_talkpay_btn_types, 'talkpay_btn_type', false, null, $cfg['talkpay_btn_type'])?>
                    <div style="margin-top:5px;"><img id="talkpay_detail" src="<?=$engine_url?>/_manage/image/openmarket/kakao_checkout/talkpay_<?=$cfg['talkpay_btn_type']?>.png"></div>
                </td>
            </tr>
            <tr>
                <th>모바일 구매 버튼</th>
                <td>
                    <?=selectArray($_talkpay_btn_m_types, 'talkpay_btn_type_m', false, null, $cfg['talkpay_btn_type_m'])?>
                    <div style="margin-top:5px;"><img id="talkpay_detail_m" src="<?=$engine_url?>/_manage/image/openmarket/kakao_checkout/talkpay_<?=$cfg['talkpay_btn_type_m']?>.png"></div>
                </td>
            </tr>
            <tr>
                <th>스낵모드</th>
                <td>
                    <label><input type="checkbox" name="talkpay_btn_snack_mb" value="Y" <?=checked($cfg['talkpay_btn_snack_mb'], 'Y')?>> 모바일 화면</label>
                    <ul class="list_info">
                        <li>카카오톡 앱 내에서 구매하기 버튼을 하단에 강조하고 싶으실 경우 체크해주세요.</li>
                    </ul>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="box_middle2 left">
        <ul class="list_info">
            <li>첫 설정 시 시간이 오래 소요될수 있습니다.</li>
            <li>주문내역이 많은 사이트인 경우 유휴시간을 이용하여 설정해주시기 바랍니다.</li>
        </ul>
    </div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_payco" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
	<div class="box_sort left">
		<i class="icon_info"></i>
		<span class="explain">
			상품 상세페이지 및 장바구니 페이지에 바로가기 버튼이 보이지 않는 경우
			<a href="?body=design@editor&type=&edit_pg=4%2F2" target="_blank" class="p_color">상품 상세페이지</a> 및
			<a href="?body=design@editor&type=&edit_pg=3%2F1" target="_blank" class="p_color">장바구니 페이지</a>에
			{{$페이코즉시구매버튼}} 디자인코드를 삽입하시기 바랍니다.
		</span>
	</div>
	<table class="tbl_row cfg_tbl">
		<caption class="hidden">페이코 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if (!$cfg['payco_sellerKey'] && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
			<tr>
				<th>가입안내/신청</th>
				<td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th>사용 여부</th>
				<td>
					<label><input type="radio" name="use_payco" value="Y" <?=checked($cfg['use_payco'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_payco" value="N" <?=checked($cfg['use_payco'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<?php if (defined('__STAND_ALONE__') == true || $admin['admin_id'] == 'wisa') { ?>
			<tr>
				<th>sellerKey</th>
				<td><input type="text" name="payco_sellerKey" value="<?=$cfg['payco_sellerKey']?>" class="input" size="20"></td>
			</tr>
			<?php } elseif($cfg['payco_sellerKey']) { ?>
			<tr>
				<th>sellerKey</th>
				<td><?=$cfg['payco_sellerKey']?></td>
			</tr>
			<?php } ?>
			<tr>
				<th>실행 모드</th>
				<td>
					<label><input type="radio" name="payco_testmode" value="Y" <?=checked($cfg['payco_testmode'], 'Y')?>> 테스트</label>
					<label><input type="radio" name="payco_testmode" value="N" <?=checked($cfg['payco_testmode'], 'N')?>> 실결제</label>
				</td>
			</tr>
			<tr>
				<th>상품상세 바로구매 버튼(PC)</th>
				<td>
					<?=selectArray($_payco_btn_types, 'payco_type1_sel', true, null, $cfg['payco_type1_sel'], 'payco_btn(1, this)')?>
					<div id="payco_type1" style="margin-top:5px"></div>
				</td>
			</tr>
			<tr>
				<th>장바구니 바로구매 버튼(PC)</th>
				<td>
					<?=selectArray($_payco_btn_types, 'payco_type2_sel', true, null, $cfg['payco_type2_sel'], 'payco_btn(2, this)')?>
					<div id="payco_type2" style="margin-top:5px"></div>
				</td>
			</tr>
			<tr>
				<th>상품상세 바로구매 버튼(Mobile)</th>
				<td>
					<?=selectArray($_payco_btn_types, 'payco_type3_sel', true, null, $cfg['payco_type3_sel'], 'payco_btn(3, this)')?>
					<div id="payco_type3" style="margin-top:5px"></div>
				</td>
			</tr>
			<tr>
				<th>장바구니 바로구매 버튼(Mobile)</th>
				<td>
					<?=selectArray($_payco_btn_types, 'payco_type4_sel', true, null, $cfg['payco_type4_sel'], 'payco_btn(4, this)')?>
					<div id="payco_type4" style="margin-top:5px"></div>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_kakaopay" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
	<div class="box_sort left">
	</div>
	<table class="tbl_row cfg_tbl" id="kakao">
		<caption class="hidden">카카오페이 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if (!$cfg['kakao_id'] && !$cfg['kakao_cid'] && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
			<tr>
				<th>가입안내/신청</th>
				<td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th>사용 여부</th>
				<td>
					<label><input type="radio" name="use_kakaopay" value="Y" <?=checked($cfg['use_kakaopay'], 'Y')?> <?=$disabled?>> 사용함</label>
					<label><input type="radio" name="use_kakaopay" value="N" <?=checked($cfg['use_kakaopay'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">연동방식</th>
				<td>
					<label class="p_cursor"><input type="radio" name="kakao_version" value="" <?=checked($cfg['kakao_version'],"")?>> (구)카카오페이</label>
					<label class="p_cursor"><input type="radio" name="kakao_version" value="new" <?=checked($cfg['kakao_version'],"new")?>> 카카오페이</label>
				</td>
			</tr>
			<?php if (defined('__STAND_ALONE__') == true || $admin['admin_id'] == 'wisa') { ?>
			<tr class="go_kakao">
				<th scope="row">MID</th>
				<td>
					<input type="text" name="kakao_id" value="<?=$cfg['kakao_id']?>" class="input" type="text">
				</td>
			</tr>
			<tr class="go_kakao">
				<th scope="row">상점키</th>
				<td>
					<input type="text" name="kaka_key" value="<?=$cfg['kaka_key']?>" class="input" type="text" style="width:20%;">
				</td>
			</tr>
			<tr class="new_kakao">
				<th scope="row">CID</th>
				<td>
					<input type="text" name="kakao_cid" value="<?=$cfg['kakao_cid']?>" class="input" type="text">
				</td>
			</tr>
			<tr class="new_kakao">
				<th scope="row">ADMIN KEY</th>
				<td>
					<input type="hidden" name="kaka_admin_key" value="<?=$cfg['kaka_admin_key']?>"> <?=$cfg['kaka_admin_key']?>
					<?php if (isset($cfg['kaka_admin_key']) == false || empty($cfg['kaka_admin_key']) == true) { ?>
					<div class="list_info">
						<p>승인완료 후 발급됩니다.</p>
					</div>
					<?php } ?>
				</td>
			</tr>
			<?php } else { ?>
			<tr class="go_kakao">
				<th scope="row">MID</th>
				<td><?=$cfg['kakao_id']?></td>
			</tr>
			<tr class="go_kakao">
				<th scope="row">상점키</th>
				<td><?=$cfg['kaka_key']?></td>
			</tr>
			<tr class="new_kakao">
				<th scope="row">CID</th>
				<td><?=$cfg['kakao_cid']?></td>
			</tr>
			<tr class="new_kakao">
				<th scope="row">ADMIN KEY</th>
				<td>
					<input type="hidden" name="kaka_admin_key" value="<?=$cfg['kaka_admin_key']?>"> <?=$cfg['kaka_admin_key']?>
				</td>
			</tr>
			<?php } ?>
			<tr class="go_kakao">
				<th scope="row">거래취소 비밀번호</th>
				<td>
					<input type="text" name="kakao_cancel" value="<?=$cfg['kakao_cancel']?>" class="input" type="text">
				</td>
			</tr>
			<tr class="go_kakao">
				<th scope="row">인증요청용 Enckey</th>
				<td>
					<input type="text" name="kakao_enc_key" value="<?=$cfg['kakao_enc_key']?>" class="input" type="text">
				</td>
			</tr>
			<tr class="go_kakao">
				<th scope="row">인증요청용 Hashkey</th>
				<td>
					<input type="text" name="kakao_hash_key" value="<?=$cfg['kakao_hash_key']?>" class="input" type="text">
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_tosspayment" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
	<div class="box_sort left">
	</div>
	<table class="tbl_row cfg_tbl" id="kakao">
		<caption class="hidden">토스계좌결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if (!$cfg['tosspayment_api_key']) { ?>
			<tr>
				<th>가입안내/신청</th>
				<td><span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th>사용 여부</th>
				<td>
					<label><input type="radio" name="use_tosspayment" value="Y" <?=checked($cfg['use_tosspayment'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_tosspayment" value="N" <?=checked($cfg['use_tosspayment'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<?php } ?>
			<?php if (defined('__STAND_ALONE__') || $admin['admin_id'] == 'wisa') { ?>
			<tr>
				<th scope="row">가맹점키</th>
				<td><input type="text" name="tosspayment_api_key" value="<?=$cfg['tosspayment_api_key']?>" class="input"></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th scope="row">가맹점키</th>
				<td><?=$cfg['tosspayment_api_key']?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_tosscard" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easypay">
	<div class="box_sort left">
	</div>
	<table class="tbl_row cfg_tbl" id="kakao">
		<caption class="hidden">토스결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if (!$cfg['tossc_liveApiKey']) { ?>
			<tr>
				<th>가입안내/신청</th>
				<td>
					<span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span>
					<ul class="list_info">
						<li>승인이 완료되면 자동으로 가맹점키가 설정됩니다.</li>
						<?php if ($cfg['use_tosspayment'] == 'Y') { ?>
						<li class="warning">토스결제 신청시 토스머니 뿐만아니라 카드 등도 지원되나 기존 토스계좌결제 설정이 해제됩니다.</li>
						<li class="warning">기존 토스계좌결제로 처리된 주문의 취소등이 불가능합니다.</li>
						<?php } ?>
					</ul>
				</td>
			</tr>
			<?php } else { ?>
			<tr>
				<th scope="row">사용 여부</th>
				<td>
					<label><input type="radio" name="use_tosscard" value="Y" <?=checked($cfg['use_tosscard'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_tosscard" value="N" <?=checked($cfg['use_tosscard'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<?php } ?>
			<?php if (defined('__STAND_ALONE__') || $admin['admin_id'] == 'wisa') { ?>
			<tr>
				<th scope="row">가맹점키</th>
				<td><input type="text" name="tossc_liveApiKey" value="<?=$cfg['tossc_liveApiKey']?>" class="input" size="30"></td>
			</tr>
			<?php } else { ?>
			<tr>
				<th scope="row">가맹점키</th>
				<td><?=$cfg['tossc_liveApiKey']?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="pg_samsungpay" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="display:none;" onsubmit="printLoading()">
    <input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="config_code" value="samsungpay">
    <div class="box_sort left">
    </div>
    <table class="tbl_row cfg_tbl" id="samsung">
        <caption class="hidden">삼성페이 설정</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody>
        <?php if ((!$scfg->get('samsungpay_id') || !$scfg->get('samsungpay_pwd')) && defined('__STAND_ALONE__') == false && $admin['admin_id'] != 'wisa') { ?>
            <tr>
                <th>가입안내/신청</th>
                <td>
                    <span class="box_btn_s gray"><input type="button" value="간편결제 계약안내/신청" onclick="goMywisa('?body=support@cooperate@payment');"></span>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <th scope="row">사용 여부</th>
                <td>
                    <label><input type="radio" name="use_samsungpay" value="Y" <?=checked($scfg->get('use_samsungpay'), 'Y')?>> 사용함</label>
                    <label><input type="radio" name="use_samsungpay" value="N" <?=checked($scfg->get('use_samsungpay'), 'N')?>> 사용안함</label>
                </td>
            </tr>
            <?php if (defined('__STAND_ALONE__') || $admin['admin_id'] == 'wisa') { ?>
                <tr>
                    <th scope="row">CPID</th>
                    <td><input type="text" name="samsungpay_id" value="<?=$scfg->get('samsungpay_id')?>" class="input" size="30"></td>
                </tr>
                <tr>
                    <th scope="row">PWD</th>
                    <td><input type="text" name="samsungpay_pwd" value="<?=$scfg->get('samsungpay_pwd')?>" class="input" size="30"></td>
                </tr>
            <?php } else { ?>
                <tr>
                    <th scope="row">CPID</th>
                    <td><?=$scfg->get('samsungpay_id')?></td>
                </tr>
                <tr>
                    <th scope="row">PWD</th>
                    <td><?=$scfg->get('samsungpay_pwd')?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>

<script type="text/javascript" src="//checkout.naver.com/customer/js/checkoutButton2.js" charset="UTF-8"></script>
<script type="text/javascript">
	function payco_btn(type, o) {
		if(o && o.value) {
			var src = engine_url+'/_manage/image/btn/payco/checkout_'+o.value.toLowerCase()+'.png';
			$('#payco_type'+type).html("<img src='"+src+"'>");
		}
	}

	for(var key = 1; key <= 4; key++) {
		payco_btn(key, $('select[name=payco_type'+key+'_sel]')[0]);
	}

    $('select[name=talkpay_btn_type]').on('change', function() {
        $('#talkpay_detail').prop('src', engine_url+'/_manage/image/openmarket/kakao_checkout/talkpay_'+this.value+'.png');
    });

    $('select[name=talkpay_btn_type_m]').on('change', function() {
        $('#talkpay_detail_m').prop('src', engine_url+'/_manage/image/openmarket/kakao_checkout/talkpay_'+this.value+'.png');
    });

	function npay_btn(area, val, suffix) {
		var area = document.getElementById('checkbtn_'+area);
		area.src = engine_url+'/_manage/image/openmarket/checkout/checkout_'+val+suffix+'.png';
	}

	$('#kakao input[type="radio"][name="kakao_version"]').bind('click', function() {
		kakaoChk();
	});

	function kakaoChk() {
		var pg_version=$('#kakao input[name="kakao_version"]:checked').val();

		if(pg_version == 'new') {
			$('.go_kakao').hide();
			$('.new_kakao').show();
		}else {
			$('.go_kakao').show();
			$('.new_kakao').hide();
		}
	}

	function cardPG(pg) {
		$('#select_pg').find('.active').removeClass('active');
		$('#select_pg .tab_'+pg+'>a').addClass('active');

		$('form[id^=pg_]').hide();
		$('#pg_'+pg).show();
	}

    function setTalkPay(f)
    {
        if (f.use_talkpay.value == 'Y') {
            if (confirm('카카오 페이구매를 처음 설정할 경우 주문과 상품 수에 따라 세팅 시간이 오래 소요될 수 있습니다.\n사이트 운영에 지장이 없는 시간대를 이용하여 설정해 주세요.\n\n세팅 진행 시 브라우저를 닫거나 페이지를 벗어날 경우 일부 설정이 중지될 수 있습니다. 진행하시겠습니까?') == true) {
                printLoading();
                return true;
            }
            return false;
        }
        printLoading();

        return true;
    }

    (setNspButton = function(){
        var val = $('select[name=nsp_button_type]').val();
        $('.nsp_button_sample').hide();
        $('.nsp_button_sample.sample'+val).show();
    })();

	$(document).ready(function() {
		kakaoChk();
        $('select[name=nsp_button_type]').change(setNspButton);
	});

	var url = location.href.split('#');
	if(url[1]) {
		cardPG(url[1]);
	} else {
		cardPG('checkout');
    }
</script>