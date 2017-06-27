<?php
/**
 * 系统设置-账号信息获取
 * 鲜彤 2016年08月01日16:15:17
 *
 * @update 2016-10-12 zhuxun37 修改企业信息字段以及整理方式
 */

namespace Apicp\Controller\SysSetting;

use VcySDK\Enterprise;
use VcySDK\Service;
use VcySDK\Member;

class AccountController extends AbstractController
{

    public function Index()
    {

        // 调用UC接口，获取账号信息
        $enterpriseSDK = new Enterprise(Service::instance());
        $detail = $enterpriseSDK->detail();

        // 组建返回信息
        $this->_result = array(
            'epName' => $detail['epName'],
            'epEnumber' => $detail['epEnumber'],
            'epCompanysize' => $detail['epCompanysize'],
            'epProvince' => $detail['epProvince'],
            'epCity' => $detail['epCity'],
            'epCounty' => $detail['epCounty'],
            'epIndustry' => $detail['epIndustry'],
            'epContacter' => $detail['epContacter'],
            'epContactmobile' => $detail['epContactmobile'],
            'epContactemail' => $detail['epContactemail'],
            'epMembercount' => $this->_getMemCount(),
            'epCreated' => $detail['epCreated']
        );

        return true;
    }

    /**
     * 获取用户总数
     *
     * @return int 返回用户总数
     */
    protected function _getMemCount()
    {

        // 获取用户列表
        $memberSDK = new Member(Service::instance());
        $memList = $memberSDK->listAll(array(), 1, 1);

        // 返回用户总数
        return isset($memList['total']) ? intval($memList['total']) : 0;
    }
}
