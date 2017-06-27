<?php
/**
 * LikeController.class.php
 * 【同事圈-手机端】同事圈点赞
 * User: heyuelong
 * Date:2017年4月27日14:52:14
 */

namespace Api\Controller\Like;

use Common\Service\LikeService;

class LikeController extends AbstractController
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
        // 初始化点赞表
        $service = new LikeService();

        // 同事圈点赞
        if (!$service->like(I('get.id'), $this->uid)) {

            return false;
        }

        $this->_result = array();

        return true;
    }

}

