<?php
/**
 * ListController.class.php
 * 同事圈详情评论列表
 * User: heyuelong
 * Date: 2017年4月27日14:26:19
 */

namespace Api\Controller\Comment;

use Common\Service\CircleService;

class ListController extends AbstractController
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
    public function Index_get()
    {
        $params = I('get.');

        // 如果话题ID为空
        if (empty($params['id'])) {

            $this->_set_error('_EMPTY_CIRCLE_ID');

            return false;
        }

        // 实例化同事圈评论信息表
        $service = new CircleService();

        // 获取评论列表
        $list = $service->get_comment_list($params, $this->uid);

        $this->_result = $list;

        return true;
    }

}

