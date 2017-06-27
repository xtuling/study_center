<?php
/**
 * 立即发布活动
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-08 12:00:27
 * @version $Id$
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;

class PublishController extends AbstractController
{
    public function Index_post()
    {
        $activity_service = new ActivityService();

        if (!$activity_service->publish_activity($this->_result, I('post.'))) {

            return false;
        }

        return true;
    }
}
