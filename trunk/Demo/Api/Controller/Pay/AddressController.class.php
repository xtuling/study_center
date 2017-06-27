<?php

namespace Api\Controller\Pay;

use VcySDK\Service;
use VcySDK\WxPay;

class AddressController extends AbstractController
{

    protected $_require_login = false;

    /**
     */
    public function Index_post()
    {
        $url = I('post.url');

        $paySdk = new WxPay(Service::instance());
        $this->_result = $paySdk->editAddress($url);

        return true;
    }
}
