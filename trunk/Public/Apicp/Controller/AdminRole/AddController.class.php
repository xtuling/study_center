<?php
/**
 * 新增管理员角色
 * AddController.class.php
 *
 */
namespace Apicp\Controller\AdminRole;

use Com\Validator;

class AddController extends AbstractController
{

    public function Index()
    {

        // 数据接收
        $earName = I('post.earName', '', 'trim');
        $earCpmenu = I('post.earCpmenu', '{}', 'htmlspecialchars_decode');
        $earDesc = I('post.earDesc');

        // 如果是保护角色名称
        if ($this->_isProtectName($earName)) {
            $this->_set_error('_ERR_ADMIN_ROLE_NAME_PROTECT');
            return false;
        }

        // 角色名称不能为空
        if (empty($earName)) {
            $this->_set_error("_ERR_ADMIN_ROLE_NAME_EMPTY");
            return false;
        }
        if (! Validator::is_realname($earName, 3, 255)) {
            $this->_set_error(L('_ERR_ADMIN_ROLE_NAME_LENGTH_INVALID', array('min' => 3, 'max' => 255)));
            return false;
        }

        // 判断权限菜单
        if (empty($earCpmenu)) {
            $earCpmenu = '{}';
        }

        // 调用UC接口，添加管理员角色
        $role = $this->_sdkRole->add(array(
            'earName' => $earName,
            'earCpmenu' => $earCpmenu,
            'earDesc' => $earDesc
        ));

        // 添加失败处理
        if (empty($role['earId'])) {
            $this->_set_error("_ERR_ADMIN_ROLE_ADD_FAILED");
            return false;
        }

        $this->_result = $role;

        return true;
    }

}

