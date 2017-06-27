<?php
/**
 * InviteApproveController.class.php
 * 重定向到前端邀请审批详情页
 * @author Deepseath
 * @version $Id$
 */
namespace Frontend\Controller\Index;

class InviteApproveController extends AbstractController
{

    /**
     * 重定向到审批详情页面
     */
    public function Index()
    {
        $invite_id = I('get.invite_id', 0, 'intval');
        redirectFront('/app/page/invite/invite-approve', [
            'invite_id' => $invite_id
        ]);
    }
}
