<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午5:34
 */

namespace Common\Service;


use Com\Validate;
use VcySDK\Role;
use VcySDK\Service;

class RoleService extends AbstractService
{

    private $__role = null;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->__role = new Role(Service::instance());
    }

    /**
     * 搜索角色
     * @param $result  array 角色列表
     * @param $request array 请求参数
     * @return bool
     */
    public function searchList(&$result, $request)
    {

        $condition = array();
        $page = (int)$request['page'];
        $limit = 1500;
        $keyword = (string)$request['keyword'];
        if (!empty($keyword)) {
            $condition['keyword'] = $keyword;
        }

        list(, $limit, $page) = page_limit($page, $limit);
        $sdkResult = $this->__role->listAll($condition, $page, $limit);

        $result = array(
            'list' => $sdkResult['list'],
            'page' => $sdkResult['pageNum'],
            'limit' => $sdkResult['pageSize'],
            'total' => $sdkResult['total']
        );

        return true;
    }

    /**
     * 新增角色信息
     * @param $result  array 角色信息
     * @param $request array 请求参数
     * @return bool
     */
    public function add(&$result, $request)
    {

        $role = $this->_fetchrole($request);
        $this->_validaterole($role);

        $result = $this->__role->create($role);
        return true;
    }

    /**
     * 删除指定角色
     * @param mixed $result
     * @param array $request
     * @return bool
     */
    public function delete(&$result, $request)
    {

        $roleId = (string)$request['roleId'];
        if (empty($roleId)) {
            E('1003:角色ID错误');
            return false;
        }

        $condition = array(
            'roleId' => $roleId
        );
        $this->__role->delete($condition);

        return true;
    }

    /**
     * 获取指定角色信息详情
     * @param $result
     * @param $request
     * @return bool
     */
    public function detail(&$result, $request)
    {

        $roleId = (string)$request['roleId'];
        if (empty($roleId)) {
            E('1003:角色ID错误');
            return false;
        }

        $condition = array(
            'roleId' => $roleId
        );
        $result = $this->__role->detail($condition);

        return true;
    }

    /**
     * 编辑指定角色信息
     * @param $result
     * @param $request
     * @return bool
     */
    public function edit(&$result, $request)
    {

        $roleId = (string)$request['roleId'];
        if (empty($roleId)) {
            E('1003:角色ID错误');
            return false;
        }

        $role = $this->_fetchrole($request);
        $this->_validaterole($role);

        $role['roleId'] = $roleId;

        $result = $this->__role->modify($role);

        return true;
    }

    /**
     * 获取角色信息
     * @param $request
     * @return array
     */
    protected function _fetchrole($request)
    {

        return array(
            'roleName' => $request['roleName'],
            'roleDisplayorder' => (int)$request['roleDisplayorder']
        );
    }

    /**
     * 检查角色信息合法性
     * @param $role
     * @return bool
     */
    protected function _validaterole(&$role)
    {

        $rules = array(
            'roleName' => 'require|length:2,80'
        );
        $msgs = array(
            'roleName.require' => L('1001:角色名称不能为空'),
            'roleName.length' => L('1002:角色名称长度不合法')
        );
        // 开始验证
        $validate = new Validate($rules, $msgs);
        if (!$validate->check($role)) {
            E($validate->getError());
            return false;
        }

        return true;
    }

}