<?php
/**
 * 提前终止活动
 * User: daijun
 * Date: 2017-05-05
 */

namespace Apicp\Controller\Activity;

use Common\Model\ActivityModel;
use Common\Service\ActivityService;

class StopController extends AbstractController
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

        // 判断该活动是否是进行中的活动
        if ($data['activity_status'] == ActivityModel::ACTIVITY_PUBLISH && $data['begin_time'] <= MILLI_TIME && ($data['end_time'] > MILLI_TIME || $data['begin_time'] <= MILLI_TIME && $data['end_time'] == 0)) {
            // 组装更新数据
            $u_data = array(
                'activity_status' => ActivityModel::ACTIVITY_STOP,
                'last_time' => MILLI_TIME,
            );

            //  判断是否更新成功
            if (!$this->_activity_serv->update($ac_id, $u_data)) {

                return false;
            }

        } else {
            E('_ERR_ACTIVITY_NOT_ING');

            return false;
        }

        return true;
    }
}
