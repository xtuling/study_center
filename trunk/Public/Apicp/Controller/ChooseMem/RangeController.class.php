<?php
/**
 * 可见范围接口
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:49
 */
namespace Apicp\Controller\ChooseMem;

use VcySDK\Department;
use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Tag;


class RangeController extends AbstractController
{

    /**
     * VcySDK 附件操作类
     *
     * @type Member
     */
    protected $_member;

    /**
     * VcySDK 部门操作类
     *
     * @type Department
     */
    protected $_department;

    /**
     * VcySDK 标签操作类
     *
     * @type Tag
     */
    protected $_tag;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_member = new Member($serv);
        $this->_department = new Department($serv);
        $this->_tag = new Tag($serv);

        return true;
    }

    public function Index()
    {

        $data = I("post.data");
        // 获取用户信息
        $member = $this->member($data);
        // 初始化部门
        $depatments = array();

        // 判断人员部门是否查询全部
        if (! empty($data['department'])) {
            // 查询部分指定部门
            $depatments = $this->partdp($data['department']);
        } else {
            // 获取全部部门
            $depatments = $this->alldp();
        }

        // 获取标签列表
        $tags = $this->taglist($data);
        $this->_result = array(
            'member' => $member,
            'department' => $depatments,
            'tag' => $tags
        );

        return true;
    }

    /**
     * 获取指定部门信息
     *
     * @param array $list 部门ID列表
     *
     * @return array
     */
    private function partdp($list)
    {

        // 初始化
        $data = array();

        // 遍历用户部门ID
        foreach ($list as $key => $v) {
            // 获取部门详情
            $list = $this->_department->detail(array('dpId' => $v['dpId']));
            $data[] = array(
                'dpId' => $list['data']['dpId'],
                'dpName' => $list['data']['dpName'],
            );
        }

        return $data;
    }

    /**
     * 获取全部部门信息
     *
     * @return array
     */
    private function alldp()
    {

        // 初始化
        $data = array();
        // 获取全部部门
        $list = $this->_department->listAll();
        // 遍历全部部门
        foreach ($list['list'] as $key => $item) {
            $data[] = array(
                'dpId' => $item['dpId'],
                'dpName' => $item['dpName']
            );
        }

        return $data;
    }

    /**
     * 获取人员信息
     *
     * @param array $list POST数组
     *
     * @return array
     */
    private function member($list)
    {

        // 初始化
        $data = array();
        // 获取所有用户UIDS
        $uids = array_column($list['member'], 'memUid');
        // 初始化用户
        $member = array();
        // 初始化查询人员条件
        $condition = ! empty($list['member']) ? array('memUids' => $uids) : array();
        // 获取人员
        $member = $this->_member->listAll($condition);
        // 循环输出人员列表
        foreach ($member['list'] as $key => $v) {
            $data[] = array(
                'memUid' => $v['memUid'],
                'memFace' => $v['memFace'] ? $v['memFace'] : '',
                'memUsername' => $v['memUsername']
            );
        }

        return $data;
    }

    /**
     * 获取标签详情
     *
     * @param array $list POST 列表数据
     *
     * @return array
     */
    public function taglist($list)
    {

        // 初始化
        $data = array();
        // 获取所有用户标签ID
        $tags = array_column($list['tag'], 'tagid');
        // 如果标签ID不存在
        if (empty($tags)) {
            return $data;
        }

        // 获取标签列表
        $result = $this->_tag->listAll(['tagIds' => $tags, 'pageSize' => 1000]);
        // 循环遍历
        foreach ($result['list'] as $item) {
            $data[] = array(
                'tagId' => $item['tagId'],
                'tagName' => $item['tagName'] ? $item['tagName'] : '',
            );
        }

        return $data;
    }

}
