<?php
/**
 * Created by IntelliJ IDEA.
 * 企业积分策略列表查询接口
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;
use VcySDK\EnterprisePlugin;
use VcySDK\Service;

class StrategyListController extends AbstractController
{
    public function Index()
    {

        $page = I('post.page', 1);
        $pageSize = I('post.limit', 20);
        $identifier = I('post.identifier');
        $identifierName = cfg('IDENTIFIER_NAME');
        // 开始的数组键值, 每页数量
        list($startKey, $perPage, $page) = page_limit($page, $pageSize);

        $cache = Cache::instance();
        $strategySetting = $cache->get('Common.StrategySetting');

        $pluginSdk = new EnterprisePlugin(Service::instance());
        $pluginList = $pluginSdk->listAll();
        $installedPlugin = [];
        foreach ($pluginList as $_plugin) {
            if ($_plugin['available'] == $pluginSdk::AVAILABLE_OPEN) {
                $installedPlugin[] = $_plugin['plIdentifier'];
            }
        }

        // 获取策略的应用标识
        $list = $strategySetting['eirsRuleSetList'];
        foreach ($list as $key => &$_item) {
            $itemIdentifier = explode('_', $_item['irKey']);
            if (!in_array($itemIdentifier[0], $installedPlugin)) {
                unset($list[$key]);
                continue;
            }
            // 如果有指定应用, 则过滤
            if (!empty($identifier) && ($identifier != $itemIdentifier[0])) {
                unset($list[$key]);
                continue;
            }
            $_item['identifier'] = $itemIdentifier[0];
        }
        $this->_result['total'] = count($list);

        // 分页 和 写入应用名称
        $list = array_slice($list, $startKey, $perPage);
        foreach ($list as &$_item) {
            $_item['pluginName'] = $identifierName[$_item['identifier']];
        }
        $this->_result['pageNum'] = $page;
        $this->_result['list'] = array_values($list);

        return true;
    }

}
