<?php
/**
 * 微信端id转服务器端id列表逻辑
 * ListChooseController.class.php
 * $author$ 何岳龙
 * $date$   2016年8月29日16:53:37
 */

namespace Api\Controller\ChooseMem;

use Common\Common\Department;
use Common\Common\Tag;
use Common\Common\User;

class Wx2LocalController extends AbstractController
{

    public function Index()
    {

        // 初始化人员返回值
        $users = array();
        // 初始化部门返回值
        $departments = array();
        // 初始化标签返回值
        $tags = array();

        // 如果是全选
        $this->_listBySelect($users, $departments, $tags);

        // 人员列表
        $this->filterByKey($users, array('memUid', 'memUserid', 'memUsername', 'memFace'));
        // 部门列表
        $this->filterByKey($departments, array('dpId', 'dpThirdid', 'dpName'));
        // 标签列表
        $this->filterByKey($tags, array('tagId', 'tagThirdId', 'tagName'));

        $this->_result = array(
            'users' => $users,
            'departments' => $departments,
            'tags' => !empty($tags) ? $tags : array()
        );

        return true;
    }

    /**
     * 获取已经选择的用户/部门/标签列表
     *
     * @param array &$memList 用户列表
     * @param array &$dpList  部门列表
     * @param array &$tagList 标签列表
     *
     * @return bool
     */
    protected function _listBySelect(&$memList = array(), &$dpList = array(), &$tagList = array())
    {

        // 获取用户Ids数组(微信端Userid)
        $userIds = I('post.userIds');
        // 获取部门IDs(微信端部门id)
        $dpIds = I('post.dpIds');
        // 获取标签IDs(微信端标签id)
        $tagIds = I('post.tagIds');

        // 判断用户
        if (!empty($userIds) && is_array($userIds)) {
            // 获取指定用列表
            $result = User::instance()->listByConds(array('userids' => $userIds), self::PAGE, count($userIds));
            $memList = $result['list'];
        }

        // 判断部门
        if (!empty($dpIds) && is_array($dpIds)) {
            // 通过条件获取部门列表
            $dpList = Department::instance()->listById($dpIds, array(), true);
        }

        // 判断标签
        if (!empty($tagIds) && is_array($tagIds)) {
            $result = Tag::instance()->listAll(array(
                'tagThirdIds' => $tagIds,
                'pageNum' => self::PAGE,
                'pageSize' => count($tagIds)
            ));
            $tagList = $result['list'];
        }

        return true;
    }

    /**
     * 返回form数组
     *
     * @param array &$list  数据列表
     * @param array $fields 需要的键值
     *
     * @return array
     */

    protected function filterByKey(&$list, $fields = array())
    {

        // 如果列表不是数组或为空，就直接返回空数组
        if (!is_array($list) || empty($list)) {
            return array();
        }

        // 初始化数组
        $data = array();
        $fields = array_fill_keys($fields, '');
        foreach ($list as $key => $_value) {
            $data[$key] = array_intersect_key($_value, $fields);
        }
        $list = array_values($data);

        return $data;
    }

}
