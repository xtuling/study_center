<?php
/**
 * 获取活动详情
 * User: daijun
 * Date: 2017-05-09
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;
use Common\Service\RightService;

class DetailController extends AbstractController
{

    /**
     * @var  ActivityService 活动信息表
     */
    protected $_activity_serv;

    /**
     * @var RightService 权限信息表
     */
    protected $_right_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化活动信息表
        $this->_activity_serv = new ActivityService();

        $this->_right_serv = new RightService();

        return true;
    }

    public function Index_post()
    {

        $ac_id = I('post.ac_id', 0, 'intval');

        // 参数验证
        if (empty($ac_id)) {
            E('_EMPTY_ACTIVITY_ID');
            return false;
        }

        // 获取活动详情
        $data = $this->_activity_serv->get($ac_id);

        if (empty($data)) {
            E('_ERR_ACTIVITY_DATA');
            return false;
        }

        // 格式化详情数据
        $result = $this->_activity_serv->format_activity_detail($data);

        // 获取权限数据
        list($right_list, $right_data) = $this->_right_serv->get_data(array('ac_id' => $ac_id));

        $result['right'] = $right_data;

        $this->_result = $result;

        return true;
    }

}
