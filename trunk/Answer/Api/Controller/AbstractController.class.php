<?php
/**
 * Created by PhpStorm.
 * User: liyifei
 * Date: 16/9/13
 * Time: 下午14:10
 */
namespace Api\Controller;

use Common\Controller\Api;
use Common\Common\Config;
use Common\Common\Constant;
use Common\Common\Department;
use Common\Common\Tag;

abstract class AbstractController extends Api\AbstractController
{
    /**
     * 权限验证
     */
    public function before_action($action = '')
    {
        parent::before_action($action);

        if ($this->_require_login) {
            if (empty($this->uid) || !$this->_checkUserRight()) {
                E('_ERR_PERMISSION_DENIED');
            }
        }
        
        return true;
    }

    /**
     * 获取当前用户权限数据
     * @author zhonglei
     * @return array
     */
    private function _getUserRight()
    {
        $user = $this->_login->user;
        $data = [];

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
        $data[Constant::RIGHT_TYPE_ALL] = Constant::RIGHT_IS_ALL_TRUE;

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
     * 验证当前用户访问权限
     * @author zhonglei
     * @return bool
     */
    private function _checkUserRight()
    {
        $config = &Config::instance()->getCacheData();

        // 全公司
        if (isset($config['rights'][Constant::RIGHT_TYPE_ALL]) &&
            $config['rights'][Constant::RIGHT_TYPE_ALL] == Constant::RIGHT_IS_ALL_TRUE) {
            return true;
        }

        $user_rights = $this->_getUserRight();

        foreach ($config['rights'] as $type => $v) {
            if (is_array($v) && isset($user_rights[$type]) && array_intersect($v, $user_rights[$type])) {
                return true;
            }
        }

        return false;
    }
}
