<?php
/**
 * ListController.class.php
 * 同事圈列表
 * User: heyuelong
 * Date:2017年4月26日16:33:44
 */

namespace Api\Controller\Workmate;

use Common\Service\CircleService;

class ListController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {
        $params = I('get.');

        // 实例化同事圈信息表
        $service = new CircleService();

        // 获取列表
        $list = $service->get_circle_list($params, $this->uid);

        $this->_result = $list;

        return true;
    }

}

