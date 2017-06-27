<?php
/**
 * Created by IntelliJ IDEA.
 * 根据手机号查询所属全部企业、管理员列表
 * User: zhoutao
 * Date: 16/9/22
 * Time: 上午10:28
 */

namespace Apicp\Controller\Admin;

use Common\Common\Sms;
use VcySDK\Adminer;
use VcySDK\Service;

class EnterpriseAdminerListController extends AbstractAnonymousController
{

    public function Index()
    {

        $params = $this->obtainPostStrval([
            'eaMobile',
            'mobileCode',
            'smsSign'
        ]);

        // 验证验证码信息
        if (! Sms::instance()->verifyCodeLocal($params['eaMobile'], $params['mobileCode'], $params['smsSign'])) {
            $this->_set_error('_ERR_SMS_CODE_ERROR');
            return false;
        }

        // 获取企业列表数据
        $sdk = new Adminer(Service::instance());
        $this->_result = $sdk->enterpriseAdminerList(['eaMobile' => $params['eaMobile']]);

        return true;
    }

    /**
     * 获取post字符串数据
     *
     * @param $paramArr
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
