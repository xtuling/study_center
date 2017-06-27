<?php

/**
 * WxPay.class.php
 * 支付接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */

namespace VcySDK;

class WxPay
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 编辑收货地址参数获取接口
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const ADDRESS_EDIT_URL = '%s/address/edit';

    /**
     * 支付参数获取接口
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const PREPAY_URL = '%s/prepay';

    /**
     * 支付参数获取接口
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const ORDERQUERY_URL = '%s/orderquery';

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 获取编辑收货地址所需的参数
     *
     * @param string $url 当前页面的Url
     * @return boolean|multitype:
     */
    public function editAddress($url)
    {
        // 过滤#以及以后的部分
        $url = preg_replace('/#.+?$/', '', $url);

        // 编辑收货地址所需参数
        $params = array(
            'url' => $url
        );

        return $this->serv->postSDK(self::ADDRESS_EDIT_URL, $params, 'generateApiUrlPay');
    }

    /**
     * 获取JSSDK支付所需的参数
     *
     * @param array $params 支付所需参数
     * @return boolean|multitype:
     */
    public function prepay($params)
    {

        return $this->serv->postSDK(self::PREPAY_URL, $params, 'generateApiUrlPay');
    }

    /**
     * 订单查询
     *
     * $worId String 订单ID
     * @return mixed
     */
    public function orderQuery($worId)
    {

        return $this->serv->postSDK(self::ORDERQUERY_URL, ['worId' => $worId], 'generateApiUrlPay');
    }
}