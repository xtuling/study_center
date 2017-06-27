<?php
/**
 * 管理员删除
 * 鲜彤 2016年08月01日10:13:37
 */
namespace Apicp\Controller\AdminManager;

class DeleteController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $eaIds = (array)I('post.eaIds'); // 管理员id

        // 管理员ID非空验证
        if (empty($eaIds)) {
            $this->_set_error('_ERR_ADMINID');
            return false;
        }

        // 管理员禁止删除自身验证
        if (in_array($this->_login->user['eaId'], $eaIds)) {
            $this->_set_error('_ERR_DEL_SELF');
            return false;
        }

        // 拼接接口所需数据
        $condition = array(
            'eaIds' => $eaIds
        );

        // 调用UC接口，获取管理员信息
        $this->_sdkAdminer->del($condition);

        return true;
    }

}
