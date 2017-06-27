<?php

namespace Api\Controller\Pay;

use VcySDK\Service;
use VcySDK\WxPay;

class QueryController extends AbstractController
{

    protected $_require_login = false;

    /**
     */
    public function Index_post()
    {
        $worId = I('post.worId');

        $paySdk = new WxPay(Service::instance());
        $this->_result = $paySdk->orderQuery($worId);

        return true;
    }
}
