<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  1:1고객상담 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;
    use Wing\common\WorkLog;

	checkBasic();

	addField($tbl['cs'], 'updir', 'varchar(50) not null default ""');
	addField($tbl['cs'], 'upfile1', 'varchar(100) not null default ""');
	addField($tbl['cs'], 'upfile2', 'varchar(100) not null default ""');

	$exec = $_POST['exec'];

	if($exec=="reply") {
		$no = numberOnly($_POST['no']);
		$answer = addslashes(trim($_POST['answer']));
		$sms = addslashes($_POST['sms']);
		$email = addslashes($_POST['email']);
		$chg_date = $_POST['chg_date'];
		$content = addslashes(trim($_POST['content']));
		$mng_memo = addslashes($_POST['mng_memo']);
		$asql = '';

		checkBlank($no,'문의글이 존재하지 않습니다.');
		if(empty($answer) == true) msg('답변을 입력해주세요.');

		$data = get_info($tbl['cs'], 'no', $no);
		checkBlank($data['no'], '문의글이 존재하지 않습니다.');

		if($chg_date == 'Y' || !$data['reply_date']) {
			$asql .= ", `reply_date`='$now', reply_ok='Y', reply_id='$admin[admin_id]'";
		}

		$updir = $data['updir'];
		for($i = 1; $i <= 2; $i++) {
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
				$up_info=uploadFile($file, $up_filename, $updir, "jpg|jpeg|gif|png|xls|xlsx|hwp|doc|pdf");
				$up_filename = $_up_filename[$i] = $up_info[0];
				$_ori_filename[$i] = $file['name'];
				$asql .= " , `upfile{$i}`='$up_filename'";
			}
		}

		if($sms && strip_tags($answer)) {
			$sms_replace['name'] = $data['name'];
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			SMS_send_case(8, $sms);
		}

		if($email && strip_tags($answer)) {
			if($data['ono']) {
				$ord = $pdo->assoc("select buyer_name from $tbl[order] where ono='$data[ono]'");
				$name = stripslashes($ord['buyer_name']);
			}
			if($data['name']) $name = stripslashes($data['name']);
			if(!$name) $name = 'guest';
            $title = stripslashes($data['title']);
            $content = stripslashes($data['content']);

			$mail_case = 9;
			include $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent($mail_case, $data['name'], $email, $cfg['admin_email']);
		}

		$sql="update `$tbl[cs]` set `reply`='$answer', mng_memo='$mng_memo' $asql where `no`='$no'";
		$pdo->query($sql);

        if ($data['answer']) {
            $log = new WorkLog();
            $log->createLog(
                $tbl['cs'],
                (int) $no,
                'title',
                $data,
                $pdo->assoc("select * from {$tbl['cs']} where no=?", array($no))
            );
        }

        $editor_file = new EditorFile();
        $editor_file->lock('counsel_answer', $no, $_POST['editor_code']);

		msg("","popup");
	}
	elseif($exec=="delete") {
        $editor_file = new EditorFile();

		$check_pno = numberOnly($_POST['check_pno']);
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$w1.=" or `no`='$no'";

            $editor_file->removeId('counsel_answer', $no);
		}
		$w1=substr($w1,3);
		if($w1) $pdo->query("delete from `$tbl[cs]` where $w1");

		msg($total." 개의 상담 내역이 성공적으로 삭제되었습니다","reload","parent");
	}
	elseif($exec == 'remove_attach') {
		header('Content-type:application/json; charset='._BASE_CHARSET_.';');

		$no = numberOnly($_POST['no']);
		$key = addslashes($_POST['key']);

		if($_SESSION['adm_view'] != 'cs@'.$no) {
			exit(json_encode(array('result'=>'faild', 'message'=>'파일을 삭제할 권한이 없습니다.')));
		}

		$data = $pdo->assoc("select no, updir, $key from $tbl[cs] where no='$no'");
		if(!$data[$key]) {
			exit(json_encode(array('result'=>'faild', 'message'=>'1대1상담 또는 첨부파일이 존재하지 않습니다.')));
		}

		deletePrdImage($data, numberOnly($key));
		$pdo->query("update $tbl[cs] set $key='' where no='$no'");

		exit(json_encode(array('result'=>'success')));
	}

?>