<?php
/**
 * 验证手机验证码(本地验证)
 * Created by PhpStorm.
 *
 */

namespace Apicp\Controller\Sms;

use Com\Validator;
use Common\Common\Sms;

class VerifyLocalController extends AbstractController
{

    public function Index()
    {

        // 获取手机号
        $mobile = I("post.mobile");
        $smsSign = I("post.smsSign");
        $code = I("post.code");
        settype($code, 'string');

        // 如果不为手机号
        if (! Validator::is_phone($mobile)) {
            $this->_set_error('_ERR_PHONE_FORMAT');
            return false;
        }

        // 手机验证码签名
        if (empty($smsSign)) {
            $this->_set_error('_ERR_SMS_SIGN_EMPTY');
            return false;
        }

        // 手机验证码
        if (empty($code)) {
            $this->_set_error('_ERR_SMS_CODE_EMPTY');
            return false;
        }

        // 验证
        if (! Sms::instance()->verifyCodeLocal($mobile, $code, $smsSign)) {
            $this->_set_error('_ERR_SMS_CODE_ERROR');
            return false;
        }

        return true;
    }

}

