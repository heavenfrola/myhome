<?PHP

checkBasic();

$exec = addslashes($_POST['exec']);
if(!$exec) msg('잘못된 경로 입니다.');

if($exec == 'register') {
	$fno = numberOnly($_POST['fno']);
	$sort = numberOnly($_POST['sort']);

	if($fno) {
		$data = get_info($tbl['store_facility_set'], "no", $fno);
		if(!$data['no']) msg("존재하지 않는 자료입니다", "popup");
	}

	$name = addslashes(trim($_POST['name']));
	$content = addslashes(trim($_POST['content']));

	checkBlank($name,"시설명을 입력해주세요.");

	$_sql_arr = array(
		'name' => $name, // 제목
		'content' => $content, //휴대폰
	);

	for($i=1; $i<=1; $i++ ) {
		if ($_FILES['upfile'.$i]) {
			$file = $_FILES['upfile'.$i];

			if ($file['size'] > 0) {
				if ($data['upfile'.$i]) {
					deletePrdImage($data, 1, 1);
				}

				if (!$updir) {
					$updir = $dir['upload'] . '/store/facility/';
					makeFullDir($updir);
					$_sql_arr['updir'] = $updir;
				}

				$up_filename = md5($file['name'].$now.$file['size']);

				$up_info = uploadFile($file, $up_filename, $updir,'jpg|gif|png');
				${'upfile'.$i} = $up_info[0];
				$_sql_arr['upfile'.$i] = ${'upfile'.$i};
			}
		}
		// 파일업로드
		$_file = array(
			'tmp_name' => $_FILES['upfile'.$i]['tmp_name'],
			'name' => $_FILES['upfile'.$i]['name'],
			'size' => $_FILES['upfile'.$i]['size'],
		);
		if(($_file['size'] > 0 ) || $_POST['delfile'.$i] == 'Y') {
			if(!$_file['size']) {
				$_sql_arr['upfile'.$i] = '';
			}
		}
	}

	$_where = array();

	if ($fno) {
		$_sql_arr['edt_date'] = $now;
		$_where['no'] = $fno;
	} else {
		$_sql_arr['sort'] = $pdo->row("select max(sort) from {$tbl['store_facility_set']} where 1")+1;
		$_sql_arr['ip'] = $_SERVER['REMOTE_ADDR'];
		$_sql_arr['reg_date'] = $now;
	}

	//쿼리 병합
	$_mqry = qryResult($_sql_arr, $_where);

	//수정 시
	if ($fno) {
		$subject = "수정";

		$msql = "update {$tbl['store_facility_set']} set " . $_mqry['u'] . " where " . $_mqry['w'];
	} else { // 추가
		$subject = "추가";
		$msql = "INSERT INTO {$tbl['store_facility_set']} (" . $_mqry['i'] . ") VALUES (" . $_mqry['v'] . ")";
	}
	$pdo->query($msql, $_mqry['a']);

	javac("parent.fdFrm.reload();");
} else if($exec == 'remove') {
	$no = numberOnly($_POST['no']);
	$no = implode(',', $no);

	// 첨부파일 삭제
	$res = $pdo->iterator("select updir, upfile1 from {$tbl['store_facility_set']} where no in ($no) and upfile1!='' ");
	foreach ($res as $data) {
		deletePrdImage($data, 1);
	}

	// 추가항목 세트 삭제
	$pdo->query("delete from {$tbl['store_facility_set']} where no in ($no)");
	exit;
} else if($exec == 'sort') {
	$source = numberOnly($_POST['source']);
	$target = numberOnly($_POST['target']);

	$source = $pdo->assoc("select no, sort from {$tbl['store_facility_set']} where no=:source",array(':source'=>$source));
	$target = $pdo->assoc("select no, sort from {$tbl['store_facility_set']} where no=:no", array(':no'=>$target));
	if(!$source['no'] || !$target['no']) exit;

	$pdo->query("update {$tbl['store_facility_set']} set sort=:sort where no=:no",
		array(':sort'=>$source['sort'], ':no'=>$target['no'])
	);
	$pdo->query("update {$tbl['store_facility_set']} set sort=:sort where no=:no",
		array(':sort'=>$target['sort'], ':no'=>$source['no'])
	);

	exit;
}
?>