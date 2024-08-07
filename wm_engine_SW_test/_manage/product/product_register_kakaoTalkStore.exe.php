<?PHP

	use Wing\API\Kakao\KakaoTalkStore;

	if($admin['level'] > 3 && $cfg['use_kts_partner'] != 'Y') {
		return;
	}

	$kko = $pdo->assoc("select * from $tbl[product_talkstore] where pno='$pno'");
	if(!$kko['no'] && $_POST['kko_useYn'] != 'Y') return;

	$useYn = ($_POST['kko_useYn'] == 'Y') ? 'Y' : 'N';
	$taxType = addslashes($_POST['kko_taxType']);
	$productCondition = addslashes($_POST['kko_productCondition']);
	$deliveryMethodType = addslashes($_POST['kko_deliveryMethodType']);
	$originAreaType = addslashes($_POST['kko_originAreaType']);
	$originAreaCode = addslashes($_POST['kko_originAreaCode_'.$originAreaType]);
	$originAreaContent = addslashes($_POST['kko_originAreaContent']);
	$announcementType = numberOnly($_POST['kko_announcementType']);
	$asPhoneNumber = addslashes($_POST['kko_asPhoneNumber']);
	$asGuideWords = addslashes($_POST['kko_asGuideWords']);
	$displayStatus = ($_POST['kko_displayStatus'] == 'OPEN') ? 'OPEN' : 'HIDDEN';
	$talkstore_prc = ($_POST['kko_use_prc'] == 'Y') ? numberOnly($_POST['talkstore_prc']) : 0;
	$shippingAddressId = numberOnly($_POST['kko_shippingAddressId']);
	$returnAddressId = numberOnly($_POST['kko_returnAddressId']);
	$certType = addslashes($_POST['kko_certType']);
	$certCode = addslashes($_POST['kko_certCode']);

	// 카테고리 정리
	$_categoryId = array_reverse($_POST['kko_categoryId']);
	$categoryName = $categoryId = '';
	foreach($_categoryId as $val) {
		list($_id, $_name) = explode('@', $val);
		if($_id) {
			if(!$categoryId) $categoryId = $_id;
			if($categoryName) $categoryName = '>'.$categoryName;
			$categoryName = addslashes($_name).$categoryName;
		}
	}
	if(!$categoryId) $categoryId = $kko['categoryId'];
	if(!$categoryName) $categoryName = $kko['categoryName'];

	if(!$kko['no'] && $useYn != 'Y') return;
	if($stat == 4 || $useYn != 'Y') $displayStatus = 'HIDDEN';

	if(addField($tbl['product_talkstore'], 'certType', 'varchar(20) not null') == true) {
		addField($tbl['product_talkstore'], 'certCode', 'varchar(50) not null');
	}

	if($kko > 0) {
		$pdo->query("
			update $tbl[product_talkstore] set
				useYn='$useYn', displayStatus='$displayStatus', talkstore_prc='$talkstore_prc',
				categoryId='$categoryId', categoryName='$categoryName', taxType='$taxType', productCondition='$productCondition', deliveryMethodType='$deliveryMethodType', shippingAddressId='$shippingAddressId', returnAddressId='$returnAddressId',
				originAreaType='$originAreaType', originAreaCode='$originAreaCode', originAreaContent='$originAreaContent', announcementType='$announcementType',
				asPhoneNumber='$asPhoneNumber', asGuideWords='$asGuideWords', certType='$certType', certCode='$certCode'
			where no='$kko[no]'
		");
	} else {
		$pdo->query("
			insert into $tbl[product_talkstore]
			(pno, useYn, displayStatus, talkstore_prc, categoryId, categoryName, taxType, productCondition, deliveryMethodType, shippingAddressId, returnAddressId, originAreaType, originAreaCode, originAreaContent, announcementType, asPhoneNumber, asGuideWords, certType, certCode, edt_date)
			values
			('$pno', '$useYn', '$displayStatus', '$talkstore_prc', '$categoryId', '$categoryName', '$taxType', '$productCondition', '$deliveryMethodType', '$shippingAddressId', '$returnAddressId', '$originAreaType', '$originAreaCode', '$originAreaContent', '$announcementType', '$asPhoneNumber', '$asGuideWords', '$certType', '$certCode', now())
		");
	}

	if($pdo->lastRowCount() > 0 || count($_img_changed) > 0 || parsePrice($data['sell_prc']) != $sell_prc || $content2 != addslashes($data['content2'])) {
		$pdo->query("update {$tbl['product_talkstore']} set edt_date=now() where no='$kko[no]'");

		$kts = new KakaoTalkStore();
		$ret = $kts->productRegister($pno);
		$ret = json_decode($ret);

		// 에러메시지 출력
		$kko_errmsg = '';
		if(count($ret->extras->validation)) {
			foreach($ret->extras->validation as $val) {
				$kko_errmsg .= $val[0]."\n";
			}
		}
		if(empty($ret->extras->error_message) == false) {
			$kko_errmsg .= $ret->extras->error_message."\n";
		}
		if($kko_errmsg) {
			$kko_errmsg = "[카카오톡 스토어 등록 체크사항]\n".$kko_errmsg;
			alert(php2java(trim($kko_errmsg)));
			$pdo->query("update {$tbl['product_talkstore']} set useYn='N', edt_date=now() where no='$kko[no]'");
		} else {
			$pdo->query("update {$tbl['product_talkstore']} set edt_date=now() where no='$kko[no]'");
		}
	}

?>