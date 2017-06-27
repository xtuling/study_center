<?php
/**
 * 试卷-权限表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:50:52
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Department;
use Common\Common\Job;
use Common\Common\Role;
use Common\Common\Tag;
use Common\Common\User;
use Common\Model\RightModel;
use VcySDK\Member;
use VcySDK\Service;

class RightService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new RightModel();

        parent::__construct();
    }

    /**
     * 格式化数据库中的权限数据
     * @author daijun
     * @param array $rights 权限数据
     * @return array
     */
    public function format_db_data($rights)
    {
        $data = array();
        // 数据分组
        $data['uids'] = array_filter(array_column($rights, 'uid'));
        $data['dp_ids'] = array_filter(array_column($rights, 'cd_id'));
        $data['tag_ids'] = array_filter(array_column($rights, 'tag_id'));
        $data['job_ids'] = array_filter(array_column($rights, 'job_id'));
        $data['role_ids'] = array_filter(array_column($rights, 'role_id'));

        return $data;
    }

    /**
     * 获取格式化后的权限数据
     * @author daijun
     * @param array $conds 权限筛选条件
     * @return  array $data 组装后权限数组
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
     *           array $list 原始权限数组
     */
    public function get_right_data($conds)
    {
        $list = $this->_d->list_by_conds($conds);

        $rights_db = $this->format_db_data($list);

        $data = array(
            'user_list' => array(),
            'dp_list' => array(),
            'tag_list' => array(),
            'job_list' => array(),
            'role_list' => array(),
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
                            $data['dp_list'][] = [
                                'dpID' => $dp['dpId'],
                                'dpName' => $dp['dpName'],
                            ];
                        }
                    }
                    break;

                // 标签
                case 'tag_ids':
                    if (!empty($v)) {
                        $tagServ = &Tag::instance();
                        sort($v);
                        $tags = $tagServ->listAll($v);
                        foreach ($tags as $tag) {
                            $data['tag_list'][] = [
                                'tagID' => $tag['tagId'],
                                'tagName' => $tag['tagName'],
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

                        // 查询出来的用户UID列表
                        $uid_list = array_column($users, 'memUid');
                        // 获取被删除的用户
                        $this->user_list($users, $v, $uid_list);

                        foreach ($users as $user) {
                            $data['user_list'][] = [
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
                        $jobs = $jobServ->listById($v);

                        foreach ($jobs as $job) {
                            $data['job_list'][] = [
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
                        $roles = $roleServ->listById($v);

                        foreach ($roles as $role) {
                            $data['role_list'][] = [
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
     * 获取权限表中的人员(即应参与人员集合)
     * @author houyingcai
     * @param array $right 权限数据
     * @return array
     */
    public function list_uids_by_right($right)
    {
        $userServ = &User::instance();
        if ($right['is_all'] == self::AUTH_ALL) {
            $list = $userServ->listAll();
            $uids = array_column($list, 'memUid');

            return $uids;
        }

        // 初始化保存人员UID
        $right['uids'] = isset($right['uids']) ? $right['uids'] : [];

        // 处理标签,取出标签中的部门及人员
        if (!empty($right['tag_ids'])) {

            sort($right['tag_ids']);
            $tagServ = &Tag::instance();
            $tagMember = $tagServ->listAllMember(['tagIds' => $right['tag_ids']]);

            foreach ($tagMember as $v) {
                // 合并部门
                if ($v['dpId']) {
                    $right['dp_ids'][] = $v['dpId'];
                }
                // 合并人员
                if ($v['memUid']) {
                    $right['uids'][] = $v['memUid'];
                }
            }

        }

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
            $job_list = $member->listAll(array('jobIdList' => $right['job_ids']),1,1);
            $job_list = $member->listAll(array('jobIdList' => $right['job_ids']),1,$job_list['total']);

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
            $role_List = $member->listAll(array('roleIdList' => $right['role_ids']),1,1);
            $role_List = $member->listAll(array('roleIdList' => $right['role_ids']),1,$role_List['total']);

            // 如果岗位下存在人员
            if (!empty($role_List['list'])) {
                $right['uids'] = array_merge($right['uids'], array_column($role_List['list'], 'memUid'));
            }

        }
        
        $uids = array_unique($right['uids']);

        return $uids;
    }

    /**
     * 获取当前用户的标签，部门，岗位，用户ID
     * @author 英才
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
            'jobIds' => $info['job']['jobId'],
            'roleIds' => $info['role']['roleId'],
        );
    }

    /***判断考试权限
     * @author 英才
     * @param array $right 权限数组
     * @param string $uid 当前用户uid
     * @return bool
     */
    public function check_get_quit($right = array(), $uid = '')
    {
        $arr = array();
        $arr['is_all'] = 0;
        $arr['uids'] = array_filter(array_column($right, 'uid'));
        $arr['dp_ids'] = array_filter(array_column($right, 'cd_id'));
        $arr['tag_ids'] = array_filter(array_column($right, 'tag_id'));
        $arr['job_ids'] = array_filter(array_column($right, 'job_id'));
        $arr['role_ids'] = array_filter(array_column($right, 'role_id'));
        $user = $this->list_uids_by_right($arr);

        return in_array($uid, $user) ? false : true;
    }
}
