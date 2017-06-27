<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/30
 * Time: 09:57
 */

namespace Api\Controller\Invite;

use Common\Common\User;
use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\UserService;
use Common\Service\InviteUserService;
use Think\Log;

class CheckController extends AbstractController
{

    /**
     * 审批
     * @author zhonglei
     */
    public function Index_post()
    {

        list($inviteId, $checkStatus) = $this->getRequestParams();

        $inviteUserService = new InviteUserService();

        $inviteDetail = $this->getInviteDetail($inviteId, $inviteUserService);

        $result = $this->createUser($checkStatus, $inviteDetail);

        // 修改审批人UID、该人员审批状态、审批时间
        $upData = [
            'check_uid' => $this->uid,
            'check_status' => $checkStatus,
            'check_time' => MILLI_TIME,
        ];

        if (isset($result['memUid'])) {
            $upData['uid'] = $result['memUid'];
        }

        $updateResult = $inviteUserService->update($inviteId, $upData);

        if (!$updateResult) {
            E('_ERR_CHECK_FAILED');
        }

        // 发送消息异常, 不影响主流程
        try {
            $inviteUserService->sendNotice($inviteDetail, $checkStatus);
        } catch (\Exception $e) {
            Log::record("邀请人员-审批发送审批记过通知异常: " . $e->getMessage());
        }

        return true;
    }

    /**
     * 获取请求参数
     * @return array
     *         inviteId  邀请ID
     *         checkStatus  审批状态 2-审批通过 3-审批驳回
     */
    private function getRequestParams()
    {
        $inviteId = I('post.invite_id', '', 'trim');
        $checkStatus = I('post.check_status', 0, 'intval');

        // 必传项不可为空
        if (empty($inviteId)) {
            E('_ERR_INVITE_ID_IS_NULL');
        }

        // 审批结果是否存在
        if (!in_array($checkStatus, [InviteUserService::CHECK_STATUS_PASS, InviteUserService::CHECK_STATUS_FAIL])) {
            E('_ERR_INVITE_INVALID_STATUS');
        }

        return array($inviteId, $checkStatus);
    }

    /**
     * 获取邀请记录详情
     * @param $inviteId
     * @param $inviteUserService
     * @return array
     */
    private function getInviteDetail($inviteId, $inviteUserService)
    {
        // 获取待审批人员信息
        $inviteDetail = $inviteUserService->get_by_conds(['invite_id' => $inviteId]);

        if (!$inviteDetail) {
            E('_ERR_INVITE_DATA_IS_NULL');
        }

        // 待审批人员是否处于待审批状态
        if ($inviteDetail['check_status'] != InviteUserService::CHECK_STATUS_WAIT) {
            E('_ERR_INVITE_CHECKED');
        }

        // 判断审核权限
        if (!$this->_hasCheckPower($inviteDetail)) {
            E('_ERR_NO_PERMISSION_APPROVAL_');
        }

        return $inviteDetail;
    }

    /**
     * 创建用户
     * @param $checkStatus
     * @param $inviteDetail
     * @return bool|mixed
     */
    private function createUser($checkStatus, $inviteDetail)
    {
        // 审批驳回
        if ($checkStatus == InviteUserService::CHECK_STATUS_FAIL) {
            return [];
        }

        // 格式化表单数据，将field_name设置为key
        $form = unserialize($inviteDetail['form']);
        $form = array_combine_by_key($form, 'field_name');

        if (empty($form['dpIdList'])) {
            E('_ERR_DEPT_UNDEFINED');
        }

        $userServ = new UserService();

        $insertData = $this->formatInsertData($inviteDetail, $form, $userServ);

        $result = $userServ->saveUser('', $insertData);

        return $result;
    }

    /**
     * 封装新增用户数据
     * @param $inviteDetail
     * @param $form
     * @param $userServ
     * @return array
     */
    private function formatInsertData($inviteDetail, $form, $userServ)
    {
        // 以架构接口参数字段为键,拼接用户信息
        $attrService = new AttrService();
        $attrs = $attrService->getAttrList(true, [AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER]);

        $data = [
            'dpIdList' => (array)$form['dpIdList']['attr_value']
        ];

        foreach ($attrs as $attr) {
            $serializeAttr = [
                AttrModel::ATTR_TYPE_CHECKBOX,
                AttrModel::ATTR_TYPE_PICTURE,
            ];

            if (in_array($form[$attr['field_name']]['type'], $serializeAttr)
                && !empty($form[$attr['field_name']]['attr_value'])
            ) {
                $data[$attr['field_name']] = serialize($form[$attr['field_name']]['attr_value']);
            } else {
                $data[$attr['field_name']] = $form[$attr['field_name']]['attr_value'];
            }
        }

        $inviteUserInfo = User::instance()->getByUid($inviteDetail['invite_uid']);
        $data['memJoinType'] = $userServ::USER_INVITE_JOIN;
        $data['memJoinInviter'] = $inviteUserInfo['memUsername'];

        return $data;
    }
}
