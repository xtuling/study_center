<?php
/**
 * LikeListController.class.php
 * 同事圈点赞列表
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Workmate;

use Common\Service\CircleService;
use Common\Service\LikeService;

class LikeListController extends AbstractController
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
        // 获取数据
        $params = I('post.');

        // 参数验证
        if (empty($params['id'])) {
            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 参数有效性验证
        $data = $this->_circle_serv->get($params['id']);
        if (empty($data)) {
            $this->_set_error('_ERR_DATA_EXIST');

            return false;
        }

        // 每页条数
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $page = empty($params['page']) ? 1 : $params['page'];

        list($start, $limit, $page) = page_limit($page, $limit);

        // 查询条件
        $cond = array('cid' => $params['id']);

        // 分页参数
        $page_option = array($start, $limit);

        // 排序参数
        $order_option = array('like_id' => 'DESC');

        // 查询总数
        $total = $this->_like_serv->count_by_conds($cond);

        // 获取列表
        $list = array();
        if ($total > 0) {
            $list = $this->_like_serv->list_by_conds($cond, $page_option, $order_option, 'like_id,created,uid');
            // 格式化列表数据
            $this->_like_serv->format_like_data($list);
        }

        // 返回数据
        $this->_result = array(
            'page' => intval($page),
            'limit' => intval($limit),
            'total' => intval($total),
            'list' => $list,
        );

        return true;
    }

}

