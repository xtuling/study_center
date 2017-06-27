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

class ListController extends AbstractController
{

    public function Index()
    {

        // 获取是否全选
        $selectAll = I('post.selectAll');
        // 初始化人员返回值
        $mems = array();
        // 初始化部门返回值
        $dps = array();
        // 初始化标签返回值
        $tagList = array();

        // 如果是全选
        if ($selectAll == self::SELECT_All) {
            $this->_listAll($mems, $dps, $tagList);
        } else {
            $this->_listBySelect($mems, $dps, $tagList);
        }

        // 人员列表
        $this->filterByKey($mems, array('memUid', 'memUserid', 'memUsername', 'memFace'));
        // 部门列表
        $this->filterByKey($dps, array('dpId', 'dpThirdid', 'dpName'));
        // 标签列表
        $this->filterByKey($tagList, array('tagId', 'tagThirdId', 'tagName'));

        $this->_result = array(
            'isAll' => $selectAll,
            'list' => array(
                'userIds' => $mems,
                'dpIds' => $dps,
                'tagIds' => !empty($tagList) ? $tagList : array()
            )
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

        // 获取用户Ids数组
        $userIds = I('post.userIds');
        // 获取部门IDs
        $dpIds = I('post.dpIds');
        // 获取标签IDs
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
     * 获取所有用户/部门/标签列表
     *
     * @param array &$memList 用户列表
     * @param array &$dpList  部门列表
     * @param array &$tagList 标签列表
     *
     * @return bool
     */
    protected function _listAll(&$memList = array(), &$dpList = array(), &$tagList = array())
    {

        // 获取全部列表
        $result = User::instance()->listByConds(array(), self::PAGE, self::MAX_LIMIT);
        $memList = $result['list'];
        // 获取全部部门
        $dpList = Department::instance()->listAll();
        // 获取标签列表
        $result = Tag::instance()->listAll(array('pageSize' => self::MAX_LIMIT));
        $tagList = $result['list'];

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
