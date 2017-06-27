<?php
/**
 *【活动中心-手机端】回复评论列表
 * @author: 蔡建华
 * @date :  2017-05-9
 * @version $Id$
 */

namespace Api\Controller\Comment;

use Common\Service\CommentService;

class ReplayListController extends AbstractController
{

    /**
     * @var bool 不强制登录
     */
    protected $_require_login = false;

    /**
     * @var CommentService 评论service
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

    public function Index_get()
    {
        $params = I('get.');
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $page = empty($params['page']) ? 1 : intval($params['page']);

        $comment_id = intval($params['comment_id']);

        // 参数验证
        if (empty($comment_id)) {
            E('_EMPTY_COMMENT_ID');
        }

        // 每页条数
        list($start, $limit) = page_limit($page, $limit);
        // 分页参数
        $page_option = array(
            $start,
            $limit,
        );

        //查询评论信息数据
        if (!$data = $this->_result = $this->_comment_serv->replay_list($params, $page_option, $this->uid)) {
            E('_ERR_DATA_NOT_EXIST');
        }

        $data['page'] = $page;
        $data['limit'] = $limit;
        $data['comment_id'] = $comment_id;

        $this->_result = $data;
        return true;
    }
}
