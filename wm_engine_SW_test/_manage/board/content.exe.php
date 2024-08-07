<?PHP

    use Wing\common\EditorFile;
    use Wing\common\WorkLog;

	include_once $engine_dir.'/_engine/include/file.lib.php';
	include_once $engine_dir.'/board/include/lib.php';

	$ii=0;
	$mng = numberOnly($_POST['mng']);
	$check_pno = numberOnly($_POST['check_pno']);
	$exec = $_POST['exec'];

	if($exec=="delete") {
		if($mng==2) {
			$table="mari_comment";
			$fld="댓글";
		}
		else {
			$table="mari_board";
			$fld="게시물";
		}
		foreach($check_pno as $key=>$val) {
			$data = $pdo->assoc("select * from $table where no='$val'");
			if(!$data[no]) {
				continue;
			}
			if($mng==2) {
				$sql="delete from `mari_comment` where `no`='$data[no]'";
				$pdo->query($sql);
				$sql="update `mari_board` set `total_comment`=`total_comment`-1 where `ref`='$data[ref]'";
				$pdo->query($sql);
			}
			else {
				if($cfg['use_trash_bbs'] == 'Y') { // 휴지통
					$ret = insertTrashBox($data, array(
						'tbl' => 'mari_board',
						'db' => $data['db'],
						'title' => $data['title'],
						'name' => $data['name'],
						'reg_date' => $data['reg_date'],
						'del_qry' => "delete from mari_board where no='$data[no]'",
					));
				} else { // 일반 삭제
					if(!$data[up_dir]) {
						$data[up_dir]="/_data/".$data[db]."/".$data[no]."/";
					}

					for($i = 1; $i <= 2; $i++) {
						deleteAttachFile('board/'.$data['up_dir'], $data['upfile'.$i]);
					}

					$pdo->query("delete from `mari_comment` where `ref`='$data[no]'");

                    $editor_file = new EditorFile();
                    $editor_file->removeId($data['db'], $data['no']);
				}
				$pdo->query("delete from `mari_board` where `no`='$data[no]'");
			}

			$ii++;
		}

		$list_url = $_SESSION['list_url'];
		$reload=$list_url ? $list_url : 'reload';

		msg("$ii 개의 $fld"."을 삭제하였습니다",$reload,"parent");
	}
	elseif($exec=="move") {
		$next_db = addslashes(trim($_POST['next_db']));
		checkBlank($next_db,"이동할 게시판을 입력해주세요.");
		$w="";
		foreach($check_pno as $key=>$val) {
			$w.=" or `no`='$val'";
			$w2.=" or `ref`='$val'";
			$ii++;
		}
		$w=trim(substr($w,3));
		$w2=trim(substr($w2,3));
		checkBlank($w, "이동할 게시물을 선택해주세요.");
		$sql="update `mari_board` set `db`='$next_db' where $w";
		$pdo->query($sql);

		$sql="update `mari_comment` set `db`='$next_db' where $w2";
		$pdo->query($sql);

		msg("$ii 개의 게시물을 이동하였습니다","reload","parent");

	}
	elseif($exec=='copy') {
		$next_db = addslashes($_POST['next_db']);
		$check_pno = implode(',', $_POST['check_pno']);

		checkBlank($next_db, '복사할 게시판을 입력해주세요.');

		$cnt = $err = 0;
		$res = $pdo->iterator("select * from mari_board where no in ($check_pno)");
        foreach ($res as $data) {
			if($data['level'] > 0) {
				$err++;
				continue;
			}
			$asql1 = $asql2 = '';
			$no = $pdo->row("select max(no) from mari_board")+1;
			$data['no'] = $no;
			$auto_increment = isAutoIncrement($mari_set['mari_board']);
			if ($auto_increment > 0) {
				$no = $auto_increment;
			}
			$data['ref'] = $no;
			$data['db'] = $next_db;
			$data['reg_date'] = $now;
            $data['total_comment'] = 0;
            $data['vote_sum'] = $data['vote_cnt'] = $data['vote_avg'] = 0;
            $data['vote_members'] = '';

			foreach($data as $key => $val) {
				if(preg_match('/^upfile[0-9]+/', $key) && $val) {
					$new = md5(time().$key.$now).'.'.getExt($val);
					if ($_use['file_server'] == 'Y' && fsConFolder('board/'.$data['up_dir'])){
						$updir = $root_dir.'/_data/auto_thumb';
						fsFileDown('board/'.$data['up_dir'], $val, $updir);
						if (is_file($updir.'/'.$val)){
							fsUploadFile('board/'.$data['up_dir'], $updir.'/'.$val, $new);
							@unlink($updir.'/'.$val);
						}
					} else {
						copy($root_dir.'/board/'.$data['up_dir'].'/'.$val, $root_dir.'/board/'.$data['up_dir'].'/'.$new);
					}
					$val = $new;
				}
                if ($key == 'hit') continue;
				$asql1 .= ",`$key`";
				$asql2 .= ",'".addslashes($val)."'";
			}

			$asql1 = preg_replace('/^,/', '', $asql1);
			$asql2 = preg_replace('/^,/', '', $asql2);

			$pdo->query("insert into mari_board ($asql1) values ($asql2)");

			$cnt++;
		}

		if($err > 0) $errmsg = "\\n답변글 $err 개는 복사제외 되었습니다.";
		msg($cnt.'개의 게시물이 복사되었습니다.'.$errmsg, 'reload', 'parent');
	}
	elseif($exec=="modify") {
		$no = numberOnly($_POST['no']);
		$title = addslashes($_POST['title']);
		$content = addslashes($_POST['content']);
		$html = numberOnly($_POST['html']);
		$secret = ($_POST['secret'] == 'Y') ? 'Y' : 'N';
		$notice = ($_POST['notice'] == 'Y') ? 'Y' : 'N';
		$pno = addslashes(trim($_POST['pno']));
		$cate = numberOnly($_POST['cate']);
		$db = addslashes($_POST['db']);
		$name = addslashes($_POST['name']);
		$hidden = ($_POST['hidden'] == 'Y') ? 'Y' : 'N';
		$use_m_content = ($_POST['use_m_content'] == 'Y') ? 'Y' : 'N';
		$m_content = addslashes($_POST['m_content']);

		checkBlank($title, "제목을 입력해주세요.");
		if(!$content) msg('내용을 입력하세요.');

		$_tbl="mari_board";

		if(!$no) {
			checkBlank($db, '게시판을 선택해주세요.');
			checkBlank($name, '작성자를 입력해주세요.');

			$no = $pdo->row("select max(no) from $_tbl")+1;
			$pdo->query("insert into $_tbl (no, ref, db, name, reg_date) values ('$no', '$no', '$db', '$name', '$now')");
		}

		$data = $pdo->assoc("select * from `$_tbl` where `no`='$no' limit 1");
		if(!$data[no]) msg("이미 삭제된 글입니다", "back", "");

		$asql = '';
		for($i = 1; $i <= $cfg['board_add_temp']; $i++) {
			$tmpval = addslashes(trim($_POST['temp'.$i]));
			$asql .= ", `temp$i`='$tmpval'";
		}

		if(!$cfg['mari_board_m_content']) {
			addField($_tbl, 'hidden', 'enum("N", "Y") default "N"');
			addField($_tbl, 'use_m_content', 'enum("N", "Y") default "N"');
			addField($_tbl, 'm_content', 'text not null default ""');
			addField($_tbl, 'upfile3', 'varchar(50) after upfile2');
			addField($_tbl, 'upfile4', 'varchar(50) after upfile3');
			addField($_tbl, 'ori_upfile3', 'varchar(200) after ori_upfile2');
			addField($_tbl, 'ori_upfile4', 'varchar(200) after ori_upfile3');
			addField($_tbl, 'w3', 'int(5) after h2');
			addField($_tbl, 'h3', 'int(5) after w3');
			addField($_tbl, 'w4', 'int(5) after h3');
			addField($_tbl, 'h4', 'int(5) after w4');

			$pdo->query("insert into $tbl[config] (name, value) values ('mari_board_m_content', 'Y')");
		}

		$updir = $data['up_dir'];
		for($ii=1; $ii<=4; $ii++) {
			$chg_file="";
			if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
				deleteAttachFile('board/'.$data['up_dir'], $data['upfile'.$ii]);
				$up_filename=$ori_filename="";
				$chg_file=1;
			}
			if($_FILES['upfile'.$ii][tmp_name] && $_FILES['upfile'.$ii][size]) {

				$up_filename=md5($ii+time());

				if(!$updir) {
					$updir = '/_data/'.$data['db'].'/'.date('Ym/d/');
					makeFullDir('board/'.$updir);
					$asql.=", `up_dir`='$updir'";
				}

				list($w, $h) = getimagesize($_FILES['upfile'.$ii]['tmp_name']);
				if($w > 0 && $h > 0) {
					$asql .= ", w$ii='$w', h$ii='$h'";
				}

				$up_info=uploadFile($_FILES["upfile".$ii], $up_filename, 'board/'.$updir, $_bconfig['upfile_ext'], $_bconfig['upfile_size']);
				$ori_filename=$up_info[1];

				$up_filename=$up_info[0];
				$chg_file=1;
			}

			// 파일 수정 쿼리
			if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."', `ori_upfile".$ii."`='".$ori_filename."'";
		}

		if($cate) $asql .= ", `cate`='$cate'";
		if(count($_POST['reg_date']) > 0) {
			$reg_date = numberOnly($_POST['reg_date']);
			$reg_date = strtotime($reg_date[0].' '.$reg_date[1].':'.$reg_date[2].':'.$reg_date[3]);
			$asql .= ", reg_date='$reg_date'";
		}

		// 기간 설정
		if(isset($no_date) == false) {
			$start_date = $_POST['start_date'].' '.$_POST['start_time'].':'.$_POST['start_min'].':00';
			$end_date = $_POST['end_date'].' '.$_POST['end_time'].':'.$_POST['end_min'].':59';
			$n_status = addslashes($_POST['n_status']);
			$n_cate = addslashes($_POST['n_cate']);
			if($n_status == 'Category' && empty($n_cate) == true) {
				msg('기간 만료 후 이동될 분류를 선택해 주세요.');
			}
            if (!$_POST['start_date'] && $_POST['end_date']) msg('시작일을 입력해주세요.');
            if ($_POST['start_date'] && !$_POST['end_date']) msg('종료일을 입력해주세요.');
            if (strtotime($start_date) > strtotime($end_date)) msg('시작일이 종료일보다 큽니다.');
		} else {
			$start_date = $end_date = '0000-00-00 00:00:00';
			$n_status = '';
			$n_cate = 0;
		}
		if(isset($cfg['use_board_timer']) == false) {
			addField('mari_board', 'start_date', 'datetime not null');
			addField('mari_board', 'end_date', 'datetime not null');
			addField('mari_board', 'n_status', 'varchar(10) not null default ""');
			addField('mari_board', 'n_cate', 'int(10) not null default "0"');
			$pdo->query("
				ALTER TABLE mari_board
					ADD INDEX `start_date` (`start_date`),
					ADD INDEX `end_date` (`end_date`),
					ADD INDEX `n_status` (`n_status`);
			");
			$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('use_board_timer', 'Y', $now)");
		}
		$asql .= ", start_date='$start_date', end_date='$end_date', n_status='$n_status', n_cate='$n_cate'";

		$sql="update `$_tbl` set `title`='$title', `content`='$content', `html`='$html', `notice`='$notice', `secret`='$secret', hidden='$hidden', `pno`='$pno', use_m_content='$use_m_content', m_content='$m_content' $asql where `no`='$no' limit 1";
		$gURL="./?body=intra@board&mode=view".$QueryString;

		$r = $pdo->query($sql);
		if($r == false) {
			alert(php2java($pdo->getError()));
			exit;
		}

        if (empty($_POST['no']) == false) {
            $log = new WorkLog();
            $log->createLog(
                'mari_board',
                (int) $no,
                'title',
                $data,
                $pdo->assoc("select * from mari_board where no=?", array($no)),
                array('ori_upfile1', 'w1', 'h1', 'up_dir')
            );
        }

        $editor_file = new EditorFile();
        $editor_file->lock($db, $no, $_POST['editor_code']);

		$list_url = $_SESSION['list_url'];
		if(!$listURL) $listURL="?body=board@content_list";
		msg("등록되었습니다", $list_url, "parent");
	} elseif($exec == 'getRefProduct') {
		printAjaxHeader();

		if($data && $data['pno']) $_POST['pno'] = $data['pno'];
		if(!$_POST['pno']) return;
		$pno = explode(',', preg_replace('/[^0-9,]/', '', $_POST['pno']));
		$result = '';
		foreach($pno as $_pno) {
			$prd = $pdo->assoc("select no, hash, name, updir, upfile3, w3, h3, sell_prc from $tbl[product] where no='$_pno'");
			$imgurl = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile3'];
			list($w, $h, $size_str) = setImageSize($prd['w3'], $prd['h3'], 60, 60);
			$prd['sell_prc'] = number_format($prd['sell_prc']);
			$result .= "
			<li>
				<div class='box_setup'>
					<div class='thumb'><a href='$root_url/shop/detail.php?pno=$prd[hash]' target='_blank'><img src='$imgurl' $size_str /></a></div>
					<p class='title'><a href='?body=product@product_register&pno=$prd[no]' target='_blank'>$prd[name]</a></p>
					<p>$prd[sell_prc] 원</p>
					<span class='box_btn_s btnp'><a href='#' onclick='refProductRemove($prd[no]); return false;'>삭제</a></span>
				</div>
			</li>
			";
		}
		if($data) {
			echo $result;
			return;
		}
		exit($result);
	} else if($exec == 'getConfig') {
		$db = addslashes(trim($_POST['db']));
		$config = $pdo->assoc("select * from mari_config where db='$db'");

		header('Content-type:application/json; charset=utf-8;');
		exit(json_encode($config));
	}

?>