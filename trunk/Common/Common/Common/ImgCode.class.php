<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/9/22
 * Time: 下午2:55
 */

namespace Common\Common;

use VcySDK\ImageVerify;
use VcySDK\Service;

class ImgCode
{

    /**
     * @type ImageVerify
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
     * @return ImgCode
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
        $this->__sdk = new ImageVerify(Service::instance());
        $this->__secret = cfg('COOKIE_SECRET');
    }

    /**
     * 生成图片验证码
     *
     * @param int $width    图片验证码宽度
     * @param int $height   图片验证码高度
     * @param int $fontSize 字体大小
     *
     * @return array
     */
    public function generate($width = 200, $height = 50, $fontSize = 40)
    {

        // 生成图片验证码
        $result = $this->__sdk->generateCode(array(
            'width' => $width,
            'height' => $height,
            'fontSize' => $fontSize
        ));

        // 签名
        $imgCaptchaSign = authcode(rstrtolower($result['imgCaptchaCode']) . "\t" . NOW_TIME, $this->__secret, 'ENCODE');
        return array(
            'imgCaptchaToken' => (string)$result['imgCaptchaToken'],
            'imgCaptchaUrl' => (string)$result['imgCaptchaUrl'],
            'imgCaptchaSign' => $imgCaptchaSign
        );
    }

    /**
     * 验证图片验证码是否正确
     *
     * @param string $token 验证码Token
     * @param string $code  验证码
     *
     * @return bool
     */
    public function verifySDK($token, $code)
    {

        // 验证图片验证码
        $this->__sdk->verifyCode(array(
            'imgCaptchaToken' => $token,
            'imgCaptchaCode' => $code
        ));

        return true;
    }

    /**
     * 验证图片验证码是否正确
     *
     * @param string $code   验证码
     * @param string $sign   验证码签名
     * @param int    $expire 有效期
     *
     * @return bool
     */
    public function verifyLocal($code, $sign, $expire = 0)
    {

        // 解出验证码
        list($_code, $_ts) = explode("\t", authcode($sign, $this->__secret));
        // 判断是否过期
        if (0 < $expire && NOW_TIME > $_ts + $expire) {
            E('_ERR_IMG_CAPTCHA_EXPIRED');
            return false;
        }

        // 验证验证码是否正确
        if ($_code != rstrtolower($code)) {
            E('_ERR_IMG_CAPTCHA_ERROR');
            return false;
        }

        return true;
    }

}
