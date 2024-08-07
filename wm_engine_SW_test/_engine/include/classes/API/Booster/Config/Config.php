<?php

/**
 * SmartWing API / Product
 */

namespace Wing\API\Booster\Config;

use Wing\API\Booster\Common\Common;
use Wing\API\Booster\Exceptions\CommonException;

Class Config
{
    use Common;
    use Privacy;

    public function __construct()
    {
        $this->init();
    }
}