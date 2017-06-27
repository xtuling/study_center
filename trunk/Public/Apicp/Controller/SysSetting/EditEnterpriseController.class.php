<?php
/**
 * 后台登录-普通登陆：完善企业信息
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年8月2日14:26:06
 *
 * @update 2016-10-12 zhuxun37 修改数据获取方式
 */

namespace Apicp\Controller\SysSetting;

use Common\Common\Sms;
use VcySDK\Enterprise;
use VcySDK\Service;

class EditEnterpriseController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $fields = array(
            'epName',
            'epIndustry',
            'epCompanysize',
            'epContacter',
            'epContactmobile',
            'epContactemail',
            'epProvince',
            'epCity',
            'epArea',
            'epPostalCode',
            'epAddress',
            'mobileCode'
        );
        extract_field($postData, $fields, I('post.'));

        if (!empty($postData['mobileCode'])) {
            // 验证手机验证码
            $service = &Service::instance();
            // 实例化短信
            $smsService = new Sms($service);
            // 验证验证码信息
            $smsService->verifyCodeSDK($postData['epContactmobile'], $postData['mobileCode']);
        } else {
            // 否则不让更改手机号
            unset($postData['epContactmobile']);
        }

        // 更新
        $enterpriseSdk = new Enterprise(Service::instance());
        $this->_result = $enterpriseSdk->modify($postData);

        return true;
    }

}
