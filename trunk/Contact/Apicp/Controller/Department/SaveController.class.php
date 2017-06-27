<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:13
 */

namespace Apicp\Controller\Department;

use Common\Common\Cache;
use Common\Service\DepartmentService;
use Common\Service\DeptRightService;

class SaveController extends AbstractController
{

    /**
     * 【通讯录】保存部门（新增、修改）
     * @author liyifei
     * @time 2016-09-17 23:13:58
     */
    public function Index_post()
    {

        // 接收参数
        $dpId = I('post.department_id', '', 'trim');
        $parentId = I('post.parent_id', '', 'trim');
        $dpName = I('post.dp_name', '', 'trim');
        $isAll = I('post.is_all', 0, 'intval');
        $isDept = I('post.is_dept', 0, 'intval');
        $dpIds = I('post.dp_ids');
        $dpLeaderUids = I('post.dp_leader_uids');
        $order = I('post.order', 0, 'intval');
        $dptId = I('post.dpt_id', '', 'trim');
        $dpSerialNum = I('post.dp_serial_num', '', 'trim');

        // 部门可见范围错误
        if ($isAll === 0 && $isDept === 0 && empty($dpIds)) {
            E('_ERR_DEPT_VISIBLE_RANGE');
        }
        // 部门可见范围参数格式错误
        if (!empty($dpIds) && !is_array($dpIds)) {
            E('_ERR_DEPT_RANGE_PARAM');
        }
        // 部门负责人参数格式错误
        if (!empty($dpLeaderUids) && !is_array($dpLeaderUids)) {
            E('_ERR_DEPT_LEADER_PARAM');
        }

        $data = [
            'isAll' => $isAll,
            'isDept' => $isDept,
            'dpIds' => $dpIds,
            'dpLeaderUids' => '',
            'dpLead' => '',
            'dptId' => $dptId,
            'dpSerialNum' => $dpSerialNum
        ];
        if (!empty($dpName)) {
            $data['dpName'] = $dpName;
        }
        if (!empty($parentId)) {
            $data['dpParentid'] = $parentId;
        }
        if (!empty($dpLeaderUids)) {
            // 将前端传递的uid数组,转为架构需要的以逗号分隔的uid字符串
            //$data['dpLeaderUids'] = implode(',', $dpLeaderUids);
            $data['dpLead'] = implode(',', $dpLeaderUids);
        }
        $data['dptId'] = $dptId;
        $data['dpSerialNum'] = $dpSerialNum;

        // 获取扩展字段信息
        $departmentService = new DepartmentService();
        $departmentService->getExt($extList, $dptId, I('post.extList'));
        $data['departmentExtJson'] = rjson_encode($extList, JSON_FORCE_OBJECT);

        $dpRightServ = new DeptRightService();
        if ($dpId) {
            // 修改部门
            if (strlen($order) == 0) {
                // 部门顺序号不能为空
                E('_ERR_DEPT_ORDER_UNDEFINED');
            } else {
                $data['order'] = $order;
            }
            $dpRightServ->updateDp($dpId, $data);

        } else {
            // 新增部门
            if (empty($parentId) || empty($dpName)) {
                // 缺少参数
                E('_ERR_PARAM_IS_NULL');
            }
            $dpRightServ->createDp($data);
        }

        $this->clearDepartmentCache();

        return true;
    }

}
