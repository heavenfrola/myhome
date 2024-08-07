<?php

    /* +----------------------------------------------------------------------------------------------+
    ' |  사용가능 쿠폰 레이어 호출
    ' +----------------------------------------------------------------------------------------------+*/
    require_once $engine_dir."/_engine/include/common.lib.php";
    include_once $engine_dir."/_engine/include/shop.lib.php";
    include_once $engine_dir."/_engine/include/shop2.lib.php";

    memberOnly();

    $_tmp_file_name = '/member/order_coupon_frm.php';
    require_once $engine_dir."/_engine/common/skin_index.php";
