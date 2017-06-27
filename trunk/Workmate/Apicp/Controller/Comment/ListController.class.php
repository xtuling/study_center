<?php
/**
 * ListController.class.php
 * 同事圈评论列表
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Comment;

use Common\Service\CircleService;
use Common\Service\LikeService;

class ListController extends AbstractController
{
    /**
     * @var LikeService 点赞信息表
     */
    protected $_like_serv;

    /**
     * @var CircleService 同事圈信息表
     */
    protected $_circle_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化点赞信息表
        $this->_like_serv = new LikeService();

        // 实例化同事圈信息表
        $this->_circle_serv = new CircleService();

        return true;
    }

    public function Index_post()
    {
        // 接收参数
        $params = I('post.');

        // 参数验证
        if (empty($params['id'])) {
            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 参数验证 数据表字段audit_state状态值为0,1,2
        if (!is_numeric($params['audit_state']) || intval($params['audit_state'] > CircleService::AUDIT_NO)) {
            $this->_set_error('_EMPTY_AUDITSTATE');

            return false;
        }

        // 获取评论列表
        $list = $this->_circle_serv->get_comment_admin_list($params);

        $this->_result = $list;

        return true;
    }

}

