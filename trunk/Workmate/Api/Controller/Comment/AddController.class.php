<?php
/**
 * AddController.class.php
 * 发布评论
 * User: heyuelong
 * Date:2017年4月26日18:07:37
 */

namespace Api\Controller\Comment;

use Common\Service\CircleService;

class AddController extends AbstractController
{

    /**
     * 是否必须登录
     *
     * @var string $_require_login
     */
    protected $_require_login = false;

    /**
     * 主方法
     * @return boolean
     */
    public function Index_post()
    {
        $params = I('post.');

        // 实例化同事圈表
        $service = new CircleService();

        // 发布评论
        $comment_id = $service->push_comment($params, $this->_login->user, $this->_setting['comment']);

        // 抛出错误提示
        if (!$comment_id) {

            return false;
        }

        $this->_result = array('pid' => intval($params['pid']), 'id' => intval($comment_id));

        return true;
    }

}

