<?php
/**
 * 管理员角色详情获取
 * InfoController.class.php
 *
 */
namespace Apicp\Controller\AdminRole;

class InfoController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $earId = I('post.earId'); // 管理员角色id

        // 管理员角色ID非空验证
        if (empty($earId)) {
            $this->_set_error('_ERR_ADMIN_ROLE_ID_EMPTY');
            return false;
        }

        // 拼接接口所需数据：管理员角色id
        $condition = array(
            'earId' => $earId
        );

        // 调用UC接口，查询管理员详情
        $this->_result = $this->_sdkRole->detail($condition);

        if (! $this->_result) {
            $this->_set_error("_ERR_ADMIN_ROLE_NOT_EXIST");
            return false;
        }

        return true;
    }

}
