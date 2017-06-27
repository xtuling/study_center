<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:01
 */

namespace Api\Controller\User;

use Common\Common\User;
use Common\Service\InviteUserService;
use \Common\Service\UserService;
use Think\Log;

class DeleteController extends AbstractController
{

    /**
     * 【通讯录】人员删除
     * @author liyifei
     * @time   2016-09-21 14:46:16
     */
    public function Index_post()
    {

        // 接收参数
        $uids = I('post.uids');
        if (empty($uids)) {
            E('_ERR_UID_IS_NULL');
            return false;
        }

        $uids = (array)$uids;
        $this->checkCurrentManagePower($this->_login->user);

        $inviteUserService = new InviteUserService();
        $inviteUserService->start_trans();

        try {

            $userUtil = new User();
            $userArr = $userUtil->listByUid($uids);

            $mobileArr = array_column($userArr, "memMobile");
            $weixinArr = array_column($userArr, "memWeixin");
            $emailArr = array_column($userArr, "memEmail");

            // 删除用户手机号,微信号,邮箱所关联的邀请记录
            $inviteUserService->delInviteUserRecord($mobileArr, $weixinArr, $emailArr);

            $userService = new UserService();
            $userService->delete($uids);

            $inviteUserService->commit();

        }  catch (\Exception $e) {
            Log::record("删除员工异常: ", var_export($e, true));
            $inviteUserService->rollback();
        }

        return true;
    }
}
