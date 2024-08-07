<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  입점신청 상세
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_GET['no']);
	if($no) {
		$data = $pdo->assoc("select * from `$tbl[partner_shop]` where `no`='$no'");
		if(!$data) msg('존재하지 않는 게시물입니다.', 'back');
		$data = array_map('stripslashes', $data);
	} else {
		$wec = new WeagleEyeClient($_we, 'account');
		$partnershop_ea = $wec->call('getPartnershopInfo');
		if($partnershop_ea != 'unlimited') {
			$cnt = $pdo->row("select count(*) from $tbl[partner_shop] where stat!=5")+1;
			if($cnt > $partnershop_ea) {
				msg('입점사는 최대 '.$partnershop_ea.'개 까지만 등록 가능합니다.\n윙 스토어에서 추가해주세요.', 'back');
			}
		}
	}

	$dates = ($data['dates'] > 0) ? date('Y-m-d', $data['dates']) : '';
	$datee = ($data['datee'] > 0) ? date('Y-m-d', $data['datee']) : '';
	$partner_no = ($no) ? $no : 'temp'.$now;

	if(!isTable($tbl['partner_sms'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['partser_sms']);
	}

	addField($tbl['partner_shop'], 'partner_sms', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_sms_use', "enum('N', 'Y') not null default 'N'");
	addField($tbl['partner_shop'], 'partner_email', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_email_use', "enum('Y', 'N') not null default 'N'");

?>
<form name="partnerFrm" method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="product@product_join_shop.exe" />
	<input type="hidden" name="no" value="<?=$no?>" />
	<input type="hidden" name="partner_no" value="<?=$partner_no?>" />

	<table class="tbl_row">
		<caption>입점사 정보입력</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">입점사명</th>
				<td><input type="text" name="corporate_name" class="input" size="80" value="<?=inputText($data['corporate_name'])?>"></td>
			</tr>
			<tr>
				<th scope="row">사업자 등록번호</th>
				<td><input type="text" name="biz_num" class="input" size="20" value="<?=inputText($data['biz_num'])?>"></td>
			</tr>
			<tr>
				<th scope="row">통신판매업신고번호</th>
				<td><input type="text" name="com_num" class="input" size="20" value="<?=inputText($data['com_num'])?>"></td>
			</tr>
			<tr>
				<th scope="row">업태/업종</th>
				<td>
					<input type="text" name="service_type1" class="input" size="10" value="<?=inputText($data['service_type1'])?>"> /
					<input type="text" name="service_type2" class="input" size="10" value="<?=inputText($data['service_type2'])?>">
				</td>
			</tr>
			<tr>
				<th scope="row">대표자명</th>
				<td><input type="text" name="ceo" class="input" size="20" value="<?=inputText($data['ceo'])?>"></td>
			</tr>
			<tr>
				<th scope="row" rowspan="3">사업장 소재지</th>
				<td>
					<input type="text" name="zipcode" class="input" size="7" value="<?=inputText($data['zipcode'])?>">
					<span class="box_btn_s"><input type="button" value="우편번호검색" onClick="zipSearchM('partnerFrm', 'zipcode', 'addr1', 'addr2')"></span>
				</td>
			</tr>
			<tr>
				<td><input type="text" name="addr1" class="input" size="80" value="<?=inputText($data['addr1'])?>"></td>
			</tr>
			<tr>
				<td><input type="text" name="addr2" class="input" size="80" value="<?=inputText($data['addr2'])?>"></td>
			</tr>
			<tr>
				<th scope="row">이메일</th>
				<td><input type="text" name="email" class="input" size="80" value="<?=inputText($data['email'])?>"></td>
			</tr>
			<tr>
				<th scope="row">연락처</th>
				<td><input type="text" name="cell" class="input" size="80" value="<?=inputText($data['cell'])?>"></td>
			</tr>
			<tr>
				<th scope="row">사이트 URL</th>
				<td><input type="text" name="siteurl" class="input" size="80" value="<?=inputText($data['siteurl'])?>"></td>
			</tr>
			<tr>
				<th scope="row">요약정보</th>
				<td><input type="text" name="title" class="input" size="80" value="<?=inputText($data['title'])?>"></td>
			</tr>
			<tr>
				<th scope="row">업체메모</th>
				<td><textarea name="content" class="txta" rows="5"><?=inputText($data['content'])?></textarea></td>
			</tr>
			<tr>
				<th scope="row">첨부파일</th>
				<td>
					<?if($data['upfile1']) {?>
					<span class="box_btn_s icon excel"><a href="./index.php?body=product@product_join_shop.exe&exec=download&no=<?=$no?>&target=1" target="hidden<?=$now?>">파일다운</a></span>
					<?}?>
					<input type="file" name="upfile1" class="input">
				</td>
			</tr>
		</tbody>
	</table>
	<br>

	<table class="tbl_row">
		<caption>담당자 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">등록 담당자</th>
				<td>
					<span class="box_btn_s blue"><input type="button" value="추가" onclick="msearch.open();"></span>
					<ul class="list_info dam_list tp">
						<?$exec = 'dam'; include 'product_join_shop.exe.php';?>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<br>

	<table class="tbl_row">
		<caption>
			입점사 기타 설정
			<p class="right"><a href="<?=$root_url?>/_manage/?body=config@partner_shop" target="_blank"><img src="<?=$engine_url?>/_manage/image/product/register/setup.png" class="btn_setup"></a></p>
		</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">입점사 문자알림</th>
				<td>
				<?if($cfg['partner_sms_config'] == '1') {
					$data['partner_sms_use'] = 'Y';
				?>
					<label><input type="radio" name="partner_sms_use" checked value="Y" <?=checked($data['partner_sms_use'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="partner_sms_use" disabled value="N" <?=checked($data['partner_sms_use'], 'N')?>> 사용안함</label>
				<?}?>
				<?if($cfg['partner_sms_config'] == '2') {?>
					<label><input type="radio" name="partner_sms_use" value="Y" <?=checked($data['partner_sms_use'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="partner_sms_use" value="N" <?=checked($data['partner_sms_use'], 'N')?>> 사용안함</label>
				<?}?>
				<?if($cfg['partner_sms_config'] == '3' || !$cfg['partner_sms_config']) {
					$data['partner_sms_use'] = 'Y';
				?>
					<label><input type="radio" name="partner_sms_use" disabled value="Y" <?=checked($data['partner_sms_use'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="partner_sms_use" checked value="N" <?=checked($data['partner_sms_use'], 'N')?>> 사용안함</label>
				<?}?>
				</td>
			</tr>
		</tbody>
	</table>
	<br>

	<table class="tbl_row">
		<caption>정산 및 계약정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">결제 계좌 명의</th>
				<td><input type="text" name="bank_name" class="input" size="20" value="<?=inputText($data['bank_name'])?>"></td>
			</tr>
			<tr>
				<th scope="row">결제 은행</th>
				<td><input type="text" name="bank" class="input" size="20" value="<?=inputText($data['bank'])?>"></td>
			</tr>
			<tr>
				<th scope="row">은행 계좌 번호</th>
				<td><input type="text" name="bank_account" class="input" size="20" value="<?=inputText($data['bank_account'])?>"></td>
			</tr>
			<tr>
				<th scope="row">계약 수수료율</th>
				<td>
					<input type="text" name="partner_rate" class="input" size="15" value="<?=inputText($data['partner_rate'])?>"> %
					<ul class="list_info">
						<li>여러개의 수수료율을 사용하실 경우 콤마(,)로 구분해 주세요.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">계약 상태</th>
				<td>
					<?=selectArray($_partner_stats, 'stat', null, false, $data['stat'])?>
				</td>
			</tr>
			<tr>
				<th scope="row">계약 기간</th>
				<td>
					<input type="text" name="dates" class="input datepicker" size="10" value="<?=$dates?>"> ~
					<input type="text" name="datee" class="input datepicker" size="10" value="<?=$datee?>">
				</td>
			</tr>
			<tr>
				<th scope="row">정산 일자</th>
				<td>
					지난달 매출을
					매월 <input type="text" name="account_dates" class="input" size="3" value="<?=inputText($data['account_dates'])?>"> 일에 정산
				</td>
			</tr>
		</tbody>
	</table>
	<br>

	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="저장"></span>
		<span class="box_btn"><input type="button" value="목록" onclick="history.back();"></span>
	</div>
</form>
<script type="text/javascript">
	var msearch = new layerWindow('intra@mng_inc.exe&level=4');
	msearch.msel = function(ano) {
		mngLoad('<?=$partner_no?>', ano);
	}

	function mngLoad(partner_no, new_mng_no, del_mng_no) {
		if(!new_mng_no) new_mng_no = '';

		$.post('./index.php', {'body':'product@product_join_shop.exe', 'exec':'dam', 'partner_no':partner_no, 'new_mng_no':new_mng_no, 'del_mng_no':del_mng_no}, function(r) {
			$('.dam_list').html(r);
			msearch.close();
		});
	}

	function removeDam(mng_no) {
		if(confirm('선택한 담당자를 입점사 담당자에서 해제 하시겠습니까?')) {
			mngLoad('<?=$partner_no?>', null, mng_no);
		}
	}
</script>