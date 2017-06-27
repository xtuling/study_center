<?php
/**
 * 【活动中心-手机端】 回复/评论接口
 * User: caijaihua
 * Date: 2017-05-09
 */

namespace Api\Controller\Comment;

use Common\Service\CommentService;

class AddController extends AbstractController
{

    /**
     * @var bool 不强制登录
     */
    protected $_require_login = false;

    /**
     * @var CommentService 评论回复信息表
     */
    protected $_comment_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $this->_comment_serv = new CommentService();

        return true;
    }

    //评论或回复
    public function Index_post()
    {
        $params = I('post.');

        // 判断是否外部人员
        if(empty($this->uid)){
            E('_ERR_COMMENT_ADD');
            return false;
        }

        //发布评论或者回复
        if (!$this->_comment_serv->publish_comment($params, $this->uid)) {
            E('_ERR_ADD_DATA');
        }

        return true;
    }
}
