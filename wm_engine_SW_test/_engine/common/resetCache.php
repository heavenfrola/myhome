<?php

use Wing\Design\DesignCache;

//페이지 캐시 갱신 요청
include_once $engine_dir."/_engine/include/common.lib.php";
DesignCache::reset();