<?php
/**
 * 发送短信
 * Created by PhpStorm.
 *
 */

namespace Api\Controller\Sms;

use Com\Validator;
use Common\Common\Sms;

class SendCodeController extends AbstractController
{

    public function Index()
    {

        // 获取手机号
        $mobile = I("post.mobile");
        $imgCaptchaToken = I("post.imgCaptchaToken");
        $imgCaptchaCode = I("post.imgCaptchaCode");

        // 如果不为手机号
        if (! Validator::is_phone($mobile)) {
            $this->_set_error('_ERR_PHONE_FORMAT');
            return false;
        }

        // 图片验证码 Token 为空
        /**if (empty($imgCaptchaToken)) {
            $this->_set_error('_ERR_EMPTY_IMG_TOKEN');
            return false;
        }

        // 图片验证码 code 为空
        if (empty($imgCaptchaCode)) {
            $this->_set_error('_ERR_EMPTY_IMG_CODE');
            return false;
        }*/

        // 发送短信
        $code = random(6, 1);
        $this->_result = array(
            'smsSign' => Sms::instance()->sendCode($mobile, $code, 'token', 'code', '', '', true)
        );

        return true;
    }

}

