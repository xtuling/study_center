<?php
/**
 * Created by PhpStorm.
 * User: zs_anything
 * Date: 17/06/18
 * Time: 13:57
 */

namespace Api\Controller\Invite;

use Common\Common\User;
use Common\Model\AttrModel;
use Common\Model\InviteUserModel;
use Common\Service\AttrService;
use Common\Service\UserService;
use Common\Service\InviteUserService;
use Think\Log;

class CheckMobileController extends AbstractController
{

    // 当前接口用户邀请人员填写邀请表单, 不能验证是否登录
    protected $_require_login = false;

    public function Index_post()
    {

        $mobile = I('post.mobile', '', 'trim');

        if (empty($mobile)) {
            E('_ERR_MOBILE_EMPTY');
        }

        // 先去UC验证手机号是否已存在
        $userService = new User();
        $isExist = $userService->checkMemInfoSingle($mobile, null, null);

        // 该手机号已是企业成员，无需重复提交加入
        if ($isExist['memMobile'] == InviteUserModel::MEM_INFO_EXIST) {
            E('_ERR_MOBILE_USER_EXISTED');
        }

        $inviteUserService = new InviteUserService();
        $waitApprovalRecord = $inviteUserService->get_by_conds(
            [
                'mobile' => $mobile,
                'check_status' => InviteUserService::CHECK_STATUS_WAIT,

            ]
        );

        // 该手机资料已经提交，在等待审核中
        if (!empty($waitApprovalRecord)) {
            E('_ERR_MOBILE_WAIT_APPROVAL');
        }

        return true;
    }

}
