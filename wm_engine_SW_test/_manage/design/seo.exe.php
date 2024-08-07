<?PHP

	include_once $engine_dir.'/_engine/include/file.lib.php';

	if(isTable($tbl['seo_config']) == false) {
		require_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['seo_config']);
	}

	$pages = array(
		'common',
		'prdList',
		'prdDetail',
		'boardList',
		'boardView'
	);
	$names = array(
		'title',
		'description',
		'keyword',
		'image_use',
		'upfile1'
	);
	$updir = '_data/banner/seo';

	$tag_type = addslashes($_POST['tag_type']);
	$admin_id = addslashes($admin['admin_id']);

	$pdo->query('start transaction;');

	foreach($pages as $page) {
		$data = $pdo->assoc("select * from {$tbl['seo_config']} where tag_type='$tag_type' and page='$page'");

		$vals = array();
		$asql = '';
		foreach($names as $name) {
			$key = $page.'_'.$name;
			if($_FILES[$key]) { // 첨부파일
				if($_FILES[$key]['size'] < 1) {
					if($_POST['delfile1'] == 'Y') { // 선택 삭제
						deletePrdImage($data, 1, 1);
						$asql .= ", `$name`=''";
					}
					continue;
				}

				if($data[$key]) {
					deletePrdImage($data, 1, 1);
					$asql .= ", `$name`=''";
				}

				$vals['updir'] = ($data['updir']) ? $data['updir'] : $updir;
				makeFullDir($vals['updir']);
				$upfile_name = uploadFile($_FILES[$key], md5($page.$name.microtime()), $updir, 'jpg|jpeg|gif|png');
				$vals[$name] = $upfile_name[0];
				if(!$asql) $asql .= ", updir='{$vals['updir']}'";
				$asql .= ", `$name`='$vals[$name]'";
			} else { // 일반 변수
				$vals[$name] = addslashes(trim($_POST[$key]));
			}

		}

		if($data['no'] > 0) {
			$r = $pdo->query("
				update {$tbl['seo_config']} set
					title='{$vals['title']}',
					description='{$vals['description']}',
					keyword='{$vals['keyword']}',
					image_use='{$vals['image_use']}',
					edt_date=now()
					$asql
				where no='{$data['no']}'
			");
		} else {
			if(!$val) continue;
			$r = $pdo->query("
				insert into {$tbl['seo_config']}
					(tag_type, page, title, description, keyword, image_use, updir, upfile1, admin_id, edt_date, reg_date)
					values
					('$tag_type', '$page', '{$vals['title']}', '{$vals['description']}', '{$vals['keyword']}', '{$vals['image_use']}', '{$vals['updir']}', '{$vals['upfile1']}', '$admin_id', now(), now())
			");
		}
		if($r == false) {
			alert(php2java($pdo->getError()));
			$pdo->query("rollback;");
			msg("데이터 처리중 오류가 발생하였습니다.");
		}
	}

	$pdo->query("commit;");

	msg('태그 설정이 완료되었습니다.', 'reload', 'parent');

?>