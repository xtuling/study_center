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
use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteUserService;
use Common\Service\InviteSettingService;

class InviteDetailController extends AbstractController
{

    /**
     * 邀请详情
     * @author zhonglei
     */
    public function Index_post()
    {

        $inviteId = I('post.invite_id', '', 'trim');
        if (empty($inviteId)) {
            E('_ERR_INVITE_ID_IS_NULL');
        }

        $inviteUserService = new InviteUserService();
        $invite = $inviteUserService->get($inviteId);
        if (!$invite) {
            E('_ERR_INVITE_DATA_IS_NULL');
        }

        // 读取字段信息
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true);

        // 将form表单格式化为前端可显示格式
        $inviteForm = [];
        $departments = Department::instance()->listAll();
        $form = unserialize($invite['form']);
        $form = array_combine_by_key($form, 'field_name');
        //foreach ($form as $v) {
        foreach ($attrs as $_attr) {
            $field_name = $_attr['field_name'];
            if ('dpName' == $_attr['field_name']) {
                $field_name = 'dpIdList';
            }
            if (empty($form[$field_name])) {
                continue;
            }
            $v = $form[$field_name];
            $v['attr_value'] = isset($v['attr_value']) ? $v['attr_value'] : '';

            if (!empty($v['attr_value']) || $v['attr_value'] !== '') {
                // 属性类型为"图片"时,将属性值转为图片详情数组
                if ($v['type'] == AttrModel::ATTR_TYPE_PICTURE) {
                    $v['attr_value'] = $inviteUserService->formatValueByType($v['type'], $v['attr_value']);
                }

                // 属性类型为"单选、下拉框单选"时,将属性值由单选value转为单选name显示
                if ($v['type'] == AttrModel::ATTR_TYPE_RADIO || $v['type'] == AttrModel::ATTR_TYPE_DROPBOX) {
                    foreach ($v['option'] as $option) {
                        if ($option['value'] == $v['attr_value']) {
                            $v['attr_value'] = $option['name'];
                            break;
                        }
                    }
                }

                // 属性类型为"多选"时,将属性值数组组成字符串
                if ($v['type'] == AttrModel::ATTR_TYPE_CHECKBOX) {
                    $tmp = '';
                    foreach ($v['attr_value'] as $val) {
                        $tmp .= $val['name'] . ';';
                    }
                    $v['attr_value'] = substr($tmp, 0, -1);
                }

                // 如果是部门ID
                if ('dpIdList' == $v['field_name']) {
                    if (empty($departments[$v['attr_value']])) {
                        $v['attr_value'] = '';
                    } else {
                        $v['attr_value'] = $departments[$v['attr_value']]['dpName'];
                    }
                }
            }

            $inviteForm[] = $v;
        }

        // 当前登录人员是否有审核权限
        if ($this->_hasCheckPower($invite)) {
            $isChecker = InviteSettingService::IS_CHECK_YES;
        } else {
            $isChecker = InviteSettingService::IS_CHECK_NO;
        }

        $data = [
            'is_checker' => $isChecker,
            'invite_id' => $invite['invite_id'],
            'type' => $invite['type'],
            'fields' => $inviteForm,
            'time' => $invite['created'],
            'check_status' => $invite['check_status'],
            'is_follow' => InviteUserService::USER_IS_FOLLOW_FALSE,
        ];

        if ($invite['check_status'] == InviteUserService::CHECK_STATUS_PASS) {
            $userServ = new User();
            $user = $userServ->getByUid($invite['uid']);
            if ($user) {
                $data['face'] = $user['memFace'];
                // 已关注
                if ($user['memSubscribeStatus'] == InviteUserService::USER_IS_FOLLOW_TRUE) {
                    $data['is_follow'] = InviteUserService::USER_IS_FOLLOW_TRUE;
                }
            }
        }

        $this->_result = $data;
    }
}
