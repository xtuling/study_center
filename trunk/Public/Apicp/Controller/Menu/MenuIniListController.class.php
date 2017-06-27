<?php
/**
 * 菜单设置-获取初始菜单
 * CreateBy：何岳龙
 * Date：2016-08-01
 */

namespace Apicp\Controller\Menu;

use Com\QyMenu;

class MenuIniListController extends AbstractController
{

    public function Index()
    {

        // 获取初始化菜单
        $menu = QyMenu::instance()->getMenu();
        $this->_result = array(
            'list' => $menu
        );

        return true;
    }

}
