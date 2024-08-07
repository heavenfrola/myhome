<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개별 배송비 등록
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['exec'] == 'removeDeliverySet') {
		$no = numberOnly($_POST['no']);
		$pdo->query("delete from {$tbl['product_delivery_set']} where no='$no'");
		$pdo->query("update {$tbl['product']} set delivery_set=0 where delivery_set='$no'");
		exit;
	}

	$no = numberOnly($_POST['no']);
	$set_name = addslashes(trim($_POST['set_name']));
	$delivery_type = numberOnly($_POST['delivery_type']);
	$delivery_loop_type = ($_POST['delivery_loop_type'] == 'Y') ? 'Y' : 'N';
	$delivery_base = numberOnly($_POST['delivery_base']);
	$free_delivery_area = addslashes($_POST['free_delivery_area']);
	$free_yn = ($_POST['free_yn'] == 'Y') ? 'Y' : 'N';
	$policy_N_std = numberOnly($_POST['policy_N_std']);
	$policy_N_end = numberOnly($_POST['policy_N_end']);
	$policy_N_prc = numberOnly($_POST['policy_N_prc']);
	$policy_Y_std = numberOnly($_POST['policy_Y_std']);
	$policy_Y_end = numberOnly($_POST['policy_Y_end']);
	$policy_Y_prc = numberOnly($_POST['policy_Y_prc']);
	$policy_static_prc = numberOnly($_POST['policy_static_prc']);

	switch($delivery_type) {
		case '4' : // 금액별 배송, 수량별 배송
		case '5' :
			if($delivery_loop_type == 'N') {
				$data = array();
				$cnt = count($policy_N_end);
				foreach($policy_N_end as $key => $_end) {
					if($cnt == $key+1) { // 마지막 범위 자동 입력
						$_end = $policy_N_std[$key];
					} else if($key > 0) { // 범위 값이 계속 커지도록 유도
						if($_end <= $policy_N_end[$key-1]) msg(($key+1).'번째 범위 값('.$_end.')이 앞 범위 값('.$policy_N_end[$key-1].')보다 커야합니다.');
					}
					if($policy_N_prc[$key] === '') msg('배송비를 입력해주세요.');
					$data[] = array($policy_N_std[$key], $_end, $policy_N_prc[$key]);
				}
				$delivery_free_limit = json_encode($data);
			} else { // 범위 반복
				$delivery_free_limit = json_encode(array(array($policy_Y_std, $policy_Y_end, $policy_Y_prc)));
			}
			break;
		case '6' :
			$delivery_free_limit = $policy_static_prc;
			break;
	}

	if($no > 0) {
		$pdo->query("
			update {$tbl['product_delivery_set']} set
				set_name='$set_name', delivery_type='$delivery_type', delivery_base='$delivery_base', delivery_loop_type='$delivery_loop_type', delivery_free_limit='$delivery_free_limit', free_delivery_area='$free_delivery_area', free_yn='$free_yn', edt_date=now()
			where no='$no'
		");
	} else {
		$pdo->query("
			insert into {$tbl['product_delivery_set']}
				(partner_no, set_name, delivery_type, delivery_base, delivery_loop_type, delivery_free_limit, free_delivery_area, free_yn, edt_date, reg_date, admin_id)
				values
				('{$admin['partner_no']}', '$set_name', '$delivery_type', '$delivery_base', '$delivery_loop_type', '$delivery_free_limit', '$free_delivery_area', '$free_yn', now(), now(), '{$admin['admin_id']}')
		");
	}
    if ($err = $pdo->getError()) {
        msg('설정 저장 중 오류가 발생하였습니다.');
    }

	$listURL = getListURL('delivery_set');
	if(empty($listURL)) $listURL = '?body=config@delivery_set';

	msg('', $listURL, 'parent');

?>