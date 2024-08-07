<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 발급
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_GET['no']);
	$is_type = addslashes($_GET['is_type']);
	$cpn_no = numberOnly($_GET['cpn_no']);

	if($no) {
		$data = get_info($tbl['coupon'], "no", $no);
	} else if ($cpn_no) {
		$data = get_info($tbl['coupon'], "no", $cpn_no);
		$data['no'] = '';
		$data['updir'] = '';
		$data['upfile1'] = '';
	}
	else {
		$data['rdate_type'] = $data['udate_type'] = 1;
	}

	if(!$data['release_limit']) $data['release_limit'] = 1;
	if(!$data['download_limit']) $data['download_limit'] = 2;
	if(!$data['stype']) $data['stype'] = "1";
	if(!$data['down_type']) $data['down_type'] = "A";
	if(!$data['down_gradeonly']) $data['down_gradeonly'] = "Y";
	if(!$data['pay_type']) $data['pay_type'] = 1;
	if(!$data['buy_prcs']) $data['buy_prcs'] = 0;
	if(!$data['buy_prce']) $data['buy_prce'] = 0;
	if(!$data['partner_type']) $data['partner_type'] = 0;
	$group = getGroupName();

	${'attach_items_'.$data['attachtype']} = $data['attach_items'];

	$ww=($pop) ? "600px" : "100%";
	$_weeks = array(1 => '월', 2 => '화', 3 => '수', 4 => '목', 5 => '금', 6 => '토', 0 => '일');
	$weeks = explode('@', $data['weeks']);

	if($cfg['use_partner_shop'] == 'Y') {
		$_partners = array();
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat=2 order by corporate_name asc");
        foreach ($pres as $ptn) {
			$_partners[$ptn['no']] = stripslashes($ptn['corporate_name']);
		}
	}

	$is_type_title = ($is_type == 'A') ? '온라인' : '시리얼';
	$tmp_is_app = 'N';

	// 리뉴얼 쿠폰코드 매칭
	switch($data['down_type']) {
		case 'A' :
			$tmp_down_type = 1;
			$tmp_sub_type1 = 'A';
		break;
		case 'B' :
			$tmp_down_type = 1;
			$tmp_sub_type1 = 'B';
		break;
		case 'C' :
			$tmp_down_type = 2;
			$tmp_sub_type2 = 'C';
		break;
		case 'D' :
			$tmp_down_type = 3;
		break;
		case 'E' :
			$tmp_down_type = 2;
			$tmp_sub_type2 = 'C';
			$tmp_is_app = 'Y';
		break;
		case 'F' :
			$tmp_down_type = 2;
			$tmp_sub_type2 = 'F';
		break;
		case 'G' :
			$tmp_down_type = 2;
			$tmp_sub_type2 = 'G';
		break;
		case 'L' : // 로그인 쿠폰
		case 'L2' : // 로그인 쿠폰(앱전용)
			$tmp_down_type = 2;
			$tmp_sub_type2 = 'L';
			if($data['down_type'] == 'L2') $tmp_is_app = 'Y';
		break;
	}
	if($tmp_down_type == 1 && $data['is_birth'] == 'Y') $tmp_down_type = 4; // 생일쿠폰 예외처리
	if(!$tmp_sub_type1) $tmp_sub_type1 = 'A'; // 기본값
	if(!$tmp_sub_type2) $tmp_sub_type2 = 'C'; // 기본값

	$download_cnt = $pdo->row("select count(*) from `".$tbl[coupon_download]."` where `cno` = '$no'");
	if($download_cnt > 0) {
		$download_cnt = "Y";
		$disabled = "disabled";
	} else {
	   $download_cnt = '';
	}

	// 시리얼쿠폰 쿠폰 코드형식 컨트롤
	if($data['is_type'] == 'B') {
		if($data['down_type'] == 'B') {
			$serial_code_type = 'manual';
			$disabled_down_type_A = 'disabled';
		} else {
			$disabled_down_type_B = 'disabled';
		}
	}

	$cpn_option = explode('@', trim($data['cpn_option'], '@'));

?>
<form name="couponFrm" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkFrm()" enctype="multipart/form-data">
	<input type="hidden" name="body" value="promotion@coupon.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="is_type" value="<?=$is_type?>">
	<input type="hidden" name="cpn_no" value="<?=$cpn_no?>">
	<input type="hidden" name="download_cnt" value="<?=$download_cnt?>">
	<div class="box_title first">
		<h2 class="title">
			<?=$is_type_title?>쿠폰 생성
			<?if($is_type == "B") echo "<span calss=\"desc1\">(시리얼쿠폰은 회원가입 후 사용이 가능합니다)</span>";?>
		</h2>
	</div>
	<table class="tbl_row coupon">
		<caption class="hidden">
			<?=$is_type_title?>쿠폰 생성
			<?if($is_type == "B") echo "<span calss=\"desc1\">(시리얼쿠폰은 회원가입 후 사용이 가능합니다)</span>";?>
		</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:25%">
		</colgroup>
		<tr>
			<th scope="row"><strong>쿠폰명</strong></th>
			<td colspan="2"><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full"></td>
		</tr>
		<?if($is_type != 'B') {?>
		<tr>
			<th scope="row">쿠폰 이미지</th>
			<td colspan="2">
				<input type="file" name="upfile1" class="input input_full">
				<?if($data['upfile1']){?>
				<span class="box_btn_s"><a href="<?="$root_url/$data[updir]/$data[upfile1]"?>" target="_blank">기존 이미지</a></span>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">쿠폰 발급방식</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="tmp_down_type" value="1" <?=checked($tmp_down_type, 1)?>> 회원 다운로드</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_down_type" value="2" <?=checked($tmp_down_type, 2)?>> 자동발급</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_down_type" value="3" <?=checked($tmp_down_type, 3)?>> 수동발급</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_down_type" value="4" <?=checked($tmp_down_type, 4)?>> 생일쿠폰</label>
				<span class="set_birth"><a href="/_manage/?body=promotion@coupon_birth" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_set.png" alt=""></a></span>
			</td>
		</tr>
		<tr class="issue issue1">
			<th scope="row">쿠폰 발급형태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type1" value="A" <?=checked($tmp_sub_type1, 'A')?>> 전체</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type1" value="B" <?=checked($tmp_sub_type1, 'B')?>> 회원등급별</label>
			</td>
			<td class="addcell">
				<dl class="issue_dl issue_dl1 summary">
					<dt class="title">전체 회원 다운로드</dt>
					<dd>회원등급에 상관없이 모든 회원이 다운로드 가능한 쿠폰입니다.</dd>
				</dl>
				<div class="issue_dl issue_dl2">
					<p class="title">회원등급별 회원 다운로드</p>
					<?=selectArray($group, "down_grade", 2, "선택", $data['down_grade'])?>
					<label class="p_cursor"><input type="radio" name="down_gradeonly" value="Y" <?=checked($data['down_gradeonly'], "Y")?>>등급만</label>
					<label class="p_cursor"><input type="radio" name="down_gradeonly" value="N" <?=checked($data['down_gradeonly'], "N")?>>등급이상</label>
				</div>
			</td>
		</tr>
		<tr class="issue issue2">
			<th scope="row">쿠폰 발급형태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type2" value="C" <?=checked($tmp_sub_type2, 'C')?>> 회원가입 시 자동발급</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type2" value="L" <?=checked($tmp_sub_type2, 'L')?>> 로그인 시 자동발급</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type2" value="G" <?=checked($tmp_sub_type2, 'G')?>> 첫구매 완료 시 자동발급</label><br>
				<label class="p_cursor"><input type="radio" name="tmp_sub_type2" value="F" <?=checked($tmp_sub_type2, 'F')?>> 구매 완료 시 자동발급</label>
			</td>
			<td class="addcell">
				<dl class="issue_auto issue_auto1">
					<dt class="title">회원가입 시 자동발급</dt>
					<dd><label class="p_cursor app"><input type="checkbox" name="tmp_is_app" value="Y" <?=checked($tmp_is_app, 'Y')?>>매직앱 전용</label><br></dd>
					<dd class="msg">
						<div class="list_info">
							<p>매직앱 전용 체크 시 매직앱을 통한 회원가입 시에만 쿠폰이 자동발급됩니다.</p>
						</div>
					</dd>
				</dl>
				<dl class="issue_auto issue_auto3">
					<dt class="title">로그인 시 자동발급</dt>
					<dd>
						<?=selectArray($group, "down_grade2", 2, "전체 등급", $data['down_grade'])?>
						<select name="down_gradeonly2">
							<option value="Y" <?=checked($data['down_gradeonly'], 'Y', true)?>>등급만</option>
							<option value="N" <?=checked($data['down_gradeonly'], 'N', true)?>>등급이상</option>
						</select>
					</dd>
					<dd>&nbsp;</dd>
					<dd>
						<label class="p_cursor app"><input type="checkbox" name="tmp_is_app" value="Y" <?=checked($tmp_is_app, 'Y')?>>매직앱 전용</label>
						<label class="p_cursor app"><input type="checkbox" name="cpn_option[]" class="cpn_option_join" value="join" <?=checked(in_array('join', $cpn_option), true)?>>회원가입 시에도 적용</label>
					</dd>
					<dd class="msg">
						<ul class="list_info pt">
							<li>매직앱 전용 체크 시 매직앱을 통한 로그인 시에만 쿠폰이 자동발급됩니다.</li>
							<li>발급대상 내 <?=$group[9]?>이 포함되어 있을 경우, &nbsp;'회원가입 시에도 적용' 옵션을 통해 회원가입 시 쿠폰 발급여부를 설정할 수 있습니다.</li>
						</ul>
					</dd>
					<dd>&nbsp;</dd>
					<dd>
						<input type="text" name="down_msg" class="input input_full" value="<?=inputText($data['down_msg'])?>" placeholder="로그인 시 쿠폰 발급메시지를 입력해주세요.">
					</dd>
				</dl>
				<div class="issue_auto issue_auto2">
					<p class="title">구매 완료 시 자동발급</p>
					<div>
						실 결제금액 기준
						<input type="text" name="buy_prcs" class="input right" size="10" value="<?=$data['buy_prcs']?>"> <?=$cfg['currency_type']?> 이상 ~
						<input type="text" name="buy_prce" class="input right" size="10" value="<?=$data['buy_prce']?>"> <?=$cfg['currency_type']?> 미만
						<span class="p_color">(배송완료시 지급)</span>
						<ul class="list_info" style="padding-top:10px;">
							<li>'구매 완료 시 자동발급 쿠폰'은 '첫구매 완료 시 자동발급 쿠폰' 지급 시 같이 지급되지 않습니다.</li>
							<li>배송완료에 따른 발급된 쿠폰은 주문서 상태변경 시 회수되지 않으며, 배송완료로 다시 변경 시 중복 지급되지 않습니다.</li>
						</ul>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">혜택 구분</th>
			<td colspan="2">
				<ul>
					<li><label class="p_cursor"><input type="radio" name="stype" id="stype" value="1" <?=checked($data['stype'], 1)?> onclick="ctrlUseLimit()"> 전체상품 할인</label><br></li>
					<li><label class="p_cursor"><input type="radio" name="stype" id="stype" value="5" <?=checked($data['stype'], 5)?> onclick="ctrlUseLimit()"> 개별상품 할인</label></li>
					<li><label class="p_cursor"><input type="radio" name="stype" id="stype" value="3" <?=checked($data['stype'], 3)?> onclick="ctrlUseLimit()"> 무료배송</label></li>
					<?if($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') {?>
					<li><label class="p_cursor"><input type="radio" name="stype" id="stype" value="4" <?=checked($data['stype'], 4)?> onclick="ctrlUseLimit()"> 해외 무료배송</label>(지정 할인대상의 무게를 차감해 배송비 계산)</li>
					<?}?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">쿠폰 적용범위</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="device" value="" <?=checked($data['device'], '')?>> 전체</label><br>
				<label class="p_cursor"><input type="radio" name="device" value="pc" <?=checked($data['device'], 'pc')?>> PC</label><br>
				<label class="p_cursor"><input type="radio" name="device" value="mobile" <?=checked($data['device'], 'mobile')?>> 모바일</label><br>
				<label class="p_cursor"><input type="radio" name="device" value="app" <?=checked($data['device'], 'app')?>> 매직앱</label><br>
				<label class="p_cursor"><input type="radio" name="device" value="mobile_all" <?=checked($data['device'], 'mobile_all')?>> 모바일 + 매직앱</label>
			</td>
		</tr>
		<?if($cfg['use_erp_interface'] == 'Y' && $cfg['erp_interface_name'] = 'dooson') {?>
		<tr>
			<th scope="row">사용 매장</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="place" value="" <?=checked($data['place'], '')?>> 온라인/오프라인</label><br>
				<label class="p_cursor"><input type="radio" name="place" value="online" <?=checked($data['place'], 'online')?>> 온라인전용</label><br>
				<label class="p_cursor"><input type="radio" name="place" value="offline" <?=checked($data['place'], 'offline')?>> 오프라인매장 전용</label>
			</td>
		</tr>
		<?}?>
		<?if($cfg['use_partner_shop'] == 'Y') { // 입점몰 관련기능?>
		<tr>
			<th scope="row">적용 입점사</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="partner_type" value="0" <?=checked($data['partner_type'], '0')?>> 모든 상품 할인</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="1" <?=checked($data['partner_type'], '1')?>> 본사 상품만 할인</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="2" <?=checked($data['partner_type'], '2')?>> 지정 입점사 상품만 할인</label><br>
				<label class="p_cursor"><input type="radio" name="partner_type" value="3" <?=checked($data['partner_type'], '3')?>> 본사 및 지정 입점사 상품 할인</label>
			</td>
		</tr>
		<tr class="partner_area">
			<th scope="row">입점사 선택</th>
			<td colspan="2">
				<?=selectArray($_partners, 'partner_no', null, ':: 선택 ::', $data['partner_no'])?>
				<div style="margin: 5px 0;">
					<input type="text" name="partner_fee" class="input right" size="10" value="<?=$data['partner_fee']?>"> <span class="sale_type"></span> 쿠폰 프로모션비 부담
				</div>
			</td>
		</tr>
		<?} // 입점몰 관련 기능?>
		<?} else {?>
		<tr>
			<th scope="row">쿠폰 코드형식</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="down_type" value="A" <?=checked($data['down_type'], "A")?> <?=$disabled_down_type_A?> onclick="setCpnLimit()">
				개별코드제공 <span class="explain">(쿠폰별로 고유코드 생성. 하나의 코드는 한 번만 사용가능)</span></label><br>
				<label class="p_cursor"><input type="radio" name="down_type" value="B" <?=checked($data['down_type'], "B")?> <?=$disabled_down_type_B?> onclick="setCpnLimit()">
				단일코드제공 <span class="explain">(하나의 코드만 생성. 같은 코드로 여러장 발행할 수 있으나 아이디마다 한 번만 사용가능)</span></label>
				<div id="man_cpnno" style="display:none; margin-left:20px;">
					<label><input type="radio" name="serial_code_type" value="auto" checked <?if($data['no']){?>disabled<?}?>> 코드 자동 생성</label>
					<label><input type="radio" name="serial_code_type" value="manual" <?=checked($serial_code_type, 'manual')?>> 코드 수동 지정</label>
					<input type="text" name="serial_code" class="input" size="15" disabled value="<?=trim($data['auth_code'], '@')?>">
				</div>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">결제방식</th>
			<td colspan="2">
				<?if($cfg['order_cpn_paytype'] == 2) {?>
				<div class="list_info">
					<p>
						주문 설정의 쿠폰사용설정이 <span class="warning">'무통장입금 결제 시에만 사용가능'</span>으로 설정되어있습니다.<br>
						쿠폰사용설정을 모든 결제방식으로 변경하고 사용하시기 바랍니다. <a href="?body=config@order" target="_blank">바로가기</a>
					</p>
				</div>
				<?}?>
				<label class="p_cursor"><input type="radio" name="pay_type" value="1" <?=checked($data['pay_type'], 1)?>> 모든 결제</label><br>
				<label class="p_cursor"><input type="radio" name="pay_type" value="2" <?=checked($data['pay_type'], 2)?>> 현금 전용</label>
			</td>
		</tr>
		<tr class="salePrc">
			<th scope="row" rowspan="2"><strong>할인금액(율)</strong></th>
			<td colspan="2">
				<input type="text" name="sale_prc" value="<?=$data['sale_prc']?>" class="input right" size="10">
				<select name="sale_type" onchange="chgSaleType()">
					<option value="m" <?=checked($data['sale_type'], "m", 1)?>><?=$cfg['currency_type']?></option>
					<option value="p" <?=checked($data['sale_type'], "p", 1)?>>%</option>
				</select>
				<span class="explain">(소수점 불가)</span>
				<?if(!$download_cnt) upNum("this.form.sale_prc")?>
				<ul class="list_info">
					<li><?=$cfg['currency_type']?> : 지정된 금액만큼 할인됩니다</li>
					<li>% : 지정된 할인율만큼 할인됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr class="salePrc">
			<td colspan="2">
				<ul>
					<li><label><input type="radio" name="sale_prc_over" value="" <?=checked($data['sale_prc_over'], '')?>><span id="text_change1"> 총 주문금액보다 쿠폰 할인금액이 클 경우 사용 불가</span></label></li>
					<li><label><input type="radio" name="sale_prc_over" value="Y" <?=checked($data['sale_prc_over'], 'Y')?>><span id="text_change2"> 총 주문금액이 쿠폰 할인금액보다 작을 경우에도 사용 가능 (차액은 소멸됩니다.)</span></label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>사용제한 결제금액</strong></th>
			<td colspan="2">
				<input type="text" name="prc_limit" value="<?=$data['prc_limit']?>" class="input right" size="10"> <?=$cfg['currency_type']?> 이상 결제 시 사용 가능
				<?if(!$download_cnt) upNum("this.form.prc_limit")?>
			</td>
		</tr>
		<tr id="saleLimit">
			<th scope="row"><strong>최대 할인금액</strong></th>
			<td colspan="2">
				최대 <input type="text" name="sale_limit" value="<?=$data['sale_limit']?>" class="input right" size="10"> <?=$cfg['currency_type']?> 까지 할인
				<?if(!$download_cnt) upNum("this.form.sale_limit")?>
			</td>
		</tr>
		<tr>
			<th scope="row">발급<? if($is_type == "B") echo "수량"; else echo "제한"; ?></th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="release_limit" value="1" <?=checked($data['release_limit'], 1)?>> 무한</label><br>
				<label class="p_cursor"><input type="radio" name="release_limit" value="2" <?=checked($data['release_limit'], 2)?>> 한정</label> : 선착순
				<input type="text" name="release_limit_ea" value="<?=$data['release_limit_ea']?>" class="input" size="8" <? if($is_type == "B" && $data['no']) echo " readonly"; ?>> 장<br>
				<?if($is_type == "B"){?>
					<div id="csv_cpnno">
						<input type="file" name="csv_cpnno" class="input">
						<ul class="list_info">
							<li>CSV 파일 업로드 시 코드는 숫자, 영문만 등록할 수 있습니다.</li>
							<li>기존 생성되어 있는 모든 쿠폰코드와 중복된 쿠폰코드는 등록할 수 없습니다.</li>
							<li>복사하기 시 업로드한 CSV 파일은 복사되지 않습니다.</li>
						</ul>
					</div>
					<?if($data['no']) {?>
					<br><span class="box_btn_s"><a href="javascript:;" class="help" onclick="resetNum();">수량변경</a></span>
					<div class="list_info">
						<p>수량을 변경하실 경우 인증코드가 다시 생성됩니다.</p>
					</div>
					<?}?>
				<?}?>
			</td>
		</tr>
		<?if($is_type <> "B"){ ?>
		<tr>
			<th scope="row">다운로드 제한</th>
			<td colspan="2">
				<p>회원 1인에 대한 쿠폰 다운로드 제한</p>
				<label class="p_cursor"><input type="radio" name="download_limit" value="1" <?=checked($data['download_limit'], 1)?>> 무한</label><br>
				<label class="p_cursor"><input type="radio" name="download_limit" value="2" <?=checked($data['download_limit'], 2)?>> 사용(만료) 후 다시 다운로드 가능</label><br>
				<label class="p_cursor"><input type="radio" name="download_limit" value="3" <?=checked($data['download_limit'], 3)?>> 한정</label> : <input type="text" name="download_limit_ea" value="<?=$data['download_limit_ea']?>" class="input" size="10"> 장
			</td>
		</tr>
		<tr>
			<th scope="row">중복할인</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="use_limit" value=""  <?=checked($data['use_limit'], "")?>> 제한 없음</label><br>
				<label class="p_cursor"><input type="radio" name="use_limit" value="1" <?=checked($data['use_limit'], "1")?>> 회원 할인/이벤트 할인된 상품은 쿠폰 사용 불가</label><br>
				<label class="p_cursor"><input type="radio" name="use_limit" value="2" <?=checked($data['use_limit'], "2")?>> 회원 할인/이벤트 할인된 상품이 하나라도 있으면 쿠폰 사용 불가</label><br>
				<label class="p_cursor"><input type="radio" name="use_limit" value="3" <?=checked($data['use_limit'], "3")?>> 회원 할인/이벤트 할인을 취소하고 쿠폰 할인만 적용</label><br>
				<label class="p_cursor"><input type="radio" name="use_limit" value="4" <?=checked($data['use_limit'], "4")?>> 다른 개별상품 할인쿠폰과 중복사용 불가</label>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">할인대상</th>
			<td colspan="2">
				<ul class="setTarget">
					<li>
						<label class="p_cursor"><input type="radio" name="attachtype" value="0" checked onclick="settarget(0)" <?=checked($data['attachtype'], 0)?>> 전체 적용</label>
					</li>
					<li>
						<label class="p_cursor"><input type="radio" name="attachtype" value="1" onclick="settarget(1)" <?=checked($data['attachtype'], 1)?>> 지정 카테고리 적용</label>
						<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(1)"></span>
					</li>
					<li>
						<label class="p_cursor"><input type="radio" name="attachtype" value="2" onclick="settarget(2)" <?=checked($data['attachtype'], 2)?>> 지정 상품 적용</label>
						<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(2)"></span>
					</li>
					<li>
						<label class="p_cursor"><input type="radio" name="attachtype" value="3" onclick="settarget(3)" <?=checked($data['attachtype'], 3)?>> 지정 카테고리 제외</label>
						<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(3)"></span>
					</li>
					<li>
						<label class="p_cursor"><input type="radio" name="attachtype" value="4" onclick="settarget(4)" <?=checked($data['attachtype'], 4)?>> 지정 상품 제외</label>
						<span class="box_btn_s"><input type="button" value="적용대상 확인/선택" onclick="selectTarget(4)"></span>
					</li>
				</ul>
				<input type="hidden" name="attach_items_1" value="<?=$attach_items_1?>">
				<input type="hidden" name="attach_items_2" value="<?=$attach_items_2?>">
				<input type="hidden" name="attach_items_3" value="<?=$attach_items_3?>">
				<input type="hidden" name="attach_items_4" value="<?=$attach_items_4?>">
			</td>
		</tr>
		<tr>
			<th scope="row">발급기간</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="rdate_type" id="rdate_type" value="1" <?=checked($data['rdate_type'], 1)?>> 무제한</label><br>
				<label class="p_cursor"><input type="radio" name="rdate_type" id="rdate_type" value="2" <?=checked($data['rdate_type'], 2)?>> 기간 설정</label>
				<input type="text" name="rstart_date" value="<?=$data['rstart_date']?>" size="10" readonly class="input datepicker">
				~
				<input type="text" name="rfinish_date" value="<?=$data['rfinish_date']?>" size="10" readonly class="input datepicker">
			</td>
		</tr>
		<tr>
			<th rowspan="2">사용기간</th>
			<td colspan="2">
				<?if($is_type == "A") { ?>
				<select name="udate_type" onchange="udateChg(this.value)" style="float:left">
					<option value="1" <?=checked($data['udate_type'], 1, 1)?>>무제한</option>
					<option value="2" <?=checked($data['udate_type'], 2, 1)?>>기간설정</option>
					<option value="3" <?=checked($data['udate_type'], 3, 1)?>>발급일로부터</option>
				</select>
				<div id="udate_type2" class="udate" style="float:left; margin-left:5px; <? if($data['udate_type'] != 2) { ?>display:none<? } ?>">
					<input type="text" name="ustart_date" value="<?=$data['ustart_date']?>" size="10" readonly class="input datepicker"> ~ <input type="text" name="ufinish_date" value="<?=$data['ufinish_date']?>" size="10" readonly class="input datepicker">
				</div>
				<div id="udate_type3" class="udate" style="float:left; margin-left:5px; <? if($data['udate_type'] != 3) { ?>display:none<? } ?>">
					<input type="text" name="udate_limit" id="udate_limit" value="<?=$data['udate_limit']?>" class="input" size="10"> 일 까지
				</div>
				<?} else {?>
				<label class="p_cursor"><input type="radio" name="udate_type" id="udate_type" value="1" <?=checked($data['udate_type'], 1)?>> 무제한</label>
				<label class="p_cursor"><input type="radio" name="udate_type"  id="udate_type" value="2" <?=checked($data['udate_type'], 2)?>> 기간 설정</label>
				<input type="text" name="ustart_date" value="<?=$data['ustart_date']?>" size="10" readonly class="input datepicker">
				~
				<input type="text" name="ufinish_date" value="<?=$data['ufinish_date']?>" size="10" readonly class="input datepicker">
				<?}?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?foreach($_weeks as $key => $nm) {?>
				<label class="p_cursor"><input type='checkbox' name='weeks[]' <?=$disabled?> value='<?=$key?>' <?=checked(in_array("$key", $weeks), true)?>> <?=$nm?></label>
				<?}?>
				<div class="list_info">
					<p>선택 시 해당 요일에만 쿠폰을 사용할 수 있습니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">쿠폰설명</th>
			<td colspan="2"><textarea name="explain" class="txta"><?=inputText($data['explain'])?></textarea></td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<?if($is_type =="A" && ($data['no'] || $cpn_no)){?>
			<li>복사하기 시 쿠폰 이미지는 복사되지 않습니다.</li>
			<?}?>
			<?if($is_type == "B"){?>
			<li>복사하기 시 기존과 다른 쿠폰 코드로 생성됩니다.</li>
			<?}?>
			<li>발급된 쿠폰은 발급기간만 수정할 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="goM('promotion@coupon&is_type=<?=$is_type?>')"></span>
		<?if($data['no']){?>
		<span class="box_btn gray"><input type="button" value="복사" onclick="couponCopy()"></span>
		<?}?>
	</div>
</form>

<script type="text/javascript">
	var f=document.couponFrm;
	var is_type = '<?=$is_type?>';
	var no = '<?=$no?>';
	var download_cnt = '<?=$download_cnt?>';

	function udateChg(v) {
		$('.udate').hide();
		if(v != 1) $('#udate_type'+v).show();
	}

	function checkFrm() {
		var tmp_down_type = $(':checked[name=tmp_down_type]', f).val();
		var cpn_tmp_level = $(':checked[name=cpn_tmp_level]', f).val();
		var stype = $(':checked[name=stype]', f).val();
		var sale_type = $('select[name=stype]').val();
		var udate_type = $('select[name=udate_type]').val();

		if(!checkBlank(f.name,'쿠폰명을 입력해주세요.')) return false;
		if(is_type != 'B') {
			if(tmp_down_type  == '1' && cpn_tmp_level == '2') {
				window.alert('발급 회원 등급을 선택하세요');
				return false;
			}

			if(stype == 3 || stype == 4) {
				f.sale_prc.value = 0;
				f.sale_limit.value = 0;
			} else {
				if(!checkBlank(f.sale_prc, '할인금액(율)을 입력해주세요.')) return false;
				if(!checkBlank(f.prc_limit, '사용제한 결제금액을 입력해주세요.')) return false;
				if(sale_type == 'p' && !checkBlank(f.sale_limit, '최대 할인 금액을 입력해주세요.')) return false;
			}
		} else {
			if(!checkBlank(f.sale_prc, '할인금액(율)을 입력해주세요.')) return false;
			if(!checkBlank(f.prc_limit, '사용제한 결제금액을 입력해주세요.')) return false;
			if(sale_type == 'p' && !checkBlank(f.sale_limit, '최대 할인 금액을 입력해주세요.')) return false;
		}

		if (f.rdate_type[1].checked == true) 		{
			if(!checkBlank(f.rstart_date,'발급 시작일을 입력해주세요.')) return false;
			if(!checkBlank(f.rfinish_date,'발급 종료일을 입력해주세요.')) return false;
		}

		if(udate_type == '2') {
			if(!checkBlank(f.ustart_date, '발매 시작일을 입력해주세요.')) return false;
			if(!checkBlank(f.ufinish_date, '발매 종료일을 입력해주세요.')) return false;
		} else if(udate_type == '3') {
			if(!checkBlank(f.udate_limit, '발급일로부터 사용할 기간을 입력해주세요.')) return false;
		}
	}

	function resetNum() { // 쿠폰인증코드 다시 생성
		if(download_cnt) return;
		if(!confirm('수량을 수정하시면 기존의 인증코드는 삭제된 후 다시 생성됩니다.\n계속 하시겠습니까?\n(계속하실 경우 수량을 원하시는 대로 변경이 가능합니다)')) return;
		f.release_limit_ea.readOnly = false;
		f.release_limit_ea.select();
	}

	setPoptitle('쿠폰 생성/수정');

	// 혜택 구분 변경시 실행
	function ctrlUseLimit() {
		var stype = $(':checked[name=stype]').val();
		var o_use_limit = $(':radio[name=use_limit]');

        if (stype == '3') {
            $('[name=partner_fee]').val('0').prop('disabled', true);
        } else {
            $('[name=partner_fee]').prop('disabled', false);
        }

		$("#text_change1").text("총 주문금액보다 쿠폰 할인금액이 클 경우 사용 불가");
		$("#text_change2").text("총 주문금액이 쿠폰 할인금액보다 작을 경우에도 사용 가능 (차액은 소멸됩니다.)");
		if(stype == 1) {
			o_use_limit.attr('disabled', false);
			o_use_limit.filter('[value=4]').attr('disabled', true);
		} else if(stype == 5) {
			$("#text_change1").text("상품금액보다 쿠폰 할인금액이 클 경우 사용 불가");
			$("#text_change2").text("상품금액이 쿠폰 할인금액보다 작을 경우에도 사용 가능 (차액은 소멸됩니다.)");
			o_use_limit.attr('disabled', true);
			o_use_limit.filter('[value=""], [value=1], [value=4]').attr('disabled', false);
		} else {
			o_use_limit.attr('disabled', true);
		}

		$('.salePrc').show();
		$(':radio[name=sale_prc_over][value="Y"]').prop('disabled', false);
		if(stype == 3 || stype == 4) {
			$('.salePrc').hide();
			$('#saleLimit').hide();
			$(':radio[name=sale_prc_over][value=""]').prop('checked', true);
		} else {
            if (stype == 5) {
                $(':radio[name=sale_prc_over][value=""]').prop('checked', true);
                $(':radio[name=sale_prc_over][value="Y"]').prop('disabled', true);
            }
			if($('[name=sale_type]').val() == 'p') {
				$('#saleLimit').show();
			} else {
				$('#saleLimit').hide();
			}
		}
	}

	// 할인대상 선택변경시 실행
	function settarget(val) {
		$('.setTarget>li').find('.box_btn_s').addClass('hidden');
		$('.setTarget>li').eq(val).find('.box_btn_s').removeClass('hidden');
	}

	var targetSelector = new layerWindow();
	targetSelector.msel = function(json) {
		$(':input[name=member_id]').val(json.member_id);
		setAddr(json);
		this.close();
	}

	function selectTarget(val) {
		switch(val) {
			case 1 :
			case 3 :
				targetSelector.body  = 'promotion@coupon_category_inc.exe'
			break;
			case 2 :
			case 4 :
				targetSelector.body  = 'promotion@coupon_product_inc.exe'
			break;
		}
		targetSelector.body += '&case='+val+'&download_cnt='+download_cnt;
		targetSelector.open();
	}

	function setTargetValue(val) {
		var data = '';
		$('.category_items:checked').each(function() {
			data += '['+this.value+']';
		});
		data = data.replace(/^@/, '');

		$('input[name=attach_items_'+val+']').val(data);

		return false;
	}

	function setTargetPrd(pno, val) {
		var input = $('input[name=attach_items_'+val+']');
		data = input.val();
		data = data.replace('['+pno+']', '')+'['+pno+']';
		input.val(data);

		reloadTargetPrd(val);
	}

	function resetTargetPrd(pno, val) {
		if(!confirm('선택한 상품을 선택취소하시겠습니까?')) return false;
		var input = $('input[name=attach_items_'+val+']');
		data = input.val();
		data = data.replace('['+pno+']', '');
		input.val(data);

		reloadTargetPrd(val);
	}

	function reloadTargetPrd(val) {
		var data = $('input[name=attach_items_'+val+']').val();
		$.post('?body=promotion@coupon_product_inc.exe&exec=selected&case='+val+'&var='+data+'&download_cnt='+download_cnt, function(result) {
			$('#selectedPrds').find('ul').html(result);
		});
	}

	// 할인방식(원/%) 변경시 실행
	function chgSaleType() {
		$('.sale_type').html($(f.sale_type).find(':selected').text());
		if($(f.sale_type).find(':selected').val() == 'p') {
			$(':radio[name=sale_prc_over]').prop('disabled', true);
			$('#saleLimit').show();
		} else {
			if (is_type != 'B') {
				if(f.stype.value != 5) $(':radio[name=sale_prc_over]').prop('disabled', false);
			} else {
				 $(':radio[name=sale_prc_over]').prop('disabled', false);
			}
			$('#saleLimit').hide();
		}
	}

	// 오프라인 쿠폰 코드형식 변경시 실행
	function setCpnLimit() {
		if(is_type == 'B') {
			var obj = $(':checked[name=down_type]');
			if(obj.val() == 'B') {
				$(':radio[name=release_limit][value=1]').prop('disabled', false);
				$(':radio[name=release_limit][value=2]').prop('disabled', false);
			} else {
				$(':radio[name=release_limit][value=1]').prop('disabled', true);
				$(':radio[name=release_limit][value=2]').prop('disabled', false);
				$(':radio[name=release_limit][value=2]').prop('checked', true);
			}

			if($(f.down_type).filter(':checked').val() == 'B') {
				$('#csv_cpnno').hide();
				$('#man_cpnno').show();
			} else {
				$('#csv_cpnno').show();
				$('#man_cpnno').hide();
			}
		}
	}

	function couponCopy() {
		if(!confirm('쿠폰을 복사하시겠습니까?')) return false;
		if(no) {
			window.open('./?body=promotion@coupon_register&is_type='+is_type+'&cpn_no='+no);
		}
	}

	$(':checkbox[name=tmp_is_app]').change(function() {
		$(':checkbox[name=tmp_is_app]').prop('checked', this.checked);
	});

	var checkTmpType = function() {
		var tmp_down_type = $(':checked[name=tmp_down_type]', f).val();

		$('.issue').hide();
		$('.issue_dl').hide();
		$('.issue_auto').hide();
		switch(tmp_down_type) {
			case '1' : // 회원다운로드
			case '4' :
				var tmp_sub_type = $(':checked[name=tmp_sub_type1]', f).val();
				if(!tmp_sub_type) tmp_sub_type = 'A';
				$('.issue.issue1').show();
				if(tmp_sub_type == 'A') { // 전체
					$('.issue_dl1').show();
				} else if(tmp_sub_type == 'B') { // 회원등급별
					$('.issue_dl2').show();
				}
			break;
			case '2' :
				var tmp_sub_type = $(':checked[name=tmp_sub_type2]', f).val();
				$('.issue.issue2').show();
				if(tmp_sub_type == 'F' || tmp_sub_type == 'G') {
					$('.issue_auto2').show();
					f.tmp_is_app.disabled = true;
					f.buy_prcs.disabled = false;
					f.buy_prce.disabled = false;
				} else if(tmp_sub_type == 'C') {
					$('.issue_auto1').show();
					f.tmp_is_app.disabled = false;
					f.buy_prcs.disabled = true;
					f.buy_prce.disabled = true;
				} else if(tmp_sub_type == 'L') {
					$('.issue_auto3').show();
					f.tmp_is_app.disabled = false;
					f.buy_prcs.disabled = true;
					f.buy_prce.disabled = true;
				}

			break;
			case '3' :
				$('.issue.issue3').show();
			break;
		}

		if(f.download_limit && f.download_limit_ea) {
			$(f.download_limit).prop('disabled', false);
			f.download_limit_ea.disabled = false;
			f.download_limit_ea.readOnly = false;
			if(tmp_sub_type && tmp_sub_type == 'G') { // 첫구매완료 쿠폰 1장 발급으로 고정
				$(f.download_limit).filter('[value=3]').prop('checked', true);
				$(f.download_limit).not('[value=3]').prop('disabled', true);
				f.download_limit_ea.value = 1;
				f.download_limit_ea.readOnly = true;
			} else if(tmp_down_type == 4) { // 생일 쿠폰 등록 실수 방지를 위해 무제한으로 고정
				$(f.download_limit).filter('[value=1]').prop('checked', true);
				$(f.download_limit).not('[value=1]').prop('disabled', true);
			}
		}
		if(download_cnt) {
			$(f).find(':text, :radio, :checkbox, select')
				.not('[name=rdate_type], [name=rstart_date], [name=rfinish_date]')
				.prop('disabled', true);
		}
	}

	$(':radio[name=tmp_down_type], :radio[name^=tmp_sub_type]').click(checkTmpType);

	var setDownGrade2 = function() {
		// 등급만, 등급 이상 선택 활성화
		var down_grade2 = $('select[name=down_grade2]').val();
		if(down_grade2 == '') {
			$('select[name=down_gradeonly2]').hide();
		} else {
			$('select[name=down_gradeonly2]').show();
		}

		// 로그인 시 자동발급 전체회원 or 일반회원 일떄만 가입시 적용 체크박스 활성화
		if(down_grade2 == '' || down_grade2 == '9') {
			$('.cpn_option_join').prop('disabled', false);
		} else {
			$('.cpn_option_join').prop('disabled', true);
		}
	}
	$('select[name=down_grade2]').change(function() {
		setDownGrade2();
	});

	$(':radio[name=serial_code_type]').change(function() {
		if(this.value == 'auto') $('input[name=serial_code]').prop('disabled', true);
		else $('input[name=serial_code]').prop('disabled', false);
	});

	function chgPartnerType() {
		var partner_type = $(':checked[name=partner_type]').val();
        var stype = $(':checked[name=stype]').val();

		if(partner_type == '2' || partner_type == '3') {
			$('.partner_area').show();
			$('select[name=partner_no], input[name=partner_fee]').prop('disabled', false);
		} else {
			$('.partner_area').hide();
			$('select[name=partner_no], input[name=partner_fee]').prop('disabled', true);
		}
        if (stype == '3') {
            $('[name=partner_fee]').val('0').prop('disabled', true);
        }
	}

	$(':radio[name=partner_type]').change(function() {
		chgPartnerType();
	});

	$(window).ready(function() {
		if(is_type != 'B') {
			ctrlUseLimit();
		}
		settarget(<?=$data['attachtype']?>);
		chgSaleType();
		setCpnLimit();
		setDownGrade2();
		checkTmpType();
		chgPartnerType();
	});
</script>