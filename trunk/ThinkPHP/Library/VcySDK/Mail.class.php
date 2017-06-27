<?php

/**
 * Mail.class.php
 * 邮件接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Mail
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 发送模板邮件的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}
     *
     * @var string
     */
    const SEND_TEMPLATE_URL = '%s/mail/template/send';

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
     * 发送模板邮件
     *
     * @param array $condition 邮件参数
     */
    public function sendTemplateMail($condition)
    {

        return $this->serv->postSDK(self::SEND_TEMPLATE_URL, $condition, 'generateApiUrlS');
    }
}
