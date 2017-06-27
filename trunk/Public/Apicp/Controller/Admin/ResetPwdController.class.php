<?php
/**
 * 后台登录-修改密码提交
 * Created by PhpStorm.
 * User: 鲜彤
 * Date: 2016/7/29
 * Time: 15:35
 */
namespace Apicp\Controller\Admin;

use Com\Validator;
use VcySDK\Config;
use VcySDK\Service;
use VcySDK\Adminer;
use VcySDK\Sms;

class ResetPwdController extends AbstractAnonymousController
{

    public function Index()
    {

        $params = $this->obtainPostStrval([
            'enumber',
            'eaMobile',
            'mobileCode',
            'newPwd',
            'repeatPwd'
        ]);
        if ($params['newPwd'] != $params['repeatPwd']) {
            E('_ERR_PWD_NOT_EQ');
            return false;
        }

        $service = &Service::instance();
        // 实例化短信
        $smsService = new Sms($service);
        // 验证验证码信息
        $smsService->verifyCode(array('scMobile' => $params['eaMobile'], 'scCode' => $params['mobileCode']));

        // 调用UC，重置密码接口
        Service::instance()->setConfig(['enumber' => $params['enumber']]);
        $service = new Adminer($service);
        $service->resetAdminerPwd(array(
            'eaMobile' => $params['eaMobile'],
            'eaPassword' => $params['newPwd']
        ));

        return true;
    }

    /**
     * 获取post字符串数据
     *
     * @param array $paramArr 参数键值数组
     *
     * @return array
     */
    private function obtainPostStrval($paramArr)
    {

        $return = [];
        foreach ($paramArr as $key) {
            $return[$key] = I('post.' . $key, '', 'strval');
        }

        return $return;
    }
}

