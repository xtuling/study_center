<?php
/**
 * MyCircleController.class.php
 * 我的同事圈详情
 * User: heyuelong
 * Date:2017年4月26日18:07:37
 */

namespace Api\Controller\Workmate;

use Common\Model\CircleModel;
use Common\Service\CircleService;

class MyCircleController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {

        $id = I('get.pid');

        // 如果话题ID不存在
        if (empty($id)) {

            $this->_set_error('_EMPTY_CIRCLE_ID');

            return false;
        }

        // 实例化话题表
        $service = new CircleService();

        // 获取话题详情
        $info = $service->get_by_conds(
            array(
                'id' => $id,
                'pid' => CircleModel::CIRCLE_PID,
                'uid' => $this->uid
            )
        );

        // 如果话题详情不存在
        if (empty($info)) {

            $this->_set_error('_EMPTY_CIRCLE_INFO');

            return false;
        }

        // 获取详情格式化后的数据
        $this->_result = $service->format_my_circle($info);

        return true;
    }

}

