<?php

namespace Api\Controller\Pay;

use VcySDK\Service;
use VcySDK\WxPay;

class PrepayController extends AbstractController
{

    protected $_require_login = false;

    /**
     */
    public function Index_post()
    {
        $field = [
            'memUid',
            'worTotalFee',
            'worBody',
            'worIp',
            'worTradeType',
            'worOrder',
            'worChannel',
            'worNotifyUrl'
        ];
        $postData = [];
        foreach ($field as $param) {
            $postData[$param] = I('post.' . $param);
        }

        $paySdk = new WxPay(Service::instance());
        $this->_result = $paySdk->prepay($postData);

        return true;
    }
}
