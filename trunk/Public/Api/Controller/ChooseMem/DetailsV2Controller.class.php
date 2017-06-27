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

class DetailsV2Controller extends AbstractController
{

    public function Index()
    {

        // 部门ids数组
        $departmentIds = (array)I("post.departmentIds");
        // 已选部门IDs数组
        $selectedDepartmentIds = (array)I("post.selectedDepartmentIds");
        // 获取部门数据
        $ids = array_merge($departmentIds, $selectedDepartmentIds);
        $departments = $this->_listDepartment($ids);

        // 未选中标签IDs数组
        $tagIds = (array)I("post.tagIds");
        // 已选标签IDs数组
        $selectedTagIds = (array)I("post.selectedTagIds");
        // 获取标签数据
        $ids = array_merge($tagIds, $selectedTagIds);
        $tags = $this->_listTag($ids);

        // 未选择UIDs数组
        $userIds = (array)I("post.userIds");
        // 已选UIDs数组
        $selectedUserIds = (array)I("post.selectedUserIds");
        // 获取用户数据
        $ids = array_merge($userIds, $selectedUserIds);
        $users = $this->_listUser($ids);

        $this->_result = array(
            'users' => $this->_pickData($users, $userIds),
            'selectedUsers' => $this->_pickData($users, $selectedUserIds),
            'departments' => $this->_pickData($departments, $departmentIds),
            'selectedDepartments' => $this->_pickData($departments, $selectedDepartmentIds),
            'tags' => $this->_pickData($tags, $tagIds),
            'selectedTags' => $this->_pickData($tags, $selectedTagIds)
        );

        return true;
    }

    /**
     * 从源数据中检出指定键值数据
     * @param $sourceData
     * @param $keys
     * @return array
     */
    protected function _pickData($sourceData, $keys)
    {

        $result = array();
        foreach ($keys as $_k) {
            if (empty($sourceData[$_k])) {
                continue;
            }

            $result[] = $sourceData[$_k];
        }

        return $result;
    }

    /**
     * 获取用户 openid
     *
     * @param array $uids 用户UID数组
     *
     * @return array
     */
    protected function _listUser($uids)
    {

        // 如果用户 uid 不是数组或为空，就直接返回空数组
        if (!is_array($uids) || empty($uids)) {
            return array();
        }

        // 获取用户列表
        $list = User::instance()->listByUid($uids);

        return array_combine_by_key($list, 'memUid');
    }

    /**
     * 获取部门列表详情
     *
     * @param array $dpIds 部门IDS
     *
     * @return array
     */

    protected function _listDepartment($dpIds)
    {

        // 如果用户dpIds不是数组或为空，就直接返回空数组
        if (!is_array($dpIds) || empty($dpIds)) {
            return array();
        }

        // 获取部门列表数组
        $departments = array();
        // 获取全部部门
        $list = Department::instance()->listAll();
        // 遍历部门ID
        foreach ($dpIds as $_dpId) {
            // 获取部门信息
            if (!isset($list[$_dpId])) {
                continue;
            }

            $departments[$_dpId] = $list[$_dpId];
        }

        return $departments;
    }

    /**
     * 获取标签列表
     *
     * @param array $tagIds 标签IDs
     *
     * @return array
     */
    protected function _listTag($tagIds)
    {

        // 如果TagIds不是数组或为空，就直接返回空数组
        if (!is_array($tagIds) || empty($tagIds)) {
            return array();
        }

        // 获取部门列表数组
        $data = Tag::instance()->listAll(array('tagIds' => $tagIds));

        return array_combine_by_key($data['list'], 'tagId');
    }

}
