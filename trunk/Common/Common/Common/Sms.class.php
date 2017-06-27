<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/9/22
 * Time: 下午3:56
 */

namespace Common\Common;

use VcySDK\Service;

class Sms
{

    /**
     * @type \VcySDK\Sms
     */
    private $__sdk = null;

    /**
     * 加密密钥
     *
     * @type mixed|string
     */
    private $__secret = '';

    /**
     * 单例
     *
     * @return Sms
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {

        // 实例化
        $this->__sdk = new \VcySDK\Sms(Service::instance());
        $this->__secret = cfg('COOKIE_SECRET');
    }

    /**
     * 发送手机短信
     * @param string $mobile  手机号码
     * @param string $message 短信内容
     * @param string $uid     发送人UID
     * @param string $ip      当前IP地址
     * @return bool
     */
    public function send($mobile, $message, $uid = '', $ip = '')
    {

        $sign = cfg('SMS_SIGN');

        if ($sign) {
            $message = $sign . $message;
        }

        if (mb_strlen($message) > 70) {
            E('_ERROR_SMS_TOO_LONG');
        }

        $this->__sdk->sendMessage(array(
            'smsMobile' => $mobile,
            'smsMessage' => $message,
            'memUid' => $uid,
            'smsIp' => $ip
        ));

        return true;
    }

    /**
     * 发送手机验证码
     *
     * @param string $mobile  手机号码
     * @param string $code    手机验证码
     * @param string $token   图片Token
     * @param string $imgcode 图片验证码
     * @param string $msgtpl  消息模板
     * @param string $sign    消息签名
     *
     * @return bool
     */
    public function sendCode($mobile, $code, $token, $imgcode, $msgtpl = '', $sign = '', $ignoreImgCaptcha = false)
    {

        // 短信签名
        $sign = empty($sign) ? cfg('SMS_CODE_SIGN') : $sign;
        // 消息模板
        $msgtpl = empty($msgtpl) ? cfg('SMS_CODE_MESSAGE_TPL') : $msgtpl;
        // 消息内容
        $message = str_replace(array('{SIGN}', '{CODE}'), array($sign, $code), $msgtpl);

        $this->__sdk->sendCode(array(
            'scMobile' => $mobile,
            'scCode' => $code,
            'smsMessage' => $message,
            'imgCaptchaToken' => $token,
            'imgCaptchaCode' => $imgcode,
            'ignoreImgCaptcha' => $ignoreImgCaptcha ? 1 : 0
        ));

        return $this->generateSign($mobile, $code);
    }

    /**
     * 验证验证码(SDK验证)
     *
     * @param string $mobile  手机号码
     * @param string $code    验证码
     * @param string $uid     用户uid
     * @param string $enumber 企业标识
     *
     * @return bool
     */
    public function verifyCodeSDK($mobile, $code, $uid = '', $enumber = '')
    {

        $this->__sdk->verifyCode(array(
            'scMobile' => $mobile,
            'scCode' => $code,
            'memUid' => $uid,
            'epEnumber' => $enumber
        ));

        return true;
    }

    /**
     * 验证验证码(本地验证)
     *
     * @param string $mobile  手机号码
     * @param string $code    验证码
     * @param string $smsSign 签名
     * @param int    $expire  过期时间
     *
     * @return bool
     */
    public function verifyCodeLocal($mobile, $code, $smsSign, $expire = 0)
    {

        list($_mobile, $_code, $_ts) = $this->parseSign($smsSign);
        // 判断是否过期
        if (0 < $expire && NOW_TIME > $_ts + $expire) {
            E('_ERR_SMS_CODE_EXPIRED');
            return false;
        }

        // 验证验证码是否正确
        if ($_code != rstrtolower($code) || $mobile != $_mobile) {
            E('_ERR_SMS_CODE_ERROR');
            return false;
        }

        return true;
    }

    /**
     * 根据手机号码和验证码生成 Token
     *
     * @param string $mobile 手机号码
     * @param string $code   验证码
     *
     * @return string
     */
    public function generateSign($mobile, $code)
    {

        return authcode($mobile . "\t" . rstrtolower($code) . "\t" . NOW_TIME, $this->__secret, 'ENCODE');
    }

    /**
     * 解析Token值
     *
     * @param string $token Token值
     *
     * @return array
     */
    public function parseSign($token)
    {

        return explode("\t", authcode($token, $this->__secret));
    }
}
