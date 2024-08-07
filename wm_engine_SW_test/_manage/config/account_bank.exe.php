<?php

/**
 *  무통장 계좌 등록, 수정, 삭제
 **/

if (isset($_POST['exec']) == true) {
    switch ($_POST['exec']) {
        case 'sort' : // 정렬
            switch($_POST['step']) {
                case '-1' :
                    $direction = '<';
                    $order = 'desc';
                    break;
                case '1' :
                    $direction = '>';
                    $order = 'asc';
                    break;
            }
            $source = $pdo->assoc("select no, sort from {$tbl['bank_account']} where no=:no", array(':no' => $_POST['no']));
            $target = $pdo->assoc("select no, sort from {$tbl['bank_account']} where type=:type and sort $direction {$source['sort']} order by sort $order limit 1", array(
                ':type' => $_POST['type']
            ));

            $pdo->query("update {$tbl['bank_account']} set sort='{$target['sort']}' where no='{$source['no']}'");
            $pdo->query("update {$tbl['bank_account']} set sort='{$source['sort']}' where no='{$target['no']}'");

            require_once 'account_bank.inc.php';

            break;
        case 'remove' : // 삭제
            $no = implode(',', numberOnly($_POST['no']));
            $pdo->query("delete from {$tbl['bank_account']} where no in ($no)");
            break;
    }
    exit;
}

// 등록 및 수정
if (empty($_POST['bank']) == true) exit('은행을 선택해주세요.');
if (empty($_POST['account']) == true) exit('계좌번호를 입력해주세요.');
if (empty($_POST['owner']) == true) exit('예금주를 입력해주세요.');

$bind = array(
    ':bank' => trim($_POST['bank']),
    ':owner' => trim($_POST['owner']),
    ':account' => trim($_POST['account']),
    ':type' => trim($_POST['type'])
);

if ($_POST['no'] > 0) {
    $r = $pdo->query("update {$tbl['bank_account']} set bank=:bank, owner=:owner, account=:account, type=:type where no=:no", array_merge($bind, array(
        ':no' => $_POST['no'],
    )));
} else {
    $r = $pdo->query("insert into {$tbl['bank_account']} (bank, owner, account, type, sort) values (:bank, :owner, :account, :type, :sort)", array_merge($bind, array(
        ':sort' => ($pdo->row("select count(*) from {$tbl['bank_account']}")+1),
    )));
}

exit(($r == true) ? 'success' : 'error');

?>