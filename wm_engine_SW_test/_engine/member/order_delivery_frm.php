<?php

    /* +----------------------------------------------------------------------------------------------+
    ' |  배송지변경 레이어 호출
    ' +----------------------------------------------------------------------------------------------+*/
    require_once $engine_dir."/_engine/include/common.lib.php";
    include_once $engine_dir."/_engine/include/shop.lib.php";
    include_once $engine_dir."/_engine/include/shop2.lib.php";
    include_once __ENGINE_DIR__.'/_engine/include/MemberAddress.lib.php';

    $address_type = numberOnly($_GET['address_type']);
    if(!$member['no']) {
        $address_type = $_GET['address_type'] = 2;
    }
    if($address_type < 2) memberOnly();

    $_tmp_file_name = '/member/order_delivery_frm.php';
    require_once $engine_dir."/_engine/common/skin_index.php";
