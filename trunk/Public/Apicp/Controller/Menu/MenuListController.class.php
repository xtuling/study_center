<?php
/**
 * 菜单设置-获取管理员菜单信息
 * CreateBy：何岳龙
 * Date：2016-08-01
 */

namespace Apicp\Controller\Menu;

use Common\Service\CommonHidemenuService;
use VcySDK\Adminer;
use VcySDK\AdminerRole;
use VcySDK\Service;

class MenuListController extends AbstractController
{

    public function Index()
    {

        // 拼接接口所需数据：管理员角色id
        $condition = array(
            'earId' => $this->_login->user['earId']
        );

        // 调用UC接口，查询管理员详情
        $sdkRole = new AdminerRole(Service::instance());
        $this->_result = $sdkRole->detail($condition);

        // 定制企业用户需要隐藏的菜单
        $servHideMenu = new CommonHidemenuService();
        $hideMenu = $servHideMenu->get_by_conds([]);
        $this->_result['hideMenu'] = empty($hideMenu['menus']) ? [] : unserialize($hideMenu['menus']) ;

        // 修改超级官员称谓
        if ($this->_login->is_super_admin()) {
            $this->_result['earName'] = cfg('ADMIN_ROLE_PROTECT_NAME');
        }

        if (! $this->_result) {
            $this->_set_error("_ERR_ADMIN_ROLE_NOT_EXIST");
            return false;
        }

        return true;
    }

}
