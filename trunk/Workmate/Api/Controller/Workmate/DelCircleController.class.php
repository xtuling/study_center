<?php
/**
 * DelCircleController.class.php
 * 删除我发布的同事圈
 * User: heyuelong
 * Date:2017年4月26日18:07:37
 */

namespace Api\Controller\Workmate;

use Common\Service\CircleService;

class DelCircleController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {
        // 实例化同事圈表
        $service = new CircleService();

        $id = I('get.pid');

        // 删除同事圈
        if (!$service->del_circle($id, $this->uid)) {

            return false;
        }

        // 删除成功后同步更新收藏状态
        $service->update_collection($id);

        $this->_result = array();

        return true;
    }

}
