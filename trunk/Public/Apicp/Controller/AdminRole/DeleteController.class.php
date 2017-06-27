<?php
/**
 * 管理员角色删除
 * DeleteController.class.php
 */
namespace Apicp\Controller\AdminRole;

class DeleteController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $earId = I('post.earId'); // 管理员角色id

        // 管理员ID非空验证
        if (empty($earId)) {
            $this->_set_error('_ERR_ADMIN_ROLE_ID_EMPTY');
            return false;
        }

        // 拼接接口所需数据
        $condition = array(
            'earId' => $earId
        );

        // 调用UC接口，获取管理员信息
        $this->_sdkRole->delete($condition);

        return true;
    }

}
