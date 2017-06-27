<?php
/**
 * 获取点赞列表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-09 15:51:04
 * @version $Id$
 */

namespace Apicp\Controller\Activity;

use Common\Service\LikeService;

class PraiseListController extends AbstractController
{
    // 类型为活动
    const TYPE_ACTIVITY = 1;

    public function Index_post()
    {
        $like_service = new LikeService();

        $params = array(
            'cid' => I('ac_id', 0, 'intval'),
            'page' => I('page', 1, 'intval'),
            'limit' => I('limit', 0, 'intval'),
            'type' => self::TYPE_ACTIVITY,
        );

        if (!$list = $like_service->get_like_list($params)) {

            return false;
        }

        $this->_result = $list;

        return true;
    }
}
