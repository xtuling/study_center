<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Reader: zhoutao 2017-06-13 10:26:15
 * Time: 11:52
 */

namespace Api\Controller\Invite;

use Com\Cookie;
use Common\Common\Department;
use Common\Common\Job;
use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteLinkService;
use Common\Service\InviteUserRightService;
use Think\Log;
use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Common\User;
use Common\Common\Msg;
use Common\Service\UserService;
use Common\Service\InviteUserService;
use Common\Service\InviteSettingService;

class SubmitController extends CheckUserController
{

    // 用户信息(手机号|邮箱|微信号)已存在
    const MEM_INFO_EXIST = 1;

    /**
     * 【通讯录】提交表单接口(被邀请人填写的表单信息)
     * @author liyifei
     */
    public function Index_post()
    {

        $link_id = I('post.link_id', '', 'trim');
        $form = I('post.form');

        // 如果已经接受了邀请
        $user = array();
        if ($this->_hasAcceptAndWrite($user, $link_id)) {
            // 已通过审核
            if (2 == $user['check_status']) {
                E('1001:您已是企业成员，快去体验吧');
            } elseif (3 == $user['check_status']) {
                E('1002:您的加入申请被驳回，请联系您的邀请人');
            } else {
                E('1003:您的资料已经提交审批，请勿重复提交申请');
            }
            return true;
        }

        // 检查邀请链接是否可用
        $this->_checkLinkId($link_id);

        $inviteLinkService = new InviteLinkService();
        if (empty($link_id) || !($inviteLink = $inviteLinkService->get($link_id))) {
            E('1009:邀请来源错误');
            return false;
        }
        $inviteLink['default_data'] = unserialize($inviteLink['default_data']);
        foreach ($inviteLink['default_data'] as $_field => $_data) {
            if ('job' == $_field) {
                $form[] = array(
                    'type' => 1,
                    'field_name' => 'memJob',
                    'attr_name' => '岗位',
                    'attr_value' => $_data,
                    'option' => ''
                );
            } elseif ('role' == $_field) {
                $form[] = array(
                    'type' => 1,
                    'field_name' => 'memRole',
                    'attr_name' => '角色',
                    'attr_value' => $_data,
                    'option' => ''
                );
            } elseif ('department' == $_field) {
                $form[] = array(
                    'type' => 1,
                    'field_name' => 'dpIdList',
                    'attr_name' => '组织',
                    'attr_value' => $_data['dpId'],
                    'option' => ''
                );
            }
        }

        if (empty($form)) {
            E('_ERR_PARAM_IS_NULL');
        }

        $userService = new User();
        $inviter = $userService->getByUid($inviteLink['invite_uid'], true);

        // 邀请人不存在或已被删除
        if (!$inviter || !$userService->isNormal($inviter)) {
            E('_ERR_INVITER_NOT_FOUND');
        }

        // 判断邀请默认数据
        $this->_checkDefaultData($inviteLink['default_data']);

        // 格式化表单数据，将field_name设置为key
        $form = array_combine_by_key($form, 'field_name');

        $settingService = new InviteSettingService();
        $setting = $settingService->getSetting();

        // 获得属性列表，然后比较表单数据，检查数据是否完整
        $attrService = new AttrService();
        $attrList = $attrService->getAttrList(true, [AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER]);
        $inviteUserService = new InviteUserService();
        if (!$inviteUserService->checkInviteData($form, $attrList)) {
            E('_ERR_FORM_CHECK_FAIL');
        }

        // 先去UC验证手机号,微信号,邮箱是否已存在
        $isExist = $userService->checkMemInfoSingle($form['memMobile']['attr_value'], $form['memEmail']['attr_value'],
            $form['memWeixin']['attr_value']);
        if ($isExist['memMobile'] == self::MEM_INFO_EXIST) {
            E(L('_ERR_PARAM_EXISTED', ['name' => '手机号码']));
        } else if ($isExist['memWeixin'] == self::MEM_INFO_EXIST) {
            E(L('_ERR_PARAM_EXISTED', ['name' => '微信号']));
        } else if ($isExist['memEmail'] == self::MEM_INFO_EXIST) {
            E(L('_ERR_PARAM_EXISTED', ['name' => '邮箱']));
        }

        $invite = $inviteUserService->getInviteUser($form, true);
        // 直接邀请
        $is_write = true;
        if ($setting['type'] == InviteSettingService::INVITE_TYPE_NO_CHECK) {
            // 邀请数据、uid已存在
            if ($invite && $invite['uid']) {
                $user = $userService->getByUid($invite['uid'], true);
                // 用户未被删除，无需记录新的邀请数据
                if ($user['memStatus'] != User::STATUS_DELETED) {
                    $is_write = false;
                }
            }
        // 审批邀请，且邀请数据已存在
        } elseif ($invite) {
            // 等待审批，无需记录新的邀请数据
            if ($invite['check_status'] == InviteUserService::CHECK_STATUS_WAIT) {
                // 再判断邀请表手机号,微信号,邮箱是否已存在
                if ($invite['mobile'] == $form['memMobile']['attr_value']) {
                    E(L('_ERR_PARAM_EXISTED', ['name' => '手机号码']));
                } elseif ($invite['weixin'] == $form['memWeixin']['attr_value']) {
                    E(L('_ERR_PARAM_EXISTED', ['name' => '微信号']));
                } elseif ($invite['email'] == $form['memEmail']['attr_value']) {
                    E(L('_ERR_PARAM_EXISTED', ['name' => '邮箱']));
                }
                $is_write = false;
            // 审批通过
            } elseif ($invite['check_status'] == InviteUserService::CHECK_STATUS_PASS) {
                if ($invite['uid']) {
                    $user = $userService->getByUid($invite['uid'], true);
                    // 用户未被删除，无需记录新的邀请数据
                    if ($user['memStatus'] != User::STATUS_DELETED) {
                        $is_write = false;
                    }
                }
            }
        }

        // 如果没有岗位则写入
        try {
            if (!empty($form['memJob'])) {
                $jobName = $form['memJob']['attr_value'];
                $jobServ = new Job();
                $jobData = $jobServ->getByName($jobName);
                if (empty($jobData)) {
                    $jobServ->addJob(['jobName' => $form['memJob']['attr_value']]);
                }
            }
        } catch (\Exception $e) {
            // 不影响主流程
            Log::record("提交邀请时,新增岗位异常:", var_export($e, true));
        }

        // 写入邀请数据
        if ($is_write) {
            $insertData = [
                'link_id' => $link_id,
                'invite_uid' => $inviter['memUid'],
                'udpid' => $inviter['memUid'] . ',' . $inviteLink['default_data']['department']['dpId'],
                'wx_openid' => Cookie::instance()->getx('wx_openid'),
                'uid' => '',
                'username' => isset($form['memUsername']['attr_value']) ? $form['memUsername']['attr_value'] : '',
                'weixin' => isset($form['memWeixin']['attr_value']) ? $form['memWeixin']['attr_value'] : '',
                'mobile' => isset($form['memMobile']['attr_value']) ? $form['memMobile']['attr_value'] : '',
                'email' => isset($form['memEmail']['attr_value']) ? $form['memEmail']['attr_value'] : '',
                'form' => serialize(array_values($form)),
                'type' => intval($setting['type']),
                'check_status' => $setting['type'] == InviteSettingService::INVITE_TYPE_NO_CHECK ? InviteUserService::CHECK_STATUS_PASS : InviteUserService::CHECK_STATUS_WAIT,
            ];

            // 直接邀请，将用户数据写入UC
            if ($setting['type'] == InviteSettingService::INVITE_TYPE_NO_CHECK) {
                // 获取部门ID
                $dpIds = $setting['departments'] ? $setting['departments'] : array_column($inviter['dpName'], 'dpId');
                $userService = new UserService();

                // 以架构接口参数字段为键,拼接用户信息
                $data = ['dpIdList' => $dpIds];
                $attrs = $attrService->getAttrList(true, [AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER]);
                foreach ($attrs as $attr) {
                    $serializeAttr = [
                        AttrModel::ATTR_TYPE_CHECKBOX,
                        AttrModel::ATTR_TYPE_PICTURE,
                    ];
                    if (in_array($form[$attr['field_name']]['type'], $serializeAttr) && !empty($form[$attr['field_name']]['attr_value'])) {
                        $data[$attr['field_name']] = serialize($form[$attr['field_name']]['attr_value']);
                    } else {
                        $data[$attr['field_name']] = $form[$attr['field_name']]['attr_value'];
                    }
                }

                // 调用验证接口,验证参数传值是否符合规范
                $errors = $attrService->checkValue($data, 'is_required');
                if (!empty($errors)) {
                    E($errors[0]);
                }
                $result = $userService->saveUser('', $data);

                $insertData['uid'] = $result['memUid'];
            }

            $invite_id = $inviteUserService->insert($insertData);

            // 邀请数据写入失败
            if (!$invite_id) {
                E('_ERR_INSERT_INVITE_FAIL');
            }

            // 新增权限对应关系
            $inviteUserRightService = new InviteUserRightService();
            $rights = array(
                array('invite_id' => $invite_id, 'udtid' => $inviter['memUid']),
                array('invite_id' => $invite_id, 'udtid' => $inviteLink['default_data']['department']['dpId'])
            );
            $inviteUserRightService->insert_all($rights);

            // 审批邀请，且审批人不为空
            //if ($setting['type'] == InviteSettingService::INVITE_TYPE_NEED_CHECK && $setting['check_uids']) {
            $checkUids = array();
            if (1 == (1 & $setting['check_type'])) {
                $checkUids[] = $inviteLink['invite_uid'];
            }
            $departments = Department::instance()->listAll();
            if (2 == (2 & $setting['check_type']) && !empty($departments[$inviteLink['default_data']['department']['dpId']]) && !empty($departments[$inviteLink['default_data']['department']['dpId']]['dpLead'])) {
                $checkUids[] = $departments[$inviteLink['default_data']['department']['dpId']]['dpLead'];
            }
            $text = "申请人：{$insertData['username']}";
            $inviteUser = User::instance()->getByUid($inviter['memUid']);
            if (!empty($inviteUser)) {
                $text .= "\n邀请人：{$inviteUser['memUsername']}";
            }
            $text .= "\n申请时间：" . rgmdate(NOW_TIME, 'Y-m-d H:i');
            // 需要审核
            if (!empty($checkUids) && $setting['type'] == InviteSettingService::INVITE_TYPE_NEED_CHECK) {
                $msgService = new Msg();
                $msgService->sendNews($checkUids, '', '', [
                    [
                        'title' => "【审核通知】您有一个同事加入待审核",
                        'description' => $text,
                        'url' => oaUrl('Frontend/Index/InviteList'),
                    ]
                ]);
            }
        }

        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $this->_result = [
            'is_check' => $setting['type'] == InviteSettingService::INVITE_TYPE_NEED_CHECK ? 1 : 0,
            'qrcode' => $ep['corpWxqrcode'],
        ];
    }

}
