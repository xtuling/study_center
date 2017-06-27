<?php
/**
 * RightService.class.php
 * 活动权限信息表
 * @author: daijun
 * @copyright: vchangyi.com
 */

namespace Common\Service;

use Common\Common\Department;
use Common\Common\Job;
use Common\Common\Role;
use Common\Common\User;
use Common\Model\RightModel;
use VcySDK\Member;
use VcySDK\Service;

class RightService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new RightModel();
    }

    /**
     * 获取参与活动权限（部门、人员、岗位、标签、角色）
     * @author houyingcai
     * @param array $right 参与权限
     * @return array
     */
    public function list_by_right($right)
    {
        $right['uids'] = array_column($right['uids'], 'memID');
        $right['dp_ids'] = array_column($right['dp_ids'], 'dpID');
        $right['job_ids'] = array_column($right['job_ids'], 'jobID');
        $right['role_ids'] = array_column($right['role_ids'], 'roleID');

        // 初始化保存人员UID
        $right['uids'] = !empty($right['uids']) ? array_unique($right['uids']) : array();

        // 处理部门
        if (!empty($right['dp_ids'])) {
            // 部门ID去重
            $right['dp_ids'] = !empty($right['dp_ids']) ? array_unique($right['dp_ids']) : array();
        }
        // 处理岗位
        if (!empty($right['job_ids'])) {
            // 岗位ID去重
            $right['job_ids'] = !empty($right['job_ids']) ? array_unique($right['job_ids']) : array();
        }
        // 处理角色
        if (!empty($right['role_ids'])) {
            // 角色ID去重
            $right['role_ids'] = !empty($right['role_ids']) ? array_unique($right['role_ids']) : array();
        }

        return $right;
    }

    /**
     * 已有权限转为全公司时需要发布消息的人员
     * @author houyingcai
     * @param array $old_list 已有权限
     * @param array $new_list 用户提交的权限
     * @return array
     */
    public function right_to_all($old_list, $new_list)
    {
        $data = array();

        // 格式化数据库中的权限数据
        $old_rights = $this->format_db_data($old_list);
        $old_rights['is_all'] = empty($old_list) ? ActivityService::IS_ALL : 0;

        // 格式化用户提交的权限数据
        $new_rights = $this->format_post_data($new_list);

        // 数据库人员
        $old_uids = $this->list_uids_by_right($old_rights);

        // 当前人员
        $new_uids = $this->list_uids_by_right($new_rights);

        // 需要更新的数据
        $data['update'] = array_intersect($old_uids, $new_uids);

        // 需要新增的数据
        $data['add'] = array_diff($new_uids, $old_uids);

        return $data;
    }

    /**
     * 获取权限表中的人员
     * @author houyingcai
     * @param array $right 权限数据
     * @return array
     */
    public function list_uids_by_right($right)
    {

        $userServ = &User::instance();

        if ($right['is_all'] == ActivityService::IS_ALL) {

            // fixme!!! 这里不允许直接不传参调用listAll，13000人的企业需要17秒才能查询完毕
            $list = $userServ->listAll();
            $uids = array_column($list, 'memUid');

            return $uids;
        }

        // 初始化保存人员UID
        $right['uids'] = isset($right['uids']) ? $right['uids'] : [];

        // 处理部门
        if (!empty($right['dp_ids'])) {

            // 部门ID去重
            $right['dp_ids'] = array_unique($right['dp_ids']);
            sort($right['dp_ids']);

            // 遍历获取部门下的人员，递归查询子部门的人员
            foreach ($right['dp_ids'] as $k => $dpid) {
                $params = [
                    'dpId' => $dpid,
                    'departmentChildrenFlag' => 1,
                ];
                $userList = $userServ->listAll($params);
                if (!empty($userList)) {
                    $right['uids'] = array_merge($right['uids'], array_column($userList, 'memUid'));
                }
            }
        }

        // 处理岗位
        if (!empty($right['job_ids'])) {

            $right['job_ids'] = array_unique($right['job_ids']);
            sort($right['job_ids']);
            // 初始化SDK
            $member = new Member(Service::instance());

            // 获取岗位列表
            $job_list = $member->listAll(array('jobIdList' => $right['job_ids']));
            $job_list = $member->listAll(array('jobIdList' => $right['job_ids']), 1, $job_list['total']);

            // 如果岗位下存在人员
            if (!empty($job_list['list'])) {
                $right['uids'] = array_merge($right['uids'], array_column($job_list['list'], 'memUid'));
            }

        }

        // 处理角色
        if (!empty($right['role_ids'])) {

            $right['role_ids'] = array_unique($right['role_ids']);

            sort($right['role_ids']);

            // 初始化SDK
            $member = new Member(Service::instance());

            // 获取角色列表
            $role_List = $member->listAll(array('roleIdList' => $right['role_ids']));
            $role_List = $member->listAll(array('roleIdList' => $right['role_ids']), 1, $role_List['total']);

            // 如果岗位下存在人员
            if (!empty($role_List['list'])) {
                $right['uids'] = array_merge($right['uids'], array_column($role_List['list'], 'memUid'));
            }

        }

        $uids = array_unique($right['uids']);

        return $uids;
    }

    /**
     * 格式化数据库中的权限数据
     * @author houyingcai
     * @param array $rights 权限数据
     * @return array
     */
    public function format_db_data($rights)
    {
        $data = array();
        // 数据分组
        $data['uids'] = array_filter(array_column($rights, 'uid'));
        $data['dp_ids'] = array_filter(array_column($rights, 'dp_id'));
        // $data['tag_ids'] = array_filter(array_column($rights, 'tag_id'));
        $data['job_ids'] = array_filter(array_column($rights, 'job_id'));
        $data['role_ids'] = array_filter(array_column($rights, 'role_id'));

        return $data;
    }

    /**
     * 格式化用户输入的权限数据
     * @author houyingcai
     * @param array $rights 权限数据
     * @return array
     */
    public function format_post_data($rights)
    {
        $data = array();

        foreach ($rights as $k => $v) {

            // 是否是全公司
            if ($k == 'is_all' && $v == ActivityService::IS_ALL) {

                $data[$k] = $v;

                return $data;
            }

            // 过滤空数组
            if (!is_array($v) || empty($v)) {

                continue;
            }

            switch ($k) {
                case 'uids':
                    $data[$k] = array_column($v, 'memID');
                    break;
                case 'dp_ids':
                    $data[$k] = array_column($v, 'dpID');
                    break;
                // case 'tag_ids':
                //     $data[$k] = array_column($v, 'tagID');
                //     break;
                case 'job_ids':
                    $data[$k] = array_column($v, 'jobID');
                    break;
                case 'role_ids':
                    $data[$k] = array_column($v, 'roleID');
                    break;
            }
        }

        return $data;
    }

    /**
     * 比较权限数据，并返回需要新增、删除和更新的数据
     * @author houyingcai
     * @param array $rights_db 数据库中的权限数据
     * @param array $rights_post 用户输入的权限数据
     * @return array
     */
    public function diff_data($rights_db, $rights_post)
    {
        $keys = array(
            'uids',
            'dp_ids', /*'tag_ids',*/
            'job_ids',
            'role_ids'
        );

        $data = array();
        // 遍历所有权限类型
        foreach ($keys as $key) {
            $rights_old = isset($rights_db[$key]) ? $rights_db[$key] : [];
            $rights_new = isset($rights_post[$key]) ? $rights_post[$key] : [];

            // 需要更新的数据
            $data[$key]['update'] = array_intersect($rights_old, $rights_new);

            // 需要删除的数据
            $data[$key]['del'] = array_diff($rights_old, $rights_new);

            // 需要新增的数据
            $data[$key]['add'] = array_diff($rights_new, $rights_old);
        }

        return $data;
    }

    /**
     * 保存权限数据
     * @author houyingcai
     * @param array $conds 权限筛选条件
     * @param array $data 权限数据
     * @return bool
     */
    public function save_data($conds, $data)
    {
        if (!is_array($conds) || empty($conds) || !is_array($data) || empty($data)) {
            return false;
        }

        // 获取活动权限列表
        $list = $this->_d->list_by_conds($conds);
        // 格式化数据库中的权限数据
        $rights_db = $this->format_db_data($list);
        // 格式化用户输入的权限数据
        $rights_post = $this->format_post_data($data);
        // 比较权限数据，并返回需要新增、删除和更新的数据
        $rights = $this->diff_data($rights_db, $rights_post);

        $add_conds = array();
        $insert_data = array();

        foreach ($rights as $k => $v) {
            // 格式化数据库字段
            $db_k = substr($k, 0, -1);
            // 删除数据
            $del_conds = array_filter($v['del']);
            if (!empty($del_conds)) {

                $del_conds = array_merge($conds, array($db_k => $del_conds));

                $this->_d->delete_by_conds($del_conds);
            }

            // 初始化字段
            $add_conds['uid'] = '';
            $add_conds['dp_id'] = '';
            $add_conds['tag_id'] = '';
            $add_conds['job_id'] = '';
            $add_conds['role_id'] = '';

            foreach ($v['add'] as $obj_id) {
                $add_conds[$db_k] = $obj_id;
                $insert_data[] = array_merge($conds, $add_conds);
            }
        }

        // 批量插入新增数据
        if (!empty($insert_data)) {
            $this->_d->insert_all($insert_data);
        }

        return true;
    }

    /**
     * 获取格式化后的权限数据
     * @author houyingcai
     * @param array $conds 权限筛选条件
     * @return array
     *          + array dp_list   部门信息
     *                    + string dp_id   部门ID
     *                    + string dp_name 部门名称
     *          + array tag_list  标签信息
     *                    + string tag_id   标签ID
     *                    + string tag_name 标签名称
     *          + array user_list 人员信息
     *                    + string uid      用户ID
     *                    + string username 用户姓名
     *                    + string face     头像
     */
    public function get_data($conds)
    {
        $list = $this->list_by_conds($conds);

        $rights_db = $this->format_db_data($list);

        $data = array(
            'user_arr' => array(),
            'dp_arr' => array(),
            // 'tag_arr' => array(),
            'job_arr' => array(),
            'role_arr' => array(),
        );

        foreach ($rights_db as $k => $v) {
            switch ($k) {

                // 部门
                case 'dp_ids':
                    if (!empty($v)) {

                        $dpServ = &Department::instance();
                        sort($v);
                        $dps = $dpServ->listById($v);

                        foreach ($dps as $dp) {
                            $data['dp_arr'][] = [
                                'dpID' => $dp['dpId'],
                                'dpName' => $dp['dpName'],
                            ];
                        }
                    }
                    break;

                // 人员
                case 'uids':
                    if (!empty($v)) {

                        $userServ = &User::instance();
                        sort($v);
                        $users = $userServ->listAll(array('memUids' => $v));
                        // 获取被删除的用户信息
                        $this->user_list($users, $v);

                        foreach ($users as $user) {
                            $data['user_arr'][] = [
                                'memID' => $user['memUid'],
                                'memUsername' => $user['memUsername'],
                                'memFace' => $this->pic_thumbs($user['memFace']),
                            ];
                        }
                    }
                    break;
                // 岗位
                case 'job_ids':
                    if (!empty($v)) {

                        $jobServ = &Job::instance();
                        sort($v);
                        $jobs = $jobServ->listById($v);

                        foreach ($jobs as $job) {
                            $data['job_arr'][] = [
                                'jobID' => $job['jobId'],
                                'jobName' => $job['jobName'],
                            ];
                        }
                    }
                    break;
                // 角色
                case 'role_ids':
                    if (!empty($v)) {

                        $roleServ = &Role::instance();
                        sort($v);
                        $roles = $roleServ->listById($v);

                        foreach ($roles as $role) {
                            $data['role_arr'][] = [
                                'roleID' => $role['roleId'],
                                'roleName' => $role['roleName'],
                            ];
                        }
                    }
                    break;
            }
        }

        return array($list, $data);
    }

    /**
     * 获取当前用户的标签，部门，岗位，用户ID
     * @param array $user 传入当前用户信息
     * @return array
     */
    public function get_by_right($user = array())
    {
        // 获取当前用户的所有部门
        $department = &Department::instance();

        // 获取用户所在部门ID以及子集部门ID
        $dpId = $department->list_dpId_by_uid($user['memUid'], true);

        list($dpIdone, $parent) = $dpId;

        $parent = !empty($parent) ? array_values($parent) : array();

        $dpIds = $dpIdone;

        // 如果存在子集部门ID
        if (!empty($parent)) {

            $dpIds = array_merge($dpIds, $parent);
        }

        // 获取用户详情
        $info = User::instance()->getByUid($user['memUid']);

        return array(
            'memID' => $user['memUid'],
            'dpIds' => $dpIds,
            'roleIds' => $info['role']['roleId'],
            'JobIds' => $info['job']['jobId'],
        );
    }

    /***判断评论权限
     * @param array $right 权限数组
     * @param string $uid 当前用户uid
     * @return bool
     */
    public function check_get_quit($right = array(), $uid = '')
    {
        $arr = array();
        $arr['is_all'] = 0;
        $arr['uids'] = array_filter(array_column($right, 'uid'));
        $arr['dp_ids'] = array_filter(array_column($right, 'dp_id'));
        // $arr['tag_ids'] = array_filter(array_column($right, 'tag_id'));
        $arr['job_ids'] = array_filter(array_column($right, 'job_id'));
        $arr['role_ids'] = array_filter(array_column($right, 'role_id'));
        $user = $this->list_uids_by_right($arr);

        return in_array($uid, $user) ? false : true;
    }
}
