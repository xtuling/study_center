<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:51
 */

namespace Api\Controller\User;

use Common\Common\Department;
use Common\Service\UserService;
use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Service\DeptRightService;
use Common\Service\InviteSettingService;

class DepartmentListController extends AbstractController
{

    /**
     * 【通讯录】部门列表
     * @author liyifei
     */
    public function Index_post()
    {

        $user = $this->_login->user;
        if (empty($user['memUid'])) {
            E('_ERR_NOT_LOGIN');
            return false;
        }

        // 检查管理权限
        $this->checkCurrentManagePower($user);

        // 实例化
        $rightServ = new DeptRightService();
        $settingServ = new InviteSettingService();
        $epServ = new Enterprise(Service::instance());

        // 获取邀请函设置信息
        $setting = $settingServ->get_by_conds([]);
        $checkUdpids = unserialize($setting['check_udpids']);
        $inviteUdpids = unserialize($setting['invite_udpids']);

        // 查询登录人员是否有邀请人员权限
        $isInvite = is_array($inviteUdpids) && in_array($user['memUid'], $inviteUdpids) ? InviteSettingService::IS_INVITE_YES : InviteSettingService::IS_INVITE_NO;

        // 查询登录人员是否有审核权限
        $isCheck = is_array($checkUdpids) && in_array($user['memUid'], $checkUdpids) ? InviteSettingService::IS_CHECK_YES : InviteSettingService::IS_CHECK_NO;

        // 人员通讯录可查看范围-列表
        //$range = $rightServ->getDpListByUid($user['memUid']);

        // 我的部门-列表
        //$myList = $rightServ->getMyDeptList($user['memUid']);

        // 获取部门列表
        $userService = new UserService();
        list($currentDpId, $p2c, $childIds, $dpIds) = $userService->getUserTopDpId($user);
        // 取出所有部门
        $departments = Department::instance()->listAll();
        $myDepartments = array();
        foreach ($departments as $_dp) {
            if (!in_array($_dp['dpId'], $dpIds)) {
                continue;
            }

            $myDepartments[$_dp['dpId']] = $_dp;
        }

        // 企业信息
        $ep = $epServ->detail();

        $this->_result = array(
            'is_check' => $isCheck,
            'is_invite' => $isInvite,
            'qy_name' => $ep['corpName'],
            'myDpIds' => $childIds,
            'list' => $myDepartments,
            'currentDpId' => $currentDpId,
            'p2c' => $p2c
        );

        return true;
    }
}
