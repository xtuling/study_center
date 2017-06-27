<?php

/**
 * Pay.class.php
 * 企业号企业支付
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK\WxQy;

class Pay
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 企业支付
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const PAY_URL = '%s/pay';

    /**
     * 企业支付查询接口
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const PAY_RESULT_URL = '%s/pay_result';

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
     * 企业支付
     * @param $params
     * + uid 必填 String 用户id主键
     * + platformOrderNo 必填 String 订单号
     * + amount 必填 int 转账金额(单位:分, 注: 转账最小金额为1元)
     * + checkName 必填 String (默认NO_CHECK)校验用户姓名选项 NO_CHECK：不校验真实姓名,FORCE_CHECK：强校验真实姓名
     * （未实名认证的用户会校验失败，无法转账）,
     *  OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
     * + reUserName 非必填 String 收款用户真实姓名。 如果check_name设置为FORCE_CHECK或OPTION_CHECK，则必填用户真实姓名
     * @return array|bool
     */
    public function pay($params)
    {

        return $this->serv->postSDK(self::PAY_URL, $params, 'generateApiUrlA');
    }

    /**
     * 企业支付查询接口
     * @param $wtlPartnerTradeNo String 必填  支付平台订单号(多个订单逗号分页如: 12345,33333,4444)
     * @return array
     */
    public function payResult($wtlPartnerTradeNo)
    {
        settype($wtlPartnerTradeNo, 'string');

        return $this->serv->postSDK(self::PAY_RESULT_URL,
            ['wtlPartnerTradeNo' => $wtlPartnerTradeNo],
            'generateApiUrlA');
    }
}
