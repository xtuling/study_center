<?php
/**
 * BizPay.class.php
 * 商家支付操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhonglei
 * @version 1.0.0
 */
namespace VcySDK;

class BizPay
{
    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 商家支付接口
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const TRANSFERS_URL = '%s/mp/transfers';

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
     * 支付
     *
     * @param array $params 支付所需参数
     * @return array
     */
    public function transfers($params)
    {
        return $this->serv->postSDK(self::TRANSFERS_URL, $params, 'generateApiUrlB');
    }
}
