<?php
/**
 * 【活动中心-手机端】取消收藏活动
 * @author: Xtong
 * @date :  2017年06月03日
 * @version $Id$
 */

namespace Api\Controller\Activity;

use Common\Service\ActivityService;
use Common\Service\RightService;
use Common\Model\ActivityModel;
use VcySDK\Logger;

class CollectionCancelController extends AbstractController
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
    public function Index_post()
    {
        $params = I('post.');

        // 获取参数
        $ac_id = $params['ac_id'] ? intval($params['ac_id']) : 0;

        // 验证参数
        if (empty($ac_id)) {
            E("_EMPTY_ACTIVITY_ID");

            return false;
        }

        // 外部人员抛错提示
        if (empty($this->uid)) {
            E("_NOT_ALLOW_OUTER_COLLECTION");
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

        // 添加收藏 xtong 2017年06月03日
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionDelete');

        $params = [
            'uid' => $this->uid,
            'app' => 'activity',
            'dataId' => $ac_id
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index', $params);

        Logger::write('取消添加：' . var_export($res, true));

        if (!$res) {
            $this->_set_error('_ERR_ALREADY_COLLECTION');

            return false;
        }

        // 返回成功
        $this->_result = [];

        return true;
    }

}
