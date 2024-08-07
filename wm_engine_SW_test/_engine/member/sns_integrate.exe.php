<?php

/**
 * SNS아이디 통합
 **/

require_once $engine_dir.'/_engine/include/common.lib.php';

memberOnly();

// SNS 연결 해제
if ($_POST['exec'] == 'disconnect') {
    header('Content-type: application/json');

    $type = $_POST['type'];
    $sns = $pdo->assoc("select no from {$tbl['sns_join']} where member_no=? and type=?", array(
        $member['no'], $type
    ));
    if ($sns['no'] > 0) {
        $pdo->query("delete from {$tbl['sns_join']} where no=?", array(
            $sns['no']
        ));
        if ($pdo->lastRowCount() == 1) {

            $login_type = str_replace('@'.$type, '', $member['login_type']);
            if ($login_type == '@') $login_type = '';
            $pdo->query("update {$tbl['member']} set login_type=? where no=? and member_id=?", array(
                $login_type, $member['no'], $member['member_id']
            ));

            exit(json_encode(array(
                'status' => 'success',
                'message' => 'sns 계정 연결이 해제되었습니다.'
            )));
        } else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => '해제 처리 중 오류가 발생하였습니다.'
            )));
        }
    }
    exit(json_encode(array(
        'status' => 'error',
        'message' => '등록된 sns계정이 없습니다.'
    )));
    exit;
}

// 기존 가입 여부 체크
$type = $_sns_type[$_SESSION['sns_login']['sns_type']];
$check = $pdo->row("select member_no from {$tbl['sns_join']} where type=? and cid=?", array(
    $type, $_SESSION['sns_login']['cid']
));

if ($check > 0) {
    if ($check == $member['no']) {
        msg(
            '이미 연결되어있는 SNS계정입니다.',
            $root_url.'/member/edit_step2.php'
        );
    } else {
        msg(
            '다른 계정에 연결된 SNS계정입니다.\\n해당 SNS계정을 탈퇴한 후 통합이 가능합니다.',
            $root_url.'/member/edit_step2.php'
        );
    }
}

// 통합 처리
$pdo->query("
    insert into {$tbl['sns_join']}
    (type, cid, member_no, member_id, reg_date)
    values (?, ?, ?, ?, ?)
", array(
    $type, $_SESSION['sns_login']['cid'], $member['no'], $member['member_id'], $now
));

if ($pdo->lastRowCount() == 1) {
    $login_type = explode('@', trim($member['login_type'], '@'));
    $login_type[] = $type;
    $login_type = implode('@', $login_type);
    $pdo->query("update {$tbl['member']} set login_type=? where no=? and member_id=?", array(
        $login_type, $member['no'], $member['member_id']
    ));
}

header('Location: '.$root_url.'/member/edit_step2.php');