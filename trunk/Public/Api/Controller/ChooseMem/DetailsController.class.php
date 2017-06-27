<?php
/**
 * 企业号管理组关系权限接口
 * DetailsChooseController.class.php
 * $author$ 何岳龙
 * $date$   2016年8月29日11:26:24
 */

namespace Api\Controller\ChooseMem;

use Common\Common\Department;
use Common\Common\User;
use Common\Common\Tag;

class DetailsController extends AbstractController
{

    public function Index()
    {

        // 部门ids数组
        $departmentIds = I("post.departmentIds");
        // 已选部门IDs数组
        $selectedDepartmentIds = I("post.selectedDepartmentIds");
        // 未选中标签IDs数组
        $tagIds = I("post.tagIds");
        // 已选标签IDs数组
        $selectedTagIds = I("post.selectedTagIds");
        // 未选择UIDs数组
        $userIds = I("post.userIds");
        // 已选UIDs数组
        $selectedUserIds = I("post.selectedUserIds");

        $this->_result = array(
            'selectedUserIds' => $this->_listUserIdsByUids($selectedUserIds),
            'userIds' => $this->_listUserIdsByUids($userIds),
            'departmentIds' => $this->_listDepartmentIds($departmentIds),
            'selectedDepartmentIds' => $this->_listDepartmentIds($selectedDepartmentIds),
            'tagIds' => $this->_listTagIds($tagIds),
            'selectedTagIds' => $this->_listTagIds($selectedTagIds)
        );

        return true;
    }

    /**
     * 获取用户 openid
     *
     * @param array $uids 用户UID数组
     *
     * @return array
     */
    protected function _listUserIdsByUids($uids)
    {

        // 如果用户 uid 不是数组或为空，就直接返回空数组
        if (!is_array($uids) || empty($uids)) {
            return array();
        }

        // 用户 UserId 数组
        $userIds = array();
        // 获取用户列表
        $list = User::instance()->listByUid($uids);

        // 重新组装数组
        foreach ($list as $_mem) {
            $userIds[] = array('memUserid' => $_mem['memUserid']);
        }

        return $userIds;
    }

    /**
     * 获取部门列表详情
     *
     * @param array $dpIds 部门IDS
     *
     * @return array
     */

    protected function _listDepartmentIds($dpIds)
    {

        // 如果用户dpIds不是数组或为空，就直接返回空数组
        if (!is_array($dpIds) || empty($dpIds)) {
            return array();
        }

        // 获取部门列表数组
        $wxDepartments = array();
        // 获取全部部门
        $list = Department::instance()->listAll();
        // 遍历部门ID
        foreach ($dpIds as $_dpId) {
            // 获取部门信息
            if (!isset($list[$_dpId])) {
                continue;
            }

            $dp = $list[$_dpId];
            $wxDepartments[] = array(
                'dpId' => $dp['dpThirdid'],
                'dpName' => $dp['dpName']
            );
        }

        return $wxDepartments;
    }

    /**
     * 获取标签列表
     *
     * @param array $tagIds 标签IDs
     *
     * @return array
     */
    protected function _listTagIds($tagIds)
    {

        // 如果TagIds不是数组或为空，就直接返回空数组
        if (!is_array($tagIds) || empty($tagIds)) {
            return array();
        }

        // 获取部门列表数组
        $list = array();
        $data = Tag::instance()->listAll(array('tagIds' => $tagIds));
        // 遍历数组
        foreach ($data['list'] as $_tag) {
            $list[] = array(
                'tagId' => $_tag['tagThirdId'],
                'tagName' => $_tag['tagName']
            );
        }

        return $list;
    }

}
