<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입 완료페이지
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/member.lib.php';

	printAjaxHeader();

	$key = $_GET['key'];
	$type = $_GET['type'];
	if(!$key) msg(__lang_member_error_noAuthkey__);

	$data = $pdo->assoc("select * from $tbl[member] where reg_code='$key'");
	if(!$data['no']) msg(__lang_member_error_wrongAuthkey__);

	if($key) {
		addField(
			$tbl['member'],
			'email_reserve',
			'varchar(100) not null default "" after email'
        );
	}

	$pdo->query("update $tbl[member] set reg_email='Y', reg_code='', email_reserve='' where no='$data[no]'");

    // 이메일 인증 사용 중 이메일 변경
    if (strlen($data['email_reserve']) > 0) {
        // 이메일 선점 체크
        if ($cfg['join_check_email'] == 'Y') {
            $check = $pdo->row("select count(*) from {$tbl['member']} where email=? and no!=?", array(
                $data['email_reserve'], $data['no']
            ));
            if ($check > 0) {
                msg(__lang_member_error_existsEmail__, $root_url);
            }
        }

        $pdo->query("update {$tbl['member']} set email=? where no=?", array(
            $data['email_reserve'], $data['no']
        ));

        // 이메일을 아이디로 이용 시 아이디도 같이 변경
        if ($cfg['member_join_id_email'] == 'Y') {
            $pdo->query("update {$tbl['member']} set member_id=? where no=?", array(
                $data['email_reserve'], $data['no']
            ));
            updateMemberIdField($data['email_reserve'], $data['member_id']);
        }
    }

    if ($data['level'] == '8') {
        $biz = $pdo->assoc("select * from {$tbl['biz_member']} where ref='{$data['no']}'");
        if ($biz['auth'] == 'N') {
            msg(__lang_member_info_joinProgress__, $root_url);
        }
    }

	$member = $pdo->assoc("select * from $tbl[member] where no='$data[no]'");
	$_SESSION['member_no'] = $data['no'];
	$_SESSION['m_member_id'] = $data['member_id'];
	$_SESSION['just_join'] = 1;

	$url = $type == 're' ? 'edit_step3.php' : 'join_step3.php';

	$name = $member['name'];
	$member_id = $member['member_id'];
	$mail_case = 1;
	include_once $engine_dir.'/_engine/include/mail.lib.php';
	if($type != "re") {
		sendMailContent($mail_case, $member['name'], $member['email']);
	}

	msg(__lang_member_info_joinAuthCompleted__, $root_url."/member/".$url);

?>