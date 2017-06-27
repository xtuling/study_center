<?php
/**
 * DeleteController.class.php
 * 删除我的评论
 * User: heyuelong
 * Date:2017年4月26日18:07:37
 */

namespace Api\Controller\Comment;

use Common\Service\CircleService;

class DeleteController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {
        // 实例化同事圈表
        $service = new CircleService();

        // 删除我的评论
        if (!$service->del_comment(I('get.cid'), $this->uid)) {

            return false;
        }

        $this->_result = array();

        return true;
    }

}

