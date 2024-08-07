<?php

/**
 * ERP APIKey 관리자 Database 처리
 **/

// 키 재생성
switch($_POST['exec']) {
    // 키 재생성
    case 'regenerate' :
        $apikey = str_shuffle(md5(time().rand(0, 9999)));
            $pdo->query("
                update {$tbl['erp_api']} set apikey=? where idx=?
            ", array(
                $apikey, $_POST['idx']
            ));
        break;

    // 삭제
    case 'remove' :
        $pdo->query("delete from {$tbl['erp_api']} where idx=?", array($_POST['idx']));
        break;

    // 사용여부 토글
    case 'toggle' :
        $is_active = $pdo->row("select is_active from {$tbl['erp_api']} where idx=?", array($_POST['idx']));
        $status = ($is_active == 'Y') ? 'N' : 'Y';

        $pdo->query("
            update {$tbl['erp_api']} set is_active=? where idx=?
        ", array(
            $status, $_POST['idx']
        ));

        header('Content-type:application/json');
        exit(json_encode(array(
            'idx' => $_POST['idx'],
            'status' => $status
        )));
        break;

    // 등록 및 수정
    default :
        checkBlank($_POST['name'], '이름을 입력해주세요.');

        if ($_POST['idx'] > 0) {
            $pdo->query("
                update {$tbl['erp_api']} set name=? where idx=?
            ", array(
                $_POST['name'], $_POST['idx']
            ));
        } else {
            $apikey = str_shuffle(md5(time().rand(0, 9999)));
            $pdo->query("
                insert into {$tbl['erp_api']}
                    (name, apikey, is_active, reg_date) values (?, ?, 'Y', now())
            ", array(
                $_POST['name'], $apikey
            ));
        }

        msg('', 'reload', 'parent');

}

?>