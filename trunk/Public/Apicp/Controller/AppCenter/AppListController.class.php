<?php
/**
 * 应用中心-获取应用列表接口
 * CreateBy：原习斌
 * Date：2016-07-29
 *
 * @update 2016-10-12 zhuxun37 修正程序中出现的常量
 */

namespace Apicp\Controller\AppCenter;

class AppListController extends AbstractController
{

    public function Index()
    {

        // SDK获取应用列表
        $pluginList = $this->_pluginSDK->listAll(array(), 1, self::PERPAGE);

        $this->_result = array();

        // 循环列表，分离格式
        foreach ($pluginList as $_plugin) {
            $groupId = $_plugin['pgGroupid'];
            // 分组数据
            $this->_generateGroup($this->_result[$groupId], $_plugin);
            // 应用信息
            $this->_result[$groupId]['plugins'][] = $this->_generatePlugin($_plugin);
        }
        sort($this->_result);

        $this->_result = [
            'app_list' => $this->_result,
            'contact_identifier' => $this->config['contactIdentifier']
        ];

        return true;
    }

    /**
     * 重新组织应用分组信息
     *
     * @param array $group  分组信息
     * @param array $plugin 应用信息
     *
     * @return bool
     */
    protected function _generateGroup(&$group, $plugin)
    {

        // 如果分组信息不为空
        if (! empty($group)) {
            return true;
        }

        /**
         * pg_name 分组(套件)名称
         * pgGroupid 分组(套件)ID
         * installUrl 分组(套件)安装Url
         * plugins 当前分组(套件)下应用列表
         */
        $group = array(
            'pg_name' => $plugin['pgName'],
            'pgGroupid' => $plugin['pgGroupid'],
            'installUrl' => $this->_getInstallUrl(array('suiteId' => $plugin['qysSuiteid'])),
            'plugins' => array(),
        );

        return true;
    }

}

