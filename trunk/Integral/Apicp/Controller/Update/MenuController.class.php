<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhoutao
 * Date: 2016/12/20
 * Time: 下午3:52
 */

namespace Apicp\Controller\Update;

use VcySDK\Enterprise;
use VcySDK\EnterprisePlugin;
use VcySDK\Service;
use VcySDK\WxQy\Menu;

class MenuController extends AbstractController
{
    /** 应用开启 */
    const PLUGIN_AVAILABLE_OPEN = 1;

    public function index()
    {
        $page = I('post.page', 1);
        if (!cfg('BOSS_OPEN_UPDATE_SWITCH')) {
            return false;
        }

        // 初始化SDK
        $enterpriseSdk = new Enterprise(Service::instance());
        $enterprisePluginSdk = new EnterprisePlugin(Service::instance());
        $menuSdk = new Menu(Service::instance());

        // 企业列表
        $enterpriseList = $enterpriseSdk->listAll([], $page, 30);
        foreach ($enterpriseList['list'] as $item) {
            // 修改SDK
            Service::instance()->setConfig([
                'enumber' => $item['epEnumber']
            ]);
            // 企业应用列表
            $pluginList = $enterprisePluginSdk->listAll();
            // 是否开启了该应用
            foreach ($pluginList as $plugin) {
                if (($plugin['plIdentifier'] == APP_IDENTIFIER)
                    && $plugin['available'] == self::PLUGIN_AVAILABLE_OPEN) {
                    // 升级应用菜单
                    $updateData = cfg('MENU_UPDATE_DATA');
                    $menuSdk->create($updateData);
                }
            }
        }

        return true;
    }
}
