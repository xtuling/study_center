<?php
/**
 * LikeListController.class.php
 * 同事圈点赞列表
 * User: heyuelong
 * Date:2017年4月27日16:01:34
 */

namespace Api\Controller\Like;

use Common\Service\LikeService;

class LikeListController extends AbstractController
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
        if (empty($params['pid'])) {

            $this->_set_error('_EMPTY_CIRCLE_ID');

            return false;
        }

        // 实例化点赞表
        $service = new LikeService();

        // 获取列表
        $list = $service->get_like_list($params);

        $this->_result = $list;

        return true;
    }

}

