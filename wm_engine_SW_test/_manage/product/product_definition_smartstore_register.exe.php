<?PHP

	checkBasic();

	if($_POST['exec'] == 'remove') {
		$no = implode(',', numberOnly($_POST['no']));
		$pdo->query("delete from $tbl[store_summary] where no in ($no)");
		exit;
	}

	$no = $_POST['no'];
	$category = numberOnly($_POST['category']);
	$title = addslashes($_POST['title']);
	$datas = $_POST['datas'];

	checkBlank($category, '정보고시 상품군을 선택해주세요.');
	checkBlank($title,'정보고시 제목을 입력해주세요.');

	$fields = array();
	$list_res = $pdo->iterator("select * from `$tbl[store_summary_list]` where summary_no='".$category."' and essential='Y' order by no asc");
    foreach ($list_res as $l_data) $fields[$l_data['name']] = $l_data['summary'];
	foreach($fields as $key => $val) {
		$datas[$key] = trim($datas[$key]);
		if( preg_match('/Date$/', $key) && !preg_match("/([0-9]{4})-([0-9]{2})/", $datas[$key]) && $datas[$key])
		{
			msg("날짜 형식이 다릅니다.");
		}
		//checkBlank($datas[$key],'정보고시 내용을 입력해주세요.');
	}

	$datas = str_replace('\\','/',json_encode($datas));
	$datas = addslashes($datas);

	if($no > 0) {
		$pdo->query("
			update $tbl[store_summary] set title='$title', category='$category', datas='$datas' where no='$no'
		");
	} else {
		$pdo->query("
			insert into $tbl[store_summary]
			(title, category, datas, reg_date, insert_id, insert_ip)
			values
			('$title', '$category', '$datas', now(), '$admin[admin_id]', '$_SERVER[REMOTE_ADDR]')
		");
	}

	$listURL = getListURL('product_definition');
	if(empty($listURL) == true) $listURL = './?body=product@product_definition&type=smartstore';

	msg('', $listURL, 'parent');
?>