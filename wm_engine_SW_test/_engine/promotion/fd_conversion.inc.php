<?php

/**
 * 페이스북 전환 API
 **/

require_once __ENGINE_DIR__.'/_engine/include/facebook.lib.php';

$ord = $pdo->assoc("
    select
        ono, buyer_email, buyer_cell, ip, pay_prc
    from {$tbl['order']}
    where
        ono='$ono'
");
try {
    fbPurchase($ord);
} catch (Exception $e) {
}
