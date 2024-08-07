<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 연결할 상품 검색
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type: application/json; charset='._BASE_CHARSET_);
	checkBasic(2);

	$search_type = addslashes($_POST['search_type']);
	$search = addslashes($_POST['search']);

    $asql = '';
    if ($admin['level'] == 4) {
        $asql .= " and partner_no='{$admin['partner_no']}'";
    }

	$list = array();
	if($search_type && $search) {
		$sql = $pdo->iterator("select `no`,`name` from {$tbl['product']} where stat in (2, 3, 4) and wm_sc = 0 and `$search_type` like '%$search%' $asql limit 50");
        foreach ($sql as $data) {
			$list[] = array(
				'no' => $data['no'],
				'name' => cutstr(trim(strip_tags(stripslashes($data['name']))), 60),
			);
		}
	}

	exit(json_encode($list));

?>