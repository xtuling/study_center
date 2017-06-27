<?php
/**
 * 管理员角色列表
 * ListController.class.php
 *
 */
namespace Apicp\Controller\AdminRole;

use VcySDK\AdminerRole;

class ListController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $pageSize = I('post.limit', 1500); // 每页条数
        $pageNum = I('post.page'); // 页码
        $earId = I('post.earId'); // 角色ID

        // 查询条件数组拼接
        $condition = array(
            'earId' => $earId,
            'filterType' => AdminerRole::ROLE_LIST_FILTER_TYPE,
        );

        // 调用UC接口，查询符合条件的列表
        $result = $this->_sdkRole->listAll($condition, $pageNum, $pageSize);

        // 输出
        $this->_result = array(
            'total' => $result['total'],
            'limit' => $pageSize,
            'page' => $pageNum,
            'list' => $result['list']
        );

        return true;
    }

}

