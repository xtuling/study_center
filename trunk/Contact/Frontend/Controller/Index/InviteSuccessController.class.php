<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

class InviteSuccessController extends AbstractController
{
    protected $_require_login = false;

    /**
     * 审核成功页面
     * @author tony
     */
    public function Index()
    {

        $invite_id = I('get.invite_id', 0, 'intval');
        redirectFront('/app/page/invite/invite-success', ['invite_id' => $invite_id]);
    }
}
