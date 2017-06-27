<?php
/**
 * 获取常用人员列表
 *
 * User: 原习斌
 * Date: 2016-08-29
 */

namespace Apicp\Controller\ChooseMem;

use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Tag;
use Common\Common\User;
use Common\Service\CommonChooseService;
use Common\Model\CommonChooselogModel;

class OftenUsedController extends AbstractController
{

    /**
     * 常用人员列表数组
     *
     * @var array
     */
    protected $_list = array();

    /**
     * service对象
     *
     * @var CommonChooseService
     */
    protected $_serv;

    /**
     * 用户列表信息
     *
     * @var array
     */
    protected $_member = array();

    /**
     * 部门列表信息
     *
     * @var array
     */
    protected $_department = array();

    /**
     * 标签列表信息
     *
     * @var array
     */
    protected $_tag = array();

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $this->_serv = new CommonChooseService();
        return true;
    }

    public function Index()
    {
        $search = I("post.search");
        $limit = I('post.limit', 10, 'intval');
        $page = I('post.page', 1, 'intval');

        // 获取分页参数
        list ($start, $limit, $page) = page_limit($page, $limit);
        $page_option = array($start, $limit);

        // 重新组合数组
        foreach ($search['dpIds'] as $v) {
            $search['uids'][] = $v;
        }

        // 重新组合数组
        foreach ($search['tagIds'] as $v) {
            $search['uids'][] = $v;
        }

        array_filter($search['uids']);

        // 合并数据
        $search_new['uids'] = array_unique($search['uids']);;

        // 获取应用权限
        $Member = new Member(Service::instance());

        // 获取权限列表
        $authList = $Member->appAllow();

        // 获取人员部门标签权限
        list($auth_memUid, $auth_dpIds, $auth_TagIds) = $this->get_auth($authList);

        // 获取权限
        $auth = array_merge_recursive($auth_memUid, $auth_dpIds, $auth_TagIds);

        // 如果存在获取交集
        $search_new['uids'] = empty($search_new['uids']) ? $auth : array_intersect($search_new['uids'], $auth);


        // 获取列表数据
        $this->_list = $this->_serv->get_often_used($this->_login->user['eaId'], $search_new, $page_option);

        // 格式化列表
        $list = $this->_formart_list();

        // 返回输出
        $this->_result = array(
            'limit' => $limit,
            'page' => $page,
            'total' => $this->_serv->count_for_often_used($this->_login->user['eaId'], $search_new),
            'list' => $list
        );
        return true;
    }

    /**
     * 根据多个用户ID获取用户信息
     *
     * @param array $ids 用户ID数组
     *
     * @return true
     */
    protected function _get_members($ids)
    {

        // 如果ID不是数组或为空，就直接返回空数组
        if (!is_array($ids) || empty($ids)) {
            return true;
        }

        // 调用缓存获取用户信息
        $user = new User();
        $this->_member = $user->listByUid($ids);

        return true;
    }

    /**
     * 根据多个部门ID获取部门信息
     *
     * @param array $ids 部门ID数组
     *
     * @return bool
     */
    protected function _get_department($ids)
    {

        // 如果部门ID不是数组或为空，就直接返回空数组
        if (!is_array($ids) || empty($ids)) {
            return true;
        }

        // 从缓存中获取部门信息
        $department = new \Common\Common\Department();
        $this->_department = $department->listById($ids);

        return true;
    }

    /**
     * 根据多个标签ID获取标签信息
     *
     * @param array $ids 标签ID数组
     *
     * @return bool
     */
    protected function _get_tag($ids)
    {

        // 如果标签ID不是数组或为空，就直接返回空数组
        if (!is_array($ids) || empty($ids)) {
            return true;
        }

        // 获取标签列表
        $tag = new Tag(Service::instance());
        $tag_list = $tag->listAll(array('tagIds' => $ids));
        // 需要循环一下，因为键名不是ID，所以找起来很麻烦
        foreach ($tag_list['list'] as $v) {
            $this->_tag[$v['tagId']] = $v;
        }

        return true;
    }

    /**
     * 格式化列表
     *
     * @return array
     */
    protected function _formart_list()
    {
        // 如果列表是空的，就返回空数组
        if (!is_array($this->_list) || empty($this->_list)) {
            return array();
        }

        // 循环列表，分别剔出人员部门和标签
        $memIDs = array();
        $depIDs = array();
        $tagIDs = array();
        foreach ($this->_list as $v) {
            // 根据type拆出人员、部门和标签ID分别放在对应的数组里
            switch ($v['choose_type']) {
                case CommonChooselogModel::CHOOSE_MEM:
                    $memIDs[] = $v['chooseid'];
                    break;
                case CommonChooselogModel::CHOOSE_DEP:
                    $depIDs[] = $v['chooseid'];
                    break;
                case CommonChooselogModel::CHOOSE_TAG:
                    $tagIDs[] = $v['chooseid'];
                    break;
            }
        }

        // 分别获取人员、部门、标签详情
        $this->_get_members($memIDs);
        $this->_get_department($depIDs);
        $this->_get_tag($tagIDs);

        // 循环列表，组建返回数据
        $new_list = array();
        foreach ($this->_list as $v) {
            // 本次循环的数据项
            $arr = array(
                'id' => $v['chooseid'],
                'name' => '',
                'face' => '',
                'times' => $v['ct']
            );
            // 根据type分别获取人员、部门和标签信息
            switch ($v['choose_type']) {
                case CommonChooselogModel::CHOOSE_MEM:
                    $arr['name'] = $this->_member[$v['chooseid']]['memUsername'];
                    $arr['face'] = $this->_member[$v['chooseid']]['memFace'];
                    $arr['type'] = 'member';
                    $arr['memMobile'] = $this->_member[$v['chooseid']]['memMobile'];
                    $arr['memEmail'] = $this->_member[$v['chooseid']]['memEmail'];
                    break;
                case CommonChooselogModel::CHOOSE_DEP:
                    $arr['name'] = $this->_department[$v['chooseid']]['dpName'];
                    $arr['type'] = 'department';
                    break;
                case CommonChooselogModel::CHOOSE_TAG:
                    $arr['name'] = $this->_tag[$v['chooseid']]['tagName'];
                    $arr['type'] = 'tag';
                    break;
            }

            // 如果信息不存在，就不取了
            if (empty($arr['type'])) {
                continue;
            }

            // 如果微信端删除人员或者标签，常用人员列表会出现为空
            if (!empty($arr['name'])) {
                // 赋值返回数组
                $new_list[] = $arr;
            }
        }
        return $new_list;
    }

    /**
     * 获取权限认证
     * @param array $authList 权限列表
     * @return array
     */
    protected function get_auth($authList = array())
    {

        // 初始化部门列表
        $auth_depIDs = array();
        // 实例化部门类
        $Department = \Common\Common\Department::instance();

        // 实例化标签类
        $Tag = \Common\Common\Tag::instance();

        // 获取人员UID列表
        $auth_memUids = array_unique(array_filter(array_column($authList['memberList'], 'memUid')));

        // 获取部门列表
        $auth_dpIds = array_unique(array_filter(array_column($authList['departmentList'], 'dpId')));

        // 如果部门存在
        if (!empty($auth_dpIds)) {
            // 获取子集部门列表
            $dpList = $Department->list_childrens_by_cdid($auth_dpIds, true);

            // 重新组装部门
            foreach ($dpList as $item) {

                // 如果部门存在
                if (!empty($item)) {
                    $auth_depIDs[] = $item;
                    $this->get_user($item, $auth_memUids);
                }
            }
        }

        // 获取标签ID列表
        $TagIds = array_unique(array_filter(array_column($authList['tagList'], 'tagId')));

        // 如果标签存在
        if (!empty($TagIds)) {
            // 获取标签列表
            $TagList = $Tag->listUserByTagId($TagIds);

            // 重新组装部门
            foreach ($TagList as $item) {

                // 标签下的部门
                if (!empty($item['dpId'])) {
                    $auth_depIDs[] = $item['dpId'];
                    $this->get_user($item, $auth_memUids);
                }

                // 标签下的部门
                if (!empty($item['memUid'])) {
                    $auth_memUids[] = $item['memUid'];
                }
            }
        }
        return array(array_unique($auth_memUids), array_unique($auth_depIDs), array_unique($TagIds));
    }

    /**
     * 获取用户详情
     * @param String $memUid 用户UID
     * @param String $auth_memUids 用户UIDS
     * @return bool
     */
    protected function get_user($memUid, &$auth_memUids)
    {
        // 实例化人员类
        $Member = new Member(Service::instance());

        // 获取人员列表
        $users = $Member->listAll(array('dpId' => $memUid));
        $users = $Member->listAll(array('dpId' => $memUid), 1, $users['list']['total']);

        // 遍历部门
        foreach ($users['list'] as $user) {
            $auth_memUids[] = $user['memUid'];
        }
        return true;
    }
}

