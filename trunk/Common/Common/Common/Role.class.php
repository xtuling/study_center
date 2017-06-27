<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/1
 * Time: 17:31
 */

namespace Common\Common;

class Role
{
    /**
     * 角色列表
     */
    protected $_role_list = [];

    public function __construct()
    {
        $cache = &Cache::instance();
        $this->_role_list = $cache->get('Common.Role', '', cfg('DATA_CACHE_TIME'));
    }

    /**
     * 实例化
     * @return Role
     */
    public static function &instance()
    {
        static $instance;

        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 获取所有角色
     * @return array|mixed
     */
    public function listAll()
    {
        return $this->_role_list;
    }

    /**
     * 根据角色ID数组获取对应的角色信息
     * @param array $role_ids 角色ID数组
     * @return array
     */
    public function listById($role_ids)
    {
        if (!is_array($role_ids) || empty($this->_role_list)) {
            return [];
        }

        $list = [];

        foreach ($role_ids as $role_id) {
            if (isset($this->_role_list[$role_id])) {
                $list[$role_id] = $this->_role_list[$role_id];
            }
        }

        return $list;
    }

    /**
     * 根据角色ID获取对应的角色信息
     * @param string $role_id 角色ID数组
     * @return array
     */
    public function getById($role_id)
    {
        return !empty($role_id) && isset($this->_role_list[$role_id]) ? $this->_role_list[$role_id] : [];
    }
}
