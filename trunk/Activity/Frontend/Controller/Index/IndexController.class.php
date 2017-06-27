<?php
/**
 * 活动中心前端页面入口
 */
namespace Frontend\Controller\Index;

class IndexController extends \Common\Controller\Frontend\AbstractController
{

    public function Index()
    {
        redirectFront('app/page/activity/activity-list', array('_identifier' => APP_IDENTIFIER));
    }

}
