<?php
/**
 * Created by PhpStorm.
 * User: liyifei
 * Date: 16/9/13
 * Time: 下午14:10
 */

namespace Api\Controller;

use Common\Common\Cache;
use Common\Common\User;
use \Common\Controller\Api;
use Common\Service\InviteSettingService;

abstract class AbstractController extends Api\AbstractController
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
     * 检查邀请权限
     * @param $user
     * @return bool
     */
    public function checkCurrentInvitePower($user)
    {

        if (empty($user) || empty($user['memUid'])) {
            E('1007:请先登录');
            return false;
        }

        $powers = array($user['memUid']);
        if (!empty($user['job'])) {
            $powers[] = $user['job']['jobId'];
        }
        if (!empty($user['role'])) {
            $powers[] = $user['role']['roleId'];
        }

        $settingService = new InviteSettingService();
        $setting = $settingService->get_by_conds([]);
        $auths = array();
        if (!empty($setting['invite_udpids'])) {
            $auths = unserialize($setting['invite_udpids']);
        }

        if (empty($auths['auths']) || !array_intersect($powers, $auths['auths'])) {
            E('1009:您无邀请权限');
            return false;
        }

        return true;
    }

    /**
     * 判断管理权限
     * @param $user
     * @return bool
     */
    public function checkCurrentManagePower($user)
    {

        if (empty($user) || empty($user['memUid'])) {
            E('1007:请先登录');
            return false;
        }

        $powers = array($user['memUid']);
        if (!empty($user['job'])) {
            $powers[] = $user['job']['jobId'];
        }
        if (!empty($user['role'])) {
            $powers[] = $user['role']['roleId'];
        }
        if (!empty($user['dpName'])) {
            $powers = array_merge($powers, array_column($user['dpName'], 'dpId'));
        }

        $settings = Cache::instance()->get('Common.AppSetting');
        // 查看是否已经配置了管理权限
        if (empty($settings['manageAuths']) || empty($settings['manageAuths']['value']['auths'])) {
            E('1009:管理员还未配置管理权限');
            return false;
        }

        $auths = $settings['manageAuths']['value']['auths'];
        if (empty($auths) || !array_intersect($powers, $auths)) {
            E('1008:您无权限管理员工');
            return false;
        }

        return true;
    }
}
