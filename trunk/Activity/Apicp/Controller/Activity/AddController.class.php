<?php
/**
 * 添加活动接口
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-05 15:24:46
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;

class AddController extends AbstractController
{

    public function Index_post()
    {

        $activity_service = new ActivityService();

        if (!$activity_service->add_activity($this->_result, I('post.'))) {

            return false;
        }

        return true;
    }

}
