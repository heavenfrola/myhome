<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 메모 입력 처리
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_POST['no']);
	$meno = numberOnly($_POST['meno']);
	$type = ($_POST['memo_type']) ? numberOnly($_POST['memo_type']) : numberOnly($_POST['type']);
	$content = addslashes(trim($_POST['content']));
	$exec = $_POST['exec'];

	$ono = addslashes($_POST['ono']);
	$mid = addslashes($_POST['mid']);
	$pno = addslashes($_POST['pno']);

	if($exec == 'delete') {
		$data = $pdo->assoc("select type, ono, admin_no, admin_id from $tbl[order_memo] where no='$no'");

		if($data['admin_no'] == 0 && $data['admin_id'] == 'system') exit('삭제 권한이 없습니다.');
		if($admin['level'] > 2 && $data['admin_id'] != $admin['admin_id']) exit('삭제 권한이 없습니다.');
		$pdo->query("delete from $tbl[order_memo] where no='$no'");

		if($data['type'] == 1) {
			$mm = $pdo->row("select count(*) from `$tbl[order_memo]` where `ono`='$data[ono]'");
			$pdo->query("update `$tbl[order]` set `memo_cnt`=$mm where `ono`='$data[ono]'");
		}

        // 첨부파일 삭제
        $neko_id = 'memo_'.$data['type'].'_'.$no;
        $files = $pdo->iterator("select * from {$tbl['neko']} where neko_id='$neko_id'");
        foreach ($files as $file) {
            deleteAttachFile($file['updir'], $file['filename']);
        }

		exit('OK');
	}

	if($exec == 'toggle') {
		$no = numberOnly($_POST['no']);
		$data = $pdo->assoc("select importance, admin_no, admin_id from $tbl[order_memo] where no='$no'");
		$importance = ($data['importance'] == 1) ? 2 : 1;

		if($data['admin_no'] == 0 && $data['admin_id'] == 'system') exit('권한이 없습니다.');
		if($admin['level'] > 2 && $data['admin_id'] != $admin['admin_id']) exit('권한이 없습니다.');

		$pdo->query("update $tbl[order_memo] set importance='$importance' where no='$no'");

		exit('OK');
	}

    if ($exec == 'removeAttach') {
		$data = $pdo->assoc("select importance, admin_no, admin_id from $tbl[order_memo] where no='$no'");
		if($data['admin_no'] == 0 && $data['admin_id'] == 'system') exit('권한이 없습니다.');
		if($admin['level'] > 2 && $data['admin_id'] != $admin['admin_id']) exit('권한이 없습니다.');

        $file = $pdo->assoc("select * from {$tbl['neko']} where no=?", array($_POST['file_no']));
        if ($file['no']) {
            deleteAttachFile($file['updir'], $file['filename']);
            $pdo->query("delete from {$tbl['neko']} where no=?", array($_POST['file_no']));
        } else {
            exit('파일이 존재하지 않습니다.');
        }

        exit('OK');
    }

    $file_cnt = 0;
    if (is_array($_FILES['upfile']['name']) == true) { // 멀티업로드 대응
        $upfile = $_FILES['upfile'];
        $tmp = array();
        foreach ($_FILES['upfile']['name'] as $key => $val) {
            if ($upfile['size'][$key] < 1) continue;

            $tmp[] = array(
                'name' => $upfile['name'][$key],
                'type' => $upfile['type'][$key],
                'tmp_name' => $upfile['tmp_name'][$key],
                'error' => $upfile['error'][$key],
                'size' => $upfile['size'][$key],
            );
            $file_cnt++;
        }
        $_FILES = $tmp;
    }
    if ($file_cnt == 0 && $meno > 0) {
        $file_cnt = $pdo->row("select count(*) from {$tbl['neko']} where neko_gr=? and neko_id=?", array(
            'memo'.$type, 'memo_'.$type.'_'.$meno
        ));
    }

	if($file_cnt == 0 && empty($content) == true) {
		alert('메모 내용을 입력해주세요.');
		return false;
	}

	addField($tbl['order_memo'], 'importance', 'char(1) not null default "1"');
	$importance = ($_POST['importance'] == 2) ? 2 : 1;

	if($meno > 0) {
		$data = $pdo->assoc("select admin_no, admin_id from $tbl[order_memo] where no='$meno'");
		if($data['admin_id'] == 'system') msg('수정 권한이 없습니다.');
		if($admin['level'] > 2 && $data['admin_id'] != $admin['admin_id']) {
			alert('수정 권한이 없습니다.');
			exit;
		}

		$pdo->query("update $tbl[order_memo] set content='$content', importance='$importance' where no='$meno'");
	} else {
		if($ono) $mid = $ono;
		elseif($pno) $mid = $pno;
		$pdo->query("insert into $tbl[order_memo] (admin_no, admin_id, ono, content, type, importance, reg_date) values ('$admin[no]', '$admin[admin_id]', '$mid', '$content', '$type', '$importance', '$now')");
        $meno = $pdo->lastInsertId();

		if($type == 1) {
			if(fieldExist($tbl['order'], 'memo_cnt')) {
				$mm = $pdo->row("select count(*) from `$tbl[order_memo]` where `ono`='$mid'");
				$pdo->query("update `$tbl[order]` set `memo_cnt`=$mm where `ono`='$mid'");
			}
		}
	}

    // 첨부파일
    $updir = $dir['upload'].'/editor_attach/memo'.$type;
    makeFullDir($updir);
    addField($tbl['neko'], 'ofilename', 'varchar(200) not null default "" after filename');
    foreach ($_FILES as $key => $file) {
        if ($file['size'] < 1) continue;

        $img = getimagesize($file['tmp_name']);
        $up_info = uploadFile($file, md5($file['name'].$now.rand(0,99999).$key), $updir, 'png|gif|jpg|jpeg|webp|xls|xlsx|pdf|ppt|pptx|doc|docx');

        $pdo->query("
            insert into {$tbl['neko']}
            (neko_id, neko_gr, updir, filename, ofilename, size, width, height, `lock`, regdate)
            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", array(
            'memo_'.$type.'_'.$meno, 'memo'.$type, $updir, $up_info[0], $file['name'], $file['size'], $img[0], $img[1], 'Y', $now
        ));
    }

	javac("parent.reloadMemo()");

?>