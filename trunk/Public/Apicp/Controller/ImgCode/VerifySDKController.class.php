<?php
/**
 * 验证图片验证码(SDK验证)
 * Created by PhpStorm.
 *
 */

namespace Apicp\Controller\ImgCode;


use Common\Common\ImgCode;

class VerifySDKController extends AbstractController
{

    public function Index()
    {

        // 获取图片验证码CODE
        $imgCaptchaCode = I("post.imgCaptchaCode");
        // 验证码签名
        $imgCaptchaToken = I('post.imgCaptchaToken');

        // 图片验证码签名为空
        if (empty($imgCaptchaToken)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_TOKEN_EMPTY');
            return false;
        }

        // 图片验证码CODE为空
        if (empty($imgCaptchaCode)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_CODE_EMPTY');
            return false;
        }

        // 验证图片验证码
        if (! ImgCode::instance()->verifySDK($imgCaptchaToken, $imgCaptchaCode)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_CODE_ERROR');
            return false;
        }

        return true;
    }

}
