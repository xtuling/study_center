<?php
/**
 * 【活动中心-手机端】获取活动列表
 * @author: 蔡建华
 * @date :  2017-05-5
 * @version $Id$
 */

namespace Api\Controller\Activity;

use  Common\Service\ActivityService;
use Common\Service\RightService;

class ListController extends AbstractController
{
    /** @var  ActivityService */
    protected $_activity_serv;

    /** @var  RightService */
    protected $_right_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        // 实例化活动表 活动权限表
        $this->_activity_serv = new ActivityService();
        $this->_right_serv = new RightService();

        return true;
    }

    /**
     * 主方法
     */
    public function Index_get()
    {
        // 获取数据
        $params = I('get.');

        // 获取当前用户部门，标签，职位
        $right = $this->_right_serv->get_by_right($this->_login->user);

        // 获取返回数据
        $this->_result = $this->_activity_serv->get_list_active($params, $right);

        return true;
    }

}

