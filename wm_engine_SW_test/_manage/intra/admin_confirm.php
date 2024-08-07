<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 중요설정 2차인증
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_engine/include/common.lib.php';

	if($admin['level'] > 2) msg('접근 권한이 없습니다.', 'back', 'parent');

	addField($tbl['mng'], 'cfg_receive', 'enum("Y","N")');
	addField($tbl['mng'], 'cfg_confirm', 'enum("Y","N")');
	addField($tbl['mng'], 'cfg_receive_regdate', 'int(10)');
	addField($tbl['mng'], 'cfg_confirm_regdate', 'int(10)');

	if(!isTable($tbl['cfg_confirm_list'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['cfg_confirm_list']);
	}
	$list = array(
		'설정 > 판매설정 > 입점몰 설정 > 입점몰 기능 설정'=> 'partner_shop',
		'설정 > 판매설정 > 오픈마켓연동 설정 > 오픈마켓 연동 설정'=> 'openmarket',
		'설정 > 판매설정 > 오픈마켓연동 설정 > 연동마켓 설정'=> 'openmarket_linkage',
		'설정 > 국내설정 > 국내 결제 설정 > 허용결제 선택'=> 'account',
		'설정 > 국내설정 > 국내 결제 설정 > 결제방식별 추가금액'=> 'account_add',
		'설정 > 국내설정 > 국내 결제 설정 > 무통장 입금 기한'=> 'account_bank_limit',
		'설정 > 국내설정 > 국내 결제 설정 > 미입금주문 자동 SMS 통보'=> 'account_sms_notice',
		'설정 > 국내설정 > 국내 결제 설정 > 현금 결제 할인'=> 'account_cash_sale',
		'설정 > 결제설정 > 해외 결제 설정 > 해외 결제 설정'=> 'card_int',
		'설정 > 결제설정 > 무통장계좌 설정 > 무통장 입금 계좌'=> 'account_bank',
		'설정 > 결제설정 > 간편결제 설정 >네이버페이 설정'=> 'naverpay_config',
		'설정 > 결제설정 > 간편결제 설정 >페이코 설정'=> 'easypay',
		'설정 > 결제설정 > 간편결제 설정 >카카오페이 설정'=> 'kakaopay_config',
		'설정 > 결제설정 > PG연동 설정 > PG연동 설정'=> 'card_pg',
		'설정 > 결제설정 > PG연동 설정 > 신용카드 결제 안내문구'=> 'card_info',
		'설정 > 결제설정 > 에스크로 설정 > 에스크로 서비스 설정'=> 'escrow_pg',
		'설정 > 결제설정 > 적립금 설정 > 적립금 설정'=> 'milage_config',
		'설정 > 결제설정 > 적립금 설정 > 적립금 사용/적립 기준'=> 'milage4',
		'설정 > 결제설정 > 적립금 설정 > 적립금 적립 기준'=> 'milage3',
		'설정 > 결제설정 > 예치금 설정 > 예치금 설정'=> 'emoney_config',
		'설정 > 결제설정 > 현금영수증 설정 > 현금영수증'=> 'cash_receipt',
		'설정 > 주문설정 > 주문 설정 > 주문 설정'=> 'order2',
		'설정 > 주문설정 > 주문 설정 > 주문 관리 설정'=> 'order',
		'설정 > 주문설정 > 주문 설정 > 주문 삭제/보관 기간 설정'=> 'order3',
		'설정 > 주문설정 > 주문 설정 > 주문서 인쇄 설정'=> 'order_print_config',
		'설정 > 주문설정 > 주문 설정 > 주문 상품 수량 및 옵션 변경'=> 'order_product_change',
		'설정 > 주문설정 > 주문 설정 > 계산서/영수증 출력'=> 'order_receipt',
		'설정 > 배송설정 > 국내배송 설정 > 배송정책 설정'=> 'delivery',
		'설정 > 배송설정 > 국내배송 설정 > 지역별 추가배송비 설정'=> 'delivery_addprice',
		'설정 > 배송설정 > 해외배송 설정 > 배송 지역 관리'=> 'oversea_delivery',
		'설정 > 배송설정 > 해외배송비 설정 > 배송비 설정'=> 'oversea_delivery_prc',
		'설정 > 배송설정 > 국가별 관세 설정 > 국가별 세금 설정'=> 'orversea_tax',
		'고객CRM > 회원설정 > 가입/탈퇴/로그인 설정 > 가입 설정'=> 'member_jumin',
		'고객CRM > 회원설정 > 가입/탈퇴/로그인 설정 > 휴먼/탈퇴요청회원 설정'=> 'member_withdraw',
		'고객CRM > 회원설정 > 가입/탈퇴/로그인 설정 > 로그인 설정'=> 'session',
		'고객CRM > 회원설정 > SNS로그인 설정 > 페이코 아이디 로그인 설정'=> 'payco_login',
		'고객CRM > 회원설정 > SNS로그인 설정 > 네이버 아이디 로그인 설정'=> 'naver_login',
		'고객CRM > 회원설정 > SNS로그인 설정 > 페이스북 로그인 설정'=> 'facebook_login',
		'고객CRM > 회원설정 > SNS로그인 설정 > 카카오톡 로그인 설정'=> 'kakao_login',
		'고객CRM > 회원설정 > 회원그룹 설정 > 회원그룹 설정'=> 'member_group',
		'고객CRM > 회원설정 > 수신동의/거부 설정 > 광고성정보 수신동의 알림'=> 'advertising_receive',
		'고객CRM > 회원설정 > 수신동의/거부 설정 > 080수신거부 설정'=> 'session',
		'고객CRM > 회원설정 > I-PIN 설정 > 아이핀 설정'=> 'ipin_config',
        '고객CRM > 회원종합관리 > 회원 조회 > 엑셀 다운로드' => 'member_excel',
        '주문배송 > 주문조회 > 전체주문조회 > 엑셀 다운로드' => 'order_excel',
        '주문배송 > 현금영수증관리 > 현금영수증 관리 > 엑셀 다운로드' => 'cash_excel',
		'광고마케팅 > 연동설정 > 다음 쇼핑하우 연동 > 다음 쇼핑하우'=> 'daum_show_linkage',
		'광고마케팅 > 연동설정 > 다음 쇼핑하우 연동 > 다음 쇼핑하우 엔진파일 생성'=> 'daum_show_engine',
		'광고마케팅 > 연동설정 > 네이버쇼핑 연동 > 네이버쇼핑'=> 'compare',
		'광고마케팅 > 연동설정 > 네이버쇼핑 연동 > 네이버쇼핑 ROI 트래커'=> 'compare_tracker',
		'광고마케팅 > 연동설정 > 네이버 CPA 연동 > 스크립트 설정'=> 'naver_cpa',
		'광고마케팅 > 연동설정 > 크리테오 연동 > 크리테오 연동'=> 'criteo_linkage',
		'광고마케팅 > 연동설정 > 페이스북 연동 > 페이스북'=> 'facebook_pixel',
		'광고마케팅 > 연동설정 > 페이스북 연동 > 페이스북 Feed URL'=> 'facebook_feed_url',
		'광고마케팅 > 연동설정 > 레코픽 연동 > 레코픽 '=> 'recopick_linkage',
		);

	$code_cache = array();
	$res = $pdo->iterator("select * from `wm_cfg_confirm_list`");
    foreach ($res as $fd) {
		$code_cache[]= $fd['code'];
	}

	$res2 = $pdo->iterator("select * from `wm_mng` where `cfg_receive` = 'Y'");
	$res3 = $pdo->iterator("select * from `wm_mng` where `cfg_confirm` = 'Y'");
	$receive_cell = $pdo->row("select count(*) from `wm_mng` where `cfg_receive` = 'Y'");
	$confirm_cell = $pdo->row("select count(*) from `wm_mng` where `cfg_confirm` = 'Y'");

	if(!$cfg['admin_set_confirm']) $cfg['admin_set_confirm'] = 'N';
	$idx = 0;

?>
<form name="con_firmFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>"  target="hidden<?=$now?>">
<input type="hidden" name="body" value="config@config.exe">
<input type="hidden" name="no" value="">
<input type="hidden" name="exec" value="">
<input type="hidden" name="mode" value="1">
	<div class="box_title first">
		<h2 class="title">변경 알림 수신자 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">변경 알림 수신자 설정</caption>
		<colgroup>
			<col style="width:17%">
			<col style="width:83%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" id="admin_set_confirm" name="admin_set_confirm" value="Y" <?=checked($cfg['admin_set_confirm'] ,'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" id="admin_set_confirm" name="admin_set_confirm" value="N" <?=checked($cfg['admin_set_confirm'] ,'N').checked($cfg['admin_set_confirm'],"")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">관리자 검색</th>
				<td>
					<span class="box_btn_s"><input type="button" value="검색" onclick="msearch.open()"></span>
				</td>
		</tr>
		<?if($receive_cell < 1) { ?>
		<tr>
			<th scope="row">수신자 목록</th>
			<td>
				등록된 수신자가 없습니다.
				<ul class="list_msg">
					<li>‘페이지 별 대상항목 설정’에 따른 대상항목 설정 변경 시 등록된 수신자에게 SMS를 통해 해당 내용을 전달합니다.</li>
				</ul>
			</td>
		</tr>
		<?} else {?>
		<tr>
			<th scope="row">수신자 목록</th>
			<td>
				<table class="tbl_inner line" style="width:100%">
					<caption class="hidden">대상항목 목록</caption>
					<colgroup>
						<col style="width:20%;">
						<col style="width:20%;">
						<col style="width:20%;">
						<col style="width:20%;">
						<col style="width:20%;">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">성명</th>
							<th scope="col">아이디</th>
							<th scope="col">휴대폰</th>
							<th scope="col">등록일</th>
							<th scope="col">삭제</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($res2 as $data) {?>
						<tr>
							<td style="text-align:center !important"><?=$data['name']?></td>
							<td style="text-align:center !important"><?=$data['admin_id']?></td>
							<td style="text-align:center !important"><?=$data['cell']?></td>
							<td style="text-align:center !important"><?=date('Y-m-d' , $data['cfg_receive_regdate'])?></td>
							<td style="text-align:center !important"><span class="box_btn_s"><input type="button" value="삭제" onclick="confirm_cell_del('<?=$data[no]?>', 'cfg_receive');"></span></td>
						</tr>
						<?}?>
					</tbody>
				</table>
				<ul class="list_msg">
					<li>‘페이지 별 대상항목 설정’에 따른 대상항목 설정 변경 시 등록된 수신자에게 SMS를 통해 해당 내용을 전달합니다.</li>
				</ul>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form name="confirmFrm"  method="post" onSubmit="return checkcell(this)" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="intra@admin_confirm.exe">
<input type="hidden" name="no" value="">
<input type="hidden" name="exec" value="">
<input type="hidden" name="name" value="">
<input type="hidden" name="mode" value="2">
	<div class="box_title">
		<h2 class="title">변경 권한자 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">변경 권한자 설정</caption>
		<colgroup>
			<col style="width:17%">
			<col style="width:83%">
		</colgroup>
		<tr>
			<th scope="row">관리자 검색</th>
				<td>
					<span class="box_btn_s"><input type="button" value="검색" onclick="msearch2.open()"></span>
				</td>
		</tr>
		<?if($confirm_cell < 1) { ?>
		<tr>
			<th scope="row">권한자 목록</th>
			<td>
				등록된 권한자가 없습니다.
				<ul class="list_msg">
					<li>'페이지 별 대상항목 설정'에 따른 대상항목 설정 변경 시 등록된 권한자에 한해 휴대폰 인증 후 변경이 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<?}else {?>
		<tr>
			<th scope="row">권한자 목록</th>
			<td>
				<table class="tbl_inner line" style="width:100%">
					<caption class="hidden">권한자 목록</caption>
					<colgroup>
						<col style="width:20%">
						<col style="width:20%">
						<col style="width:20%">
						<col style="width:20%">
						<col style="width:20%">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">성명</th>
							<th scope="col">아이디</th>
							<th scope="col">휴대폰</th>
							<th scope="col">등록일</th>
							<th scope="col">삭제</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($res3 as $data) {?>
						<tr>
							<td style="text-align:center !important"><?=$data['name']?></td>
							<td style="text-align:center !important"><?=$data['admin_id']?></td>
							<td style="text-align:center !important"><?=$data['cell']?></td>
							<td style="text-align:center !important"><?=date('Y-m-d' , $data['cfg_confirm_regdate'])?></td>
							<td style="text-align:center !important"><span class="box_btn_s"><input type="button" value="삭제" name = "del" onclick="confirm_cell_del('<?=$data[no]?>', 'cfg_confirm');"></span></td>
						</tr>
						<?}?>
					</tbody>
				</table>
				<ul class="list_msg">
					<li>'페이지 별 대상항목 설정'에 따른 대상항목 설정 변경 시 등록된 권한자에 한해 휴대폰 인증 후 변경이 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<?}?>
	</table>
</form>

<div class="box_title">
	<h2 class="title">페이지 별 대상항목 설정</h2>
</div>
<form name="ipFrm" method="post" target="hidden<?=$now?>" class="contentFrm">
	<input type="hidden" name="body" value="intra@admin_confirm.exe">
	<input type="hidden" name="exec" value="use">
	<table class="tbl_col">
		<caption class="hidden">대상항목 목록</caption>
		<colgroup>
			<col style="width:8%">
			<col style="width:23%">
			<col style="width:23%">
			<col style="width:23%">
			<col style="width:23%">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.ipFrm.check_pno, this.checked)"></th>
				<th scope="col">대분류</th>
				<th scope="col">중분류</th>
				<th scope="col">소분류</th>
				<th scope="col">대상항목</th>
			</tr>
		</thead>
		<tbody>
			<?foreach($list as $key => $val) { $idx++;?>
				<?$mname = explode('>', stripslashes($key));?>
				<?$tr_class = (in_array($val, $code_cache) == true) ? "noanswer" : "";?>
				<tr class="<?=$tr_class?>">
					<td>
						<input type="checkbox" name="check_pno[<?=$idx?>]" id="check_pno" value="Y"<?=checked($tr_class, 'noanswer')?>></td>
						<input type="hidden" name="name[<?=$idx?>]" value="<?=$key?>">
						<input type="hidden" name="code[<?=$idx?>]" value="<?=$val?>">
					<td><?=$mname[0]?></td>
					<td><?=$mname[1]?></td>
					<td><?=$mname[2]?></td>
					<td><?=$mname[3]?></td>
				</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue" style="margin-top:10px;"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function confirm_cell_del(no, name) {
		if(!confirm("해당 번호를 삭제하시겠습니까?")) return;
		f=document.confirmFrm;
		f.no.value=no;
		f.name.value=name;
		f.exec.value='del';
		f.submit();
	}

	layerWindow.prototype.msel = function(json) {
		$(':input[name=admin_id]').val(json.admin_id);
		this.close();
	}

	var msearch = new layerWindow('intra@admin_inc2.exe');
	var msearch2 = new layerWindow('intra@admin_inc3.exe');

</script>