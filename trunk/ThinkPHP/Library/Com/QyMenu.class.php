<?php
/**
 * QyMenu.class.php
 * 初始化菜单类
 * $Author$
 */
namespace Com;

use VcySDK\EnterprisePlugin;
use VcySDK\Exception;
use VcySDK\Service;

class QyMenu
{

    protected $_sdkPlugin = null;

    /**
     * 获取一个验证权限类的实例
     * @return QyMenu
     */
    public static function &instance()
    {

        static $instance = null;
        if (! $instance) {
            $instance = new QyMenu();
        }

        return $instance;
    }

    public function __construct()
    {

        $this->_sdkPlugin = new EnterprisePlugin(Service::instance());
    }

    /**
     * 获取初始化菜单
     *
     * @return array
     */
    public function getMenu()
    {

        // 获取所有菜单
        $menu = C('MENU');

        try {
            // 获取所有应用列表
            $pluginList = $this->_sdkPlugin->listAll();
        } catch (Exception $e) {
            $pluginList = array();
        }

        // 循环应用，并取出已安装应用对应的配置菜单
        $app_menu = array();
        foreach ($pluginList as $_plugin) {
            // 如果不是已安装的就跳过
            if (! $this->_sdkPlugin->isInstall($_plugin['available'])) {
                continue;
            }

            $_app_menu_dir = APP_PATH . '..' . D_S . ucfirst($_plugin['plIdentifier']) . D_S . 'Common' . D_S . 'Conf' . D_S . 'menu.php';
            // 如果文件不存在就跳过
            if (! file_exists($_app_menu_dir)) {
                continue;
            }

            // 应用对应的Menu
            $_app_menu_temp = load_config($_app_menu_dir);
            if (isset($_app_menu_temp['ManageMenu']) && ! empty($_app_menu_temp['ManageMenu'])) {
                $app_menu[] = $_app_menu_temp['ManageMenu'];
            }
        }

        $menu[1]['subMenu'] = $app_menu;

        return $menu;
    }


    /**
     * 超级管理员获取菜单
     *
     * @param array $list 初始化菜单
     *
     * @return array
     */
    public function SuperAdmin($list = array())
    {

        // 初始化菜单
        $data = array();
        // 遍历菜单
        foreach ($list as $key => $item) {
            $item['auth'] = 1;
            $data[] = $item;
            // 如果子集菜单不为空
            if (! empty($item['subMenu'])) {
                // 递归获取菜单
                $data[$key]['subMenu'] = $this->SuperAdmin($item['subMenu']);
            }
        }

        return $data;
    }

}
