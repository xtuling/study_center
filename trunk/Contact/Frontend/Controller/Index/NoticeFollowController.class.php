<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2016-11-25 11:18:40
 */

namespace Frontend\Controller\Index;

class NoticeFollowController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 提醒关注的页面
     * @author tony
     */
    public function Index()
    {

        $notice_id = I('get.notice_id', 0, 'intval');
        redirectFront('/app/page/contacts/notice-follow', ['notice_id' => $notice_id]);
    }
}
