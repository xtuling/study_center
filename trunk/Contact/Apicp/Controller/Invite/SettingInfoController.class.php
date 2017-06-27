<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:44
 */

namespace Apicp\Controller\Invite;

use Common\Common\User;
use Common\Common\Department;
use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteSettingService;

class SettingInfoController extends AbstractController
{

    /**
     * 【通讯录】获取邀请函设置
     * @author liyifei
     */
    public function Index_post()
    {

        $settingServ = new InviteSettingService();
        $data = $settingServ->get_by_conds([]);
        if (empty($data)) {
            E('_ERR_DATA_IS_NULL');
        }

        // 读取所有已设置开启的属性详情
        $newForm = [];
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, [AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER], true);
        foreach ($attrs as $k => $attr) {
            $newForm[$k] = [
                'order' => intval($attr['order']),
                'postion' => intval($attr['postion']),
                'attr_name' => $attr['attr_name'],
                'field_name' => $attr['field_name'],
                'is_open' => intval($attr['is_open']),
                'is_open_edit' => intval($attr['is_open_edit']),
                'is_required' => intval($attr['is_required']),
                'is_required_edit' => intval($attr['is_required_edit']),
            ];
        }

        // 格式化邀请人员、审批人员、部门信息
        $inviteUdpids = array(
            'selectedList' => array()
        );
        if (!empty($data['invite_udpids'])) {
            $inviteUdpids = unserialize($data['invite_udpids']);
            if (empty($inviteUdpids['selectedList'])) {
                $inviteUdpids['selectedList'] = array();
            }
        }
        // 格式化审核人员
        $checkUdpids = '';
        if (!empty($data['check_udpids'])) {
            $checkUdpids = $this->formateUser(unserialize($data['check_udpids']));
        }
        // 格式化部门
        $department = '';
        if (!empty($data['departments'])) {
            $department = $this->formateDepartment(unserialize($data['departments']));
        }
        // 邀请者填写
        $inviterWrite = array();
        if (!empty($data['inviter_write'])) {
            $inviterWrite = unserialize($data['inviter_write']);
        }

        $this->_result = [
            'type' => intval($data['type']),
            'qrcode_expire' => intval($data['qrcode_expire']),
            'departments' => $department,
            'inviter' => $inviteUdpids,
            'checker' => $checkUdpids,
            'form' => $newForm,
            'check_type' => $data['check_type'],
            'inviter_write' => $inviterWrite
        ];

        return true;
    }

    /**
     * 格式化人员信息
     * @param $uids
     * @return array
     */
    public function formateUser($uids)
    {

        $data = [];
        $user = new User();
        $list = $user->listByUid($uids);
        foreach ($list as $k => $item) {
            $data[] = [
                'uid' => $item['memUid'],
                'name' => $item['memUsername'],
                'face' => $item['memFace'],
            ];
        }
        return $data;
    }

    /**
     * 格式化部门信息
     * @param $dps
     * @return array
     */
    public function formateDepartment($dps)
    {

        $data = [];
        $department = new Department();
        $list = $department->listById($dps);
        foreach ($list as $k => $item) {
            $data[] = [
                'dp_id' => $item['dpId'],
                'dp_name' => $item['dpName'],
            ];
        }
        return $data;
    }
}
