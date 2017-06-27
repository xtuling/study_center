<?php
/**
 * 管理员详情获取
 * 鲜彤 2016年08月01日10:43:42
 */
namespace Apicp\Controller\AdminManager;

class InfoController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $eaId = I('post.eaId'); // 管理员id

        // 管理员ID非空验证
        if (empty($eaId)) {
            $this->_set_error('_ERR_ADMINID');
            return false;
        }

        // 拼接接口所需数据：管理员id
        $condition = array(
            'eaId' => $eaId
        );

        // 调用UC接口，查询管理员详情
        $res = $this->_sdkAdminer->fetch($condition);

        if (! $res) {
            $this->_set_error("_ERR_INFO_ADMIN");
            return false;
        }

        // 反序列化菜单权限
        $res['eaCpmenu'] = unserialize($res['eaCpmenu']);
        $this->_result = $res;

        return true;
    }

}
