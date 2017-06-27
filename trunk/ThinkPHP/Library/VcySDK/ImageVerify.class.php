<?php
/**
 * ImageVerify.class.php
 * 手机短消息接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */

namespace VcySDK;

class ImageVerify
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 生成图片验证码的接口地址
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const GENERATE_URL = '%s/img/code/generate';

    /**
     * 验证验证码的接口地址
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const VERIFY_URL = '%s/img/code/verify';

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
     * 生成图片验证码
     *
     * @param array $condition 读取条件
     *        + width Number 图片宽，默认：200
     *        + height Number 图片宽，默认：50
     *        + fontSize Number 字体大小 ，默认：40
     */
    public function generateCode($condition)
    {

        return $this->serv->postSDK(self::GENERATE_URL, $condition, 'generateApiUrlS');
    }

    /**
     * 发送验证码
     *
     * @param array $condition 读取条件
     *        + imgCaptchaToken string 图片验证码token
     *        + imgCaptchaCode string 图片验证码code
     */
    public function verifyCode($condition)
    {

        return $this->serv->postSDK(self::VERIFY_URL, $condition, 'generateApiUrlS');
    }
}
