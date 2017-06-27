<?php
/**
 * 系统设置-更改环境设置
 * CreateBy：何岳龙
 * Date：2016年8月1日16:39:22
 */
namespace Apicp\Controller\SysSetting;

use VcySDK\Enterprise;
use VcySDK\Service;

class SetEnvController extends AbstractController
{

    public function Index()
    {

        // 获取企业信息
        $epName = I("post.epName");
        // 获取企业简介
        $epIntroduce = I("post.epIntroduce");
        // 初始化
        $enterpriseSDK = new Enterprise(Service::instance());

        // 修改
        $data = $enterpriseSDK->modify(array('epName' => $epName, 'epIntroduce' => $epIntroduce));

        // 是否成功
        if (empty($data['epId'])) {
            $this->_set_error("_ERR_SETENV");
            return false;
        }

        return true;
    }

}
