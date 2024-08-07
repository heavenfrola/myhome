<?PHP

	printAjaxheader();

	if($_POST['exec'] == 'putCoupon') {
		$cno = numberOnly($_POST['cno']);
		$member_no = numberOnly($_POST['member_no']);

		$cpn = $pdo->assoc("select * from $tbl[coupon] where no='$cno'");
		$amember = $pdo->assoc("select * from $tbl[member] where no='$member_no'");

		if(!$cpn['no']) exit('존재하지 않는 쿠폰정보입니다.');
		if(!$amember['no']) exit('존재하지 않는 회원정보입니다.');

		if(putCoupon($cpn, $amember)) {
			exit('OK');
		}

		exit("쿠폰발급이 실패되었습니다.\n다운로드 권한 및 발급 횟수를 확인해주세요.");
	}

	$no = numberOnly($_GET['no']);
	$cpn = $pdo->assoc("select * from $tbl[coupon] where no='$no'");
	$cpn = array_map('stripslashes', $cpn);

	if(!$cpn['device']) $cpn['device'] = 'PC+모바일';
	if(!$cpn['place']) $cpn['place'] = 'on/offline';

	if($cpn['down_type'] == 'B') {
		$cpn['down_grade_str']  = $pdo->row("select name from $tbl[member_group] where no='$cpn[down_grade]'");
		$cpn['down_grade_str'] .= ' 등급';
		$cpn['down_grade_str'] .= $cpn['down_gradeonly'] == 'Y' ? '전용' : '이상';
	}

	$cpn['give_prc1'] = number_format($cpn['give_prc1']);
	$cpn['give_prc2'] = number_format($cpn['give_prc2']);

	$cpn['release_limit_ea'] = $cpn['release_limit'] == 3 ? $cpn['release_limit_ea'].' 장' : '';
	$cpn['download_limit_ea'] = $cpn['release_limit'] == 3 ? $cpn['download_limit_ea'].' 장' : '';

	if($cpn['rdate_type'] == 1) {
		$cpn['rdate_type'] = '무제한';
	} else {
		$cpn['rstart_date'] = date('Y-m-d H 시', strtotime($cpn['rstart_date']));
		$cpn['rfinish_date'] = date('Y-m-d H 시', strtotime($cpn['rfinish_date']));
		$cpn['rdate_type'] = "$cpn[rstart_date] ~ $cpn[rfinish_date]";
	}

	if($cpn['udate_type'] == 1) {
		$cpn['udate_type'] = "무제한";
	} else if($cpn['udate_type'] == 2) {
		$cpn['ustart_date'] = date('Y-m-d H 시', strtotime($cpn['ustart_date']));
		$cpn['ufinish_date'] = date('Y-m-d H 시', strtotime($cpn['ufinish_date']));
		$cpn['udate_type'] = $cpn['ustart_date'].'<br>~ '.$cpn['ufinish_date'];
	} else {
		$cpn['udate_type'] = '발급후 '.$cpn['udate_limit'].' 일';
	}

	$_week = array(1 => '월', 2 => '화', 3 => '수', 4 => '목', 5 => '금', 6 => '토', 0 => '일');
	if($cpn['weeks']) {
		$cpn['weeks'] = explode('@', $cpn['weeks']);
		foreach($cpn['weeks'] as $val) {
			$cpn_weeks .= $_week[$val].'요일 ';
		}
	} else {
		$cpn_weeks = '전체';
	}

	if($cpn['attach_items'] && ($cpn['attachtype'] == 1 || $cpn['attachtype'] == 3)) {
		$_attach = explode('][', preg_replace('/^\[|\]$/', '', $cpn['attach_items']));
		$cres = $pdo->iterator("select name, ctype from $tbl[category] where no in (".implode(',', $_attach).") order by ctype asc, sort asc");
        foreach ($cres as $cdata) {
			switch($cdata['ctype']) {
				case 1 : $_ctype_name = '[기본매장분류]'; break;
				case 2 : $_ctype_name = '[기획전]'; break;
				case 4 : $_ctype_name = $cfg['xbig_name_mng']; break;
				case 5 : $_ctype_name = $cfg['ybig_name_mng']; break;
			}
			$val = stripslashes($cdata['name']);
			$attach_items .= "<li>$_ctype_name <strong>$val</strong></li>";
		}
	} else if($cpn['attachtype'] == 2 || $cpn['attachtype'] == 4) {
		$_attach = explode('][', preg_replace('/^\[|\]$/', '', $cpn['attach_items']));
		$pres = $pdo->iterator("select no, name from $tbl[product] where no in (".implode(',', $_attach).") order by name asc");
        foreach ($pres as $pdata) {
			$val = stripslashes($pdata['name']);
			$attach_items .= "<li>$val <a href='./index.php?body=product@product_register&pno=$pdata[no]' class='sclink' target='_blank'>보기</a></li>";
		}
	}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">쿠폰 할인 정보</div>
	</div>
	<div id="popupContentArea">
		<?if($cpn['no']) {?>
		<table class="tbl_row">
			<caption>쿠폰 할인 정보</caption>
			<colgroup>
				<col style="width:160px">
			</colgroup>
			<tr>
				<th scope="row">쿠폰명</th>
				<td><?=$cpn['name']?></td>
			</tr>
			<tr>
				<th scope="row">쿠폰 종류</th>
				<td><?=$_cpn_stype[$cpn['stype']]?></td>
			</tr>
			<tr>
				<th scope="row">적용 범위</th>
				<td>
					<ul>
						<li><?=$cpn['device']?></li>
						<li><?=$cpn['place']?></li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">할인 금액</th>
				<td><?=number_format($cpn['sale_prc'])?><?=$_cpn_sale_type[$cpn['sale_type']]?></td>
			</tr>
			<tr>
				<th scope="row">사용 제한 결제액</th>
				<td><?=number_format($cpn['prc_limit'])?> 원 이상 주문시 사용 가능</td>
			</tr>
			<?if($cpn['sale_type'] == 'p') {?>
			<tr>
				<th scope="row">최대 할인금액</th>
				<td><?=number_format($cpn['sale_limit'])?> 원</td>
			</tr>
			<?}?>
			<?if($cpn['give_type'] == 4) {?>
			<tr>
				<th scope="row">필요 주문금액</th>
				<td>
					<?=$cpn['give_prc1']?>원 ~ <?=$cpn['give_prc2']?>원
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">결제 방식</th>
				<td><?=$_cpn_pay_type[$cpn['pay_type']]?></td>
			</tr>
			<tr>
				<th scope="row">사용 제한</th>
				<td><?=$_cpn_use_limit_type[$cpn['use_limit']]?></td>
			</tr>
		</table>
		<table class="tbl_row">
			<caption>쿠폰 발급 정보</caption>
			<colgroup>
				<col style="width:160px">
			</colgroup>
			<?if($cpn['is_type'] == 'A') {?>
			<tr>
				<th scope="row">다운로드 권한</th>
				<td>
					<?=$_cpn_downtype[$cpn['down_type']]?>
					<?=$cpn['down_grade_str']?>
				</td>
			</tr>
			<?}?>
			<?if($cpn['release_limit']) {?>
			<tr>
				<th scope="row">발급 수량</th>
				<td><?=$_cpn_release_type[$cpn['release_limit']]?> <?=$data['release_limit_ea']?></td>
			</tr>
			<?}?>
			<?if($cpn['download_limit']) {?>
			<tr>
				<th scope="row">1인당 보유 가능 수량</th>
				<td><?=$_cpn_download_limit_type[$cpn['download_limit']]?> <?=$data['download_limit_ea']?></td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">발급 기간</th>
				<td><?=$cpn['rdate_type']?></td>
			</tr>
			<tr>
				<th scope="row">사용 기간</th>
				<td><?=$cpn['udate_type']?></td>
			</tr>
			<tr>
				<th scope="row">사용 요일</th>
				<td><?=$cpn_weeks?></td>
			</tr>
		</table>
		<table class="tbl_row">
			<caption>쿠폰 할인 및 제외 대상</caption>
			<colgroup>
				<col style="width:160px">
			</colgroup>
			<tr>
				<th scope="row">적용대상</th>
				<td><?=$_cpn_attatch_type[$cpn['attachtype']]?></td>
			</tr>
			<?if($attach_items){?>
			<tr>
				<th scope="row">상세 적용대상</th>
				<td>
					<ul style="overflow:auto; max-height: 150px;">
						<?=$attach_items?>
					</ul>
				</td>
			</tr>
			<?}?>
		</table>
		<?} else {?>
		삭제된 쿠폰입니다.
		<?}?>
		<div class="pop_bottom">
			<span class="box_btn blue"><input type="button" value="닫기" onclick="cpndetail.close();"></span>
			<?if(!$readOnly) {?>
			<span class="box_btn gray"><input type="button" value="지급" onclick="putCoupon(<?=$cpn['no']?>);"></span>
			<?}?>
		</div>
	</div>
</div>