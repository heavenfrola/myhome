<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

    include_once __ENGINE_DIR__.'/_engine/include/shop_detail.lib.php';

	checkBasic();

	function checkMileInit($milage = null) {
		global $cfg,$tbl;

		if(!$milage) $milage = numberOnly($cfg['milage_review']);
		if(!$milage) {
			msg("상품후기 적립금이 설정되어 있지 않습니다", "reload", "parent");
		}
	}

	$mode = $_POST['mode'];
	$exec = $_POST['exec'];
	$cno = numberOnly($_POST['cno']);
	$check_pno = numberOnly($_POST['check_pno']);

	if($exec == "comment_Modify") {
	    header('Content-type:application/json; charset='._BASE_CHARSET_.';');
		$result = $pdo->row("select `content` from `$tbl[review_comment]` where `no`='$cno'");
		exit(json_encode(array('result'=>$result, 'cno'=>$cno)));
	}

	if($mode == "single") {
		if($exec == "edit") {
			$no = numberOnly($_POST['no']);
			$pno = numberOnly($_POST['pno']);
			$cate = addslashes(trim($_POST['cate']));
			$title = addslashes(del_html($_POST['title']));
			$name = addslashes(del_html($_POST['name']));
			$rev_pt = numberOnly($_POST['rev_pt']);
			$content = addslashes(trim($_POST['content']));
			$notice = ($_POST['notice'] == 'Y') ? 'Y' : 'N';
			$answer = addslashes($_POST['answer']);
			$mng_no = addslashes($_POST['mng_no']);

			$data = $pdo->assoc("select * from `$tbl[review]` where `no` = '$no'");
   			$commment_mng = $pdo->assoc("select `name`, `member_id` from `$tbl[member]` where `no`='$mng_no'");

			$updir = $data['updir'];
			for($i = 1; $i <= 2; $i++) {
				$file = $_FILES['upfile'.$i];

				if($updir && ($_POST['delfile'.$i] == "Y" || $file['size'] > 0)) {
					deletePrdImage($data, $i, $i);
					$asql .= " , `upfile{$i}`=''";
				}

				if($file['size'] > 0) {
					if(!$updir) {
						$updir = $dir['upload']."/".$dir['review']."/".date("Ym",$now)."/".date("d",$data['reg_date']);
						makeFullDir($updir);
						$asql .= " , `updir`='$updir'";
					}

					$up_filename = md5($i+time());
					$up_info = uploadFile($file, $up_filename, $updir);
					$up_filename = $_up_filename[$i] = $up_info[0];
					$asql .= ", `upfile{$i}`='$up_filename'";
				}
			}

			if($no) {
				$ems = "수정되었습니다";
				$asql .= ", `cate`='$cate'";
				$pdo->query("update `$tbl[review]` set `pno`='$pno', `title`='$title', `content`='$content', `name`='$name', `rev_pt`='$rev_pt' $asql where `no`='$no'");

                // 상품 후기 이미지 수 업데이트
                if ($scfg->comp('use_review_image_cnt', 'Y') == true) {
                    reviewImageCount(
                        $pdo->assoc("select no, upfile1, upfile2, content from {$tbl['review']} where no='$no'")
                    );
                }
			}
			else {
				if(!$notice) {
					msg("공지사항이 아닙니다");
				}
				$ems = "공지사항이 등록되었습니다";
				$pdo->query("insert into `$tbl[review]` (`title`,`content`,`notice`,`reg_date`,`stat`,`member_id`,`cate`) values ('$title', '$content','Y','$now','2','','$cate')");

				$review_no = $no = $pdo->lastInsertId();
				$asql = preg_replace("/^,/","",trim($asql));
				if($asql && $review_no) $pdo->query("update `$tbl[review]` set $asql where `no`='$review_no'");
			}

            $editor_file = new EditorFile();
            $editor_file->lock('product_review', $no, $_POST['editor_code']);

			if($pno) {
				setRevPt($pno);
			}
			if($cno) {
				$pdo->query("update `$tbl[review_comment]` set `content`='$answer', `name`='$commment_mng[name]' where `no`='$cno'");
			}
			if(!$cno && $answer) {
				$pdo->query("insert into `$tbl[review_comment]` (`ref`, `content`, `name`, `member_id`, `member_no`, `ip`, `reg_date`) values ('$no', '$answer', '$commment_mng[name]', '$commment_mng[member_id]', '$mng_no', '$_SERVER[REMOTE_ADDR]', '$now')");
				$pdo->query("update $tbl[review] set total_comment=total_comment+1 where no='$no'");
			}
			msg($ems,"reload","parent");
		}
		elseif($exec == "milage") {
			$no = numberOnly($_POST['no']);
			$milage_review = numberOnly($_POST['milage_review']);
			$milage_review_image = numberOnly($_POST['milage_review_image']);

			checkMileInit($milage_review);
			include_once $engine_dir."/_engine/include/milage.lib.php";
			$r = reviewMilage($no, '', $milage_review, $milage_review_image);
			if($r) {
				msg("적립금이 지급되었습니다","reload","parent");
			}
			else {
				msg("이미 적립금이 지급되었거나 비회원 또는 대기상태의 상품후기글입니다.","reload","parent");
			}
		}
		elseif($exec == "comment_edit") {
			$no = numberOnly($_POST['no']);
			$name = addslashes(trim($_POST['name']));
			$content = addslashes(trim($_POST['content']));

			$pdo->query("update `$tbl[review_comment]` set `content`='$content', `name`='$name' where `no`='$no'");
			msg("댓글이 수정되었습니다", "reload", "parent");
		}
	}

	if($exec == "milage") {
		checkMileInit();
		$total = 0;
		include_once $engine_dir."/_engine/include/milage.lib.php";
		foreach($check_pno as $key=>$val) {
			if(reviewMilage($val)) {
				$total++;
			}
		}
		msg($total."명의 회원에게 적립금을 지급했습니다", "reload", "parent");
	}

    // 상품 후기 이미지 포토리뷰 카운팅 마이그레이션
    if ($exec == 'migration') {
        $pdo->query("
            alter table {$tbl['review']}
                add column image_cnt tinyint not null default '0' comment '게시중인 이미지 개수',
                add index image_cnt (image_cnt)
        ");
        $res = $pdo->query("select no, upfile1, upfile2, content from {$tbl['review']}");
        foreach ($res as $data) {
            $image_cnt = reviewImageCount($data);
        }

        $scfg->import(array(
            'use_review_image_cnt' => 'Y'
        ));
        exit;
    }

	$total = count($check_pno);
	if($total < 1) msg('상품후기를 선택해주세요.');

	if($exec == "delete") {
		include_once $engine_dir."/_engine/include/milage.lib.php";
		foreach($check_pno as $key=>$val) {
			$data = $pdo->assoc("select * from $tbl[review] where no='$val'");

			if($cfg['use_trash_rev'] == 'Y') { // 휴지통
				$ret = insertTrashBox($data, array(
					'tbl' => $tbl['review'],
					'title' => $data['title'],
					'name' => $data['name'],
					'reg_date' => $data['reg_date'],
					'del_qry' => "delete from $tbl[review] where no='$data[no]'",
				));
			} else { // 일반삭제
				reviewMilage($val,1);
				$data = $pdo->assoc("select no, updir, upfile1, upfile2 from `$tbl[review]` where `no`='$val' limit 1");
				deletePrdImage($data, 1, 2);

				$r = $pdo->query("delete from `$tbl[review]` where `no` in (".implode(",",$check_pno).")");
				if($r) {
					$pdo->query("delete from {$tbl['review_comment']} where `ref` in (".implode(",",$check_pno).")");
					$pdo->query("delete from {$tbl['review_recommend']} where rno in (".implode(",",$check_pno).")");

                    $editor_file = new EditorFile();
                    $editor_file->removeId('product_review', $data['no']);
				}
			}

			if($data['pno'] > 0) {
				setRevPt($data['pno']);
			}
		}
		$ems = "상품후기글이 삭제되었습니다.";
	}
	elseif($exec == "comment_delete") {
        if($_POST['cno']) {
            $check_pno = array(
                'cno' => $_POST['cno']
            );
            $msg = "댓글이 삭제되었습니다.";
        } else {
            $msg = $total."개의 댓글을 삭제하였습니다.";
        }
        foreach($check_pno as $key=>$val){
            $ref = $pdo->row("select `ref` from `$tbl[review_comment]` where `no`='$val'");
            $r = $pdo->query("delete from `$tbl[review_comment]` where `no`='$val'");
            if($r) $pdo->query("update `$tbl[review]` set `total_comment`=(select count(*) from `$tbl[review_comment]` where `ref`='$ref') where `no`='$ref'");
        }
		if($_POST['ajax']) {
			header('Content-type:application/json; charset='._BASE_CHARSET_.';');
			exit(json_encode(array('result'=>'success', 'message'=>'댓글이 삭제되었습니다.')));
		}
		msg($msg, "reload", "parent");
	}
	elseif($exec == 'remove_attach') {
		header('Content-type:application/json; charset='._BASE_CHARSET_.';');

		$no = numberOnly($_POST['no']);
		$key = addslashes($_POST['key']);

		if($_SESSION['adm_view'] != 'rev@'.$no) {
			exit(json_encode(array('result'=>'faild', 'message'=>'파일을 삭제할 권한이 없습니다.')));
		}

		$data = $pdo->assoc("select no, updir, $key from $tbl[review] where no='$no'");
		if(!$data[$key]) {
			exit(json_encode(array('result'=>'faild', 'message'=>'상품후기 또는 첨부파일이 존재하지 않습니다.')));
		}

		deletePrdImage($data, numberOnly($key));
		$pdo->query("update $tbl[review] set $key='' where no='$no'");

		exit(json_encode(array('result'=>'success')));
	}
	else {
		$ext = numberOnly($_POST['ext']);

		checkBlank($ext,"필수값을 입력해주세요.");

		$pno = array();
		$res = $pdo->iterator("select pno from {$tbl['review']} where no in (".implode(",",$check_pno).") and stat!='$ext'");
        foreach ($res as $data) {
			$pno[] = $data['pno'];
		}

		$r = $pdo->query("update `$tbl[review]` set `stat`='$ext' where `no` in (".implode(",",$check_pno).") and `stat`!='$ext'");
		$ems = "상품후기글의 상태를 변경하였습니다";
		if($cfg['milage_review_auto'] == "Y" && $r && $ext == 2){ // 2007-10-31 : 상품평 자동지급 - Han
			include_once $engine_dir."/_engine/include/milage.lib.php";
			foreach($check_pno as $key=>$val) {
				reviewMilage($val);
			}
		}
	}

	array_unique($pno);
	foreach($pno as $key=>$val) {
		setRevPt($val);
	}

	msg($total."개의 ".$ems, "reload", "parent");

?>