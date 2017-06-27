<?php
/**
 * Created by IntelliJ IDEA.
 * 完善HR账号信息
 * User: zhoutao
 * Date: 16/9/22
 * Time: 上午10:28
 */

namespace Apicp\Controller\Admin;

use VcySDK\Adminer;
use VcySDK\Service;
use VcySDK\Sms;

class CompleteAccountInfoController extends AbstractController
{

    public function index()
    {

        $params = $this->obtainPostStrval([
            'eaRealname',
            'eaEmail',
            'eaMobile',
            'eaPassword',
            'epIndustry',
            'mobileCode'
        ]);
        $params['epCompanysize'] = I('post.epCompanysize');

        // 验证验证码信息
        $smsSdk = new Sms(Service::instance());
        $smsSdk->verifyCode(['scMobile' => $params['eaMobile'], 'scCode' => $params['mobileCode']]);

        // 提交更新
        $params['eaId'] = $this->_login->user['eaId'];
        $adminSdk = new Adminer(Service::instance());
        $enterpriseData = $adminSdk->completeOwnerInfo($params);

        $this->_result = $enterpriseData;
        return true;
    }

    /**
     * 获取post字符串数据
     *
     * @param array $paramArr 参数键值
     *
     * @return array | bool
     */
    private function obtainPostStrval($paramArr)
    {

        $keyName = [
            'eaRealname' => L('CONTACTER'),
            'eaEmail' => L('CONTACTER_EMAIL'),
            'eaMobile' => L('MOBILE'),
            'eaPassword' => L('PASSWORD'),
            'epIndustry' => L('INDUSTRY'),
            'mobileCode' => L('MOBILE_CODE')
        ];

        $return = [];
        foreach ($paramArr as $key) {
            $return[$key] = I('post.' . $key, '', 'strval');
            if ($key != 'eaEmail' && empty($return[$key])) {
                E(L('PLEASE_INPUT') . $keyName[$key]);
                return false;
            }
        }

        return $return;
    }
}
