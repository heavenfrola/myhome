<?php

/**
 * 입점사 세트상품 등록 폼
 **/

if ($scfg->comp('partner_prd_ref', 'N') == false) {
    msg('세트상품 등록 권한이 없습니다.');
}

require_once __ENGINE_DIR__.'/_manage/product/set_register.php';