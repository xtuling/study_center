<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/19
 * Time: 19:59
 */

namespace Common\Service;

use VcySDK\Service;
use VcySDK\Tag;

class TagService
{

    /**
     * 是否按应用权限对标签进行过滤,不过滤
     */
    const TAG_FILTER_DISABLED = 1;

    /**
     * 是否按应用权限对标签进行过滤,过滤
     */
    const TAG_FILTER_ENABLED = 0;

    /**
     * 标签类型,畅移创建的标签
     */
    const TAG_TYPE_CY = 1;

    /**
     * 标签类型,其它
     */
    const TAG_TYPE_OTHER = 2;

    /**
     * 标签成员类型,用户
     */
    const MEMBER_TYPE_USER = 1;

    /**
     * 标签类型,部门
     */
    const MEMBER_TYPE_DEPT = 2;

    /**
     * 获取标签列表
     *
     * @author zhonglei
     *
     * @param array $params 查询参数
     *
     * @return array
     */
    public function listAll($params = [])
    {

        $result = \Common\Common\Tag::instance()->listAll($params);
        $list = [];

        foreach ($result as $v) {
            $list[] = [
                'tag_id' => $v['tagId'],
                'tagname' => $v['tagName'],
                'order' => $v['tagDisplayorder'],
                'total' => $v['tagUsersSum'],
            ];
        }

        $orders = array_column($list, 'order');
        array_multisort($orders, $list);

        return $list;
    }

    /**
     * 保存标签
     *
     * @author zhonglei
     *
     * @param string  $tagId    标签ID
     * @param string  $tagName  标签名称
     * @param integer $tagOrder 排序
     *
     * @return mixed
     */
    public function save($tagId, $tagName, $tagOrder)
    {

        $tag = \Common\Common\Tag::instance();

        if (!$tagId) {
            // 创建时UC顺序不保存,需先创建标签,再修改标签顺序 edit by liyifei 2017-04-05 14:46:49
            $result = $tag->create([
                'tagName' => $tagName,
                'tagDisplayorder' => $tagOrder
            ]);

            $tagId = $result['tagId'];
        }

        $tag->update([
            'tagId' => $tagId,
            'tagName' => $tagName,
            'tagDisplayorder' => $tagOrder
        ]);

        return true;
    }

    /**
     * 删除标签
     *
     * @author zhonglei
     *
     * @param string $tagId 标签ID
     *
     * @return void
     */
    public function delete($tagId)
    {

        \Common\Common\Tag::instance()->delete(['tagId' => $tagId]);
    }

    /**
     * 获取标签成员列表
     *
     * @author zhonglei
     *
     * @param array $params  查询参数
     * @param int   $page    当前页
     * @param int   $perpage 每页个数
     *
     * @return array
     */
    public function memberList($params, $page, $perpage)
    {

        $result = \Common\Common\Tag::instance()->listUserAll($params, $page, $perpage);

        $data = [
            'page' => $result['pageNum'],
            'limit' => $result['pageSize'],
            'total' => $result['total'],
            'list' => [],
        ];

        foreach ($result['list'] as $v) {
            if (empty($v['memUid'])) {
                // 部门
                $member = [
                    'type' => self::MEMBER_TYPE_DEPT,
                    'member_id' => $v['dpId'],
                    'name' => $v['dpName'],
                ];
            } else {
                // 人员
                $member = [
                    'type' => self::MEMBER_TYPE_USER,
                    'member_id' => $v['memUid'],
                    'name' => $v['memUsername'],
                    'face' => $v['memFace'],
                ];
            }

            $data['list'][] = $member;
        }

        return $data;
    }

    /**
     * 添加标签成员
     *
     * @author zhonglei
     *
     * @param string $tagId    标签ID
     * @param array  $uids     用户ID数组
     * @param array  $dept_ids 部门ID数组
     *
     * @return void
     */
    public function addMember($tagId, $uids, $dept_ids)
    {

        $params = ['tagId' => $tagId];

        if (is_array($uids) && $uids) {
            $params['userIds'] = $uids;
        }

        if (is_array($dept_ids) && $dept_ids) {
            $params['partyIds'] = $dept_ids;
        }

        \Common\Common\Tag::instance()->addUsers($params);
    }

    /**
     * 删除标签成员
     *
     * @author zhonglei
     *
     * @param string $tagId    标签ID
     * @param array  $uids     用户ID数组
     * @param array  $dept_ids 部门ID数组
     *
     * @return void
     */
    public function deleteMember($tagId, $uids, $dept_ids)
    {

        $params = ['tagId' => $tagId];

        if (is_array($uids) && $uids) {
            $params['userIds'] = $uids;
        }

        if (is_array($dept_ids) && $dept_ids) {
            $params['partyIds'] = $dept_ids;
        }

        \Common\Common\Tag::instance()->delUsers($params);
    }

    /**
     * 清空标签成员
     * @author zhonglei
     * @param string $tagId 标签ID
     * @return void
     */
    public function emptyMember($tagId)
    {

        $tag = new Tag(Service::instance());
        $tag->delTagUsers(['tagId' => $tagId]);
    }
}
