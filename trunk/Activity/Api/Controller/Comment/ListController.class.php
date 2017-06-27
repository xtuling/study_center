<?php
/**
 * 评论列表接口
 * User: daijun
 * Date: 2017-05-08
 */

namespace Api\Controller\Comment;

use Common\Service\ActivityService;
use Common\Service\CommentService;

class ListController extends AbstractController
{
    /**
     * @var bool 不强制登录
     */
    protected $_require_login = false;

    /**
     * @var  ActivityService 活动信息表
     */
    protected $_activity_serv;

    /**
     * @var CommentService 评论回复信息表
     */
    protected $_comment_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化活动信息表
        $this->_activity_serv = new ActivityService();
        $this->_comment_serv = new CommentService();

        return true;
    }


    public function Index_get()
    {
        // 获取参数
        $params = I('get.');
        $ac_id = $params['ac_id'];

        if (empty($ac_id)) {
            E('_EMPTY_ACTIVITY_ID');
            return false;
        }

        // 默认值
        $page = isset($params['page']) ? intval($params['page']) : ActivityService::DEFAULT_PAGE;
        $limit = isset($params['limit']) ? intval($params['limit']) : ActivityService::DEFAULT_LIMIT;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('created' => 'ASC');

        // 分页
        $page_option = array($start, $limit);

        // 查询总记录数
        $total = $this->_comment_serv->count_by_conds(array('ac_id' => $ac_id, 'parent_id' => 0));

        $list = array();
        if ($total > 0) {
            // 查询列表
            $comm_list = $this->_comment_serv->list_by_conds(array('ac_id' => $ac_id, 'parent_id' => 0), $page_option,
                $order_option);
            // 格式化列表数据
            $list = $this->_comment_serv->format_comment_list($comm_list, $this->uid);
        }

        $this->_result = array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $list
        );

        return true;
    }
}
