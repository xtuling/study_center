<?php
/**
 * 选人组件-搜索接口
 * Created by PhpStorm.
 * User: 鲜彤
 * Date: 2016/8/26
 * Time: 15:00
 */
namespace Apicp\Controller\ChooseMem;

use VcySDK\Service;
use VcySDK\Member;

class SearchController extends AbstractController
{

    /**
     * 成员类型
     *
     * @var unknown
     */
    const TYPE_MEMBER = 1;

    /**
     * 部门类型
     *
     * @var unknown
     */
    const TYPE_DEPARTMEN = 2;

    /**
     * 标签类型
     *
     * @var unknown
     */
    const TYPE_TAG = 3;

    public function Index()
    {

        $search = I("post.search");

        // 获取参数key
        $key = I('post.key', '', 'trim');
        $field = I('post.field', []);

        // 调用SDK获取列表
        $sdk = new Member(Service::instance());
        $result = $sdk->searchList(array('name' => $key));

        // 循环返回列表，更改类型字段值
        foreach ($result['list'] as $k => $v) {
            // 如果有需要查询某类 并且 返回值的类型不在内， 则剔除
            if (!empty($field) && !in_array($v['flag'], $field)) {
                unset($result['list'][$k]);
                continue;
            }

            $flag = $v['flag'];
            $type = '';
            switch ($flag) {
                case self::TYPE_MEMBER:
                    $type = 'member';
                    break;
                case self::TYPE_DEPARTMEN:
                    $type = 'department';
                    break;
                case self::TYPE_TAG:
                    $type = 'tag';
                    break;
            }

            // 删掉UC返回的flag键
            unset($result['list'][$k]['flag']);

            // 添加type键
            $result['list'][$k]['type'] = $type;
        }

        // 特殊处理
        if (!empty($search)) {

            $data = $this->search($result['list'], $search);

            $result['list'] = $data;
            $result['total'] = count($data);
        }

        $this->_result = $result;

        return true;
    }

    /**
     * 特殊查询处理
     * @param array $list 搜索后的数据列表
     * @param array $search
     *              +array dpIds  部门IDS
     *              +array uids  人员UIDS
     *              +array tagIds  标签IDS
     * @return array
     */
    private function search($list = array(), $search = array())
    {
        // 初始化返回值
        $data = array();

        // 遍历结果集
        foreach ($list as $key => $v) {

            // 如果是人员
            if ($v['type'] == 'member') {

                if (in_array($v['id'], $search['uids'])) {

                    $data[] = $v;
                }

            }

            // 如果是部门
            if ($v['type'] == 'department') {

                if (in_array($v['id'], $search['dpIds'])) {

                    $data[] = $v;
                }
            }

            // 如果是标签
            if ($v['type'] == 'tag') {

                if (in_array($v['id'], $search['tagIds'])) {

                    $data[] = $v;

                }
            }


        }
        return $data;
    }
}
