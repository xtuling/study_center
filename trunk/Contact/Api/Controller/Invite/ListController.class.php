<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:52
 */

namespace Api\Controller\Invite;

use Common\Common\Department;
use Common\Common\User;
use Common\Service\InviteSettingService;
use Common\Service\InviteUserService;

class ListController extends AbstractController
{

    /**
     * 我的邀请、审核列表
     * @author zhonglei
     */
    public function Index_post()
    {

        //$listType = I('post.list_type', 0, 'intval');
        $checkStatus = I('post.check_status', 0, 'intval');
        $page = I('post.page', 1, 'Intval');
        $pageSize = I('post.limit', 15, 'Intval');
        // 验证传参
        if (empty($checkStatus)) {
            E('_ERR_PARAM_IS_NULL');
            return false;
        }

        // 列表类型是否存在
        $type = [
            InviteUserService::MY_INVITE_LIST,
            InviteUserService::MY_CHECK_LIST,
        ];
        /**if (!in_array($listType, $type)) {
         * E('_ERR_LIST_TYPE_UNDEFINED');
         * }*/

        $settingServ = new InviteSettingService();
        $setting = $settingServ->getSetting();
        // 验证是否有审核权限
        /**if ($listType == InviteUserService::MY_CHECK_LIST && !in_array($this->uid, $setting['check_uids'])) {
         * E('_ERR_NO_CHECK_RIGHT');
         * }*/

        // 审核状态是否存在
        $status = [
            InviteUserService::CHECK_STATUS_WAIT,
            InviteUserService::CHECK_STATUS_PASS,
        ];
        if (!in_array($checkStatus, $status)) {
            E('_ERR_INVITE_INVALID_STATUS');
        }

        // 已审核、未审核条件
        $conds = [
            '`u`.`check_status`' => InviteUserService::CHECK_STATUS_WAIT,
        ];
        if ($checkStatus == InviteUserService::CHECK_STATUS_PASS) {
            /**$conds['`u`.`check_status`'] = [
             * InviteUserService::CHECK_STATUS_PASS,
             * InviteUserService::CHECK_STATUS_FAIL,
             * ];*/
            unset($conds['`u`.`check_status`']);
            $conds['`u`.`check_uid`'] = $this->_login->user['memUid'];
        }

        // 我的邀请列表
        /**if ($listType == InviteUserService::MY_INVITE_LIST) {
         * $conds['invite_uid'] = $this->uid;
         * }*/
        $conds['`r`.`udtid`'] = array($this->uid);
        $myDpIds = $this->_getMyDpId($this->uid);
        if (!empty($myDpIds)) {
            $conds['`r`.`udtid`'] = array_merge($myDpIds, $conds['`r`.`udtid`']);
        }

        list($start, $limit, $page) = page_limit($page, $pageSize);
        $inviteUserServ = new InviteUserService();
        list($inviteIds, $count) = $inviteUserServ->listByRight($conds, [$start, $limit], ['`u`.`created`' => 'DESC'], 'DISTINCT `r`.`invite_id`');
        $invites = $inviteUserServ->list_by_pks(array_column($inviteIds, 'invite_id'));
        //$invites = $inviteUserServ->list_by_conds($conds, [$start, $limit], ['created' => 'DESC']);
        //$count = $invites ? $inviteUserServ->count_by_conds($conds) : 0;
        $list = [];

        if ($invites) {
            $uids = array_column($invites, 'uid');
            $userServ = new User();
            $users = $userServ->listByUid($uids);

            foreach ($invites as $v) {
                $user = $v['uid'] && isset($users[$v['uid']]) ? $users[$v['uid']] : null;
                $is_follow = InviteUserService::USER_IS_FOLLOW_FALSE;
                if ($user && $user['memSubscribeStatus'] == InviteUserService::USER_IS_FOLLOW_TRUE) {
                    $is_follow = InviteUserService::USER_IS_FOLLOW_TRUE;
                }

                $list[] = [
                    'invite_id' => $v['invite_id'],
                    'type' => $v['type'],
                    'username' => $user ? $user['memUsername'] : $v['username'],
                    'face' => $user ? $user['memFace'] : '',
                    'is_follow' => $is_follow,
                    'check_status' => $v['check_status'],
                    'time' => $v['created'],
                ];
            }
        }

        $this->_result = [
            'page' => $page,
            'limit' => $pageSize,
            'total' => intval($count),
            'list' => $list,
        ];
    }

    /**
     * 获取我负责的部门
     * @param $uid
     * @return array
     */
    protected function _getMyDpId($uid)
    {

        $dpIds = array();
        $departments = Department::instance()->listAll();
        foreach ($departments as $_dp) {
            if ($_dp['dpLead'] == $uid) {
                $dpIds[] = $_dp['dpId'];
            }
        }

        return $dpIds;
    }

}
