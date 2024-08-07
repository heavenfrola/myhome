<?php

/**
 * 개인정보보호 설정
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

if ($scfg->comp('use_account_enc', 'Y') == false && $_POST['use_account_enc'] == 'Y') {
    $pdo->query("
    ALTER TABLE {$tbl['order_payment']}
        CHANGE COLUMN bank_name bank_name VARCHAR(100),
        CHANGE COLUMN bank_account bank_account VARCHAR(100)
    ");

    $res = $pdo->iterator("select no, bank_name, bank_account from {$tbl['order_payment']} where bank_account!=''");
    foreach ($res as $data) {
        $bank_name = aes128_encode($data['bank_name']);
        $bank_account = aes128_encode($data['bank_account']);
        $pdo->query("update {$tbl['order_payment']} set bank_name=?, bank_account=? where no=?", array(
            $bank_name, $bank_account, $data['no']
        ));
    }
}

require __ENGINE_DIR__.'/_manage/config/config.exe.php';

?>