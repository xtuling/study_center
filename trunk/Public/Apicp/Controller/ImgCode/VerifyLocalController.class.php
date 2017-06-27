<?php
/**
 * 验证图片验证码(本地验证)
 * Created by PhpStorm.
 *
 */

namespace Apicp\Controller\ImgCode;


use Common\Common\ImgCode;

class VerifyLocalController extends AbstractController
{

    public function Index()
    {

        // 获取图片验证码CODE
        $imgCaptchaCode = I("post.imgCaptchaCode");
        // 验证码签名
        $imgCaptchaSign = I('post.imgCaptchaSign');

        // 图片验证码签名为空
        if (empty($imgCaptchaSign)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_SIGN_EMPTY');
            return false;
        }

        // 图片验证码CODE为空
        if (empty($imgCaptchaCode)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_CODE_EMPTY');
            return false;
        }

        // 验证图片验证码
        if (! ImgCode::instance()->verifyLocal($imgCaptchaCode, $imgCaptchaSign)) {
            $this->_set_error('_ERR_IMG_CAPTCHA_CODE_ERROR');
            return false;
        }

        return true;
    }

}
