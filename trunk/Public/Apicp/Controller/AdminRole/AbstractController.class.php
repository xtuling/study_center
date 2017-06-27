<?php
/**
 * 管理员角色基类
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\AdminRole;

use VcySDK\Service;
use VcySDK\AdminerRole;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * @type AdminerRole
     */
    protected $_sdkRole = null;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        // 调用UC接口，查询管理员角色详情
        $this->_sdkRole = new AdminerRole(Service::instance());
        return true;
    }

    /**
     * 判断是否为保护角色名称
     *
     * @param string $name 角色名称
     *
     * @return bool
     */
    protected function _isProtectName($name)
    {

        return cfg('ADMIN_ROLE_PROTECT_NAME') == $name;
    }

}
