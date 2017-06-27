<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:39
 */
namespace Common\Service;

use Common\Common\Constant;
use Common\Common\Department;
use Common\Common\Tag;
use Common\Common\User;
use Common\Common\Job;
use Common\Common\Role;
use Common\Model\RightModel;

class RightService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new RightModel();
    }

    /**
     * 格式化数据库中的权限数据
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     */
    public function formatDBData($rights)
    {
        $data = [];

        // 数据分组
        foreach ($rights as $right) {
            $data[$right['obj_type']][] = $right['obj_id'];
        }

        return $data;
    }

    /**
     * 格式化用户输入的权限数据
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     */
    public function formatPostData($rights)
    {
        $data = [];

        foreach ($rights as $k => $v) {
            // 全公司
            if ($k == 'is_all' && $v == Constant::RIGHT_IS_ALL_TRUE) {
                $data = [Constant::RIGHT_TYPE_ALL => [Constant::RIGHT_IS_ALL_TRUE]];
                break;
            }

            // 过滤空数组
            if (!is_array($v) || empty($v)) {
                continue;
            }

            // 数据分组
            switch ($k) {
                case 'dp_ids':
                    $data[Constant::RIGHT_TYPE_DEPARTMENT] = $v;
                    break;
                case 'tag_ids':
                    $data[Constant::RIGHT_TYPE_TAG] = $v;
                    break;
                case 'uids':
                    $data[Constant::RIGHT_TYPE_USER] = $v;
                    break;
                case 'job_ids':
                    $data[Constant::RIGHT_TYPE_JOB] = $v;
                    break;
                case 'role_ids':
                    $data[Constant::RIGHT_TYPE_ROLE] = $v;
                    break;
            }
        }

        return $data;
    }

    /**
     * 比较权限数据，并返回需要新增和删除的数据
     * @author zhonglei
     * @param array $rights_db 数据库中的权限数据
     * @param array $rights_post 用户输入的权限数据
     * @return array
     */
    public function diffData($rights_db, $rights_post)
    {
        $keys = [
            Constant::RIGHT_TYPE_ALL,
            Constant::RIGHT_TYPE_DEPARTMENT,
            Constant::RIGHT_TYPE_TAG,
            Constant::RIGHT_TYPE_USER,
            Constant::RIGHT_TYPE_JOB,
            Constant::RIGHT_TYPE_ROLE,
        ];

        $data = [];

        // 遍历所有权限类型
        foreach ($keys as $key) {
            $rights_old = isset($rights_db[$key]) ? $rights_db[$key] : [];
            $rights_new = isset($rights_post[$key]) ? $rights_post[$key] : [];

            // 需要删除的数据
            $data[$key]['del'] = array_diff($rights_old, $rights_new);

            // 需要新增的数据
            $data[$key]['add'] = array_diff($rights_new, $rights_old);
        }

        return $data;
    }

    /**
     * 保存权限数据
     * @author zhonglei
     * @param array $conds 权限筛选条件
     * @param array $data 权限数据
     *        + int is_all 是否全公司
     *        + array dp_ids 部门ID
     *        + array tag_ids 标签ID
     *        + array uids 用户ID
     *        + array job_ids 职位ID
     *        + array role_ids 角色ID
     * @return bool
     */
    public function saveData($conds, $data)
    {
        if (!is_array($conds) || empty($conds) || !is_array($data) || empty($data)) {
            return false;
        }

        $list = $this->list_by_conds($conds);
        $rights_db = $this->formatDBData($list);
        $rights_post = $this->formatPostData($data);
        $rights = $this->diffData($rights_db, $rights_post);
        $insert_data = [];

        foreach ($rights as $k => $v) {
            // 删除数据
            if (!empty($v['del'])) {
                $del_conds = array_merge($conds, ['obj_type' => $k, 'obj_id' => $v['del']]);
                $this->delete_by_conds($del_conds);
            }

            // 新增数据
            foreach ($v['add'] as $obj_id) {
                $insert_data[] = array_merge($conds, [
                    'obj_type' => $k,
                    'obj_id' => $obj_id,
                ]);
            }
        }

        // 批量插入新增数据
        if (!empty($insert_data)) {
            $this->insert_all($insert_data);
        }

        return true;
    }

    /**
     * 获取格式化后的权限数据
     * @author zhonglei
     * @param array $conds 权限筛选条件
     * @return array
     *          + int is_all    是否全公司（1=否；2=是）
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
     *          + array job_list  职位信息
     *                    + string job_id   职位ID
     *                    + string job_name 职位名称
     *          + array role_list  角色信息
     *                    + string role_id   角色ID
     *                    + string role_name 角色名称
     */
    public function getData($conds)
    {
        $list = $this->list_by_conds($conds);
        $rights_db = $this->formatDBData($list);
        $data = [
            'is_all' => Constant::RIGHT_IS_ALL_FALSE,
            'dp_list' => [],
            'tag_list' => [],
            'user_list' => [],
            'job_list' => [],
            'role_list' => [],
        ];

        foreach ($rights_db as $k => $v) {
            switch ($k) {
                // 全公司
                case Constant::RIGHT_TYPE_ALL:
                    $data['is_all'] = Constant::RIGHT_IS_ALL_TRUE;
                    break;

                // 部门
                case Constant::RIGHT_TYPE_DEPARTMENT:
                    $dpServ = &Department::instance();
                    $dps = $dpServ->listById($v);

                    foreach ($dps as $dp) {
                        $data['dp_list'][] = [
                            'dp_id' => $dp['dpId'],
                            'dp_name' => $dp['dpName'],
                        ];
                    }
                    break;

                // 标签
                case Constant::RIGHT_TYPE_TAG:
                    $tagServ = &Tag::instance();
                    $tags = $tagServ->listAll($v);

                    foreach ($tags as $tag) {
                        $data['tag_list'][] = [
                            'tag_id' => $tag['tagId'],
                            'tag_name' => $tag['tagName'],
                        ];
                    }
                    break;

                // 人员
                case Constant::RIGHT_TYPE_USER:
                    $userServ = &User::instance();
                    $users = $userServ->listAll(['memUids' => $v]);

                    foreach ($users as $user) {
                        $data['user_list'][] = [
                            'uid' => $user['memUid'],
                            'username' => $user['memUsername'],
                            'face' => $user['memFace'],
                        ];
                    }
                    break;

                // 职位
                case Constant::RIGHT_TYPE_JOB:
                    $jobServ = &Job::instance();
                    $jobs = $jobServ->listById($v);

                    foreach ($jobs as $job) {
                        $data['job_list'][] = [
                            'job_id' => $job['jobId'],
                            'job_name' => $job['jobName'],
                        ];
                    }
                    break;

                // 角色
                case Constant::RIGHT_TYPE_ROLE:
                    $roleServ = &Role::instance();
                    $roles = $roleServ->listById($v);

                    foreach ($roles as $role) {
                        $data['role_list'][] = [
                            'role_id' => $role['roleId'],
                            'role_name' => $role['roleName'],
                        ];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * 根据权限数据，获取对应的用户ID
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     */
    public function getUidsByRight($rights)
    {
        $dp_ids = [];
        $uids = [];
        $conds = [];

        // 全公司
        if (isset($rights[Constant::RIGHT_TYPE_ALL])) {
            $userServ = User::instance();
            $list = $userServ->listAll();
            $uids = array_column($list, 'memUid');

            // 直接返回
            return $uids;
        }

        // 部门
        if (isset($rights[Constant::RIGHT_TYPE_DEPARTMENT])) {
            $dp_ids = $rights[Constant::RIGHT_TYPE_DEPARTMENT];
        }

        // 标签
        if (isset($rights[Constant::RIGHT_TYPE_TAG])) {
            $tagServ = Tag::instance();
            $list = $tagServ->listAllMember(['tagIds' => $rights[Constant::RIGHT_TYPE_TAG]]);

            foreach ($list as $v) {
                // 标签中的部门
                if (isset($v['dpId']) && !empty($v['dpId'])) {
                    $dp_ids[] = $v['dpId'];

                    // 标签中的人员
                } elseif (isset($v['memUid']) && !empty($v['memUid'])) {
                    $uids[] = $v['memUid'];
                }
            }
        }

        // 部门ID
        if (!empty($dp_ids)) {
            $conds['dpIdList'] = $dp_ids;
            $conds['departmentChildrenFlag'] = 1;
        }

        // 职位
        if (isset($rights[Constant::RIGHT_TYPE_JOB])) {
            $conds['jobIdList'] = $rights[Constant::RIGHT_TYPE_JOB];
        }

        // 角色
        if (isset($rights[Constant::RIGHT_TYPE_ROLE])) {
            $conds['roleIdList'] = $rights[Constant::RIGHT_TYPE_ROLE];
        }

        // 根据筛选条件获取用户列表
        if (!empty($conds)) {
            $userServ = User::instance();
            $list = $userServ->listAll($conds);
            $data = array_column($list, 'memUid');
            $uids = array_merge($uids, $data);
        }

        // 人员
        if (isset($rights[Constant::RIGHT_TYPE_USER])) {
            $uids = array_merge($uids, $rights[Constant::RIGHT_TYPE_USER]);
        }

        $uids = array_unique($uids);
        return $uids;
    }

    /**
     * 获取用户权限数据
     * @author zhonglei
     * @param array $user 用户信息
     * @return array
     */
    public function getUserRight($user)
    {
        $data = [];

        if (!is_array($user) || empty($user)) {
            return $data;
        }

        // 标签
        if (isset($user['tagName']) && !empty($user['tagName'])) {
            // 获取标签ID
            $data[Constant::RIGHT_TYPE_TAG] = array_column($user['tagName'], 'tagId');

            // 获取标签成员
            $tagServ = &Tag::instance();
            $members = $tagServ->listAllMember(['tagIds' => $data[Constant::RIGHT_TYPE_TAG]]);

            // 获取标签成员中的部门ID
            $dp_ids = array_column($members, 'dpId');

            if ($dp_ids) {
                $data[Constant::RIGHT_TYPE_DEPARTMENT] = array_filter(array_unique($dp_ids));
            }
        }

        // 部门
        if (isset($user['dpName']) && !empty($user['dpName'])) {
            $dp_ids = array_column($user['dpName'], 'dpId');

            // 合并标签成员中的部门ID
            if (isset($data[Constant::RIGHT_TYPE_DEPARTMENT])) {
                $dp_ids = array_unique(array_merge($data[Constant::RIGHT_TYPE_DEPARTMENT], $dp_ids));
            }

            // 获取子级部门ID
            $dpServ = &Department::instance();
            $child_ids = $dpServ->list_childrens_by_cdid($dp_ids);
            $dp_ids = array_merge($dp_ids, array_values($child_ids));

            $data[Constant::RIGHT_TYPE_DEPARTMENT] = array_unique($dp_ids);
        }

        // 全公司
        $data[Constant::RIGHT_TYPE_ALL] = Constant::RIGHT_TYPE_ALL;

        // 用户
        $data[Constant::RIGHT_TYPE_USER] = [$user['memUid']];

        // 职位
        if (isset($user['job']['jobId'])) {
            $data[Constant::RIGHT_TYPE_JOB] = [$user['job']['jobId']];
        }

        // 角色
        if (isset($user['role']['roleId'])) {
            $data[Constant::RIGHT_TYPE_ROLE] = [$user['role']['roleId']];
        }

        return $data;
    }

    /**
     * 检查用户是否有权限阅读新闻
     * @author zhonglei
     * @param array $user 用户信息
     * @param int $article_id 新闻ID
     * @return bool
     */
    public function checkUserRight($user, $article_id)
    {
        $rights = $this->getUserRight($user);
        $count = $this->_d->countByRight($article_id, $rights);
        return $count > 0;
    }
}
