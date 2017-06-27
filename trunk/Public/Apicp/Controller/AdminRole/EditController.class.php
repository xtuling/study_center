<?php
/**
 * 编辑管理员角色信息
 * EditController.class.php
 *
 */
namespace Apicp\Controller\AdminRole;

use Com\Validator;

class EditController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $earId = I('post.earId');
        $earName = I('post.earName', '', 'trim');
        $earCpmenu = I('post.earCpmenu', '{}', 'htmlspecialchars_decode');
        $earDesc = I('post.earDesc');

        // 管理员ID非空验证
        if (empty($earId)) {
            $this->_set_error('_ERR_ADMIN_ROLE_ID_EMPTY');
            return false;
        }

        // 如果是保护角色名称
        if ($this->_isProtectName($earName)) {
            $this->_set_error('_ERR_ADMIN_ROLE_NAME_PROTECT');
            return false;
        }

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

        // 获取修改管理员的参数
        $param = array(
            'earName' => $earName,
            'earCpmenu' => $earCpmenu,
            'earId' => $earId,
            'earDesc' => $earDesc
        );

        // 调用UC，编辑管理员提交
        $this->_result = $this->_sdkRole->modify($param);

        return true;
    }

}

