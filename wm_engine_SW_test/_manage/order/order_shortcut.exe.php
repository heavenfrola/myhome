<?php

/**
 * 주문서 바로가기 링크 생성
 **/

$ono = $_GET['ono'];
$ord = $pdo->assoc("select no, hash from {$tbl['order']} where ono=?", array($ono));
if ($ord == false) {
    msg('존재하지 않는 주문번호입니다.');
}
$hash = $ord['hash'];

if (empty($hash) == true) {
    // 필드 생성
    if (isset($ord['hash']) == true) {
        $pdo->query("
            ALTER TABLE {$tbl['order']}
                ADD COLUMN hash VARCHAR(10) NOT NULL DEFAULT '' AFTER ono,
                ADD INDEX hash (hash);
        ");
    }

    // 해쉬 생성
    while (1) {
        $hash = substr(preg_replace('/[^0-9a-z]/i', '', substr(crypt($ono.microtime()), 3)), 0, 8);
        if ($pdo->row("select count(*) from {$tbl['order']} where hash=?", array($hash)) == 0) {
            break;
        }
    }
    $pdo->query("update {$tbl['order']} set hash=? where ono=?", array(
        $hash, $ono
    ));
}

$link = $root_url.'/o/'.$hash;

?>
<script>
window.open('<?=$link?>');
</script>