<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\AppCenter;

use VcySDK\Service;
use VcySDK\EnterprisePlugin;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    // 已安装
    const AVAILABLE_INSTALLED = 1;

    // 未安装
    const AVAILABLE_UNINSTALL = 2;

    // 已上架
    const SHELVED = 1;

    // 未上架
    const NOT_SHELVE = 2;

    // 常量
    const PERPAGE = 150;

    /**
     * VcySDK 应用操作类
     *
     * @type EnterprisePlugin
     */
    protected $_pluginSDK = null;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_pluginSDK = new EnterprisePlugin($serv);

        return true;
    }

    /**
     * 重新组织应用信息数组
     *
     * @param array $plugin  应用信息
     *
     * @return bool
     */
    protected function _generatePlugin($plugin)
    {

        // 安装应用的地址
        $appInstallParams = array(
            'suiteId' => $plugin['qysSuiteid'],
            'appid' => $plugin['appid']
        );

        /**
         * customApp 是否标准应用 1: 是 2: 否
         * pl_pluginid 应用ID
         * qysSuiteid 套件ID
         * pl_identifier 应用标识
         * pl_name 应用名称
         * pl_icon 应用图标
         * plTagName 应用标签名称
         * pl_description 应用描述
         * is_installed 是否安装, 1 已安装; 2 未安装
         * installUrl 应用安装Url
         */
        return array(
            'customApp' => $plugin['customApp'],
            'pl_pluginid' => $plugin['plPluginid'],
            'qysSuiteid' => $plugin['qysSuiteid'],
            'pl_identifier' => $plugin['plIdentifier'],
            'pl_name' => $plugin['plName'],
            'pl_icon' => $plugin['plIcon'],
            'pl_tag_name' => $plugin['plTagName'],
            'pl_description' => $plugin['plDescription'],
            'is_installed' => $this->_pluginSDK->isInstall($plugin['available']) ? self::AVAILABLE_INSTALLED : self::AVAILABLE_UNINSTALL,
            'installUrl' => $this->_pluginSDK->isInstall($plugin['available']) ? '' : $this->_getInstallUrl($appInstallParams)
        );
    }

    /**
     * 获取安装URL
     *
     * @param array $params 安装参数
     *
     * @return string
     */
    protected function _getInstallUrl($params)
    {

        $url = oaUrl('/Frontend/App/Install/Index?' . http_build_query($params));
        return $url . (false === stripos($url, '?') ? '?' : '&') . '_identifier=common';
    }

}
