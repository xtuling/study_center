<?php
/**
 * Created by IntelliJ IDEA.
 * 有积分规则的应用列表
 * User: zhoutao
 * Date: 2016-12-14
 * Time: 14:47:37
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;
use VcySDK\EnterprisePlugin;
use VcySDK\Service;

class AppListController extends AbstractController
{

    public function Index()
    {
        // 获取企业安装的应用
        $pluginSdk = new EnterprisePlugin(Service::instance());
        $pluginList = $pluginSdk->listAll();
        $installedPlugin = [];
        foreach ($pluginList as $_plugin) {
            if ($_plugin['available'] == $pluginSdk::AVAILABLE_OPEN) {
                $installedPlugin[] = $_plugin['plIdentifier'];
            }
        }

        $cache = Cache::instance();
        $strategySetting = $cache->get('Common.StrategySetting');
        // 获取策略的应用标识
        $list = $strategySetting['eirsRuleSetList'];
        foreach ($list as $key => &$_item) {
            $itemIdentifier = explode('_', $_item['irKey']);
            if (!in_array($itemIdentifier[0], $installedPlugin)) {
                unset($list[$key]);
                continue;
            }
            $_item['identifier'] = $itemIdentifier[0];
        }

        // 有积分规则的应用
        $haveRulePlugin = array_unique(array_column($list, 'identifier'));
        foreach ($pluginList as $plugin) {
            if (in_array($plugin['plIdentifier'], $haveRulePlugin)) {
                $this->_result[$plugin['plPluginid']] = [
                    'pl_identifier' => $plugin['plIdentifier'],
                    'pl_name' => $plugin['plName'],
                ];
            }
        }

        return true;
    }
}
