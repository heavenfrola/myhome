<?PHP

	if($_POST['exec'] == 'remove') {
		$idx = implode(',', numberOnly($_POST['idx']));
		$pdo->query("delete from $tbl[product_talkstore_announce] where idx in ($idx)");
		exit;
	}

	require_once $engine_dir.'/_config/set.talkStore.php';

	$idx = $_POST['idx'];
	$type = $_POST['type'];
	$title = addslashes($_POST['title']);
	$datas = $_POST['datas'];
	$fields = $_talkstore_announce[$type]['fields'];

	checkBlank($type, '상품군을 선택해주세요.');
	checkBlank($title, '정보고시 제목을 입력해주세요.');
	if(is_array($fields) == false) msg('지정된 상품군이 아닙니다.');

	foreach($fields as $key => $val) {
		$datas[$key] = trim($datas[$key]);
		checkBlank($datas[$key], $val.' 항목을 입력해주세요.');
	}
	$data['ignoreAll'] = 'false';
	$datas = addslashes(json_encode($datas));

	if($idx > 0) {
		$pdo->query("
			update $tbl[product_talkstore_announce] set title='$title', type='$type', datas='$datas' where idx='$idx'
		");
	} else {
		$pdo->query("
			insert into $tbl[product_talkstore_announce]
			(title, type, datas, reg_date)
			values
			('$title', '$type', '$datas', now())
		");
	}

	$listURL = getListURL('product_definition');
	if(empty($listURL) == true) $listURL = './?body=product@product_definition&type=talkstore';

	msg('', $listURL, 'parent');

?>