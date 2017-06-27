<?php
/**
 * Sms.class.php
 * 手机短消息接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Sms
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 发送短信的接口地址
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const SEND_MESSAGE_URL = '%s/sms/message/send';

    /**
     * 发送验证码的接口地址
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const SEND_CODE_URL = '%s/sms/code/send';

    /**
     * 验证验证码的接口地址
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const VERIFY_CODE_URL = '%s/sms/code/verify';

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
     * 验证短信验证码
     *
     * @param array $condition 读取条件
     *        + memUid string 用户UID(uid和手机必须有一个值)
     *        + scMobile string 手机号码
     */
    public function verifyCode($condition)
    {

        return $this->serv->postSDK(self::VERIFY_CODE_URL, $condition, 'generateApiUrlS');
    }

    /**
     * 发送验证码
     *
     * @param array $condition 验证码信息
     */
    public function sendCode($condition)
    {

        return $this->serv->postSDK(self::SEND_CODE_URL, $condition, 'generateApiUrlS');
    }

    /**
     * 发送短信消息
     *
     * @param array $condition 短信消息
     */
    public function sendMessage($condition)
    {
        return $this->serv->postSDK(self::SEND_MESSAGE_URL, $condition, 'generateApiUrlS');
    }
}
