<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  탈퇴요청 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Kakao\KakaoSync;

	include_once $engine_dir.'/_engine/include/common.lib.php';
	memberOnly();

	$pwd = $_POST['pwd'];
	$content = trim($_POST['content']);

	checkBlank($pwd, __lang_member_input_pwd__);
	checkBlank($content, __lang_mypage_input_reason__);

	$pwd = sql_password($pwd);
	if ($pwd != $member['pwd']) msg(__lang_member_error_wrongPwd__);

    // 카카오로그인 이용 시 연결 끊기
    if ($_SESSION['sns_login']['bearer']) {
        $kkosync = new KakaoSync();
        $ret = $kkosync->unlink(
            $pdo->row("select cid from {$tbl['sns_join']} where member_no='{$member['no']}'")
        );
        $pdo->query("delete from {$tbl['sns_join']} where member_no='{$member['no']}'");
        $pdo->query("update {$tbl['member']} set login_type=replace(login_type, '@kko', '') where no='{$member['no']}'");
    }

    // 탈퇴 시 즉시 삭제
    if ($scfg->comp('withdrawal', 'immediately') == true) {
        include_once __ENGINE_DIR__.'/_manage/manage.lib.php';

        deleteMember(array(
            $_SESSION['member_no']
        ));

        unset($_SESSION['member_no'], $_SESSION['m_member_id']);
        $_SESSION['out_member_no'] = $member['no'];

    	msg('', $root_url.'/mypage/withdraw_step2.php', 'parent');
    }

	$content  = del_html($content);
	$content .= ':::::'.$now;

	$pdo->query("update {$tbl['member']} set withdraw='Y', withdraw_content=? where no=?", array(
        $content, $member['no']
    ));

	unset($_SESSION['member_no'], $_SESSION['m_member_id']);

	$_SESSION['out_member_no'] = $member['no'];
	msg('', $root_url.'/mypage/withdraw_step2.php', 'parent');

?>