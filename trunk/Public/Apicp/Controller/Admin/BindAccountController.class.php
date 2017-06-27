<?php
/**
 * Created by IntelliJ IDEA.
 * 绑定HR账号
 * User: zhoutao
 * Date: 16/9/22
 * Time: 上午10:28
 */

namespace Apicp\Controller\Admin;

use VcySDK\Adminer;
use VcySDK\Service;
use VcySDK\Sms;

class BindAccountController extends AbstractController
{

    public function Index()
    {

        extract_field($params, array(
            'eaMobile',
            'eaPassword',
            'eaRealname',
            'mobileCode',
            'imgCaptchaToken',
            'imgCaptchaCode'
        ));

        // 验证验证码信息
        $smsSdk = new Sms(Service::instance());
        $smsSdk->verifyCode(['scMobile' => $params['eaMobile'], 'scCode' => $params['mobileCode']]);

        // 管理员绑定手机号
        $adminSdk = new Adminer(Service::instance());
        $bindMobileData = [
            'eaId' => $this->_login->user['eaId'],
            'eaMobile' => $params['eaMobile'],
            'eaRealname' => $params['eaRealname']
        ];
        // 如果密码不为空, 则
        if (! empty($params['eaPassword'])) {
            $bindMobileData['eaPassword'] = $params['eaPassword'];
        }
        $this->_result = $adminSdk->bindMobile($bindMobileData);

        // 新绑定的管理账号
        $this->_bindAccount();

        return true;
    }

    /**
     * 更新 cookie
     *
     * @return bool
     */
    protected function _bindAccount()
    {

        $newEaId = $this->_result['adminerInfo']['eaId'];
        // 如果绑定后的账号eaId未改变, 则不需要写cookie
        if ($newEaId == $this->_login->user['eaId']) {
            return true;
        }

        $enumber = $this->_result['enterpriseInfo']['epEnumber'];
        $this->_login->flushAuth($newEaId, $this->_login->getAuthPwd($newEaId, $enumber), $enumber);
        return true;
    }

}
