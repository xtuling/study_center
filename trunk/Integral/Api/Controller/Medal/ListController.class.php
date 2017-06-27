<?php

/**
 * Created by PhpStorm.
 * 赋予勋章RPC测试接口
 */

namespace Api\Controller\Medal;

use Api\Controller\Integral\AbstractController;
use Common\Common\Integral;

class ListController extends AbstractController
{
    public function Index_post()
    {
        $integralUtil = new Integral();
        $this->_result = $integralUtil->listMedal();

        return true;
    }
}
