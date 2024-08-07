<?PHP

	if($_POST['exec'] == 'naccount_id') {
		$wec_acc = new weagleEyeClient($_we, 'account');
		$wec_acc->call('setCheckoutAccountID', array('engine'=>'wing', 'naccount_id'=>$_POST['ncc_AccountId']));

		if($wec_acc->error) {
			msg($wec_acc->result);
		}

		if($cfg['ncpa_use'] != 'Y' && $_POST['ncpa_use'] == 'Y') {
			$_POST['ncpa_use_date'] = $now;
		}
	} else {
		$script = "
		<?PHP
			\$urlfix = 'Y';
			chdir('../..');
			\$exec_file = \$_REQUEST['exec_file'] = 'shop/checkout_prd.php';
			include '../main/exec.php';
		?>
		";

		$script2 = "
		<?PHP
			\$urlfix = 'Y';
			chdir('../..');
			\$exec_file = \$_REQUEST['exec_file'] = 'shop/npaydlv.php';
			include '../main/exec.php';
		?>
		";

		$script_path = '_data/compare/naver/';
		makeFullDir($script_path);

		$fp = fopen("$root_dir/$script_path/checkoutprd.php", 'w');
		fwrite($fp, trim($script));
		fclose($fp);
		chmod("$root_dir/$script_path/checkoutprd.php", 0777);

		$fp = fopen("$root_dir/$script_path/npaydlv.php", 'w');
		fwrite($fp, trim($script2));
		fclose($fp);
		chmod("$root_dir/$script_path/npaydlv.php", 0777);

		if(!$_POST['npay_review_general']) $_POST['npay_review_general'] = 'N';
		if(!$_POST['npay_review_premium']) $_POST['npay_review_premium'] = 'N';

		if($_POST['npay_review_general'] == 'Y' || $_POST['npay_review_premium'] == 'Y') {
			$pdo->query("alter table $tbl[review] change ono ono varchar(30) not null default ''");
			addField($tbl['review'], 'npay', 'enum("N","Y") not null default "N"');
		}

		if($_POST['use_npay_qna'] == 'Y') {
			addField($tbl['qna'], 'checkout_no', 'int(12) not null default "0" comment "네이버페이 문의번호"');
			addField($tbl['qna'], 'checkout_ans_no', 'int(12) not null default "0" comment "네이버페이 답변글 번호"');
			$pdo->query("alter table $tbl[qna] add index checkout_no(checkout_no)");
		}

		// readonly 풀고 강제로 아이디 변경 불가
		$wec_acc = new weagleEyeClient($_we, 'account');
		$npay = $wec_acc->call('checkoutStatus');

		$_POST['checkout_id'] = $npay[0]->checkout_id[0];
		$_POST['checkout_key'] = $npay[0]->auth_key[0];
		$_POST['checkout_btn_key'] = $npay[0]->button_auth_key[0];

		$wec = new weagleEyeClient($_we, 'Etc');
		$wec->call('setExternalService', array(
			'service_name' => 'pg_naverpay',
			'use_yn' => (empty($_POST['checkout_id']) == false ? 'Y' : 'N'),
			'root_url' => $root_url,
			'extradata' => $_POST['checkout_id'],
            'client_ip' => $_SERVER['REMOTE_ADDR']
		));
	}

	include $engine_dir.'/_manage/config/config.exe.php';

?>