<?php
/**
 * 选人组件-人员相关接口
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年9月1日15:19:36
 */
namespace Apicp\Controller\ChooseMem;

use VcySDK\Service;
use VcySDK\Member;

class MemberListController extends AbstractController
{

    /**
     * VcySDK 人员操作类
     *
     * @type Member
     */
    protected $_mem = null;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_mem = new Member($serv);

        return true;
    }

    public function Index()
    {

        // 部门ID
        $dpId = I('post.dpId');
        $limit = I("post.limit", 10);
        $page = I("post.page", 1);

//        // 部门ID不存在
//        if (empty($dpId)) {
//            $this->_set_error('_ERR_EMPTY_DPID_ID');
//            return false;
//        }

        // 调用SDK获取用户列表
        $member_list = $this->_mem->listAll(empty($dpId) ? [] : ['dpId' => $dpId], $page, $limit);
        $list = $member_list['list'];

        // 格式化列表
        $return = array();
        foreach ($list as $v) {
            $return[] = array(
                'memUid' => $v['memUid'],
                'memUsername' => $v['memUsername'],
                'memFace' => $v['memFace'],
                'memMobile' => $v['memMobile'],
                'memEmail' => $v['memEmail']
            );
        }

        $this->_result = array(
            'total' => (int)$member_list['total'],
            'limit' => (int)$member_list['pageSize'],
            'page' => (int)$member_list['pageNum'],
            'list' => $return
        );

        return true;
    }
}
