<?php
/**
 * MyCommentController.class.php
 * 我的评论详情
 * User: heyuelong
 * Date: 2017年5月2日14:15:36
 */

namespace Api\Controller\Comment;

use Common\Service\CircleService;

class MyCommentController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {
        // 初始化
        $service = new CircleService();

        // 获取详情
        $info = $service->get_comment_my_info(I('get.cid'), $this->_login->user);

        // 如果详情不存在
        if (!$info) {

            return false;
        }

        $this->_result = $info;

        return true;
    }

}

