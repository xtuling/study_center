<?php
/**
 * 编辑保存活动接口
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-08 18:16:23
 */

namespace Apicp\Controller\Activity;

use Common\Service\ActivityService;

class EditController extends AbstractController
{

    public function Index_post()
    {

        $activity_service = new ActivityService();

        if (!$activity_service->update_activity($this->_result, I('post.'))) {

            return false;
        }

        return true;
    }

}
