<?php
/**
 * 设置管理员密码（初始化管理员密码）
 */
namespace Apicp\Controller\AdminManager;

use Com\Validate;
use VcySDK\Adminer;
use VcySDK\Exception;
use VcySDK\Service;
use VcySDK\Sms;

class InitPwdController extends AbstractController
{
    protected $_require_login = false;

    /** 提交的数据 */
    private $postData = [];

    public function Index()
    {
        // 获取和验证 数据
        $this->getParams();

        try {
            // 短信token
            $smsSdk = new Sms(Service::instance());
            $smsVerify = $smsSdk->verifyCode([
                'scMobile' => $this->postData['eaMobile'],
                'scCode' => $this->postData['mobileCode']
            ]);
            $this->postData['token'] = $smsVerify;

            // 初始化
            $sdk = new Adminer(Service::instance());
            $sdk->initPwd($this->postData);
        } catch (Exception $e) {
            /**
             * UC ERROR CODE
             * SMS_CODE_EXPIRED_FAILD
             */
            E($e->getSdkCode());
            return false;
        }

        return true;
    }

    /**
     * 获取提交数据
     * @return bool
     */
    protected function getParams()
    {
        $field = [
            'aiaToken',
            'eaPassword',
            'eaMobile',
            'mobileCode'
        ];
        foreach ($field as $_name) {
            $this->postData[$_name] = I('post.' . $_name);
        }

        $this->validateParams();

        return true;
    }

    /**
     * 验证数据
     * @return bool
     */
    protected function validateParams()
    {
        $validator = new Validate(
            [
                'aiaToken' => 'require',
                'eaPassword' => 'require',
                'eaMobile' => 'require',
                'mobileCode' => 'require'
            ],
            [
                'aiaToken' => L('_ERR_PLS_SUBMIT_ID', ['name' => 'token']),
                'eaPassword' => L('_ERR_PLS_SUBMIT_ID', ['name' => '密钥']),
                'eaMobile' => L('_ERR_PLS_SUBMIT_ID', ['name' => '手机号']),
                'mobileCode' => L('_ERR_PLS_SUBMIT_ID', ['name' => '手机验证码']),
            ]
        );
        if (!$validator->check($this->postData)) {
            E($validator->getError());
        }

        return true;
    }
}
