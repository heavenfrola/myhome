<?PHP

    use Wing\common\EditorFile;

	include_once $engine_dir.'/_engine/include/file.lib.php';

	$exec = $_POST['exec'];
	$no = $_POST['no'];
	$tblname = addslashes($_POST['tblname']);

	if($exec == 'truncate') {
		$no = array();
		$tmpres = $pdo->iterator("select no from $tbl[common_trashbox] where tblname='$tblname'");
        foreach ($tmpres as $tmpdata) {
			$no[] = $tmpdata['no'];
		}
	}

	foreach($no as $del_no) {
		$del_no = numberOnly($del_no);
		$tmp = $pdo->assoc("select no, tblname, data from $tbl[common_trashbox] where no='$del_no'");
		if(!$tmp['no']) continue;
		$data = unserialize(stripslashes($tmp['data']));

		switch($exec) {
			case 'restore' :
				$makequery1 = $makequery2 = '';
				foreach($data as $key => $val) {
					if($makequery1) $makequery1 .= ',';
					if($makequery2) $makequery2 .= ',';
					$makequery1 .= "`$key`";
					$makequery2 .= "'".addslashes($val)."'";
				}
				if($pdo->query("insert into $tmp[tblname] ($makequery1) values ($makequery2)")) {
					$pdo->query("delete from $tbl[common_trashbox] where no='$del_no'");

					if($data['pno'] > 0) {
						if($tmp['tblname'] == 'wm_qna') {
							$tmp = $pdo->row("select count(*) from {$tbl['qna']} where pno='{$data['pno']}'");
							$pdo->query("update {$tbl['product']} set qna_cnt='$tmp' where `no`='{$data['pno']}'");
						} else if($tmp['tblname'] == 'wm_review') {
							setRevPt($data['pno']);
						}
					}
				}

				if($tmp['tblname'] == $tbl['review']) {
					include_once $engine_dir.'/_engine/include/milage.lib.php';
					reviewMilage($data['no']);
				}
			break;
			case 'truncate' :
			case 'remove' :
				$pdo->query("delete from $tbl[common_trashbox] where no='$del_no'");

				// 첨부이미지 삭제
				if($tmp['tblname'] == 'mari_board') {
					$data['updir'] = 'board/'.$data['up_dir'];
				}
				deletePrdImage($data, 1, 3);

				// 부속 데이터 삭제
				switch($tmp['tblname']) {
					case 'mari_board' :
                        $editor_file = new EditorFile();
                        $editor_file->removeId($data['db'], $data['no']);

						$pdo->query("delete from mari_comment where ref='$data[no]'");
					break;
					case 'wm_review' :
                        $editor_file = new EditorFile();
                        $editor_file->removeId('product_review', $data['no']);

						$pdo->query("delete from $tbl[review_comment] where ref='$data[no]'");
					break;
					case 'wm_qna' :
                        $editor_file = new EditorFile();
                        $editor_file->removeId('product_qna', $data['no']);
					break;
				}
			break;
		}
	}
	msg('', 'reload', 'parent');

?>