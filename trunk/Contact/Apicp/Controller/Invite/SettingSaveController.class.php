<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:45
 */

namespace Apicp\Controller\Invite;

use Common\Service\InviteSettingService;
use Common\Service\InviteUserService;

class SettingSaveController extends AbstractController
{

    /**
     * 【通讯录】保存邀请函设置
     * @author liyifei
     * tony 2016-11-10 11:02:28 通讯录迭代，去掉表单字段的设置。
     */
    public function Index_post()
    {

        $qrcodeExpire = I('post.qrcode_expire', -1, 'intval');
        $type = I('post.type', 0, 'intval');
        $dpIds = I('post.departments');
        $checkUids = I('post.check_uids');
        $inviteUids = I('post.invite_uids');
        $inviterWrite = I('post.inviter_write');
        $checkType = I('post.check_type');

        if (empty($type)) {
            E('_ERR_PARAM_IS_NULL');
            return false;
        }

        // 获取权限id
        $inviteUids['auths'] = array();
        foreach ($inviteUids['selectedList'] as $_udt) {
            $inviteUids['auths'][] = $_udt['id'];
        }

        $upData = [
            //'qrcode_expire' => $qrcodeExpire,
            'type' => $type,
            'departments' => empty($dpIds) ? '' : serialize($dpIds),
            'check_udpids' => empty($checkUids) ? '' : serialize($checkUids),
            'invite_udpids' => empty($inviteUids) ? '' : serialize($inviteUids),
            'inviter_write' => empty($inviterWrite) ? '' : serialize($inviterWrite),
            'check_type' => empty($checkType) ? 3 : $checkType
        ];

        $settingServ = new InviteSettingService();
        $setting = $settingServ->getSetting();

        // 审批邀请修改为直接邀请，需要判断当前是否还有待审批的数据
        if ($setting['type'] == InviteSettingService::INVITE_TYPE_NEED_CHECK && $upData['type'] != $setting['type']) {
            $inviteServ = new InviteUserService();
            $count = $inviteServ->count_by_conds(['check_status' => InviteUserService::CHECK_STATUS_WAIT]);

            if ($count) {
                E('_ERR_CANNOT_CHANGE_INVITE_SETTING');
            }
        }

        $settingServ->update_by_conds([], $upData);
    }
}
