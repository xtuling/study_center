<?php
/**
 * 系统设置-更新缓存
 * 鲜彤 2016年08月02日18:10:25
 *
 * @update 2016-10-12 zhuxun37 修改调用方法;
 */

namespace Apicp\Controller\SysSetting;

use Com\Rpc;
use VcySDK\Service;
use VcySDK\EnterprisePlugin;

class RefreshCacheController extends AbstractController
{

    public function Index()
    {

        // 获取安装的应用列表
        $pluginSDK = new EnterprisePlugin(Service::instance());
        $pluginList = $pluginSDK->listAll();
        // RPC通知应用清除缓存
        foreach ($pluginList as $_plugin) {
            // 已安装的才清缓存
            if ($pluginSDK->isInstall($_plugin['allowStatus'])) {
                continue;
            }

            $identifier = ucfirst($_plugin['plIdentifier']);
            $rpc = Rpc::phprpc($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . QY_DOMAIN . '/' . $identifier . '/' . 'Rpc/Cache/Clear');
            call_user_func(array($rpc, 'Index'));
        }

        return true;
    }

}


