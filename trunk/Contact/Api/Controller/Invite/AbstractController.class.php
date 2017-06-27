<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Time: 2016年9月27日11:40:37
 */

namespace Api\Controller\Invite;

use Com\Cookie;
use Common\Common\Cache;
use Common\Common\Department;
use Common\Common\User;
use Common\Model\InviteSettingModel;
use Common\Service\AttrService;
use Common\Service\InviteLinkService;
use Common\Service\InviteSettingService;
use Common\Service\InviteUserService;

abstract class AbstractController extends \Api\Controller\AbstractController
{

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    public function after_action($action = '')
    {

        return parent::after_action();
    }

    /**
     * 判断是否已经接受邀请并填写资料
     * @param array $user   用户信息
     * @param int   $linkId 邀请链接ID
     * @return bool
     */
    protected function _hasAcceptAndWrite(&$user, $linkId = 0)
    {

        $wx_openid = Cookie::instance()->getx('wx_openid');
        if (null == $wx_openid) {
            E('PLEASE_LOGIN');
        }

        $condition = array('wx_openid' => $wx_openid);
        if (0 < $linkId) {
            $condition['link_id'] = $linkId;
        }
        $inviteUserService = new InviteUserService();
        $user = $inviteUserService->get_by_conds($condition);

        return !empty($user);
    }

    /**
     * 获取邀请配置
     * @return array|bool
     */
    protected function _getInviteSetting()
    {

        static $setting = array();

        if (!empty($setting)) {
            return $setting;
        }

        // 读取邀请配置
        $settingService = new InviteSettingService();
        $setting = $settingService->get_by_conds([]);
        if (empty($setting)) {
            E('1007:请通知管理员配置邀请设置');
            return false;
        }

        return $setting;
    }

    /**
     * 检查邀请链接是否合法
     * @param $link_id
     * @return bool
     */
    protected function _checkLinkId($link_id)
    {

        if (empty($link_id)) {
            E('_ERR_LINK_ID_IS_NULL');
        }

        $inviteLinkService = new InviteLinkService();
        $link = $inviteLinkService->get($link_id);

        // 邀请连接不存在或已被删除
        if (empty($link)) {
            E('_ERR_LINK_NOT_FOUND');
        }

        // 读取字段配置
        $attrService = new AttrService();
        $fields = $attrService->getAttrList(true, array(), false, false);
        $fields = array_combine_by_key($fields, 'field_name');

        // 读取邀请配置
        $inviteSetting = $this->_getInviteSetting();
        if (empty($inviteSetting['inviter_write'])) {
            $inviteWrite = array();
        } else {
            $inviteWrite = unserialize($inviteSetting['inviter_write']);
        }

        $default_data = unserialize($link['default_data']);
        // 如果岗位已经开启并且必填, 邀请岗位也开启时, 邀请链接中岗位为空则说明邀请链接过期
        if (1 == $fields['memJob']['is_open'] && 1 == $fields['memJob']['is_required']
            && in_array('job', $inviteWrite) && empty($default_data['job'])
        ) {
            E('1009:邀请链接已过期');
            return false;
        }
        // 如果角色已经开启并且必填, 邀请角色也开启时, 邀请链接中角色为空则说明邀请链接过期
        if (1 == $fields['memRole']['is_open'] && 1 == $fields['memRole']['is_required']
            && in_array('role', $inviteWrite) && empty($default_data['role'])
        ) {
            E('1009:邀请链接已过期');
            return false;
        }

        // 检查管理权限
        $user = User::instance()->getByUid($link['invite_uid']);
        $this->checkCurrentInvitePower($user);
        return true;
    }

    /**
     * 检查是否有审核权限
     * @param $inviteUser
     * @return bool
     */
    protected function _hasCheckPower($inviteUser)
    {

        // 读取邀请配置
        $settingService = new InviteSettingService();
        $data = $settingService->get_by_conds([]);
        // 如果配置为空, 则说明配置有问题
        if (empty($data)) {
            return false;
        }

        // 当前记录的审核权限
        $auths = explode(',', $inviteUser['udpid']);

        // 如果用户uid在审核权限中
        $approveInviter = InviteSettingModel::APPROVE_INVITER & $data['check_type'];
        if (InviteSettingModel::APPROVE_INVITER == $approveInviter && in_array($this->_login->user['memUid'], $auths)) {
            return true;
        }

        // 判断负责人
        $approveLeader = InviteSettingModel::APPROVE_LEADER & $data['check_type'];
        if (InviteSettingModel::APPROVE_LEADER == $approveLeader) {
            $departments = Department::instance()->listAll();
            foreach ($auths as $_id) {
                if (empty($departments[$_id])) {
                    continue;
                }

                if ($this->_login->user['memUid'] == $departments[$_id]['dpLead']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 把默认数据追加到用户提交数据中
     * @param $data
     * @param $form
     * @return bool
     */
    protected function _addDefaultData(&$form, $data)
    {

        if (!empty($data['job'])) {
            $form['memJob'] = array(
                'type' => '11',
                'field_name' => 'memJob',
                'attr_name' => '职位',
                'attr_value' => $data['job'],
                'option' => array(
                    array(
                        'name' => $data['job'],
                        'value' => $data['job']
                    )
                )
            );
        }

        if (!empty($data['role'])) {
            $form['memRole'] = array(
                'type' => '11',
                'field_name' => 'memJob',
                'attr_name' => '角色',
                'attr_value' => $data['role'],
                'option' => array(
                    array(
                        'name' => $data['role'],
                        'value' => $data['role']
                    )
                )
            );
        }

        return true;
    }

    /**
     * 检查邀请数据是否正常
     * @param $data
     * @return bool
     */
    protected function _checkDefaultData($data)
    {

        if (empty($data) || empty($data['department']) || empty($data['department']['dpId'])) {
            E('1009:邀请链接非法');
        }

        $department = Department::instance()->getById($data['department']['dpId']);
        if (empty($department)) {
            E('1009:邀请失效');
        }

        // 读取字段配置
        $attrService = new AttrService();
        $fields = $attrService->getAttrList(true, array(), false, false);
        $fields = array_combine_by_key($fields, 'field_name');

        // 读取邀请配置
        $settingService = new InviteSettingService();
        $setting = $settingService->get_by_conds([]);
        if (empty($setting)) {
            E('1007:请通知管理员配置邀请设置');
            return false;
        }

        if (empty($setting['inviter_write'])) {
            $inviteWrite = array();
        } else {
            $inviteWrite = unserialize($setting['inviter_write']);
        }

        if (in_array('job', $inviteWrite) && empty($data['job']) && 1 == $fields['memJob']['is_open']) {
            E('1009:邀请链接已过期');
        }

        if (in_array('role', $inviteWrite) && empty($data['role']) && 1 == $fields['memRole']['is_open']) {
            E('1009:邀请链接已过期');
        }

        return true;
    }
}

