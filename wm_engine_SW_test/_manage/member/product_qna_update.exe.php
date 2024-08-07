<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품Q&A 처리
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;
	use Wing\API\Kakao\KakaoTalkPay;
    use Wing\common\EditorFile;
    use Wing\common\WorkLog;

	$exec = $_POST['exec'];
	$notice = addslashes($_POST['notice']);
	$no = numberOnly($_POST['no']);

	if($exec=="edit") {
		if($notice=="N") {
			checkBlank($no,'필수값(1)을 입력해주세요.');
		}

		if($no) {
			$data=get_info($tbl[qna],"no",$no);
			checkBlank($data[no],'필수값(2)을 입력해주세요.');
		}

		$updir = $data['updir'];
		for($i = 1; $i <= 4; $i++) {
			$file = $_FILES['upfile'.$i];

			if($updir && ($_POST['delfile'.$i]=="Y" || $file['size'] > 0)) {
				deletePrdImage($data, $i, $i);
				$asql .= " , `upfile{$i}`=''";
			}

			if($file['size'] > 0) {
				$img = getimagesize($file['tmp_name']);

				if(!$updir) {
					$updir=$dir['upload']."/".$dir['qna']."/".date("Ym",$now)."/".date("d",$data['reg_date']);
					makeFullDir($updir);
					$asql .= " , `updir`='$updir'";
				}

				$up_filename = md5($i+time()); // 새파일명
				$up_info=uploadFile($file, $up_filename, $updir);
				$up_filename = $_up_filename[$i] = $up_info[0];
				$_ori_filename[$i] = $file['name'];
				$asql .= " , `upfile{$i}`='$up_filename',`ori_file{$i}`='{$file['name']}'";
			}
		}

		if($_up_filename[3] || $_up_filename[4]) {
			addField($tbl['qna'], 'upfile3', "varchar(255) not null default ''");
			addField($tbl['qna'], 'upfile4', "varchar(255) not null default ''");
			addField($tbl['qna'], 'ori_file3', "varchar(255) not null default ''");
			addField($tbl['qna'], 'ori_file4', "varchar(255) not null default ''");
		}

		$name = addslashes($_POST['name']);
		$title = addslashes($_POST['title']);
		$content = addslashes($_POST['content']);
        if ($scfg->comp('product_qna_use_editor', 'Y')) {
            $answer = addslashes($_POST['answer']);
        } else {
            $answer = addslashes(del_html($_POST['answer']));
        }
		$mng_memo = addslashes($_POST['mng_memo']);
		$cate = addslashes($_POST['cate']);
		$sms = addslashes($_POST['sms']);
		$email = addslashes($_POST['email']);
		$secret = addslashes($_POST['secret']);
		$answer_check = strip_tags($answer, '<img>');
		$answer_check2 = strip_tags($data['answer'], '<img>');

		if(!$data['answer_date'] && ($answer_check || $_FILES['upfile3']['name'] || $_FILES['upfile4']['name'])) {
			$asql .= " ,`answer_date` = '$now', answer_id='$admin[admin_id]', answer_ok='Y'";
		}
		if($answer_check2 != $answer_check) {
			$asql .= " ,answer_id='$admin[admin_id]'";
		}

		if($sms && ($answer_check || $_FILES['upfile3']['name'] || $_FILES['upfile4']['name'])) {
			include $engine_dir."/_engine/sms/sms_module.php";
			$sms_replace[name]=$data[name];
			SMS_send_case(8,$sms);
		}

		if($email && ($answer_check || $_FILES['upfile3']['name'] || $_FILES['upfile4']['name'])) {
			$mail_case = 9;
			include $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent($mail_case, $data['name'], $email);
		}

		if(!$secret) {
			$secret="N";
		}

        if (strpos($data['external_id'], 'talkpay') === 0) {
            if(mb_strlen($answer, _BASE_CHARSET_) > 1000) {
                alert("카카오페이 구매 답변은 1000자 이상 입력할 수 없습니다.");
            }

            $talkpay = new KakaoTalkPay($scfg);
            $ret = $talkpay->answer(stripslashes($answer), $data['external_id'], $data['external_answer_id']);

            if ($ret->answerId) $answer_id = $ret->answerId;
            else {
                msg(php2java($ret->message));
            }
            $asql .= ", external_answer_id='$answer_id'";
        }

		if($notice=="Y" && !$no) {
			$sql="INSERT INTO `$tbl[qna]` ( `title` , `content` , `reg_date` , `notice` , `cate`, updir, upfile1, upfile2, ori_file1, ori_file2) VALUES ('$title', '$content', '$now', 'Y', '$cate', '$updir', '$_up_filename[1]', '$_up_filename[2]', '$_ori_filename[1]', '$_ori_filename[2]')";
		}
		else {
			$asql.=",`cate`='$cate'";
			$sql="update `$tbl[qna]` set `title`='$title', `name`='$name', `content`='$content', `answer`='$answer', `mng_memo`='$mng_memo', `secret`='$secret' $asql where `no`='$no'";
		}

		$pdo->query($sql);
        if (empty($no) == true) {
            $no = $pdo->lastInsertId();
        }

        if ($data['answer'] && empty($_POST['no']) == false) {
            $log = new WorkLog();
            $log->createLog(
                $tbl['qna'],
                (int) $no,
                'title',
                $data,
                $pdo->assoc("select * from {$tbl['qna']} where no=?", array($no))
            );
        }

        $editor_file = new EditorFile();
        $editor_file->lock('product_qna', $no, $_POST['editor_code']);

		if($data['checkout_no'] > 0) {
			$checkout = new CheckoutApi4();
			$checkout->setInquiryAnswer($data['checkout_no'], strip_tags(str_replace('&', '&amp;', $answer)), $data['checkout_ans_no']);
		}

		if($data['smartstore_no'] > 0) {
			if(getSmartStoreState() == true) {
                $CommerceAPI = new CommerceAPI();
                $CommerceAPI->contentsQnasPut($data['smartstore_no'], $answer);
			}
		}

		if($data['talkstore_qnaId']) {
			$kts = new KakaoTalkStore();
			$kts->setStoreQnaAnswer($data['talkstore_qnaId'], strip_tags(stripslashes($answer)));
		}

		msg("","popup");
	}
	elseif($exec=="delete") {
		$x=0;
		$check_pno = numberOnly($_POST['check_pno']);
		foreach($check_pno as $key=>$val) {
			$data = $pdo->assoc("select * from $tbl[qna] where no='$val'");
			if(!$data['no']) continue;

			// 휴지통
			if($cfg['use_trash_qna'] == 'Y') { // 휴지통
				$ret = insertTrashBox($data, array(
					'tbl' => $tbl['qna'],
					'title' => $data['title'],
					'name' => $data['name'],
					'reg_date' => $data['reg_date'],
					'del_qry' => "delete from $tbl[qna] where no='$data[no]'",
				));
			} else { // 일반삭제
				$sql="delete from `$tbl[qna]` where `no`='$val'";
				$pdo->query($sql);

                $editor_file = new EditorFile();
                $editor_file->removeId('product_qna', $data['no']);
			}

			if($data['pno'] > 0) {
				$tmp = $pdo->row("select count(*) from {$tbl['qna']} where pno='{$data['pno']}'");
				$pdo->query("update {$tbl['product']} set qna_cnt='$tmp' where `no`='{$data['pno']}'");
			}

			$x++;
		}
		msg("$x 개의 질문을 삭제했습니다","reload","parent");
	}
	elseif($exec == 'remove_attach') {
		header('Content-type:application/json; charset='._BASE_CHARSET_.';');

		$no = numberOnly($_POST['no']);
		$key = addslashes($_POST['key']);

		if($_SESSION['adm_view'] != 'qna@'.$no) {
			exit(json_encode(array('result'=>'faild', 'message'=>'파일을 삭제할 권한이 없습니다.')));
		}

		$data = $pdo->assoc("select no, updir, $key from $tbl[qna] where no='$no'");
		if(!$data[$key]) {
			exit(json_encode(array('result'=>'faild', 'message'=>'상품문의 또는 첨부파일이 존재하지 않습니다.')));
		}

		deletePrdImage($data, numberOnly($key));
		$pdo->query("update $tbl[qna] set $key='' where no='$no'");

		exit(json_encode(array('result'=>'success')));
	}

?>