<?php
/**
 * CancelLikeController.class.php
 * 【同事圈-手机端】同事圈取消点赞
 * User: heyuelong
 * Date: 2017年4月27日15:01:09
 */

namespace Api\Controller\Like;

use Common\Service\LikeService;

class CancelLikeController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {

        // 初始化点赞表
        $service = new LikeService();

        // 取消点赞
        if (!$service->cancel_like(I('get.id'), $this->uid)) {

            return false;
        }

        $this->_result = array();

        return true;
    }

}

