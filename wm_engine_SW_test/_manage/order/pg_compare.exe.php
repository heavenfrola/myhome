<?PHP

	// 설정 저장
	if($_POST['exec'] == 'setup') {
		$data = addslashes(trim($_POST['data']));

		$no = $pdo->row("select no from $tbl[excel_preset] where type='pg' and name='used'");
		if($no) {
			$qry = "update $tbl[excel_preset] set data='$data', reg_date='$now' where no='$no'";
		} else {
			$qry = "insert into $tbl[excel_preset] (type, name, data, reg_date) values ('pg', 'used', '$data', '$now')";
		}
		$r = $pdo->query($qry);

		if($r) msg('설정이 저장되었습니다.');
		else msg('설정 저장 중 오류가 발생하였습니다.');

		return;
	}

	// 엑셀파일 처리
	require 'pg_compare.php';

	$csv = $_FILES['csv'];
	if($csv['size'] < 1) {
		msg('csv파일을 업로드 해주세요.');
	}

	$linenum = 0;
	$onos = array();
	$fp = fopen($csv['tmp_name'], 'r');
	while($row = fgetcsv($fp, 1024)) {
		$linenum++;
		if($linenum == 1) continue;

		foreach($data as $key => $val) {
			$data[$val] = addslashes(trim($row[$key]));
		}

		// 중복 처리
		if(in_array($data['ono'], $onos) == true) {
			$linenum--;
			continue;
		}
		$onos[] = $data['ono'];

		$ord = $pdo->assoc("select stat, stat2 from {$tbl['order']} where ono='{$data['ono']}'");

		$err = null;
		if(empty($ord['stat'])) $err = '존재하지 않는 주문번호입니다.';
		elseif($ord['stat'] != 11 && strpos($ord['stat2'], '@11@') === false) $err = '주문서가 승인대기 상태가 아닙니다.';

		if($err) {
			$result[($linenum-1)] = array(
				'status' => 'error',
				'ono' => $data['ono'],
				'message' => $err
			);
			continue;
		}

		$asql = "stat='2', res_msg='수기인증'";
		if(empty($data['confirm_date']) == false) {
			$data['confirm_date'] = numberOnly($data['confirm_date']);
			$asql .= ", app_time='{$data['confirm_date']}'";
		}
		if(empty($data['tid']) == false) $asql .= ", tno='{$data['tid']}'";
		if(empty($data['card_nm']) == false) $asql .= ", card_name='{$data['card_nm']}'";
		if(empty($data['interest']) == false) {
			$data['interest'] = numberOnly($data['interest']);
			$asql .= ", quota='{$data['interest']}'";
		}
		$pdo->query("update {$tbl['card']} set $asql where wm_ono='{$data['ono']}' and stat=1");

		$result[($linenum-1)] = array(
			'status' => 'ok',
			'ono' => $data['ono'],
		);
	}
	$result = json_encode($result);

?>
<form id="resultFrm" method="post" action="?body=order@pg_compare" target="_parent">
	<input type="hidden" name="result" value="<?=htmlspecialchars($result)?>">
</form>
<script type="text/javascript">
document.querySelector('#resultFrm').submit();
</script>