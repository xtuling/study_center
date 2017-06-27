<?php
/**
 * 活动列表接口
 * User: daijun
 * Date: 2017-05-05
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;

class ListController extends AbstractController
{

    /**
     * @var  ActivityService 活动信息表
     */
    protected $_activity_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化活动信息表
        $this->_activity_serv = new ActivityService();

        return true;
    }


    public function Index_post()
    {
        // 获取参数
        $params = I('post.');

        // 获取返回数据
        $this->_result = $this->_activity_serv->get_list_admin($params);

        return true;
    }

}
