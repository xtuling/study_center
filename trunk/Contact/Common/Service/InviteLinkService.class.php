<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/23
 * Time: 下午6:09
 */

namespace Common\Service;


use Common\Common\Department;
use Common\Model\InviteLinkModel;

class InviteLinkService extends AbstractService
{

    public function __construct()
    {

        parent::__construct();
        $this->_d = new InviteLinkModel();
    }

    /**
     * 读取邀请连接信息
     * @param $result
     * @param $request
     * @param $user
     * @return bool
     */
    public function readLink(&$result, $request, $user)
    {

        $result = $this->_d->get_by_conds(array('invite_uid' => $user['memUid']));
        if (!empty($result)) {
            $result['default_data'] = unserialize($result['default_data']);
        } else {
            $result['default_data'] = array();
        }

        return true;
    }

    /**
     * 新邀请连接
     * @param $result
     * @param $request
     * @param $user
     * @return bool
     */
    public function newLink(&$result, $request, $user)
    {

        $data = $this->_getLink($request);
        $departments = Department::instance()->listAll();
        $dpId = $data['default_data']['department']['dpId'];
        if (empty($dpId) || empty($departments[$dpId])) {
            E('1001:部门ID不正确');
            return false;
        }

        // 读取字段配置
        $attrService = new AttrService();
        $fields = $attrService->getAttrList(true, array(), false, false);
        $fields = array_combine_by_key($fields, 'field_name');

        $settingService = new InviteSettingService();
        $setting = $settingService->get_by_conds([]);
        // 邀请者填写
        $inviterWrite = array();
        if (!empty($setting['inviter_write'])) {
            $inviterWrite = unserialize($setting['inviter_write']);
        }

        if (!empty(in_array('job', $inviterWrite)) && empty($data['default_data']['job']) && 1 == $fields['memJob']['is_open']) {
            E('1002:未选择岗位');
            return false;
        }

        if (!empty(in_array('role', $inviterWrite)) && empty($data['default_data']['role']) && 1 == $fields['memRole']['is_open']) {
            E('1003:未选择角色');
            return false;
        }

        if (!in_array('job', $inviterWrite) || 0 == $fields['memJob']['is_open']) {
            unset($data['default_data']['job']);
        }

        if (!in_array('role', $inviterWrite) || 0 == $fields['memRole']['is_open']) {
            unset($data['default_data']['role']);
        }

        // 默认数据
        $data['default_data'] = serialize($data['default_data']);
        $data['type'] = $setting['type'];
        $data['invite_uid'] = $user['memUid'];

        if (empty($data['link_id'])) {
            $data['link_id'] = $this->_d->insert($data);
        } else {
            $link_id = $data['link_id'];
            unset($data['link_id']);
            $this->_d->update($link_id, $data);
        }

        $result = $data;
        return true;
    }

    /**
     * 获取邀请连接信息
     * @param $request
     * @return array
     */
    protected function _getLink($request)
    {

        return array(
            'default_data' => array(
                'department' => $request['default_data']['department'],
                'job' => $request['default_data']['job'],
                'role' => $request['default_data']['role']
            ),
            'link_id' => $request['link_id']
        );
    }

}