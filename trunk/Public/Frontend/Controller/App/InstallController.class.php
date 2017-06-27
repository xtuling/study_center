<?php
/**
 * 安装应用类
 * CreateBy: 原习斌
 * Date: 2016-08-15
 * Time: 11:46:12
 */

namespace Frontend\Controller\App;

use VcySDK\EnterprisePlugin;
use VcySDK\Service;

class InstallController extends AbstractController
{

    protected $_require_login = false;

    public function Index()
    {

        // 套件ID
        $suiteId = I('get.suiteId');
        // 应用ID
        $appId = I('get.appid');
        $appId = empty($appId) ? array() : array($appId);
        // 是否跳过添加企业标识 (官网授权逻辑)
        $skipEnumber = I('get.skipEnumber');
        if (!empty($skipEnumber) && $skipEnumber = 1) {
            $enumber = '';
        } else {
            // 企业标识
            $enumber = I('get.enumber');
            if (empty($enumber)) {
                $enumber = QY_DOMAIN;
            }
        }
        // 修改配置
        Service::instance()->setConfig(array('enumber' => $enumber));

        // 请求的来源地址
        $callback_url = cfg('PROTOCAL') . $_SERVER['HTTP_HOST'] . '/admincp/#/login';
        // 调用SDK获取授权地址
        $epPluginSDK = new EnterprisePlugin(Service::instance());
        $authUrl = $epPluginSDK->getSuiteInstallUrl($suiteId, $callback_url, $appId);

        redirect($authUrl);
    }
}
