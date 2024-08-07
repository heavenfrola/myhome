<?PHP

	printAjaxHeader();
	checkBasic();

	if($_POST['exec'] == 'initial_xy') { // 2,3 분류 정렬기능 초기화
		$sort = 0;
		$res = $pdo->iterator("select * from {$tbl['product']} where stat>1 and (xbig > 0 or ybig > 0) order by reg_date asc");
        foreach ($res as $data) {
			$sort++;
			for($i = 4; $i <= 5; $i++) {
				$_prefix = ($i == 4) ? 'x' : 'y';
				if($data[$_prefix.'big'] > 0) {
					$nbig = $data[$_prefix.'big'];
					$nmid = $data[$_prefix.'mid'];
					$nsmall = $data[$_prefix.'small'];

					$pdo->query("
						insert into {$tbl['product_link']} (pno, ctype, nbig, nmid, nsmall, sort_big, sort_mid, sort_small)
						values ('{$data['no']}', '$i', '$nbig', '$nmid', '$nsmall', '$sort', '$sort', '$sort')
					");
				}
			}
		}

		// 설정 저장
		unset($_POST);
		$_POST['use_new_sortxy'] = 'Y';
		include $engine_dir.'/_manage/config/config.exe.php';

		exit;
	}

	$ctype = numberOnly($_POST['ctype']);
	$_tmp_sort = explode("|", $_POST['sortingArray']);
	$_sort_fd_name = ($_POST['sort_fd_name']) ? addslashes($_POST['sort_fd_name']) : 'edt_date';
	$sort_val = explode('|', $_POST['sort_val']);
	if(count($sort_val) != count(array_unique($sort_val))) {
		exit("상품정렬코드에 문제가 있습니다.\n정렬값이 업데이트 되지 않았을수 있으므로\n1:1고객센터 문의 글로 접수 바랍니다.");
	}

	if($ctype == 1)	rsort($sort_val);
	else sort($sort_val);

	if(count($_tmp_sort) < 1) {
		exit('ERROR2');
	}

	foreach($_tmp_sort as $k => $pno){
		$sort_no = $sort_val[$k];
		if($ctype == 1) {
			$pdo->query("update {$tbl['product']} p set $_sort_fd_name='$sort_no' where `no`='$pno'");
		} else {
			$pdo->query("update {$tbl['product_link']} l set $_sort_fd_name='$sort_no' where pno='$pno' and ctype='$ctype'");
		}
	}

	exit("OK");

?>