<?php
/**
 * 验证手机验证码(SDK验证)
 * Created by PhpStorm.
 *
 */

namespace Api\Controller\Sms;

use Com\Validator;
use Common\Common\Sms;

class VerifySDKController extends AbstractController
{

    public function Index()
    {

        // 获取手机号
        $mobile = I("post.mobile");
        $code = I("post.code");

        // 如果不为手机号
        if (! Validator::is_phone($mobile)) {
            $this->_set_error('_ERR_PHONE_FORMAT');
            return false;
        }

        // 手机验证码
        if (empty($code)) {
            $this->_set_error('_ERR_SMS_CODE_EMPTY');
            return false;
        }

        // 验证手机验证码
        if (! Sms::instance()->verifyCodeSDK($mobile, $code)) {
            $this->_set_error('_ERR_SMS_CODE_ERROR');
            return false;
        }

        $this->_result = array(
            'smsCodeSign' => Sms::instance()->generateSign($mobile, $code)
        );

        return true;
    }

}

