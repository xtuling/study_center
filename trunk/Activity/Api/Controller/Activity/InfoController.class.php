<?php
/**
 * 【活动中心-手机端】获取活动详情
 * @author: 蔡建华
 * @date :  2017-05-8
 * @version $Id$
 */

namespace Api\Controller\Activity;

use Common\Service\ActivityService;
use Common\Service\RightService;
use Common\Model\ActivityModel;

class InfoController extends AbstractController
{
    /**
     * @var bool 接口不强制登录
     */
    protected $_require_login = false;

    /**
     * @var  ActivityService 活动yService对象
     */
    protected $_activity_serv;

    /**
     * @var  RightService  实例化权限表对象
     */
    protected $_right_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化权限表
        $this->_activity_serv = new ActivityService();

        $this->_right_serv = new RightService();
        return true;
    }

    /**
     * @return bool
     */
    public function Index_get()
    {
        $params = I('get.');

        // 获取参数
        $ac_id = $params['ac_id'] ? intval($params['ac_id']) : 0;

        // 验证参数
        if (empty($ac_id)) {
            E("_EMPTY_ACTIVITY_ID");
            return false;
        }

        // 获取活动详细内容
        $activity = $this->_activity_serv->get_by_conds(array(
            'activity_status' => ActivityModel::ACTIVITY_PUBLISH,
            "ac_id" => $ac_id
        ));

        // 数据不存在时抛错
        if (empty($activity)) {
            E("_ERR_DATA_NOT_EXIST");
            return false;
        }

        //判断活动权限
        if ($activity['is_all'] != ActivityModel::ACTIVITY_COMPANY_ALL) {
            $right = $this->_right_serv->list_by_conds(array('ac_id' => $ac_id));
            if (!empty($right)) {
                if ($this->_right_serv->check_get_quit($right, $this->uid)) {
                    E("_ERR_COMMENT_QUIT");
                    return false;
                }
            } else {
                E("_ERR_COMMENT_QUIT");
                return false;
            }
        }

        // 格式化详情数据
        $this->_result = $this->_activity_serv->format_detail_data($activity, $this->uid);

        return true;
    }

}
