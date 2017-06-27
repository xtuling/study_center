<?php
/**
 * 删除活动接口
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-08 11:37:42
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $activity_service = new ActivityService();

        $params = I('post.');
        if (!$activity_service->delete_activity($params)) {

            return false;
        }

        // 删除成功后同步更新收藏状态
        $activity_service->update_collection(implode(',',$params['ac_ids']));

        return true;
    }

}
