<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/21
 * Time: 17:14
 */
namespace Common\Service;

use Common\Model\ConfigModel;
use Common\Common\Constant;
use Common\Common\Department;
use Common\Common\Tag;
use Common\Common\User;
use Common\Common\Job;
use Common\Common\Role;

class ConfigService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ConfigModel();
    }

    /**
     * 格式化用户输入的权限数据
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     */
    public function formatPostRight($rights)
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
     * 获取格式化后的权限数据
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     *          + int is_all               是否全公司（1=否；2=是）
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
    public function getRightData($rights)
    {
        $data = [
            'is_all' => Constant::RIGHT_IS_ALL_FALSE,
            'dp_list' => [],
            'tag_list' => [],
            'user_list' => [],
            'job_list' => [],
            'role_list' => [],
        ];

        foreach ($rights as $k => $v) {
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
     * 获取配置数据
     * @author zhonglei
     * @return array
     */
    public function getData()
    {
        $list = $this->list_all();

        if (empty($list)) {
            // 默认数据
            $data = [
                // 权限默认为全公司
                'rights' => [
                    Constant::RIGHT_TYPE_ALL => Constant::RIGHT_IS_ALL_TRUE,
                ],
            ];
        } else {
            $data['rights'] = unserialize($list[0]['rights']);
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
}
